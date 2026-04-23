<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Services\Chat\WhatsAppHelpdeskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WhatsAppWebhookController extends Controller
{
    public function __construct(private readonly WhatsAppHelpdeskService $helpdesk) {}

    public function inbound(Request $request)
    {
        $expectedToken = (string) config('app.chat_webhook_token') ?: 'test_token_123';
        $providedToken = (string) ($request->header('X-Chat-Webhook-Token') ?: $request->input('token'));

        if ($expectedToken === '' || $providedToken === '' || ! hash_equals($expectedToken, $providedToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Token de webhook invalido.',
                'debug' => ['expected' => $expectedToken, 'provided' => $providedToken],
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'canal_user_id' => 'nullable|string|max:120',
            'from' => 'nullable|string|max:120',
            'telefono' => 'nullable|string|max:40',
            'numero' => 'nullable|string|max:40',
            'nombre' => 'nullable|string|max:120',
            'mensaje' => 'nullable|string',
            'contenido' => 'nullable|string',
            'tipo' => 'nullable|string|max:30',
            'tipo_contenido' => 'nullable|string|max:30',
            'media_url' => 'nullable|url',
            'archivo_url' => 'nullable|url',
            'mime_type' => 'nullable|string|max:120',
            'media_mime_type' => 'nullable|string|max:120',
            'external_message_id' => 'nullable|string|max:191',
            'external_thread_id' => 'nullable|string|max:191',
            'media_id' => 'nullable|string|max:191',
            'idcli' => 'nullable|integer|exists:clientes,idcli',
            'instance' => 'nullable|string|max:120',
            'apikey' => 'nullable|string|max:191',
            'server_url' => 'nullable|string|max:191',
            'payload' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->helpdesk->ingestInbound($request->all());

            return response()->json([
                'success' => true,
                'message' => $result['duplicate']
                    ? 'Mensaje duplicado ignorado.'
                    : 'Mensaje inbound registrado.',
                'data' => [
                    'idconv' => $result['conversation']->idconv,
                    'idmsg' => $result['message']?->idmsg,
                    'duplicate' => $result['duplicate'],
                ],
            ], $result['duplicate'] ? 200 : 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo registrar el mensaje inbound.',
            ], 500);
        }
    }
}
