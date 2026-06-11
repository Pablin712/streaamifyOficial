<div>
<style>
    /* ── Stats ── */
    .tb-stats  { display:flex; gap:.75rem; flex-wrap:wrap; margin-bottom:1.25rem; }
    .tb-stat   {
        flex:1; min-width:110px; background:var(--bg-card,#fff); border:2px solid #e5e7eb;
        border-radius:.65rem; padding:.7rem .9rem; text-align:center;
    }
    .tb-stat-num { font-size:1.6rem; font-weight:700; line-height:1; }
    .tb-stat-lbl { font-size:.73rem; color:#6b7280; margin-top:.15rem; }
    .tb-stat.pool { border-color:#6366f1; } .tb-stat.pool .tb-stat-num { color:#6366f1; }
    .tb-stat.mias { border-color:#22c55e; } .tb-stat.mias .tb-stat-num { color:#15803d; }
    .tb-stat.done { border-color:#f59e0b; } .tb-stat.done .tb-stat-num { color:#d97706; }

    /* ── Tabs ── */
    .tb-tabs { display:flex; gap:.35rem; flex-wrap:wrap; }
    .tb-tab  {
        padding:.42rem .9rem; border-radius:.45rem; font-size:.83rem; font-weight:600;
        border:2px solid #e5e7eb; background:var(--bg-card,#fff); cursor:pointer;
        transition:all .12s; color:#374151;
    }
    .tb-tab.active                  { background:#6366f1; color:#fff; border-color:#6366f1; }
    .tb-tab:hover:not(.active)      { border-color:#a5b4fc; background:#f5f3ff; }

    /* ── Toolbar ── */
    .tb-toolbar  { display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; margin-bottom:1rem; }
    .tb-toolbar-r{ display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; margin-left:auto; }

    /* ── Tarjetas ── */
    .task-card {
        background:var(--bg-card,#fff); border:2px solid #e5e7eb; border-radius:.6rem;
        padding:.75rem .9rem; margin-bottom:.5rem; display:flex; gap:.7rem; align-items:flex-start;
        transition:box-shadow .12s;
    }
    .task-card:hover { box-shadow:0 3px 10px rgba(0,0,0,.07); }
    .task-card.alta  { border-left:4px solid #ef4444; }
    .task-card.media { border-left:4px solid #f59e0b; }
    .task-card.baja  { border-left:4px solid #94a3b8; }
    .task-icon  { font-size:1.25rem; flex-shrink:0; line-height:1; padding-top:.1rem; }
    .task-body  { flex:1; min-width:0; }
    .task-title { font-weight:600; font-size:.87rem; color:var(--text-primary,#111827); word-break:break-word; }
    .task-desc  { font-size:.77rem; color:#6b7280; margin-top:.2rem; line-height:1.4; }
    .task-meta  { display:flex; gap:.4rem; flex-wrap:wrap; margin-top:.3rem; align-items:center; }
    .task-pill  { font-size:.68rem; font-weight:600; padding:.1rem .42rem; border-radius:999px; }
    .pill-alta  { background:#fee2e2; color:#991b1b; }
    .pill-media { background:#fef3c7; color:#92400e; }
    .pill-baja  { background:#f3f4f6; color:#6b7280; }
    .pill-tipo  { background:#ede9fe; color:#5b21b6; }
    .pill-asig  { background:#dcfce7; color:#166534; }
    .pill-venc  { color:#9ca3af; }
    .task-actions { display:flex; flex-direction:column; gap:.28rem; align-items:flex-end; flex-shrink:0; }
    .tb-btn {
        display:inline-flex; align-items:center; gap:.28rem;
        padding:.28rem .6rem; border-radius:.35rem; font-size:.76rem; font-weight:600;
        border:none; cursor:pointer; transition:background .1s; white-space:nowrap;
    }
    .tb-btn-tomar   { background:#6366f1; color:#fff; }
    .tb-btn-tomar:hover   { background:#4f46e5; }
    .tb-btn-done    { background:#22c55e; color:#fff; }
    .tb-btn-done:hover    { background:#16a34a; }
    .tb-btn-liberar { background:#e5e7eb; color:#374151; }
    .tb-btn-liberar:hover { background:#d1d5db; }
    .tb-btn-del     { background:#fee2e2; color:#b91c1c; }
    .tb-btn-del:hover     { background:#fca5a5; }
    .tb-assign-sel {
        font-size:.73rem; border:1px solid #d1d5db; border-radius:.3rem;
        padding:.22rem .35rem; background:var(--bg-card,#fff); cursor:pointer; max-width:145px;
    }

    /* ── Paginación ── */
    .tb-pagination .pagination { gap:.2rem; flex-wrap:wrap; margin:0; }
    .tb-pagination .pagination .page-link {
        border-radius:.4rem !important; border:2px solid #e5e7eb; color:#4b5563;
        font-size:.79rem; padding:.28rem .6rem; font-weight:600; line-height:1.4;
        background:var(--bg-card,#fff); transition:all .1s;
    }
    .tb-pagination .pagination .page-item.active .page-link {
        background:#6366f1; border-color:#6366f1; color:#fff;
    }
    .tb-pagination .pagination .page-link:hover:not(:disabled) {
        background:#f5f3ff; border-color:#a5b4fc; color:#4338ca;
    }
    .tb-pagination .pagination .page-item.disabled .page-link { opacity:.38; pointer-events:none; }

    /* ── Modal masivo — grid de 4 columnas fijas ── */
    .masivo-row {
        display:grid;
        grid-template-columns: 1.6rem 1fr 80px 62px;
        align-items:center; gap:.5rem;
        padding:.5rem .65rem;
        border:2px solid #e5e7eb; border-radius:.5rem; margin-bottom:.35rem;
        background:var(--bg-card,#fff); transition:border-color .1s;
    }
    .masivo-row:hover:not(.masivo-disabled) { border-color:#c7d2fe; }
    .masivo-row.masivo-disabled { opacity:.45; }
    .masivo-icon   { font-size:1.15rem; line-height:1; }
    .masivo-label  { font-weight:600; font-size:.83rem; line-height:1.2; }
    .masivo-hint   { font-size:.7rem; color:#6b7280; margin-top:.1rem; }
    .masivo-badge  {
        font-size:.69rem; font-weight:700; padding:.15rem .4rem; border-radius:999px;
        background:#ede9fe; color:#5b21b6; text-align:center; white-space:nowrap;
        overflow:hidden; text-overflow:ellipsis;
    }
    .masivo-input  {
        width:62px; text-align:center; border:2px solid #d1d5db; border-radius:.4rem;
        padding:.22rem .3rem; font-size:.85rem; font-weight:700;
        -moz-appearance:textfield;
    }
    .masivo-input::-webkit-inner-spin-button,
    .masivo-input::-webkit-outer-spin-button { -webkit-appearance:none; }
    .masivo-input:focus { border-color:#6366f1; outline:none; background:#fafafe; }
    .masivo-footer {
        display:flex; justify-content:space-between; align-items:center;
        margin-top:.7rem; padding-top:.6rem; border-top:1px solid #e5e7eb;
    }
    .masivo-total-num { font-size:1.3rem; font-weight:700; color:#4338ca; }

    /* ── Contexto de tarea ── */
    .task-context { display:flex; gap:.3rem; flex-wrap:wrap; margin-top:.28rem; }
    .pill-ctx-cli  { background:#dbeafe; color:#1e40af; }
    .pill-ctx-cta  { background:#f3f4f6; color:#374151; }
    .pill-ctx-tipo { background:#fef3c7; color:#92400e; }
    .tb-btn-chat   { background:#25d366; color:#fff; }
    .tb-btn-chat:hover   { background:#128c7e; }
    .tb-btn-soporte      { background:#6366f1; color:#fff; }
    .tb-btn-soporte:hover{ background:#4f46e5; }

    /* ── Vacío ── */
    .tb-empty { text-align:center; padding:2.5rem 1rem; color:#9ca3af; font-size:.88rem; }

    /* ── Form manual ── */
    .tb-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:.65rem; }
    @media(max-width:500px){ .tb-form-grid { grid-template-columns:1fr; } }

    /* ── WhatsApp ── */
    .wsp-bar { display:flex; gap:.4rem; flex-wrap:wrap; align-items:center;
               background:#f0fdf4; border:1px solid #bbf7d0; border-radius:.5rem;
               padding:.45rem .7rem; margin-bottom:.75rem; }
    .tb-btn-wsp       { background:#25d366; color:#fff; }
    .tb-btn-wsp:hover { background:#128c7e; }
    .tb-btn-wsp-sel       { background:#dcfce7; color:#166534; border:1.5px solid #86efac; }
    .tb-btn-wsp-sel:hover { background:#bbf7d0; }
    .wsp-template-editor { width:100%; padding:.55rem .7rem; background:#fff;
                           border:1.5px solid #86efac; border-radius:.4rem; margin-top:.35rem; }
    .wsp-template-editor textarea { width:100%; border:1px solid #d1d5db; border-radius:.35rem;
                                    padding:.4rem .55rem; font-size:.82rem; resize:vertical; min-height:72px; }
    .wsp-vars { font-size:.72rem; color:#6b7280; margin:.2rem 0 .4rem; }
    .task-select-cb { width:1.1rem; height:1.1rem; cursor:pointer; flex-shrink:0; accent-color:#25d366; }
</style>

    {{-- Stats --}}
    <div class="tb-stats">
        <div class="tb-stat pool">
            <div class="tb-stat-num">{{ $totalPool }}</div>
            <div class="tb-stat-lbl">Disponibles</div>
        </div>
        <div class="tb-stat mias">
            <div class="tb-stat-num">{{ $totalMias }}</div>
            <div class="tb-stat-lbl">Mis tareas</div>
        </div>
        <div class="tb-stat done">
            <div class="tb-stat-num">{{ $completadasHoy }}</div>
            <div class="tb-stat-lbl">Completadas hoy</div>
        </div>
    </div>

    {{-- Toolbar principal --}}
    <div class="tb-toolbar">
        {{-- Tabs --}}
        <div class="tb-tabs">
            <button class="tb-tab {{ $tab==='pool'       ? 'active' : '' }}" wire:click="setTab('pool')"
                    wire:loading.class.add="opacity-50" wire:target="setTab">
                🗂 Pool ({{ $totalPool }})
            </button>
            <button class="tb-tab {{ $tab==='mis_tareas' ? 'active' : '' }}" wire:click="setTab('mis_tareas')"
                    wire:loading.class.add="opacity-50" wire:target="setTab">
                ✅ Mis tareas ({{ $totalMias }})
            </button>
            @if($isAdmin)
            <button class="tb-tab {{ $tab==='asignadas'  ? 'active' : '' }}" wire:click="setTab('asignadas')"
                    wire:loading.class.add="opacity-50" wire:target="setTab"
                    style="{{ $tab==='asignadas' ? '' : 'border-color:#f97316;color:#ea580c;' }}">
                👥 Asignadas ({{ $totalAsignadas }})
            </button>
            @endif
            <button class="tb-tab {{ $tab==='completadas'? 'active' : '' }}" wire:click="setTab('completadas')"
                    wire:loading.class.add="opacity-50" wire:target="setTab">
                📋 Historial
            </button>
        </div>

        {{-- Acciones derechas --}}
        <div class="tb-toolbar-r">
            @if($isAdmin && $tab === 'asignadas')
                <select wire:model.live="filtroEmpleadoAsignadas" class="form-select form-select-sm" style="max-width:170px;">
                    <option value="">Todos los empleados</option>
                    @foreach($empleados as $emp)
                        <option value="{{ $emp->idemp }}">{{ $emp->nombreemp }}</option>
                    @endforeach
                </select>
                @if($totalAsignadas > 0)
                    @php $liberarMsg = "¿Devolver {$totalAsignadas} " . ($totalAsignadas !== 1 ? 'tareas' : 'tarea') . " al pool? Esta acción no se puede deshacer."; @endphp
                    <button class="btn btn-outline-danger btn-sm"
                            wire:loading.attr="disabled"
                            wire:target="liberarTodas"
                            x-on:click="if(confirm('{{ $liberarMsg }}')) $wire.liberarTodas()">
                        <span wire:loading wire:target="liberarTodas" class="spinner-border spinner-border-sm me-1"></span>
                        <i wire:loading.remove wire:target="liberarTodas" class="fas fa-undo me-1"></i>
                        Devolver todas ({{ $totalAsignadas }})
                    </button>
                @endif
            @endif
            <select wire:model.live="filtroTipo" class="form-select form-select-sm" style="max-width:185px;">
                <option value="">Todos los tipos</option>
                @foreach($tipos as $k => $meta)
                    <option value="{{ $k }}">{{ $meta['icon'] }} {{ $meta['label'] }}</option>
                @endforeach
            </select>

            {{-- Tomar masivo — solo Alpine, sin wire:click --}}
            <button class="btn btn-primary btn-sm"
                    x-on:click="window.dispatchEvent(new CustomEvent('open-modal',{detail:'tareasMasivoModal'}))">
                <i class="fas fa-layer-group me-1"></i>
                {{ $isAdmin ? 'Repartir tareas' : 'Tomar en bloque' }}
            </button>

            {{-- Tarea manual --}}
            <button type="button" class="btn btn-outline-secondary btn-sm"
                    x-on:click="window.dispatchEvent(new CustomEvent('open-modal',{detail:'tareaManualModal'}))">
                <i class="fas fa-plus me-1"></i> Manual
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════ TAB POOL ══════════════════════════════ --}}
    @if($tab === 'pool')
        @if($totalPool === 0)
            @if($tareasDeOtros->isNotEmpty())
                <div class="tb-empty pb-2">
                    <i class="fas fa-handshake fa-2x mb-2 d-block" style="color:#6366f1;"></i>
                    El pool está vacío. ¿Ayudas a un compañero?
                </div>
                {{-- ── Tareas asignadas a otros que el empleado puede tomar ── --}}
                <div style="font-size:.73rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #e5e7eb;padding-bottom:.3rem;margin-bottom:.5rem;">
                    🤝 Tareas de compañeros
                </div>
                @foreach($tareasDeOtros as $t)
                    <div class="task-card {{ $t->prioridad }}" wire:key="ayuda-{{ $t->id }}">
                        <div class="task-icon">{{ $t->tipoIcon() }}</div>
                        <div class="task-body">
                            <div class="task-title">{{ $t->nombretarea }}</div>
                            @if($t->descripcion)
                                <div class="task-desc">{{ $t->descripcion }}</div>
                            @endif
                            <div class="task-meta">
                                <span class="task-pill pill-{{ $t->prioridad }}">{{ ucfirst($t->prioridad) }}</span>
                                <span class="task-pill pill-tipo">{{ $t->tipoLabel() }}</span>
                                <span class="task-pill pill-asig">
                                    👤 {{ optional($t->assignee)->nombreemp ?? "Empleado #{$t->assignee_id}" }}
                                </span>
                                @if($t->fechalimit)
                                    @php $venc = $t->fechalimit->isPast(); @endphp
                                    <span class="task-pill {{ $venc ? 'pill-alta' : 'pill-venc' }}">
                                        📅 {{ $t->fechalimit->format('d/m') }}{{ $venc ? ' ⚠️' : '' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="task-actions" x-data>
                            <button class="tb-btn" style="background:#6366f1;color:#fff;"
                                    wire:loading.attr="disabled" wire:target="ayudar"
                                    x-on:click="$wire.ayudar({{ $t->id }})"
                                    title="Tomar esta tarea para ayudar">
                                🤝 Ayudar
                            </button>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="tb-empty">
                    <i class="fas fa-check-double fa-2x mb-2 d-block"></i>
                    No hay tareas disponibles en el pool. ¡Todo al día!
                </div>
            @endif
        @else
            @foreach($pool as $t)
                @php $ctx = $contextoPorTarea[$t->id] ?? null; @endphp
                <div class="task-card {{ $t->prioridad }}" wire:key="pool-{{ $t->id }}">
                    <div class="task-icon">{{ $t->tipoIcon() }}</div>
                    <div class="task-body">
                        <div class="task-title">{{ $t->nombretarea }}</div>
                        @if($t->descripcion)
                            <div class="task-desc">{{ $t->descripcion }}</div>
                        @endif
                        <div class="task-meta">
                            <span class="task-pill pill-{{ $t->prioridad }}">{{ ucfirst($t->prioridad) }}</span>
                            <span class="task-pill pill-tipo">{{ $t->tipoLabel() }}</span>
                            @if($t->fechalimit)
                                @php $venc = $t->fechalimit->isPast(); @endphp
                                <span class="task-pill {{ $venc ? 'pill-alta' : 'pill-venc' }}">
                                    📅 {{ $t->fechalimit->format('d/m') }}{{ $venc ? ' ⚠️' : '' }}
                                </span>
                            @endif
                        </div>
                        @if($ctx)
                        <div class="task-context">
                            @if(!empty($ctx['cliente']))
                                <span class="task-pill pill-ctx-cli">👤 {{ $ctx['cliente'] }}</span>
                            @endif
                            @if(!empty($ctx['idcue']))
                                <span class="task-pill pill-ctx-cta">🏠 Cta #{{ $ctx['idcue'] }}</span>
                            @endif
                            @if(!empty($ctx['tipo']))
                                <span class="task-pill pill-ctx-tipo">🔧 {{ ucfirst($ctx['tipo']) }}</span>
                            @endif
                        </div>
                        @endif
                    </div>
                    <div class="task-actions" x-data>
                        <button class="tb-btn tb-btn-tomar"
                                wire:loading.attr="disabled" wire:target="tomar"
                                x-on:click="$wire.tomar({{ $t->id }})">
                            <i class="fas fa-hand-pointer fa-xs"></i> Tomar
                        </button>
                        @if($isAdmin)
                            <select class="tb-assign-sel"
                                    x-on:change="const v=+$event.target.value; if(v>0){$wire.asignar({{ $t->id }},v);$event.target.value='';}">
                                <option value="">Asignar a…</option>
                                @foreach($empleados as $emp)
                                    <option value="{{ $emp->idemp }}">{{ $emp->nombreemp }}</option>
                                @endforeach
                            </select>
                        @endif
                        @if(Auth::user()->hasPermissionTo('tareas.destroy'))
                            <button class="tb-btn tb-btn-del"
                                    x-on:click="if(confirm('¿Eliminar esta tarea del pool?')) $wire.eliminar({{ $t->id }})">
                                <i class="fas fa-trash fa-xs"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
            <div class="tb-pagination mt-3">{{ $pool->links() }}</div>
        @endif
    @endif

    {{-- ══════════════════════════════ TAB MIS TAREAS ══════════════════════════════ --}}
    @if($tab === 'mis_tareas')
        @php $cobrarCount = $misTareas->where('tipo_tarea','cobrar_usuario')->count(); @endphp

        {{-- Barra WhatsApp (solo si hay tareas de cobro y canal activo) --}}
        @if($cobrarCount > 0 && $tieneCanalWsp)
        @php
            $allWspData  = array_values($datosWspPorTarea);
            $allWspJson  = json_encode($allWspData, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]';
            $wspConTel   = collect($allWspData)->filter(fn($r) => !empty($r['telefono']))->count();
        @endphp
        <div class="wsp-bar">
            <i class="fab fa-whatsapp text-success"></i>
            <span class="fw-semibold" style="font-size:.82rem;color:#166534;">
                WhatsApp cobros ({{ $wspConTel }}/{{ $cobrarCount }} con teléfono)
            </span>

            {{-- Enviar a todos --}}
            <button class="tb-btn tb-btn-wsp"
                    data-tareas="{{ htmlspecialchars($allWspJson, ENT_QUOTES) }}"
                    onclick="wspEnviarTodos(this)"
                    title="Enviar mensaje a {{ $wspConTel }} clientes con teléfono registrado">
                <i class="fab fa-whatsapp fa-xs"></i> Enviar a todos ({{ $wspConTel }})
            </button>

            {{-- Seleccionar todos --}}
            <button id="wsp-btn-all-sel" class="tb-btn tb-btn-wsp-sel" onclick="wspSelectAll()"
                    title="Seleccionar / deseleccionar todos">
                <i class="fas fa-check-square fa-xs"></i>
                <span id="wsp-all-sel-label">Seleccionar todos</span>
            </button>

            {{-- Enviar seleccionados --}}
            <button id="wsp-btn-sel" class="tb-btn tb-btn-wsp-sel" style="display:none"
                    onclick="wspEnviarSeleccionados()" title="Enviar a los seleccionados">
                <i class="fab fa-whatsapp fa-xs"></i>
                Enviar (<span id="wsp-sel-count">0</span>)
            </button>

            {{-- Editar plantilla --}}
            <button class="tb-btn tb-btn-liberar ms-auto" wire:click="$toggle('showPlantillaEditor')"
                    title="Configurar plantilla de mensaje">
                <i class="fas fa-pen fa-xs"></i> Plantilla
            </button>

            {{-- Editor de plantilla (inline) --}}
            @if($showPlantillaEditor)
            <div class="wsp-template-editor">
                <textarea wire:model="plantillaCobro"
                          placeholder="{{ \App\Livewire\TareasBoard::PLANTILLA_DEFAULT }}"></textarea>
                <div class="wsp-vars">
                    Variables disponibles: <code>{nombre}</code> = nombre del cliente,
                    <code>{fecha}</code> = fecha de vencimiento.
                    Si dejas vacío se usa la plantilla por defecto.
                </div>
                <div class="d-flex gap-2">
                    <button class="tb-btn tb-btn-done" wire:click="guardarPlantilla"
                            wire:loading.attr="disabled" wire:target="guardarPlantilla">
                        <i class="fas fa-save fa-xs"></i> Guardar
                    </button>
                    <button class="tb-btn tb-btn-liberar" wire:click="$toggle('showPlantillaEditor')">
                        Cancelar
                    </button>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- Tarjetas --}}
        @if($misTareas->isEmpty())
            <div class="tb-empty">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                No tienes tareas asignadas.<br>
                Usa <strong>"Tomar en bloque"</strong> para elegir cuántas tomar de cada tipo.
            </div>
        @else
            @foreach($misTareas as $t)
                @php
                    $wsp = ($tieneCanalWsp && $t->tipo_tarea === 'cobrar_usuario' && isset($datosWspPorTarea[$t->id]))
                        ? $datosWspPorTarea[$t->id] : null;
                    $ctx = $contextoPorTarea[$t->id] ?? null;
                @endphp
                <div class="task-card {{ $t->prioridad }}" wire:key="mis-{{ $t->id }}">
                    @if($wsp)
                        <input type="checkbox" class="task-select-cb"
                               data-tid="{{ $t->id }}"
                               data-nombre="{{ $wsp['nombre'] }}"
                               data-tel="{{ $wsp['telefono'] }}"
                               data-idcli="{{ $wsp['idcli'] }}"
                               data-msg="{{ htmlspecialchars($wsp['mensaje'], ENT_QUOTES) }}"
                               onchange="wspToggle(this)">
                    @endif
                    <div class="task-icon">{{ $t->tipoIcon() }}</div>
                    <div class="task-body">
                        <div class="task-title">{{ $t->nombretarea }}</div>
                        @if($t->descripcion)
                            <div class="task-desc">{{ $t->descripcion }}</div>
                        @endif
                        <div class="task-meta">
                            <span class="task-pill pill-{{ $t->prioridad }}">{{ ucfirst($t->prioridad) }}</span>
                            <span class="task-pill pill-tipo">{{ $t->tipoLabel() }}</span>
                            @if($t->asignado_por && $t->asignado_por !== Auth::user()->idemp)
                                <span class="task-pill pill-asig">
                                    Asignada por {{ optional($t->asignadoPorEmp)->nombreemp ?? 'admin' }}
                                </span>
                            @endif
                            @if($t->assigned_at)
                                <span class="task-pill pill-venc">{{ $t->assigned_at->diffForHumans() }}</span>
                            @endif
                        </div>
                        @if($ctx)
                        <div class="task-context">
                            @if(!empty($ctx['cliente']))
                                <span class="task-pill pill-ctx-cli">👤 {{ $ctx['cliente'] }}</span>
                            @endif
                            @if(!empty($ctx['idcue']))
                                <span class="task-pill pill-ctx-cta">🏠 Cta #{{ $ctx['idcue'] }}</span>
                            @endif
                            @if(!empty($ctx['tipo']))
                                <span class="task-pill pill-ctx-tipo">🔧 {{ ucfirst($ctx['tipo']) }}</span>
                            @endif
                        </div>
                        @endif
                    </div>
                    <div class="task-actions" x-data>
                        @if($t->tipo_tarea === 'soporte_pendiente' && $ctx)
                            <a href="{{ route('chat.whatsapp') }}?idcli={{ $ctx['idcli'] }}"
                               class="tb-btn tb-btn-chat" target="_blank"
                               title="Abrir chat de {{ $ctx['cliente'] }}">
                                <i class="fab fa-whatsapp fa-xs"></i> Chat
                            </a>
                            <a href="{{ route('soportes.index') }}?open={{ $ctx['idsop'] }}"
                               class="tb-btn tb-btn-soporte" target="_blank"
                               title="Ver soporte #{{ $ctx['idsop'] }}">
                                <i class="fas fa-life-ring fa-xs"></i> Soporte
                            </a>
                        @endif
                        @if($wsp)
                            <button class="tb-btn tb-btn-wsp"
                                    data-tid="{{ $t->id }}"
                                    data-nombre="{{ $wsp['nombre'] }}"
                                    data-tel="{{ $wsp['telefono'] }}"
                                    data-idcli="{{ $wsp['idcli'] }}"
                                    data-msg="{{ htmlspecialchars($wsp['mensaje'], ENT_QUOTES) }}"
                                    onclick="wspAbrirDesdeBtn(this)"
                                    title="Enviar WhatsApp a este cliente">
                                <i class="fab fa-whatsapp fa-xs"></i> WA
                            </button>
                        @endif
                        <button class="tb-btn tb-btn-done"
                                wire:loading.attr="disabled" wire:target="completar"
                                x-on:click="$wire.completar({{ $t->id }})">
                            <i class="fas fa-check fa-xs"></i> Completar
                        </button>
                        <button class="tb-btn tb-btn-liberar"
                                x-on:click="if(confirm('¿Devolver esta tarea al pool?')) $wire.liberar({{ $t->id }})">
                            <i class="fas fa-undo fa-xs"></i> Devolver
                        </button>
                        @if(Auth::user()->hasPermissionTo('tareas.destroy'))
                            <button class="tb-btn tb-btn-del"
                                    x-on:click="if(confirm('¿Eliminar esta tarea?')) $wire.eliminar({{ $t->id }})">
                                <i class="fas fa-trash fa-xs"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    @endif

    {{-- ══════════════════════════════ TAB COMPLETADAS ══════════════════════════════ --}}
    @if($tab === 'completadas')
        @if($completadas->isEmpty())
            <div class="tb-empty">
                <i class="fas fa-star fa-2x mb-2 d-block"></i>
                No has completado tareas todavía.
            </div>
        @else
            @foreach($completadas as $t)
                <div class="task-card baja" wire:key="comp-{{ $t->id }}" style="opacity:.7;">
                    <div class="task-icon">✅</div>
                    <div class="task-body">
                        <div class="task-title" style="text-decoration:line-through;color:#6b7280;">
                            {{ $t->nombretarea }}
                        </div>
                        <div class="task-meta">
                            <span class="task-pill pill-tipo">{{ $t->tipoLabel() }}</span>
                            @if($t->fecha_completada)
                                <span class="task-pill pill-venc">{{ $t->fecha_completada->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="tb-pagination mt-3">{{ $completadas->links() }}</div>
        @endif
    @endif

    {{-- ═══════════════════════════ TAB ASIGNADAS (admin) ═══════════════════════════ --}}
    @if($isAdmin && $tab === 'asignadas')
        @if($todasAsignadas->isEmpty())
            <div class="tb-empty">
                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                No hay tareas asignadas
                {{ $filtroEmpleadoAsignadas ? 'para este empleado' : 'en este momento' }}.
            </div>
        @else
            @php $empActual = null; @endphp
            @foreach($todasAsignadas as $t)
                {{-- Separador por empleado --}}
                @if($t->assignee_id !== $empActual)
                    @php $empActual = $t->assignee_id; @endphp
                    <div style="font-size:.73rem;font-weight:700;color:#6b7280;text-transform:uppercase;
                                letter-spacing:.06em;padding:.45rem .1rem .2rem;margin-top:.4rem;
                                border-bottom:1px solid #e5e7eb;">
                        👤 {{ optional($t->assignee)->nombreemp ?? "Empleado #{$t->assignee_id}" }}
                    </div>
                @endif

                <div class="task-card {{ $t->prioridad }}" wire:key="asig-{{ $t->id }}">
                    <div class="task-icon">{{ $t->tipoIcon() }}</div>
                    <div class="task-body">
                        <div class="task-title">{{ $t->nombretarea }}</div>
                        @if($t->descripcion)
                            <div class="task-desc">{{ $t->descripcion }}</div>
                        @endif
                        <div class="task-meta">
                            <span class="task-pill pill-{{ $t->prioridad }}">{{ ucfirst($t->prioridad) }}</span>
                            <span class="task-pill pill-tipo">{{ $t->tipoLabel() }}</span>
                            @if($t->assigned_at)
                                <span class="task-pill pill-venc">{{ $t->assigned_at->diffForHumans() }}</span>
                            @endif
                            @if($t->fechalimit)
                                @php $venc = $t->fechalimit->isPast(); @endphp
                                <span class="task-pill {{ $venc ? 'pill-alta' : 'pill-venc' }}">
                                    📅 {{ $t->fechalimit->format('d/m') }}{{ $venc ? ' ⚠️' : '' }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="task-actions" x-data>
                        {{-- Reasignar a otro empleado --}}
                        <select class="tb-assign-sel"
                                x-on:change="const v=+$event.target.value; if(v>0){$wire.reasignar({{ $t->id }},v);$event.target.value='0';}"
                                style="border-color:#f97316;color:#ea580c;">
                            <option value="0">↔ Reasignar…</option>
                            @foreach($empleados as $emp)
                                @if($emp->idemp !== $t->assignee_id)
                                    <option value="{{ $emp->idemp }}">{{ $emp->nombreemp }}</option>
                                @endif
                            @endforeach
                        </select>
                        {{-- Liberar al pool --}}
                        <button class="tb-btn tb-btn-liberar"
                                x-on:click="if(confirm('¿Devolver esta tarea al pool?')) $wire.liberar({{ $t->id }})">
                            <i class="fas fa-undo fa-xs"></i> Pool
                        </button>
                        @if(Auth::user()->hasPermissionTo('tareas.destroy'))
                            <button class="tb-btn tb-btn-del"
                                    x-on:click="if(confirm('¿Eliminar esta tarea?')) $wire.eliminar({{ $t->id }})">
                                <i class="fas fa-trash fa-xs"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
            <div class="tb-pagination mt-3">{{ $todasAsignadas->links() }}</div>
        @endif
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════
         MODAL: Tomar / Repartir en bloque
    ══════════════════════════════════════════════════════════════════════ --}}
    <x-modal name="tareasMasivoModal" :show="false" maxWidth="lg">
        <div x-data="{
                vals: { cobrar_usuario: 0, quitar_usuario: 0, renovar_cuenta: 0, cuenta_caida: 0, colapso_cuenta: 0, soporte_pendiente: 0 },
                selectedAssignee: 0,
                get total() { return Object.values(this.vals).reduce((s,v) => s + (parseInt(v)||0), 0); },
                get tiposActivos() { return Object.values(this.vals).filter(v => (parseInt(v)||0) > 0).length; }
             }"
             x-on:close-modal.window="if ($event.detail === 'tareasMasivoModal') { Object.keys(vals).forEach(k => vals[k] = 0); selectedAssignee = 0; }">

        <div class="modal-header">
            <h5 class="modal-title">
                <i class="fas fa-layer-group me-2"></i>
                {{ $isAdmin ? 'Repartir tareas en bloque' : 'Tomar tareas en bloque' }}
            </h5>
            <button type="button" class="btn-close"
                    onclick="window.dispatchEvent(new CustomEvent('close-modal',{detail:'tareasMasivoModal'}))">
            </button>
        </div>

        <div class="modal-body">
            <p style="font-size:.81rem;color:#6b7280;margin-bottom:.9rem;">
                Elige cuántas tareas tomar de cada tipo. Se asignan
                <strong>agrupadas</strong>: usuarios de la misma cuenta juntos,
                cuentas del mismo proveedor juntas, cobros por fecha.
            </p>

            {{-- Admin: selector de empleado destino (manejado solo en Alpine para evitar race conditions) --}}
            @if($isAdmin)
                <div class="mb-3 px-1 py-2" style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:.5rem;">
                    <label class="form-label fw-semibold mb-1" style="font-size:.8rem;color:#4338ca;">
                        <i class="fas fa-user-check me-1"></i> Asignar a:
                    </label>
                    <select x-model.number="selectedAssignee" class="form-select form-select-sm">
                        <option value="0">👤 Yo mismo ({{ Auth::user()->nombreemp ?? Auth::user()->name }})</option>
                        @foreach($empleados as $emp)
                            @if($emp->idemp !== Auth::user()->idemp)
                                <option value="{{ $emp->idemp }}">{{ $emp->nombreemp }}</option>
                            @endif
                        @endforeach
                    </select>
                    <div x-show="selectedAssignee > 0"
                         style="font-size:.74rem;color:#4338ca;margin-top:.35rem;padding-left:.1rem;">
                        <i class="fas fa-info-circle me-1"></i>
                        Las tareas se asignarán a
                        <strong x-text="$el.closest('[x-data]').querySelector('select option:checked').textContent.trim()"></strong>
                    </div>
                </div>
            @endif

            {{-- Cabecera de columnas --}}
            <div style="display:grid;grid-template-columns:1.6rem 1fr 72px 62px;gap:.5rem;
                        padding:.15rem .65rem .3rem;border-bottom:1px solid #f3f4f6;margin-bottom:.3rem;">
                <div></div>
                <div style="font-size:.69rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Tipo</div>
                <div style="font-size:.69rem;font-weight:700;color:#9ca3af;text-align:center;">Dispon.</div>
                <div style="font-size:.69rem;font-weight:700;color:#9ca3af;text-align:center;">Tomar</div>
            </div>

            {{-- Filas --}}
            @php
                $tiposHint = [
                    'cobrar_usuario'    => 'Por fecha de vencimiento',
                    'quitar_usuario'    => 'Agrupados por cuenta',
                    'renovar_cuenta'    => 'Por proveedor / servicio',
                    'cuenta_caida'      => 'Por proveedor / servicio',
                    'colapso_cuenta'    => 'Por proveedor / servicio',
                    'soporte_pendiente' => 'Por cuenta y servicio',
                    'agregar_stock'     => 'Servicios con stock bajo',
                ];
            @endphp

            @foreach($tipos as $k => $meta)
                @if($k === 'manual') @continue @endif
                @php $disp = $disponiblesPorTipo[$k] ?? 0; @endphp
                <div class="masivo-row {{ $disp === 0 ? 'masivo-disabled' : '' }}">
                    <span class="masivo-icon">{{ $meta['icon'] }}</span>
                    <div>
                        <div class="masivo-label">{{ $meta['label'] }}</div>
                        <div class="masivo-hint">{{ $tiposHint[$k] ?? '' }}</div>
                    </div>
                    <span class="masivo-badge">{{ $disp }}</span>
                    <input type="number"
                           class="masivo-input"
                           x-model.number="vals.{{ $k }}"
                           min="0"
                           max="{{ $disp }}"
                           {{ $disp === 0 ? 'disabled' : '' }}>
                </div>
            @endforeach

            {{-- Totales reactivos via Alpine --}}
            <div class="masivo-footer">
                <span style="font-size:.79rem;color:#6b7280;"
                      x-text="`${tiposActivos} tipo${tiposActivos===1?'':'s'} seleccionado${tiposActivos===1?'':'s'}`">
                    0 tipos seleccionados
                </span>
                <div style="display:flex;align-items:baseline;gap:.3rem;">
                    <span style="font-size:.79rem;color:#6b7280;">Total:</span>
                    <span class="masivo-total-num" x-text="total">0</span>
                    <span style="font-size:.79rem;color:#6b7280;">tareas</span>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-secondary btn-sm"
                    onclick="window.dispatchEvent(new CustomEvent('close-modal',{detail:'tareasMasivoModal'}))">
                Cancelar
            </button>
            <button class="btn btn-primary btn-sm"
                    x-on:click="
                        const wireEl = $el.closest('[wire\\:id]');
                        if (!wireEl) return;
                        const comp = window.Livewire.find(wireEl.getAttribute('wire:id'));
                        if (!comp) return;
                        comp.tomarMasivo(Object.assign({}, vals), parseInt(selectedAssignee) || 0)
                            .then(() => { Object.keys(vals).forEach(k => vals[k] = 0); selectedAssignee = 0; });
                    "
                    wire:loading.attr="disabled"
                    wire:target="tomarMasivo"
                    :disabled="total === 0">
                <span wire:loading wire:target="tomarMasivo"
                      class="spinner-border spinner-border-sm me-1"></span>
                <i wire:loading.remove wire:target="tomarMasivo"
                   class="fas fa-layer-group me-1"></i>
                {{ $isAdmin ? 'Asignar' : 'Tomar' }}<span x-text="total > 0 ? ' ' + total + ' tareas' : ' tareas'"> tareas</span>
            </button>
        </div>

        </div>{{-- /x-data masivo --}}
    </x-modal>

    {{-- ══════════════════════════════════════════════════════════════════════
         MODAL: Tarea manual
    ══════════════════════════════════════════════════════════════════════ --}}
    <x-modal name="tareaManualModal" :show="false" maxWidth="md">
        <form wire:submit.prevent="guardarManual">
        <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nueva tarea manual</h5>
            <button type="button" class="btn-close"
                    onclick="window.dispatchEvent(new CustomEvent('close-modal',{detail:'tareaManualModal'}))">
            </button>
        </div>
        <div class="modal-body">
            @if($errors->any())
                <div class="alert alert-danger py-2" style="font-size:.82rem;">
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            <div class="mb-3">
                <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                <input wire:model="formNombre" type="text" class="form-control"
                       placeholder="Ej: Llamar al proveedor de Netflix">
                @error('formNombre') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Descripción</label>
                <textarea wire:model="formDesc" class="form-control" rows="3"
                          placeholder="Detalles adicionales…"></textarea>
            </div>
            <div class="tb-form-grid">
                <div>
                    <label class="form-label fw-semibold">Prioridad</label>
                    <select wire:model="formPrioridad" class="form-select">
                        <option value="alta">🔴 Alta</option>
                        <option value="media">🟡 Media</option>
                        <option value="baja">⚪ Baja</option>
                    </select>
                </div>
                <div>
                    <label class="form-label fw-semibold">Fecha límite</label>
                    <input wire:model="formFecha" type="date" class="form-control">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                    onclick="window.dispatchEvent(new CustomEvent('close-modal',{detail:'tareaManualModal'}))">
                Cancelar
            </button>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <span wire:loading wire:target="guardarManual" class="spinner-border spinner-border-sm me-1"></span>
                <i wire:loading.remove wire:target="guardarManual" class="fas fa-save me-1"></i>
                Crear tarea
            </button>
        </div>
        </form>
    </x-modal>

</div>
