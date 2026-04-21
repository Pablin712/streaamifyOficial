<?php

namespace App\Services\Chat;

use App\Events\Chat\ChatMessageReceived;
use App\Events\Chat\ChatMessageSent;
use App\Models\ChatContactoCanal;
use App\Models\ChatMensajeCanal;
use App\Models\ChatWhatsappChannel;
use App\Models\Cliente;
use App\Models\Conversacion;
use App\Models\Empleado;
use App\Models\Mensaje;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class WhatsAppHelpdeskService
{
    private const OPEN_STATES = ['nueva', 'nuevo', 'abierta', 'abierto', 'asignado', 'atendiendo', 'pausado', 'en_atencion', 'en_espera', 'bot_activo'];

    public function __construct(
        private readonly ChatSettingsService $settings,
        private readonly WhatsAppOutboundService $outbound
    ) {
    }

    public function ingestInbound(array $payload): array
    {
        $type = $this->normalizeType($payload['tipo'] ?? $payload['tipo_contenido'] ?? 'texto');

        if (!$this->settings->isTypeAllowed($type)) {
            throw new \InvalidArgumentException("El tipo de mensaje {$type} no esta habilitado.");
        }

        $content = trim((string) ($payload['mensaje'] ?? $payload['contenido'] ?? ''));
        $mediaUrl = $payload['media_url'] ?? $payload['archivo_url'] ?? null;

        if ($content === '' && !$mediaUrl) {
            throw new \InvalidArgumentException('Debes enviar contenido o media_url.');
        }

        return DB::transaction(function () use ($payload, $type, $content, $mediaUrl) {
            $channel = 'whatsapp';
            $channelUserId = trim((string) ($payload['canal_user_id'] ?? $payload['from'] ?? $payload['numero'] ?? $payload['telefono'] ?? ''));

            if ($channelUserId === '') {
                throw new \InvalidArgumentException('canal_user_id, from, numero o telefono es requerido.');
            }

            $phone = $this->normalizePhone($payload['telefono'] ?? $payload['numero'] ?? $channelUserId);
            $cliente = $this->resolveCliente($payload['idcli'] ?? null, $phone);
            $whatsappChannel = $this->resolveChannel($payload['instance'] ?? null);

            $contact = ChatContactoCanal::query()->firstOrCreate(
                [
                    'canal' => $channel,
                    'canal_user_id' => $channelUserId,
                ],
                [
                    'telefono_normalizado' => $phone,
                    'nombre_canal' => $payload['nombre'] ?? null,
                    'idcli' => $cliente?->idcli,
                    'estado_relacion' => $cliente ? 'cliente' : 'lead',
                    'origen' => $payload['origen'] ?? 'n8n',
                    'metadata' => $this->channelMetadata($payload, $whatsappChannel),
                    'last_seen_at' => now(),
                ]
            );

            $contact->fill([
                'telefono_normalizado' => $phone ?: $contact->telefono_normalizado,
                'nombre_canal' => $payload['nombre'] ?? $contact->nombre_canal,
                'idcli' => $cliente?->idcli ?? $contact->idcli,
                'estado_relacion' => $cliente ? 'cliente' : $contact->estado_relacion,
                'metadata' => array_merge($contact->metadata ?? [], $this->channelMetadata($payload, $whatsappChannel)),
                'last_seen_at' => now(),
            ])->save();

            $conversation = Conversacion::query()
                ->where('canal_principal', 'whatsapp')
                ->where('canal_contacto_id', $contact->id)
                ->whereIn('estado', self::OPEN_STATES)
                ->latest('last_message_at')
                ->latest('ultima_actividad')
                ->first();

            if (!$conversation) {
                $conversation = Conversacion::query()
                    ->where('canal_principal', 'whatsapp')
                    ->where('canal_contacto_id', $contact->id)
                    ->whereIn('estado', ['cerrado', 'cerrada'])
                    ->latest('closed_at')
                    ->latest('last_message_at')
                    ->latest('ultima_actividad')
                    ->first();
            }

            if (!$conversation) {
                $conversation = Conversacion::create([
                    'idcli' => $cliente?->idcli,
                    'canal_principal' => 'whatsapp',
                    'canal_contacto_id' => $contact->id,
                    'origen' => $payload['origen'] ?? 'n8n',
                    'estado' => 'nueva',
                    'assigned_to' => null,
                    'ultima_actividad' => now(),
                    'last_message_at' => now(),
                    'mensajes_no_leidos' => 0,
                    'unread_count' => 0,
                    'prioridad' => 'normal',
                    'requiere_humano' => true,
                    'metadata' => [
                        'tags' => [],
                        'notas' => null,
                        'primer_contacto_at' => now()->toIso8601String(),
                    ] + $this->channelMetadata($payload, $whatsappChannel),
                ]);
            }

            if (!empty($payload['external_message_id'])) {
                $duplicate = ChatMensajeCanal::query()
                    ->where('canal', 'whatsapp')
                    ->where('external_message_id', $payload['external_message_id'])
                    ->first();

                if ($duplicate) {
                    return [
                        'conversation' => $conversation->fresh(),
                        'message' => $duplicate->mensaje,
                        'duplicate' => true,
                    ];
                }
            }

            $message = Mensaje::create([
                'idconv' => $conversation->idconv,
                'tipo_remitente' => 'cliente',
                'idcli' => $cliente?->idcli,
                'contenido' => $this->sanitizeText($content),
                'tipo_contenido' => $type,
                'tipo' => $type,
                'archivo_url' => $mediaUrl,
                'media_url' => $mediaUrl,
                'mime_type' => $payload['mime_type'] ?? $payload['media_mime_type'] ?? null,
                'external_id' => $payload['external_message_id'] ?? null,
                'leido' => false,
                'respondido_por_ai' => false,
                'metadata' => [
                    'origen' => 'chat_helpdesk',
                    'payload' => $payload['payload'] ?? $payload,
                ],
            ]);

            $canalMessage = ChatMensajeCanal::create([
                'idmsg' => $message->idmsg,
                'idconv' => $conversation->idconv,
                'contacto_canal_id' => $contact->id,
                'canal' => 'whatsapp',
                'direccion' => 'inbound',
                'external_message_id' => $payload['external_message_id'] ?? null,
                'external_thread_id' => $payload['external_thread_id'] ?? null,
                'external_status' => 'received',
                'media_id' => $payload['media_id'] ?? null,
                'media_url' => $mediaUrl,
                'media_mime_type' => $payload['mime_type'] ?? $payload['media_mime_type'] ?? null,
                'payload' => array_merge($payload['payload'] ?? [], $this->channelMetadata($payload, $whatsappChannel)),
            ]);

            $conversation->increment('mensajes_no_leidos');
            $conversation->increment('unread_count');
            $conversation->update([
                'idcli' => $cliente?->idcli ?? $conversation->idcli,
                'estado' => $conversation->estado === 'cerrado' || $conversation->estado === 'cerrada'
                    ? 'nueva'
                    : $conversation->estado,
                'closed_at' => $conversation->estado === 'cerrado' || $conversation->estado === 'cerrada'
                    ? null
                    : $conversation->closed_at,
                'ultima_actividad' => now(),
                'last_message_at' => now(),
                'metadata' => array_merge($conversation->metadata ?? [], $this->channelMetadata($payload, $whatsappChannel)),
            ]);

            event(new ChatMessageReceived($conversation->fresh(), $message->fresh()));

            return [
                'conversation' => $conversation->fresh(),
                'message' => $message->fresh(),
                'channel_message' => $canalMessage,
                'duplicate' => false,
            ];
        });
    }

    public function sendOperatorMessage(Conversacion $conversation, Empleado $operator, string $type, string $content = '', ?UploadedFile $file = null): Mensaje
    {
        $type = $this->normalizeType($type);

        if (!$this->settings->isTypeAllowed($type)) {
            throw new \InvalidArgumentException("El tipo de mensaje {$type} no esta habilitado.");
        }

        $storedUrl = null;
        $mimeType = null;

        if ($file) {
            $folder = match ($type) {
                'imagen' => 'chat/images',
                'audio' => 'chat/audio',
                default => 'chat/files',
            };

            $path = $file->store($folder, 'public');
            $storedUrl = url(Storage::disk('public')->url($path));
            $mimeType = $file->getMimeType();
        }

        $content = $this->sanitizeText($content);

        if ($content === '' && !$storedUrl) {
            throw new \InvalidArgumentException('No hay contenido para enviar.');
        }

        return DB::transaction(function () use ($conversation, $operator, $type, $content, $storedUrl, $mimeType) {
            $message = Mensaje::create([
                'idconv' => $conversation->idconv,
                'tipo_remitente' => 'empleado',
                'idemp' => $operator->idemp,
                'contenido' => $content,
                'tipo_contenido' => $type,
                'tipo' => $type,
                'archivo_url' => $storedUrl,
                'media_url' => $storedUrl,
                'mime_type' => $mimeType,
                'leido' => true,
                'metadata' => [
                    'origen' => 'chat_whatsapp_panel',
                ],
            ]);

            $channelMessage = ChatMensajeCanal::create([
                'idmsg' => $message->idmsg,
                'idconv' => $conversation->idconv,
                'contacto_canal_id' => $conversation->canal_contacto_id,
                'canal' => 'whatsapp',
                'direccion' => 'outbound',
                'external_status' => 'accepted',
                'media_url' => $storedUrl,
                'media_mime_type' => $mimeType,
                'payload' => [
                    'origen' => 'chat-whatsapp-panel',
                    'tipo' => $type,
                ],
            ]);

            $dispatch = $this->dispatchOutbound($conversation, $type, $content, $storedUrl, $mimeType);

            $message->update([
                'external_id' => $dispatch['external_message_id'] ?? null,
                'delivered_at' => $dispatch['ok'] ? now() : null,
                'error_message' => $dispatch['ok'] ? null : ($dispatch['error'] ?? 'No se pudo enviar el mensaje.'),
                'metadata' => array_merge($message->metadata ?? [], [
                    'dispatch' => $dispatch,
                ]),
            ]);

            $channelMessage->update([
                'external_status' => $dispatch['ok'] ? 'sent' : 'failed',
                'external_message_id' => $dispatch['external_message_id'] ?? null,
                'payload' => array_merge($channelMessage->payload ?? [], [
                    'dispatch' => $dispatch,
                ]),
            ]);

            $conversation->update([
                'estado' => 'atendiendo',
                'assigned_to' => $conversation->assigned_to ?: $operator->idemp,
                'ultimo_idemp' => $operator->idemp,
                'ultima_actividad' => now(),
                'last_message_at' => now(),
            ]);

            event(new ChatMessageSent($conversation->fresh(), $message->fresh()));

            return $message->fresh();
        });
    }

    public function assign(Conversacion $conversation, Empleado $operator, ?Empleado $target = null): void
    {
        $assignedTo = $target?->idemp ?? $operator->idemp;

        $conversation->update([
            'assigned_to' => $assignedTo,
            'ultimo_idemp' => $operator->idemp,
            'estado' => 'asignado',
            'ultima_actividad' => now(),
        ]);

        $this->systemMessage($conversation, 'Conversacion asignada a operador #' . $assignedTo, $operator);
    }

    public function close(Conversacion $conversation, Empleado $operator): void
    {
        $conversation->update([
            'estado' => 'cerrado',
            'ultimo_idemp' => $operator->idemp,
            'closed_at' => now(),
            'ultima_actividad' => now(),
        ]);

        $this->systemMessage($conversation, 'Conversacion cerrada por ' . $operator->nombreemp, $operator);
    }

    public function reopen(Conversacion $conversation, Empleado $operator): void
    {
        $conversation->update([
            'estado' => 'abierto',
            'ultimo_idemp' => $operator->idemp,
            'closed_at' => null,
            'ultima_actividad' => now(),
        ]);

        $this->systemMessage($conversation, 'Conversacion reabierta por ' . $operator->nombreemp, $operator);
    }

    public function systemMessage(Conversacion $conversation, string $content, ?Empleado $operator = null): Mensaje
    {
        return Mensaje::create([
            'idconv' => $conversation->idconv,
            'tipo_remitente' => 'sistema',
            'idemp' => $operator?->idemp,
            'contenido' => $this->sanitizeText($content),
            'tipo_contenido' => 'sistema',
            'tipo' => 'sistema',
            'leido' => true,
            'metadata' => [
                'origen' => 'chat_helpdesk',
            ],
        ]);
    }

    public function normalizeType(?string $type): string
    {
        return match (strtolower(trim((string) $type))) {
            'text', 'texto' => 'texto',
            'image', 'imagen' => 'imagen',
            'audio', 'voice', 'ptt' => 'audio',
            'document', 'documento', 'archivo' => 'documento',
            'location', 'ubicacion', 'ubicación' => 'ubicacion',
            'template', 'plantilla' => 'plantilla',
            'system', 'sistema' => 'sistema',
            default => 'texto',
        };
    }

    public function sanitizeText(string $text): string
    {
        return trim(strip_tags($text));
    }

    private function dispatchOutbound(Conversacion $conversation, string $type, string $content, ?string $mediaUrl, ?string $mimeType): array
    {
        $metadata = $conversation->metadata ?? [];
        $contactMetadata = $conversation->contactoCanal?->metadata ?? [];
        $instance = data_get($metadata, 'instance') ?? data_get($contactMetadata, 'instance');
        $apiKey = data_get($metadata, 'apikey') ?? data_get($contactMetadata, 'apikey');
        $serverUrl = data_get($metadata, 'server_url') ?? data_get($contactMetadata, 'server_url');
        $number = (string) ($conversation->contactoCanal?->canal_user_id ?? $conversation->contactoCanal?->telefono_normalizado ?? '');

        if ($type === 'texto') {
            return $this->outbound->sendText($number, $content, (string) $instance, $apiKey, $serverUrl, [
                'tipo_contenido' => 'texto',
            ]);
        }

        $webhookUrl = (string) config('services.n8n.client_message_webhook');

        if ($webhookUrl === '') {
            return [
                'ok' => false,
                'status' => 0,
                'external_message_id' => null,
                'provider' => 'none',
                'error' => 'No hay webhook n8n configurado para media.',
            ];
        }

        try {
            $response = Http::timeout((int) config('services.evoapi.timeout_seconds', 20))
                ->acceptJson()
                ->post($webhookUrl, array_filter([
                    'instance_name' => $instance,
                    'instance_apikey' => $apiKey,
                    'numero' => $number,
                    'mensaje' => $content,
                    'tipo_contenido' => $type,
                    'media_url' => $mediaUrl,
                    'mime_type' => $mimeType,
                ], fn ($value) => $value !== null && $value !== ''));

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'external_message_id' => data_get($response->json(), 'key.id'),
                'payload' => $response->json() ?? ['raw' => $response->body()],
                'provider' => 'n8n-webhook',
                'error' => $response->successful() ? null : 'Webhook n8n devolvio error.',
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'status' => 0,
                'external_message_id' => null,
                'provider' => 'n8n-webhook',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function resolveCliente(?int $idcli, ?string $phone): ?Cliente
    {
        if ($idcli) {
            return Cliente::query()->find($idcli);
        }

        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        return Cliente::query()
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(telefonocli, ' ', ''), '-', ''), '+', ''), '(', '') LIKE ?", ['%' . $digits])
            ->first();
    }

    private function resolveChannel(?string $instance): ?ChatWhatsappChannel
    {
        return $this->outbound->resolveChannelByInstance($instance);
    }

    private function channelMetadata(array $payload, ?ChatWhatsappChannel $channel): array
    {
        return array_filter([
            'instance' => $payload['instance'] ?? $channel?->instance_name,
            'apikey' => $payload['apikey'] ?? $channel?->api_key,
            'server_url' => $payload['server_url'] ?? $channel?->server_url,
            'whatsapp_channel_id' => $channel?->id,
            'whatsapp_color' => $channel?->color,
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function normalizePhone(?string $phone): ?string
    {
        $phone = trim((string) $phone);

        if ($phone === '') {
            return null;
        }

        return preg_replace('/@.+$/', '', $phone);
    }
}
