@extends('layouts.static')

@section('title', 'Inteligencia de Negocio')

@section('h1')
    <i class="fas fa-brain text-primary me-2"></i> Inteligencia de Negocio
@endsection
@section('breadcrumb') Inteligencia de Negocio @endsection
@section('introduccion')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-1">Análisis de servicios, ventas y clientes</h4>
            <p class="mb-0 text-muted">Todo lo que no cabe en el dashboard principal: de dónde vienen las ventas, qué tan fieles son los clientes y cómo evoluciona cada servicio.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
        </a>
    </div>
@endsection

@section('styles')
<style>
.kpi-card {
    border: none; border-radius: 14px; padding: 18px 20px;
    position: relative; overflow: hidden;
}
.kpi-card .kpi-label { font-size: .68rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; opacity: .75; margin-bottom: 4px; }
.kpi-card .kpi-value { font-size: 1.5rem; font-weight: 800; line-height: 1.1; }
.kpi-card .kpi-sub { font-size: .74rem; opacity: .65; margin-top: 2px; }

.chart-card { border: none; border-radius: 14px; overflow: hidden; }
.chart-card .card-header { font-weight: 600; font-size: .88rem; padding: 14px 20px; border-bottom: 1px solid var(--bs-border-color); background: var(--bs-body-bg); }
.fin-tabs .nav-link { font-weight: 600; font-size: .85rem; color: var(--bs-secondary-color); border: none; border-bottom: 3px solid transparent; border-radius: 0; padding: 10px 16px; }
.fin-tabs .nav-link.active { color: var(--bs-primary); border-bottom-color: var(--bs-primary); background: transparent; }
.servicio-btn { border: 1px solid var(--bs-border-color); border-radius: 8px; padding: 5px 12px; font-size: .78rem; font-weight: 600; cursor: pointer; background: var(--bs-body-bg); color: var(--bs-body-color); }
.servicio-btn.active, .servicio-btn:hover { background: var(--bs-primary); color: #fff; border-color: var(--bs-primary); }
.segmento-badge { font-size: .7rem; padding: 4px 10px; }
</style>
@endsection

@section('content')

{{-- ── Tabs ────────────────────────────────────────── --}}
<ul class="nav fin-tabs mb-4" id="biTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="resumen-tab" data-bs-toggle="tab" data-bs-target="#resumen" type="button" role="tab">
            <i class="fas fa-chart-pie me-1"></i> Resumen
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="ventas-tab" data-bs-toggle="tab" data-bs-target="#ventas" type="button" role="tab">
            <i class="fas fa-file-invoice-dollar me-1"></i> Ventas
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="servicios-tab" data-bs-toggle="tab" data-bs-target="#servicios" type="button" role="tab">
            <i class="fas fa-server me-1"></i> Servicios
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="clientes-tab" data-bs-toggle="tab" data-bs-target="#clientes" type="button" role="tab">
            <i class="fas fa-users me-1"></i> Clientes
            <span class="badge bg-primary ms-1 rounded-pill" style="font-size:.65rem;">{{ $clientesRFM->total() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="retencion-tab" data-bs-toggle="tab" data-bs-target="#retencion" type="button" role="tab">
            <i class="fas fa-chart-line me-1"></i> Retención
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="cuentas-tab" data-bs-toggle="tab" data-bs-target="#cuentas" type="button" role="tab">
            <i class="fas fa-tv me-1"></i> Cuentas
            @php $totalDanadas = collect($cuentasResumen)->sum('danadas'); @endphp
            @if($totalDanadas > 0)
            <span class="badge bg-danger ms-1 rounded-pill" style="font-size:.65rem;">{{ $totalDanadas }}</span>
            @endif
        </button>
    </li>
</ul>

<div class="tab-content" id="biTabsContent">

    {{-- ═══ TAB RESUMEN ═══════════════════════════════════ --}}
    <div class="tab-pane fade show active" id="resumen" role="tabpanel">
        <div class="card chart-card shadow-sm mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i> Comparativa de los 9 servicios (mes actual, ordenado por ganancia)
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th class="text-end">Cuentas</th>
                            <th class="text-end">Usuarios</th>
                            <th class="text-end">Ingresos</th>
                            <th class="text-end">Costos</th>
                            <th class="text-end">Ganancia</th>
                            <th class="text-end">% del total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalGanancia = $comparativaServicios->sum('ganancia'); @endphp
                        @foreach($comparativaServicios as $s)
                        <tr>
                            <td class="fw-semibold">{{ $s['nombre'] }}</td>
                            <td class="text-end">{{ $s['cuentas'] }}</td>
                            <td class="text-end">{{ $s['usuarios'] }}</td>
                            <td class="text-end">${{ number_format($s['ingresos'], 2) }}</td>
                            <td class="text-end">${{ number_format($s['costos'], 2) }}</td>
                            <td class="text-end fw-bold {{ $s['ganancia'] >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($s['ganancia'], 2) }}</td>
                            <td class="text-end">{{ $totalGanancia > 0 ? number_format($s['ganancia'] / $totalGanancia * 100, 1) : 0 }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══ TAB VENTAS ════════════════════════════════════ --}}
    <div class="tab-pane fade" id="ventas" role="tabpanel">
        <p class="text-muted small mb-3"><i class="fas fa-calendar-alt me-1"></i>Mes actual — de dónde vienen las ventas</p>
        <div class="row g-3 mb-4">
            @php
                $colores = ['nueva'=>'kpi-blue','ampliacion'=>'kpi-green','renovacion'=>'kpi-purple','reactivacion'=>'kpi-orange','sin_clasificar'=>'kpi-teal'];
                $iconos = ['nueva'=>'fa-user-plus','ampliacion'=>'fa-arrow-trend-up','renovacion'=>'fa-rotate','reactivacion'=>'fa-user-check','sin_clasificar'=>'fa-question'];
            @endphp
            @foreach($desgloseVentasMes['desglose'] as $d)
            <div class="col-xl-2-4 col-lg-4 col-md-6" style="flex:1 1 19%; max-width:19%;">
                <div class="kpi-card {{ $colores[$d['tipo']] }} shadow-sm h-100">
                    <div class="kpi-label"><i class="fas {{ $iconos[$d['tipo']] }} me-1"></i>{{ $d['etiqueta'] }}</div>
                    <div class="kpi-value">{{ $d['cantidad'] }}</div>
                    <div class="kpi-sub">${{ number_format($d['monto'], 2) }} · {{ $d['pct_cantidad'] }}%</div>
                </div>
            </div>
            @endforeach
            <div class="col-xl-2-4 col-lg-4 col-md-6" style="flex:1 1 19%; max-width:19%;">
                <div class="kpi-card kpi-red shadow-sm h-100">
                    <div class="kpi-label"><i class="fas fa-coins me-1"></i>Total ventas</div>
                    <div class="kpi-value">{{ $desgloseVentasMes['total_cantidad'] }}</div>
                    <div class="kpi-sub">${{ number_format($desgloseVentasMes['total_monto'], 2) }}</div>
                </div>
            </div>
        </div>

        <div class="card chart-card shadow-sm mb-4">
            <div class="card-header"><i class="fas fa-chart-column me-1"></i> Ventas por tipo, últimos 12 meses</div>
            <div class="card-body p-3" style="height:320px;">
                <canvas id="ventasTipoChart"></canvas>
            </div>
            <div class="card-footer text-muted" style="font-size:.75rem;">
                "Sin clasificar" son ventas registradas antes de esta funcionalidad; se completan con el comando <code>ventas:clasificar-tipo</code>.
            </div>
        </div>
    </div>

    {{-- ═══ TAB SERVICIOS ═════════════════════════════════ --}}
    <div class="tab-pane fade" id="servicios" role="tabpanel">
        <div class="card chart-card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="fas fa-chart-line me-1"></i> Nuevos vs. perdidos por servicio, últimos 12 meses</span>
                <div class="d-flex gap-1 flex-wrap" id="servicioSelector">
                    @foreach($nuevosPerdidos['servicios'] as $idser => $s)
                    <button class="servicio-btn {{ $loop->first ? 'active' : '' }}" data-idser="{{ $idser }}">{{ $s['nombre'] }}</button>
                    @endforeach
                </div>
            </div>
            <div class="card-body p-3" style="height:320px;">
                <canvas id="servicioLineChart"></canvas>
            </div>
            <div class="card-footer text-muted" style="font-size:.75rem;">
                Nuevo = primera compra de ese cliente en ese servicio. Perdido = su última suscripción a ese servicio venció y no se renovó.
            </div>
        </div>

        <div class="card chart-card shadow-sm mb-4">
            <div class="card-header"><i class="fas fa-list-ol me-1"></i> Totales de los últimos 12 meses por servicio</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Servicio</th><th class="text-end">Nuevos</th><th class="text-end">Perdidos</th><th class="text-end">Saldo neto</th></tr></thead>
                    <tbody>
                        @foreach($nuevosPerdidos['servicios'] as $idser => $s)
                        @php $tn = array_sum($s['nuevos']); $tp = array_sum($s['perdidos']); @endphp
                        <tr>
                            <td class="fw-semibold">{{ $s['nombre'] }}</td>
                            <td class="text-end text-success">+{{ $tn }}</td>
                            <td class="text-end text-danger">-{{ $tp }}</td>
                            <td class="text-end fw-bold {{ ($tn-$tp) >= 0 ? 'text-success' : 'text-danger' }}">{{ $tn - $tp >= 0 ? '+' : '' }}{{ $tn - $tp }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══ TAB CLIENTES ══════════════════════════════════ --}}
    <div class="tab-pane fade" id="clientes" role="tabpanel">
        <div class="d-flex flex-wrap gap-2 mb-4">
            @foreach($resumenSegmentos as $rs)
            <a href="{{ route('inteligencia-negocio', ['segmento' => $segmento === $rs['segmento'] ? null : $rs['segmento']]) }}"
               class="badge segmento-badge text-decoration-none {{ $segmento === $rs['segmento'] ? 'bg-primary' : 'bg-secondary-subtle text-body border' }}">
                {{ $rs['etiqueta'] }}: {{ $rs['cantidad'] }}
            </a>
            @endforeach
            @if($segmento)
            <a href="{{ route('inteligencia-negocio') }}" class="badge segmento-badge bg-danger-subtle text-danger border text-decoration-none">
                <i class="fas fa-times me-1"></i>Quitar filtro
            </a>
            @endif
        </div>

        <form method="GET" action="{{ route('inteligencia-negocio') }}#clientes" class="mb-3">
            @if($segmento)<input type="hidden" name="segmento" value="{{ $segmento }}">@endif
            <div class="input-group" style="max-width:360px;">
                <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Buscar por nombre o teléfono" value="{{ $search }}">
                <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>

        <div class="card chart-card shadow-sm mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Segmento</th>
                            <th class="text-center">Vigente</th>
                            <th class="text-end">Compras</th>
                            <th class="text-end">Total gastado</th>
                            <th class="text-end">Última compra</th>
                            <th class="text-end">Cliente desde</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $segmentoColor = [
                                'fiel' => 'success', 'gastador' => 'success', 'nuevo' => 'info', 'regular' => 'secondary',
                                'en_riesgo' => 'danger', 'ocasional' => 'warning', 'temporada' => 'dark',
                            ];
                        @endphp
                        @forelse($clientesRFM as $c)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $c['nombre'] }}</div>
                                <div class="text-muted" style="font-size:.75rem;">{{ $c['telefono'] }}</div>
                            </td>
                            <td><span class="badge bg-{{ $segmentoColor[$c['segmento']] ?? 'secondary' }}-subtle text-{{ $segmentoColor[$c['segmento']] ?? 'secondary' }} border">{{ $segmentos[$c['segmento']] ?? $c['segmento'] }}</span></td>
                            <td class="text-center">
                                @if($c['vigente'])
                                    <i class="fas fa-circle-check text-success" title="Tiene algo pagado y vigente"></i>
                                @else
                                    <i class="fas fa-circle-xmark text-danger" title="Ya venció y no ha vuelto a comprar"></i>
                                @endif
                            </td>
                            <td class="text-end">{{ $c['frequency'] }}</td>
                            <td class="text-end fw-semibold">${{ number_format($c['monetary'], 2) }}</td>
                            <td class="text-end">{{ $c['ultima_compra'] }} <span class="text-muted">({{ $c['recency_dias'] }}d)</span></td>
                            <td class="text-end">{{ $c['primera_compra'] }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No hay clientes que coincidan con el filtro.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($clientesRFM->hasPages())
            <div class="card-footer">{{ $clientesRFM->onEachSide(1)->fragment('clientes')->links() }}</div>
            @endif
        </div>
    </div>

    {{-- ═══ TAB RETENCIÓN ═════════════════════════════════ --}}
    <div class="tab-pane fade" id="retencion" role="tabpanel">
        <div class="card chart-card shadow-sm mb-4">
            <div class="card-header"><i class="fas fa-layer-group me-1"></i> Cohortes de retención (últimos 12 meses)</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mes de entrada</th>
                            <th class="text-end">Clientes nuevos</th>
                            <th class="text-end">Retención a 1 mes</th>
                            <th class="text-end">Retención a 3 meses</th>
                            <th class="text-end">Retención a 6 meses</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cohortes as $co)
                        <tr>
                            <td class="fw-semibold">{{ $co['mes'] }}</td>
                            <td class="text-end">{{ $co['clientes'] }}</td>
                            @foreach(['m1','m3','m6'] as $key)
                            <td class="text-end">
                                @if($co[$key] === null)
                                    <span class="text-muted">—</span>
                                @else
                                    <span class="{{ $co[$key] >= 50 ? 'text-success' : ($co[$key] >= 25 ? 'text-warning' : 'text-danger') }} fw-semibold">{{ $co[$key] }}%</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Todavía no hay suficiente historial para calcular cohortes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted" style="font-size:.75rem;">
                % de clientes de esa cohorte que registran al menos una compra N meses después de su primera compra. "—" = todavía no pasó ese tiempo para esa cohorte.
            </div>
        </div>
    </div>

    {{-- ═══ TAB CUENTAS ═══════════════════════════════════ --}}
    <div class="tab-pane fade" id="cuentas" role="tabpanel">
        @php
            $totalDanadasHoy = collect($cuentasResumen)->sum('danadas');
            $totalVencidasHoy = collect($cuentasResumen)->sum('vencidas');
            $totalNuevasMes = collect($incidenciasPorServicio)->sum('nuevas');
            $totalReparadasMes = collect($incidenciasPorServicio)->sum('reparadas');
            $reparadasConTiempo = collect($incidenciasPorServicio)->filter(fn($f) => $f['promedio_horas'] !== null);
            $promedioGeneralHoras = $reparadasConTiempo->isNotEmpty()
                ? round($reparadasConTiempo->sum(fn($f) => $f['promedio_horas'] * $f['reparadas']) / $reparadasConTiempo->sum('reparadas'), 1)
                : null;
        @endphp
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card kpi-red shadow-sm h-100">
                    <div class="kpi-label"><i class="fas fa-triangle-exclamation me-1"></i>Dañadas ahora</div>
                    <div class="kpi-value">{{ $totalDanadasHoy }}</div>
                    <div class="kpi-sub">De todos los servicios</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card kpi-orange shadow-sm h-100">
                    <div class="kpi-label"><i class="fas fa-clock me-1"></i>Tiempo prom. reparación</div>
                    <div class="kpi-value">{{ $promedioGeneralHoras !== null ? $promedioGeneralHoras . 'h' : '—' }}</div>
                    <div class="kpi-sub">Reparadas este mes</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card kpi-purple shadow-sm h-100">
                    <div class="kpi-label"><i class="fas fa-plug-circle-xmark me-1"></i>Se dañaron este mes</div>
                    <div class="kpi-value">{{ $totalNuevasMes }}</div>
                    <div class="kpi-sub">Reparadas: {{ $totalReparadasMes }}</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card kpi-teal shadow-sm h-100">
                    <div class="kpi-label"><i class="fas fa-hourglass-half me-1"></i>Vencidas sin renovar</div>
                    <div class="kpi-value">{{ $totalVencidasHoy }}</div>
                    <div class="kpi-sub">No cuentan como dañadas</div>
                </div>
            </div>
        </div>

        <div class="alert alert-info" style="font-size:.82rem;">
            <i class="fas fa-circle-info me-1"></i>
            El seguimiento de "cuánto tiempo dura dañada" recién empieza a registrarse desde hoy — antes no existía este dato.
            Los KPIs de arriba y la línea de tiempo van a ganar precisión con cada día que pase.
        </div>

        <div class="card chart-card shadow-sm mb-4">
            <div class="card-header"><i class="fas fa-chart-bar me-1"></i> Estado de cuentas por servicio (hoy)</div>
            <div class="card-body p-3" style="height:300px;">
                <canvas id="cuentasEstadoChart"></canvas>
            </div>
        </div>

        <div class="card chart-card shadow-sm mb-4">
            <div class="card-header"><i class="fas fa-table me-1"></i> Incidencias por servicio (mes actual)</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th class="text-end">Se dañaron</th>
                            <th class="text-end">Se repararon</th>
                            <th class="text-end">Tiempo prom. reparación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($incidenciasPorServicio as $f)
                        <tr>
                            <td class="fw-semibold">{{ $f['nombre'] }}</td>
                            <td class="text-end">{{ $f['nuevas'] }}</td>
                            <td class="text-end text-success">{{ $f['reparadas'] }}</td>
                            <td class="text-end">{{ $f['promedio_horas'] !== null ? $f['promedio_horas'] . 'h' : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card chart-card shadow-sm mb-4">
            <div class="card-header"><i class="fas fa-list me-1"></i> Incidencias del mes (detalle)</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Servicio</th>
                            <th>Se dañó</th>
                            <th>Se reparó</th>
                            <th class="text-end">Duración</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidenciasDetalle as $inc)
                        <tr>
                            <td class="fw-semibold">{{ $inc['idcue'] }}</td>
                            <td>{{ $inc['servicio'] }}</td>
                            <td class="text-muted small">{{ $inc['inicio']->format('d/m/Y H:i') }}</td>
                            <td class="text-muted small">{{ $inc['fin']?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="text-end">{{ $inc['duracion_horas'] !== null ? $inc['duracion_horas'] . 'h' : '—' }}</td>
                            <td>
                                @if($inc['estado'] === 'reparada')
                                    <span class="badge bg-success-subtle text-success border">Reparada</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border">En curso</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Sin incidencias este mes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card chart-card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="fas fa-timeline me-1"></i> Línea de tiempo de cuentas por servicio</span>
                <div class="d-flex gap-1 flex-wrap" id="timelineServicioSelector">
                    @foreach($timelineServicios as $key => $t)
                    <button class="servicio-btn timeline-btn {{ $loop->first ? 'active' : '' }}" data-servicio="{{ $key }}">{{ $t['nombre'] }} ({{ count($t['cuentas']) }})</button>
                    @endforeach
                </div>
            </div>
            <div class="d-flex gap-3 align-items-center px-3 pt-3 flex-wrap" style="font-size:.78rem;">
                <span><span class="d-inline-block rounded-1" style="width:12px;height:12px;background:#10b981;"></span> Activa (pagada)</span>
                <span><span class="d-inline-block rounded-1" style="width:12px;height:12px;background:#cbd5e1;"></span> Vencida sin renovar</span>
                <span><span class="d-inline-block rounded-1" style="width:12px;height:12px;background:#ef4444;"></span> Dañada</span>
            </div>
            <div class="d-flex justify-content-between align-items-center gap-2 px-3 pt-2">
                <button type="button" id="timelinePrev" class="btn btn-sm btn-outline-secondary" title="Ver más atrás">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="fw-semibold small" id="timelineRango"></span>
                <button type="button" id="timelineNext" class="btn btn-sm btn-outline-secondary" title="Ver más reciente">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="card-body p-3">
                <div id="timelineAxis" style="position:relative; height:20px; margin-left:138px; border-bottom:1px solid var(--bs-border-color); margin-bottom:6px;"></div>
                <div id="timelineContainer" style="cursor:ew-resize;"></div>
            </div>
            <div class="card-footer text-muted" style="font-size:.75rem;">
                Por defecto se ven los últimos 2 meses — deslizá con la rueda del mouse, arrastrá, o usá las flechas para ver más atrás.
                El tramo "Activa" es aproximado (desde que se creó la cuenta hasta que venció por última vez, sin distinguir cada renovación puntual).
                El tramo "Dañada" es exacto desde que se activó este seguimiento; antes de esa fecha no queda registro aunque haya estado dañada.
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Si venimos de un link de paginación/filtro de la pestaña Clientes, reactivarla al cargar.
    const params = new URLSearchParams(window.location.search);
    if (window.location.hash === '#clientes' || params.has('segmento') || params.has('buscar') || params.has('page')) {
        const tabBtn = document.getElementById('clientes-tab');
        if (tabBtn && window.bootstrap) {
            new bootstrap.Tab(tabBtn).show();
        }
    }

    Chart.defaults.font.family = "'Inter','Segoe UI',system-ui,sans-serif";
    Chart.defaults.font.size = 12;
    const gridColor = 'rgba(150,150,150,.1)';

    /* ── Ventas por tipo, barras apiladas ─────────────── */
    const ventasMensuales = @json($ventasMensuales);
    const tipoColors = {
        nueva: '#3b82f6', ampliacion: '#10b981', renovacion: '#8b5cf6',
        reactivacion: '#f97316', sin_clasificar: '#14b8a6',
    };
    const tipoLabels = {
        nueva: 'Cliente nuevo', ampliacion: 'Cliente activo (compra más)',
        renovacion: 'Renovación', reactivacion: 'Cliente que vuelve', sin_clasificar: 'Sin clasificar',
    };
    new Chart(document.getElementById('ventasTipoChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: ventasMensuales.labels,
            datasets: Object.keys(ventasMensuales.series).map(tipo => ({
                label: tipoLabels[tipo],
                data: ventasMensuales.series[tipo],
                backgroundColor: tipoColors[tipo],
                borderRadius: 3,
            })),
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, beginAtZero: true, grid: { color: gridColor } },
            },
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, font: { size: 11 } } } },
        },
    });

    /* ── Nuevos/perdidos por servicio, selector ────────── */
    const nuevosPerdidos = @json($nuevosPerdidos);
    let servicioChart;
    function renderServicioChart(idser) {
        const s = nuevosPerdidos.servicios[idser];
        if (!s) return;
        if (servicioChart) servicioChart.destroy();
        servicioChart = new Chart(document.getElementById('servicioLineChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: nuevosPerdidos.labels,
                datasets: [
                    { label: 'Nuevos', data: Object.values(s.nuevos), borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.12)', fill: true, tension: .3 },
                    { label: 'Perdidos', data: Object.values(s.perdidos), borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,.12)', fill: true, tension: .3 },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { x: { grid: { color: gridColor } }, y: { beginAtZero: true, grid: { color: gridColor } } },
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } },
            },
        });
    }
    const primerIdser = Object.keys(nuevosPerdidos.servicios)[0];
    if (primerIdser) renderServicioChart(primerIdser);

    document.querySelectorAll('#servicioSelector .servicio-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#servicioSelector .servicio-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            renderServicioChart(this.dataset.idser);
        });
    });

    /* ── Estado de cuentas por servicio (barras) ─────────── */
    const cuentasResumen = @json($cuentasResumen);
    new Chart(document.getElementById('cuentasEstadoChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: cuentasResumen.map(s => s.nombre),
            datasets: [
                { label: 'Activas', data: cuentasResumen.map(s => s.activas), backgroundColor: '#10b981', borderRadius: 3 },
                { label: 'Vencidas sin renovar', data: cuentasResumen.map(s => s.vencidas), backgroundColor: '#cbd5e1', borderRadius: 3 },
                { label: 'Dañadas', data: cuentasResumen.map(s => s.danadas), backgroundColor: '#ef4444', borderRadius: 3 },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, beginAtZero: true, grid: { color: gridColor } },
            },
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, font: { size: 11 } } } },
        },
    });

    /* ── Linea de tiempo de cuentas por servicio (ventana deslizable) ── */
    const timelineServicios = @json($timelineServicios);
    const timelineContainer = document.getElementById('timelineContainer');
    const timelineAxis = document.getElementById('timelineAxis');
    const timelineRango = document.getElementById('timelineRango');
    const segmentoColor = { activa: '#10b981', vencida: '#cbd5e1', danada: '#ef4444' };
    const MS_DIA = 86400000;
    const VENTANA_DIAS = 60; // 2 meses por defecto

    let timelineServicioActual = null;
    let timelineMinDate = null;   // limite: cuenta mas antigua del servicio actual
    let timelineWindowEnd = hoyMedianoche();

    function hoyMedianoche() {
        const d = new Date();
        d.setHours(0, 0, 0, 0);
        return d;
    }

    function parseFecha(str) {
        return new Date(str + 'T00:00:00');
    }

    function fmtFecha(d) {
        return d.toLocaleDateString('es-EC', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function clampVentana() {
        const hoy = hoyMedianoche();
        if (timelineWindowEnd > hoy) timelineWindowEnd = hoy;
        if (timelineMinDate) {
            const minFin = new Date(timelineMinDate.getTime() + VENTANA_DIAS * MS_DIA);
            if (timelineWindowEnd < minFin) timelineWindowEnd = new Date(Math.min(hoy.getTime(), minFin.getTime()));
        }
    }

    function renderEjeTiempo(inicio, fin) {
        timelineAxis.innerHTML = '';
        const totalMs = fin - inicio;
        const totalDias = totalMs / MS_DIA;
        const pasoDias = totalDias <= 75 ? 7 : (totalDias <= 400 ? 30 : 90);

        let cursor = new Date(inicio);
        while (cursor <= fin) {
            const pct = (cursor - inicio) / totalMs * 100;
            const tick = document.createElement('div');
            tick.style.cssText = `position:absolute; left:${pct}%; top:0; bottom:0; border-left:1px solid var(--bs-border-color); padding-left:4px; font-size:.68rem; color:var(--bs-secondary-color); white-space:nowrap;`;
            tick.textContent = cursor.toLocaleDateString('es-EC', { day: '2-digit', month: 'short' });
            timelineAxis.appendChild(tick);
            cursor = new Date(cursor.getTime() + pasoDias * MS_DIA);
        }
    }

    function renderTimeline() {
        const data = timelineServicios[timelineServicioActual];
        timelineContainer.innerHTML = '';
        timelineAxis.innerHTML = '';
        if (!data) return;

        timelineMinDate = data.desde ? parseFecha(data.desde) : null;
        clampVentana();

        const inicioVentana = new Date(timelineWindowEnd.getTime() - VENTANA_DIAS * MS_DIA);
        const finVentana = timelineWindowEnd;
        const totalMs = finVentana - inicioVentana;

        timelineRango.textContent = `${fmtFecha(inicioVentana)} → ${fmtFecha(finVentana)}`;
        renderEjeTiempo(inicioVentana, finVentana);

        if (!data.cuentas.length) {
            timelineContainer.innerHTML = '<p class="text-muted text-center py-4 mb-0">Sin cuentas activas en este servicio.</p>';
            return;
        }

        data.cuentas.forEach(cuenta => {
            const row = document.createElement('div');
            row.style.cssText = 'display:flex; align-items:center; gap:8px; margin-bottom:4px;';

            const label = document.createElement('div');
            label.style.cssText = 'width:130px; flex-shrink:0; font-size:.72rem; font-family:monospace; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;';
            label.textContent = cuenta.idcue;
            if (cuenta.caida_ahora) label.style.color = '#ef4444';
            row.appendChild(label);

            const track = document.createElement('div');
            track.style.cssText = 'position:relative; flex:1; height:16px; background:var(--bs-tertiary-bg); border-radius:3px; overflow:hidden;';

            cuenta.segmentos.forEach(seg => {
                const segInicio = parseFecha(seg.inicio);
                const segFin = seg.en_curso ? finVentana : parseFecha(seg.fin);
                if (segFin < inicioVentana || segInicio > finVentana) return; // fuera de la ventana visible

                const clipInicio = segInicio < inicioVentana ? inicioVentana : segInicio;
                const clipFin = segFin > finVentana ? finVentana : segFin;
                const left = (clipInicio - inicioVentana) / totalMs * 100;
                const width = Math.max(0.4, (clipFin - clipInicio) / totalMs * 100);

                const bar = document.createElement('div');
                bar.title = `${seg.tipo} — ${seg.inicio} a ${seg.en_curso ? 'ahora' : seg.fin}`;
                bar.style.cssText = `position:absolute; top:0; bottom:0; left:${left}%; width:${width}%; background:${segmentoColor[seg.tipo]};`;
                track.appendChild(bar);
            });

            row.appendChild(track);
            timelineContainer.appendChild(row);
        });
    }

    function desplazarVentana(deltaDias) {
        timelineWindowEnd = new Date(timelineWindowEnd.getTime() + deltaDias * MS_DIA);
        renderTimeline();
    }

    document.getElementById('timelinePrev').addEventListener('click', () => desplazarVentana(-14));
    document.getElementById('timelineNext').addEventListener('click', () => desplazarVentana(14));

    timelineContainer.addEventListener('wheel', e => {
        e.preventDefault();
        const raw = Math.abs(e.deltaX) > Math.abs(e.deltaY) ? e.deltaX : e.deltaY;
        desplazarVentana(Math.sign(raw) * 5);
    }, { passive: false });

    let timelineTouchX = 0;
    timelineContainer.addEventListener('touchstart', e => { timelineTouchX = e.touches[0].clientX; }, { passive: true });
    timelineContainer.addEventListener('touchmove', e => {
        const dx = timelineTouchX - e.touches[0].clientX;
        if (Math.abs(dx) > 15) {
            desplazarVentana(Math.sign(dx) * 3);
            timelineTouchX = e.touches[0].clientX;
        }
    }, { passive: true });

    const primerServicioTimeline = Object.keys(timelineServicios)[0];
    if (primerServicioTimeline) {
        timelineServicioActual = primerServicioTimeline;
        renderTimeline();
    }

    document.querySelectorAll('#timelineServicioSelector .timeline-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#timelineServicioSelector .timeline-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            timelineServicioActual = this.dataset.servicio;
            timelineWindowEnd = hoyMedianoche(); // reiniciar a "ultimos 2 meses" al cambiar de servicio
            renderTimeline();
        });
    });
});
</script>
@endsection
