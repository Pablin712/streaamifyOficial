<?php

namespace App\Services\Chat;

use App\Models\ChatWhatsappChannel;
use Illuminate\Support\Facades\Http;

/**
 * Servicio simplificado para envío de mensajes WhatsApp
 */
class WhatsAppOutboundService
{
    /**
     * Envía mensaje de texto directo por Evolution API
     */
    public function sendText(string $number, string $message, ?string $instance, ?string $apiKey, ?string $serverUrl = null): array
    {
        if (! $apiKey || ! $instance) {
            return ['ok' => false, 'error' => 'No hay credenciales para Evolution API'];
        }

        return $this->sendViaEvolution($number, $message, $instance, $apiKey, $serverUrl);
    }

    /**
     * Resuelve canal WhatsApp por nombre de instancia
     */
    public function resolveChannelByInstance(?string $instance): ?ChatWhatsappChannel
    {
        if (empty($instance)) {
            return null;
        }

        return ChatWhatsappChannel::availableForOutbound()
            ->whereRaw('LOWER(instance_name) = ?', [strtolower(trim($instance))])
            ->first();
    }

    // --- Métodos privados simples ---

    private function sendViaEvolution(string $number, string $message, string $instance, string $apiKey, ?string $serverUrl): array
    {
        $baseUrl = rtrim($serverUrl ?: config('services.evoapi.base_url'), '/');

        try {
            $response = Http::timeout(20)
                ->withHeader('apiKey', $apiKey)
                ->post("{$baseUrl}/message/sendText/{$instance}", [
                    'number' => $this->formatNumber($number),
                    'text' => $message,
                ]);

            return [
                'ok' => $response->successful(),
                'external_message_id' => $response->json('key.id') ?? $response->json('message.key.id'),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function formatNumber(string $number): string
    {
        $number = trim($number);

        if (str_contains($number, '@')) {
            return $number;
        }

        return preg_replace('/\D/', '', $number).'@s.whatsapp.net';
    }
}
