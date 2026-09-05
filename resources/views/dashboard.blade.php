@extends('layouts.static')

@section('title', 'Dashboard')

@section('h1')
    <i class="fas fa-chart-line text-primary me-2"></i> Dashboard Financiero
@endsection
@section('breadcrumb') Dashboard @endsection
@section('introduccion')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-1">Bienvenido, <strong>{{ Auth::user()->nombreemp }}</strong></h4>
            <p class="mb-0 text-muted">Resumen del rendimiento financiero y operativo de Streamify HQ</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inteligencia-negocio') }}" class="btn btn-outline-primary">
                <i class="fas fa-brain me-1"></i> Inteligencia de Negocio
            </a>
            <button type="button" class="btn btn-danger" onclick="abrirModalReportes()">
                <i class="fas fa-file-pdf me-1"></i> Generar Reporte
            </button>
        </div>
    </div>
@endsection

@section('styles')
<style>
/* ── KPI Cards ───────────────────────────────────────────── */
.kpi-card {
    border: none;
    border-radius: 14px;
    padding: 20px 22px;
    position: relative;
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
}
.kpi-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.12) !important; }
.kpi-card .kpi-icon {
    width: 52px; height: 52px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.kpi-card .kpi-label {
    font-size: .7rem; font-weight: 700;
    letter-spacing: .06em; text-transform: uppercase;
    opacity: .75; margin-bottom: 4px;
}
.kpi-card .kpi-value {
    font-size: 1.65rem; font-weight: 800; line-height: 1.1;
}
.kpi-card .kpi-sub { font-size: .75rem; opacity: .6; margin-top: 2px; }
.kpi-card::after {
    content: '';
    position: absolute; bottom: -16px; right: -16px;
    width: 80px; height: 80px; border-radius: 50%;
    opacity: .07;
}

/* Color variants */

/* ── Section headers ─────────────────────────────────────── */
.dash-section-label {
    font-size: .7rem; font-weight: 700; letter-spacing: .09em;
    text-transform: uppercase; opacity: .55; margin-bottom: 12px;
}

/* ── Chart cards ─────────────────────────────────────────── */
.chart-card { border: none; border-radius: 14px; overflow: hidden; }
.chart-card .card-header {
    font-weight: 600; font-size: .88rem;
    padding: 14px 20px;
    border-bottom: 1px solid var(--bs-border-color);
    background: var(--bs-body-bg);
    display: flex; align-items: center; justify-content: space-between;
}
.chart-card .card-footer {
    font-size: .75rem; padding: 8px 20px;
    background: var(--bs-tertiary-bg); border-top: 1px solid var(--bs-border-color);
}
.interval-btn {
    border: 1px solid var(--bs-border-color);
    border-radius: 6px; padding: 3px 10px;
    font-size: .75rem; font-weight: 600; cursor: pointer;
    background: var(--bs-body-bg); color: var(--bs-body-color);
    transition: all .15s;
}
.interval-btn.active, .interval-btn:hover {
    background: var(--bs-primary); color: #fff; border-color: var(--bs-primary);
}

/* ── Finance summary table ───────────────────────────────── */
.finance-table th { font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.finance-table td { font-size: .85rem; }
</style>
@endsection

@section('content')

{{-- ══ ROW 0: Metas del mes ══════════════════════════════ --}}
@can('metas')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <p class="dash-section-label mb-0"><i class="fas fa-bullseye me-1"></i>Metas de {{ ucfirst(now()->locale('es')->translatedFormat('F')) }}</p>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if (count($metasTablero))
                <div class="meta-resumen">
                    <span class="meta-resumen-chip">
                        <span class="meta-resumen-punto meta-resumen-punto--good"></span>
                        <span class="sf-num">{{ $metasResumen['bien'] }}</span> en objetivo
                    </span>
                    <span class="meta-resumen-chip">
                        <span class="meta-resumen-punto meta-resumen-punto--warning"></span>
                        <span class="sf-num">{{ $metasResumen['atencion'] }}</span> ajustadas
                    </span>
                    <span class="meta-resumen-chip">
                        <span class="meta-resumen-punto meta-resumen-punto--critical"></span>
                        <span class="sf-num">{{ $metasResumen['mal'] }}</span> fuera de ritmo
                    </span>
                    @if (($metasResumen['sin_datos'] ?? 0) > 0)
                        <span class="meta-resumen-chip">
                            <span class="meta-resumen-punto meta-resumen-punto--neutro"></span>
                            <span class="sf-num">{{ $metasResumen['sin_datos'] }}</span> sin datos
                        </span>
                    @endif
                </div>
            @endif
            <a href="{{ route('metas') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-sliders me-1"></i> Gestionar metas
            </a>
        </div>
    </div>

    @if (count($metasTablero))
        <div class="row g-3 mb-4">
            @foreach ($metasTablero as $eval)
                <div class="col-xl-4 col-md-6">
                    <x-meta-card :eval="$eval" />
                </div>
            @endforeach
        </div>
    @else
        <div class="sf-panel d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <strong>Sin metas definidas.</strong>
                <span class="text-muted">Fija un objetivo y el dashboard te dirá cada día cuánto falta y a qué ritmo ir.</span>
            </div>
            @can('metas.store')
                <a href="{{ route('metas') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Definir metas
                </a>
            @endcan
        </div>
    @endif
@endcan

{{-- ══ ROW 1: Ingresos & Ventas ══════════════════════════ --}}
<p class="dash-section-label"><i class="fas fa-money-bill-wave me-1"></i>Ingresos y Ventas</p>
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-blue shadow-sm h-100 d-flex align-items-center gap-3">
            <div class="kpi-icon"><i class="fas fa-calendar-alt"></i></div>
            <div>
                <div class="kpi-label">Ingresos este mes</div>
                <div class="kpi-value">${{ number_format($ingresos_mes, 2) }}</div>
                <div class="kpi-sub">Ventas: {{ $ventas_mes }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-green shadow-sm h-100 d-flex align-items-center gap-3">
            <div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div>
            <div>
                <div class="kpi-label">Ingresos este año</div>
                <div class="kpi-value">${{ number_format($ingresos_ano, 2) }}</div>
                <div class="kpi-sub">Ventas: {{ $ventas_ano }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-teal shadow-sm h-100 d-flex align-items-center gap-3">
            <div class="kpi-icon"><i class="fas fa-coins"></i></div>
            <div>
                <div class="kpi-label">Total en bancos</div>
                <div class="kpi-value">${{ number_format($totalDisponible ?? 0, 2) }}</div>
                <div class="kpi-sub">Suma de todos los bancos</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        @php $balanceColor = (($totalDisponible??0) - ($totalDeudasPendientes??0)) >= 0 ? 'kpi-green' : 'kpi-red'; @endphp
        <div class="kpi-card {{ $balanceColor }} shadow-sm h-100 d-flex align-items-center gap-3">
            <div class="kpi-icon"><i class="fas fa-balance-scale"></i></div>
            <div>
                <div class="kpi-label">Balance neto</div>
                <div class="kpi-value">${{ number_format(($totalDisponible??0)-($totalDeudasPendientes??0), 2) }}</div>
                <div class="kpi-sub">Deudas: ${{ number_format($totalDeudasPendientes??0, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-yellow shadow-sm h-100 d-flex align-items-center gap-3">
            <div class="kpi-icon"><i class="fas fa-user-tie"></i></div>
            <div>
                <div class="kpi-label">Retiro / personal este mes</div>
                <div class="kpi-value">${{ number_format($gastos_personal_mes ?? 0, 2) }}</div>
                <div class="kpi-sub">Informativo, no afecta la utilidad</div>
            </div>
        </div>
    </div>
</div>

{{-- ══ ROW 2: Operaciones ═════════════════════════════════ --}}
<p class="dash-section-label"><i class="fas fa-server me-1"></i>Operaciones</p>
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-indigo shadow-sm h-100 d-flex align-items-center gap-3">
            <div class="kpi-icon"><i class="fas fa-crown"></i></div>
            <div>
                <div class="kpi-label">Cuentas activas</div>
                <div class="kpi-value">{{ $num_cuentas }}</div>
                <div class="kpi-sub">Espacios: {{ $espacios }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-orange shadow-sm h-100 d-flex align-items-center gap-3">
            <div class="kpi-icon"><i class="fas fa-clock"></i></div>
            <div>
                <div class="kpi-label">Pagos pendientes</div>
                <div class="kpi-value">{{ $usuarios_acobrar }}</div>
                <div class="kpi-sub">Usuarios por cobrar</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-red shadow-sm h-100 d-flex align-items-center gap-3">
            <div class="kpi-icon"><i class="fas fa-tools"></i></div>
            <div>
                <div class="kpi-label">Cuentas caídas</div>
                <div class="kpi-value">{{ $cuentas_caidas }}</div>
                <div class="kpi-sub">Requieren atención</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-purple shadow-sm h-100 d-flex align-items-center gap-3">
            <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <div class="kpi-label">Deudas pendientes</div>
                <div class="kpi-value">${{ number_format($totalDeudasPendientes??0, 2) }}</div>
                <div class="kpi-sub">Por pagar a proveedores</div>
            </div>
        </div>
    </div>
</div>

{{-- ══ ROW 3: Clientes ════════════════════════════════════ --}}
<p class="dash-section-label"><i class="fas fa-users me-1"></i>Clientes</p>
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-blue shadow-sm h-100 d-flex align-items-center gap-3">
            <div class="kpi-icon"><i class="fas fa-users"></i></div>
            <div>
                <div class="kpi-label">Clientes activos</div>
                <div class="kpi-value">{{ $clientes_activos }}</div>
                <div class="kpi-sub">Usuarios: {{ $total_usuarios_activos }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-green shadow-sm h-100 d-flex align-items-center gap-3">
            <div class="kpi-icon"><i class="fas fa-id-badge"></i></div>
            <div>
                <div class="kpi-label">Usuarios activos</div>
                <div class="kpi-value">{{ $total_usuarios_activos }}</div>
                <div class="kpi-sub">Suscripciones vigentes</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-yellow shadow-sm h-100 d-flex align-items-center gap-3">
            <div class="kpi-icon"><i class="fas fa-coins"></i></div>
            <div>
                <div class="kpi-label">Media de pago / cliente</div>
                <div class="kpi-value">${{ number_format($promedio_pagos_mes, 2) }}</div>
                <div class="kpi-sub">Este mes</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-teal shadow-sm h-100 d-flex align-items-center gap-3">
            <div class="kpi-icon"><i class="fas fa-user-tie"></i></div>
            <div>
                <div class="kpi-label">Cliente más facturado</div>
                <div class="kpi-value" style="font-size:1.1rem;">{{ $cliente_mas_facturado->nombre_cliente ?? '—' }}</div>
                <div class="kpi-sub">${{ number_format($cliente_mas_facturado->facturado ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-red shadow-sm h-100 d-flex align-items-center gap-3">
            <div class="kpi-icon"><i class="fas fa-user-slash"></i></div>
            <div>
                <div class="kpi-label">Clientes perdidos hoy</div>
                <div class="kpi-value">{{ $clientes_perdidos }}</div>
                <div class="kpi-sub">Quedaron con 0 usuarios activos</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-orange shadow-sm h-100 d-flex align-items-center gap-3">
            <div class="kpi-icon"><i class="fas fa-user-minus"></i></div>
            <div>
                <div class="kpi-label">Usuarios removidos hoy</div>
                <div class="kpi-value">{{ $usuarios_removidos }}</div>
                <div class="kpi-sub">Veces que se quitó un usuario</div>
            </div>
        </div>
    </div>
</div>

{{-- ══ Tabla de resultados ════════════════════════════════ --}}
@include('partials.dashboard-statistics-table')

{{-- ══ Resumen Financiero ═════════════════════════════════ --}}
<div class="card chart-card shadow-sm mb-4">
    <div class="card-header">
        <span><i class="fas fa-calculator me-2 text-primary"></i>Resumen Financiero del Mes</span>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <h6 class="fw-bold mb-3 small text-uppercase opacity-75"><i class="fas fa-chart-pie me-1"></i>Análisis de resultados</h6>
                <table class="table table-sm finance-table">
                    <thead><tr><th>Concepto</th><th class="text-end">Monto</th><th class="text-end">%</th></tr></thead>
                    <tbody>
                        <tr><td><strong>Ingresos</strong></td><td class="text-end">${{ number_format($ingresos_mes,2) }}</td><td class="text-end fw-bold">100%</td></tr>
                        <tr><td>Costos</td><td class="text-end">${{ number_format($costos_mes,2) }}</td><td class="text-end">{{ number_format($costos_pct,1) }}%</td></tr>
                        <tr><td>Gastos operativos</td><td class="text-end">${{ number_format($gastos_mes,2) }}</td><td class="text-end">{{ number_format($gastos_pct,1) }}%</td></tr>
                        <tr class="table-{{ $balance>=0?'success':'danger' }} fw-bold">
                            <td>Utilidad real del negocio</td>
                            <td class="text-end">${{ number_format($balance,2) }}</td>
                            <td class="text-end">{{ number_format($balance_pct,1) }}%</td>
                        </tr>
                        <tr class="text-muted">
                            <td><i class="fas fa-user-tie me-1"></i>Retiro / personal (informativo)</td>
                            <td class="text-end">${{ number_format($gastos_personal_mes ?? 0,2) }}</td>
                            <td class="text-end">—</td>
                        </tr>
                    </tbody>
                </table>
                <p class="text-muted small mb-0"><i class="fas fa-circle-info me-1"></i>El retiro/personal no se resta de la utilidad real; es dinero que sale de esa utilidad, no un gasto del negocio.</p>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold mb-3 small text-uppercase opacity-75"><i class="fas fa-receipt me-1"></i>Desglose de gastos</h6>
                <table class="table table-sm finance-table">
                    <thead><tr><th>Concepto</th><th class="text-end">Monto</th><th class="text-end">%</th></tr></thead>
                    <tbody>
                        @foreach($gastos as $g)
                        <tr class="{{ $g['excluido_de_ganancia'] ? 'text-muted' : '' }}">
                            <td>{{ $g['concepto'] }} @if($g['excluido_de_ganancia'])<span class="badge bg-secondary ms-1" style="font-size:.65rem;">informativo</span>@endif</td>
                            <td class="text-end">${{ number_format($g['total'],2) }}</td>
                            <td class="text-end">{{ $g['porcentaje'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ══ Gráfico de progreso (área) ═════════════════════════ --}}
<div class="card chart-card shadow-sm mb-4">
    <div class="card-header">
        <span><i class="fas fa-chart-area me-1"></i>Progreso de Streamify</span>
        <div class="d-flex gap-1 flex-wrap">
            @foreach(['1d'=>'1D','1w'=>'1S','1m'=>'1M','3m'=>'3M','1y'=>'1A'] as $val=>$label)
            <button class="interval-btn filter-btn {{ $loop->first?'active':'' }}" data-interval="{{ $val }}">{{ $label }}</button>
            @endforeach
        </div>
    </div>

    {{-- Canvas: altura fija, Chart.js responsive llena el ancho --}}
    <div style="position:relative; height:270px; padding:12px 16px 4px;">
        <div id="areaChartLoader" style="position:absolute;top:8px;right:16px;font-size:.72rem;color:#6b7280;display:none;z-index:5;">
            <i class="fas fa-spinner fa-spin me-1"></i> Cargando...
        </div>
        <canvas id="myAreaChart" style="cursor:ew-resize;"></canvas>
    </div>

    {{-- Leyenda estática en el fondo --}}
    <div id="areaChartLegend" class="px-3 pt-2 pb-2 d-flex flex-wrap gap-1"
         style="border-top:1px solid var(--bs-border-color);min-height:36px;"></div>

    <div class="card-footer text-muted" style="font-size:.73rem;">
        <i class="fas fa-mouse me-1"></i> Rueda del ratón o desliza para navegar · carga historial automáticamente
    </div>
</div>

{{-- ══ Bar + Pie ══════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card chart-card shadow-sm h-100">
            <div class="card-header">
                <span><i class="fas fa-chart-bar me-1"></i>Finanzas por servicio este mes</span>
            </div>
            <div class="card-body p-3">
                <canvas id="myBarChart" style="width:100%;height:260px;max-height:260px;"></canvas>
            </div>
            <div class="card-footer text-muted">Ingresos, costos y ganancias por plataforma</div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card chart-card shadow-sm h-100">
            <div class="card-header">
                <span><i class="fas fa-chart-pie me-1"></i>Ganancias por servicio</span>
            </div>
            <div class="card-body p-3 d-flex align-items-center justify-content-center">
                <canvas id="myPieChart" style="width:100%;max-height:260px;"></canvas>
            </div>
            <div class="card-footer text-muted">Distribución de ganancias del mes, de mayor a menor</div>
        </div>
    </div>
</div>

{{-- ══ Pie de usuarios por servicio ═══════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-lg-5 mx-auto">
        <div class="card chart-card shadow-sm h-100">
            <div class="card-header">
                <span><i class="fas fa-users me-1"></i>Usuarios activos por servicio</span>
            </div>
            <div class="card-body p-3 d-flex align-items-center justify-content-center">
                <canvas id="myUsersPieChart" style="width:100%;max-height:260px;"></canvas>
            </div>
            <div class="card-footer text-muted">Cuántos usuarios activos tiene cada plataforma, de mayor a menor</div>
        </div>
    </div>
</div>

{{-- ══ Estado de cuentas por servicio ══════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card chart-card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-tv me-1"></i>Estado de cuentas por servicio</span>
                <a href="{{ route('inteligencia-negocio') }}#cuentas" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-magnifying-glass me-1"></i> Ver detalle y tiempos de reparación
                </a>
            </div>
            <div class="card-body p-3" style="height:280px;">
                <canvas id="cuentasEstadoChart"></canvas>
            </div>
            <div class="card-footer text-muted">Activas, vencidas sin renovar y dañadas — foto de hoy</div>
        </div>
    </div>
</div>

@endsection

@section('pie') Streamify HQ @endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    /* ── Paleta ─────────────────────────────────────────── */
    const C = {
        blue:   { line:'#3b82f6', fill:'rgba(59,130,246,.12)' },
        orange: { line:'#f97316', fill:'rgba(249,115,22,.12)' },
        red:    { line:'#ef4444', fill:'rgba(239,68,68,.12)' },
        green:  { line:'#10b981', fill:'rgba(16,185,129,.12)' },
        yellow: { line:'#f59e0b', fill:'rgba(245,158,11,.12)' },
        pink:   { line:'#ec4899', fill:'rgba(236,72,153,.12)' },
        purple: { line:'#8b5cf6', fill:'rgba(139,92,246,.12)' },
        brown:  { line:'#78716c', fill:'rgba(120,113,108,.12)' },
        teal:   { line:'#14b8a6', fill:'rgba(20,184,166,.12)' },
        indigo: { line:'#6366f1', fill:'rgba(99,102,241,.12)' },
        rose:   { line:'#e11d48', fill:'rgba(225,29,72,.12)' },
    };

    const gridColor = 'rgba(150,150,150,.1)';
    const money = v => '$' + parseFloat(v||0).toLocaleString('es-EC',{minimumFractionDigits:2,maximumFractionDigits:2});

    Chart.defaults.font.family = "'Inter','Segoe UI',system-ui,sans-serif";
    Chart.defaults.font.size   = 12;

    /* ── Área Chart (ventana deslizante, como bolsas de valores) ──── */
    const ctxArea  = document.getElementById('myAreaChart').getContext('2d');
    const canvasEl = document.getElementById('myAreaChart');
    const loaderEl = document.getElementById('areaChartLoader');
    const legendEl = document.getElementById('areaChartLegend');
    let myAreaChart;

    function dataset(label, c, data, hidden = false, fill = true) {
        return {
            label, data, hidden, fill, tension: 0.4,
            backgroundColor: fill ? c.fill : 'transparent',
            borderColor: c.line, borderWidth: 2,
            pointBackgroundColor: c.line,
            pointRadius: 2, pointHoverRadius: 5,
        };
    }

    // Cuántos puntos mostrar por intervalo (ventana visible)
    const WINDOW_SIZES = { '1d': 30, '1w': 20, '1m': 15, '3m': 10, '1y': 8 };
    const DS_KEYS = [
        'ingresos', 'costos', 'gastos', 'ganancias',
        'ventasChart', 'newCustomers', 'users', 'accounts',
        'dangerAccounts', 'pendingPayments', 'affectedCustomers', 'clientesPerdidos'
    ];

    let activeInterval = '1d';
    let allData        = { labels: [] };   // dataset completo en memoria
    let windowStart    = 0;                // índice del primer punto visible
    let hasMore        = false;
    let loadingOlder   = false;
    let oldestDate     = null;

    const wSize    = () => WINDOW_SIZES[activeInterval] || 30;
    const totalPts = () => (allData.labels || []).length;

    /* ── Leyenda estática (fondo) ────────────────────────── */
    function buildLegend() {
        legendEl.innerHTML = '';
        myAreaChart.data.datasets.forEach(ds => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.style.cssText = 'border:none;background:none;padding:2px 8px;cursor:pointer;font-size:.72rem;font-weight:600;border-radius:4px;transition:opacity .15s;';
            btn.innerHTML = `<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:${ds.borderColor};margin-right:4px;"></span>${ds.label}`;
            btn.style.opacity = ds.hidden ? '0.35' : '1';
            btn.addEventListener('click', () => {
                ds.hidden = !ds.hidden;
                btn.style.opacity = ds.hidden ? '0.35' : '1';
                myAreaChart.update('none');
            });
            legendEl.appendChild(btn);
        });
    }

    /* ── Actualizar la ventana visible ───────────────────── */
    function updateWindow() {
        if (!myAreaChart) return;
        const ws    = wSize();
        const total = totalPts();
        const start = Math.max(0, Math.min(windowStart, Math.max(0, total - ws)));
        const end   = Math.min(start + ws, total);
        myAreaChart.data.labels = allData.labels.slice(start, end);
        DS_KEYS.forEach((k, i) => {
            myAreaChart.data.datasets[i].data = (allData[k] || []).slice(start, end);
        });
        myAreaChart.update('none');
        // Cerca del borde izquierdo (datos más antiguos) → carga más
        if (start < 5 && hasMore && !loadingOlder) fetchOlderChunk();
    }

    /* ── Desplazar ventana (delta>0 = más reciente, <0 = más antiguo) ── */
    function shiftWindow(delta) {
        const maxStart = Math.max(0, totalPts() - wSize());
        windowStart = Math.max(0, Math.min(windowStart + delta, maxStart));
        updateWindow();
    }

    /* ── Cargar chunk histórico más antiguo ──────────────── */
    function fetchOlderChunk() {
        loadingOlder = true;
        loaderEl.style.display = '';
        fetch(`{{ route('dashboard.filter') }}?range=${activeInterval}&before=${oldestDate || ''}`)
            .then(r => r.json())
            .then(d => {
                if (!d.labels || !d.labels.length) { hasMore = false; return; }
                const added = d.labels.length;
                allData.labels = [...d.labels, ...allData.labels];
                DS_KEYS.forEach(k => { allData[k] = [...(d[k] || []), ...(allData[k] || [])]; });
                hasMore      = d.has_more;
                oldestDate   = d.oldest_date || oldestDate;
                windowStart += added; // compensar para que la vista no salte
            })
            .catch(e => console.error(e))
            .finally(() => { loadingOlder = false; loaderEl.style.display = 'none'; });
    }

    /* ── Crear el chart ──────────────────────────────────── */
    function initChart(interval) {
        if (myAreaChart) { myAreaChart.destroy(); myAreaChart = null; }

        const ws    = WINDOW_SIZES[interval] || 30;
        const start = Math.max(0, totalPts() - ws);
        const sl    = k => (allData[k] || []).slice(start);

        myAreaChart = new Chart(ctxArea, {
            type: 'line',
            data: {
                labels: allData.labels.slice(start),
                datasets: [
                    dataset('Ingresos',           C.blue,   sl('ingresos')),
                    dataset('Costos',             C.orange, sl('costos')),
                    dataset('Gastos',             C.red,    sl('gastos')),
                    dataset('Ganancias',          C.green,  sl('ganancias'),         true),
                    dataset('Ventas',             C.yellow, sl('ventasChart'),       true, false),
                    dataset('Clientes nuevos',    C.pink,   sl('newCustomers'),      true, false),
                    dataset('Suscripciones',      C.purple, sl('users'),             true, false),
                    dataset('Cuentas',            C.brown,  sl('accounts'),          true, false),
                    dataset('Cuentas riesgo',     C.teal,   sl('dangerAccounts'),    true, false),
                    dataset('Pagos pendientes',   C.indigo, sl('pendingPayments'),   true, false),
                    dataset('Clientes afectados', C.orange, sl('affectedCustomers'), true, false),
                    dataset('Clientes perdidos',  C.rose,   sl('clientesPerdidos'),  true, false),
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 200 },
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { maxRotation: 45 } },
                    y: { beginAtZero: true, grid: { color: gridColor },
                         ticks: { callback: v => '$' + v.toLocaleString() } },
                },
                plugins: {
                    legend:  { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${money(ctx.parsed.y)}` } },
                },
            },
        });

        buildLegend();
    }

    /* ── Rueda del ratón / touchpad ──────────────────────── */
    canvasEl.addEventListener('wheel', e => {
        e.preventDefault();
        const raw = Math.abs(e.deltaX) > Math.abs(e.deltaY) ? e.deltaX : e.deltaY;
        shiftWindow(Math.sign(raw) * 3);
    }, { passive: false });

    /* ── Deslizamiento táctil ────────────────────────────── */
    let touchX = 0;
    canvasEl.addEventListener('touchstart', e => { touchX = e.touches[0].clientX; }, { passive: true });
    canvasEl.addEventListener('touchmove', e => {
        const dx = touchX - e.touches[0].clientX; // >0 = deslizó a la izquierda = más antiguo
        if (Math.abs(dx) > 10) {
            shiftWindow(-Math.sign(dx));
            touchX = e.touches[0].clientX;
        }
    }, { passive: true });

    /* ── Cargar intervalo completo ───────────────────────── */
    function loadChartData(interval) {
        activeInterval = interval;
        oldestDate     = null;
        hasMore        = false;
        loadingOlder   = false;
        allData        = { labels: [] };
        DS_KEYS.forEach(k => (allData[k] = []));

        fetch(`{{ route('dashboard.filter') }}?range=${interval}`)
            .then(r => r.json())
            .then(d => {
                allData.labels = d.labels || [];
                DS_KEYS.forEach(k => (allData[k] = d[k] || []));
                hasMore     = d.has_more;
                oldestDate  = d.oldest_date;
                windowStart = Math.max(0, allData.labels.length - (WINDOW_SIZES[interval] || 30));
                initChart(interval);
            })
            .catch(e => console.error(e));
    }

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            loadChartData(this.dataset.interval);
        });
    });

    loadChartData('1d');

    /* ── Bar Chart ──────────────────────────────────────── */
    const servicios = ['Netflix','Disney','Prime','Max','Magis','Crunchy','Paramount','Spotify','Otros'];
    const ing = [@json($ingresos_netflix),@json($ingresos_disney),@json($ingresos_prime),@json($ingresos_max),@json($ingresos_magis),@json($ingresos_crunchy),@json($ingresos_paramount),@json($ingresos_spotify),@json($ingresos_otros)];
    const cos = [@json($costos_netflix),@json($costos_disney),@json($costos_prime),@json($costos_max),@json($costos_magis),@json($costos_crunchy),@json($costos_paramount),@json($costos_spotify),@json($costos_otros)];
    const gan = ing.map((v,i) => v - cos[i]);

    new Chart(document.getElementById('myBarChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: servicios,
            datasets: [
                { label:'Ingresos', data:ing, backgroundColor:'rgba(59,130,246,.75)', borderColor:'#3b82f6', borderWidth:1, borderRadius:4 },
                { label:'Costos',   data:cos, backgroundColor:'rgba(249,115,22,.75)', borderColor:'#f97316', borderWidth:1, borderRadius:4 },
                { label:'Ganancias',data:gan, backgroundColor:'rgba(16,185,129,.75)', borderColor:'#10b981', borderWidth:1, borderRadius:4 },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode:'index', intersect:false },
            scales: {
                x: { grid:{ color:gridColor } },
                y: { beginAtZero:true, grid:{ color:gridColor },
                     ticks:{ callback: v => '$'+v } },
            },
            plugins: {
                legend: { position:'top', labels:{ usePointStyle:true, boxWidth:8 } },
                tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${money(ctx.parsed.y)}` } },
            },
        },
    });

    /* ── Pie Chart (helper: ordena labels/valores de mayor a menor) ──
       Color fijo por servicio (no por posición), para que el color de
       cada plataforma no cambie aunque cambie su lugar en el ranking. ── */
    const SERVICIO_COLORS = {
        Netflix:    '#ef4444', // rojo
        Disney:     '#2563eb', // azul
        Prime:      '#38bdf8', // celeste
        Max:        '#7c3aed', // morado
        Magis:      '#f97316', // naranja
        Crunchy:    '#eab308', // amarillo
        Paramount:  '#0891b2', // entre azul y celeste
        Spotify:    '#1db954', // verde
        Otros:      '#94a3b8', // gris, fuera de los 8 principales
    };

    function sortedPieData(labels, values) {
        const rows = labels.map((label, i) => ({ label, value: values[i] || 0 }))
            .sort((a, b) => b.value - a.value);
        return {
            labels: rows.map(r => r.label),
            values: rows.map(r => r.value),
            colors: rows.map(r => SERVICIO_COLORS[r.label] || '#94a3b8'),
        };
    }

    function buildPieChart(canvasId, labels, values, tooltipFormatter) {
        const sorted = sortedPieData(labels, values);
        new Chart(document.getElementById(canvasId).getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: sorted.labels,
                datasets: [{ data: sorted.values, backgroundColor: sorted.colors, borderColor: '#fff', borderWidth: 2 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 8, padding: 10, font: { size: 11 } } },
                    tooltip: { callbacks: { label: ctx => tooltipFormatter(ctx.label, ctx.parsed) } },
                },
            },
        });
    }

    buildPieChart('myPieChart', servicios, gan, (label, value) => ` ${label}: ${money(value)}`);

    const usu = [@json($usuarios_netflix),@json($usuarios_disney),@json($usuarios_prime),@json($usuarios_max),@json($usuarios_magis),@json($usuarios_crunchy),@json($usuarios_paramount),@json($usuarios_spotify),@json($usuarios_otros)];
    buildPieChart('myUsersPieChart', servicios, usu, (label, value) => ` ${label}: ${value} usuarios`);

    /* ── Estado de cuentas por servicio (barras apiladas) ── */
    const cuentasEstadoResumen = @json($cuentasEstadoResumen);
    new Chart(document.getElementById('cuentasEstadoChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: cuentasEstadoResumen.map(s => s.nombre),
            datasets: [
                { label: 'Activas', data: cuentasEstadoResumen.map(s => s.activas), backgroundColor: '#10b981', borderRadius: 3 },
                { label: 'Vencidas sin renovar', data: cuentasEstadoResumen.map(s => s.vencidas), backgroundColor: '#cbd5e1', borderRadius: 3 },
                { label: 'Dañadas', data: cuentasEstadoResumen.map(s => s.danadas), backgroundColor: '#ef4444', borderRadius: 3 },
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
});
</script>

{{-- Enhanced Table --}}
<script src="{{ asset('js/enhanced-table-v2.js') }}?v={{ filemtime(public_path('js/enhanced-table-v2.js')) }}"></script>

<script>
    function abrirModalReportes() {
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'reportesDashboardModal' }));
    }

    function captureCharts() {
        const charts = {};
        ['myAreaChart','myBarChart','myPieChart','myUsersPieChart'].forEach(id => {
            const canvas = document.getElementById(id);
            if (canvas) {
                try { charts[id] = canvas.toDataURL('image/png', 0.85); } catch(e) {}
            }
        });
        return charts;
    }

    function generarReporteMensual() {
        const mes = document.getElementById('mes_reporte').value;
        const ano = document.getElementById('ano_reporte').value;
        if (!mes || !ano) { alert('Por favor selecciona mes y año'); return; }

        const charts = captureCharts();
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('dashboard.pdf') }}";
        form.target = '_blank';

        const fields = { _token: '{{ csrf_token() }}', tipo: 'mensual', mes, ano,
            chart_area: charts['myAreaChart'] || '',
            chart_bar:  charts['myBarChart']  || '',
            chart_pie:  charts['myPieChart']  || '' };

        Object.entries(fields).forEach(([k,v]) => {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = k; inp.value = v;
            form.appendChild(inp);
        });

        document.body.appendChild(form);
        form.submit();
        setTimeout(() => form.remove(), 1000);
    }

    function generarReporteAnual() {
        const ano = document.getElementById('ano_reporte_anual').value;
        if (!ano) { alert('Por favor selecciona el año'); return; }
        window.open(`{{ route('dashboard.pdf') }}?tipo=anual&ano=${ano}`, '_blank');
    }
</script>
@endsection

<!-- Modal para Generar Reportes -->
<x-modal name="reportesDashboardModal" :show="false" maxWidth="lg">
    <div class="modal-header modal-header-blue">
        <h5 class="modal-title fw-bold">
            <i class="fas fa-file-pdf me-2"></i> Generar Reporte Financiero
        </h5>
        <button type="button" class="btn-close btn-close-white"
                onclick="window.dispatchEvent(new CustomEvent('close-modal',{detail:'reportesDashboardModal'}))"></button>
    </div>
    <div class="modal-body p-0">

        {{-- Tabs inside modal --}}
        <ul class="nav nav-pills p-3 pb-0 gap-2" id="reporteTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active px-4" id="rep-mensual-tab"
                        data-bs-toggle="pill" data-bs-target="#rep-mensual" type="button">
                    <i class="fas fa-calendar-alt me-1"></i> Mensual
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4" id="rep-anual-tab"
                        data-bs-toggle="pill" data-bs-target="#rep-anual" type="button">
                    <i class="fas fa-calendar me-1"></i> Anual
                </button>
            </li>
        </ul>
        <hr class="mt-2 mb-0">

        <div class="tab-content p-3">
            {{-- Mensual --}}
            <div class="tab-pane fade show active" id="rep-mensual" role="tabpanel">
                <p class="text-muted small mb-3">
                    <i class="fas fa-chart-bar me-1 text-primary"></i>
                    El reporte incluirá KPIs, resumen financiero, tabla de resultados por servicio
                    y <strong>los gráficos del dashboard</strong> (área, barras y circular) capturados en el momento actual.
                </p>
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label for="mes_reporte" class="form-label fw-semibold small">
                            <i class="fas fa-calendar-day text-primary me-1"></i> Mes
                        </label>
                        <select id="mes_reporte" class="form-select">
                            @foreach(['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] as $i=>$m)
                            <option value="{{ $i+1 }}" {{ now()->subMonth()->month == $i+1 ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label for="ano_reporte" class="form-label fw-semibold small">
                            <i class="fas fa-calendar-alt text-primary me-1"></i> Año
                        </label>
                        <select id="ano_reporte" class="form-select">
                            @for($i=now()->year;$i>=2020;$i--)
                            <option value="{{ $i }}" {{ $i==now()->year?'selected':'' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- Preview info --}}
                <div class="d-flex gap-2 mb-3 flex-wrap">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2" style="font-size:.75rem;">
                        <i class="fas fa-chart-area me-1"></i> Gráfico de progreso incluido
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2" style="font-size:.75rem;">
                        <i class="fas fa-chart-bar me-1"></i> Finanzas por servicio incluido
                    </span>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2" style="font-size:.75rem;">
                        <i class="fas fa-chart-pie me-1"></i> Ganancias por servicio incluido
                    </span>
                </div>

                <button type="button" class="btn btn-primary w-100 py-2" onclick="generarReporteMensual()">
                    <i class="fas fa-file-pdf me-2"></i> Descargar Reporte Mensual (PDF)
                </button>
                <p class="text-muted text-center mt-2 mb-0" style="font-size:.72rem;">
                    Los gráficos se capturan del dashboard en tiempo real al generar el PDF.
                </p>
            </div>

            {{-- Anual --}}
            <div class="tab-pane fade" id="rep-anual" role="tabpanel">
                <p class="text-muted small mb-3">
                    <i class="fas fa-chart-line me-1 text-success"></i>
                    Reporte consolidado de los 12 meses del año seleccionado con resumen de KPIs e ingresos anuales.
                </p>
                <div class="mb-3">
                    <label for="ano_reporte_anual" class="form-label fw-semibold small">
                        <i class="fas fa-calendar-alt text-success me-1"></i> Año
                    </label>
                    <select id="ano_reporte_anual" class="form-select">
                        @for($i=now()->year;$i>=2020;$i--)
                        <option value="{{ $i }}" {{ $i==now()->year?'selected':'' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <button type="button" class="btn btn-success w-100 py-2" onclick="generarReporteAnual()">
                    <i class="fas fa-file-pdf me-2"></i> Descargar Reporte Anual (PDF)
                </button>
            </div>
        </div>

    </div>
    <div class="modal-footer justify-content-start py-2">
        <small class="text-muted">
            <i class="fas fa-info-circle me-1"></i>
            El mes anterior se selecciona por defecto para obtener datos históricos completos.
        </small>
    </div>
</x-modal>
