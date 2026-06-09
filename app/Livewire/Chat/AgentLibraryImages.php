<?php

namespace App\Livewire\Chat;

use App\Models\ChatAgenteImagen;
use App\Models\ChatMemoriaNegocio;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class AgentLibraryImages extends Component
{
    use WithPagination, WithFileUploads;

    protected string $paginationTheme = 'bootstrap';

    public string $filtroCategoria = '';
    public string $busqueda        = '';

    public bool $showModal = false;
    public ?int $editingId  = null;

    // Campos del formulario
    public string $nombre      = '';
    public string $descripcion = '';
    public string $categoria   = 'general';
    public string $tagsInput   = '';
    public int    $prioridad   = 50;
    public bool   $activo      = true;
    public $archivo = null;

    protected function rules(): array
    {
        $archivoRule = $this->editingId
            ? 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120'
            : 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120';

        return [
            'nombre'      => 'required|string|max:120',
            'descripcion' => 'required|string|max:1000',
            'categoria'   => 'required|in:' . implode(',', array_keys(ChatMemoriaNegocio::CATEGORIAS)),
            'tagsInput'   => 'nullable|string',
            'prioridad'   => 'required|integer|min:1|max:999',
            'activo'      => 'boolean',
            'archivo'     => $archivoRule,
        ];
    }

    public function mount(): void
    {
        Gate::authorize('chat.ver');
    }

    public function updatingBusqueda(): void    { $this->resetPage(); }
    public function updatingFiltroCategoria(): void { $this->resetPage(); }

    private function openModal(): void
    {
        $this->js("window.dispatchEvent(new CustomEvent('open-modal',{detail:'agentImgModal'}))");
    }

    private function closeModalJs(): void
    {
        $this->js("window.dispatchEvent(new CustomEvent('close-modal',{detail:'agentImgModal'}))");
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->openModal();
    }

    public function openEdit(int $id): void
    {
        $img = ChatAgenteImagen::findOrFail($id);
        $this->editingId   = $id;
        $this->nombre      = $img->nombre;
        $this->descripcion = $img->descripcion;
        $this->categoria   = $img->categoria;
        $this->tagsInput   = implode(', ', $img->tags ?? []);
        $this->prioridad   = $img->prioridad;
        $this->activo      = $img->activo;
        $this->archivo     = null;
        $this->openModal();
    }

    public function closeModal(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate();

        $tags = array_values(array_filter(array_map('trim', explode(',', $this->tagsInput))));

        $data = [
            'nombre'      => $this->nombre,
            'descripcion' => $this->descripcion,
            'categoria'   => $this->categoria,
            'tags'        => $tags,
            'prioridad'   => $this->prioridad,
            'activo'      => $this->activo,
        ];

        try {
            if ($this->archivo) {
                Storage::disk('public')->makeDirectory('agente');
                $extension = $this->archivo->getClientOriginalExtension();
                $filename  = \Str::uuid() . '.' . $extension;
                $this->archivo->storeAs('agente', $filename, 'public');
                $data['archivo']   = $filename;
                $data['mime_type'] = $this->archivo->getMimeType();

                // Eliminar archivo anterior si se está editando
                if ($this->editingId) {
                    $old = ChatAgenteImagen::find($this->editingId);
                    if ($old && Storage::disk('public')->exists('agente/' . $old->archivo)) {
                        Storage::disk('public')->delete('agente/' . $old->archivo);
                    }
                }
            }

            if ($this->editingId) {
                ChatAgenteImagen::findOrFail($this->editingId)->update($data);
                $this->dispatch('notify', ['type' => 'success', 'msg' => 'Imagen actualizada.']);
            } else {
                ChatAgenteImagen::create($data);
                $this->dispatch('notify', ['type' => 'success', 'msg' => 'Imagen agregada.']);
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
        $img = ChatAgenteImagen::findOrFail($id);
        $img->update(['activo' => !$img->activo]);
    }

    public function delete(int $id): void
    {
        $img = ChatAgenteImagen::findOrFail($id);
        if (Storage::disk('public')->exists('agente/' . $img->archivo)) {
            Storage::disk('public')->delete('agente/' . $img->archivo);
        }
        $img->delete();
        $this->dispatch('notify', ['type' => 'info', 'msg' => 'Imagen eliminada.']);
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'nombre', 'descripcion', 'tagsInput', 'archivo']);
        $this->categoria = 'general';
        $this->prioridad = 50;
        $this->activo    = true;
        $this->resetValidation();
    }

    public function render()
    {
        $imagenes = ChatAgenteImagen::query()
            ->when($this->filtroCategoria, fn ($q) => $q->where('categoria', $this->filtroCategoria))
            ->when($this->busqueda, fn ($q) => $q->where(function ($q2) {
                $q2->where('nombre', 'like', "%{$this->busqueda}%")
                   ->orWhere('descripcion', 'like', "%{$this->busqueda}%");
            }))
            ->orderBy('prioridad')
            ->orderBy('categoria')
            ->paginate(12);

        return view('livewire.chat.agent-library-images', [
            'imagenes'   => $imagenes,
            'categorias' => ChatMemoriaNegocio::CATEGORIAS,
        ]);
    }
}
