<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\ChatContactoCanal;
use App\Models\ChatMemoriaContacto;
use App\Models\ChatMemoriaNegocio;
use App\Models\ChatMemoriaResumen;
use App\Models\ChatMensajeCanal;
use App\Models\ChatSubagente;
use App\Models\ChatWhatsappChannel;
use App\Models\Cliente;
use App\Models\Conversacion;
use App\Models\Mensaje;
use App\Models\MensajeReaccion;
use App\Models\Proveedor;
use App\Services\Chat\WhatsAppOutboundService;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
        $payloadInput = $request->input('payload');
        $payloadData = is_array($payloadInput) ? $payloadInput : [];

        $request->merge([
            'instance' => $request->input('instance') ?: $request->input('instance_name'),
            'apikey' => $request->input('apikey') ?: $request->input('instance_apikey'),
            'numero' => $request->input('numero') ?: $request->input('numero_persona'),
            'tipo_contenido' => $request->input('tipo_contenido')
                ?: data_get($payloadData, 'message.type')
                ?: data_get($payloadData, 'type'),
            'media_url' => $request->input('media_url') ?: $this->resolveInboundMediaUrlFromPayload($payloadData),
            'media_mime_type' => $request->input('media_mime_type')
                ?: $request->input('mime_type')
                ?: $this->resolveInboundMediaMimeTypeFromPayload($payloadData),
            'mensaje' => $request->input('mensaje')
                ?: $request->input('contenido')
                ?: $this->resolveInboundCaptionFromPayload($payloadData),
            'from_me' => $request->input('from_me')
                ?? $request->input('fromMe')
                ?? data_get($payloadData, 'message.fromMe')
                ?? data_get($payloadData, 'fromMe')
                ?? false,
        ]);

        $isFromMe = filter_var($request->input('from_me'), FILTER_VALIDATE_BOOLEAN);

        if ($isFromMe) {
            return response()->json([
                'success' => false,
                'message' => 'Mensaje fromMe detectado. Usa /api/v2/chat/router/save-respond para registrar outbound.',
            ], 422);
        }

        // Una reaccion con emoji no es un mensaje nuevo -- solo anota uno existente.
        // Se maneja aparte, antes de la validacion que exige contenido/media.
        if ($request->filled('reaction_to_external_id')) {
            return $this->handleReaction($request);
        }

        $validator = Validator::make($request->all(), [
            'canal' => 'required|in:' . implode(',', self::CANALES),
            'canal_user_id' => 'required|string|max:120',
            'mensaje' => 'nullable|string',
            'contenido' => 'nullable|string',
            'tipo_contenido' => 'nullable|in:texto,imagen,archivo,audio,video,documento,sticker',
            'external_message_id' => 'nullable|string|max:191',
            'external_thread_id' => 'nullable|string|max:191',
            'reply_to_external_id' => 'nullable|string|max:191',
            'telefono' => 'nullable|string|max:40',
            'numero' => 'nullable|string|max:40',
            'nombre' => 'nullable|string|max:120',
            'idcli' => 'nullable|exists:clientes,idcli',
            'origen' => 'nullable|string|max:50',
            'payload' => 'nullable|array',
            'media_url' => 'nullable|string',
            'media_mime_type' => 'nullable|string|max:120',
            'media_base64' => 'nullable|string',
            'media_file_name' => 'nullable|string|max:191',
            'media_id' => 'nullable|string|max:191',
            'mensaje_original' => 'nullable|string',
            'texto_extraido' => 'nullable|string',
            'media_kind' => 'nullable|string|max:40',
            'media_transcription' => 'nullable|string',
            'media_caption' => 'nullable|string',
            'media_analysis_json' => 'nullable',
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
        $tipoContenido = $this->normalizeContentType($request->input('tipo_contenido', 'texto'));
        $mensajeOriginal = trim((string) $request->input('mensaje_original', ''));
        $textoExtraido = trim((string) $request->input('texto_extraido',
            $request->input('media_transcription', '')));

        if ($contenido !== '') {
            $textoAgente = $contenido;
        } elseif ($tipoContenido === 'audio') {
            $textoAgente = "<audio>\nTranscripción: {$textoExtraido}\n</audio>";
        } elseif ($tipoContenido === 'imagen') {
            $parts = ['<imagen>'];
            if ($mensajeOriginal !== '') {
                $parts[] = "Texto del usuario: {$mensajeOriginal}";
            }
            if ($textoExtraido !== '') {
                $parts[] = "Descripción de la imagen: {$textoExtraido}";
            }
            $parts[] = '</imagen>';
            $textoAgente = implode("\n", $parts);
        } else {
            $textoAgente = $mensajeOriginal ?: $textoExtraido ?: $contenido;
        }

        if ($contenido === '' && $textoExtraido === '' && !$request->filled('media_url') && !$request->filled('media_base64')) {
            return response()->json([
                'success' => false,
                'message' => 'Debes enviar mensaje, texto_extraido, media_url o media_base64.',
            ], 422);
        }

        try {
            $resultado = DB::transaction(function () use ($request, $contenido, $tipoContenido, $mensajeOriginal, $textoExtraido, $textoAgente) {
                $canal = $request->string('canal')->toString();
                $canalUserId = trim((string) $request->input('canal_user_id'));
                $telefono = $this->normalizePhone($request->input('telefono') ?? $request->input('numero') ?? $canalUserId);
                $cliente = $this->resolveCliente($request->input('idcli'), $telefono);

                if ($canal === 'whatsapp') {
                    $this->upsertWhatsappChannelFromRequest($request);
                }

                $whatsappChannel = $this->resolveWhatsappChannel($request->input('instance'));
                $whatsappColor = $whatsappChannel?->color ?? $this->resolveWhatsappColorByInstance($request->input('instance'));

                $subagenteCodigo = $this->normalizeSubagentCode($request->input('subagente_codigo'))
                    ?? self::SUBAGENTE_POR_DEFECTO;

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
                        'metadata' => array_merge($this->buildChannelMetadata($request), $this->proveedorMetadataTag($telefono)),
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

                $conversacion = Conversacion::query()->firstOrCreate(
                    ['canal_contacto_id' => $contacto->id],
                    [
                        'idcli' => $cliente?->idcli,
                        'canal_principal' => $canal,
                        'origen' => $request->input('origen', 'n8n'),
                        'subagente_codigo' => $subagenteCodigo,
                        'estado' => 'abierta',
                        'ultima_actividad' => now(),
                        'last_message_at' => now(),
                        'mensajes_no_leidos' => 0,
                        'requiere_humano' => false,
                        'metadata' => array_merge([
                            'canal_user_id' => $canalUserId,
                            'instance' => $request->input('instance') ?: $whatsappChannel?->instance_name,
                            'apikey' => $request->input('apikey') ?: $whatsappChannel?->api_key,
                            'server_url' => $request->input('server_url') ?: $whatsappChannel?->server_url,
                            'whatsapp_channel_id' => $whatsappChannel?->id,
                            'whatsapp_color' => $whatsappColor,
                        ], $this->proveedorMetadataTag($telefono)),
                    ]
                );

                $conversacion->fill([
                    'idcli' => $cliente?->idcli ?? $conversacion->idcli,
                    'subagente_codigo' => $subagenteCodigo ?: $conversacion->subagente_codigo,
                    'ultima_actividad' => now(),
                    'last_message_at' => now(),
                    'metadata' => array_merge($conversacion->metadata ?? [], [
                        'canal_user_id' => $canalUserId,
                        'instance' => $request->input('instance', data_get($conversacion->metadata, 'instance') ?: $whatsappChannel?->instance_name),
                        'apikey' => $request->input('apikey', data_get($conversacion->metadata, 'apikey') ?: $whatsappChannel?->api_key),
                        'server_url' => $request->input('server_url', data_get($conversacion->metadata, 'server_url') ?: $whatsappChannel?->server_url),
                        'whatsapp_channel_id' => data_get($conversacion->metadata, 'whatsapp_channel_id') ?: $whatsappChannel?->id,
                        'whatsapp_color' => data_get($conversacion->metadata, 'whatsapp_color') ?: $whatsappColor,
                    ]),
                ]);

                if (in_array($conversacion->estado, ['cerrado', 'cerrada', 'resuelto'], true)) {
                    $conversacion->estado = 'abierta';
                    $conversacion->closed_at = null;
                }

                $conversacion->save();

                [$storedMediaUrl, $resolvedMediaMimeType] = $this->persistInboundMedia(
                    $request->input('media_url'),
                    $tipoContenido,
                    $request->input('media_mime_type'),
                    $request->input('media_base64'),
                    $request->input('media_file_name')
                );

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

                // Si el cliente respondio a otro mensaje (cita estilo WhatsApp), resolvemos
                // a que idmsg local corresponde ese stanzaId. Puede ser tanto un mensaje
                // entrante anterior como uno saliente (external_id ya se guarda en ambos casos).
                $replyToIdmsg = $request->filled('reply_to_external_id')
                    ? Mensaje::query()->where('external_id', $request->input('reply_to_external_id'))->value('idmsg')
                    : null;

                $mensaje = Mensaje::create([
                    'idconv' => $conversacion->idconv,
                    'tipo_remitente' => 'cliente',
                    'idcli' => $cliente?->idcli,
                    'contenido' => $textoAgente ?: $contenido,
                    'mensaje_original' => $mensajeOriginal ?: null,
                    'texto_extraido' => $textoExtraido ?: null,
                    'texto_agente' => $textoAgente ?: null,
                    'tipo_contenido' => $tipoContenido,
                    'media_kind' => $request->input('media_kind'),
                    'archivo_url' => $storedMediaUrl,
                    'media_url' => $storedMediaUrl,
                    'mime_type' => $resolvedMediaMimeType,
                    'media_file_name' => $request->input('media_file_name'),
                    'external_id' => $request->input('external_message_id'),
                    'reply_to_idmsg' => $replyToIdmsg,
                    'media_transcription' => $request->input('media_transcription') ?: ($textoExtraido ?: null),
                    'media_caption' => $request->input('media_caption') ?: ($mensajeOriginal ?: null),
                    'media_analysis_json' => $request->input('media_analysis_json'),
                    'leido' => false,
                    'respondido_por_ai' => false,
                    'metadata' => [
                        'origen' => 'router',
                        'tipo_contenido_original' => $request->input('tipo_contenido', 'texto'),
                        'external_message_id' => $request->input('external_message_id'),
                        'instance' => $request->input('instance'),
                        'media_original_url' => $request->input('media_url'),
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
                    'media_url' => $storedMediaUrl,
                    'media_mime_type' => $resolvedMediaMimeType,
                    'payload' => array_merge($request->input('payload', []), [
                        'instance' => $request->input('instance') ?: $whatsappChannel?->instance_name,
                        'apikey' => $request->input('apikey') ?: $whatsappChannel?->api_key,
                        'server_url' => $request->input('server_url') ?: $whatsappChannel?->server_url,
                        'whatsapp_channel_id' => $whatsappChannel?->id,
                        'whatsapp_color' => $whatsappColor,
                        'media_original_url' => $request->input('media_url'),
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

    /**
     * Procesa una reaccion con emoji a un mensaje ya existente (no crea mensaje nuevo).
     * $request trae reaction_to_external_id (external_id del mensaje reaccionado),
     * reaction_emoji ("" significa que se quito la reaccion) y from_me.
     */
    private function handleReaction(Request $request)
    {
        $externalId = trim((string) $request->input('reaction_to_external_id'));
        $emoji = (string) $request->input('reaction_emoji', '');
        $fromMe = filter_var($request->input('from_me'), FILTER_VALIDATE_BOOLEAN);

        $mensaje = Mensaje::query()->where('external_id', $externalId)->first();

        if (! $mensaje) {
            return response()->json([
                'success' => false,
                'message' => 'Mensaje reaccionado no encontrado localmente.',
            ], 404);
        }

        $autorTipo = $fromMe ? 'empleado' : 'cliente';

        if ($emoji === '') {
            MensajeReaccion::query()
                ->where('idmsg', $mensaje->idmsg)
                ->where('autor_tipo', $autorTipo)
                ->delete();
        } else {
            MensajeReaccion::query()->updateOrCreate(
                ['idmsg' => $mensaje->idmsg, 'autor_tipo' => $autorTipo],
                ['emoji' => $emoji]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Reacción procesada.',
            'data' => ['idmsg' => $mensaje->idmsg],
        ]);
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

            $now = now();
            $memoriaNegocio = ChatMemoriaNegocio::query()
                ->where('activo', true)
                ->whereIn('visibilidad', ['cliente', 'ambas'])
                ->where(function ($q) use ($now) {
                    // sin fecha de inicio, o fecha de inicio ya pasó
                    $q->whereNull('inicio_at')->orWhere('inicio_at', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    // sin fecha de fin, o fecha de fin aún no llega
                    $q->whereNull('fin_at')->orWhere('fin_at', '>=', $now);
                })
                ->orderBy('categoria')
                ->orderBy('prioridad')
                ->limit(40)
                ->get(['id', 'tipo', 'categoria', 'clave', 'titulo', 'resumen', 'contenido', 'tags']);

            // Imágenes que el agente ya envió en esta conversación (últimos 7 días)
            // Permite al agente saber qué NO repetir.
            $imagenesAgente = Mensaje::where('idconv', $conversacion->idconv)
                ->where('tipo_remitente', 'ia')
                ->where('tipo_contenido', 'imagen')
                ->where('created_at', '>=', now()->subDays(7))
                ->orderByDesc('created_at')
                ->get(['idmsg', 'metadata', 'created_at'])
                ->map(fn ($m) => [
                    'imagen_id'    => data_get($m->metadata, 'imagen_agente_id'),
                    'imagen_nombre'=> data_get($m->metadata, 'imagen_agente_nombre'),
                    'enviada_at'   => optional($m->created_at)->toIso8601String(),
                    'hace_dias'    => (int) now()->diffInDays($m->created_at),
                ])
                ->filter(fn ($r) => $r['imagen_id'] !== null)
                ->values();

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
                    'imagenes_agente_recientes' => $imagenesAgente,
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
        $request->merge([
            'instance' => $request->input('instance') ?: $request->input('instance_name'),
            'apikey' => $request->input('apikey') ?: $request->input('instance_apikey'),
            'canal' => $request->input('canal') ?: 'whatsapp',
            'canal_user_id' => $request->input('canal_user_id')
                ?: $request->input('numero')
                ?: $this->resolveCanalUserIdFromChatId($request->input('external_thread_id'))
                ?: $this->resolveCanalUserIdFromChatId($request->input('chat_id')),
        ]);

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
            'dispatch_async' => 'nullable|boolean',
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
                    $canal = (string) $request->input('canal', 'whatsapp');
                    $canalUserId = trim((string) $request->input('canal_user_id'));

                    if ($canalUserId === '') {
                        throw new \RuntimeException('Conversación no encontrada.');
                    }

                    $telefono = $this->normalizePhone($request->input('numero') ?? $canalUserId);
                    $cliente = $this->resolveCliente($request->input('idcli'), $telefono);

                    if ($canal === 'whatsapp') {
                        $this->upsertWhatsappChannelFromRequest($request);
                    }

                    $contacto = ChatContactoCanal::query()->firstOrCreate(
                        [
                            'canal' => $canal,
                            'canal_user_id' => $canalUserId,
                        ],
                        [
                            'telefono_normalizado' => $telefono,
                            'idcli' => $cliente?->idcli,
                            'estado_relacion' => $cliente ? 'cliente' : 'lead',
                            'origen' => $request->input('origen', 'n8n'),
                            'metadata' => array_merge($this->buildChannelMetadata($request), $this->proveedorMetadataTag($telefono)),
                            'last_seen_at' => now(),
                        ]
                    );

                    $contacto->fill([
                        'telefono_normalizado' => $telefono ?: $contacto->telefono_normalizado,
                        'idcli' => $cliente?->idcli ?? $contacto->idcli,
                        'estado_relacion' => $cliente ? 'cliente' : $contacto->estado_relacion,
                        'origen' => $request->input('origen', $contacto->origen),
                        'metadata' => array_merge($contacto->metadata ?? [], $this->buildChannelMetadata($request)),
                        'last_seen_at' => now(),
                    ]);
                    $contacto->save();

                    $conversacion = Conversacion::query()->firstOrCreate(
                        ['canal_contacto_id' => $contacto->id],
                        [
                            'idcli' => $cliente?->idcli,
                            'canal_principal' => $canal,
                            'origen' => $request->input('origen', 'n8n'),
                            'estado' => 'abierta',
                            'ultima_actividad' => now(),
                            'last_message_at' => now(),
                            'mensajes_no_leidos' => 0,
                            'requiere_humano' => false,
                            'metadata' => $this->proveedorMetadataTag($telefono),
                        ]
                    );

                    $conversacion->load('contactoCanal');
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

            $isWhatsappConversation = (($resultado['conversacion']->canal_principal ?? null) === 'whatsapp');
            $whatsappDispatchQueued = false;
            $whatsappDispatchSkipReason = null;
            $whatsappDispatchOk = !$isWhatsappConversation;
            $whatsappDispatchResult = null;

            if ($isWhatsappConversation) {
                [$instance, $apiKey, $serverUrl] = $this->resolveWhatsappCredentials($resultado['conversacion'], $request);
                $dispatchAsync = filter_var($request->input('dispatch_async', false), FILTER_VALIDATE_BOOLEAN);

                if ($resultado['canal_mensaje']) {
                    if ($dispatchAsync) {
                        $resultado['canal_mensaje']->update([
                            'external_status' => 'accepted',
                            'payload' => array_merge($resultado['canal_mensaje']->payload ?? [], [
                                'dispatch' => [
                                    'queued_at' => now()->toIso8601String(),
                                ],
                            ]),
                        ]);

                        $this->scheduleWhatsappDispatch(
                            (int) $resultado['conversacion']->idconv,
                            (int) $resultado['canal_mensaje']->id,
                            (string) $request->input('contenido'),
                            [
                                'tipo_contenido' => $request->input('tipo_contenido', 'texto'),
                                'media_url' => $request->input('media_url'),
                                'media_mime_type' => $request->input('media_mime_type'),
                            ],
                            $instance,
                            $apiKey,
                            $serverUrl
                        );

                        $whatsappDispatchQueued = true;
                        $whatsappDispatchOk = true;
                    } else {
                        $whatsappDispatchResult = $this->performWhatsappDispatch(
                            (int) $resultado['conversacion']->idconv,
                            (int) $resultado['canal_mensaje']->id,
                            (string) $request->input('contenido'),
                            [
                                'tipo_contenido' => $request->input('tipo_contenido', 'texto'),
                                'media_url' => $request->input('media_url'),
                                'media_mime_type' => $request->input('media_mime_type'),
                            ],
                            $instance,
                            $apiKey,
                            $serverUrl
                        );

                        $whatsappDispatchOk = (bool) ($whatsappDispatchResult['ok'] ?? false);

                        if (!$whatsappDispatchOk) {
                            $whatsappDispatchSkipReason = $whatsappDispatchResult['error'] ?? 'envio_whatsapp_fallido';
                        }
                    }
                } else {
                    $whatsappDispatchSkipReason = 'canal_mensaje_no_registrado';
                    $whatsappDispatchOk = false;
                }
            }

            return response()->json([
                'success' => true,
                'message' => $whatsappDispatchQueued
                    ? 'Respuesta del agente registrada y envio WhatsApp programado.'
                    : ($whatsappDispatchOk
                        ? 'Respuesta del agente registrada y enviada por WhatsApp.'
                        : 'Respuesta del agente registrada, pero WhatsApp no pudo enviarse.'),
                'data' => [
                    'idconv' => $resultado['conversacion']->idconv,
                    'idmsg' => $resultado['mensaje']->idmsg,
                    'chat_mensaje_canal_id' => $resultado['canal_mensaje']?->id,
                    'pendientes_cerrados' => $resultado['pendientes_cerrados'],
                    'estado_conversacion' => $resultado['conversacion']->estado,
                    'whatsapp_enviado' => $whatsappDispatchOk,
                    'whatsapp_dispatch_programado' => $whatsappDispatchQueued,
                    'whatsapp_dispatch_omitido_motivo' => $whatsappDispatchSkipReason,
                    'whatsapp_dispatch_error' => $whatsappDispatchResult['error'] ?? null,
                ],
            ], $whatsappDispatchOk || $whatsappDispatchQueued ? 201 : 207);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la respuesta del agente.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function saveRespond(Request $request)
    {
        $payloadInput = $request->input('payload');
        $payloadData = is_array($payloadInput) ? $payloadInput : [];

        $rawFromMe = $request->input('from_me')
            ?? $request->input('fromMe')
            ?? data_get($payloadData, 'message.fromMe')
            ?? data_get($payloadData, 'fromMe')
            ?? true;

        $normalizedFromMe = filter_var($rawFromMe, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($normalizedFromMe === null) {
            $normalizedFromMe = true;
        }

        $rawTipoRemitenteInput = $request->input('tipo_remitente');
        $hasExplicitTipoRemitente = is_string($rawTipoRemitenteInput) && trim($rawTipoRemitenteInput) !== '';

        $looksLikeAiMessage = (bool) (
            $request->filled('subagente_codigo')
            || data_get($payloadData, 'subagente_codigo')
            || data_get($request->input('metadata', []), 'subagente_codigo')
            || data_get($request->input('metadata', []), 'origen') === 'ai'
            || data_get($payloadData, 'metadata.subagente_codigo')
            || data_get($payloadData, 'metadata.origen') === 'ai'
        );

        $defaultTipoRemitente = ($normalizedFromMe === true && $looksLikeAiMessage) ? 'ia' : 'empleado';

        $rawTipoRemitente = strtolower(trim((string) ($hasExplicitTipoRemitente ? $rawTipoRemitenteInput : $defaultTipoRemitente)));
        $normalizedTipoRemitente = in_array($rawTipoRemitente, ['empleado', 'ia', 'sistema'], true)
            ? $rawTipoRemitente
            : 'empleado';

        $request->merge([
            'instance' => $request->input('instance') ?: $request->input('instance_name'),
            'apikey' => $request->input('apikey') ?: $request->input('instance_apikey'),
            'canal' => $request->input('canal') ?: 'whatsapp',
            'canal_user_id' => $request->input('canal_user_id')
                ?: $request->input('numero')
                ?: data_get($payloadData, 'contact.numero')
                ?: data_get($payloadData, 'message.from')
                ?: $this->resolveCanalUserIdFromChatId($request->input('external_thread_id'))
                ?: $this->resolveCanalUserIdFromChatId($request->input('chat_id'))
                ?: $this->resolveCanalUserIdFromChatId(data_get($payloadData, 'message.chat_id'))
                ?: $this->resolveCanalUserIdFromChatId(data_get($payloadData, 'chat_id')),
            'contenido' => $request->input('contenido')
                ?: $request->input('mensaje')
                ?: data_get($payloadData, 'message.caption')
                ?: data_get($payloadData, 'content')
                ?: data_get($payloadData, 'message.text')
                ?: '',
            'tipo_contenido' => $request->input('tipo_contenido')
                ?: data_get($payloadData, 'message.type')
                ?: 'texto',
            'media_mime_type' => $request->input('media_mime_type')
                ?: $request->input('mime_type')
                ?: data_get($payloadData, 'mimetype')
                ?: data_get($payloadData, 'message.mimetype')
                ?: data_get($payloadData, 'message.mime_type'),
            'external_message_id' => $request->input('external_message_id') ?: data_get($payloadData, 'message.id'),
            'external_thread_id' => $request->input('external_thread_id')
                ?: data_get($payloadData, 'message.chat_id')
                ?: data_get($payloadData, 'chat_id'),
            'numero' => $request->input('numero')
                ?: data_get($payloadData, 'contact.numero')
                ?: data_get($payloadData, 'message.from')
                ?: $this->resolveCanalUserIdFromChatId($request->input('external_thread_id'))
                ?: $this->resolveCanalUserIdFromChatId($request->input('chat_id')),
            'from_me' => $normalizedFromMe,
            'tipo_remitente' => $normalizedTipoRemitente,
        ]);

        $validator = Validator::make($request->all(), [
            'idconv' => 'nullable|exists:conversaciones,idconv',
            'canal' => 'nullable|in:' . implode(',', self::CANALES),
            'canal_user_id' => 'nullable|string|max:120',
            'mensaje' => 'nullable|string',
            'contenido' => 'nullable|string',
            'tipo_contenido' => 'nullable|in:texto,imagen,archivo,audio,video,documento,sticker',
            'media_url' => 'nullable|string',
            'media_base64' => 'nullable|string',
            'media_file_name' => 'nullable|string|max:191',
            'media_mime_type' => 'nullable|string|max:120',
            'media_id' => 'nullable|string|max:191',
            'external_message_id' => 'nullable|string|max:191',
            'external_thread_id' => 'nullable|string|max:191',
            'metadata' => 'nullable|array',
            'tipo_remitente' => 'nullable|in:empleado,ia,sistema',
            'idemp' => 'nullable|integer|exists:empleados,idemp',
            'instance' => 'nullable|string|max:120',
            'apikey' => 'nullable|string|max:191',
            'server_url' => 'nullable|string|max:191',
            'from_me' => 'nullable|boolean',
            'enviar_whatsapp' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $contenido = trim((string) ($request->input('contenido') ?? ''));

        if ($contenido === '' && !$request->filled('media_url') && !$request->filled('media_base64')) {
            return response()->json([
                'success' => false,
                'message' => 'Debes enviar contenido, media_url o media_base64.',
            ], 422);
        }

        try {
            $resultado = DB::transaction(function () use ($request, $contenido) {
                $conversacion = $this->resolveConversation($request);

                if (!$conversacion) {
                    $canal = $request->input('canal', 'whatsapp');
                    $canalUserId = trim((string) $request->input('canal_user_id'));

                    if ($canalUserId === '') {
                        throw new \RuntimeException('Conversación no encontrada y canal_user_id vacío.');
                    }

                    $telefono = $this->normalizePhone($request->input('numero') ?? $canalUserId);
                    $cliente = $this->resolveCliente($request->input('idcli'), $telefono);

                    if ($canal === 'whatsapp') {
                        $this->upsertWhatsappChannelFromRequest($request);
                    }

                    $contacto = ChatContactoCanal::query()->firstOrCreate(
                        [
                            'canal' => $canal,
                            'canal_user_id' => $canalUserId,
                        ],
                        [
                            'telefono_normalizado' => $telefono,
                            'idcli' => $cliente?->idcli,
                            'estado_relacion' => $cliente ? 'cliente' : 'lead',
                            'origen' => $request->input('origen', 'n8n'),
                            'metadata' => array_merge($this->buildChannelMetadata($request), $this->proveedorMetadataTag($telefono)),
                            'last_seen_at' => now(),
                        ]
                    );

                    $conversacion = Conversacion::query()->firstOrCreate(
                        ['canal_contacto_id' => $contacto->id],
                        [
                            'idcli' => $cliente?->idcli,
                            'canal_principal' => $canal,
                            'origen' => $request->input('origen', 'n8n'),
                            'estado' => 'abierta',
                            'ultima_actividad' => now(),
                            'last_message_at' => now(),
                            'mensajes_no_leidos' => 0,
                            'requiere_humano' => false,
                            'metadata' => $this->proveedorMetadataTag($telefono),
                        ]
                    );
                    $conversacion->load('contactoCanal');
                }

                $tipoContenido = $this->normalizeContentType($request->input('tipo_contenido', 'texto'));

                [$storedMediaUrl, $resolvedMediaMimeType] = $this->persistInboundMedia(
                    $request->input('media_url'),
                    $tipoContenido,
                    $request->input('media_mime_type'),
                    $request->input('media_base64'),
                    $request->input('media_file_name')
                );

                if ($request->filled('external_message_id')) {
                    $chatMensajeExistente = ChatMensajeCanal::query()
                        ->where('canal', $conversacion->canal_principal)
                        ->where('direccion', 'outbound')
                        ->where('external_message_id', $request->input('external_message_id'))
                        ->first();

                    if ($chatMensajeExistente) {
                        return [
                            'conversacion' => $conversacion,
                            'mensaje' => $chatMensajeExistente->mensaje,
                            'canal_mensaje' => $chatMensajeExistente,
                            'duplicado' => true,
                        ];
                    }
                }

                $tipoRemitente = $request->input('tipo_remitente', 'empleado');

                $mensaje = Mensaje::create([
                    'idconv' => $conversacion->idconv,
                    'tipo_remitente' => $tipoRemitente,
                    'idemp' => $request->input('idemp'),
                    'contenido' => $contenido,
                    'tipo_contenido' => $tipoContenido,
                    'archivo_url' => $storedMediaUrl,
                    'media_url' => $storedMediaUrl,
                    'mime_type' => $resolvedMediaMimeType,
                    'leido' => true,
                    'respondido_por_ai' => $tipoRemitente === 'ia',
                    'metadata' => array_merge($request->input('metadata', []), [
                        'source' => 'save-respond',
                        'from_me' => filter_var($request->input('from_me', true), FILTER_VALIDATE_BOOLEAN),
                        'tipo_contenido_original' => $request->input('tipo_contenido', 'texto'),
                        'instance' => $request->input('instance'),
                        'media_original_url' => $request->input('media_url'),
                        'media_file_name' => $request->input('media_file_name'),
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
                        'media_url' => $storedMediaUrl,
                        'media_mime_type' => $resolvedMediaMimeType,
                        'payload' => array_merge($request->input('metadata', []), [
                            'instance' => $request->input('instance'),
                            'apikey' => $request->input('apikey'),
                            'server_url' => $request->input('server_url'),
                            'from_me' => filter_var($request->input('from_me', true), FILTER_VALIDATE_BOOLEAN),
                        ]),
                    ]);
                }

                $conversacion->update([
                    'estado' => $tipoRemitente === 'ia' ? 'bot_activo' : 'atendiendo',
                    'ultima_actividad' => now(),
                    'last_message_at' => now(),
                ]);

                return [
                    'conversacion' => $conversacion->fresh(),
                    'mensaje' => $mensaje,
                    'canal_mensaje' => $canalMensaje,
                    'duplicado' => false,
                ];
            });

            $isWhatsappConversation = (($resultado['conversacion']->canal_principal ?? null) === 'whatsapp');
            $whatsappDispatchQueued = false;
            $whatsappDispatchSkipReason = null;

            if ($isWhatsappConversation) {
                $debeEnviarWhatsapp = filter_var($request->input('enviar_whatsapp', true), FILTER_VALIDATE_BOOLEAN);
                $tieneExternalMessageId = $request->filled('external_message_id');

                if ($resultado['duplicado']) {
                    $whatsappDispatchSkipReason = 'mensaje_duplicado';
                } elseif (!$resultado['canal_mensaje']) {
                    $whatsappDispatchSkipReason = 'canal_mensaje_no_registrado';
                } elseif (!$debeEnviarWhatsapp) {
                    $whatsappDispatchSkipReason = 'envio_whatsapp_deshabilitado';
                } elseif ($tieneExternalMessageId) {
                    $whatsappDispatchSkipReason = 'external_message_id_presente';
                } else {
                    [$instance, $apiKey, $serverUrl] = $this->resolveWhatsappCredentials($resultado['conversacion'], $request);

                    $resultado['canal_mensaje']->update([
                        'external_status' => 'accepted',
                        'payload' => array_merge($resultado['canal_mensaje']->payload ?? [], [
                            'dispatch' => [
                                'queued_at' => now()->toIso8601String(),
                            ],
                        ]),
                    ]);

                    $this->scheduleWhatsappDispatch(
                        (int) $resultado['conversacion']->idconv,
                        (int) $resultado['canal_mensaje']->id,
                        (string) $resultado['mensaje']->contenido,
                        [
                            'tipo_contenido' => $resultado['mensaje']->tipo_contenido,
                            'media_url' => $resultado['mensaje']->media_url,
                            'media_mime_type' => $resultado['mensaje']->mime_type,
                        ],
                        $instance,
                        $apiKey,
                        $serverUrl
                    );

                    $whatsappDispatchQueued = true;
                }
            }

            return response()->json([
                'success' => true,
                'message' => $resultado['duplicado']
                    ? 'Mensaje outbound ya estaba registrado (fromMe).'
                    : ($whatsappDispatchQueued
                        ? 'Mensaje outbound guardado y envio WhatsApp programado.'
                        : 'Mensaje outbound guardado correctamente (fromMe).'),
                'data' => [
                    'idconv' => $resultado['conversacion']->idconv,
                    'idmsg' => $resultado['mensaje']->idmsg,
                    'chat_mensaje_canal_id' => $resultado['canal_mensaje']?->id,
                    'estado_conversacion' => $resultado['conversacion']->estado,
                    'duplicado' => $resultado['duplicado'],
                    'whatsapp_dispatch_programado' => $whatsappDispatchQueued,
                    'whatsapp_dispatch_omitido_motivo' => $whatsappDispatchSkipReason,
                ],
            ], $resultado['duplicado'] ? 200 : 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar mensaje outbound (fromMe).',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function derivarHumano(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idconv' => 'nullable|exists:conversaciones,idconv',
            'idcli' => 'nullable|string|exists:clientes,idcli',
            'telefono' => 'nullable|string|max:40',
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

        if ($request->filled('idcli')) {
            return Conversacion::query()
                ->with('contactoCanal')
                ->where('idcli', $request->input('idcli'))
                ->latest('ultima_actividad')
                ->first();
        }

        $telefonoFallback = $this->normalizePhone((string) ($request->input('telefono') ?? ''));

        if ($telefonoFallback !== '' && !$request->filled('canal')) {
            $contactoPorTelefono = ChatContactoCanal::query()
                ->where('telefono_normalizado', $telefonoFallback)
                ->latest('last_seen_at')
                ->first();

            if ($contactoPorTelefono) {
                return Conversacion::query()
                    ->with('contactoCanal')
                    ->where('canal_contacto_id', $contactoPorTelefono->id)
                    ->latest('ultima_actividad')
                    ->first();
            }
        }

        if (!$request->filled('canal')) {
            return null;
        }

        $canal = (string) $request->input('canal');
        $rawCanalUserId = trim((string) $request->input('canal_user_id'));
        $threadFromExternal = trim((string) $request->input('external_thread_id'));
        $threadFromChat = trim((string) $request->input('chat_id'));

        $preferredCandidates = array_values(array_unique(array_filter([
            $rawCanalUserId,
            $this->resolveCanalUserIdFromChatId($rawCanalUserId),
            $this->normalizePhone($rawCanalUserId),
        ], fn ($value) => is_string($value) && trim($value) !== '')));

        if (!empty($preferredCandidates)) {
            $contactoPreferido = ChatContactoCanal::query()
                ->where('canal', $canal)
                ->where(function ($query) use ($preferredCandidates) {
                    $query->whereIn('canal_user_id', $preferredCandidates);

                    foreach ($preferredCandidates as $candidate) {
                        if (preg_match('/^\d+$/', (string) $candidate)) {
                            $query->orWhere('canal_user_id', 'like', $candidate . '@%');
                        }
                    }
                })
                ->first();

            if ($contactoPreferido) {
                return Conversacion::query()
                    ->with('contactoCanal')
                    ->where('canal_contacto_id', $contactoPreferido->id)
                    ->latest('ultima_actividad')
                    ->first();
            }
        }

        $candidates = array_values(array_unique(array_filter([
            $rawCanalUserId,
            $this->resolveCanalUserIdFromChatId($rawCanalUserId),
            $this->resolveCanalUserIdFromChatId($threadFromExternal),
            $this->resolveCanalUserIdFromChatId($threadFromChat),
            $this->normalizePhone($rawCanalUserId),
            $this->normalizePhone($threadFromExternal),
            $this->normalizePhone($threadFromChat),
        ], fn ($value) => is_string($value) && trim($value) !== '')));

        if (empty($candidates)) {
            if ($telefonoFallback !== '') {
                $contactoPorTelefonoCanal = ChatContactoCanal::query()
                    ->where('canal', $canal)
                    ->where('telefono_normalizado', $telefonoFallback)
                    ->first();

                if ($contactoPorTelefonoCanal) {
                    return Conversacion::query()
                        ->with('contactoCanal')
                        ->where('canal_contacto_id', $contactoPorTelefonoCanal->id)
                        ->latest('ultima_actividad')
                        ->first();
                }
            }

            return null;
        }

        $contacto = ChatContactoCanal::query()
            ->where('canal', $canal)
            ->where(function ($query) use ($candidates) {
                $query->whereIn('canal_user_id', $candidates);

                foreach ($candidates as $candidate) {
                    if (preg_match('/^\d+$/', (string) $candidate)) {
                        $query->orWhere('canal_user_id', 'like', $candidate . '@%');
                    }
                }
            })
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

    /**
     * Si el telefono coincide con un proveedor registrado, arma el tag de metadata
     * para que el contacto/conversacion se cree ya clasificado como "proveedor" y el
     * agente IA (o el panel) no lo trate como cliente nuevo. Solo se usa en los
     * valores por defecto de firstOrCreate (primera vez que se ve el numero); nunca
     * se debe mezclar en los fill()/update() de cada mensaje, porque pisaria una
     * reclasificacion manual hecha despues por un empleado en el panel.
     */
    private function proveedorMetadataTag(?string $telefono): array
    {
        return $this->resolveProveedorByPhone($telefono) ? ['tipo_contacto' => 'proveedor'] : [];
    }

    private function resolveProveedorByPhone(?string $telefono): ?Proveedor
    {
        $digits = preg_replace('/\D/', '', (string) $telefono);

        if (strlen($digits) < 7) {
            return null;
        }

        $suffix = substr($digits, -9);

        return Proveedor::query()
            ->where('activopro', true)
            ->get(['idpro', 'nombrepro', 'telefonopro'])
            ->first(function (Proveedor $proveedor) use ($suffix) {
                $provDigits = preg_replace('/\D/', '', (string) $proveedor->telefonopro);

                if (strlen($provDigits) < 7) {
                    return false;
                }

                $provSuffix = substr($provDigits, -9);
                $len = min(strlen($suffix), strlen($provSuffix));

                return $len >= 7 && substr($suffix, -$len) === substr($provSuffix, -$len);
            });
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
        $textoAgente = trim((string) ($mensaje->texto_agente ?? ''));
        if ($textoAgente !== '') {
            return $textoAgente;
        }

        $contenido = trim((string) $mensaje->contenido);
        if ($contenido !== '') {
            return $contenido;
        }

        return match ($mensaje->tipo_contenido) {
            'imagen' => '[imagen] ' . ($mensaje->archivo_url ?: 'sin_url'),
            'audio' => '[audio] ' . ($mensaje->archivo_url ?: 'sin_url'),
            'sticker' => '[sticker] ' . ($mensaje->archivo_url ?: 'sin_url'),
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
        $normalized = strtolower(trim((string) $tipo));

        return match ($normalized) {
            'text', 'conversation', 'texto' => 'texto',
            'image', 'imagemessage', 'imagen' => 'imagen',
            'audio', 'voice', 'ptt', 'audiomessage' => 'audio',
            'sticker', 'stickermessage' => 'sticker',
            'video', 'videomessage' => 'video',
            'document', 'documentmessage', 'documento' => 'documento',
            'archivo', 'file' => 'archivo',
            default => 'texto',
        };
    }

    private function persistInboundMedia(?string $mediaUrl, string $tipoContenido, ?string $mediaMimeType, ?string $mediaBase64 = null, ?string $mediaFileName = null): array
    {
        $base64 = trim((string) $mediaBase64);

        if ($base64 !== '') {
            return $this->storeInboundMediaFromBase64($base64, $tipoContenido, $mediaMimeType, $mediaFileName);
        }

        $url = trim((string) $mediaUrl);

        if ($url === '') {
            return [null, $mediaMimeType];
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [$url, $mediaMimeType];
        }

        try {
            $response = Http::timeout(20)->get($url);

            if (! $response->successful()) {
                return [$url, $mediaMimeType];
            }

            $resolvedMimeType = $this->normalizeInboundMimeType($mediaMimeType ?: (string) $response->header('Content-Type'));
            $extension = $this->resolveInboundMediaExtension($resolvedMimeType, $url, $tipoContenido);
            $folder = match ($tipoContenido) {
                'imagen' => 'chat/inbound/images',
                'audio' => 'chat/inbound/audio',
                'sticker' => 'chat/inbound/stickers',
                default => 'chat/inbound/files',
            };

            $filename = now()->format('YmdHis') . '_' . Str::random(10) . '.' . $extension;
            $path = $folder . '/' . $filename;

            Storage::disk('public')->put($path, $response->body());

            return [Storage::url($path), $resolvedMimeType];
        } catch (\Throwable) {
            return [$url, $mediaMimeType];
        }
    }

    private function resolveInboundMediaExtension(?string $mimeType, string $sourceUrl, string $tipoContenido): string
    {
        $lowerMime = strtolower((string) $mimeType);

        if ($lowerMime !== '') {
            if (str_contains($lowerMime, 'image/webp')) {
                return 'webp';
            }

            if (str_contains($lowerMime, 'image/')) {
                return 'jpg';
            }

            if (str_contains($lowerMime, 'audio/ogg')) {
                return 'ogg';
            }

            if (str_contains($lowerMime, 'audio/mpeg')) {
                return 'mp3';
            }

            if (str_contains($lowerMime, 'audio/')) {
                return 'mp3';
            }
        }

        $path = parse_url($sourceUrl, PHP_URL_PATH);
        $extension = strtolower((string) pathinfo((string) $path, PATHINFO_EXTENSION));

        if ($extension !== '') {
            return $extension;
        }

        return match ($tipoContenido) {
            'imagen', 'sticker' => 'jpg',
            'audio' => 'mp3',
            default => 'bin',
        };
    }

    private function storeInboundMediaFromBase64(string $base64, string $tipoContenido, ?string $mimeType, ?string $fileName = null): array
    {
        $normalized = preg_replace('/^data:[^;]+;base64,/', '', trim($base64));
        // n8n/form-urlencoded puede convertir '+' en espacios y agregar saltos de linea.
        $normalized = str_replace(' ', '+', (string) $normalized);
        $normalized = preg_replace('/\s+/', '', (string) $normalized);
        if (str_contains((string) $normalized, '%2B') || str_contains((string) $normalized, '%2F') || str_contains((string) $normalized, '%3D')) {
            $normalized = urldecode((string) $normalized);
            $normalized = preg_replace('/\s+/', '', (string) $normalized);
        }
        $binary = base64_decode($normalized, true);

        if ($binary === false) {
            return [null, $mimeType];
        }

        $extension = $this->resolveBase64MediaExtension($mimeType, $fileName, $tipoContenido);
        $folder = match ($tipoContenido) {
            'imagen' => 'chat/inbound/images',
            'audio' => 'chat/inbound/audio',
            'sticker' => 'chat/inbound/stickers',
            default => 'chat/inbound/files',
        };

        $filename = now()->format('YmdHis') . '_' . Str::random(10) . '.' . $extension;
        $path = $folder . '/' . $filename;
        Storage::disk('public')->put($path, $binary);

        return [Storage::url($path), $this->normalizeInboundMimeType($mimeType)];
    }

    private function resolveBase64MediaExtension(?string $mimeType, ?string $fileName, string $tipoContenido): string
    {
        $nameExtension = strtolower((string) pathinfo((string) $fileName, PATHINFO_EXTENSION));

        if ($nameExtension !== '') {
            return match ($nameExtension) {
                'oga', 'opus' => 'ogg',
                default => $nameExtension,
            };
        }

        return $this->resolveInboundMediaExtension($mimeType, (string) $fileName, $tipoContenido);
    }

    private function normalizeInboundMimeType(?string $mimeType): ?string
    {
        $normalized = strtolower(trim((string) $mimeType));

        if ($normalized === '') {
            return null;
        }

        $baseMimeType = trim(strtok($normalized, ';'));

        return match ($baseMimeType) {
            'audio/ogg', 'audio/opus', 'application/ogg' => 'audio/ogg',
            default => $baseMimeType,
        };
    }

    private function resolveInboundMediaUrlFromPayload(array $payload): ?string
    {
        return data_get($payload, 'media_url')
            ?: data_get($payload, 'message.media_url')
            ?: data_get($payload, 'message.mediaUrl')
            ?: data_get($payload, 'message.url')
            ?: data_get($payload, 'message.image.url')
            ?: data_get($payload, 'message.audio.url')
            ?: data_get($payload, 'message.sticker.url')
            ?: data_get($payload, 'content.media_url')
            ?: null;
    }

    private function resolveInboundMediaMimeTypeFromPayload(array $payload): ?string
    {
        return data_get($payload, 'media_mime_type')
            ?: data_get($payload, 'mime_type')
            ?: data_get($payload, 'message.mime_type')
            ?: data_get($payload, 'message.mimetype')
            ?: data_get($payload, 'message.mimeType')
            ?: null;
    }

    private function resolveInboundCaptionFromPayload(array $payload): ?string
    {
        return data_get($payload, 'message.caption')
            ?: data_get($payload, 'message.text')
            ?: data_get($payload, 'content.text')
            ?: data_get($payload, 'content')
            ?: null;
    }

    private function resolveCanalUserIdFromChatId(?string $chatId): ?string
    {
        $value = trim((string) $chatId);

        if ($value === '') {
            return null;
        }

        return preg_replace('/@.+$/', '', $value) ?: null;
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
        return PhoneNumber::canonicalEc($value);
    }

    private function scheduleWhatsappDispatch(
        int $conversationId,
        int $chatMensajeCanalId,
        string $contenido,
        array $options = [],
        ?string $instance = null,
        ?string $apiKey = null,
        ?string $serverUrl = null
    ): void {
        app()->terminating(function () use ($conversationId, $chatMensajeCanalId, $contenido, $options, $instance, $apiKey, $serverUrl) {
            $this->performWhatsappDispatch(
                $conversationId,
                $chatMensajeCanalId,
                $contenido,
                $options,
                $instance,
                $apiKey,
                $serverUrl
            );
        });
    }

    private function performWhatsappDispatch(
        int $conversationId,
        int $chatMensajeCanalId,
        string $contenido,
        array $options = [],
        ?string $instance = null,
        ?string $apiKey = null,
        ?string $serverUrl = null
    ): array {
        $canalMensaje = ChatMensajeCanal::query()->find($chatMensajeCanalId);

        try {
            $conversacion = Conversacion::query()->with('contactoCanal')->find($conversationId);

            if (!$conversacion || !$canalMensaje) {
                return ['ok' => false, 'error' => 'Conversacion o canal_mensaje no encontrado.'];
            }

            $credentialsRequest = new Request(array_filter([
                'instance' => $instance,
                'apikey' => $apiKey,
                'server_url' => $serverUrl,
            ], fn ($value) => $value !== null && $value !== ''));

            [$resolvedInstance, $resolvedApiKey, $resolvedServerUrl] = $this->resolveWhatsappCredentials($conversacion, $credentialsRequest);

            if (!$resolvedInstance || !$resolvedApiKey) {
                $error = 'Credenciales de WhatsApp incompletas.';

                $canalMensaje->update([
                    'external_status' => 'failed',
                    'payload' => array_merge($canalMensaje->payload ?? [], [
                        'dispatch' => [
                            'ok' => false,
                            'error' => $error,
                        ],
                    ]),
                ]);

                return ['ok' => false, 'error' => $error];
            }

            $destino = (string) ($conversacion->contactoCanal?->canal_user_id
                ?: $conversacion->contactoCanal?->telefono_normalizado
                ?: '');

            if ($destino === '') {
                $error = 'No se pudo resolver el numero destino.';

                $canalMensaje->update([
                    'external_status' => 'failed',
                    'payload' => array_merge($canalMensaje->payload ?? [], [
                        'dispatch' => [
                            'ok' => false,
                            'error' => $error,
                        ],
                    ]),
                ]);

                return ['ok' => false, 'error' => $error];
            }

            $dispatch = $this->whatsAppOutboundService->sendText(
                $destino,
                $this->resolveOutboundTextContent($contenido, $options),
                $resolvedInstance,
                $resolvedApiKey,
                $resolvedServerUrl,
                $options
            );

            $dispatchOk = (bool) ($dispatch['ok'] ?? false);

            $canalMensaje->update([
                'external_status' => $dispatchOk ? 'sent' : 'failed',
                'external_message_id' => $dispatch['external_message_id'] ?? $canalMensaje->external_message_id,
                'payload' => array_merge($canalMensaje->payload ?? [], [
                    'dispatch' => [
                        'ok' => $dispatchOk,
                        'status' => $dispatch['status'] ?? null,
                        'error' => $dispatch['error'] ?? null,
                        'response' => $dispatch['payload'] ?? ($dispatch['response'] ?? null),
                        'sent_at' => now()->toIso8601String(),
                    ],
                ]),
            ]);

            return $dispatch;
        } catch (\Throwable $e) {
            if ($canalMensaje) {
                $canalMensaje->update([
                    'external_status' => 'failed',
                    'payload' => array_merge($canalMensaje->payload ?? [], [
                        'dispatch' => [
                            'ok' => false,
                            'error' => $e->getMessage(),
                        ],
                    ]),
                ]);
            }

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function resolveOutboundTextContent(string $contenido, array $options = []): string
    {
        $text = trim($contenido);

        if ($text !== '') {
            return $text;
        }

        return match ((string) ($options['tipo_contenido'] ?? 'texto')) {
            'imagen' => '[imagen enviada]',
            'audio' => '[audio enviado]',
            'video' => '[video enviado]',
            'documento', 'archivo' => '[archivo enviado]',
            'sticker' => '[sticker enviado]',
            default => '[mensaje enviado]',
        };
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

    private function upsertWhatsappChannelFromRequest(Request $request): ?ChatWhatsappChannel
    {
        $instance = trim((string) $request->input('instance'));
        $apiKey = trim((string) $request->input('apikey'));

        if ($instance === '' || $apiKey === '') {
            return null;
        }

        $serverUrl = trim((string) $request->input('server_url', config('services.evoapi.base_url')));
        $color = $request->input('color')
            ?: $request->input('whatsapp_color')
            ?: $this->resolveWhatsappColorByInstance($instance)
            ?: 'otro';

        return ChatWhatsappChannel::query()->updateOrCreate(
            ['instance_name' => $instance],
            [
                'display_name' => $request->input('display_name') ?: $instance,
                'api_key' => $apiKey,
                'server_url' => $serverUrl !== '' ? $serverUrl : config('services.evoapi.base_url'),
                'color' => in_array($color, ['verde', 'azul', 'otro'], true) ? $color : 'otro',
                'is_active' => true,
                'outbound_enabled' => true,
                'metadata' => array_filter([
                    'source' => 'router-auto-sync',
                    'last_seen_at' => now()->toIso8601String(),
                ]),
            ]
        );
    }
}
