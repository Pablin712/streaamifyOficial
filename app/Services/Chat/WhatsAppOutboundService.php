<?php

namespace App\Services\Chat;

use App\Models\ChatWhatsappChannel;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Http;

/**
 * Servicio simplificado para envío de mensajes WhatsApp
 */
class WhatsAppOutboundService
{
    /**
     * Envía mensaje de texto directo por Evolution API
     */
    public function sendText(string $number, string $message, ?string $instance, ?string $apiKey, ?string $serverUrl = null, array $options = []): array
    {
        if (! $apiKey || ! $instance) {
            return ['ok' => false, 'error' => 'No hay credenciales para Evolution API'];
        }

        return $this->sendViaEvolution($number, $message, $instance, $apiKey, $serverUrl);
    }

    /**
     * Envía media (imagen/audio/video/documento) por Evolution API
     */
    public function sendMedia(string $number, string $mediaUrl, string $mimeType, string $type, ?string $fileName, string $caption, ?string $instance, ?string $apiKey, ?string $serverUrl = null): array
    {
        if (! $apiKey || ! $instance) {
            return ['ok' => false, 'error' => 'No hay credenciales para Evolution API'];
        }

        return $this->sendMediaViaEvolution($number, $mediaUrl, $mimeType, $type, $fileName, $caption, $instance, $apiKey, $serverUrl);
    }

    /**
     * Envía una nota de voz (audio) usando el endpoint dedicado de Evolution API,
     * que convierte el archivo (mp3/wav/ogg/etc.) a Opus/OGG con ptt:true del lado
     * del servidor de Evolution. A diferencia de sendMedia, esto sí llega como nota
     * de voz reproducible nativa de WhatsApp.
     */
    public function sendVoiceNote(string $number, string $mediaUrl, ?string $instance, ?string $apiKey, ?string $serverUrl = null): array
    {
        if (! $apiKey || ! $instance) {
            return ['ok' => false, 'error' => 'No hay credenciales para Evolution API'];
        }

        return $this->sendVoiceNoteViaEvolution($number, $mediaUrl, $instance, $apiKey, $serverUrl);
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

    private function sendMediaViaEvolution(string $number, string $mediaUrl, string $mimeType, string $type, ?string $fileName, string $caption, string $instance, string $apiKey, ?string $serverUrl): array
    {
        $baseUrl = rtrim($serverUrl ?: config('services.evoapi.base_url'), '/');

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'apikey' => $apiKey,
                ])
                ->post("{$baseUrl}/message/sendMedia/{$instance}", [
                    'number' => $this->formatNumber($number),
                    'mediatype' => $this->mapMediaType($type),
                    'mimetype' => $mimeType,
                    'caption' => $caption,
                    'media' => $mediaUrl,
                    'fileName' => $fileName ?: 'archivo',
                ]);

            return [
                'ok' => $response->successful(),
                'external_message_id' => $response->json('key.id') ?? $response->json('message.key.id'),
                'status' => $response->status(),
                'error' => $response->successful() ? null : $response->body(),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendVoiceNoteViaEvolution(string $number, string $mediaUrl, string $instance, string $apiKey, ?string $serverUrl): array
    {
        $baseUrl = rtrim($serverUrl ?: config('services.evoapi.base_url'), '/');

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'apikey' => $apiKey,
                ])
                ->post("{$baseUrl}/message/sendWhatsAppAudio/{$instance}", [
                    'number' => $this->formatNumber($number),
                    'audio' => $mediaUrl,
                    'encoding' => true,
                ]);

            return [
                'ok' => $response->successful(),
                'external_message_id' => $response->json('key.id') ?? $response->json('message.key.id'),
                'status' => $response->status(),
                'error' => $response->successful() ? null : $response->body(),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function mapMediaType(string $type): string
    {
        return match ($type) {
            'imagen' => 'image',
            'audio' => 'audio',
            'video' => 'video',
            'documento', 'archivo' => 'document',
            default => 'document',
        };
    }

    private function formatNumber(string $number): string
    {
        return PhoneNumber::toWhatsappJid($number);
    }
}
