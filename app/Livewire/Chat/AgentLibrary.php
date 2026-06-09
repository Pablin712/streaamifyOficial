<?php

namespace App\Livewire\Chat;

use App\Models\ChatMemoriaNegocio;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class AgentLibrary extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // Lista / filtros
    public string $filtroTipo      = '';
    public string $filtroCategoria = '';
    public string $busqueda        = '';

    // Modal create/edit
    public bool $showModal = false;
    public ?int $editingId  = null;

    // Campos del formulario
    public string $tipo       = 'faq';
    public string $categoria  = 'general';
    public string $clave      = '';
    public string $titulo     = '';
    public string $contenido  = '';
    public string $resumen    = '';
    public string $visibilidad = 'cliente';
    public string $tagsInput  = '';
    public int    $prioridad  = 50;
    public bool   $activo     = true;
    public string $inicio_at  = '';
    public string $fin_at     = '';

    protected function rules(): array
    {
        $claveRule = $this->editingId
            ? 'required|string|max:120|unique:chat_memoria_negocio,clave,' . $this->editingId . ',id,tipo,' . $this->tipo
            : 'required|string|max:120|unique:chat_memoria_negocio,clave,NULL,id,tipo,' . $this->tipo;

        return [
            'tipo'        => 'required|in:' . implode(',', array_keys(ChatMemoriaNegocio::TIPOS)),
            'categoria'   => 'required|in:' . implode(',', array_keys(ChatMemoriaNegocio::CATEGORIAS)),
            'clave'       => $claveRule,
            'titulo'      => 'required|string|max:160',
            'contenido'   => 'required|string',
            'resumen'     => 'nullable|string|max:255',
            'visibilidad' => 'required|in:cliente,interna,ambas',
            'tagsInput'   => 'nullable|string',
            'prioridad'   => 'required|integer|min:1|max:999',
            'activo'      => 'boolean',
            'inicio_at'   => 'nullable|date',
            'fin_at'      => 'nullable|date|after_or_equal:inicio_at',
        ];
    }

    public function mount(): void
    {
        Gate::authorize('chat.ver');
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroCategoria(): void
    {
        $this->resetPage();
    }

    private function openModal(): void
    {
        $this->js("window.dispatchEvent(new CustomEvent('open-modal',{detail:'agentLibraryModal'}))");
    }

    private function closeModalJs(): void
    {
        $this->js("window.dispatchEvent(new CustomEvent('close-modal',{detail:'agentLibraryModal'}))");
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->openModal();
    }

    public function openEdit(int $id): void
    {
        $entry = ChatMemoriaNegocio::findOrFail($id);
        $this->editingId   = $id;
        $this->tipo        = $entry->tipo;
        $this->categoria   = $entry->categoria ?? 'general';
        $this->clave       = $entry->clave;
        $this->titulo      = $entry->titulo;
        $this->contenido   = $entry->contenido;
        $this->resumen     = $entry->resumen ?? '';
        $this->visibilidad = $entry->visibilidad;
        $this->tagsInput   = implode(', ', $entry->tags ?? []);
        $this->prioridad   = $entry->prioridad;
        $this->activo      = $entry->activo;
        $this->inicio_at   = $entry->inicio_at?->format('Y-m-d') ?? '';
        $this->fin_at      = $entry->fin_at?->format('Y-m-d') ?? '';
        $this->openModal();
    }

    public function closeModal(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate();

        $tags = array_filter(array_map('trim', explode(',', $this->tagsInput)));

        $data = [
            'tipo'        => $this->tipo,
            'categoria'   => $this->categoria,
            'clave'       => trim($this->clave),
            'titulo'      => $this->titulo,
            'contenido'   => $this->contenido,
            'resumen'     => $this->resumen ?: null,
            'visibilidad' => $this->visibilidad,
            'tags'        => array_values($tags),
            'prioridad'   => $this->prioridad,
            'activo'      => $this->activo,
            'inicio_at'   => $this->inicio_at ?: null,
            'fin_at'      => $this->fin_at ?: null,
        ];

        try {
            if ($this->editingId) {
                ChatMemoriaNegocio::findOrFail($this->editingId)->update($data);
                $this->dispatch('notify', ['type' => 'success', 'msg' => 'Entrada actualizada.']);
            } else {
                ChatMemoriaNegocio::create($data);
                $this->dispatch('notify', ['type' => 'success', 'msg' => 'Entrada creada.']);
            }
        } catch (\Throwable $e) {
            $this->dispatch('notify', ['type' => 'error', 'msg' => 'Error al guardar: ' . $e->getMessage()]);
            return;
        }

        $this->resetForm();
        $this->closeModalJs();
    }

    public function toggleActivo(int $id): void
    {
        $entry = ChatMemoriaNegocio::findOrFail($id);
        $entry->update(['activo' => ! $entry->activo]);
    }

    public function delete(int $id): void
    {
        ChatMemoriaNegocio::findOrFail($id)->delete();
        $this->dispatch('notify', ['type' => 'info', 'msg' => 'Entrada eliminada.']);
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'clave', 'titulo', 'contenido',
            'resumen', 'tagsInput', 'inicio_at', 'fin_at',
        ]);
        $this->tipo        = 'faq';
        $this->categoria   = 'general';
        $this->visibilidad = 'cliente';
        $this->prioridad   = 50;
        $this->activo      = true;
        $this->resetValidation();
    }

    public function render()
    {
        $entries = ChatMemoriaNegocio::query()
            ->when($this->filtroTipo,      fn ($q) => $q->where('tipo', $this->filtroTipo))
            ->when($this->filtroCategoria, fn ($q) => $q->where('categoria', $this->filtroCategoria))
            ->when($this->busqueda, fn ($q) => $q->where(function ($q2) {
                $q2->where('titulo', 'like', "%{$this->busqueda}%")
                   ->orWhere('contenido', 'like', "%{$this->busqueda}%")
                   ->orWhere('clave', 'like', "%{$this->busqueda}%");
            }))
            ->orderBy('categoria')
            ->orderBy('prioridad')
            ->orderBy('tipo')
            ->paginate(12);

        return view('livewire.chat.agent-library', [
            'entries'    => $entries,
            'tipos'      => ChatMemoriaNegocio::TIPOS,
            'categorias' => ChatMemoriaNegocio::CATEGORIAS,
        ]);
    }
}
