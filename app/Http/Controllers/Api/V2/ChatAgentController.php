<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Services\N8nMessageService;
use Illuminate\Http\Request;
use App\Models\ChatMensajeCanal;
use App\Models\ChatContactoCanal;
use Illuminate\Support\Facades\Log;

class ChatAgentController extends Controller
{
    protected $n8nService;
    
    public function __construct(N8nMessageService $n8nService)
    {
        $this->n8nService = $n8nService;
    }
    
    /**
     * Listar conversaciones activas
     */
    public function listConversations(Request $request)
    {
        try {
            $conversaciones = ChatContactoCanal::with(['ultimoMensaje', 'agenteAsignado'])
                ->whereHas('mensajes', function($query) {
                    $query->where('direccion', 'inbound')
                          ->where('created_at', '>=', now()->subDays(7));
                })
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function($contacto) {
                    return [
                        'contact_id' => $contacto->id,
                        'telefono' => $contacto->telefono,
                        'nombre' => $contacto->nombre,
                        'ultimo_mensaje' => $contacto->ultimoMensaje?->mensaje,
                        'ultimo_mensaje_fecha' => $contacto->ultimoMensaje?->created_at,
                        'mensajes_pendientes' => $contacto->mensajes()
                            ->where('direccion', 'inbound')
                            ->where('leido', false)
                            ->count(),
                        'agente_asignado' => $contacto->agenteAsignado?->name,
                        'canal' => $contacto->canal,
                        'metadata' => $contacto->metadata
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $conversaciones,
                'total' => $conversaciones->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error listando conversaciones', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }
    
    /**
     * Obtener mensajes de una conversación
     */
    public function getMessages($contactId)
    {
        try {
            $contacto = ChatContactoCanal::findOrFail($contactId);
            
            $mensajes = ChatMensajeCanal::where('id_contacto_canal', $contactId)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function($mensaje) {
                    return [
                        'id' => $mensaje->id,
                        'mensaje' => $mensaje->mensaje,
                        'tipo_contenido' => $mensaje->tipo_contenido,
                        'direccion' => $mensaje->direccion,
                        'created_at' => $mensaje->created_at,
                        'leido' => $mensaje->leido,
                        'payload' => $mensaje->payload
                    ];
                });
            
            // Marcar mensajes como leídos
            ChatMensajeCanal::where('id_contacto_canal', $contactId)
                ->where('direccion', 'inbound')
                ->where('leido', false)
                ->update(['leido' => true]);
            
            return response()->json([
                'success' => true,
                'contacto' => [
                    'id' => $contacto->id,
                    'telefono' => $contacto->telefono,
                    'nombre' => $contacto->nombre,
                    'canal' => $contacto->canal,
                    'metadata' => $contacto->metadata
                ],
                'mensajes' => $mensajes,
                'total' => $mensajes->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo mensajes', [
                'contact_id' => $contactId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }
    
    /**
     * Enviar mensaje como agente
     */
    public function sendMessage(Request $request, $contactId)
    {
        try {
            $request->validate([
                'mensaje' => 'required|string',
                'tipo_contenido' => 'nullable|string|in:texto,imagen,video,audio,documento'
            ]);
            
            $contacto = ChatContactoCanal::findOrFail($contactId);
            
            // Enviar mensaje a través de n8n
            $resultado = $this->n8nService->sendToClient(
                $contacto->telefono,
                $request->mensaje,
                $request->tipo_contenido ?? 'texto',
                [
                    'operator_id' => auth()->id(),
                    'operator_name' => auth()->user()->name ?? 'Agente',
                    'contact_id' => $contactId
                ]
            );
            
            if (!$resultado['success']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error enviando mensaje: ' . ($resultado['error'] ?? 'Desconocido')
                ], 500);
            }
            
            // Guardar mensaje localmente
            $mensaje = ChatMensajeCanal::create([
                'id_contacto_canal' => $contactId,
                'mensaje' => $request->mensaje,
                'tipo_contenido' => $request->tipo_contenido ?? 'texto',
                'direccion' => 'outbound',
                'external_message_id' => $resultado['message_id'] ?? uniqid('n8n_out_'),
                'external_status' => 'sent',
                'payload' => [
                    'operator_id' => auth()->id(),
                    'operator_name' => auth()->user()->name ?? 'Agente',
                    'n8n_response' => $resultado['n8n_response'] ?? null
                ]
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado exitosamente',
                'data' => [
                    'message_id' => $mensaje->id,
                    'contact_id' => $contactId,
                    'telefono' => $contacto->telefono,
                    'n8n_response' => $resultado['n8n_response'] ?? null
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error enviando mensaje como agente', [
                'contact_id' => $contactId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }
    
    /**
     * Responder a un mensaje específico
     */
    public function replyToMessage(Request $request, $messageId)
    {
        try {
            $request->validate([
                'respuesta' => 'required|string',
                'tipo_contenido' => 'nullable|string|in:texto,imagen,video,audio,documento'
            ]);
            
            $resultado = $this->n8nService->replyToMessage(
                $messageId,
                $request->respuesta,
                $request->tipo_contenido ?? 'texto'
            );
            
            if (!$resultado['success']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error enviando respuesta: ' . ($resultado['error'] ?? 'Desconocido')
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Respuesta enviada exitosamente',
                'data' => [
                    'message_id' => $resultado['local_message_id'] ?? null,
                    'n8n_response' => $resultado['n8n_response'] ?? null
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error respondiendo mensaje', [
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }
    
    /**
     * Asignar conversación a agente
     */
    public function assignToAgent(Request $request, $contactId)
    {
        try {
            $request->validate([
                'agent_id' => 'required|integer|exists:users,id'
            ]);
            
            $contacto = ChatContactoCanal::findOrFail($contactId);
            $contacto->update(['id_agente_asignado' => $request->agent_id]);
            
            Log::info('Conversación asignada a agente', [
                'contact_id' => $contactId,
                'agent_id' => $request->agent_id,
                'operator_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Conversación asignada exitosamente',
                'data' => [
                    'contact_id' => $contactId,
                    'agent_id' => $request->agent_id,
                    'agent_name' => \App\Models\User::find($request->agent_id)->name ?? 'Desconocido'
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error asignando conversación', [
                'contact_id' => $contactId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }
}