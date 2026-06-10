<div>
    <style>
        .img-toolbar { display:flex; gap:.75rem; align-items:center; flex-wrap:wrap; margin-bottom:1.25rem; }
        .img-search  { flex:1; min-width:200px; }

        .img-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:1rem; }

        .img-card {
            background:var(--bg-card,#fff); border-radius:.75rem; border:2px solid #e5e7eb;
            overflow:hidden; display:flex; flex-direction:column;
            transition:box-shadow .15s;
        }
        .img-card:hover  { box-shadow:0 4px 14px rgba(0,0,0,.08); }
        .img-card.inactiva { opacity:.7; border-color:#ef4444; }

        .img-thumb {
            width:100%; aspect-ratio:16/9; object-fit:cover;
            background:#f3f4f6; display:block;
        }
        .img-thumb-placeholder {
            width:100%; aspect-ratio:16/9;
            display:flex; align-items:center; justify-content:center;
            background:#f3f4f6; color:#9ca3af; font-size:2rem;
        }
        .img-body  { padding:.8rem 1rem; flex:1; display:flex; flex-direction:column; gap:.4rem; }
        .img-name  { font-weight:600; font-size:.9rem; color:var(--text-primary,#111827); }
        .img-desc  { font-size:.8rem; color:#6b7280; line-height:1.4; }
        .img-foot  { padding:.6rem 1rem; border-top:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between; }
        .img-actions { display:flex; gap:.3rem; }

        .img-btn {
            background:transparent; border:1px solid #e5e7eb; border-radius:.4rem;
            width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center;
            cursor:pointer; transition:background .1s; color:var(--text-secondary,#374151);
        }
        .img-btn:hover        { background:#f3f4f6; }
        .img-btn.danger:hover { background:#fee2e2; border-color:#fca5a5; color:#b91c1c; }

        /* Cat filter reutilizado */
        .cat-filter { display:flex; gap:.4rem; flex-wrap:wrap; margin-bottom:.85rem; }
        .cat-pill {
            padding:.25rem .75rem; border-radius:999px; font-size:.75rem; font-weight:600;
            border:1.5px solid #e5e7eb; background:var(--bg-card,#fff); cursor:pointer;
            transition:all .12s; white-space:nowrap; color:var(--text-secondary,#374151);
        }
        .cat-pill:hover  { border-color:#a5b4fc; background:#f5f3ff; }
        .cat-pill.active { border-color:#6366f1; background:#eef2ff; color:#4338ca; }

        .badge-cat { display:inline-block; padding:.1rem .45rem; border-radius:.3rem; font-size:.68rem; font-weight:600; background:#f3f4f6; color:#6b7280; }
        .badge-cat-netflix { background:#fee2e2; color:#b91c1c; }
        .badge-cat-disney_plus { background:#dbeafe; color:#1e40af; }
        .badge-cat-max { background:#ede9fe; color:#5b21b6; }
        .badge-cat-paramount_plus { background:#fef3c7; color:#78350f; }
        .badge-cat-crunchyroll { background:#fff7ed; color:#c2410c; }
        .badge-cat-flujo_tv { background:#dcfce7; color:#166534; }
        .badge-cat-spotify { background:#d1fae5; color:#065f46; }
        .badge-cat-prime_video { background:#e0f2fe; color:#0369a1; }
        .badge-cat-soporte { background:#f3f4f6; color:#374151; }

        .tags-row { display:flex; flex-wrap:wrap; gap:.25rem; }
        .tag-chip { background:#f3f4f6; color:#374151; padding:.08rem .4rem; border-radius:.3rem; font-size:.71rem; }

        /* Info banner */
        .info-banner { background:#eef2ff; border:1px solid #c7d2fe; border-radius:.5rem; padding:.75rem 1rem; margin-bottom:1rem; font-size:.8rem; color:#4338ca; }
        .info-banner code { background:#c7d2fe; border-radius:.25rem; padding:.05rem .3rem; font-size:.78rem; }

        /* Modal upload */
        .upload-zone {
            border:2px dashed #c7d2fe; border-radius:.6rem; padding:1.5rem;
            text-align:center; cursor:pointer; transition:border-color .15s, background .15s;
        }
        .upload-zone:hover { border-color:#6366f1; background:#f5f3ff; }
        .upload-preview { max-height:160px; object-fit:contain; border-radius:.4rem; border:1px solid #e5e7eb; }
    </style>

    {{-- ─── Filtro categorías ─── --}}
    <div class="cat-filter">
        <button wire:click="$set('filtroCategoria','')" class="cat-pill {{ $filtroCategoria === '' ? 'active' : '' }}">
            🖼 Todas
        </button>
        @foreach($categorias as $k => $label)
            <button wire:click="$set('filtroCategoria','{{ $k }}')" class="cat-pill {{ $filtroCategoria === $k ? 'active' : '' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ─── Banner info del agente ─── --}}
    <div class="info-banner">
        <strong>🤖 Cómo usa el agente estas imágenes:</strong>
        llama <code>GET /api/v2/chat/agente/imagenes</code> para ver qué imágenes existen y su descripción,
        luego <code>POST /api/v2/chat/agente/imagenes/{id}/enviar</code> con el número de WhatsApp del cliente para enviarla.
    </div>

    {{-- ─── Toolbar ─── --}}
    <div class="img-toolbar">
        <div class="img-search">
            <input wire:model.live.debounce.300ms="busqueda" type="text"
                   class="form-control" placeholder="Buscar por nombre o descripción…">
        </div>
        <button class="btn btn-primary" style="white-space:nowrap;" wire:click="openCreate">
            <i class="fas fa-plus me-1"></i> Agregar imagen
        </button>
    </div>

    {{-- ─── Grid de imágenes ─── --}}
    @if($imagenes->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fas fa-images fa-3x mb-3 d-block opacity-25"></i>
            Sin imágenes aún. Agrega la primera para que el agente pueda enviarla.
        </div>
    @else
        <div class="img-grid">
            @foreach($imagenes as $img)
                <div class="img-card {{ $img->activo ? '' : 'inactiva' }}">
                    @php $thumb = Storage::disk('public')->url('agente/' . $img->archivo); @endphp
                    <img src="{{ $thumb }}" alt="{{ $img->nombre }}" class="img-thumb"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="img-thumb-placeholder" style="display:none;">🖼</div>

                    <div class="img-body">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="img-name">{{ $img->nombre }}</span>
                            @if($img->categoria !== 'general')
                                <span class="badge-cat badge-cat-{{ $img->categoria }}">{{ $categorias[$img->categoria] ?? $img->categoria }}</span>
                            @endif
                        </div>
                        <div class="img-desc">{{ Str::limit($img->descripcion, 90) }}</div>
                        @if(!empty($img->tags))
                            <div class="tags-row">
                                @foreach($img->tags as $tag)<span class="tag-chip">#{{ $tag }}</span>@endforeach
                            </div>
                        @endif
                        <div style="font-size:.72rem;color:#9ca3af;font-family:monospace;">ID: {{ $img->id }}</div>
                    </div>

                    <div class="img-foot">
                        <span style="font-size:.75rem;color:#6b7280;">
                            P{{ $img->prioridad }} &nbsp;·&nbsp;
                            @if($img->activo) <span style="color:#16a34a;">✅ Activa</span>
                            @else <span style="color:#dc2626;">⏸ Inactiva</span> @endif
                        </span>
                        <div class="img-actions">
                            <button class="img-btn" wire:click="openEdit({{ $img->id }})" title="Editar">
                                <i class="fas fa-pen fa-xs"></i>
                            </button>
                            <button class="img-btn" wire:click="toggleActivo({{ $img->id }})"
                                    title="{{ $img->activo ? 'Desactivar' : 'Activar' }}">
                                <i class="fas fa-{{ $img->activo ? 'pause' : 'play' }} fa-xs"></i>
                            </button>
                            <button class="img-btn danger"
                                    wire:click="delete({{ $img->id }})"
                                    wire:confirm="¿Eliminar '{{ addslashes($img->nombre) }}'? Se borrará el archivo."
                                    title="Eliminar">
                                <i class="fas fa-trash fa-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-3">{{ $imagenes->links() }}</div>
    @endif


    {{-- ══════════════════════════════════════════════════════════
         MODAL — controlado por Livewire ($showModal), sin Alpine
         ══════════════════════════════════════════════════════════ --}}
    @if($showModal)
    <div style="position:fixed;inset:0;z-index:1040;background:rgba(0,0,0,.55);display:flex;align-items:flex-start;justify-content:center;padding:1.75rem 1rem;overflow-y:auto;">
        <div class="modal-dialog modal-lg w-100" style="margin:0;max-width:780px;">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-{{ $editingId ? 'pen' : 'image' }} me-2"></i>
                        {{ $editingId ? 'Editar imagen' : 'Agregar imagen al agente' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>

                <div class="modal-body" style="max-height:65vh; overflow-y:auto;">

                    @if($errors->any())
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 ps-3" style="font-size:.82rem;">
                                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Imagen --}}
                    <div class="mb-3">
                        <label class="fw-semibold form-label">
                            Imagen <span class="text-danger">*</span>
                            @if($editingId) <span class="text-muted fw-normal">(deja vacío para mantener la actual)</span> @endif
                        </label>

                        @if($archivo)
                            <div class="mb-2 text-center">
                                <img src="{{ $archivo->temporaryUrl() }}" class="upload-preview" alt="Preview">
                            </div>
                        @elseif($archivoActual)
                            <div class="mb-2 text-center">
                                <img src="{{ Storage::disk('public')->url('agente/' . $archivoActual) }}" class="upload-preview" alt="Actual">
                                <div style="font-size:.75rem;color:#9ca3af;margin-top:.25rem;">Imagen actual</div>
                            </div>
                        @endif

                        <input type="file" wire:model="archivo" accept="image/jpeg,image/png,image/gif,image/webp"
                               class="form-control @error('archivo') is-invalid @enderror">
                        <div wire:loading wire:target="archivo" class="mt-1" style="font-size:.78rem;color:#6366f1;">
                            <span class="spinner-border spinner-border-sm me-1"></span> Subiendo imagen…
                        </div>
                        <span style="font-size:.75rem;color:#6b7280;">JPG, PNG, GIF o WebP. Máx. 5 MB.</span>
                        @error('archivo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    {{-- Nombre --}}
                    <div class="mb-3">
                        <label class="fw-semibold form-label">Nombre <span class="text-danger">*</span></label>
                        <input wire:model="nombre" type="text" class="form-control @error('nombre') is-invalid @enderror"
                               placeholder="Ej: Catálogo Netflix enero 2026">
                        <span style="font-size:.75rem;color:#6b7280;">Nombre interno para identificar la imagen en la lista.</span>
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Descripción --}}
                    <div class="mb-3">
                        <label class="fw-semibold form-label">Descripción para el agente <span class="text-danger">*</span></label>
                        <textarea wire:model="descripcion" rows="3"
                                  class="form-control @error('descripcion') is-invalid @enderror"
                                  placeholder="Ej: Imagen con los precios actuales de Netflix: perfil 1 pantalla $X, 2 pantallas $Y. Úsala cuando el cliente pregunte por precios de Netflix."></textarea>
                        <span style="font-size:.75rem;color:#6b7280;">
                            <strong>El agente lee esto</strong> para decidir cuándo y qué imagen enviar. Sé descriptivo.
                        </span>
                        @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Categoría + Prioridad --}}
                    <div class="row g-3 mb-3">
                        <div class="col-sm-7">
                            <label class="fw-semibold form-label">Categoría</label>
                            <select wire:model="categoria" class="form-select @error('categoria') is-invalid @enderror">
                                @foreach($categorias as $k => $label)
                                    <option value="{{ $k }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('categoria') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-sm-5">
                            <label class="fw-semibold form-label">Prioridad</label>
                            <input wire:model="prioridad" type="number" min="1" max="999" class="form-control @error('prioridad') is-invalid @enderror">
                            <span style="font-size:.75rem;color:#6b7280;">1 = más prioritaria en la lista.</span>
                            @error('prioridad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Tags --}}
                    <div class="mb-3">
                        <label class="fw-semibold form-label">Tags <span class="text-muted fw-normal">(opcional)</span></label>
                        <input wire:model="tagsInput" type="text" class="form-control"
                               placeholder="netflix, precios, plan-basico">
                        <span style="font-size:.75rem;color:#6b7280;">Separados por coma.</span>
                    </div>

                    {{-- Activo --}}
                    <div class="mb-2">
                        <label class="d-flex align-items-center gap-2" style="cursor:pointer;">
                            <input wire:model="activo" type="checkbox" class="form-check-input" style="width:20px;height:20px;">
                            <span class="fw-medium">Activa</span>
                            <span style="font-size:.8rem;color:#6b7280;">(el agente solo puede enviar imágenes activas)</span>
                        </label>
                    </div>

                </div>

                @if($errors->any())
                    <div class="px-3 pb-1">
                        <div class="alert alert-danger py-2 mb-0">
                            <ul class="mb-0 ps-3" style="font-size:.82rem;">
                                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary"
                            wire:click="save"
                            wire:loading.attr="disabled"
                            wire:target="save">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        <i wire:loading.remove wire:target="save" class="fas fa-save me-1"></i>
                        {{ $editingId ? 'Guardar cambios' : 'Agregar imagen' }}
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif
</div>
