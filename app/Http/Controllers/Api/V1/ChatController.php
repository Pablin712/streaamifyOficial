<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Conversacion;
use App\Models\Mensaje;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Listar todas las conversaciones (para empleados)
     * GET /api/v1/chat/conversaciones
     */
    public function listarConversaciones(Request $request)
    {
        try {
            // Verificar permiso
            $empleado = auth()->guard('empleado')->user();

            if (!$empleado || !$empleado->can('chat.ver')) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso para ver el chat'
                ], 403);
            }

            $estado = $request->input('estado'); // abierta, cerrada, etc.
            $search = $request->input('search'); // Buscar por nombre de cliente

            $query = Conversacion::with([
                'cliente:idcli,nombrecli,telefonocli',
                'ultimoEmpleado:idemp,nombreemp',
                'ultimoMensaje'
            ]);

            // Filtrar por estado
            if ($estado) {
                $query->where('estado', $estado);
            } else {
                // Por defecto, solo conversaciones abiertas
                $query->abiertas();
            }

            // Buscar por cliente
            if ($search) {
                $query->whereHas('cliente', function ($q) use ($search) {
                    $q->where('nombrecli', 'like', "%{$search}%")
                      ->orWhere('telefonocli', 'like', "%{$search}%");
                });
            }

            $conversaciones = $query->orderBy('ultima_actividad', 'desc')
                                   ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $conversaciones->items(),
                'pagination' => [
                    'total' => $conversaciones->total(),
                    'current_page' => $conversaciones->currentPage(),
                    'last_page' => $conversaciones->lastPage(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener conversaciones',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener mensajes de una conversación
     * GET /api/v1/chat/conversaciones/{id}/mensajes
     */
    public function obtenerMensajes(string $idconv)
    {
        try {
            $empleado = auth()->guard('empleado')->user();

            if (!$empleado || !$empleado->can('chat.ver')) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso'
                ], 403);
            }

            $conversacion = Conversacion::with([
                'cliente',
                'mensajes.empleado:idemp,nombreemp',
                'mensajes.cliente:idcli,nombrecli'
            ])->find($idconv);

            if (!$conversacion) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversación no encontrada'
                ], 404);
            }

            // Marcar mensajes como leídos por empleado
            $conversacion->marcarComoLeida();

            return response()->json([
                'success' => true,
                'data' => [
                    'conversacion' => $conversacion,
                    'mensajes' => $conversacion->mensajes,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener mensajes',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar mensaje como empleado
     * POST /api/v1/chat/conversaciones/{id}/mensajes
     */
    public function enviarMensaje(Request $request, string $idconv)
    {
        try {
            $empleado = auth()->guard('empleado')->user();

            if (!$empleado || !$empleado->can('chat.responder')) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso para responder'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'contenido' => 'required|string',
                'tipo_contenido' => 'nullable|in:texto,imagen,archivo',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $conversacion = Conversacion::find($idconv);

            if (!$conversacion) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversación no encontrada'
                ], 404);
            }

            // Crear mensaje
            $mensaje = Mensaje::create([
                'idconv' => $idconv,
                'tipo_remitente' => 'empleado',
                'idemp' => $empleado->idemp,
                'contenido' => $request->contenido,
                'tipo_contenido' => $request->tipo_contenido ?? 'texto',
            ]);

            // Actualizar conversación
            $conversacion->cambiarEstado('en_atencion', $empleado->idemp);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado',
                'data' => $mensaje->load('empleado')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al enviar mensaje',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cliente envía mensaje (inicia o continúa conversación)
     * POST /api/v1/chat/cliente/enviar
     */
    public function clienteEnviarMensaje(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'idcli' => 'required|exists:clientes,idcli',
                'contenido' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buscar conversación abierta para este cliente
            $conversacion = Conversacion::where('idcli', $request->idcli)
                ->abiertas()
                ->first();

            // Si no existe, crear nueva
            if (!$conversacion) {
                $conversacion = Conversacion::create([
                    'idcli' => $request->idcli,
                    'estado' => 'abierta',
                    'ultima_actividad' => now(),
                ]);
            }

            // Crear mensaje del cliente
            $mensaje = Mensaje::create([
                'idconv' => $conversacion->idconv,
                'tipo_remitente' => 'cliente',
                'idcli' => $request->idcli,
                'contenido' => $request->contenido,
                'tipo_contenido' => 'texto',
            ]);

            // Incrementar contador de mensajes no leídos
            $conversacion->increment('mensajes_no_leidos');
            $conversacion->update(['ultima_actividad' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado',
                'data' => [
                    'conversacion' => $conversacion,
                    'mensaje' => $mensaje->load('cliente'),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al enviar mensaje',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de conversación
     * PATCH /api/v1/chat/conversaciones/{id}/estado
     */
    public function cambiarEstado(Request $request, string $idconv)
    {
        try {
            $empleado = auth()->guard('empleado')->user();

            if (!$empleado || !$empleado->can('chat.cerrar')) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'estado' => 'required|in:abierta,en_atencion,cerrada,bot_activo',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $conversacion = Conversacion::find($idconv);

            if (!$conversacion) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversación no encontrada'
                ], 404);
            }

            $conversacion->cambiarEstado($request->estado, $empleado->idemp);

            // Si se cierra, enviar mensaje del sistema
            if ($request->estado === 'cerrada') {
                Mensaje::create([
                    'idconv' => $idconv,
                    'tipo_remitente' => 'sistema',
                    'contenido' => "Conversación cerrada por {$empleado->nombreemp}",
                    'tipo_contenido' => 'sistema',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado',
                'data' => $conversacion
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al cambiar estado',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de chat
     * GET /api/v1/chat/estadisticas
     */
    public function estadisticas()
    {
        try {
            $empleado = auth()->guard('empleado')->user();

            if (!$empleado || !$empleado->can('chat.ver_estadisticas')) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso'
                ], 403);
            }

            $abiertas = Conversacion::where('estado', 'abierta')->count();
            $enAtencion = Conversacion::where('estado', 'en_atencion')->count();
            $cerradasHoy = Conversacion::where('estado', 'cerrada')
                ->whereDate('updated_at', today())
                ->count();

            $noLeidos = Conversacion::sum('mensajes_no_leidos');

            return response()->json([
                'success' => true,
                'data' => [
                    'conversaciones_abiertas' => $abiertas,
                    'en_atencion' => $enAtencion,
                    'cerradas_hoy' => $cerradasHoy,
                    'mensajes_no_leidos' => $noLeidos,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener estadísticas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cliente anónimo envía mensaje
     * POST /api/v1/chat/anonimo/enviar
     */
    public function anonimoEnviarMensaje(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_id' => 'required|string',
                'contenido' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buscar o crear conversación anónima
            $conversacion = Conversacion::where('metadata->session_id', $request->session_id)
                ->whereIn('estado', ['abierta', 'en_atencion', 'en_espera'])
                ->first();

            if (!$conversacion) {
                $conversacion = Conversacion::create([
                    'idcli' => null, // Sin cliente
                    'estado' => 'abierta',
                    'ultima_actividad' => now(),
                    'metadata' => [
                        'session_id' => $request->session_id,
                        'tipo' => 'anonimo',
                        'created_at' => now()->toIso8601String(),
                    ],
                ]);
            }

            // Crear mensaje
            $mensaje = Mensaje::create([
                'idconv' => $conversacion->idconv,
                'tipo_remitente' => 'cliente',
                'idcli' => null,
                'contenido' => $request->contenido,
                'tipo_contenido' => 'texto',
                'metadata' => [
                    'session_id' => $request->session_id,
                ],
            ]);

            // Incrementar contador
            $conversacion->increment('mensajes_no_leidos');
            $conversacion->update(['ultima_actividad' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado',
                'data' => [
                    'conversacion' => $conversacion,
                    'mensaje' => $mensaje,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al enviar mensaje',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener conversación anónima
     * GET /api/v1/chat/anonimo/{session_id}/conversacion
     */
    public function obtenerConversacionAnonima(string $sessionId)
    {
        try {
            $conversacion = Conversacion::where('metadata->session_id', $sessionId)
                ->with('mensajes')
                ->whereIn('estado', ['abierta', 'en_atencion', 'en_espera'])
                ->first();

            if (!$conversacion) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'conversacion' => null,
                        'mensajes' => [],
                    ]
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'conversacion' => $conversacion,
                    'mensajes' => $conversacion->mensajes,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener conversación',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener conversación de cliente autenticado
     * GET /api/v1/chat/cliente/{idcli}/conversacion
     */
    public function obtenerConversacionCliente(int $idcli)
    {
        try {
            $conversacion = Conversacion::where('idcli', $idcli)
                ->with('mensajes')
                ->whereIn('estado', ['abierta', 'en_atencion', 'en_espera'])
                ->first();

            if (!$conversacion) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'conversacion' => null,
                        'mensajes' => [],
                    ]
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'conversacion' => $conversacion,
                    'mensajes' => $conversacion->mensajes,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener conversación',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ================================================
     * ENDPOINTS PARA N8N AI BOT
     * ================================================
     */

    /**
     * Obtener mensajes pendientes de respuesta AI
     * GET /api/v1/chat/n8n/mensajes-pendientes
     *
     * Este endpoint retorna los mensajes de clientes que:
     * - Pertenecen a conversaciones en estado 'abierta' o 'bot_activo'
     * - NO tienen requiere_humano = true
     * - Son del cliente (tipo_remitente = 'cliente')
     * - Aún no han sido respondidos por el bot
     */
    public function mensajesPendientesParaAI(Request $request)
    {
        try {
            $limit = $request->input('limit', 5); // Máximo 5 mensajes por request

            // Buscar conversaciones que el bot puede atender
            $conversaciones = Conversacion::with([
                'cliente:idcli,nombrecli,telefonocli,email',
                'mensajes' => function($query) {
                    $query->where('tipo_remitente', 'cliente')
                          ->whereNull('respondido_por_ai')
                          ->orWhere('respondido_por_ai', false)
                          ->orderBy('created_at', 'desc')
                          ->limit(1); // Solo el último mensaje sin responder
                }
            ])
            ->where('requiere_humano', false)
            ->whereIn('estado', ['abierta', 'bot_activo'])
            ->whereHas('mensajes', function($query) {
                $query->where('tipo_remitente', 'cliente')
                      ->where(function($q) {
                          $q->whereNull('respondido_por_ai')
                            ->orWhere('respondido_por_ai', false);
                      });
            })
            ->orderBy('ultima_actividad', 'asc')
            ->limit($limit)
            ->get();

            // Formatear respuesta para n8n
            $mensajesPendientes = $conversaciones->map(function($conv) {
                $ultimoMensaje = $conv->mensajes->first();

                return [
                    'idconv' => $conv->idconv,
                    'idmsg' => $ultimoMensaje->idmsg ?? null,
                    'cliente' => [
                        'idcli' => $conv->cliente->idcli,
                        'nombre' => $conv->cliente->nombrecli,
                        'telefono' => $conv->cliente->telefonocli,
                        'email' => $conv->cliente->email,
                    ],
                    'mensaje' => [
                        'contenido' => $ultimoMensaje->contenido ?? '',
                        'tipo_contenido' => $ultimoMensaje->tipo_contenido ?? 'texto',
                        'fecha' => $ultimoMensaje->created_at ?? null,
                    ],
                    'conversacion' => [
                        'estado' => $conv->estado,
                        'mensajes_no_leidos' => $conv->mensajes_no_leidos,
                        'ultima_actividad' => $conv->ultima_actividad,
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $mensajesPendientes->count(),
                'data' => $mensajesPendientes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener mensajes pendientes',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Responder desde AI (n8n + DeepSeek)
     * POST /api/v1/chat/n8n/responder
     *
     * Body:
     * {
     *   "idconv": "001",
     *   "idmsg": "001",  // ID del mensaje del cliente que se está respondiendo
     *   "contenido": "¡Hola! Netflix cuesta $2.50 el perfil...",
     *   "metadata": { "model": "deepseek-chat", "tokens": 150 }
     * }
     */
    public function responderDesdeAI(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'idconv' => 'required|exists:conversaciones,idconv',
                'idmsg' => 'required|exists:mensajes,idmsg',
                'contenido' => 'required|string',
                'metadata' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $conversacion = Conversacion::find($request->idconv);

            if (!$conversacion) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversación no encontrada'
                ], 404);
            }

            // Verificar que la conversación no requiera atención humana
            if ($conversacion->requiere_humano) {
                return response()->json([
                    'success' => false,
                    'error' => 'Esta conversación requiere atención humana'
                ], 403);
            }

            // Marcar el mensaje del cliente como respondido por AI
            $mensajeCliente = Mensaje::find($request->idmsg);
            if ($mensajeCliente) {
                $mensajeCliente->update([
                    'respondido_por_ai' => true,
                    'metadata' => array_merge(
                        $mensajeCliente->metadata ?? [],
                        ['ai_response_at' => now()]
                    )
                ]);
            }

            // Crear mensaje de respuesta del bot
            $mensaje = Mensaje::create([
                'idconv' => $request->idconv,
                'tipo_remitente' => 'bot',
                'idemp' => null, // No es un empleado
                'idcli' => null, // No es un cliente
                'contenido' => $request->contenido,
                'tipo_contenido' => 'texto',
                'metadata' => array_merge(
                    $request->metadata ?? [],
                    [
                        'respuesta_ai' => true,
                        'modelo' => $request->metadata['model'] ?? 'deepseek-chat',
                        'timestamp' => now(),
                    ]
                ),
            ]);

            // Actualizar estado de conversación
            $conversacion->update([
                'estado' => 'bot_activo',
                'ultima_actividad' => now(),
                'mensajes_no_leidos' => 0, // Reset porque el bot respondió
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Respuesta AI enviada correctamente',
                'data' => $mensaje
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al enviar respuesta AI',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar conversación como requiere atención humana
     * POST /api/v1/chat/n8n/marcar-requiere-humano
     *
     * Body:
     * {
     *   "idconv": "001",
     *   "razon": "Cliente solicita hablar con asesor"
     * }
     */
    public function marcarRequiereHumano(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'idconv' => 'required|exists:conversaciones,idconv',
                'razon' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $conversacion = Conversacion::find($request->idconv);

            $conversacion->update([
                'requiere_humano' => true,
                'estado' => 'en_espera',
                'metadata' => array_merge(
                    $conversacion->metadata ?? [],
                    [
                        'requiere_humano_desde' => now(),
                        'razon' => $request->razon ?? 'Derivado por AI',
                    ]
                ),
            ]);

            // Crear mensaje automático informando al cliente
            Mensaje::create([
                'idconv' => $request->idconv,
                'tipo_remitente' => 'sistema',
                'contenido' => '🔔 Hemos derivado tu consulta a un asesor humano. En breve te atenderemos.',
                'tipo_contenido' => 'texto',
                'metadata' => [
                    'tipo' => 'notificacion_sistema',
                    'razon_derivacion' => $request->razon ?? 'Solicitud de atención humana'
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Conversación marcada para atención humana',
                'data' => $conversacion
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al marcar conversación',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}


