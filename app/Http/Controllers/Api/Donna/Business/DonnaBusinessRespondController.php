<?php

namespace App\Http\Controllers\Api\Donna\Business;

use App\Http\Controllers\Controller;
use App\Models\DonnaChannel;
use App\Models\DonnaConversation;
use App\Models\DonnaMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DonnaBusinessRespondController extends Controller
{
    /**
     * POST /api/donna/business/save-respond
     *
     * Guarda la respuesta en el historial Y la envía por WhatsApp via Evo API
     * si el canal tiene api_base_url y api_key_encrypted configurados.
     *
     * Parámetro opcional `send_whatsapp` (default: true) para controlar si se envía.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'           => 'required|integer',
            'service_id'          => 'required|integer',
            'channel_id'          => 'required|integer',
            'conversation_id'     => 'required|integer',
            'message_id'          => 'nullable|integer',
            'sender_type'         => 'required|in:ai_agent,business_owner,system',
            'direction'           => 'nullable|in:inbound,outbound',
            'external_chat_id'    => 'required|string',  // remoteJid del cliente final
            'recipient_number'    => 'nullable|string',  // número limpio sin @s.whatsapp.net
            'content_text'        => 'required|string',
            'provider_message_id' => 'nullable|string',
            'ai_metadata'         => 'nullable|array',
            'send_whatsapp'       => 'nullable|boolean', // default true
        ]);

        // 1. Marcar mensaje inbound como respondido
        if (!empty($data['message_id'])) {
            DonnaMessage::where('id', $data['message_id'])
                ->where('client_id', $data['client_id'])
                ->update([
                    'ai_response_text'  => $data['content_text'],
                    'processing_status' => 'responded',
                ]);
        }

        // 2. Guardar el mensaje outbound en historial
        $outbound = DonnaMessage::create([
            'client_id'           => $data['client_id'],
            'service_id'          => $data['service_id'],
            'channel_id'          => $data['channel_id'],
            'conversation_id'     => $data['conversation_id'],
            'direction'           => $data['direction'] ?? 'outbound',
            'sender_type'         => $data['sender_type'],
            'message_type'        => 'text',
            'external_chat_id'    => $data['external_chat_id'],
            'content_text'        => $data['content_text'],
            'provider_message_id' => $data['provider_message_id'] ?? null,
            'processing_status'   => 'responded',
            'metadata_json'       => $data['ai_metadata'] ?? null,
        ]);

        // 3. Actualizar última actividad de la conversación
        DonnaConversation::where('id', $data['conversation_id'])->update([
            'last_message_at'      => now(),
            'last_message_preview' => substr($data['content_text'], 0, 255),
        ]);

        // 4. Enviar mensaje por WhatsApp via Evo API (si está configurado y send_whatsapp != false)
        $evoResult = null;
        // Default false: el workflow n8n ya envió vía Evo API antes de llamar save-respond
        $shouldSend = $data['send_whatsapp'] ?? false;

        if ($shouldSend) {
            $channel = DonnaChannel::find($data['channel_id']);
            $evoResult = $this->sendViaEvo($channel, $data);

            // Actualizar provider_message_id si Evo nos devolvió el ID del mensaje enviado
            if (!empty($evoResult['key']['id'])) {
                $outbound->update(['provider_message_id' => $evoResult['key']['id']]);
            }
        }

        return response()->json([
            'success'              => true,
            'stored'               => true,
            'sent_whatsapp'        => $evoResult !== null,
            'conversation_id'      => $data['conversation_id'],
            'message_id'           => $outbound->id,
            'provider_message_id'  => $outbound->fresh()->provider_message_id,
        ]);
    }

    private function sendViaEvo(?DonnaChannel $channel, array $data): ?array
    {
        if (!$channel || !$channel->api_base_url || !$channel->api_key_encrypted) {
            return null;
        }

        try {
            $apiKey       = Crypt::decryptString($channel->api_key_encrypted);
            $instanceName = $channel->instance_name;
            $serverUrl    = rtrim($channel->api_base_url, '/');

            // Número del destinatario: usar recipient_number limpio o extraer del remoteJid
            $recipient = $data['recipient_number']
                ?? preg_replace('/@.+$/', '', $data['external_chat_id']);

            $response = Http::withHeaders([
                'apikey'       => $apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$serverUrl}/message/sendText/{$instanceName}", [
                'number' => $recipient,
                'text'   => $data['content_text'],
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Donna Business: Evo API send failed', [
                'channel_id' => $channel->id,
                'status'     => $response->status(),
                'body'       => $response->body(),
            ]);

            return null;

        } catch (\Throwable $e) {
            Log::error('Donna Business: Evo API exception', [
                'channel_id' => $channel->id,
                'error'      => $e->getMessage(),
            ]);
            return null;
        }
    }
}
