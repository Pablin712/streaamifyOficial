@extends('layouts.static')

@section('title', 'Metas')

@section('h1')
    <i class="fas fa-bullseye text-primary me-2"></i> Metas del negocio
@endsection
@section('breadcrumb') Metas @endsection

@section('introduccion')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-1">Objetivos de {{ ucfirst($periodo) }}</h4>
            <p class="mb-0 text-muted">
                Cada tarjeta compara lo que llevas con lo que hace falta. El color dice
                si vas a llegar al ritmo actual, no si la cifra es alta o baja.
            </p>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <form method="GET" action="{{ route('metas') }}" class="d-flex gap-2">
                <select name="mes" class="form-select form-select-sm" style="width: auto">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" @selected($m === $mes)>
                            {{ ucfirst(\Carbon\Carbon::create($anio, $m, 1)->locale('es')->translatedFormat('F')) }}
                        </option>
                    @endforeach
                </select>
                <select name="anio" class="form-select form-select-sm" style="width: auto">
                    @foreach (range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}" @selected($y === $anio)>{{ $y }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-filter"></i></button>
            </form>
            @can('metas.store')
                <button type="button" class="btn btn-primary btn-sm" onclick="nuevaMeta()">
                    <i class="fas fa-plus me-1"></i> Nueva meta
                </button>
            @endcan
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-chart-line me-1"></i> Dashboard
            </a>
        </div>
    </div>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-triangle-exclamation me-1"></i>
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ══ Tablero ═══════════════════════════════════════════════════ --}}
    @if (count($tablero))
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <span class="sf-eyebrow">Seguimiento de {{ $periodo }}</span>
            <div class="meta-resumen">
                <span class="meta-resumen-chip">
                    <span class="meta-resumen-punto meta-resumen-punto--good"></span>
                    <span class="sf-num">{{ $resumen['bien'] }}</span> en objetivo
                </span>
                <span class="meta-resumen-chip">
                    <span class="meta-resumen-punto meta-resumen-punto--warning"></span>
                    <span class="sf-num">{{ $resumen['atencion'] }}</span> ajustadas
                </span>
                <span class="meta-resumen-chip">
                    <span class="meta-resumen-punto meta-resumen-punto--critical"></span>
                    <span class="sf-num">{{ $resumen['mal'] }}</span> fuera de ritmo
                </span>
            </div>
        </div>

        <div class="row g-3 mb-4">
            @foreach ($tablero as $eval)
                <div class="col-xl-4 col-md-6">
                    <x-meta-card :eval="$eval" />
                </div>
            @endforeach
        </div>
    @else
        <div class="sf-panel text-center mb-4">
            <p class="mb-2"><i class="fas fa-bullseye fa-2x text-muted"></i></p>
            <h5 class="mb-1">Todavía no hay metas para {{ $periodo }}</h5>
            <p class="text-muted mb-3">
                Fija un objetivo y el tablero te dirá cada día cuánto falta y a qué ritmo hay que ir.
            </p>
            @can('metas.store')
                <button type="button" class="btn btn-primary" onclick="nuevaMeta()">
                    <i class="fas fa-plus me-1"></i> Crear la primera meta
                </button>
            @endcan
        </div>
    @endif

    {{-- ══ Metas configuradas ════════════════════════════════════════ --}}
    <section class="sf-panel">
        <div class="sf-panel-head">
            <h2 class="sf-panel-title">Metas configuradas</h2>
            <span class="sf-panel-meta">{{ $metas->count() }} en total</span>
        </div>

        @if ($metas->isEmpty())
            <p class="text-muted mb-0">No hay ninguna meta definida.</p>
        @else
            <div class="sf-scroll-x">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Indicador</th>
                            <th class="text-end">Objetivo</th>
                            <th>Periodo</th>
                            <th>Alcance</th>
                            <th class="text-end">Umbral</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($metas as $meta)
                            @php $def = $servicio->definicion($meta->kpi); @endphp
                            <tr @class(['opacity-50' => !$meta->activo])>
                                <td>
                                    @if ($def)
                                        <i class="fas {{ $def['icono'] }} me-1 text-muted"></i>
                                        {{ $def['label'] }}
                                        <small class="text-muted d-block">{{ $def['grupo'] }}</small>
                                    @else
                                        <span class="text-danger">
                                            <i class="fas fa-circle-question me-1"></i>{{ $meta->kpi }}
                                        </span>
                                        <small class="text-muted d-block">Indicador ya no disponible</small>
                                    @endif
                                </td>
                                <td class="text-end sf-num">
                                    {{ $def ? $servicio->formatear((float) $meta->objetivo, $def['unidad']) : number_format($meta->objetivo, 2) }}
                                    @if ($def)
                                        <small class="text-muted d-block">
                                            {{ $def['direccion'] === 'subir' ? 'mínimo' : 'máximo' }}
                                        </small>
                                    @endif
                                </td>
                                <td>{{ ucfirst($meta->periodo) }}</td>
                                <td>
                                    @if (is_null($meta->anio))
                                        <span class="badge bg-secondary">Permanente</span>
                                    @elseif (is_null($meta->mes))
                                        <span class="sf-num">{{ $meta->anio }}</span>
                                    @else
                                        <span class="sf-num">
                                            {{ ucfirst(\Carbon\Carbon::create($meta->anio, $meta->mes, 1)->locale('es')->translatedFormat('M Y')) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end sf-num">{{ $meta->umbral_atencion }}%</td>
                                <td>
                                    @if ($meta->activo)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Pausada</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @can('metas.update')
                                        {{-- Js::from escapa las comillas: una nota con apostrofos
                                             rompia el atributo con @json a secas. --}}
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="editarMeta({{ Js::from($meta) }})">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    @endcan
                                    @can('metas.destroy')
                                        <form method="POST" action="{{ route('metas.destroy', $meta->idmet) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('¿Eliminar esta meta?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- ══ Modal crear / editar ══════════════════════════════════════ --}}
    @canany(['metas.store', 'metas.update'])
        <x-modal name="meta-form" maxWidth="lg">
            <x-slot name="title">
                <i class="fas fa-bullseye"></i> <span id="metaFormTitulo">Nueva meta</span>
            </x-slot>

            <form id="metaForm" method="POST" action="{{ route('metas.store') }}">
                @csrf
                <input type="hidden" name="_method" id="metaMetodo" value="POST">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="meta_kpi">Indicador <span class="text-danger">*</span></label>
                        <select name="kpi" id="meta_kpi" class="form-select" required>
                            <option value="">-- Elige el KPI --</option>
                            @foreach ($catalogo as $grupo => $items)
                                <optgroup label="{{ $grupo }}">
                                    @foreach ($items as $codigo => $def)
                                        <option value="{{ $codigo }}"
                                                data-direccion="{{ $def['direccion'] }}"
                                                data-unidad="{{ $def['unidad'] }}"
                                                data-ayuda="{{ $def['ayuda'] }}">
                                            {{ $def['label'] }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <small class="text-muted" id="meta_ayuda"></small>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="meta_objetivo">
                                Objetivo <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" id="meta_unidad_prefijo">#</span>
                                <input type="number" step="0.01" min="0" name="objetivo" id="meta_objetivo"
                                       class="form-control" required>
                            </div>
                            <small class="text-muted" id="meta_direccion_ayuda"></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="meta_umbral">Umbral de aviso</label>
                            <div class="input-group">
                                <input type="number" min="10" max="100" name="umbral_atencion" id="meta_umbral"
                                       class="form-control" value="90">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">
                                Por debajo de este % de la proyección la tarjeta pasa a rojo.
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="meta_periodo">Periodo</label>
                            <select name="periodo" id="meta_periodo" class="form-select">
                                <option value="mensual">Mensual</option>
                                <option value="anual">Anual</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="meta_alcance">Alcance</label>
                            <select name="alcance" id="meta_alcance" class="form-select">
                                <option value="periodo">Solo este periodo</option>
                                <option value="permanente">Permanente (se repite cada periodo)</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="meta_wrap_anio">
                            <label class="form-label" for="meta_anio">Año</label>
                            <select name="anio" id="meta_anio" class="form-select">
                                @foreach (range(now()->year - 1, now()->year + 1) as $y)
                                    <option value="{{ $y }}" @selected($y === $anio)>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6" id="meta_wrap_mes">
                            <label class="form-label" for="meta_mes">Mes</label>
                            <select name="mes" id="meta_mes" class="form-select">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" @selected($m === $mes)>
                                        {{ ucfirst(\Carbon\Carbon::create($anio, $m, 1)->locale('es')->translatedFormat('F')) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="meta_nota">Nota</label>
                            <input type="text" name="nota" id="meta_nota" class="form-control" maxlength="255"
                                   placeholder="Opcional: por qué esta meta, o cómo se piensa lograr">
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="activo" value="0">
                                <input class="form-check-input" type="checkbox" name="activo" id="meta_activo"
                                       value="1" checked>
                                <label class="form-check-label" for="meta_activo">Meta activa</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar meta
                    </button>
                </div>
            </form>
        </x-modal>
    @endcanany

@endsection

@section('scripts')
<script>
    const metaForm      = document.getElementById('metaForm');
    const metaKpi       = document.getElementById('meta_kpi');
    const metaPeriodo   = document.getElementById('meta_periodo');
    const metaAlcance   = document.getElementById('meta_alcance');
    const rutaStore     = @json(route('metas.store'));
    const plantillaPut  = @json(route('metas.update', ['idmet' => '__ID__']));

    // El prefijo y el texto de ayuda cambian según el KPI: no es lo mismo
    // "al menos $5.000" que "como mucho 50 bajas".
    function refrescarKpi() {
        const opcion = metaKpi.selectedOptions[0];
        if (!opcion || !opcion.value) return;

        const unidad    = opcion.dataset.unidad;
        const direccion = opcion.dataset.direccion;

        document.getElementById('meta_unidad_prefijo').textContent =
            unidad === 'dinero' ? '$' : unidad === 'horas' ? 'h' : unidad === 'porcentaje' ? '%' : '#';

        document.getElementById('meta_ayuda').textContent = opcion.dataset.ayuda || '';
        document.getElementById('meta_direccion_ayuda').textContent = direccion === 'subir'
            ? 'Más es mejor: la meta es un mínimo a alcanzar.'
            : 'Menos es mejor: la meta es un techo que no hay que pasar.';
    }

    // Una meta permanente no lleva fecha; una anual no lleva mes.
    function refrescarAlcance() {
        const permanente = metaAlcance.value === 'permanente';
        const anual      = metaPeriodo.value === 'anual';

        document.getElementById('meta_wrap_anio').style.display = permanente ? 'none' : '';
        document.getElementById('meta_wrap_mes').style.display  = (permanente || anual) ? 'none' : '';
    }

    metaKpi.addEventListener('change', refrescarKpi);
    metaPeriodo.addEventListener('change', refrescarAlcance);
    metaAlcance.addEventListener('change', refrescarAlcance);

    function nuevaMeta() {
        metaForm.reset();
        metaForm.action = rutaStore;
        document.getElementById('metaMetodo').value = 'POST';
        document.getElementById('metaFormTitulo').textContent = 'Nueva meta';
        document.getElementById('meta_umbral').value = 90;
        document.getElementById('meta_activo').checked = true;
        refrescarKpi();
        refrescarAlcance();
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'meta-form' }));
    }

    function editarMeta(meta) {
        metaForm.action = plantillaPut.replace('__ID__', meta.idmet);
        document.getElementById('metaMetodo').value = 'PUT';
        document.getElementById('metaFormTitulo').textContent = 'Editar meta';

        metaKpi.value     = meta.kpi;
        metaPeriodo.value = meta.periodo;
        metaAlcance.value = meta.anio === null ? 'permanente' : 'periodo';

        document.getElementById('meta_objetivo').value = meta.objetivo;
        document.getElementById('meta_umbral').value   = meta.umbral_atencion;
        document.getElementById('meta_nota').value     = meta.nota ?? '';
        document.getElementById('meta_activo').checked = !!meta.activo;

        if (meta.anio) document.getElementById('meta_anio').value = meta.anio;
        if (meta.mes)  document.getElementById('meta_mes').value  = meta.mes;

        refrescarKpi();
        refrescarAlcance();
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'meta-form' }));
    }

    refrescarAlcance();
</script>
@endsection
