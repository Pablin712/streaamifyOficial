<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatAIController extends Controller
{
    /**
     * Proxy para enviar mensajes al webhook de n8n
     * Evita problemas de CORS
     */
    public function sendMessage(Request $request)
    {
        try {
            // Verificar si hay sesión de empleado
            if (!Auth::check()) {
                Log::warning('Chat AI: No hay sesión autenticada', [
                    'session_id' => session()->getId(),
                    'has_session' => session()->has('_token'),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'No autenticado',
                    'message' => 'Debes iniciar sesión como empleado para usar el asistente IA.',
                ], 401);
            }

            $request->validate([
                'message' => 'required|string|max:2000',
                'chatHistory' => 'nullable|array',
                'sessionId' => 'nullable|string',
            ]);

            $webhookUrl = 'https://autobot.aaronsoft.es/webhook/44ebbc32-71e5-42b0-b103-d787741a59b9';

            /** @var \App\Models\Empleado $user */
            $user = Auth::user();
            //escribir en consola de navegador como debug no como error
            Log::info('Empleado autenticado para ChatAI', [
                'empleado_id' => $user?->idemp,
                'empleado_nombre' => $user?->nombreemp,
            ]);

            // Preparar payload para n8n
            $payload = [
                'message' => $request->message,
                'chatHistory' => $request->chatHistory ?? [],
                'timestamp' => now()->toISOString(),
                'empleadoId' => $user?->idemp ?? null,
                'empleadoNombre' => $user?->nombreemp ?? 'Desconocido',
                'sessionId' => $request->sessionId,
            ];

            Log::info('Enviando mensaje a n8n', [
                'empleado_id' => $user?->idemp,
                'message_length' => strlen($request->message),
            ]);

            // Hacer petición al webhook de n8n (GET con parámetros en URL)
            // Timeout aumentado a 120 segundos para consultas complejas de IA
            $response = Http::timeout(120)
                ->get($webhookUrl, [
                    'message' => $request->message,
                    'chatHistory' => json_encode($payload['chatHistory']),
                    'timestamp' => $payload['timestamp'],
                    'empleadoId' => $payload['empleadoId'],
                    'empleadoNombre' => $payload['empleadoNombre'],
                    'sessionId' => $payload['sessionId'],
                ]);

            // Verificar si hay respuesta válida (status 200-299)
            if ($response->status() < 200 || $response->status() >= 300) {
                Log::error('Error en respuesta de n8n', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Error al comunicarse con el asistente',
                    'details' => $response->body(),
                ], 500);
            }

            // Obtener respuesta como texto plano (como Telegram)
            $botResponse = $response->body();

            Log::info('Respuesta de n8n recibida', [
                'response_length' => strlen($botResponse),
                'response_preview' => substr($botResponse, 0, 100),
            ]);

            // Validar que haya contenido
            if (empty(trim($botResponse))) {
                Log::warning('Respuesta vacía de n8n');

                $botResponse = 'Lo siento, el asistente no pudo generar una respuesta en este momento. Por favor, intenta de nuevo.';
            }

            return response()->json([
                'success' => true,
                'output' => $botResponse,
                'message' => $botResponse,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ChatAI proxy', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => config('app.debug') ? $e->getMessage() : 'Error al procesar tu mensaje',
            ], 500);
        }
    }
}
