<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\ChatContactoCanal;
use App\Models\ChatMemoriaContacto;
use App\Models\ChatMemoriaNegocio;
use App\Models\ChatMemoriaResumen;
use App\Models\ChatMensajeCanal;
use App\Models\ChatSubagente;
use App\Models\Cliente;
use App\Models\Conversacion;
use App\Models\Mensaje;
use App\Services\Chat\WhatsAppOutboundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatRouterController extends Controller
{
    private const CANALES = ['whatsapp', 'messenger', 'telegram', 'webchat'];
    private const ESTADOS_ABIERTOS = ['abierta', 'bot_activo', 'en_atencion', 'en_espera'];
    private const DEBOUNCE_SECONDS = 35;
    private const SUBAGENTE_POR_DEFECTO = 'router_general';
    private const SUBAGENTE_HANDOFF = 'espera_humano';
    private const SUBAGENTE_ALIAS = [
        'router' => 'router_general',
        'humano' => 'espera_humano',
        'ventas' => 'vendedor_cierre',
        'soporte' => 'soporte_cliente',
        'cobranzas' => 'cobranzas_pago',
        'postventa' => 'postventa_reciente',
    ];

    public function __construct(private readonly WhatsAppOutboundService $whatsAppOutboundService)
    {
    }

    public function recibirMensaje(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'canal' => 'required|in:' . implode(',', self::CANALES),
            'canal_user_id' => 'required|string|max:120',
            'mensaje' => 'nullable|string',
            'contenido' => 'nullable|string',
            'tipo_contenido' => 'nullable|in:texto,imagen,archivo,audio,video,documento,sticker',
            'external_message_id' => 'nullable|string|max:191',
            'external_thread_id' => 'nullable|string|max:191',
            'telefono' => 'nullable|string|max:40',
            'numero' => 'nullable|string|max:40',
            'nombre' => 'nullable|string|max:120',
            'idcli' => 'nullable|exists:clientes,idcli',
            'origen' => 'nullable|string|max:50',
            'payload' => 'nullable|array',
            'media_url' => 'nullable|string',
            'media_mime_type' => 'nullable|string|max:120',
            'media_id' => 'nullable|string|max:191',
            'subagente_codigo' => 'nullable|string|max:50',
            'debounce_seconds' => 'nullable|integer|min:1|max:300',
            'instance' => 'nullable|string|max:120',
            'apikey' => 'nullable|string|max:191',
            'server_url' => 'nullable|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $contenido = trim((string) ($request->input('mensaje') ?? $request->input('contenido') ?? ''));

        if ($contenido === '' && !$request->filled('media_url')) {
            return response()->json([
                'success' => false,
                'message' => 'Debes enviar mensaje o media_url.',
            ], 422);
        }

        try {
            $resultado = DB::transaction(function () use ($request, $contenido) {
                $canal = $request->string('canal')->toString();
                $canalUserId = trim((string) $request->input('canal_user_id'));
                $telefono = $this->normalizePhone($request->input('telefono') ?? $request->input('numero') ?? $canalUserId);
                $cliente = $this->resolveCliente($request->input('idcli'), $telefono);
                $whatsappChannel = $this->resolveWhatsappChannel($request->input('instance'));
                $whatsappColor = $whatsappChannel?->color ?? $this->resolveWhatsappColorByInstance($request->input('instance'));

                $subagenteCodigo = $this->normalizeSubagentCode($request->input('subagente_codigo'))
                    ?? self::SUBAGENTE_POR_DEFECTO;

                $tipoContenido = $this->normalizeContentType($request->input('tipo_contenido', 'texto'));

                $contacto = ChatContactoCanal::query()->firstOrCreate(
                    [
                        'canal' => $canal,
                        'canal_user_id' => $canalUserId,
                    ],
                    [
                        'telefono_normalizado' => $telefono,
                        'nombre_canal' => $request->input('nombre'),
                        'idcli' => $cliente?->idcli,
                        'estado_relacion' => $cliente ? 'cliente' : 'lead',
                        'origen' => $request->input('origen', 'n8n'),
                        'metadata' => $this->buildChannelMetadata($request),
                        'last_seen_at' => now(),
                    ]
                );

                $contacto->fill([
                    'telefono_normalizado' => $telefono ?: $contacto->telefono_normalizado,
                    'nombre_canal' => $request->input('nombre', $contacto->nombre_canal),
                    'idcli' => $cliente?->idcli ?? $contacto->idcli,
                    'estado_relacion' => $cliente ? 'cliente' : $contacto->estado_relacion,
                    'origen' => $request->input('origen', $contacto->origen),
                    'metadata' => array_merge($contacto->metadata ?? [], $this->buildChannelMetadata($request)),
                    'last_seen_at' => now(),
                ]);
                $contacto->save();

                $conversacion = Conversacion::query()
                    ->where('canal_contacto_id', $contacto->id)
                    ->whereIn('estado', self::ESTADOS_ABIERTOS)
                    ->latest('ultima_actividad')
                    ->first();

                if (!$conversacion) {
                    $conversacion = Conversacion::create([
                        'idcli' => $cliente?->idcli,
                        'canal_principal' => $canal,
                        'canal_contacto_id' => $contacto->id,
                        'origen' => $request->input('origen', 'n8n'),
                        'subagente_codigo' => $subagenteCodigo,
                        'estado' => 'abierta',
                        'ultima_actividad' => now(),
                        'mensajes_no_leidos' => 0,
                        'requiere_humano' => false,
                        'metadata' => [
                            'canal_user_id' => $canalUserId,
                            'instance' => $request->input('instance') ?: $whatsappChannel?->instance_name,
                            'apikey' => $request->input('apikey') ?: $whatsappChannel?->api_key,
                            'server_url' => $request->input('server_url') ?: $whatsappChannel?->server_url,
                            'whatsapp_channel_id' => $whatsappChannel?->id,
                            'whatsapp_color' => $whatsappColor,
                        ],
                    ]);
                } else {
                    $conversacion->fill([
                        'idcli' => $cliente?->idcli ?? $conversacion->idcli,
                        'subagente_codigo' => $subagenteCodigo ?: $conversacion->subagente_codigo,
                        'ultima_actividad' => now(),
                        'metadata' => array_merge($conversacion->metadata ?? [], [
                            'canal_user_id' => $canalUserId,
                            'instance' => $request->input('instance', data_get($conversacion->metadata, 'instance') ?: $whatsappChannel?->instance_name),
                            'apikey' => $request->input('apikey', data_get($conversacion->metadata, 'apikey') ?: $whatsappChannel?->api_key),
                            'server_url' => $request->input('server_url', data_get($conversacion->metadata, 'server_url') ?: $whatsappChannel?->server_url),
                            'whatsapp_channel_id' => data_get($conversacion->metadata, 'whatsapp_channel_id') ?: $whatsappChannel?->id,
                            'whatsapp_color' => data_get($conversacion->metadata, 'whatsapp_color') ?: $whatsappColor,
                        ]),
                    ]);
                    $conversacion->save();
                }

                if ($request->filled('external_message_id')) {
                    $chatMensajeExistente = ChatMensajeCanal::query()
                        ->where('canal', $canal)
                        ->where('external_message_id', $request->input('external_message_id'))
                        ->first();

                    if ($chatMensajeExistente) {
                        return [
                            'conversacion' => $conversacion,
                            'contacto' => $contacto,
                            'mensaje' => $chatMensajeExistente->mensaje,
                            'canal_mensaje' => $chatMensajeExistente,
                            'duplicado' => true,
                        ];
                    }
                }

                $mensaje = Mensaje::create([
                    'idconv' => $conversacion->idconv,
                    'tipo_remitente' => 'cliente',
                    'idcli' => $cliente?->idcli,
                    'contenido' => $contenido,
                    'tipo_contenido' => $tipoContenido,
                    'archivo_url' => $request->input('media_url'),
                    'leido' => false,
                    'respondido_por_ai' => false,
                    'metadata' => [
                        'origen' => 'router',
                        'tipo_contenido_original' => $request->input('tipo_contenido', 'texto'),
                        'external_message_id' => $request->input('external_message_id'),
                        'instance' => $request->input('instance'),
                        'payload' => $request->input('payload'),
                    ],
                ]);

                $canalMensaje = ChatMensajeCanal::create([
                    'idmsg' => $mensaje->idmsg,
                    'idconv' => $conversacion->idconv,
                    'contacto_canal_id' => $contacto->id,
                    'canal' => $canal,
                    'direccion' => 'inbound',
                    'external_message_id' => $request->input('external_message_id'),
                    'external_thread_id' => $request->input('external_thread_id'),
                    'external_status' => 'received',
                    'media_id' => $request->input('media_id'),
                    'media_url' => $request->input('media_url'),
                    'media_mime_type' => $request->input('media_mime_type'),
                    'payload' => array_merge($request->input('payload', []), [
                        'instance' => $request->input('instance') ?: $whatsappChannel?->instance_name,
                        'apikey' => $request->input('apikey') ?: $whatsappChannel?->api_key,
                        'server_url' => $request->input('server_url') ?: $whatsappChannel?->server_url,
                        'whatsapp_channel_id' => $whatsappChannel?->id,
                        'whatsapp_color' => $whatsappColor,
                    ]),
                ]);

                $conversacion->increment('mensajes_no_leidos');
                $conversacion->update([
                    'estado' => $conversacion->requiere_humano ? 'en_espera' : 'abierta',
                    'ultima_actividad' => now(),
                ]);

                return [
                    'conversacion' => $conversacion->fresh(),
                    'contacto' => $contacto->fresh(),
                    'mensaje' => $mensaje->fresh(),
                    'canal_mensaje' => $canalMensaje,
                    'duplicado' => false,
                ];
            });

            $debounceSeconds = (int) ($request->input('debounce_seconds') ?? self::DEBOUNCE_SECONDS);

            return response()->json([
                'success' => true,
                'message' => $resultado['duplicado']
                    ? 'Mensaje ya registrado previamente.'
                    : 'Mensaje inbound registrado correctamente.',
                'data' => [
                    'idconv' => $resultado['conversacion']->idconv,
                    'idmsg' => $resultado['mensaje']?->idmsg,
                    'contacto_canal_id' => $resultado['contacto']->id,
                    'chat_mensaje_canal_id' => $resultado['canal_mensaje']->id ?? null,
                    'duplicado' => $resultado['duplicado'],
                    'esperar_hasta' => now()->addSeconds($debounceSeconds)->toIso8601String(),
                    'conversacion_estado' => $resultado['conversacion']->estado,
                ],
            ], $resultado['duplicado'] ? 200 : 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar mensaje inbound.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function contextoConversacion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idconv' => 'nullable|exists:conversaciones,idconv',
            'canal' => 'nullable|in:' . implode(',', self::CANALES),
            'canal_user_id' => 'nullable|string|max:120',
            'trigger_idmsg' => 'nullable|exists:mensajes,idmsg',
            'external_message_id' => 'nullable|string|max:191',
            'historial_limite' => 'nullable|integer|min:1|max:50',
            'memoria_limite' => 'nullable|integer|min:1|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $conversacion = $this->resolveConversation($request);

            if (!$conversacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversación no encontrada.',
                ], 404);
            }

            $historialLimite = (int) ($request->input('historial_limite') ?? 10);
            $memoriaLimite = (int) ($request->input('memoria_limite') ?? 8);

            $mensajesPendientes = Mensaje::query()
                ->where('idconv', $conversacion->idconv)
                ->where('tipo_remitente', 'cliente')
                ->where(function ($query) {
                    $query->whereNull('respondido_por_ai')
                        ->orWhere('respondido_por_ai', false);
                })
                ->orderBy('created_at', 'asc')
                ->get();

            $primerPendienteId = $mensajesPendientes->first()?->idmsg;

            $historialReciente = Mensaje::query()
                ->where('idconv', $conversacion->idconv)
                ->when($primerPendienteId, function ($query) use ($primerPendienteId) {
                    $query->where('idmsg', '<', $primerPendienteId);
                })
                ->with(['cliente:idcli,nombrecli', 'empleado:idemp,nombreemp'])
                ->orderBy('created_at', 'desc')
                ->limit($historialLimite)
                ->get()
                ->reverse()
                ->values();

            $triggerId = $this->resolveTriggerMessageId($request, $conversacion->idconv);
            $ultimoPendienteId = $mensajesPendientes->last()?->idmsg;
            $debeResponder = $ultimoPendienteId !== null && ($triggerId === null || (int) $triggerId === (int) $ultimoPendienteId);

            $contacto = $conversacion->contactoCanal()->with('cliente')->first();

            $memoriasContacto = ChatMemoriaContacto::query()
                ->where('contacto_canal_id', $conversacion->canal_contacto_id)
                ->orderByDesc('ultima_referencia_at')
                ->orderByDesc('id')
                ->limit($memoriaLimite)
                ->get();

            $resumenes = ChatMemoriaResumen::query()
                ->where(function ($query) use ($conversacion) {
                    $query->where('idconv', $conversacion->idconv);

                    if ($conversacion->canal_contacto_id) {
                        $query->orWhere('contacto_canal_id', $conversacion->canal_contacto_id);
                    }
                })
                ->with('subagente:id,codigo,nombre,tipo')
                ->orderByDesc('ventana_hasta')
                ->orderByDesc('id')
                ->limit(5)
                ->get();

            $subagentes = ChatSubagente::query()
                ->where('activo', true)
                ->orderBy('prioridad')
                ->get(['id', 'codigo', 'nombre', 'tipo', 'descripcion', 'criterios']);

            $memoriaNegocio = ChatMemoriaNegocio::query()
                ->where('activo', true)
                ->whereIn('visibilidad', ['cliente', 'ambas'])
                ->orderBy('prioridad')
                ->limit(12)
                ->get(['id', 'tipo', 'clave', 'titulo', 'resumen', 'contenido', 'tags']);

            return response()->json([
                'success' => true,
                'message' => $debeResponder
                    ? 'Contexto listo para responder.'
                    : 'Ya existe un mensaje más reciente pendiente por procesar.',
                'data' => [
                    'debe_responder' => $debeResponder,
                    'idconv' => $conversacion->idconv,
                    'contacto' => $contacto,
                    'conversacion' => [
                        'idconv' => $conversacion->idconv,
                        'estado' => $conversacion->estado,
                        'requiere_humano' => $conversacion->requiere_humano,
                        'subagente_codigo' => $conversacion->subagente_codigo,
                        'ultima_actividad' => optional($conversacion->ultima_actividad)->toIso8601String(),
                    ],
                    'trigger_idmsg' => $triggerId,
                    'ultimo_pendiente_idmsg' => $ultimoPendienteId,
                    'mensajes_pendientes' => $mensajesPendientes->map(fn (Mensaje $mensaje) => $this->formatMessage($mensaje)),
                    'mensaje_agrupado' => $this->buildAggregatedMessage($mensajesPendientes),
                    'historial_reciente' => $historialReciente->map(fn (Mensaje $mensaje) => $this->formatMessage($mensaje)),
                    'memorias_contacto' => $memoriasContacto,
                    'resumenes' => $resumenes,
                    'memoria_negocio' => $memoriaNegocio,
                    'subagentes' => $subagentes,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al construir el contexto de la conversación.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function responderAgente(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idconv' => 'nullable|exists:conversaciones,idconv',
            'canal' => 'nullable|in:' . implode(',', self::CANALES),
            'canal_user_id' => 'nullable|string|max:120',
            'contenido' => 'required|string',
            'tipo_contenido' => 'nullable|in:texto,imagen,archivo,audio,video,documento,sticker',
            'media_url' => 'nullable|string',
            'media_mime_type' => 'nullable|string|max:120',
            'media_id' => 'nullable|string|max:191',
            'subagente_codigo' => 'nullable|string|max:50',
            'metadata' => 'nullable|array',
            'external_message_id' => 'nullable|string|max:191',
            'external_thread_id' => 'nullable|string|max:191',
            'instance' => 'nullable|string|max:120',
            'apikey' => 'nullable|string|max:191',
            'marcar_leidos' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $resultado = DB::transaction(function () use ($request) {
                $conversacion = $this->resolveConversation($request);

                if (!$conversacion) {
                    throw new \RuntimeException('Conversación no encontrada.');
                }

                $pendientes = Mensaje::query()
                    ->where('idconv', $conversacion->idconv)
                    ->where('tipo_remitente', 'cliente')
                    ->where(function ($query) {
                        $query->whereNull('respondido_por_ai')
                            ->orWhere('respondido_por_ai', false);
                    })
                    ->get();

                foreach ($pendientes as $pendiente) {
                    $pendiente->update([
                        'respondido_por_ai' => true,
                        'leido' => (bool) ($request->input('marcar_leidos', true)),
                        'leido_at' => now(),
                        'metadata' => array_merge($pendiente->metadata ?? [], [
                            'ai_response_at' => now()->toIso8601String(),
                        ]),
                    ]);
                }

                $subagenteCodigo = $this->normalizeSubagentCode($request->input('subagente_codigo'))
                    ?? $conversacion->subagente_codigo
                    ?? self::SUBAGENTE_POR_DEFECTO;

                $tipoContenido = $this->normalizeContentType($request->input('tipo_contenido', 'texto'));

                $mensaje = Mensaje::create([
                    'idconv' => $conversacion->idconv,
                    'tipo_remitente' => 'ia',
                    'contenido' => $request->input('contenido'),
                    'tipo_contenido' => $tipoContenido,
                    'archivo_url' => $request->input('media_url'),
                    'leido' => true,
                    'respondido_por_ai' => true,
                    'metadata' => array_merge($request->input('metadata', []), [
                        'subagente_codigo' => $subagenteCodigo,
                        'instance' => $request->input('instance'),
                        'tipo_contenido_original' => $request->input('tipo_contenido', 'texto'),
                        'respondio_pendientes' => $pendientes->pluck('idmsg')->values(),
                    ]),
                ]);

                $contacto = $conversacion->contactoCanal;

                $canalMensaje = null;
                if ($contacto && $conversacion->canal_principal) {
                    $canalMensaje = ChatMensajeCanal::create([
                        'idmsg' => $mensaje->idmsg,
                        'idconv' => $conversacion->idconv,
                        'contacto_canal_id' => $contacto->id,
                        'canal' => $conversacion->canal_principal,
                        'direccion' => 'outbound',
                        'external_message_id' => $request->input('external_message_id'),
                        'external_thread_id' => $request->input('external_thread_id'),
                        'external_status' => $request->input('external_message_id') ? 'sent' : 'accepted',
                        'media_id' => $request->input('media_id'),
                        'media_url' => $request->input('media_url'),
                        'media_mime_type' => $request->input('media_mime_type'),
                        'payload' => array_merge($request->input('metadata', []), [
                            'instance' => $request->input('instance'),
                            'apikey' => $request->input('apikey'),
                            'server_url' => $request->input('server_url'),
                        ]),
                    ]);
                }

                $conversacion->update([
                    'estado' => 'bot_activo',
                    'subagente_codigo' => $subagenteCodigo,
                    'ultima_actividad' => now(),
                    'mensajes_no_leidos' => 0,
                    'requiere_humano' => false,
                ]);

                return [
                    'conversacion' => $conversacion->fresh(),
                    'mensaje' => $mensaje,
                    'canal_mensaje' => $canalMensaje,
                    'pendientes_cerrados' => $pendientes->count(),
                ];
            });

            $whatsappDispatchOk = true;

            if (($resultado['conversacion']->canal_principal ?? null) === 'whatsapp') {
                [$instance, $apiKey, $serverUrl] = $this->resolveWhatsappCredentials($resultado['conversacion'], $request);

                if ($instance && $apiKey && $resultado['canal_mensaje']) {
                    $dispatch = $this->whatsAppOutboundService->sendText(
                        (string) ($resultado['conversacion']->contactoCanal?->canal_user_id ?? ''),
                        (string) $request->input('contenido'),
                        $instance,
                        $apiKey,
                        $serverUrl,
                        [
                            'tipo_contenido' => $request->input('tipo_contenido', 'texto'),
                            'media_url' => $request->input('media_url'),
                            'media_mime_type' => $request->input('media_mime_type'),
                        ]
                    );

                    $resultado['canal_mensaje']->update([
                        'external_status' => $dispatch['ok'] ? 'sent' : 'failed',
                        'external_message_id' => $dispatch['external_message_id'] ?? $resultado['canal_mensaje']->external_message_id,
                        'payload' => array_merge($resultado['canal_mensaje']->payload ?? [], [
                            'dispatch' => [
                                'ok' => $dispatch['ok'],
                                'status' => $dispatch['status'],
                                'error' => $dispatch['error'],
                                'response' => $dispatch['payload'],
                            ],
                        ]),
                    ]);

                    $whatsappDispatchOk = (bool) $dispatch['ok'];
                }
            }

            return response()->json([
                'success' => true,
                'message' => $whatsappDispatchOk
                    ? 'Respuesta del agente registrada correctamente.'
                    : 'Respuesta registrada, pero WhatsApp reporto fallo al enviar.',
                'data' => [
                    'idconv' => $resultado['conversacion']->idconv,
                    'idmsg' => $resultado['mensaje']->idmsg,
                    'chat_mensaje_canal_id' => $resultado['canal_mensaje']?->id,
                    'pendientes_cerrados' => $resultado['pendientes_cerrados'],
                    'estado_conversacion' => $resultado['conversacion']->estado,
                    'whatsapp_enviado' => $whatsappDispatchOk,
                ],
            ], $whatsappDispatchOk ? 201 : 207);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la respuesta del agente.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function derivarHumano(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idconv' => 'nullable|exists:conversaciones,idconv',
            'canal' => 'nullable|in:' . implode(',', self::CANALES),
            'canal_user_id' => 'nullable|string|max:120',
            'razon' => 'nullable|string',
            'subagente_codigo' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $conversacion = $this->resolveConversation($request);

            if (!$conversacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversación no encontrada.',
                ], 404);
            }

            $subagenteCodigo = $this->normalizeSubagentCode($request->input('subagente_codigo'))
                ?? self::SUBAGENTE_HANDOFF;

            $conversacion->update([
                'estado' => 'en_espera',
                'requiere_humano' => true,
                'subagente_codigo' => $subagenteCodigo,
                'ultima_actividad' => now(),
                'metadata' => array_merge($conversacion->metadata ?? [], [
                    'requiere_humano_desde' => now()->toIso8601String(),
                    'razon_handoff' => $request->input('razon'),
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Conversación derivada a humano.',
                'data' => [
                    'idconv' => $conversacion->idconv,
                    'estado' => $conversacion->estado,
                    'requiere_humano' => $conversacion->requiere_humano,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al derivar conversación a humano.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function guardarResumen(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idconv' => 'nullable|exists:conversaciones,idconv',
            'canal' => 'nullable|in:' . implode(',', self::CANALES),
            'canal_user_id' => 'nullable|string|max:120',
            'tipo' => 'nullable|in:conversacion,cliente,handoff,followup',
            'resumen' => 'required|string',
            'hechos_clave' => 'nullable|array',
            'subagente_codigo' => 'nullable|string|max:50',
            'ventana_desde' => 'nullable|date',
            'ventana_hasta' => 'nullable|date',
            'expira_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $conversacion = $this->resolveConversation($request);
            $subagente = $request->filled('subagente_codigo')
                ? ChatSubagente::query()->where('codigo', $request->input('subagente_codigo'))->first()
                : null;

            $resumen = ChatMemoriaResumen::create([
                'idconv' => $conversacion?->idconv,
                'contacto_canal_id' => $conversacion?->canal_contacto_id,
                'idcli' => $conversacion?->idcli,
                'subagente_id' => $subagente?->id,
                'tipo' => $request->input('tipo', 'conversacion'),
                'ventana_desde' => $request->input('ventana_desde', now()->subMinutes(30)),
                'ventana_hasta' => $request->input('ventana_hasta', now()),
                'resumen' => $request->input('resumen'),
                'hechos_clave' => $request->input('hechos_clave', []),
                'expira_at' => $request->input('expira_at'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Resumen guardado correctamente.',
                'data' => $resumen,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar resumen.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function guardarMemoriaContacto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idconv' => 'nullable|exists:conversaciones,idconv',
            'canal' => 'nullable|in:' . implode(',', self::CANALES),
            'canal_user_id' => 'nullable|string|max:120',
            'tipo' => 'required|in:perfil,preferencia,objecion,seguimiento,venta,pago,incidencia,contexto',
            'clave' => 'required|string|max:120',
            'valor_texto' => 'nullable|string',
            'valor_json' => 'nullable|array',
            'origen' => 'nullable|in:sistema,ai,empleado,cliente',
            'confianza' => 'nullable|numeric|min:0|max:100',
            'vigente_hasta' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $conversacion = $this->resolveConversation($request);

            if (!$conversacion || !$conversacion->canal_contacto_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo resolver el contacto para guardar memoria.',
                ], 422);
            }

            $memoria = ChatMemoriaContacto::query()->updateOrCreate(
                [
                    'contacto_canal_id' => $conversacion->canal_contacto_id,
                    'tipo' => $request->input('tipo'),
                    'clave' => $request->input('clave'),
                ],
                [
                    'idcli' => $conversacion->idcli,
                    'valor_texto' => $request->input('valor_texto'),
                    'valor_json' => $request->input('valor_json'),
                    'origen' => $request->input('origen', 'ai'),
                    'confianza' => $request->input('confianza'),
                    'vigente_hasta' => $request->input('vigente_hasta'),
                    'ultima_referencia_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Memoria de contacto guardada correctamente.',
                'data' => $memoria,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar memoria de contacto.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function resolveConversation(Request $request): ?Conversacion
    {
        if ($request->filled('idconv')) {
            return Conversacion::query()->with('contactoCanal')->find($request->input('idconv'));
        }

        if (!$request->filled('canal') || !$request->filled('canal_user_id')) {
            return null;
        }

        $contacto = ChatContactoCanal::query()
            ->where('canal', $request->input('canal'))
            ->where('canal_user_id', $request->input('canal_user_id'))
            ->first();

        if (!$contacto) {
            return null;
        }

        return Conversacion::query()
            ->with('contactoCanal')
            ->where('canal_contacto_id', $contacto->id)
            ->latest('ultima_actividad')
            ->first();
    }

    private function resolveCliente($idcli, ?string $telefono): ?Cliente
    {
        if ($idcli) {
            return Cliente::query()->find($idcli);
        }

        return Cliente::buscarPorTelefonoNormalizado($telefono);
    }

    private function resolveTriggerMessageId(Request $request, int $idconv): ?int
    {
        if ($request->filled('trigger_idmsg')) {
            return (int) $request->input('trigger_idmsg');
        }

        if (!$request->filled('external_message_id')) {
            return null;
        }

        return ChatMensajeCanal::query()
            ->where('idconv', $idconv)
            ->where('external_message_id', $request->input('external_message_id'))
            ->value('idmsg');
    }

    private function formatMessage(Mensaje $mensaje): array
    {
        return [
            'idmsg' => $mensaje->idmsg,
            'tipo_remitente' => $mensaje->tipo_remitente,
            'nombre_remitente' => $mensaje->nombre_remitente,
            'contenido' => $mensaje->contenido,
            'tipo_contenido' => $mensaje->tipo_contenido,
            'archivo_url' => $mensaje->archivo_url,
            'leido' => $mensaje->leido,
            'respondido_por_ai' => $mensaje->respondido_por_ai,
            'created_at' => optional($mensaje->created_at)->toIso8601String(),
            'metadata' => $mensaje->metadata,
        ];
    }

    private function buildAggregatedMessage($mensajes): string
    {
        return $mensajes
            ->map(fn (Mensaje $mensaje) => $this->messageToPromptLine($mensaje))
            ->filter()
            ->implode("\n");
    }

    private function messageToPromptLine(Mensaje $mensaje): ?string
    {
        $contenido = trim((string) $mensaje->contenido);

        if ($contenido !== '') {
            return $contenido;
        }

        return match ($mensaje->tipo_contenido) {
            'imagen' => '[imagen] ' . ($mensaje->archivo_url ?: 'sin_url'),
            'audio' => '[audio] ' . ($mensaje->archivo_url ?: 'sin_url'),
            'archivo' => '[archivo] ' . ($mensaje->archivo_url ?: 'sin_url'),
            'video' => '[video] ' . ($mensaje->archivo_url ?: 'sin_url'),
            'documento' => '[documento] ' . ($mensaje->archivo_url ?: 'sin_url'),
            default => null,
        };
    }

    private function buildChannelMetadata(Request $request): array
    {
        $whatsappChannel = $this->resolveWhatsappChannel($request->input('instance'));

        return array_filter([
            'instance' => $request->input('instance') ?: $whatsappChannel?->instance_name,
            'apikey' => $request->input('apikey') ?: $whatsappChannel?->api_key,
            'server_url' => $request->input('server_url') ?: $whatsappChannel?->server_url,
            'whatsapp_channel_id' => $whatsappChannel?->id,
            'whatsapp_color' => $whatsappChannel?->color ?: $this->resolveWhatsappColorByInstance($request->input('instance')),
            'nombre' => $request->input('nombre'),
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function normalizeContentType(?string $tipo): string
    {
        return match ($tipo) {
            'sticker' => 'imagen',
            'texto', 'imagen', 'archivo', 'audio', 'video', 'documento' => $tipo,
            default => 'texto',
        };
    }

    private function normalizeSubagentCode(?string $codigo): ?string
    {
        if (!$codigo) {
            return null;
        }

        $codigo = trim($codigo);

        return self::SUBAGENTE_ALIAS[$codigo] ?? $codigo;
    }

    private function normalizePhone(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits !== '' ? $digits : null;
    }

    private function resolveWhatsappCredentials(Conversacion $conversacion, Request $request): array
    {
        $metadata = $conversacion->metadata ?? [];
        $contactMetadata = $conversacion->contactoCanal?->metadata ?? [];
        $channelConfigId = data_get($metadata, 'whatsapp_channel_id') ?: data_get($contactMetadata, 'whatsapp_channel_id');

        $instance = $request->input('instance')
            ?? data_get($metadata, 'instance')
            ?? data_get($contactMetadata, 'instance');

        $whatsappChannel = $channelConfigId
            ? \App\Models\ChatWhatsappChannel::query()->availableForOutbound()->find($channelConfigId)
            : $this->resolveWhatsappChannel($instance);

        if (!$instance) {
            $instance = $whatsappChannel?->instance_name;
        }

        $apiKey = $request->input('apikey')
            ?? data_get($metadata, 'apikey')
            ?? data_get($contactMetadata, 'apikey')
            ?? $whatsappChannel?->api_key;

        $serverUrl = $request->input('server_url')
            ?? data_get($metadata, 'server_url')
            ?? data_get($contactMetadata, 'server_url')
            ?? $whatsappChannel?->server_url;

        return [$instance, $apiKey, $serverUrl];
    }

    private function resolveWhatsappChannel(?string $instance): ?\App\Models\ChatWhatsappChannel
    {
        return $this->whatsAppOutboundService->resolveChannelByInstance($instance);
    }

    private function resolveWhatsappColorByInstance(?string $instance): ?string
    {
        if (!$instance) {
            return null;
        }

        return strtolower(trim((string) $instance)) === 'bot-pagos' ? 'verde' : 'azul';
    }
}
