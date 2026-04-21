<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class N8nMessageService
{
    protected $n8nOutboundWebhook;
    protected $n8nToken;
    
    public function __construct()
    {
        $this->n8nOutboundWebhook = config('services.n8n.outbound_webhook', 'https://autobot.aaronsoft.es/webhook/laravel-outbound');
        $this->n8nToken = config('services.n8n.webhook_token', 'laravel_token_123');
    }
    
    /**
     * Enviar mensaje a n8n para que lo reenvíe al cliente
     */
    public function sendToClient($telefono, $mensaje, $tipo = 'texto', $options = [])
    {
        try {
            // Determinar instancia (azul/verde) basado en tu lógica
            $instance = $this->determineInstance($telefono);
            
            $payload = [
                'telefono' => $telefono,
                'mensaje' => $mensaje,
                'tipo_contenido' => $tipo,
                'instance' => $instance, // 'azul' o 'verde'
                'operator_id' => auth()->check() ? auth()->id() : null,
                'timestamp' => now()->toISOString(),
                ...$options
            ];
            
            Log::info('Enviando mensaje a n8n', [
                'telefono' => $telefono,
                'instance' => $instance,
                'payload' => $payload
            ]);
            
            // Enviar a n8n
            $response = Http::withHeaders([
                'X-N8N-Token' => $this->n8nToken,
                'Content-Type' => 'application/json'
            ])->post($this->n8nOutboundWebhook, $payload);
            
            if ($response->successful()) {
                Log::info('Mensaje enviado exitosamente a n8n', [
                    'telefono' => $telefono,
                    'n8n_response' => $response->json()
                ]);
                
                return [
                    'success' => true,
                    'n8n_response' => $response->json(),
                    'message_id' => $response->json('message_id')
                ];
            }
            
            Log::error('Error enviando mensaje a n8n', [
                'telefono' => $telefono,
                'status' => $response->status(),
                'error' => $response->body()
            ]);
            
            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status()
            ];
            
        } catch (\Exception $e) {
            Log::error('Excepción enviando mensaje a n8n', [
                'telefono' => $telefono,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Determinar a qué instancia enviar (azul/verde)
     */
    private function determineInstance($telefono)
    {
        // Tu lógica para determinar azul/verde
        // Ejemplo: ciertos números van a verde
        $numerosVerdes = ['593996464991', '593961412826'];
        
        return in_array($telefono, $numerosVerdes) ? 'verde' : 'azul';
    }
    
    /**
     * Enviar mensaje como respuesta a un mensaje existente
     */
    public function replyToMessage($mensajeId, $respuesta, $tipo = 'texto')
    {
        try {
            // Obtener el mensaje original
            $mensajeOriginal = \App\Models\ChatMensajeCanal::find($mensajeId);
            
            if (!$mensajeOriginal) {
                return [
                    'success' => false,
                    'error' => 'Mensaje original no encontrado'
                ];
            }
            
            // Obtener el contacto
            $contacto = $mensajeOriginal->contacto;
            
            // Enviar respuesta
            $resultado = $this->sendToClient(
                $contacto->telefono,
                $respuesta,
                $tipo,
                [
                    'in_reply_to' => $mensajeId,
                    'contact_id' => $contacto->id
                ]
            );
            
            // Si se envió exitosamente, guardar el mensaje de respuesta
            if ($resultado['success']) {
                $mensajeRespuesta = \App\Models\ChatMensajeCanal::create([
                    'id_contacto_canal' => $contacto->id,
                    'mensaje' => $respuesta,
                    'tipo_contenido' => $tipo,
                    'direccion' => 'outbound',
                    'external_message_id' => $resultado['message_id'] ?? uniqid('n8n_out_'),
                    'external_status' => 'sent',
                    'payload' => [
                        'in_reply_to' => $mensajeId,
                        'n8n_response' => $resultado['n8n_response'] ?? null
                    ]
                ]);
                
                $resultado['local_message_id'] = $mensajeRespuesta->id;
            }
            
            return $resultado;
            
        } catch (\Exception $e) {
            Log::error('Error respondiendo mensaje', [
                'mensaje_id' => $mensajeId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}