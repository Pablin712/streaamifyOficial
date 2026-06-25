@extends('layouts.static')
@section('title', 'Rendimiento de Empleados')
@section('h1', 'Rendimiento')
@section('breadcrumb')
    <a href="{{ route('inicio') }}">Inicio</a>
@endsection
@section('breadcrumb2', 'Rendimiento')
@section('introduccion')
    Productividad basada en tareas completadas. Compara empleados, puntos acumulados y niveles de desempeño por período.
@endsection

@section('styles')
<style>
.nivel-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    white-space: nowrap;
}
.emp-avatar {
    width: 54px; height: 54px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.emp-initials {
    width: 54px; height: 54px;
    border-radius: 50%;
    background: var(--bs-secondary-bg);
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 1.1rem;
    color: var(--bs-secondary-color);
    flex-shrink: 0;
}
.points-big {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1;
}
.tipo-row {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-bottom: 5px;
    font-size: .77rem;
}
.tipo-dot {
    width: 9px; height: 9px;
    border-radius: 50%;
    flex-shrink: 0;
}
.tipo-bar-track {
    flex: 2;
    background: var(--bs-secondary-bg);
    border-radius: 4px;
    overflow: hidden;
    height: 6px;
}
.tipo-bar-fill {
    height: 6px;
    border-radius: 4px;
    transition: width .4s;
}
.tipo-label { flex: 1; }
.tipo-count { font-weight: 700; min-width: 20px; text-align: right; }
.tipo-pts { color: var(--bs-secondary-color); font-size: .68rem; min-width: 36px; text-align: right; }
.stat-mini { text-align: center; flex: 1; }
.stat-mini .val { font-size: 1.15rem; font-weight: 800; }
.stat-mini .lbl { font-size: .67rem; color: var(--bs-secondary-color); }
.rank-medal { position: absolute; bottom: -2px; right: -4px; font-size: .85rem; }
</style>
@endsection

@section('content')

{{-- Period filter --}}
<div class="d-flex gap-2 mb-4 flex-wrap align-items-center">
    <span class="text-muted small me-1 fw-semibold">Período:</span>
    <a href="?periodo=hoy"    class="btn btn-sm {{ $periodo === 'hoy'    ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">Hoy</a>
    <a href="?periodo=semana" class="btn btn-sm {{ $periodo === 'semana' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">Esta semana</a>
    <a href="?periodo=mes"    class="btn btn-sm {{ $periodo === 'mes'    ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">Este mes</a>
    <span class="text-muted small ms-2">
        Desde <strong>{{ $desde->locale('es')->translatedFormat('d \d\e F') }}</strong>
        · Promedio sobre <strong>{{ $dias }} {{ $dias === 1 ? 'día' : 'días' }}</strong>
    </span>
</div>

{{-- Global comparison chart --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 p-3 p-md-4">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <h6 class="fw-bold mb-0">
            <i class="bi bi-bar-chart-fill me-2 text-primary"></i>Comparación de empleados — Puntos totales
        </h6>
        <span class="badge bg-secondary-subtle text-secondary">
            {{ $periodo === 'hoy' ? 'Hoy' : ($periodo === 'mes' ? 'Este mes' : 'Esta semana') }}
        </span>
    </div>
    @if($data->isEmpty())
        <p class="text-muted small mb-0">Sin datos.</p>
    @else
        <canvas id="chartComparacion" style="max-height:200px;"></canvas>
    @endif
</div>

{{-- Employee cards --}}
@if($data->isEmpty())
    <div class="text-center text-muted py-5">
        <i class="bi bi-people fs-1 d-block mb-3 opacity-40"></i>
        No hay empleados activos con roles asignados.
    </div>
@else
<div class="row g-3 mb-4">
    @foreach($data as $i => $emp)
    @php
        $maxTipoCount = max(1, max(array_values($emp['por_tipo'])));
    @endphp
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 h-100"
             style="{{ $emp['sospechosas'] > 0 ? 'box-shadow: 0 0 0 2px #fbbf24 !important;' : '' }}">
            <div class="card-body p-3 d-flex flex-column gap-0">

                {{-- Header: avatar + name + points --}}
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="position-relative">
                        @if($emp['foto'])
                            <img src="{{ asset($emp['foto']) }}" class="emp-avatar" alt="">
                        @else
                            <div class="emp-initials">{{ strtoupper(mb_substr($emp['nombre'], 0, 2)) }}</div>
                        @endif
                        @if($i < 3)
                            <span class="rank-medal">{{ ['🥇','🥈','🥉'][$i] }}</span>
                        @endif
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-bold text-truncate" title="{{ $emp['nombre'] }}">{{ $emp['nombre'] }}</div>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach($emp['roles'] as $rol)
                                <span class="badge bg-secondary-subtle text-secondary" style="font-size:.62rem;">{{ $rol }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="points-big" style="color:{{ $emp['nivel']['bg'] }};">{{ $emp['puntos'] }}</div>
                        <div style="font-size:.65rem; color:var(--bs-secondary-color);">pts totales</div>
                    </div>
                </div>

                {{-- Performance badge + daily average --}}
                <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                    <span class="nivel-badge" style="background:{{ $emp['nivel']['bg'] }};color:{{ $emp['nivel']['text'] }};">
                        {{ $emp['nivel']['label'] }}
                    </span>
                    <span class="text-muted small">≈ {{ $emp['prom_diario'] }} pts/día</span>
                    @if($emp['sospechosas'] > 0)
                        <span class="badge bg-warning text-dark ms-auto"
                              title="{{ $emp['sospechosas'] }} {{ $emp['sospechosas'] === 1 ? 'tarea completada' : 'tareas completadas' }} en menos de 60 segundos — requiere revisión">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $emp['sospechosas'] }} revisar
                        </span>
                    @endif
                </div>

                <hr class="my-0 mb-3">

                {{-- Task type breakdown --}}
                <div class="mb-3 flex-grow-1">
                    @php $alguna = false; @endphp
                    @foreach($tiposConfig as $tipo => $cfg)
                        @if($emp['por_tipo'][$tipo] > 0)
                            @php $alguna = true; $pts = $puntosPorTipo[$tipo] * $emp['por_tipo'][$tipo]; @endphp
                            <div class="tipo-row">
                                <span class="tipo-dot" style="background:{{ $cfg['color'] }};"></span>
                                <span class="tipo-label">{{ $cfg['label'] }}</span>
                                <div class="tipo-bar-track">
                                    <div class="tipo-bar-fill" style="width:{{ round($emp['por_tipo'][$tipo] / $maxTipoCount * 100) }}%; background:{{ $cfg['color'] }};"></div>
                                </div>
                                <span class="tipo-count">{{ $emp['por_tipo'][$tipo] }}</span>
                                <span class="tipo-pts">+{{ $pts }}pt</span>
                            </div>
                        @endif
                    @endforeach
                    @if(!$alguna)
                        <div class="text-muted small text-center py-3">Sin tareas completadas en este período</div>
                    @endif
                </div>

                <hr class="my-0 mb-2">

                {{-- Footer stats --}}
                <div class="d-flex">
                    <div class="stat-mini">
                        <div class="val">{{ $emp['total_tareas'] }}</div>
                        <div class="lbl">Tareas</div>
                    </div>
                    <div class="stat-mini">
                        <div class="val">{{ $emp['ventas'] }}</div>
                        <div class="lbl">Ventas</div>
                    </div>
                    <div class="stat-mini">
                        <div class="val" style="color:{{ $emp['nivel']['bg'] }};">{{ $emp['prom_diario'] }}</div>
                        <div class="lbl">Pts/día</div>
                    </div>
                    @if($emp['sospechosas'] > 0)
                    <div class="stat-mini">
                        <div class="val" style="color:#f59e0b;">{{ $emp['sospechosas'] }}</div>
                        <div class="lbl">⚠ Revisar</div>
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Reference tables --}}
<div class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-muted"></i>Puntos por tarea y niveles diarios</h6>
    <div class="row g-4">
        <div class="col-md-6">
            <table class="table table-sm table-borderless mb-0">
                <thead>
                    <tr class="text-muted" style="font-size:.75rem; text-transform:uppercase; letter-spacing:.05em;">
                        <th>Tipo de tarea</th>
                        <th class="text-end">Puntos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tiposConfig as $tipo => $cfg)
                    <tr>
                        <td class="d-flex align-items-center gap-2">
                            <span class="tipo-dot" style="background:{{ $cfg['color'] }};"></span>
                            {{ $cfg['label'] }}
                        </td>
                        <td class="text-end fw-bold">{{ $puntosPorTipo[$tipo] }} pt{{ $puntosPorTipo[$tipo] > 1 ? 's' : '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-sm table-borderless mb-0">
                <thead>
                    <tr class="text-muted" style="font-size:.75rem; text-transform:uppercase; letter-spacing:.05em;">
                        <th>Nivel de desempeño</th>
                        <th class="text-end">Pts/día</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($niveles as $n)
                    <tr>
                        <td>
                            <span class="nivel-badge" style="background:{{ $n['bg'] }};color:{{ $n['text'] }};">{{ $n['label'] }}</span>
                        </td>
                        <td class="text-end text-muted small">
                            {{ $n['min'] }}{{ isset($niveles[array_search($n, $niveles) + 1]) ? '–'.($niveles[array_search($n, $niveles) + 1]['min'] - 1) : '+' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="text-muted small mt-3 mb-0">
                <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                <strong>Tareas a revisar:</strong> completadas en menos de 60 segundos desde que fueron asignadas. Pueden indicar ejecución sin verificar el trabajo real.
            </p>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const empData = @json($data);
    if (!empData.length) return;

    const TIPO_COLORS = @json(collect($tiposConfig)->map(fn($v) => $v['color']));

    // ── Horizontal comparison bar chart ──────────────────────────────────
    const ctx = document.getElementById('chartComparacion').getContext('2d');
    const palette = ['#f43f5e','#8b5cf6','#10b981','#3b82f6','#f59e0b','#06b6d4','#64748b','#ec4899','#84cc16','#fb923c'];

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: empData.map(e => e.nombre),
            datasets: [{
                label: 'Puntos totales',
                data: empData.map(e => e.puntos),
                backgroundColor: empData.map((_, i) => palette[i % palette.length] + 'cc'),
                borderColor:     empData.map((_, i) => palette[i % palette.length]),
                borderWidth: 1,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: c => ` ${c.parsed.x} puntos — ${empData[c.dataIndex].total_tareas} tareas`
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(128,128,128,.1)' },
                    ticks: { font: { size: 11 } },
                    title: { display: true, text: 'Puntos', font: { size: 11 } }
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { size: 12, weight: '600' } }
                }
            }
        }
    });
})();
</script>
@endsection
