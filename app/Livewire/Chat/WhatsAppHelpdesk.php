<?php

namespace App\Livewire\Chat;

use Carbon\Carbon;
use App\Models\Chat\ChatSetting;
use App\Models\ChatEtiqueta;
use App\Models\ChatMemoriaContacto;
use App\Models\ChatMemoriaNegocio;
use App\Models\ChatMemoriaResumen;
use App\Models\ChatMensajeCanal;
use App\Models\ChatContactoCanal;
use App\Models\ChatWhatsappChannel;
use App\Models\Conversacion;
use App\Models\Empleado;
use App\Models\Historial;
use App\Models\Mensaje;
use App\Models\QuickResponse;
use App\Models\Soporte;
use App\Services\Chat\ChatSettingsService;
use App\Services\Chat\WhatsAppHelpdeskService;
use App\Services\ConcentracionService;
use App\Services\TareaService;
use App\Support\PhoneNumber;
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

    public ?int $etiquetaFilter = null;

    public bool $showEtiquetaCreator = false;

    public string $newEtiquetaNombre = '';

    public string $newEtiquetaColor = '';

    public string $search = '';

    public ?int $activeConversationId = null;

    public string $messageText = '';

    public ?TemporaryUploadedFile $imageUpload = null;

    public ?TemporaryUploadedFile $audioUpload = null;

    public ?int $replyingToIdmsg = null;

    public ?int $reactionPickerForIdmsg = null;

    public ?int $forwardingIdmsg = null;

    public string $forwardSearch = '';

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

    public string $soporteSolucion = '';

    public string $soporteNotice = '';

    public int $conversationsLimit = 25;

    public int $messagesLimit = 80;

    public string $activeMessageSearch = '';

    public function mount(): void
    {
        abort_if(Gate::denies('chat.ver'), 403, 'No tienes permiso para acceder al chat.');

        // Pre-filtrar por cliente cuando se llega desde tareas (?idcli=X)
        $idcli = (int) request()->query('idcli', 0);
        if ($idcli > 0) {
            $nombre = DB::table('clientes')->where('idcli', $idcli)->value('nombrecli');
            if ($nombre) {
                $this->search = $nombre;
            }
        }

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

        $clientIds = $conversationsData['items']->pluck('idcli')->filter()->unique()->values()->toArray();
        $conversationLabels = $this->computeConversationLabels($clientIds);

        return view('livewire.chat.whatsapp-helpdesk', [
            'conversations'         => $conversationsData['items'],
            'conversationsHasMore'  => $conversationsData['has_more'],
            'conversationsLoaded'   => $conversationsData['loaded'],
            'matchedMessages'       => $conversationsData['matched_messages'],
            'activeConversation'    => $activeConversation,
            'messages'              => $activeMessages['items'],
            'messagesHasMore'       => $activeMessages['has_more'],
            'messagesLoaded'        => $activeMessages['loaded'],
            'activeContactIdentity' => $activeContactIdentity,
            'clientActiveUsers'     => $this->clientActiveUsersForConversation($activeConversation),
            'clientePendingSoporte' => $this->clientePendingSoporte($activeConversation),
            'soporteNotice'         => $this->soporteNotice,
            'quickResponseSuggestions' => $this->quickResponseSuggestions(),
            'quickResponses'        => QuickResponse::query()->orderBy('orden')->orderBy('comando')->get(),
            'operators'             => Empleado::query()->orderBy('nombreemp')->get(['idemp', 'nombreemp']),
            'settings'              => $settings,
            'whatsappChannels'      => ChatWhatsappChannel::query()
                ->orderByDesc('is_active')
                ->orderBy('instance_name')
                ->get(),
            'conversationLabels'    => $conversationLabels,
            'concentracionActive'   => ConcentracionService::isActive(),
            'concentracionLocked'   => ConcentracionService::isLocked(),
            'allEtiquetas'          => ChatEtiqueta::query()->orderBy('nombre')->get(),
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

    public function updatingEtiquetaFilter(): void
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
        $this->soporteSolucion = '';
        $this->soporteNotice = '';
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

    public function startReply(int $idmsg): void
    {
        abort_if(Gate::denies('chat.responder'), 403, 'No tienes permiso para responder.');
        $this->requireConversation();

        $pertenece = Mensaje::query()
            ->where('idmsg', $idmsg)
            ->where('idconv', $this->activeConversation()?->idconv)
            ->exists();

        if ($pertenece) {
            $this->replyingToIdmsg = $idmsg;
            $this->dispatch('chat-focus-composer');
        }
    }

    public function cancelReply(): void
    {
        $this->replyingToIdmsg = null;
    }

    public function toggleEtiquetaFiltro(?int $etiquetaId): void
    {
        $this->etiquetaFilter = $this->etiquetaFilter === $etiquetaId ? null : $etiquetaId;
        $this->conversationsLimit = 25;
    }

    public function toggleEtiquetaEnConversacion(int $etiquetaId): void
    {
        abort_if(Gate::denies('chat.responder'), 403, 'No tienes permiso para etiquetar conversaciones.');
        $this->requireConversation();

        $this->activeConversation()?->etiquetas()->toggle($etiquetaId);
    }

    public function toggleEtiquetaCreator(): void
    {
        $this->showEtiquetaCreator = ! $this->showEtiquetaCreator;
        $this->newEtiquetaNombre = '';
        $this->newEtiquetaColor = ChatEtiqueta::PALETA[0];
    }

    public function guardarNuevaEtiqueta(): void
    {
        abort_if(Gate::denies('chat.responder'), 403, 'No tienes permiso para crear etiquetas.');
        $this->requireConversation();

        $nombre = trim($this->newEtiquetaNombre);

        abort_if($nombre === '' || mb_strlen($nombre) > 30, 422, 'Nombre de etiqueta inválido.');
        abort_unless(in_array($this->newEtiquetaColor, ChatEtiqueta::PALETA, true), 422, 'Color de etiqueta inválido.');

        $etiqueta = ChatEtiqueta::query()->firstOrCreate(
            ['nombre' => $nombre],
            ['color' => $this->newEtiquetaColor]
        );

        $this->activeConversation()?->etiquetas()->syncWithoutDetaching([$etiqueta->id]);

        $this->showEtiquetaCreator = false;
        $this->newEtiquetaNombre = '';
    }

    public function eliminarEtiqueta(int $etiquetaId): void
    {
        abort_if(Gate::denies('chat.responder'), 403, 'No tienes permiso para eliminar etiquetas.');

        ChatEtiqueta::query()->where('id', $etiquetaId)->delete();

        if ($this->etiquetaFilter === $etiquetaId) {
            $this->etiquetaFilter = null;
        }
    }

    public function togglePin(int $idconv): void
    {
        abort_if(Gate::denies('chat.responder'), 403, 'No tienes permiso para fijar conversaciones.');

        $conversation = Conversacion::query()
            ->where('canal_principal', 'whatsapp')
            ->findOrFail($idconv);

        $conversation->update([
            'pinned_at' => $conversation->pinned_at ? null : now(),
        ]);
    }

    public function deleteMessage(int $idmsg): void
    {
        abort_if(Gate::denies('chat.responder'), 403, 'No tienes permiso para borrar mensajes.');
        $this->requireConversation();

        $mensaje = Mensaje::query()
            ->where('idmsg', $idmsg)
            ->where('idconv', $this->activeConversation()?->idconv)
            ->where('tipo_remitente', 'empleado')
            ->first();

        if (! $mensaje) {
            return;
        }

        app(WhatsAppHelpdeskService::class)->deleteOperatorMessage($mensaje);

        if ($this->replyingToIdmsg === $idmsg) {
            $this->replyingToIdmsg = null;
        }
    }

    /** Paleta fija de reacciones rápidas, mismas que usa WhatsApp. */
    public const EMOJIS_REACCION = ['👍', '❤️', '😂', '😮', '😢', '🙏'];

    public function reactToMessage(int $idmsg, string $emoji): void
    {
        abort_if(Gate::denies('chat.responder'), 403, 'No tienes permiso para reaccionar a mensajes.');
        $this->requireConversation();
        abort_unless(in_array($emoji, self::EMOJIS_REACCION, true), 422, 'Emoji no permitido.');

        $mensaje = Mensaje::query()
            ->where('idmsg', $idmsg)
            ->where('idconv', $this->activeConversation()?->idconv)
            ->first();

        if (! $mensaje) {
            return;
        }

        $yaReaccione = $mensaje->reacciones()->where('autor_tipo', 'empleado')->where('emoji', $emoji)->exists();

        app(WhatsAppHelpdeskService::class)->reactToMessage($mensaje, $yaReaccione ? '' : $emoji);

        $this->reactionPickerForIdmsg = null;
    }

    public function toggleReactionPicker(?int $idmsg): void
    {
        $this->reactionPickerForIdmsg = $this->reactionPickerForIdmsg === $idmsg ? null : $idmsg;
    }

    public function startForward(int $idmsg): void
    {
        abort_if(Gate::denies('chat.responder'), 403, 'No tienes permiso para reenviar mensajes.');
        $this->requireConversation();

        $mensaje = Mensaje::query()
            ->where('idmsg', $idmsg)
            ->where('idconv', $this->activeConversation()?->idconv)
            ->whereNull('eliminado_at')
            ->first();

        if (! $mensaje) {
            return;
        }

        $this->forwardingIdmsg = $idmsg;
        $this->forwardSearch = '';
    }

    public function cancelForward(): void
    {
        $this->forwardingIdmsg = null;
        $this->forwardSearch = '';
    }

    public function forwardCandidates()
    {
        $query = Conversacion::query()
            ->with(['cliente', 'contactoCanal'])
            ->where('canal_principal', 'whatsapp')
            ->orderByRaw('COALESCE(last_message_at, ultima_actividad, updated_at) DESC');

        $term = trim($this->forwardSearch);

        if ($term !== '') {
            $search = '%'.$term.'%';
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

        return $query->limit(20)->get();
    }

    public function forwardTo(int $targetIdconv): void
    {
        abort_if(Gate::denies('chat.responder'), 403, 'No tienes permiso para reenviar mensajes.');

        if (! $this->forwardingIdmsg) {
            return;
        }

        $original = Mensaje::query()->where('idmsg', $this->forwardingIdmsg)->first();
        $target = Conversacion::query()->where('canal_principal', 'whatsapp')->find($targetIdconv);

        if ($original && $target) {
            app(WhatsAppHelpdeskService::class)->forwardMessage($original, $target, $this->operator());
        }

        $this->cancelForward();
    }

    public function highlightMessageContent(?string $content, ?string $term = null): string
    {
        $value = (string) $content;

        if ($value === '') {
            return '';
        }

        $escaped = e($value);
        $term = trim($term ?? $this->activeMessageSearch);

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

    public function atenderSoporteDesdeChat(int $idsop): void
    {
        $user = $this->operator();

        if (! $user || Gate::forUser($user)->denies('chat.responder')) {
            $this->addError('soporteSolucion', 'No tienes permiso para atender soportes desde el chat.');
            return;
        }

        if (! $this->activeConversation()) {
            $this->addError('soporteSolucion', 'Selecciona una conversación activa primero.');
            return;
        }

        $this->validate([
            'soporteSolucion' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $soporte = Soporte::with(['cliente', 'cuenta'])->findOrFail($idsop);

        $conversation = $this->activeConversation();
        $idcli = $conversation?->cliente?->idcli ?? $conversation?->contactoCanal?->idcli;

        if ((int) $soporte->idcli !== (int) $idcli) {
            $this->addError('soporteSolucion', 'Este soporte no corresponde al cliente activo.');
            return;
        }

        $soporte->update([
            'solucion' => $this->soporteSolucion,
            'estado'   => 'atendido',
        ]);

        app(TareaService::class)->completarTareasRelacionadas('soporte_pendiente', 'Soporte', $idsop, $user->idemp);

        Historial::create([
            'accion'      => 'Atención de soporte',
            'descripcion' => 'Soporte #' . $soporte->idsop . ' atendido desde chat para cliente ' . ($soporte->cliente?->nombrecli ?? 'N/A'),
            'empleado_id' => $user->idemp,
            'created_at'  => now(),
        ]);

        $this->soporteSolucion = '';
        $this->soporteNotice = 'Soporte #' . $soporte->idsop . ' marcado como atendido correctamente.';
    }

    private function clientePendingSoporte(?Conversacion $conversation): ?Soporte
    {
        $idcli = $conversation?->cliente?->idcli ?? $conversation?->contactoCanal?->idcli;

        if (! $idcli) {
            return null;
        }

        return Soporte::with('cuenta.valor.servicio')
            ->where('idcli', $idcli)
            ->where('estado', 'pendiente')
            ->orderByDesc('created_at')
            ->first();
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
                // video/webm y video/mp4 incluidos porque el sniffer de PHP (finfo) detecta
                // grabaciones de audio-only del navegador (MediaRecorder) como contenedor de
                // video, aunque no tengan pista de video real. audio/x-wav y audio/wave son
                // variantes que reporta finfo para WAV segun la version de libmagic.
                'audioUpload' => ['required', 'file', 'mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/wave,audio/ogg,audio/webm,audio/mp4,video/webm,video/mp4', 'max:'.$maxKb],
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
            $file,
            $this->replyingToIdmsg
        );

        $this->messageText = '';
        $this->imageUpload = null;
        $this->audioUpload = null;
        $this->replyingToIdmsg = null;

        $this->dispatch('chat-clear-composer');
        $this->dispatch('chat-scroll-bottom');
    }

    private function conversationQuery()
    {
        $query = Conversacion::query()
            ->with(['cliente', 'contactoCanal', 'ultimoMensaje', 'operadorAsignado', 'operadorEscribiendo', 'etiquetas'])
            ->where('canal_principal', 'whatsapp')
            ->orderByRaw('pinned_at IS NULL')
            ->orderByDesc('pinned_at')
            ->orderByRaw('COALESCE(last_message_at, ultima_actividad, updated_at) DESC');

        // ── Modo concentración ───────────────────────────────────────────────
        if (ConcentracionService::isActive()) {
            $operator = $this->operator();

            if (! $operator) {
                $query->whereRaw('0 = 1');
            } else {
                [$allowedClientIds, $allowedProviderPhones, $allClientes] =
                    $this->concentracionAllowedIds($operator->idemp);

                if ($allClientes) {
                    // Tarea general activa (vender / atender_clientes): todos los chats de clientes
                } elseif (empty($allowedClientIds) && empty($allowedProviderPhones)) {
                    $query->whereRaw('0 = 1');
                } else {
                    $query->where(function ($q) use ($allowedClientIds, $allowedProviderPhones) {
                        if (! empty($allowedClientIds)) {
                            $q->whereIn('idcli', $allowedClientIds);
                        }
                        if (! empty($allowedProviderPhones)) {
                            $q->orWhereHas('contactoCanal', function ($cc) use ($allowedProviderPhones) {
                                $cc->whereIn('telefono_normalizado', $allowedProviderPhones);
                            });
                        }
                    });
                }
            }
        }
        // ────────────────────────────────────────────────────────────────────

        match ($this->filter) {
            'todos' => null,
            'nuevas' => $query->whereIn('estado', ['nueva', 'nuevo', 'abierta', 'abierto']),
            'no_leidas' => $query->where(function ($q) {
                $q->where('unread_count', '>', 0)->orWhere('mensajes_no_leidos', '>', 0);
            }),
            'proveedor' => $query->where(function ($q) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.tipo_contacto'))) LIKE '%proveedor%'")
                  ->orWhereHas('contactoCanal', function ($cc) {
                      $cc->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.tipo_contacto'))) LIKE '%proveedor%'");
                  });
            }),
            'bot' => $query->where(function ($q) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.tipo_contacto'))) LIKE '%bot%'")
                  ->orWhereRaw("JSON_EXTRACT(metadata, '$.is_bot') = true")
                  ->orWhereHas('contactoCanal', function ($cc) {
                      $cc->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.tipo_contacto'))) LIKE '%bot%'")
                         ->orWhereRaw("JSON_EXTRACT(metadata, '$.is_bot') = true");
                  });
            }),
            'asignadas_mi' => $query->where('assigned_to', $this->operator()?->idemp),
            'abiertas' => $query->whereIn('estado', ['nueva', 'nuevo', 'abierta', 'abierto', 'asignado', 'atendiendo', 'pausado', 'en_atencion', 'en_espera']),
            'cerradas' => $query->whereIn('estado', ['cerrado', 'cerrada']),
            default => null,
        };

        if ($this->search !== '') {
            $searchTerm = trim($this->search);
            $search = '%'.$searchTerm.'%';
            $query->where(function ($q) use ($search, $searchTerm) {
                $q->whereHas('cliente', function ($client) use ($search) {
                    $client->where('nombrecli', 'like', $search)
                        ->orWhere('telefonocli', 'like', $search);
                })->orWhereHas('contactoCanal', function ($contact) use ($search) {
                    $contact->where('canal_user_id', 'like', $search)
                        ->orWhere('telefono_normalizado', 'like', $search)
                        ->orWhere('nombre_canal', 'like', $search);
                });

                // Buscar tambien por contenido de mensajes (busqueda global), pero solo
                // con 3+ caracteres para no barrer toda la tabla mensajes en cada letra.
                if (mb_strlen($searchTerm) >= 3) {
                    $q->orWhereHas('mensajes', function ($m) use ($search) {
                        $m->where('contenido', 'like', $search);
                    });
                }
            });
        }

        if ($this->etiquetaFilter) {
            $query->whereHas('etiquetas', function ($q) {
                $q->where('chat_etiquetas.id', $this->etiquetaFilter);
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

        $matchedMessages = collect();
        $searchTerm = trim($this->search);

        if (mb_strlen($searchTerm) >= 3) {
            $search = '%'.$searchTerm.'%';
            $matchedMessages = Mensaje::query()
                ->whereIn('idconv', $conversations->pluck('idconv'))
                ->where('contenido', 'like', $search)
                ->orderByDesc('idmsg')
                ->get()
                ->groupBy('idconv')
                ->map(fn ($grupo) => $grupo->first());
        }

        return [
            'items' => $conversations->values(),
            'has_more' => $hasMore,
            'loaded' => $conversations->count(),
            'matched_messages' => $matchedMessages,
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
            ->with(['empleado', 'cliente', 'replyTo', 'reacciones'])
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

    /**
     * Devuelve [allowedClientIds[], allowedProviderPhones[]] para el modo concentración.
     * Delega la lógica de filtrado a ConcentracionService.getIds() para mantener
     * las reglas centralizadas (RequisitosV7.2).
     */
    private function concentracionAllowedIds(int $empId): array
    {
        $ids = app(ConcentracionService::class)->getIds($empId);

        $allowedClientIds = $ids['idcli'];
        $allClientes = $ids['all_clientes'] ?? false;

        if ($ids['all_providers']) {
            // agregar_stock → todos los proveedores activos
            $allowedProviderPhones = DB::table('proveedores')
                ->where('activopro', true)
                ->whereNotNull('telefonopro')
                ->pluck('telefonopro')
                ->unique()
                ->map(fn($p) => PhoneNumber::canonicalEc($p))
                ->filter()
                ->values()
                ->toArray();
        } elseif (! empty($ids['idcue_providers'])) {
            // renovar_cuenta + colapso_cuenta → solo proveedores de esas cuentas
            $allowedProviderPhones = DB::table('cuentas')
                ->join('valores', 'valores.idval', '=', 'cuentas.idval')
                ->join('proveedores', 'proveedores.idpro', '=', 'valores.idpro')
                ->whereIn('cuentas.idcue', $ids['idcue_providers'])
                ->whereNotNull('proveedores.telefonopro')
                ->pluck('proveedores.telefonopro')
                ->unique()
                ->map(fn($p) => PhoneNumber::canonicalEc($p))
                ->filter()
                ->values()
                ->toArray();
        } else {
            $allowedProviderPhones = [];
        }

        return [$allowedClientIds, $allowedProviderPhones, $allClientes];
    }

    private function computeConversationLabels(array $clientIds): array
    {
        // ── Labels por idcli (clientes) ──────────────────────────────────────

        $soporteIds = !empty($clientIds)
            ? DB::table('soportes')
                ->whereIn('idcli', $clientIds)
                ->where('estado', 'pendiente')
                ->pluck('idcli')
                ->unique()->flip()->toArray()
            : [];

        $cobrarIds = !empty($clientIds)
            ? DB::table('tareas')
                ->join('view_usuarios_activos as vu', 'vu.iddet', '=', 'tareas.related_id')
                ->whereIn('vu.idcli', $clientIds)
                ->where('tareas.tipo_tarea', 'cobrar_usuario')
                ->where('tareas.completada', false)
                ->pluck('vu.idcli')
                ->unique()->flip()->toArray()
            : [];

        $quitarIds = !empty($clientIds)
            ? DB::table('tareas')
                ->join('view_usuarios_activos as vu', 'vu.iddet', '=', 'tareas.related_id')
                ->whereIn('vu.idcli', $clientIds)
                ->where('tareas.tipo_tarea', 'quitar_usuario')
                ->where('tareas.completada', false)
                ->pluck('vu.idcli')
                ->unique()->flip()->toArray()
            : [];

        // Clientes con al menos una cuenta caída (caidacue = true)
        $cuentaCaidaCliIds = !empty($clientIds)
            ? DB::table('view_usuarios_activos as vu')
                ->join('cuentas', 'cuentas.idcue', '=', 'vu.idcue')
                ->whereIn('vu.idcli', $clientIds)
                ->where('cuentas.caidacue', true)
                ->pluck('vu.idcli')
                ->unique()->flip()->toArray()
            : [];

        // ── Labels por telefono_normalizado (proveedores) ────────────────────

        $renovarPhones = DB::table('tareas')
            ->join('cuentas', 'cuentas.idcue', '=', 'tareas.related_id')
            ->join('valores', 'valores.idval', '=', 'cuentas.idval')
            ->join('proveedores', 'proveedores.idpro', '=', 'valores.idpro')
            ->where('tareas.tipo_tarea', 'renovar_cuenta')
            ->where('tareas.completada', false)
            ->whereNotNull('proveedores.telefonopro')
            ->pluck('proveedores.telefonopro')
            ->unique()
            ->map(fn ($p) => \App\Support\PhoneNumber::canonicalEc($p))
            ->filter()
            ->flip()
            ->toArray();

        $caidaProPhones = DB::table('tareas')
            ->join('cuentas', 'cuentas.idcue', '=', 'tareas.related_id')
            ->join('valores', 'valores.idval', '=', 'cuentas.idval')
            ->join('proveedores', 'proveedores.idpro', '=', 'valores.idpro')
            ->where('tareas.tipo_tarea', 'cuenta_caida')
            ->where('tareas.completada', false)
            ->whereNotNull('proveedores.telefonopro')
            ->pluck('proveedores.telefonopro')
            ->unique()
            ->map(fn ($p) => \App\Support\PhoneNumber::canonicalEc($p))
            ->filter()
            ->flip()
            ->toArray();

        return [
            'soporte'       => $soporteIds,
            'cobrar'        => $cobrarIds,
            'quitar'        => $quitarIds,
            'cuenta_caida'  => $cuentaCaidaCliIds,
            'renovar'       => $renovarPhones,
            'caida_pro'     => $caidaProPhones,
        ];
    }
}
