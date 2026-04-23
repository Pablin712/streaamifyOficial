<?php

namespace App\Services\Chat;

use App\Events\Chat\ChatMessageReceived;
use App\Events\Chat\ChatMessageSent;
use App\Models\ChatContactoCanal;
use App\Models\ChatMensajeCanal;
use App\Models\Cliente;
use App\Models\Conversacion;
use App\Models\Empleado;
use App\Models\Mensaje;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

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
        // 1. Validar y normalizar entrada
        $type = $this->normalizeType($payload['tipo'] ?? $payload['tipo_contenido'] ?? 'texto');
        $content = trim(($payload['mensaje'] ?? $payload['contenido'] ?? ''));
        $mediaUrl = $payload['media_url'] ?? $payload['archivo_url'] ?? null;
        $channelUserId = trim(($payload['canal_user_id'] ?? $payload['from'] ?? $payload['numero'] ?? $payload['telefono'] ?? ''));

        if (! $this->settings->isTypeAllowed($type)) {
            throw new \InvalidArgumentException("Tipo de mensaje {$type} no permitido");
        }

        if ($content === '' && ! $mediaUrl) {
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

        return DB::transaction(function () use ($payload, $type, $content, $mediaUrl, $channelUserId) {
            // 3. Obtener o crear contacto
            $contact = $this->getOrCreateContact($payload, $channelUserId);

            // 4. Obtener o crear conversación
            $conversation = $this->getOrCreateConversation($contact, $payload);

            // 5. Guardar mensaje
            $message = $this->createMessage($conversation, $type, $content, $mediaUrl, $payload);

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

        $contact = ChatContactoCanal::firstOrCreate(
            ['canal' => 'whatsapp', 'canal_user_id' => $channelUserId],
            [
                'telefono_normalizado' => $phone,
                'nombre_canal' => $payload['nombre'] ?? null,
                'idcli' => $cliente?->idcli,
                'estado_relacion' => $cliente ? 'cliente' : 'lead',
                'last_seen_at' => now(),
            ]
        );

        // Actualizar datos del contacto siempre
        $contact->update([
            'telefono_normalizado' => $phone,
            'nombre_canal' => $payload['nombre'] ?? $contact->nombre_canal,
            'idcli' => $cliente?->idcli ?? $contact->idcli,
            'estado_relacion' => $cliente ? 'cliente' : $contact->estado_relacion,
            'last_seen_at' => now(),
        ]);

        return $contact;
    }

    /**
     * Obtiene o crea una conversación
     */
    private function getOrCreateConversation(ChatContactoCanal $contact, array $payload): Conversacion
    {
        // Primero buscar conversación abierta
        $conversation = Conversacion::where('canal_contacto_id', $contact->id)
            ->whereIn('estado', ['nueva', 'abierto', 'asignado', 'atendiendo', 'en_espera'])
            ->latest('last_message_at')
            ->first();

        // Si no, buscar última cerrada
        if (! $conversation) {
            $conversation = Conversacion::where('canal_contacto_id', $contact->id)
                ->whereIn('estado', ['cerrado', 'cerrada'])
                ->latest('closed_at')
                ->first();
        }

        // Si ninguna, crear nueva
        if (! $conversation) {
            $conversation = Conversacion::create([
                'idcli' => $contact->idcli,
                'canal_principal' => 'whatsapp',
                'canal_contacto_id' => $contact->id,
                'origen' => $payload['origen'] ?? 'n8n',
                'estado' => 'nueva',
                'mensajes_no_leidos' => 0,
                'ultima_actividad' => now(),
                'last_message_at' => now(),
            ]);
        }

        return $conversation;
    }

    /**
     * Crea un mensaje y su registro de canal
     */
    private function createMessage(Conversacion $conversation, string $type, string $content, ?string $mediaUrl, array $payload): Mensaje
    {
        $message = Mensaje::create([
            'idconv' => $conversation->idconv,
            'tipo_remitente' => 'cliente',
            'idcli' => $conversation->idcli,
            'contenido' => trim(strip_tags($content)),
            'tipo_contenido' => $type,
            'tipo' => $type, // Para compatibilidad con tests existentes
            'media_url' => $mediaUrl,
            'mime_type' => $payload['mime_type'] ?? null,
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
            'media_url' => $mediaUrl,
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

        if ($file) {
            $folder = match ($type) {
                'imagen' => 'chat/images',
                'audio' => 'chat/audio',
                default => 'chat/files'
            };

            $path = $file->store($folder, 'public');
            $storedUrl = URL::asset(Storage::disk('public')->url($path));
            $mimeType = $file->getMimeType();
        }

        $content = trim(strip_tags($content));

        if ($content === '' && ! $storedUrl) {
            throw new \InvalidArgumentException('Mensaje vacío');
        }

        return DB::transaction(function () use ($conversation, $operator, $type, $content, $storedUrl, $mimeType) {
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
            ChatMensajeCanal::create([
                'idmsg' => $message->idmsg,
                'idconv' => $conversation->idconv,
                'contacto_canal_id' => $conversation->canal_contacto_id,
                'canal' => 'whatsapp',
                'direccion' => 'outbound',
                'external_status' => 'accepted',
                'media_url' => $storedUrl,
            ]);

            // 3. Enviar
            $dispatch = $this->sendMessageToWhatsApp($conversation, $type, $content, $storedUrl);

            // 4. Actualizar estado
            $message->update([
                'external_id' => $dispatch['external_message_id'] ?? null,
                'error_message' => $dispatch['ok'] ? null : $dispatch['error'],
            ]);

            // 5. Actualizar conversación
            $conversation->update([
                'estado' => 'atendiendo',
                'assigned_to' => $conversation->assigned_to ?: $operator->idemp,
                'ultimo_idemp' => $operator->idemp,
                'ultima_actividad' => now(),
                'last_message_at' => now(),
            ]);

            event(new ChatMessageSent($conversation, $message));

            return $message;
        });
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
            'image', 'imagen' => 'imagen',
            'audio', 'voice', 'ptt' => 'audio',
            'document', 'documento', 'archivo' => 'documento',
            'location', 'ubicacion' => 'ubicacion',
            default => 'texto'
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

        $digits = preg_replace('/\D/', '', (string) $phone);

        if (empty($digits)) {
            return null;
        }

        return Cliente::whereRaw("REPLACE(REPLACE(REPLACE(telefonocli, ' ', ''), '-', ''), '+', '') LIKE ?", ["%$digits"])
            ->first();
    }

    /**
     * Normaliza número de teléfono
     */
    private function normalizePhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        return preg_replace('/@.+$/', '', trim($phone));
    }

    /**
     * Envía mensaje a WhatsApp vía Evolution o n8n
     */
    private function sendMessageToWhatsApp(Conversacion $conversation, string $type, string $content, ?string $mediaUrl): array
    {
        $contacto = $conversation->contactoCanal;

        if (! $contacto) {
            return ['ok' => false, 'error' => 'Conversación sin contacto asociado'];
        }

        $number = $contacto->canal_user_id;
        $instance = $contacto->metadata['instance'] ?? null;
        $apiKey = $contacto->metadata['apikey'] ?? null;
        $serverUrl = $contacto->metadata['server_url'] ?? null;

        // Si es texto, usar servicio directo
        if ($type === 'texto') {
            return $this->outbound->sendText($number, $content, $instance, $apiKey, $serverUrl);
        }

        // Para media usar n8n
        $webhookUrl = $this->settings->get('n8n_webhook_url', config('services.n8n.client_message_webhook'));

        if (empty($webhookUrl)) {
            return ['ok' => false, 'error' => 'No configurado webhook n8n'];
        }

        try {
            $response = Http::timeout(20)->post($webhookUrl, [
                'numero' => $number,
                'mensaje' => $content,
                'tipo_contenido' => $type,
                'media_url' => $mediaUrl,
                'instance_name' => $instance,
                'instance_apikey' => $apiKey,
            ]);

            return [
                'ok' => $response->successful(),
                'external_message_id' => $response->json('key.id'),
                'error' => $response->successful() ? null : 'Error envío',
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
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
