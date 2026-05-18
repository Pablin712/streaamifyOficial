<?php

namespace App\Livewire\Chat;

use Carbon\Carbon;
use App\Models\Chat\ChatSetting;
use App\Models\ChatMemoriaContacto;
use App\Models\ChatMemoriaNegocio;
use App\Models\ChatMemoriaResumen;
use App\Models\ChatMensajeCanal;
use App\Models\ChatContactoCanal;
use App\Models\ChatWhatsappChannel;
use App\Models\Conversacion;
use App\Models\Empleado;
use App\Models\Mensaje;
use App\Models\QuickResponse;
use App\Services\Chat\ChatSettingsService;
use App\Services\Chat\WhatsAppHelpdeskService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class WhatsAppHelpdesk extends Component
{
    use WithFileUploads;

    public string $filter = 'todos';

    public string $search = '';

    public ?int $activeConversationId = null;

    public string $messageText = '';

    public ?TemporaryUploadedFile $imageUpload = null;

    public ?TemporaryUploadedFile $audioUpload = null;

    public string $mobilePane = 'list';

    public bool $showSettingsModal = false;

    public ?int $editingChannelId = null;

    public string $channelInstanceName = '';

    public string $channelDisplayName = '';

    public string $channelApiKey = '';

    public string $channelServerUrl = '';

    public string $channelColor = 'otro';

    public bool $channelIsActive = true;

    public bool $channelOutboundEnabled = true;

    public int $lastUnreadConversations = 0;

    public ?string $lastActiveMessageFingerprint = null;

    public ?string $settingsNotice = null;

    public ?int $editingQuickResponseId = null;

    public string $quickResponseCommand = '';

    public string $quickResponseTitle = '';

    public string $quickResponseContent = '';

    public int $quickResponseOrder = 0;

    public bool $quickResponseActive = true;

    public array $paginators = [];

    public int $conversationsLimit = 25;

    public int $messagesLimit = 80;

    public string $activeMessageSearch = '';

    public function mount(): void
    {
        abort_if(Gate::denies('chat.ver'), 403, 'No tienes permiso para acceder al chat.');
        $this->lastUnreadConversations = $this->unreadConversationsCount();
        $this->lastActiveMessageFingerprint = $this->activeConversationMessageFingerprint();
    }

    public function render()
    {
        $settings = app(ChatSettingsService::class)->all();
        $activeConversation = $this->activeConversation();
        $activeMessages = $this->conversationMessages($activeConversation);
        $activeContactIdentity = $this->contactIdentity($activeConversation);
        $conversationsData = $this->conversationList();

        return view('livewire.chat.whatsapp-helpdesk', [
            'conversations' => $conversationsData['items'],
            'conversationsHasMore' => $conversationsData['has_more'],
            'conversationsLoaded' => $conversationsData['loaded'],
            'activeConversation' => $activeConversation,
            'messages' => $activeMessages['items'],
            'messagesHasMore' => $activeMessages['has_more'],
            'messagesLoaded' => $activeMessages['loaded'],
            'activeContactIdentity' => $activeContactIdentity,
            'clientActiveUsers' => $this->clientActiveUsersForConversation($activeConversation),
            'quickResponseSuggestions' => $this->quickResponseSuggestions(),
            'quickResponses' => QuickResponse::query()->orderBy('orden')->orderBy('comando')->get(),
            'operators' => Empleado::query()->orderBy('nombreemp')->get(['idemp', 'nombreemp']),
            'settings' => $settings,
            'whatsappChannels' => ChatWhatsappChannel::query()
                ->orderByDesc('is_active')
                ->orderBy('instance_name')
                ->get(),
        ]);
    }

    public function updatingSearch(): void
    {
        $this->conversationsLimit = 25;
    }

    public function updatingFilter(): void
    {
        $this->conversationsLimit = 25;
    }

    public function selectConversation(int $conversationId): void
    {
        $conversation = Conversacion::query()
            ->where('canal_principal', 'whatsapp')
            ->findOrFail($conversationId);

        $conversation->marcarComoLeida();

        $this->activeConversationId = $conversation->idconv;
        $this->messagesLimit = 80;
        $this->activeMessageSearch = '';
        $this->mobilePane = 'chat';
        $this->lastUnreadConversations = $this->unreadConversationsCount();
        $this->lastActiveMessageFingerprint = $this->conversationMessageFingerprint($conversation);
        $this->dispatch('chat-scroll-bottom');
    }

    public function updatingActiveMessageSearch(): void
    {
        $this->messagesLimit = 120;
    }

    public function loadOlderMessages(): void
    {
        $this->requireConversation();
        $this->messagesLimit = min(600, $this->messagesLimit + 80);
    }

    public function loadMoreConversations(): void
    {
        $this->conversationsLimit = min(500, $this->conversationsLimit + 25);
    }

    public function contactIdentity(?Conversacion $conversation): array
    {
        if (! $conversation) {
            return [
                'type' => 'desconocido',
                'label' => 'Desconocido',
                'tone' => 'muted',
            ];
        }

        $contact = $conversation->contactoCanal;
        $conversationMeta = (array) ($conversation->metadata ?? []);
        $contactMeta = (array) ($contact?->metadata ?? []);

        $declaredType = strtolower(trim((string) (
            $conversationMeta['tipo_contacto']
            ?? $contactMeta['tipo_contacto']
            ?? $conversationMeta['contact_type']
            ?? $contactMeta['contact_type']
            ?? $conversationMeta['role']
            ?? $contactMeta['role']
            ?? ''
        )));

        if (str_contains($declaredType, 'proveedor') || str_contains($declaredType, 'provider')) {
            return [
                'type' => 'proveedor',
                'label' => 'Proveedor',
                'tone' => 'warning',
            ];
        }

        if ($conversation->idcli || $contact?->idcli) {
            return [
                'type' => 'cliente',
                'label' => 'Cliente',
                'tone' => 'success',
            ];
        }

        if (str_contains($declaredType, 'proveedor') || str_contains($declaredType, 'provider')) {
            return [
                'type' => 'proveedor',
                'label' => 'Proveedor',
                'tone' => 'warning',
            ];
        }

        $channelUserId = strtolower(trim((string) ($contact?->canal_user_id ?? '')));
        if (str_ends_with($channelUserId, '@g.us') || str_contains($declaredType, 'grupo') || str_contains($declaredType, 'group')) {
            return [
                'type' => 'grupo',
                'label' => 'Grupo',
                'tone' => 'info',
            ];
        }

        $origin = strtolower(trim((string) ($conversation->origen ?? $contact?->origen ?? '')));
        $isBot = (bool) ($conversationMeta['is_bot'] ?? $contactMeta['is_bot'] ?? false);
        if ($isBot || str_contains($declaredType, 'bot') || str_contains($origin, 'bot')) {
            return [
                'type' => 'bot',
                'label' => 'Bot',
                'tone' => 'bot',
            ];
        }

        return [
            'type' => 'desconocido',
            'label' => 'No clasificado',
            'tone' => 'muted',
        ];
    }

    public function setContactType(string $type): void
    {
        abort_if(Gate::denies('chat.ver'), 403, 'No tienes permiso para clasificar contactos.');
        $this->requireConversation();

        abort_unless(in_array($type, ['cliente', 'proveedor', 'grupo', 'bot', 'desconocido'], true), 422, 'Tipo de contacto no valido.');

        $conversation = $this->activeConversation();
        $contact = $conversation?->contactoCanal;

        if (! $conversation) {
            return;
        }

        $conversationMetadata = array_merge((array) ($conversation->metadata ?? []), [
            'tipo_contacto' => $type,
        ]);

        $conversation->update([
            'metadata' => $conversationMetadata,
        ]);

        if ($contact) {
            $contactMetadata = array_merge((array) ($contact->metadata ?? []), [
                'tipo_contacto' => $type,
            ]);

            $contact->update([
                'metadata' => $contactMetadata,
            ]);
        }

        $identity = $this->contactIdentity($conversation->fresh(['contactoCanal']));
        $this->settingsNotice = 'Contacto clasificado como '.$identity['label'].'.';
    }

    public function highlightMessageContent(?string $content): string
    {
        $value = (string) $content;

        if ($value === '') {
            return '';
        }

        $escaped = e($value);
        $term = trim($this->activeMessageSearch);

        if ($term === '') {
            return nl2br($escaped);
        }

        $pattern = '/('.preg_quote($term, '/').')/iu';

        return nl2br((string) preg_replace($pattern, '<mark class="wa-highlight">$1</mark>', $escaped));
    }

    public function backToList(): void
    {
        $this->mobilePane = 'list';
    }

    public function refreshChat(): void
    {
        $currentUnread = $this->unreadConversationsCount();

        if ($currentUnread > $this->lastUnreadConversations) {
            $this->dispatch('chat-notification-sound');
        }

        $this->lastUnreadConversations = $currentUnread;

        if ($this->activeConversationId) {
            $currentFingerprint = $this->activeConversationMessageFingerprint();

            if ($currentFingerprint !== $this->lastActiveMessageFingerprint) {
                $this->lastActiveMessageFingerprint = $currentFingerprint;
                $this->dispatch('chat-scroll-bottom');
            }
        }
    }

    private function activeConversationMessageFingerprint(): ?string
    {
        $conversation = $this->activeConversation();

        if (! $conversation) {
            return null;
        }

        return $this->conversationMessageFingerprint($conversation);
    }

    private function conversationMessageFingerprint(Conversacion $conversation): ?string
    {
        $latestMessage = $conversation->mensajes()
            ->select(['idmsg', 'updated_at', 'created_at'])
            ->latest('idmsg')
            ->first();

        if (! $latestMessage) {
            return 'conversation:'.$conversation->idconv.':empty';
        }

        $timestamp = $latestMessage->updated_at ?? $latestMessage->created_at;

        return implode(':', [
            'conversation',
            $conversation->idconv,
            $latestMessage->idmsg,
            optional($timestamp)->toISOString(),
        ]);
    }

    public function markTyping(): void
    {
        $conversation = $this->activeConversation();
        $operator = $this->operator();

        if (! $conversation || ! $operator) {
            return;
        }

        $conversation->update([
            'operator_typing_id' => $operator->idemp,
            'operator_typing_at' => now(),
        ]);
    }

    public function takeConversation(): void
    {
        abort_if(Gate::denies('chat.responder'), 403, 'No tienes permiso para tomar conversaciones.');
        $this->requireConversation();

        app(WhatsAppHelpdeskService::class)->assign($this->activeConversation(), $this->operator());
    }

    public function assignTo(int $operatorId): void
    {
        abort_if(Gate::denies('chat.supervisor'), 403, 'No tienes permiso para asignar conversaciones.');
        $this->requireConversation();

        $target = Empleado::query()->findOrFail($operatorId);
        app(WhatsAppHelpdeskService::class)->assign($this->activeConversation(), $this->operator(), $target);
    }

    public function closeConversation(): void
    {
        abort_if(Gate::denies('chat.responder'), 403, 'No tienes permiso para cerrar conversaciones.');
        $this->requireConversation();

        app(WhatsAppHelpdeskService::class)->close($this->activeConversation(), $this->operator());
    }

    public function reopenConversation(): void
    {
        abort_if(Gate::denies('chat.responder'), 403, 'No tienes permiso para reabrir conversaciones.');
        $this->requireConversation();

        app(WhatsAppHelpdeskService::class)->reopen($this->activeConversation(), $this->operator());
    }

    public function saveSetting(string $key, mixed $value): void
    {
        abort_if(Gate::denies('chat.responder'), 403, 'No tienes permiso para modificar ajustes.');

        ChatSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => is_bool($value) ? 'bool' : (is_numeric($value) ? 'int' : 'string')]
        );

        Cache::forget('chat.settings');
    }

    public function saveChannel(): void
    {
        abort_if(Gate::denies('chat.supervisor') && Gate::denies('chat.responder'), 403, 'No tienes permiso para administrar instancias.');

        $data = $this->validate([
            'channelInstanceName' => [
                'required',
                'string',
                'max:120',
                Rule::unique('chat_whatsapp_channels', 'instance_name')->ignore($this->editingChannelId),
            ],
            'channelDisplayName' => ['nullable', 'string', 'max:120'],
            'channelApiKey' => ['required', 'string', 'max:191'],
            'channelServerUrl' => ['nullable', 'string', 'max:191'],
            'channelColor' => ['required', Rule::in(['verde', 'azul', 'otro'])],
            'channelIsActive' => ['boolean'],
            'channelOutboundEnabled' => ['boolean'],
        ]);

        $channel = $this->editingChannelId
            ? ChatWhatsappChannel::query()->findOrFail($this->editingChannelId)
            : new ChatWhatsappChannel();

        $channel->instance_name = trim($data['channelInstanceName']);
        $channel->display_name = trim((string) $data['channelDisplayName']) !== ''
            ? trim((string) $data['channelDisplayName'])
            : trim($data['channelInstanceName']);
        $channel->api_key = $data['channelApiKey'];
        $channel->server_url = trim((string) $data['channelServerUrl']) !== ''
            ? trim((string) $data['channelServerUrl'])
            : (string) config('services.evoapi.base_url');
        $channel->color = $data['channelColor'];
        $channel->is_active = (bool) $data['channelIsActive'];
        $channel->outbound_enabled = (bool) $data['channelOutboundEnabled'];
        $channel->save();

        $this->resetChannelForm();
    }

    public function editChannel(int $channelId): void
    {
        abort_if(Gate::denies('chat.supervisor') && Gate::denies('chat.responder'), 403, 'No tienes permiso para administrar instancias.');

        $channel = ChatWhatsappChannel::query()->findOrFail($channelId);

        $this->editingChannelId = $channel->id;
        $this->channelInstanceName = (string) $channel->instance_name;
        $this->channelDisplayName = (string) ($channel->display_name ?? '');
        $this->channelApiKey = (string) $channel->api_key;
        $this->channelServerUrl = (string) $channel->server_url;
        $this->channelColor = (string) $channel->color;
        $this->channelIsActive = (bool) $channel->is_active;
        $this->channelOutboundEnabled = (bool) $channel->outbound_enabled;
    }

    public function deleteChannel(int $channelId): void
    {
        abort_if(Gate::denies('chat.supervisor') && Gate::denies('chat.responder'), 403, 'No tienes permiso para administrar instancias.');

        ChatWhatsappChannel::query()->whereKey($channelId)->delete();

        if ($this->editingChannelId === $channelId) {
            $this->resetChannelForm();
        }
    }

    public function resetChannelForm(): void
    {
        $this->editingChannelId = null;
        $this->channelInstanceName = '';
        $this->channelDisplayName = '';
        $this->channelApiKey = '';
        $this->channelServerUrl = '';
        $this->channelColor = 'otro';
        $this->channelIsActive = true;
        $this->channelOutboundEnabled = true;
    }

    public function clearInternalChatHistory(): void
    {
        abort_if(Gate::denies('chat.supervisor') && Gate::denies('chat.responder'), 403, 'No tienes permiso para limpiar historial.');

        DB::transaction(function () {
            ChatMensajeCanal::query()->delete();
            Mensaje::query()->delete();
            ChatMemoriaResumen::query()->delete();
            ChatMemoriaContacto::query()->delete();
            ChatMemoriaNegocio::query()->delete();
            Conversacion::query()->delete();
            ChatContactoCanal::query()->delete();
        });

        $this->activeConversationId = null;
        $this->mobilePane = 'list';
        $this->messageText = '';
        $this->imageUpload = null;
        $this->audioUpload = null;
        $this->lastUnreadConversations = 0;
        $this->settingsNotice = 'Historial interno de chats limpiado correctamente.';
    }

    public function clearActiveConversationHistory(): void
    {
        abort_if(Gate::denies('chat.supervisor') && Gate::denies('chat.responder'), 403, 'No tienes permiso para limpiar historial.');
        $this->requireConversation();

        $conversation = $this->activeConversation();

        if (! $conversation) {
            return;
        }

        DB::transaction(function () use ($conversation) {
            ChatMensajeCanal::query()
                ->where('idconv', $conversation->idconv)
                ->delete();

            Mensaje::query()
                ->where('idconv', $conversation->idconv)
                ->delete();

            ChatMemoriaResumen::query()
                ->where('idconv', $conversation->idconv)
                ->delete();

            $conversation->update([
                'mensajes_no_leidos' => 0,
                'operator_typing_id' => null,
                'operator_typing_at' => null,
                'last_message_at' => null,
                'ultima_actividad' => now(),
            ]);
        });

        $this->messagesLimit = 80;
        $this->activeMessageSearch = '';
        $this->messageText = '';
        $this->imageUpload = null;
        $this->audioUpload = null;
        $this->lastUnreadConversations = $this->unreadConversationsCount();
        $this->lastActiveMessageFingerprint = $this->activeConversationMessageFingerprint();
        $this->settingsNotice = 'Historial del chat seleccionado limpiado correctamente.';
        $this->dispatch('chat-scroll-bottom');
    }

    public function sendText(): void
    {
        $this->sendMessage('texto');
    }

    public function applyQuickResponse(int $quickResponseId): void
    {
        abort_if(Gate::denies('chat.ver'), 403, 'No tienes permiso para usar respuestas rápidas.');

        $quickResponse = QuickResponse::query()
            ->activas()
            ->tipo('empleado')
            ->findOrFail($quickResponseId);

        $this->messageText = (string) $quickResponse->contenido;
        $this->dispatch('chat-focus-composer');
    }

    public function applyFirstQuickResponse(): void
    {
        abort_if(Gate::denies('chat.ver'), 403, 'No tienes permiso para usar respuestas rápidas.');

        $firstSuggestion = $this->quickResponseSuggestions()->first();

        if (! $firstSuggestion) {
            return;
        }

        $this->applyQuickResponse((int) $firstSuggestion->id);
    }

    public function saveQuickResponse(): void
    {
        abort_if(Gate::denies('chat.ver'), 403, 'No tienes permiso para administrar respuestas rápidas.');

        $data = $this->validate([
            'quickResponseCommand' => [
                'required',
                'string',
                'max:50',
                Rule::unique('quick_responses', 'comando')->ignore($this->editingQuickResponseId),
            ],
            'quickResponseTitle' => ['required', 'string', 'max:200'],
            'quickResponseContent' => ['required', 'string', 'max:4000'],
            'quickResponseOrder' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'quickResponseActive' => ['boolean'],
        ]);

        $payload = [
            'comando' => ltrim(strtolower(trim($data['quickResponseCommand'])), '/'),
            'titulo' => trim($data['quickResponseTitle']),
            'contenido' => trim($data['quickResponseContent']),
            'tipo' => 'empleado',
            'activo' => (bool) $data['quickResponseActive'],
            'orden' => (int) ($data['quickResponseOrder'] ?? 0),
        ];

        if ($this->editingQuickResponseId) {
            QuickResponse::query()->findOrFail($this->editingQuickResponseId)->update($payload);
            $this->settingsNotice = 'Respuesta rápida actualizada correctamente.';
        } else {
            QuickResponse::query()->create($payload);
            $this->settingsNotice = 'Respuesta rápida creada correctamente.';
        }

        $this->resetQuickResponseForm();
    }

    public function editQuickResponse(int $quickResponseId): void
    {
        abort_if(Gate::denies('chat.ver'), 403, 'No tienes permiso para administrar respuestas rápidas.');

        $quickResponse = QuickResponse::query()->findOrFail($quickResponseId);

        $this->editingQuickResponseId = $quickResponse->id;
        $this->quickResponseCommand = (string) $quickResponse->comando;
        $this->quickResponseTitle = (string) $quickResponse->titulo;
        $this->quickResponseContent = (string) $quickResponse->contenido;
        $this->quickResponseOrder = (int) $quickResponse->orden;
        $this->quickResponseActive = (bool) $quickResponse->activo;
    }

    public function deleteQuickResponse(int $quickResponseId): void
    {
        abort_if(Gate::denies('chat.ver'), 403, 'No tienes permiso para administrar respuestas rápidas.');

        QuickResponse::query()->whereKey($quickResponseId)->delete();
        $this->settingsNotice = 'Respuesta rápida eliminada.';

        if ($this->editingQuickResponseId === $quickResponseId) {
            $this->resetQuickResponseForm();
        }
    }

    public function resetQuickResponseForm(): void
    {
        $this->editingQuickResponseId = null;
        $this->quickResponseCommand = '';
        $this->quickResponseTitle = '';
        $this->quickResponseContent = '';
        $this->quickResponseOrder = 0;
        $this->quickResponseActive = true;
    }

    public function sendImage(): void
    {
        $this->sendMessage('imagen');
    }

    public function sendAudio(): void
    {
        $this->sendMessage('audio');
    }

    private function sendMessage(string $type): void
    {
        abort_if(Gate::denies('chat.responder'), 403, 'No tienes permiso para responder.');
        $this->requireConversation();

        $settings = app(ChatSettingsService::class);
        $maxKb = $settings->maxUploadKilobytes();

        if ($type === 'imagen') {
            $this->validate([
                'imageUpload' => ['required', 'image', 'max:'.$maxKb],
            ]);
        }

        if ($type === 'audio') {
            $this->validate([
                'audioUpload' => ['required', 'file', 'mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/ogg,audio/webm,audio/mp4', 'max:'.$maxKb],
            ]);
        }

        if ($type === 'texto') {
            $this->messageText = $this->expandQuickResponseText($this->messageText);

            $this->validate([
                'messageText' => ['required', 'string', 'max:4000'],
            ]);
        }

        $file = match ($type) {
            'imagen' => $this->imageUpload,
            'audio' => $this->audioUpload,
            default => null,
        };

        app(WhatsAppHelpdeskService::class)->sendOperatorMessage(
            $this->activeConversation(),
            $this->operator(),
            $type,
            $this->messageText,
            $file
        );

        $this->messageText = '';
        $this->imageUpload = null;
        $this->audioUpload = null;

        $this->dispatch('chat-clear-composer');
        $this->dispatch('chat-scroll-bottom');
    }

    private function conversationQuery()
    {
        $query = Conversacion::query()
            ->with(['cliente', 'contactoCanal', 'ultimoMensaje', 'operadorAsignado', 'operadorEscribiendo'])
            ->where('canal_principal', 'whatsapp')
            ->orderByRaw('COALESCE(last_message_at, ultima_actividad, updated_at) DESC');

        match ($this->filter) {
            'todos' => null,
            'nuevas' => $query->whereIn('estado', ['nueva', 'nuevo', 'abierta', 'abierto']),
            'no_leidas' => $query->where(function ($q) {
                $q->where('unread_count', '>', 0)->orWhere('mensajes_no_leidos', '>', 0);
            }),
            'asignadas_mi' => $query->where('assigned_to', $this->operator()?->idemp),
            'abiertas' => $query->whereIn('estado', ['nueva', 'nuevo', 'abierta', 'abierto', 'asignado', 'atendiendo', 'pausado', 'en_atencion', 'en_espera']),
            'cerradas' => $query->whereIn('estado', ['cerrado', 'cerrada']),
            default => null,
        };

        if ($this->search !== '') {
            $search = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('cliente', function ($client) use ($search) {
                    $client->where('nombrecli', 'like', $search)
                        ->orWhere('telefonocli', 'like', $search);
                })->orWhereHas('contactoCanal', function ($contact) use ($search) {
                    $contact->where('canal_user_id', 'like', $search)
                        ->orWhere('telefono_normalizado', 'like', $search)
                        ->orWhere('nombre_canal', 'like', $search);
                });
            });
        }

        return $query;
    }

    private function conversationList(): array
    {
        $query = $this->conversationQuery();

        $conversations = $query
            ->limit($this->conversationsLimit + 1)
            ->get();

        $hasMore = $conversations->count() > $this->conversationsLimit;

        if ($hasMore) {
            $conversations = $conversations->take($this->conversationsLimit);
        }

        return [
            'items' => $conversations->values(),
            'has_more' => $hasMore,
            'loaded' => $conversations->count(),
        ];
    }

    private function activeConversation(): ?Conversacion
    {
        if (! $this->activeConversationId) {
            return null;
        }

        return Conversacion::query()
            ->with([
                'cliente.ventas',
                'cliente.usuarios.cuenta.valor.servicio',
                'cliente.usuarios.detalle_venta.perfil',
                'contactoCanal',
                'operadorAsignado',
                'operadorEscribiendo',
            ])
            ->find($this->activeConversationId);
    }

    private function conversationMessages(?Conversacion $conversation): array
    {
        if (! $conversation) {
            return [
                'items' => collect(),
                'has_more' => false,
                'loaded' => 0,
            ];
        }

        $messagesQuery = Mensaje::query()
            ->where('idconv', $conversation->idconv)
            ->with(['empleado', 'cliente'])
            ->orderByDesc('idmsg');

        if (trim($this->activeMessageSearch) !== '') {
            $search = '%'.trim($this->activeMessageSearch).'%';
            $messagesQuery->where('contenido', 'like', $search);
        }

        $messages = $messagesQuery
            ->limit($this->messagesLimit + 1)
            ->get();

        $hasMore = $messages->count() > $this->messagesLimit;

        if ($hasMore) {
            $messages = $messages->take($this->messagesLimit);
        }

        $messages = $messages->reverse()->values();

        return [
            'items' => $messages,
            'has_more' => $hasMore,
            'loaded' => $messages->count(),
        ];
    }

    private function requireConversation(): void
    {
        abort_if(! $this->activeConversation(), 404, 'Selecciona una conversacion.');
    }

    private function operator(): ?Empleado
    {
        return Auth::guard('empleado')->user() ?? Auth::user();
    }

    private function unreadConversationsCount(): int
    {
        return (int) Conversacion::query()
            ->where('canal_principal', 'whatsapp')
            ->where(function ($q) {
                $q->where('unread_count', '>', 0)
                    ->orWhere('mensajes_no_leidos', '>', 0);
            })
            ->count();
    }

    private function quickResponseSuggestions()
    {
        $text = trim((string) $this->messageText);

        if (!str_starts_with($text, '/')) {
            return collect();
        }

        $term = ltrim($text, '/');

        return QuickResponse::query()
            ->activas()
            ->tipo('empleado')
            ->when($term !== '', function ($query) use ($term) {
                $query->buscar($term);
            })
            ->orderBy('orden')
            ->orderBy('comando')
            ->limit(8)
            ->get(['id', 'comando', 'titulo', 'contenido']);
    }

    private function expandQuickResponseText(string $text): string
    {
        $trimmed = trim($text);

        if (!str_starts_with($trimmed, '/')) {
            return $text;
        }

        if (!preg_match('/^\/([a-zA-Z0-9_\-]+)(\s+(.*))?$/u', $trimmed, $matches)) {
            return $text;
        }

        $command = strtolower(trim($matches[1] ?? ''));
        $suffix = trim((string) ($matches[3] ?? ''));

        if ($command === '') {
            return $text;
        }

        $quickResponse = QuickResponse::query()
            ->activas()
            ->tipo('empleado')
            ->porComando($command)
            ->first();

        if (!$quickResponse) {
            return $text;
        }

        return trim($quickResponse->contenido . ($suffix !== '' ? PHP_EOL . $suffix : ''));
    }

    private function clientActiveUsersForConversation(?Conversacion $conversation)
    {
        $client = $conversation?->cliente;

        if (! $client) {
            return collect();
        }

        return $client->usuarios
            ->sortBy('fecha_vencimiento')
            ->values()
            ->map(function ($user) {
                $account = $user->cuenta;
                $serviceCode = strtoupper((string) ($account?->valor?->idser ?? 'SERVICIO'));
                $serviceName = (string) ($account?->valor?->servicio?->nombreser ?? $serviceCode);
                $accountUser = trim((string) ($account?->usuariocue ?? ''));
                $accountPass = trim((string) ($account?->contrasenacue ?? ''));
                $pin = trim((string) (data_get($user, 'detalle_venta.perfil.pinper') ?? ''));
                $profileNumber = (int) ($user->perfil ?? 0);

                $displayProfile = $profileNumber > 0 && ! in_array($serviceCode, ['MAGIS', 'FLUJO'], true)
                    ? (string) $profileNumber
                    : null;

                $displayPin = $pin !== '' && ! in_array($serviceCode, ['MAGIS', 'FLUJO'], true)
                    ? $pin
                    : null;

                $spotifyUser = null;
                $spotifyPass = null;
                if ($serviceCode === 'SPOTIFY') {
                    if ($profileNumber <= 1 || $pin === '') {
                        $spotifyUser = $accountUser;
                        $spotifyPass = $accountPass;
                    } elseif (str_contains($pin, '|')) {
                        [$spotifyUserPart, $spotifyPassPart] = array_pad(explode('|', $pin, 2), 2, '');
                        $spotifyUser = trim((string) $spotifyUserPart) !== '' ? trim((string) $spotifyUserPart) : $accountUser;
                        $spotifyPass = trim((string) $spotifyPassPart) !== '' ? trim((string) $spotifyPassPart) : $accountPass;
                    } else {
                        $spotifyUser = $pin;
                        $spotifyPass = $pin;
                    }
                }

                return [
                    'service_code' => $serviceCode,
                    'service_name' => $serviceName,
                    'account_id' => (string) ($account?->idcue ?? ''),
                    'account_user' => $accountUser,
                    'account_pass' => $accountPass,
                    'profile' => $displayProfile,
                    'profile_pin' => $displayPin,
                    'spotify_user' => $spotifyUser,
                    'spotify_pass' => $spotifyPass,
                    'expires_at' => $user->fecha_vencimiento,
                    'status' => $this->resolveAccountStatus($account?->caidacue, $account?->activocue, $account?->fechavencue),
                ];
            });
    }

    private function resolveAccountStatus($isDown, $isActive, $expiresAt): array
    {
        if ($isActive === false) {
            return ['label' => 'Inactiva', 'tone' => 'muted'];
        }

        if ((bool) $isDown) {
            return ['label' => 'Danada', 'tone' => 'danger'];
        }

        if (! $expiresAt) {
            return ['label' => 'Activa', 'tone' => 'success'];
        }

        $expiration = Carbon::parse($expiresAt)->startOfDay();
        $today = now()->startOfDay();

        if ($expiration->lessThan($today)) {
            return ['label' => 'Vencida', 'tone' => 'danger'];
        }

        if ($expiration->lessThanOrEqualTo($today->copy()->addDays(5))) {
            return ['label' => 'Por vencer', 'tone' => 'warning'];
        }

        return ['label' => 'Activa', 'tone' => 'success'];
    }
}
