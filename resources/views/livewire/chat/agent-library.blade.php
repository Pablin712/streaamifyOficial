<div>
    <style>
        /* ── Tarjetas ── */
        .lib-toolbar { display:flex; gap:.75rem; align-items:center; flex-wrap:wrap; margin-bottom:1.5rem; }
        .lib-search  { flex:1; min-width:200px; }
        .lib-grid    { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:1rem; }

        .lib-card {
            background:var(--bg-card,#fff); border-radius:.75rem; border:2px solid #e5e7eb;
            padding:1rem 1.1rem; display:flex; flex-direction:column; gap:.55rem;
            transition:box-shadow .15s;
        }
        .lib-card:hover       { box-shadow:0 4px 14px rgba(0,0,0,.08); }
        .lib-card.vigente     { border-color:#22c55e; }
        .lib-card.programada  { border-color:#3b82f6; }
        .lib-card.expirada    { border-color:#9ca3af; }
        .lib-card.inactiva    { border-color:#ef4444; opacity:.75; }

        .lib-card-head  { display:flex; align-items:center; gap:.45rem; flex-wrap:wrap; }
        .lib-card-foot  { display:flex; align-items:center; justify-content:space-between; font-size:.77rem; color:#6b7280; margin-top:.2rem; }
        .lib-card-body  { font-size:.84rem; color:var(--text-secondary,#374151); line-height:1.45; }
        .lib-card-title { font-weight:600; font-size:.95rem; color:var(--text-primary,#111827); }
        .lib-card-clave { font-size:.74rem; color:#9ca3af; font-family:monospace; }

        .badge-tipo { display:inline-block; padding:.15rem .55rem; border-radius:999px; font-size:.71rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
        .badge-faq                { background:#ede9fe; color:#6d28d9; }
        .badge-servicio           { background:#dbeafe; color:#1d4ed8; }
        .badge-metodo_pago        { background:#d1fae5; color:#065f46; }
        .badge-politica_venta     { background:#fef3c7; color:#92400e; }
        .badge-politica_descuento { background:#fee2e2; color:#991b1b; }
        .badge-confianza          { background:#e0f2fe; color:#0369a1; }
        .badge-marca              { background:#fce7f3; color:#9d174d; }
        .badge-objecion           { background:#fef9c3; color:#78350f; }
        .badge-guion              { background:#f3f4f6; color:#374151; }
        .badge-campana            { background:#fff7ed; color:#c2410c; }
        .badge-soporte_pasos      { background:#dcfce7; color:#15803d; }
        .badge-soporte_escalado   { background:#fee2e2; color:#991b1b; }

        /* ── Filtro de categorías ── */
        .cat-filter { display:flex; gap:.4rem; flex-wrap:wrap; margin-bottom:.85rem; }
        .cat-pill {
            padding:.25rem .75rem; border-radius:999px; font-size:.75rem; font-weight:600;
            border:1.5px solid #e5e7eb; background:var(--bg-card,#fff); cursor:pointer;
            transition:all .12s; white-space:nowrap; color:var(--text-secondary,#374151);
        }
        .cat-pill:hover  { border-color:#a5b4fc; background:#f5f3ff; }
        .cat-pill.active { border-color:#6366f1; background:#eef2ff; color:#4338ca; }

        /* ── Badge de categoría en tarjeta ── */
        .badge-cat {
            display:inline-block; padding:.1rem .45rem; border-radius:.3rem;
            font-size:.68rem; font-weight:600; letter-spacing:.02em;
            background:#f3f4f6; color:#6b7280;
        }
        .badge-cat-netflix        { background:#fee2e2; color:#b91c1c; }
        .badge-cat-disney_plus    { background:#dbeafe; color:#1e40af; }
        .badge-cat-max            { background:#ede9fe; color:#5b21b6; }
        .badge-cat-paramount_plus { background:#fef3c7; color:#78350f; }
        .badge-cat-crunchyroll    { background:#fff7ed; color:#c2410c; }
        .badge-cat-flujo_tv       { background:#dcfce7; color:#166534; }
        .badge-cat-spotify        { background:#d1fae5; color:#065f46; }
        .badge-cat-prime_video    { background:#e0f2fe; color:#0369a1; }
        .badge-cat-soporte        { background:#f3f4f6; color:#374151; }

        .vpill { display:inline-flex; align-items:center; gap:.2rem; padding:.12rem .5rem; border-radius:999px; font-size:.71rem; font-weight:700; }
        .vpill.activa    { background:#dcfce7; color:#15803d; }
        .vpill.programada{ background:#dbeafe; color:#1d4ed8; }
        .vpill.expirada  { background:#f3f4f6; color:#6b7280; }
        .vpill.inactiva  { background:#fee2e2; color:#b91c1c; }

        .tags-row { display:flex; flex-wrap:wrap; gap:.25rem; }
        .tag-chip { background:#f3f4f6; color:#374151; padding:.08rem .4rem; border-radius:.3rem; font-size:.71rem; }

        .lib-card-actions { display:flex; gap:.35rem; }
        .lib-btn-icon {
            background:transparent; border:1px solid #e5e7eb; border-radius:.4rem;
            width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center;
            cursor:pointer; transition:background .1s; color:var(--text-secondary,#374151);
        }
        .lib-btn-icon:hover         { background:#f3f4f6; }
        .lib-btn-icon.danger:hover  { background:#fee2e2; border-color:#fca5a5; color:#b91c1c; }

        /* ── Modal: selector de tipo ── */
        .tipo-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(115px,1fr)); gap:.35rem; }

        /* El label actúa como la tarjeta visual */
        .tipo-card {
            position:relative;
            border:2px solid #e5e7eb; border-radius:.45rem; padding:.45rem .5rem;
            cursor:pointer; font-size:.8rem; font-weight:500; text-align:center;
            transition:border-color .1s, background .1s; background:var(--bg-card,#fff);
            user-select:none; display:block;
        }
        /* Radio input invisible pero accesible — pointer-events activos para que dispare change */
        .tipo-card input[type=radio] {
            position:absolute; opacity:0; width:1px; height:1px; overflow:hidden;
        }
        /* Seleccionado: solo CSS :has — no se usa clase PHP */
        .tipo-card:has(input:checked) {
            border-color:#6366f1 !important; background:#eef2ff !important; color:#4338ca !important;
        }
        .tipo-card:hover:not(:has(input:checked)) { border-color:#a5b4fc; background:#f5f3ff; }

        /* ── Modal: selector de visibilidad ── */
        .vis-btns { display:flex; gap:.35rem; flex-wrap:wrap; }
        .vis-btn {
            flex:1; min-width:100px; position:relative;
            border:2px solid #e5e7eb; border-radius:.45rem; padding:.45rem .5rem;
            font-size:.79rem; font-weight:500; cursor:pointer; text-align:center;
            background:var(--bg-card,#fff); transition:border-color .1s, background .1s;
            user-select:none; display:block;
        }
        .vis-btn input[type=radio] {
            position:absolute; opacity:0; width:1px; height:1px; overflow:hidden;
        }
        .vis-btn:has(input:checked) {
            border-color:#6366f1 !important; background:#eef2ff !important; color:#4338ca !important;
        }
        .vis-btn:hover:not(:has(input:checked)) { border-color:#a5b4fc; }

        /* ── Otros ── */
        .campaign-box { background:#fff7ed; border:1px solid #fdba74; border-radius:.5rem; padding:.8rem; }
        .lib-hint { font-size:.75rem; color:var(--text-muted,#6c757d); line-height:1.4; margin-top:.2rem; display:block; }
        .lib-field { margin-bottom:.85rem; }
        .lib-field:last-child { margin-bottom:0; }
        .lib-row  { display:grid; grid-template-columns:1fr 1fr; gap:.65rem; }
        @media(max-width:540px){ .lib-row{ grid-template-columns:1fr; } }
        .flow-step { display:flex; align-items:flex-start; gap:.6rem; padding:.45rem .7rem; background:var(--bg-light,#f8f9fa); border-radius:.4rem; font-size:.8rem; margin-bottom:.3rem; }
        .flow-num  { flex-shrink:0; width:19px; height:19px; border-radius:50%; background:#6366f1; color:#fff; font-size:.68rem; font-weight:700; display:flex; align-items:center; justify-content:center; }
    </style>

    {{-- ─── Filtro de categorías (carpetas) ─── --}}
    <div class="cat-filter">
        <button wire:click="$set('filtroCategoria','')"
                class="cat-pill {{ $filtroCategoria === '' ? 'active' : '' }}">
            📚 Todas
        </button>
        @foreach($categorias as $k => $label)
            <button wire:click="$set('filtroCategoria','{{ $k }}')"
                    class="cat-pill {{ $filtroCategoria === $k ? 'active' : '' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ─── Toolbar ─── --}}
    <div class="lib-toolbar">
        <div class="lib-search">
            <input wire:model.live.debounce.300ms="busqueda" type="text"
                   class="form-control" placeholder="Buscar por título, clave o contenido…">
        </div>
        <select wire:model.live="filtroTipo" class="form-select" style="max-width:190px;">
            <option value="">Todos los tipos</option>
            @foreach($tipos as $k => $label)
                <option value="{{ $k }}">{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary" style="white-space:nowrap;" wire:click="openCreate">
            <i class="fas fa-plus me-1"></i> Nueva entrada
        </button>
    </div>

    {{-- ─── Grid de tarjetas ─── --}}
    @if($entries->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fas fa-book-open fa-3x mb-3 d-block opacity-25"></i>
            La biblioteca está vacía. Crea la primera entrada.
        </div>
    @else
        <div class="lib-grid">
            @foreach($entries as $e)
                @php $vigencia = $e->estadoVigencia(); @endphp
                <div class="lib-card {{ $vigencia }}">
                    <div class="lib-card-head">
                        <span class="badge-tipo badge-{{ str_replace('ñ','n',$e->tipo) }}">{{ $tipos[$e->tipo] ?? $e->tipo }}</span>
                        @if(($e->categoria ?? 'general') !== 'general')
                            <span class="badge-cat badge-cat-{{ $e->categoria }}">{{ $categorias[$e->categoria] ?? $e->categoria }}</span>
                        @endif
                        <span class="vpill {{ $vigencia }}">
                            @if($vigencia==='activa') ✅ Activa
                            @elseif($vigencia==='programada') 🕐 Programada
                            @elseif($vigencia==='expirada') ⏰ Expirada
                            @else ⏸ Inactiva @endif
                        </span>
                        <span class="lib-card-clave ms-auto">{{ $e->clave }}</span>
                    </div>
                    <div class="lib-card-title">{{ $e->titulo }}</div>
                    @if($e->inicio_at || $e->fin_at)
                        <div style="font-size:.76rem;color:#6b7280;">
                            📅 {{ $e->inicio_at?->format('d/m/Y') ?? '—' }} → {{ $e->fin_at?->format('d/m/Y') ?? '∞' }}
                        </div>
                    @endif
                    <div class="lib-card-body">{{ Str::limit($e->resumen ?: $e->contenido, 110) }}</div>
                    @if(!empty($e->tags))
                        <div class="tags-row">
                            @foreach($e->tags as $tag)<span class="tag-chip">#{{ $tag }}</span>@endforeach
                        </div>
                    @endif
                    <div class="lib-card-foot">
                        <span>
                            Prioridad <strong>{{ $e->prioridad }}</strong> &nbsp;·&nbsp;
                            @if($e->visibilidad==='cliente') 👤 Cliente
                            @elseif($e->visibilidad==='interna') 🔒 Interna
                            @else 🔁 Ambas @endif
                        </span>
                        <div class="lib-card-actions">
                            <button class="lib-btn-icon" wire:click="openEdit({{ $e->id }})" title="Editar">
                                <i class="fas fa-pen fa-xs"></i>
                            </button>
                            <button class="lib-btn-icon" wire:click="toggleActivo({{ $e->id }})"
                                    title="{{ $e->activo ? 'Desactivar' : 'Activar' }}">
                                <i class="fas fa-{{ $e->activo ? 'pause' : 'play' }} fa-xs"></i>
                            </button>
                            <button class="lib-btn-icon danger"
                                    wire:click="delete({{ $e->id }})"
                                    wire:confirm="¿Eliminar '{{ addslashes($e->titulo) }}'?"
                                    title="Eliminar">
                                <i class="fas fa-trash fa-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-3">{{ $entries->links() }}</div>
    @endif


    {{-- ══════════════════════════════════════════════════════════
         MODAL — patrón estándar <x-modal> del proyecto
         $this->js() abre/cierra DESPUÉS del morph → form siempre fresco
         wire:key fuerza recreación del DOM al cambiar de entrada
         ══════════════════════════════════════════════════════════ --}}
    <x-modal name="agentLibraryModal" :show="false" maxWidth="xl">

        {{-- wire:key cambia al editar diferente ID → Livewire recrea los inputs --}}
        <div wire:key="agent-form-{{ $editingId ?? 0 }}">

        {{-- Header --}}
        <div class="modal-header">
            <h5 class="modal-title">
                <i class="fas fa-{{ $editingId ? 'pen' : 'plus-circle' }} me-2"></i>
                {{ $editingId ? 'Editar entrada' : 'Nueva entrada en la biblioteca' }}
            </h5>
            <button type="button" class="btn-close"
                    onclick="window.dispatchEvent(new CustomEvent('close-modal',{detail:'agentLibraryModal'}))"
                    wire:click="closeModal">
            </button>
        </div>

        {{-- Body --}}
        <div class="modal-body" style="max-height:72vh; overflow-y:auto;">

            {{-- Flujo de uso --}}
            <div style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:.5rem;padding:.75rem;margin-bottom:1rem;">
                <p class="fw-bold mb-2" style="font-size:.79rem;color:#4338ca;margin:0 0 .45rem;">
                    <i class="fas fa-robot me-1"></i> ¿Cómo usa el agente esta biblioteca?
                </p>
                <div class="flow-step"><span class="flow-num">1</span><span>WhatsApp llega → n8n llama <code>GET /api/v2/chat/router/context</code></span></div>
                <div class="flow-step"><span class="flow-num">2</span><span>La API retorna hasta <strong>40 entradas activas</strong>, filtradas por fecha, ordenadas por categoría y prioridad</span></div>
                <div class="flow-step"><span class="flow-num">3</span><span>n8n las inyecta como <strong>CONTEXTO DE NEGOCIO</strong> en el prompt del subagente (Vendedor, Soporte, Cobranzas…)</span></div>
                <div class="flow-step" style="margin-bottom:0;"><span class="flow-num">4</span><span>El agente responde usando ese contexto — sin tocar n8n</span></div>
            </div>

            {{-- Errores --}}
            @if($errors->any())
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 ps-3" style="font-size:.82rem;">
                        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- 1. Tipo — label + radio oculto + wire:model.live --}}
            {{-- CSS :has(input:checked) da feedback visual instantáneo sin JS --}}
            <div class="lib-field">
                <label class="fw-semibold form-label">
                    Tipo de entrada <span class="text-danger">*</span>
                </label>
                <span class="lib-hint" style="margin-bottom:.5rem;">
                    Clasifica el conocimiento. <strong>Campaña</strong> activa el selector de fechas de vigencia.
                </span>
                <div class="tipo-grid">
                    @foreach($tipos as $k => $label)
                        <label class="tipo-card">
                            <input type="radio" name="lib_tipo" wire:model.live="tipo" value="{{ $k }}" @checked($tipo === $k)>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- 1b. Categoría / Carpeta ── --}}
            <div class="lib-field">
                <label class="fw-semibold form-label">
                    Carpeta / Categoría <span class="text-danger">*</span>
                </label>
                <select wire:model="categoria" class="form-select">
                    @foreach($categorias as $k => $label)
                        <option value="{{ $k }}">{{ $label }}</option>
                    @endforeach
                </select>
                <span class="lib-hint">
                    <strong>General</strong> = aplica a todos los agentes. Servicios (Netflix, Disney+…) = solo el agente de Soporte los usa cuando detecta ese servicio.
                </span>
            </div>

            {{-- 2a. Fechas campaña (solo si tipo === 'campaña') --}}
            @if($tipo === 'campaña')
                <div class="lib-field campaign-box">
                    <p class="fw-semibold mb-1" style="font-size:.83rem;color:#c2410c;">
                        🎯 Campaña temporal — solo activa durante este período
                    </p>
                    <span class="lib-hint" style="margin-bottom:.6rem;display:block;">
                        Fuera del rango el agente la ignora automáticamente. Ideal para Mundial, Black Friday, estrenos, etc.
                    </span>
                    <div class="lib-row">
                        <div>
                            <label class="form-label">Inicio</label>
                            <input wire:model="inicio_at" type="date" class="form-control">
                            <span class="lib-hint">Primer día activo</span>
                        </div>
                        <div>
                            <label class="form-label">Fin</label>
                            <input wire:model="fin_at" type="date" class="form-control">
                            <span class="lib-hint">Último día activo</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 3. Título + Clave --}}
            <div class="lib-row lib-field">
                <div>
                    <label class="fw-semibold form-label">Título <span class="text-danger">*</span></label>
                    <input wire:model="titulo" type="text" class="form-control"
                           placeholder="Ej: Promoción Mundial 2026 — Paramount+">
                    <span class="lib-hint">Nombre interno. El cliente no lo ve.</span>
                </div>
                <div>
                    <label class="fw-semibold form-label">Clave única <span class="text-danger">*</span></label>
                    <input wire:model="clave" type="text" class="form-control"
                           placeholder="ej: promo-mundial-2026">
                    <span class="lib-hint">Minúsculas y guiones. Único por tipo.</span>
                </div>
            </div>

            {{-- 4. Contenido --}}
            <div class="lib-field">
                <label class="fw-semibold form-label">Contenido <span class="text-danger">*</span></label>
                <textarea wire:model="contenido" rows="5" class="form-control"
                          placeholder="Escribe lo que el agente debe saber. Ej:&#10;Durante el Mundial 2026 recomienda Paramount+. Precio: $X/mes. Todos los partidos en vivo. Cierre: 'Es la única plataforma con todos los partidos en HD.'"></textarea>
                <span class="lib-hint">
                    <strong>Este texto es el que el agente lee.</strong>
                    Lenguaje natural — precios, argumentos, instrucciones tipo "si preguntan X, di Y". Máx. ~1000 caracteres.
                </span>
            </div>

            {{-- 5. Resumen --}}
            <div class="lib-field">
                <label class="fw-semibold form-label">Resumen <span class="text-muted fw-normal">(opcional)</span></label>
                <input wire:model="resumen" type="text" class="form-control"
                       placeholder="Descripción corta para mostrar en la tarjeta">
                <span class="lib-hint">Solo para esta lista — el agente siempre recibe el Contenido completo.</span>
            </div>

            {{-- 6. Tags --}}
            <div class="lib-field">
                <label class="fw-semibold form-label">Tags <span class="text-muted fw-normal">(opcional)</span></label>
                <input wire:model="tagsInput" type="text" class="form-control"
                       placeholder="mundial, paramount, streaming">
                <span class="lib-hint">Separados por coma. Para búsqueda y categorización futura.</span>
            </div>

            {{-- 7. Visibilidad — label + radio oculto + wire:model.live --}}
            <div class="lib-field">
                <label class="fw-semibold form-label">Visibilidad</label>
                <span class="lib-hint" style="margin-bottom:.45rem;display:block;">
                    Controla en qué contexto el agente usa esta entrada.
                </span>
                <div class="vis-btns">
                    <label class="vis-btn">
                        <input type="radio" name="lib_vis" wire:model.live="visibilidad" value="cliente" @checked($visibilidad === 'cliente')>
                        👤 <strong>Cliente</strong>
                        <div style="font-size:.71rem;font-weight:400;margin-top:.1rem;">Para responder al cliente</div>
                    </label>
                    <label class="vis-btn">
                        <input type="radio" name="lib_vis" wire:model.live="visibilidad" value="interna" @checked($visibilidad === 'interna')>
                        🔒 <strong>Interna</strong>
                        <div style="font-size:.71rem;font-weight:400;margin-top:.1rem;">Solo referencia del agente</div>
                    </label>
                    <label class="vis-btn">
                        <input type="radio" name="lib_vis" wire:model.live="visibilidad" value="ambas" @checked($visibilidad === 'ambas')>
                        🔁 <strong>Ambas</strong>
                        <div style="font-size:.71rem;font-weight:400;margin-top:.1rem;">Todos los contextos</div>
                    </label>
                </div>
            </div>

            {{-- 8. Prioridad + Activo --}}
            <div class="lib-row lib-field">
                <div>
                    <label class="fw-semibold form-label">Prioridad</label>
                    <input wire:model="prioridad" type="number" min="1" max="999" class="form-control" placeholder="50">
                    <span class="lib-hint">
                        <strong>1 = máxima.</strong> El agente recibe hasta 40 entradas.
                        Usa 1–10 para políticas críticas, 10–30 para FAQ, 50+ para info secundaria.
                    </span>
                </div>
                <div>
                    <label class="fw-semibold form-label">Estado</label>
                    <label class="d-flex align-items-center gap-2 mt-2" style="cursor:pointer;user-select:none;">
                        <input wire:model="activo" type="checkbox" class="form-check-input"
                               style="width:20px;height:20px;cursor:pointer;">
                        <span class="fw-medium">Activo</span>
                    </label>
                    <span class="lib-hint">Desactivar pausa el contexto sin borrarlo.</span>
                </div>
            </div>

            {{-- 9. Fechas para tipos no-campaña --}}
            @if($tipo !== 'campaña')
                <div class="lib-field">
                    <label class="fw-semibold form-label">
                        Rango de vigencia <span class="text-muted fw-normal">(opcional)</span>
                    </label>
                    <span class="lib-hint" style="margin-bottom:.4rem;display:block;">Deja vacío para que sea permanente.</span>
                    <div class="lib-row">
                        <div>
                            <label class="form-label" style="font-weight:400;">Desde</label>
                            <input wire:model="inicio_at" type="date" class="form-control">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight:400;">Hasta</label>
                            <input wire:model="fin_at" type="date" class="form-control">
                        </div>
                    </div>
                </div>
            @endif

        </div>{{-- /modal-body --}}

        {{-- Footer --}}
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                    onclick="window.dispatchEvent(new CustomEvent('close-modal',{detail:'agentLibraryModal'}))"
                    wire:click="closeModal">
                Cancelar
            </button>
            <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                <i wire:loading.remove wire:target="save" class="fas fa-save me-1"></i>
                {{ $editingId ? 'Guardar cambios' : 'Crear entrada' }}
            </button>
        </div>

        </div>{{-- /wire:key wrapper --}}
    </x-modal>

</div>
