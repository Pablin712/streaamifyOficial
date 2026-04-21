<?php

namespace App\Livewire\Chat;

use App\Models\Conversacion;
use App\Models\Empleado;
use App\Services\Chat\ChatSettingsService;
use App\Services\Chat\WhatsAppHelpdeskService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class WhatsAppHelpdesk extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $filter = 'nuevas';
    public string $search = '';
    public ?int $activeConversationId = null;
    public string $messageText = '';
    public ?TemporaryUploadedFile $imageUpload = null;
    public ?TemporaryUploadedFile $audioUpload = null;
    public string $mobilePane = 'list';

    public function mount(): void
    {
        abort_if(Gate::denies('chat.ver'), 403, 'No tienes permiso para acceder al chat.');
    }

    public function render()
    {
        $settings = app(ChatSettingsService::class)->all();

        return view('livewire.chat.whatsapp-helpdesk', [
            'conversations' => $this->conversationQuery()->paginate(20),
            'activeConversation' => $this->activeConversation(),
            'messages' => $this->activeConversation()
                ? $this->activeConversation()->mensajes()->with(['empleado', 'cliente'])->get()
                : collect(),
            'operators' => Empleado::query()->orderBy('nombreemp')->get(['idemp', 'nombreemp']),
            'settings' => $settings,
        ]);
    }

    public function selectConversation(int $conversationId): void
    {
        $conversation = Conversacion::query()
            ->where('canal_principal', 'whatsapp')
            ->findOrFail($conversationId);

        $conversation->marcarComoLeida();

        $this->activeConversationId = $conversation->idconv;
        $this->mobilePane = 'chat';
        $this->dispatch('chat-scroll-bottom');
    }

    public function backToList(): void
    {
        $this->mobilePane = 'list';
    }

    public function refreshChat(): void
    {
        if ($this->activeConversationId) {
            $this->dispatch('chat-scroll-bottom');
        }
    }

    public function markTyping(): void
    {
        $conversation = $this->activeConversation();
        $operator = $this->operator();

        if (!$conversation || !$operator) {
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

    public function sendText(): void
    {
        $this->sendMessage('texto');
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
                'imageUpload' => ['required', 'image', 'max:' . $maxKb],
            ]);
        }

        if ($type === 'audio') {
            $this->validate([
                'audioUpload' => ['required', 'file', 'mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/ogg,audio/webm,audio/mp4', 'max:' . $maxKb],
            ]);
        }

        if ($type === 'texto') {
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

        $this->reset(['messageText', 'imageUpload', 'audioUpload']);
        $this->dispatch('chat-scroll-bottom');
    }

    private function conversationQuery()
    {
        $query = Conversacion::query()
            ->with(['cliente', 'contactoCanal', 'ultimoMensaje', 'operadorAsignado', 'operadorEscribiendo'])
            ->where('canal_principal', 'whatsapp')
            ->orderByRaw('COALESCE(last_message_at, ultima_actividad, updated_at) DESC');

        match ($this->filter) {
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
            $search = '%' . trim($this->search) . '%';
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

    private function activeConversation(): ?Conversacion
    {
        if (!$this->activeConversationId) {
            return null;
        }

        return Conversacion::query()
            ->with(['cliente.ventas', 'contactoCanal', 'operadorAsignado', 'operadorEscribiendo'])
            ->find($this->activeConversationId);
    }

    private function requireConversation(): void
    {
        abort_if(!$this->activeConversation(), 404, 'Selecciona una conversacion.');
    }

    private function operator(): ?Empleado
    {
        return Auth::guard('empleado')->user() ?? Auth::user();
    }
}
