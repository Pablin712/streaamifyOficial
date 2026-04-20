<?php

namespace App\Services\Chat;

use App\Models\ChatWhatsappChannel;
use Illuminate\Support\Facades\Http;

class WhatsAppOutboundService
{
    public function sendText(
        string $number,
        string $message,
        string $instance,
        ?string $apiKey,
        ?string $serverUrl = null,
        array $extraPayload = []
    ): array
    {
        $webhookUrl = (string) config('services.n8n.client_message_webhook');

        if ($webhookUrl !== '') {
            $webhookResult = $this->dispatchViaN8nWebhook(
                $webhookUrl,
                $instance,
                (string) $apiKey,
                $number,
                $message,
                $extraPayload
            );

            if ($webhookResult['ok']) {
                return $webhookResult;
            }
        }

        if (!$apiKey) {
            return [
                'ok' => false,
                'status' => 0,
                'external_message_id' => null,
                'payload' => null,
                'provider' => 'none',
                'error' => 'No hay apiKey para enviar por Evo API y el webhook n8n falló.',
            ];
        }

        $serverUrl = rtrim((string) ($serverUrl ?: config('services.evoapi.base_url', 'https://evoapi.abigailsoft.com')), '/');
        $to = $this->normalizeRecipient($number);

        try {
            $response = Http::timeout((int) config('services.evoapi.timeout_seconds', 20))
                ->acceptJson()
                ->withHeaders([
                    'apiKey' => $apiKey,
                ])
                ->post("{$serverUrl}/message/sendText/{$instance}", [
                    'number' => $to,
                    'text' => $message,
                ]);

            $responseBody = $response->json();
            $messageId = $responseBody['key']['id']
                ?? $responseBody['message']['key']['id']
                ?? $responseBody['data']['key']['id']
                ?? null;

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'external_message_id' => $messageId,
                'payload' => $responseBody,
                'provider' => 'evoapi',
                'error' => $response->successful() ? null : ($responseBody['message'] ?? 'Evo API devolvio un error.'),
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'status' => 0,
                'external_message_id' => null,
                'payload' => null,
                'provider' => 'evoapi',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function resolveChannelByInstance(?string $instance): ?ChatWhatsappChannel
    {
        $resolvedInstance = $this->normalizeInstanceForLookup($instance);

        if (!$resolvedInstance) {
            return null;
        }

        $query = ChatWhatsappChannel::query()
            ->availableForOutbound()
            ->where('instance_name', $resolvedInstance);

        $channel = $query->first();

        if ($channel) {
            return $channel;
        }

        return ChatWhatsappChannel::query()
            ->availableForOutbound()
            ->whereRaw('LOWER(instance_name) = ?', [strtolower($resolvedInstance)])
            ->first();
    }

    private function dispatchViaN8nWebhook(
        string $webhookUrl,
        string $instance,
        string $apiKey,
        string $number,
        string $message,
        array $extraPayload = []
    ): array {
        try {
            $payload = array_filter(array_merge([
                'instance_name' => $instance,
                'instance_apikey' => $apiKey,
                'numero' => $this->normalizeRecipient($number),
                'mensaje' => $message,
            ], $extraPayload), fn ($value) => $value !== null);

            $response = Http::timeout((int) config('services.evoapi.timeout_seconds', 20))
                ->acceptJson()
                ->post($webhookUrl, $payload);

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'external_message_id' => null,
                'payload' => $response->json() ?? ['raw' => $response->body()],
                'provider' => 'n8n-webhook',
                'error' => $response->successful() ? null : 'Webhook n8n devolvio error.',
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'status' => 0,
                'external_message_id' => null,
                'payload' => null,
                'provider' => 'n8n-webhook',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function normalizeRecipient(string $number): string
    {
        $number = trim($number);

        if (str_contains($number, '@')) {
            return $number;
        }

        $digits = preg_replace('/\D+/', '', $number);

        return $digits . '@s.whatsapp.net';
    }

    private function normalizeInstanceForLookup(?string $instance): ?string
    {
        $raw = trim((string) $instance);

        if ($raw === '') {
            return null;
        }

        $raw = trim($raw, " \t\n\r\0\x0B\"'");
        $key = strtolower($raw);

        $aliases = (array) config('services.evoapi.instance_aliases', []);

        return $aliases[$key] ?? $raw;
    }
}
