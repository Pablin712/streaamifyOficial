<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMensajeCanal;
use App\Models\ChatContactoCanal;
use Illuminate\Support\Facades\Log;

class N8nWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('N8N Webhook recibido', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'ip' => $request->ip()
        ]);

        // 1. Validar token
        $token = $request->header('X-N8N-Token');
        $expectedToken = config('services.n8n.webhook_token', 'laravel_token_123');

        if ($token !== $expectedToken) {
            Log::warning('Token inválido recibido', ['token' => $token]);
            return response()->json(['error' => 'Token inválido'], 401);
        }

        // 2. Extraer datos
        $data = $request->all();

        // Validaciones básicas
        if (empty($data['telefono']) || empty($data['mensaje'])) {
            return response()->json(['error' => 'Datos incompletos'], 400);
        }

        try {
            // 3. Buscar o crear contacto
            $canalUserId = $data['canal_user_id'] ?? $data['telefono'] . '@s.whatsapp.net';

            $contacto = ChatContactoCanal::firstOrCreate(
                ['canal_user_id' => $canalUserId],
                [
                    'telefono' => $data['telefono'],
                    'nombre' => $data['nombre'] ?? 'Cliente',
                    'canal' => 'whatsapp',
                    'metadata' => [
                        'instance' => $data['instance'] ?? 'Streamify Azul',
                        'color' => $data['color'] ?? 'azul',
                        'es_empleado' => $data['es_empleado'] ?? false
                    ]
                ]
            );

            // 4. Guardar mensaje
            $mensaje = ChatMensajeCanal::create([
                'contacto_canal_id' => $contacto->id,
                'canal' => 'whatsapp',
                'direccion' => 'inbound',
                'external_message_id' => $data['external_message_id'] ?? uniqid('n8n_'),
                'external_status' => 'received',
                'payload' => array_merge($data, [
                    'mensaje_texto' => $data['mensaje'],
                    'tipo_contenido' => $data['tipo'] ?? 'texto',
                    'leido' => false,
                    'processed_at' => now()->toISOString()
                ])
            ]);

            Log::info('Mensaje guardado exitosamente', [
                'contacto_id' => $contacto->id,
                'mensaje_id' => $mensaje->id,
                'telefono' => $data['telefono']
            ]);

            // 5. Asignar a agente (si hay lógica implementada)
            $this->assignToAgent($contacto, $mensaje);

            // 6. Responder éxito
            return response()->json([
                'success' => true,
                'message' => 'Mensaje recibido y procesado',
                'data' => [
                    'contact_id' => $contacto->id,
                    'message_id' => $mensaje->id,
                    'telefono' => $data['telefono']
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error procesando webhook n8n', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function assignToAgent($contacto, $mensaje)
    {
        // Lógica para asignar a agente disponible
        // Por ahora solo logueamos
        Log::info('Asignando mensaje a agente', [
            'contacto_id' => $contacto->id,
            'mensaje_id' => $mensaje->id
        ]);

        // Aquí iría tu lógica de múltiples agentes
        // Ejemplo:
        // $agente = User::where('role', 'agente')->where('activo', true)->first();
        // if ($agente) {
        //     $contacto->update(['id_agente_asignado' => $agente->id]);
        // }
    }
}

