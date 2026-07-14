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
use App\Support\PhoneNumber;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Servicio simplificado para manejo de chat WhatsApp
 *
 * PRINCIPIOS APLICADOS:
 * 1. Código es simple y directo, sin abstracciones innecesarias
 * 2. Cada método hace UNA SOLA cosa
 * 3. Sin magia, sin patrones sobreingenierizados
 * 4. Fácil de entender y modificar
 */
class WhatsAppHelpdeskService
{
    public function __construct(
        private readonly ChatSettingsService $settings,
        private readonly WhatsAppOutboundService $outbound
    ) {}

    /**
     * Recibe un mensaje de WhatsApp y lo guarda
     */
    public function ingestInbound(array $payload): array
    {
        $nestedPayload = is_array($payload['payload'] ?? null) ? $payload['payload'] : [];

        // 1. Validar y normalizar entrada
        $type = $this->normalizeType(
            $payload['tipo']
                ?? $payload['tipo_contenido']
                ?? data_get($nestedPayload, 'message.type')
                ?? data_get($nestedPayload, 'type')
                ?? 'texto'
        );
        $content = trim((string) (
            $payload['mensaje']
                ?? $payload['contenido']
                ?? data_get($nestedPayload, 'message.caption')
                ?? data_get($nestedPayload, 'message.text')
                ?? data_get($nestedPayload, 'content.text')
                ?? ''
        ));
        $mediaUrl = $payload['media_url']
            ?? $payload['archivo_url']
            ?? data_get($nestedPayload, 'media_url')
            ?? data_get($nestedPayload, 'message.media_url')
            ?? data_get($nestedPayload, 'message.mediaUrl')
            ?? data_get($nestedPayload, 'message.url')
            ?? data_get($nestedPayload, 'message.image.url')
            ?? data_get($nestedPayload, 'message.audio.url')
            ?? data_get($nestedPayload, 'message.sticker.url')
            ?? null;
        $mediaBase64 = $payload['media_base64']
            ?? data_get($nestedPayload, 'media_base64')
            ?? data_get($nestedPayload, 'message.base64')
            ?? null;
        $mediaFileName = $payload['media_file_name']
            ?? data_get($nestedPayload, 'media_file_name')
            ?? data_get($nestedPayload, 'fileName')
            ?? data_get($nestedPayload, 'message.fileName')
            ?? null;
        $channelUserId = trim(($payload['canal_user_id'] ?? $payload['from'] ?? $payload['numero'] ?? $payload['telefono'] ?? ''));

        if (! $this->settings->isTypeAllowed($type)) {
            throw new \InvalidArgumentException("Tipo de mensaje {$type} no permitido");
        }

        if ($content === '' && ! $mediaUrl && ! $mediaBase64) {
            throw new \InvalidArgumentException('Mensaje vacío');
        }

        if ($channelUserId === '') {
            throw new \InvalidArgumentException('Número de teléfono requerido');
        }

        // 2. Control de duplicados (ANTES de cualquier cosa!)
        if (! empty($payload['external_message_id'])) {
            $exists = ChatMensajeCanal::where('external_message_id', $payload['external_message_id'])->first();
            if ($exists) {
                return ['duplicate' => true, 'message' => $exists->mensaje, 'conversation' => $exists->mensaje->conversacion];
            }
        }

        return DB::transaction(function () use ($payload, $type, $content, $mediaUrl, $mediaBase64, $mediaFileName, $channelUserId) {
            // 3. Obtener o crear contacto
            $contact = $this->getOrCreateContact($payload, $channelUserId);

            // 4. Obtener o crear conversación
            $conversation = $this->getOrCreateConversation($contact, $payload);

            // 5. Guardar mensaje
            $message = $this->createMessage($conversation, $type, $content, $mediaUrl, $payload, $mediaBase64, $mediaFileName);

            // 6. Actualizar contadores y estado
            $this->updateConversationStats($conversation, $payload);

            // 7. Disparar evento
            event(new ChatMessageReceived($conversation, $message));

            return [
                'conversation' => $conversation,
                'message' => $message,
                'duplicate' => false,
            ];
        });
    }

    /**
     * Obtiene o crea un contacto
     */
    private function getOrCreateContact(array $payload, string $channelUserId): ChatContactoCanal
    {
        $phone = $this->normalizePhone($payload['telefono'] ?? $payload['numero'] ?? $channelUserId);
        $cliente = $this->findClienteByPhone($phone, $payload['idcli'] ?? null);
        $channel = $this->resolveChannelFromPayload($payload);
        $metadata = $this->buildChannelMetadata($payload, $channel);

        $contact = ChatContactoCanal::query()
            ->where('canal', 'whatsapp')
            ->where(function ($query) use ($channelUserId, $phone) {
                $query->where('canal_user_id', $channelUserId)
                    ->orWhere('canal_user_id', $phone)
                    ->orWhere('telefono_normalizado', $phone);
            })
            ->first();

        if (! $contact) {
            $contact = ChatContactoCanal::create([
                'canal' => 'whatsapp',
                'canal_user_id' => $channelUserId,
                'telefono_normalizado' => $phone,
                'nombre_canal' => $payload['nombre'] ?? null,
                'idcli' => $cliente?->idcli,
                'estado_relacion' => $cliente ? 'cliente' : 'lead',
                'metadata' => $metadata,
                'last_seen_at' => now(),
            ]);
        }

        // Actualizar datos del contacto siempre
        $contact->update([
            'canal_user_id' => $channelUserId,
            'telefono_normalizado' => $phone,
            'nombre_canal' => $payload['nombre'] ?? $contact->nombre_canal,
            'idcli' => $cliente?->idcli ?? $contact->idcli,
            'estado_relacion' => $cliente ? 'cliente' : $contact->estado_relacion,
            'metadata' => array_merge($contact->metadata ?? [], $metadata),
            'last_seen_at' => now(),
        ]);

        return $contact;
    }

    /**
     * Obtiene o crea una conversación
     * Garantiza UN SOLO registro por canal_contacto_id (evita duplicados)
     */
    private function getOrCreateConversation(ChatContactoCanal $contact, array $payload): Conversacion
    {
        // Usar firstOrCreate para garantizar unicidad: UN solo registro por contacto
        $conversation = Conversacion::firstOrCreate(
            ['canal_contacto_id' => $contact->id],
            [
                'idcli' => $contact->idcli,
                'canal_principal' => 'whatsapp',
                'origen' => $payload['origen'] ?? 'n8n',
                'estado' => 'nueva',
                'mensajes_no_leidos' => 0,
                'ultima_actividad' => now(),
                'last_message_at' => now(),
            ]
        );

        // Si la conversación estaba cerrada y recibe un nuevo mensaje, reabrir
        if (in_array($conversation->estado, ['cerrado', 'cerrada', 'resuelto'])) {
            $conversation->update([
                'estado' => 'abierto',
                'ultima_actividad' => now(),
                'last_message_at' => now(),
                'closed_at' => null,
            ]);
        }

        return $conversation;
    }

    /**
     * Crea un mensaje y su registro de canal
     */
    private function createMessage(Conversacion $conversation, string $type, string $content, ?string $mediaUrl, array $payload, ?string $mediaBase64 = null, ?string $mediaFileName = null): Mensaje
    {
        [$storedMediaUrl, $resolvedMimeType] = $this->persistInboundMedia(
            $mediaUrl,
            $type,
            $payload['media_mime_type']
                ?? $payload['mime_type']
                ?? data_get($payload, 'payload.message.mimetype')
                ?? data_get($payload, 'payload.message.mime_type')
                ?? data_get($payload, 'payload.mime_type')
                ?? null,
            $mediaBase64,
            $mediaFileName
        );

        $message = Mensaje::create([
            'idconv' => $conversation->idconv,
            'tipo_remitente' => 'cliente',
            'idcli' => $conversation->idcli,
            'contenido' => trim(strip_tags($content)),
            'tipo_contenido' => $type,
            'tipo' => $type, // Para compatibilidad con tests existentes
            'archivo_url' => $storedMediaUrl,
            'media_url' => $storedMediaUrl,
            'mime_type' => $resolvedMimeType,
            'external_id' => $payload['external_message_id'] ?? null,
            'leido' => false,
        ]);

        ChatMensajeCanal::create([
            'idmsg' => $message->idmsg,
            'idconv' => $conversation->idconv,
            'contacto_canal_id' => $conversation->canal_contacto_id,
            'canal' => 'whatsapp',
            'direccion' => 'inbound',
            'external_message_id' => $payload['external_message_id'] ?? null,
            'external_status' => 'received',
            'media_url' => $storedMediaUrl,
            'media_mime_type' => $resolvedMimeType,
            'payload' => [
                'media_original_url' => $mediaUrl,
            ],
        ]);

        return $message;
    }

    /**
     * Actualiza estadísticas de la conversación
     */
    private function updateConversationStats(Conversacion $conversation, array $payload): void
    {
        $wasClosed = in_array($conversation->estado, ['cerrado', 'cerrada']);

        $conversation->update([
            'mensajes_no_leidos' => $conversation->mensajes_no_leidos + 1,
            'unread_count' => $conversation->unread_count + 1,
            'estado' => $wasClosed ? 'nueva' : $conversation->estado,
            'closed_at' => $wasClosed ? null : $conversation->closed_at,
            'ultima_actividad' => now(),
            'last_message_at' => now(),
        ]);
    }

    /**
     * Envía un mensaje de operador al usuario
     */
    public function sendOperatorMessage(Conversacion $conversation, Empleado $operator, string $type, string $content = '', ?UploadedFile $file = null): Mensaje
    {
        $type = $this->normalizeType($type);

        if (! $this->settings->isTypeAllowed($type)) {
            throw new \InvalidArgumentException('Tipo de mensaje no permitido');
        }

        // Manejar archivos
        $storedUrl = null;
            $mimeType = null;
            $fileName = null;

        if ($file) {
            $folder = match ($type) {
                'imagen' => 'chat/images',
                'audio' => 'chat/audio',
                default => 'chat/files'
            };

            $path = $file->store($folder, 'public');
            $storedUrl = asset(Storage::url($path));
            $mimeType = $file->getMimeType();
            $fileName = $file->getClientOriginalName();
        }

        $content = trim(strip_tags($content));

        if ($content === '' && ! $storedUrl) {
            throw new \InvalidArgumentException('Mensaje vacío');
        }

        $result = DB::transaction(function () use ($conversation, $operator, $type, $content, $storedUrl, $mimeType, $fileName) {
            // 1. Crear mensaje
            $message = Mensaje::create([
                'idconv' => $conversation->idconv,
                'tipo_remitente' => 'empleado',
                'idemp' => $operator->idemp,
                'contenido' => $content,
                'tipo_contenido' => $type,
                'tipo' => $type, // Para compatibilidad con tests existentes
                'media_url' => $storedUrl,
                'mime_type' => $mimeType,
                'leido' => true,
            ]);

            // 2. Crear registro de canal
            $canalMensaje = ChatMensajeCanal::create([
                'idmsg' => $message->idmsg,
                'idconv' => $conversation->idconv,
                'contacto_canal_id' => $conversation->canal_contacto_id,
                'canal' => 'whatsapp',
                'direccion' => 'outbound',
                'external_status' => 'accepted',
                'media_url' => $storedUrl,
                'payload' => [
                    'dispatch' => [
                        'queued_at' => now()->toIso8601String(),
                    ],
                ],
            ]);

            // 3. Actualizar conversación
            $conversation->update([
                'estado' => 'atendiendo',
                'assigned_to' => $conversation->assigned_to ?: $operator->idemp,
                'ultimo_idemp' => $operator->idemp,
                'ultima_actividad' => now(),
                'last_message_at' => now(),
            ]);

            event(new ChatMessageSent($conversation, $message));

            return [
                'message' => $message,
                'conversation_id' => (int) $conversation->idconv,
                'canal_mensaje_id' => (int) $canalMensaje->id,
                'type' => $type,
                'content' => $content,
                'media_url' => $storedUrl,
                // URL firmada servida por Laravel: el hosting bloquea /storage directo,
                // así que Evolution API necesita esta ruta para poder descargar el archivo.
                'external_media_url' => $storedUrl ? $message->media_playable_url : null,
                'mime_type' => $mimeType,
                'file_name' => $fileName,
            ];
        });

        app()->terminating(function () use ($result) {
            $conversation = Conversacion::query()->with('contactoCanal')->find($result['conversation_id']);
            $canalMensaje = ChatMensajeCanal::query()->find($result['canal_mensaje_id']);

            if (!$conversation || !$canalMensaje) {
                return;
            }

            $dispatch = $this->sendMessageToWhatsApp(
                $conversation,
                $result['type'],
                $result['content'],
                $result['external_media_url'],
                $result['mime_type'],
                $result['file_name']
            );

            $dispatchOk = (bool) ($dispatch['ok'] ?? false);

            $canalMensaje->update([
                'external_status' => $dispatchOk ? 'sent' : 'failed',
                'external_message_id' => $dispatch['external_message_id'] ?? $canalMensaje->external_message_id,
                'payload' => array_merge($canalMensaje->payload ?? [], [
                    'dispatch' => [
                        'ok' => $dispatchOk,
                        'status' => $dispatch['status'] ?? null,
                        'error' => $dispatch['error'] ?? null,
                        'sent_at' => now()->toIso8601String(),
                    ],
                ]),
            ]);

            $result['message']->update([
                'external_id' => $dispatch['external_message_id'] ?? null,
                'error_message' => $dispatchOk ? null : ($dispatch['error'] ?? 'Error enviando WhatsApp.'),
            ]);
        });

        return $result['message'];
    }

    /**
     * Asigna conversación a operador
     */
    public function assign(Conversacion $conversation, Empleado $operator, ?Empleado $target = null): void
    {
        $conversation->update([
            'assigned_to' => $target?->idemp ?? $operator->idemp,
            'ultimo_idemp' => $operator->idemp,
            'estado' => 'asignado',
            'ultima_actividad' => now(),
        ]);

        $this->addSystemMessage($conversation, 'Asignada a operador', $operator);
    }

    /**
     * Cierra conversación
     */
    public function close(Conversacion $conversation, Empleado $operator): void
    {
        $conversation->update([
            'estado' => 'cerrado',
            'ultimo_idemp' => $operator->idemp,
            'closed_at' => now(),
            'ultima_actividad' => now(),
        ]);

        $this->addSystemMessage($conversation, "Cerrada por {$operator->nombreemp}", $operator);
    }

    /**
     * Reabre conversación
     */
    public function reopen(Conversacion $conversation, Empleado $operator): void
    {
        $conversation->update([
            'estado' => 'abierto',
            'ultimo_idemp' => $operator->idemp,
            'closed_at' => null,
            'ultima_actividad' => now(),
        ]);

        $this->addSystemMessage($conversation, "Reabierta por {$operator->nombreemp}", $operator);
    }

    // --- Métodos auxiliares simples ---

    /**
     * Normaliza tipo de mensaje
     */
    public function normalizeType(?string $type): string
    {
        return match (strtolower(trim((string) $type))) {
            'text', 'conversation', 'texto' => 'texto',
            'image', 'imagemessage', 'imagen' => 'imagen',
            'audio', 'voice', 'ptt', 'audiomessage' => 'audio',
            'sticker', 'stickermessage' => 'sticker',
            'video', 'videomessage' => 'video',
            'document', 'documentmessage', 'documento', 'archivo', 'file' => 'documento',
            'location', 'ubicacion' => 'ubicacion',
            default => 'texto'
        };
    }

    private function persistInboundMedia(?string $mediaUrl, string $type, ?string $mimeType, ?string $mediaBase64 = null, ?string $mediaFileName = null): array
    {
        $base64 = trim((string) $mediaBase64);

        if ($base64 !== '') {
            return $this->storeInboundMediaFromBase64($base64, $type, $mimeType, $mediaFileName);
        }

        $url = trim((string) $mediaUrl);

        if ($url === '') {
            return [null, $mimeType];
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [$url, $mimeType];
        }

        try {
            $response = Http::timeout(20)->get($url);

            if (! $response->successful()) {
                return [$url, $mimeType];
            }

            $resolvedMime = $this->normalizeInboundMimeType($mimeType ?: (string) $response->header('Content-Type'));
            $extension = $this->resolveInboundMediaExtension($resolvedMime, $url, $type);
            $folder = match ($type) {
                'imagen' => 'chat/inbound/images',
                'audio' => 'chat/inbound/audio',
                'sticker' => 'chat/inbound/stickers',
                default => 'chat/inbound/files',
            };

            $filename = now()->format('YmdHis') . '_' . Str::random(10) . '.' . $extension;
            $path = $folder . '/' . $filename;

            Storage::disk('public')->put($path, $response->body());

            return [Storage::url($path), $resolvedMime];
        } catch (\Throwable) {
            return [$url, $mimeType];
        }
    }

    private function resolveInboundMediaExtension(?string $mimeType, string $sourceUrl, string $type): string
    {
        $lowerMime = strtolower((string) $mimeType);

        if ($lowerMime !== '') {
            if (str_contains($lowerMime, 'image/webp')) {
                return 'webp';
            }

            if (str_contains($lowerMime, 'image/')) {
                return 'jpg';
            }

            if (str_contains($lowerMime, 'audio/ogg')) {
                return 'ogg';
            }

            if (str_contains($lowerMime, 'audio/mpeg')) {
                return 'mp3';
            }

            if (str_contains($lowerMime, 'audio/')) {
                return 'mp3';
            }
        }

        $path = parse_url($sourceUrl, PHP_URL_PATH);
        $extension = strtolower((string) pathinfo((string) $path, PATHINFO_EXTENSION));

        if ($extension !== '') {
            return $extension;
        }

        return match ($type) {
            'imagen', 'sticker' => 'jpg',
            'audio' => 'mp3',
            default => 'bin',
        };
    }

    private function storeInboundMediaFromBase64(string $base64, string $type, ?string $mimeType, ?string $fileName = null): array
    {
        $normalized = preg_replace('/^data:[^;]+;base64,/', '', trim($base64));
        // n8n/form-urlencoded puede convertir '+' en espacios y agregar saltos de linea.
        $normalized = str_replace(' ', '+', (string) $normalized);
        $normalized = preg_replace('/\s+/', '', (string) $normalized);
        if (str_contains((string) $normalized, '%2B') || str_contains((string) $normalized, '%2F') || str_contains((string) $normalized, '%3D')) {
            $normalized = urldecode((string) $normalized);
            $normalized = preg_replace('/\s+/', '', (string) $normalized);
        }
        $binary = base64_decode($normalized, true);

        if ($binary === false) {
            return [null, $mimeType];
        }

        $extension = $this->resolveBase64MediaExtension($mimeType, $fileName, $type);
        $folder = match ($type) {
            'imagen' => 'chat/inbound/images',
            'audio' => 'chat/inbound/audio',
            'sticker' => 'chat/inbound/stickers',
            default => 'chat/inbound/files',
        };

        $filename = now()->format('YmdHis') . '_' . Str::random(10) . '.' . $extension;
        $path = $folder . '/' . $filename;
        Storage::disk('public')->put($path, $binary);

        return [Storage::url($path), $this->normalizeInboundMimeType($mimeType)];
    }

    private function resolveBase64MediaExtension(?string $mimeType, ?string $fileName, string $type): string
    {
        $nameExtension = strtolower((string) pathinfo((string) $fileName, PATHINFO_EXTENSION));

        if ($nameExtension !== '') {
            return match ($nameExtension) {
                'oga', 'opus' => 'ogg',
                default => $nameExtension,
            };
        }

        return $this->resolveInboundMediaExtension($mimeType, (string) $fileName, $type);
    }

    private function normalizeInboundMimeType(?string $mimeType): ?string
    {
        $normalized = strtolower(trim((string) $mimeType));

        if ($normalized === '') {
            return null;
        }

        $baseMimeType = trim(strtok($normalized, ';'));

        return match ($baseMimeType) {
            'audio/ogg', 'audio/opus', 'application/ogg' => 'audio/ogg',
            default => $baseMimeType,
        };
    }

    /**
     * Busca cliente por teléfono
     */
    private function findClienteByPhone(?string $phone, ?int $idcli = null): ?Cliente
    {
        if ($idcli) {
            return Cliente::find($idcli);
        }

        return Cliente::buscarPorTelefonoNormalizado($phone);
    }

    /**
     * Normaliza número de teléfono
     */
    private function normalizePhone(?string $phone): ?string
    {
        return PhoneNumber::canonicalEc($phone);
    }

    private function resolveChannelFromPayload(array $payload): ?ChatWhatsappChannel
    {
        $instance = trim((string) ($payload['instance'] ?? $payload['instance_name'] ?? ''));
        $apiKey = trim((string) ($payload['apikey'] ?? $payload['instance_apikey'] ?? ''));
        $serverUrl = trim((string) ($payload['server_url'] ?? config('services.evoapi.base_url')));

        if ($instance !== '' && $apiKey !== '') {
            ChatWhatsappChannel::query()->updateOrCreate(
                ['instance_name' => $instance],
                [
                    'display_name' => $payload['display_name'] ?? $instance,
                    'api_key' => $apiKey,
                    'server_url' => $serverUrl !== '' ? $serverUrl : config('services.evoapi.base_url'),
                    'color' => in_array(($payload['color'] ?? null), ['verde', 'azul', 'otro'], true)
                        ? $payload['color']
                        : (strtolower($instance) === 'bot-pagos' ? 'verde' : 'azul'),
                    'is_active' => true,
                    'outbound_enabled' => true,
                    'metadata' => [
                        'source' => 'helpdesk-auto-sync',
                        'last_seen_at' => now()->toIso8601String(),
                    ],
                ]
            );
        }

        return $this->outbound->resolveChannelByInstance($instance !== '' ? $instance : null);
    }

    private function buildChannelMetadata(array $payload, ?ChatWhatsappChannel $channel): array
    {
        return array_filter([
            'instance' => $payload['instance'] ?? $payload['instance_name'] ?? $channel?->instance_name,
            'apikey' => $payload['apikey'] ?? $payload['instance_apikey'] ?? $channel?->api_key,
            'server_url' => $payload['server_url'] ?? $channel?->server_url,
            'whatsapp_channel_id' => $channel?->id,
            'whatsapp_color' => $payload['color'] ?? $channel?->color,
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * Envía mensaje a WhatsApp vía Evolution o n8n
     */
    private function sendMessageToWhatsApp(Conversacion $conversation, string $type, string $content, ?string $mediaUrl, ?string $mimeType = null, ?string $fileName = null): array
    {
        $contacto = $conversation->contactoCanal;

        if (! $contacto) {
            return ['ok' => false, 'error' => 'Conversación sin contacto asociado'];
        }

            $contactMetadata = is_array($contacto->metadata) ? $contacto->metadata : [];
            $conversationMetadata = is_array($conversation->metadata) ? $conversation->metadata : [];

            $number = (string) ($contacto->canal_user_id ?: $contacto->telefono_normalizado ?: '');
            $instance = $contactMetadata['instance']
                ?? $conversationMetadata['instance']
                ?? null;
            $apiKey = $contactMetadata['apikey']
                ?? $conversationMetadata['apikey']
                ?? null;
            $serverUrl = $contactMetadata['server_url']
                ?? $conversationMetadata['server_url']
                ?? null;

            $channelId = $contactMetadata['whatsapp_channel_id']
                ?? $conversationMetadata['whatsapp_channel_id']
                ?? null;

            $channel = null;
            if ($channelId) {
                $channel = ChatWhatsappChannel::query()->find($channelId);
            }

            if (! $channel && $instance) {
                $channel = $this->outbound->resolveChannelByInstance((string) $instance);
            }

            $instance = $instance ?: $channel?->instance_name;
            $apiKey = $apiKey ?: $channel?->api_key;
            $serverUrl = $serverUrl ?: $channel?->server_url;

            if ($number === '') {
                return ['ok' => false, 'error' => 'No hay número destino en el contacto.'];
            }

            // Si es texto (o no hay media disponible), enviar directo por Evo API
        if ($type === 'texto' || ! $mediaUrl) {
            return $this->outbound->sendText($number, $content, $instance, $apiKey, $serverUrl);
        }

            // Media: enviar el archivo real por Evo API (imagen/audio/video/documento)
            return $this->outbound->sendMedia(
                $number,
                $mediaUrl,
                $mimeType ?: 'application/octet-stream',
                $type,
                $fileName,
                $content,
                $instance,
                $apiKey,
                $serverUrl
            );
    }

    /**
     * Agrega mensaje de sistema
     */
    private function addSystemMessage(Conversacion $conversation, string $text, ?Empleado $operator = null): Mensaje
    {
        return Mensaje::create([
            'idconv' => $conversation->idconv,
            'tipo_remitente' => 'sistema',
            'idemp' => $operator?->idemp,
            'contenido' => $text,
            'tipo_contenido' => 'sistema',
            'leido' => true,
        ]);
    }
}
