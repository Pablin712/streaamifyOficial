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
     *
     * $quoted (opcional): ['id' => external_id del mensaje citado, 'fromMe' => bool, 'preview' => string]
     * para que el mensaje se vea como respuesta a otro, estilo WhatsApp.
     */
    public function sendText(string $number, string $message, ?string $instance, ?string $apiKey, ?string $serverUrl = null, array $options = [], ?array $quoted = null): array
    {
        if (! $apiKey || ! $instance) {
            return ['ok' => false, 'error' => 'No hay credenciales para Evolution API'];
        }

        return $this->sendViaEvolution($number, $message, $instance, $apiKey, $serverUrl, $quoted);
    }

    /**
     * Envía media (imagen/audio/video/documento) por Evolution API
     */
    public function sendMedia(string $number, string $mediaUrl, string $mimeType, string $type, ?string $fileName, string $caption, ?string $instance, ?string $apiKey, ?string $serverUrl = null, ?array $quoted = null): array
    {
        if (! $apiKey || ! $instance) {
            return ['ok' => false, 'error' => 'No hay credenciales para Evolution API'];
        }

        return $this->sendMediaViaEvolution($number, $mediaUrl, $mimeType, $type, $fileName, $caption, $instance, $apiKey, $serverUrl, $quoted);
    }

    /**
     * Envía una nota de voz (audio) usando el endpoint dedicado de Evolution API,
     * que convierte el archivo (mp3/wav/ogg/etc.) a Opus/OGG con ptt:true del lado
     * del servidor de Evolution. A diferencia de sendMedia, esto sí llega como nota
     * de voz reproducible nativa de WhatsApp.
     *
     * Se manda el audio en base64 puro (sin prefijo data:...;base64,) en vez de una
     * URL: el hosting bloquea con 403 el fetch que hace Evolution hacia nuestro
     * dominio para audio (aunque para imágenes sí funciona), así que evitamos que
     * Evolution tenga que descargar nada y viaja directo en el POST.
     */
    public function sendVoiceNote(string $number, string $base64Audio, ?string $instance, ?string $apiKey, ?string $serverUrl = null, ?array $quoted = null): array
    {
        if (! $apiKey || ! $instance) {
            return ['ok' => false, 'error' => 'No hay credenciales para Evolution API'];
        }

        return $this->sendVoiceNoteViaEvolution($number, $base64Audio, $instance, $apiKey, $serverUrl, $quoted);
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

    private function sendViaEvolution(string $number, string $message, string $instance, string $apiKey, ?string $serverUrl, ?array $quoted = null): array
    {
        $baseUrl = rtrim($serverUrl ?: config('services.evoapi.base_url'), '/');

        try {
            $response = Http::timeout(20)
                ->withHeader('apiKey', $apiKey)
                ->post("{$baseUrl}/message/sendText/{$instance}", array_merge([
                    'number' => $this->formatNumber($number),
                    'text' => $message,
                ], $this->buildQuotedPayload($quoted, $number)));

            return [
                'ok' => $response->successful(),
                'external_message_id' => $response->json('key.id') ?? $response->json('message.key.id'),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendMediaViaEvolution(string $number, string $mediaUrl, string $mimeType, string $type, ?string $fileName, string $caption, string $instance, string $apiKey, ?string $serverUrl, ?array $quoted = null): array
    {
        $baseUrl = rtrim($serverUrl ?: config('services.evoapi.base_url'), '/');

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'apikey' => $apiKey,
                ])
                ->post("{$baseUrl}/message/sendMedia/{$instance}", array_merge([
                    'number' => $this->formatNumber($number),
                    'mediatype' => $this->mapMediaType($type),
                    'mimetype' => $mimeType,
                    'caption' => $caption,
                    'media' => $mediaUrl,
                    'fileName' => $fileName ?: 'archivo',
                ], $this->buildQuotedPayload($quoted, $number)));

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

    private function sendVoiceNoteViaEvolution(string $number, string $base64Audio, string $instance, string $apiKey, ?string $serverUrl, ?array $quoted = null): array
    {
        $baseUrl = rtrim($serverUrl ?: config('services.evoapi.base_url'), '/');

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'apikey' => $apiKey,
                ])
                ->post("{$baseUrl}/message/sendWhatsAppAudio/{$instance}", array_merge([
                    'number' => $this->formatNumber($number),
                    'audio' => $base64Audio,
                    'encoding' => true,
                ], $this->buildQuotedPayload($quoted, $number)));

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

    /**
     * Arma el campo "quoted" que espera Evolution API para que un mensaje saliente
     * se vea como respuesta a otro (hilo/cita estilo WhatsApp). $quoted trae
     * ['id' => external_id del mensaje citado, 'fromMe' => bool, 'preview' => string].
     */
    private function buildQuotedPayload(?array $quoted, string $number): array
    {
        if (! $quoted || empty($quoted['id'])) {
            return [];
        }

        return [
            'quoted' => [
                'key' => [
                    'id' => $quoted['id'],
                    'remoteJid' => $this->formatNumber($number),
                    'fromMe' => (bool) ($quoted['fromMe'] ?? false),
                ],
                'message' => [
                    'conversation' => $quoted['preview'] ?? '',
                ],
            ],
        ];
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
