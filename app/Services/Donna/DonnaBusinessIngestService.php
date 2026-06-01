<?php

namespace App\Services\Donna;

use App\Models\DonnaAgentConfig;
use App\Models\DonnaChannel;
use App\Models\DonnaConversation;
use App\Models\DonnaMessage;
use App\Models\DonnaSubscription;
use Illuminate\Support\Facades\Log;

class DonnaBusinessIngestService
{
    /**
     * Resuelve el tenant por instance_name, guarda conversación y mensaje.
     * Devuelve el array de respuesta estandarizado para n8n.
     */
    public function ingest(array $data): array
    {
        $instanceName = $data['instance_name'] ?? null;
        $fromMe       = (bool) ($data['from_me'] ?? false);

        // Ignorar mensajes propios para evitar bucles
        if ($fromMe) {
            return $this->blocked('from_me_ignored', 'Mensaje enviado por el bot, ignorado para evitar bucle.');
        }

        // 1. Resolver canal por instance_name
        $channel = DonnaChannel::where('instance_name', $instanceName)
            ->where('channel_type', 'whatsapp')
            ->where('provider', 'evolution_api')
            ->first();

        if (!$channel) {
            return $this->blocked('channel_not_found', "No existe canal configurado para instance_name: {$instanceName}");
        }

        if ($channel->status !== 'active') {
            return $this->blocked('channel_inactive', 'El canal WhatsApp está inactivo o suspendido.');
        }

        // 2. Validar suscripción activa
        $sub = DonnaSubscription::where('id', $channel->subscription_id)
            ->where('service_type', 'business')
            ->where('status', 'active')
            ->where('is_enabled', true)
            ->first();

        if (!$sub) {
            $this->storeMessage($channel, null, null, $data, 'blocked_service_inactive', 'service_inactive');
            return $this->blocked('service_inactive', 'Donna Business no está activa para este negocio.', $channel->client_id);
        }

        if ($sub->expires_at && $sub->expires_at->isPast()) {
            $this->storeMessage($channel, null, null, $data, 'blocked_service_inactive', 'service_expired');
            return $this->blocked('service_expired', 'La suscripción Donna Business ha vencido.', $channel->client_id);
        }

        // 3. Resolver o crear conversación
        // remote_jid puede llegar vacío si n8n no lo extrajo correctamente; se reconstruye desde sender_identifier
        $senderIdentifier  = $data['sender_identifier'] ?? '';
        $remoteJid         = $data['remote_jid']
                            ?? $data['customer_jid']
                            ?? ($senderIdentifier ? $senderIdentifier . '@s.whatsapp.net' : '');
        if (!$senderIdentifier) {
            $senderIdentifier = preg_replace('/@.+$/', '', $remoteJid);
        }
        $senderName        = $data['sender_name'] ?? null;

        $conversation = DonnaConversation::firstOrCreate(
            ['channel_id' => $channel->id, 'external_chat_id' => $remoteJid],
            [
                'client_id'         => $channel->client_id,
                'service_id'        => $sub->id,
                'sender_identifier' => $senderIdentifier,
                'sender_name'       => $senderName,
                'status'            => 'open',
            ]
        );

        // Actualizar nombre si cambió
        if ($senderName && $conversation->sender_name !== $senderName) {
            $conversation->update(['sender_name' => $senderName]);
        }

        // 4. Verificar human_takeover
        if ($conversation->isHumanTakeover()) {
            $msg = $this->storeMessage($channel, $sub, $conversation, $data, 'stored');
            return $this->blocked('human_takeover', 'Conversación en atención humana, IA no interviene.', $channel->client_id,
                $sub, $channel, $conversation, $msg);
        }

        // 5. Guardar mensaje
        $message = $this->storeMessage($channel, $sub, $conversation, $data, 'stored');

        // 6. Actualizar último mensaje en conversación
        $preview = substr(
            $data['transcription_text'] ?? $data['content_text'] ?? $data['caption_text'] ?? ('[' . ($data['message_type'] ?? 'media') . ']'),
            0, 255
        );
        $conversation->update(['last_message_at' => now(), 'last_message_preview' => $preview]);

        // 7. Obtener configuración del agente para exponer wait_seconds a n8n
        $agentConfig = DonnaAgentConfig::where('client_id', $channel->client_id)
            ->where('service_type', 'business')
            ->first();

        return [
            'stored'  => true,
            'allowed' => true,
            'reason'  => null,
            'config'  => ['wait_seconds' => $agentConfig?->wait_seconds ?? 35],
            'client'  => ['id' => $channel->client_id, 'name' => $channel->cliente?->nombrecli ?? ''],
            'service' => [
                'id'         => $sub->id,
                'type'       => 'business',
                'status'     => $sub->status,
                'expires_at' => $sub->expires_at?->toDateString(),
            ],
            'channel' => [
                'id'            => $channel->id,
                'type'          => 'whatsapp',
                'provider'      => $channel->provider,
                'instance_name' => $channel->instance_name,
                'server_url'    => $channel->api_base_url,
            ],
            'conversation' => [
                'id'               => $conversation->id,
                'external_chat_id' => $conversation->external_chat_id,
                'status'           => $conversation->status,
            ],
            'message' => [
                'id'                 => $message->id,
                'type'               => $message->message_type,
                'content_text'       => $message->content_text,
                'transcription_text' => $message->transcription_text,
                'ocr_text'           => $message->ocr_text,
                // Texto efectivo: prioridad transcripción > ocr > caption > texto directo
                'effective_text'     => $message->transcription_text
                                        ?? $message->ocr_text
                                        ?? $message->content_text
                                        ?? '',
                // Alias para compatibilidad con nodos n8n legacy
                'content_for_agent'  => $message->transcription_text
                                        ?? $message->ocr_text
                                        ?? $message->content_text
                                        ?? '',
            ],
        ];
    }

    private function storeMessage(
        DonnaChannel $channel,
        ?DonnaSubscription $sub,
        ?DonnaConversation $conversation,
        array $data,
        string $status = 'stored',
        string $blockedReason = null
    ): DonnaMessage {
        // Evitar duplicados por provider_message_id
        $providerId = $data['provider_message_id'] ?? null;
        if ($providerId) {
            $existing = DonnaMessage::where('provider_message_id', $providerId)->first();
            if ($existing) {
                return $existing;
            }
        }

        return DonnaMessage::create([
            'client_id'           => $channel->client_id,
            'service_id'          => $sub?->id,
            'channel_id'          => $channel->id,
            'conversation_id'     => $conversation?->id,
            'direction'           => 'inbound',
            'sender_type'         => 'final_customer',
            'message_type'        => $this->resolveMessageType($data),
            'provider_message_id' => $providerId,
            'external_chat_id'    => $data['remote_jid'] ?? null,
            'sender_identifier'   => $data['sender_identifier'] ?? null,
            'sender_name'         => $data['sender_name'] ?? null,
            'content_text'        => $data['content_text'] ?? $data['caption_text'] ?? null,
            'transcription_text'  => $data['transcription_text'] ?? null,
            'ocr_text'            => $data['ocr_text'] ?? null,
            'media_url'           => $data['media_url'] ?? null,
            'media_size'          => $data['media_size'] ?? null,
            'media_mime_type'     => $data['media_mime_type'] ?? null,
            'processing_status'   => $status,
            'blocked_reason'      => $blockedReason,
            'raw_payload_json'    => $data['raw_payload'] ?? null,
            // Guardar análisis de imagen si vino del workflow
            'metadata_json'       => !empty($data['image_analysis_json'])
                ? ['image_analysis' => json_decode($data['image_analysis_json'], true)]
                : null,
        ]);
    }

    private function resolveMessageType(array $data): string
    {
        $type = $data['message_type'] ?? 'conversation';
        return match(true) {
            str_contains($type, 'audio')    => 'audio',
            str_contains($type, 'image')    => 'image',
            str_contains($type, 'video')    => 'video',
            str_contains($type, 'document') => 'document',
            str_contains($type, 'sticker')  => 'sticker',
            default                         => 'text',
        };
    }

    private function blocked(
        string $reason,
        string $message,
        int $clientId = null,
        $sub = null,
        $channel = null,
        $conversation = null,
        $msg = null
    ): array {
        $result = [
            'stored'  => $msg !== null,
            'allowed' => false,
            'reason'  => $reason,
            'message' => $message,
        ];

        if ($channel) {
            $result['channel'] = ['id' => $channel->id, 'instance_name' => $channel->instance_name];
        }
        if ($sub) {
            $result['plan'] = [
                'name'        => $sub->plan?->name ?? 'Donna Business',
                'payment_url' => url('/donna'),
            ];
        }
        if ($conversation) {
            $result['conversation'] = ['id' => $conversation->id, 'status' => $conversation->status];
        }
        if ($msg) {
            $result['message_id'] = $msg->id;
        }

        return $result;
    }
}
