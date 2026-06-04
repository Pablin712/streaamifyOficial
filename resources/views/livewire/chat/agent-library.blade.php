<div>
    <style>
        /* ─── Grid de tarjetas ─── */
        .lib-toolbar { display:flex; gap:.75rem; align-items:center; flex-wrap:wrap; margin-bottom:1.5rem; }
        .lib-search  { flex:1; min-width:200px; }
        .lib-grid    { display:grid; grid-template-columns:repeat(auto-fill,minmax(340px,1fr)); gap:1rem; }

        .lib-card {
            background:var(--bg-card,#fff); border-radius:.75rem; border:2px solid #e5e7eb;
            padding:1.1rem 1.2rem; display:flex; flex-direction:column; gap:.6rem;
            transition:box-shadow .15s;
        }
        .lib-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.08); }
        .lib-card.vigente    { border-color:#22c55e; }
        .lib-card.programada { border-color:#3b82f6; }
        .lib-card.expirada   { border-color:#9ca3af; }
        .lib-card.inactiva   { border-color:#ef4444; opacity:.75; }

        .lib-card-head  { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
        .lib-card-foot  { display:flex; align-items:center; justify-content:space-between; font-size:.78rem; color:#6b7280; margin-top:.25rem; }
        .lib-card-body  { font-size:.85rem; color:var(--text-secondary,#374151); line-height:1.5; }
        .lib-card-title { font-weight:600; font-size:.97rem; color:var(--text-primary,#111827); }
        .lib-card-clave { font-size:.75rem; color:#9ca3af; font-family:monospace; }

        .badge-tipo {
            display:inline-block; padding:.18rem .6rem; border-radius:999px;
            font-size:.73rem; font-weight:600; text-transform:uppercase; letter-spacing:.03em;
        }
        .badge-faq               { background:#ede9fe; color:#6d28d9; }
        .badge-servicio          { background:#dbeafe; color:#1d4ed8; }
        .badge-metodo_pago       { background:#d1fae5; color:#065f46; }
        .badge-politica_venta    { background:#fef3c7; color:#92400e; }
        .badge-politica_descuento{ background:#fee2e2; color:#991b1b; }
        .badge-confianza         { background:#e0f2fe; color:#0369a1; }
        .badge-marca             { background:#fce7f3; color:#9d174d; }
        .badge-objecion          { background:#fef9c3; color:#78350f; }
        .badge-guion             { background:#f3f4f6; color:#374151; }
        .badge-campana           { background:#fff7ed; color:#c2410c; }

        .vigencia-pill {
            display:inline-flex; align-items:center; gap:.25rem;
            padding:.15rem .55rem; border-radius:999px; font-size:.72rem; font-weight:600;
        }
        .vigencia-pill.activa    { background:#dcfce7; color:#15803d; }
        .vigencia-pill.programada{ background:#dbeafe; color:#1d4ed8; }
        .vigencia-pill.expirada  { background:#f3f4f6; color:#6b7280; }
        .vigencia-pill.inactiva  { background:#fee2e2; color:#b91c1c; }

        .tags-row { display:flex; flex-wrap:wrap; gap:.3rem; }
        .tag-chip { background:#f3f4f6; color:#374151; padding:.1rem .45rem; border-radius:.3rem; font-size:.72rem; }

        .lib-card-actions { display:flex; gap:.4rem; }
        .lib-btn-icon {
            background:transparent; border:1px solid #e5e7eb; border-radius:.4rem;
            width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;
            cursor:pointer; transition:background .12s;
        }
        .lib-btn-icon:hover { background:#f3f4f6; }
        .lib-btn-icon.danger:hover { background:#fee2e2; border-color:#fca5a5; }

        /* ─── Dentro del modal ─── */
        .tipo-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:.4rem; }
        .tipo-card {
            border:2px solid #e5e7eb; border-radius:.5rem; padding:.5rem .7rem;
            cursor:pointer; font-size:.82rem; font-weight:500; text-align:center;
            transition:all .12s; background:var(--bg-card,#fff);
        }
        .tipo-card.selected { border-color:#6366f1; background:#eef2ff; color:#4338ca; }
        .tipo-card:hover:not(.selected) { border-color:#a5b4fc; }

        .campaign-box {
            background:#fff7ed; border:1px solid #fdba74; border-radius:.5rem; padding:.85rem 1rem;
        }

        .lib-field { display:flex; flex-direction:column; gap:.2rem; }
        .lib-hint  { font-size:.76rem; color:var(--text-muted,#6c757d); line-height:1.4; }
        .lib-row   { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; }
        @media(max-width:560px){ .lib-row{ grid-template-columns:1fr; } }

        .vis-btns { display:flex; gap:.4rem; flex-wrap:wrap; }
        .vis-btn {
            flex:1; min-width:120px; border:2px solid #e5e7eb; border-radius:.45rem;
            padding:.5rem .6rem; font-size:.8rem; font-weight:500;
            cursor:pointer; text-align:center; background:var(--bg-card,#fff); transition:all .12s;
        }
        .vis-btn.selected { border-color:#6366f1; background:#eef2ff; color:#4338ca; }
        .vis-btn:hover:not(.selected) { border-color:#a5b4fc; }

        .flow-step {
            display:flex; align-items:flex-start; gap:.75rem;
            padding:.6rem .8rem; background:var(--bg-light,#f8f9fa);
            border-radius:.4rem; font-size:.82rem;
        }
        .flow-step-num {
            flex-shrink:0; width:22px; height:22px; border-radius:50%;
            background:#6366f1; color:#fff; font-size:.72rem; font-weight:700;
            display:flex; align-items:center; justify-content:center;
        }
    </style>

    {{-- ─── Toolbar ─── --}}
    <div class="lib-toolbar">
        <div class="lib-search">
            <input wire:model.live.debounce.300ms="busqueda" type="text"
                   class="form-control"
                   placeholder="Buscar por título, clave o contenido…">
        </div>
        <select wire:model.live="filtroTipo" class="form-select" style="max-width:200px;">
            <option value="">Todos los tipos</option>
            @foreach($tipos as $k => $label)
                <option value="{{ $k }}">{{ $label }}</option>
            @endforeach
        </select>
        <button wire:click="openCreate" class="btn btn-primary" style="white-space:nowrap;">
            <i class="fas fa-plus me-1"></i> Nueva entrada
        </button>
    </div>

    {{-- ─── Grid de tarjetas ─── --}}
    @if($entries->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fas fa-book-open fa-3x mb-3 opacity-25"></i>
            <p>La biblioteca está vacía. Crea la primera entrada.</p>
        </div>
    @else
        <div class="lib-grid">
            @foreach($entries as $e)
                @php $vigencia = $e->estadoVigencia(); @endphp
                <div class="lib-card {{ $vigencia }}">

                    <div class="lib-card-head">
                        <span class="badge-tipo badge-{{ str_replace(['ñ'],['n'], str_replace('_','_',$e->tipo)) }}">
                            {{ $tipos[$e->tipo] ?? $e->tipo }}
                        </span>
                        <span class="vigencia-pill {{ $vigencia }}">
                            @if($vigencia === 'activa')         ✅ Activa
                            @elseif($vigencia === 'programada') 🕐 Programada
                            @elseif($vigencia === 'expirada')   ⏰ Expirada
                            @else                               ⏸ Inactiva
                            @endif
                        </span>
                        <span class="lib-card-clave ms-auto">{{ $e->clave }}</span>
                    </div>

                    <div class="lib-card-title">{{ $e->titulo }}</div>

                    @if($e->inicio_at || $e->fin_at)
                        <div style="font-size:.77rem;color:#6b7280;">
                            📅 {{ $e->inicio_at?->format('d/m/Y') ?? '—' }} → {{ $e->fin_at?->format('d/m/Y') ?? '∞' }}
                        </div>
                    @endif

                    <div class="lib-card-body">{{ Str::limit($e->resumen ?: $e->contenido, 120) }}</div>

                    @if(!empty($e->tags))
                        <div class="tags-row">
                            @foreach($e->tags as $tag)
                                <span class="tag-chip">#{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="lib-card-foot">
                        <span>
                            Prioridad <strong>{{ $e->prioridad }}</strong>
                            &nbsp;·&nbsp;
                            @if($e->visibilidad === 'cliente')  👤 Cliente
                            @elseif($e->visibilidad === 'interna') 🔒 Interna
                            @else 🔁 Ambas
                            @endif
                        </span>
                        <div class="lib-card-actions">
                            <button class="lib-btn-icon" wire:click="openEdit({{ $e->id }})" title="Editar">
                                <i class="fas fa-pen fa-xs"></i>
                            </button>
                            <button class="lib-btn-icon" wire:click="toggleActivo({{ $e->id }})"
                                    title="{{ $e->activo ? 'Desactivar' : 'Activar' }}">
                                @if($e->activo)
                                    <i class="fas fa-pause fa-xs"></i>
                                @else
                                    <i class="fas fa-play fa-xs"></i>
                                @endif
                            </button>
                            <button class="lib-btn-icon danger"
                                    wire:click="delete({{ $e->id }})"
                                    wire:confirm="¿Eliminar '{{ addslashes($e->titulo) }}'? Esta acción no se puede deshacer."
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


    {{-- ═══════════════════════════════════════════════════════════════
         MODAL — usa el sistema de modales del proyecto (Alpine + $wire)
         ═══════════════════════════════════════════════════════════════ --}}
    <div
        x-data="{ get open() { return $wire.showModal } }"
        x-show="open"
        x-init="$watch('open', val => document.body.style.overflow = val ? 'hidden' : '')"
        x-on:keydown.escape.window="open && $wire.set('showModal', false)"
        class="modal-overlay"
        style="display:none;"
    >
        <div class="modal-backdrop" x-on:click="$wire.set('showModal', false)"></div>

        <div class="modal-dialog modal-3xl"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <div class="modal-content">

                {{-- Header --}}
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if($editingId)
                            <i class="fas fa-pen me-2"></i> Editar entrada de la biblioteca
                        @else
                            <i class="fas fa-plus-circle me-2"></i> Nueva entrada en la biblioteca
                        @endif
                    </h5>
                    <button type="button" class="btn-close"
                            x-on:click="$wire.set('showModal', false)"
                            aria-label="Cerrar"></button>
                </div>

                {{-- Body --}}
                <div class="modal-body">

                    {{-- Cómo usa el agente esta biblioteca --}}
                    <div style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:.5rem;padding:.85rem 1rem;margin-bottom:.5rem;">
                        <p style="font-size:.8rem;font-weight:700;color:#4338ca;margin:0 0 .5rem;">
                            <i class="fas fa-robot me-1"></i> ¿Cómo usa el agente esta biblioteca?
                        </p>
                        <div style="display:flex;flex-direction:column;gap:.35rem;">
                            <div class="flow-step">
                                <span class="flow-step-num">1</span>
                                <span>El cliente envía un WhatsApp → n8n recibe el mensaje y llama a <code>GET /api/v2/chat/router/context</code></span>
                            </div>
                            <div class="flow-step">
                                <span class="flow-step-num">2</span>
                                <span>La API devuelve hasta <strong>15 entradas activas</strong> de esta biblioteca, ordenadas por prioridad, filtrando por fecha de vigencia automáticamente</span>
                            </div>
                            <div class="flow-step">
                                <span class="flow-step-num">3</span>
                                <span>n8n inyecta esas entradas como <strong>CONTEXTO DE NEGOCIO</strong> en el prompt del subagente (Vendedor, Soporte, Cobranzas…)</span>
                            </div>
                            <div class="flow-step">
                                <span class="flow-step-num">4</span>
                                <span>El agente usa ese contexto para responder con información actualizada, sin necesidad de modificar n8n</span>
                            </div>
                        </div>
                    </div>

                    {{-- Errores de validación --}}
                    @if($errors->any())
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 ps-3" style="font-size:.83rem;">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- ── 1. Tipo ── --}}
                    <div class="lib-field">
                        <label class="fw-semibold">
                            Tipo de entrada
                            <span class="text-danger">*</span>
                        </label>
                        <p class="lib-hint mb-1">
                            Clasifica el conocimiento para que el agente lo interprete correctamente.
                            <em>Campaña</em> es el tipo especial para promociones con fechas límite.
                        </p>
                        <div class="tipo-grid">
                            @foreach($tipos as $k => $label)
                                <div class="tipo-card {{ $tipo === $k ? 'selected' : '' }}"
                                     wire:click="$set('tipo', '{{ $k }}')">
                                    {{ $label }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ── 2. Rango campaña (solo si tipo = campaña) ── --}}
                    @if($tipo === 'campaña')
                        <div class="campaign-box">
                            <p class="fw-semibold mb-1" style="color:#c2410c;font-size:.85rem;">
                                🎯 Campaña temporal — fechas de vigencia obligatorias
                            </p>
                            <p class="lib-hint mb-2">
                                El agente <strong>solo incluirá esta entrada durante este rango</strong>.
                                Fuera de él la ignora completamente, sin que tengas que hacer nada.
                                Ideal para: temporada del Mundial, Black Friday, lanzamiento de Paramount+, etc.
                            </p>
                            <div class="lib-row">
                                <div class="lib-field">
                                    <label>Inicio de campaña</label>
                                    <input wire:model="inicio_at" type="date" class="form-control">
                                    <span class="lib-hint">Primer día en que el agente usará este contexto</span>
                                </div>
                                <div class="lib-field">
                                    <label>Fin de campaña</label>
                                    <input wire:model="fin_at" type="date" class="form-control">
                                    <span class="lib-hint">Último día. A partir del día siguiente, se ignora</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ── 3. Título + Clave ── --}}
                    <div class="lib-row">
                        <div class="lib-field">
                            <label class="fw-semibold">Título <span class="text-danger">*</span></label>
                            <input wire:model="titulo" type="text" class="form-control"
                                   placeholder="Ej: Promoción Mundial 2026 — Paramount+">
                            <span class="lib-hint">
                                Nombre interno para identificarlo en esta lista.
                                El agente no se lo muestra al cliente.
                            </span>
                        </div>
                        <div class="lib-field">
                            <label class="fw-semibold">Clave única <span class="text-danger">*</span></label>
                            <input wire:model="clave" type="text" class="form-control"
                                   placeholder="ej: promo-mundial-2026">
                            <span class="lib-hint">
                                Identificador técnico, único por tipo.
                                Usa minúsculas y guiones.
                            </span>
                        </div>
                    </div>

                    {{-- ── 4. Contenido ── --}}
                    <div class="lib-field">
                        <label class="fw-semibold">Contenido <span class="text-danger">*</span></label>
                        <textarea wire:model="contenido" rows="5" class="form-control"
                                  placeholder="Escribe aquí el conocimiento que el agente debe tener. Ejemplo:&#10;&#10;Durante el Mundial 2026 (junio–julio), si el cliente pregunta por fútbol, canales de deportes o transmisión en vivo, recomienda Paramount+. Precio: $X/mes. Incluye todos los partidos del Mundial en vivo. Argumento de cierre: 'Es la única plataforma con todos los partidos en HD, no te vas a perder ninguno.'"></textarea>
                        <span class="lib-hint">
                            <strong>Este es el texto que el agente lee.</strong>
                            Escribe en lenguaje natural, como si le hablaras a un vendedor nuevo.
                            Puedes incluir precios, argumentos, instrucciones ("si preguntan X, di Y"),
                            políticas o cualquier información útil. Máx. recomendado: ~1000 caracteres
                            para no saturar el prompt.
                        </span>
                    </div>

                    {{-- ── 5. Resumen ── --}}
                    <div class="lib-field">
                        <label class="fw-semibold">Resumen <span class="text-muted fw-normal">(opcional)</span></label>
                        <input wire:model="resumen" type="text" class="form-control"
                               placeholder="Versión ultra-corta para la tarjeta de esta lista">
                        <span class="lib-hint">
                            Si lo rellenas, aparece en la tarjeta de esta biblioteca.
                            No afecta al agente — el agente siempre recibe el <em>Contenido</em> completo.
                        </span>
                    </div>

                    {{-- ── 6. Tags ── --}}
                    <div class="lib-field">
                        <label class="fw-semibold">Tags <span class="text-muted fw-normal">(opcional)</span></label>
                        <input wire:model="tagsInput" type="text" class="form-control"
                               placeholder="mundial, paramount, streaming, fútbol">
                        <span class="lib-hint">
                            Separados por coma. Hoy son decorativos, pero en futuras versiones
                            permitirán filtros semánticos ("¿qué entradas tocan el tema streaming?").
                        </span>
                    </div>

                    {{-- ── 7. Visibilidad ── --}}
                    <div class="lib-field">
                        <label class="fw-semibold">Visibilidad</label>
                        <span class="lib-hint mb-1">
                            Controla en qué contexto usa el agente esta entrada.
                        </span>
                        <div class="vis-btns">
                            <div class="vis-btn {{ $visibilidad === 'cliente' ? 'selected' : '' }}"
                                 wire:click="$set('visibilidad', 'cliente')">
                                👤 <strong>Cliente</strong>
                                <div style="font-size:.73rem;font-weight:400;margin-top:.15rem;">
                                    El agente lo usa para responder al cliente directamente
                                </div>
                            </div>
                            <div class="vis-btn {{ $visibilidad === 'interna' ? 'selected' : '' }}"
                                 wire:click="$set('visibilidad', 'interna')">
                                🔒 <strong>Interna</strong>
                                <div style="font-size:.73rem;font-weight:400;margin-top:.15rem;">
                                    Solo referencia para el agente, no para el cliente
                                </div>
                            </div>
                            <div class="vis-btn {{ $visibilidad === 'ambas' ? 'selected' : '' }}"
                                 wire:click="$set('visibilidad', 'ambas')">
                                🔁 <strong>Ambas</strong>
                                <div style="font-size:.73rem;font-weight:400;margin-top:.15rem;">
                                    Disponible en todos los contextos
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── 8. Prioridad + Activo ── --}}
                    <div class="lib-row">
                        <div class="lib-field">
                            <label class="fw-semibold">Prioridad</label>
                            <input wire:model="prioridad" type="number" min="1" max="999" class="form-control"
                                   placeholder="50">
                            <span class="lib-hint">
                                <strong>1 = máxima prioridad.</strong>
                                El contexto se limita a 15 entradas. Las de número menor llegan primero al agente.
                                Usa prioridad 1–10 para lo más crítico (políticas, precios actuales) y 50+ para información secundaria.
                            </span>
                        </div>
                        <div class="lib-field" style="justify-content:center;">
                            <label class="fw-semibold">Estado</label>
                            <label class="d-flex align-items-center gap-2 mt-1" style="cursor:pointer;user-select:none;">
                                <input wire:model="activo" type="checkbox" class="form-check-input"
                                       style="width:20px;height:20px;accent-color:#6366f1;">
                                <span class="fw-medium">Activo</span>
                            </label>
                            <span class="lib-hint">
                                Desactivar = el agente deja de recibirla de inmediato,
                                sin borrarla. Útil para pausar entradas temporalmente.
                            </span>
                        </div>
                    </div>

                    {{-- ── 9. Fechas para tipos no-campaña ── --}}
                    @if($tipo !== 'campaña')
                        <div class="lib-field">
                            <label class="fw-semibold">Rango de vigencia <span class="text-muted fw-normal">(opcional)</span></label>
                            <span class="lib-hint mb-1">
                                Si defines fechas, el agente solo usará esta entrada en ese período.
                                Deja vacío para que sea permanente.
                            </span>
                            <div class="lib-row">
                                <div class="lib-field">
                                    <label style="font-weight:400;">Desde</label>
                                    <input wire:model="inicio_at" type="date" class="form-control">
                                </div>
                                <div class="lib-field">
                                    <label style="font-weight:400;">Hasta</label>
                                    <input wire:model="fin_at" type="date" class="form-control">
                                </div>
                            </div>
                        </div>
                    @endif

                </div>{{-- /modal-body --}}

                {{-- Footer --}}
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            x-on:click="$wire.set('showModal', false)">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="save">
                        <i class="fas fa-save me-1"></i>
                        {{ $editingId ? 'Guardar cambios' : 'Crear entrada' }}
                    </button>
                </div>

            </div>{{-- /modal-content --}}
        </div>{{-- /modal-dialog --}}
    </div>{{-- /modal-overlay --}}

</div>
