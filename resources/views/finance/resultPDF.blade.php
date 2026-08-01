<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Mensual — {{ $mes }} {{ $year }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size:11px; color:#1e293b; background:#fff; }

        /* ── Header ─────────────────────────────────── */
        .pdf-header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            padding: 22px 28px;
            border-radius: 10px;
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .pdf-header h1 { font-size:24px; font-weight:bold; letter-spacing:-.5px; margin-bottom:2px; }
        .pdf-header .period { font-size:14px; opacity:.85; }
        .pdf-header .company { font-size:11px; opacity:.65; margin-top:4px; }
        .pdf-header .balance-box { text-align:right; }
        .pdf-header .balance-label { font-size:10px; opacity:.7; text-transform:uppercase; letter-spacing:.05em; }
        .pdf-header .balance-val { font-size:28px; font-weight:bold; }

        /* ── KPI grid ────────────────────────────────── */
        .kpi-grid { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:18px; }
        .kpi-box {
            flex: 1 1 160px;
            border-radius:8px;
            padding:10px 14px;
            border-left:4px solid;
        }
        .kpi-box.blue   { background:#eff6ff; border-color:#3b82f6; }
        .kpi-box.green  { background:#f0fdf4; border-color:#22c55e; }
        .kpi-box.red    { background:#fef2f2; border-color:#ef4444; }
        .kpi-box.purple { background:#faf5ff; border-color:#a855f7; }
        .kpi-box.orange { background:#fffbeb; border-color:#f59e0b; }
        .kpi-box.teal   { background:#f0fdfa; border-color:#14b8a6; }
        .kpi-box.indigo { background:#eef2ff; border-color:#6366f1; }
        .kpi-box.yellow { background:#fefce8; border-color:#eab308; }

        .kpi-label { font-size:8px; font-weight:bold; text-transform:uppercase; letter-spacing:.05em; color:#64748b; margin-bottom:3px; }
        .kpi-value { font-size:16px; font-weight:bold; }
        .kpi-box.blue   .kpi-value { color:#1d4ed8; }
        .kpi-box.green  .kpi-value { color:#166534; }
        .kpi-box.red    .kpi-value { color:#991b1b; }
        .kpi-box.purple .kpi-value { color:#7e22ce; }
        .kpi-box.orange .kpi-value { color:#92400e; }
        .kpi-box.teal   .kpi-value { color:#0f766e; }
        .kpi-box.indigo .kpi-value { color:#3730a3; }
        .kpi-box.yellow .kpi-value { color:#713f12; }

        /* ── Section title ───────────────────────────── */
        .section-title {
            font-size:10px; font-weight:bold; text-transform:uppercase;
            letter-spacing:.07em; color:#3b82f6; margin:16px 0 8px;
            border-bottom: 2px solid #dbeafe; padding-bottom:4px;
        }

        /* ── Tables ──────────────────────────────────── */
        table { width:100%; border-collapse:collapse; margin-bottom:12px; }
        thead tr { background:#1e3a8a; color:white; }
        thead th { padding:8px 8px; font-size:9px; font-weight:bold; text-transform:uppercase; letter-spacing:.04em; text-align:left; }
        tbody tr:nth-child(even) { background:#eff6ff; }
        tbody td { padding:6px 8px; border-bottom:1px solid #e2e8f0; font-size:10px; }
        tfoot tr { background:#1e3a8a; color:white; font-weight:bold; }
        tfoot td { padding:7px 8px; font-size:10px; }
        .text-right { text-align:right; }
        .text-center { text-align:center; }

        /* ── Finance summary ─────────────────────────── */
        .finance-cols { display:flex; gap:16px; margin-bottom:12px; }
        .finance-col  { flex:1; }

        /* ── Charts ──────────────────────────────────── */
        .chart-section { margin:16px 0; page-break-inside:avoid; }
        .chart-card {
            border:1px solid #e2e8f0; border-radius:8px;
            overflow:hidden; margin-bottom:12px;
        }
        .chart-card-header {
            background:#f8fafc; padding:8px 14px;
            font-size:9px; font-weight:bold; text-transform:uppercase;
            letter-spacing:.06em; color:#475569;
            border-bottom:1px solid #e2e8f0;
        }
        .chart-card-body { padding:10px; text-align:center; }
        .chart-card img { max-width:100%; height:auto; border-radius:4px; }
        .chart-row { display:flex; gap:12px; }
        .chart-col-7 { flex:0 0 60%; }
        .chart-col-5 { flex:0 0 38%; }

        /* ── Footer ──────────────────────────────────── */
        .pdf-footer {
            margin-top:20px; text-align:center;
            font-size:8px; color:#94a3b8;
            border-top:1px solid #e2e8f0; padding-top:8px;
        }

        /* ── Helpers ─────────────────────────────────── */
        .positive { color:#166534; font-weight:bold; }
        .negative { color:#991b1b; font-weight:bold; }
        .badge-ingreso { background:#dcfce7; color:#166534; padding:2px 6px; border-radius:8px; font-size:8.5px; font-weight:bold; }
        .badge-egreso  { background:#fee2e2; color:#991b1b; padding:2px 6px; border-radius:8px; font-size:8.5px; font-weight:bold; }
    </style>
</head>
<body>

    {{-- ── Header ─────────────────────────────────────── --}}
    <div class="pdf-header">
        <div>
            <h1>REPORTE MENSUAL</h1>
            <div class="period">{{ ucfirst($mes) }} {{ $year }}</div>
            <div class="company">Streamify HQ — Generado el {{ now()->format('d/m/Y H:i') }}</div>
        </div>
        <div class="balance-box">
            <div class="balance-label">Utilidad real del mes</div>
            <div class="balance-val" style="color:{{ $balance >= 0 ? '#bbf7d0' : '#fecaca' }}">
                ${{ number_format($balance, 2) }}
            </div>
        </div>
    </div>

    {{-- ── KPI Grid: Ingresos & Ventas ─────────────────── --}}
    <div class="section-title">Ingresos y Ventas</div>
    <div class="kpi-grid">
        <div class="kpi-box blue">
            <div class="kpi-label">Ingresos del mes</div>
            <div class="kpi-value">${{ number_format($ingresos_mes, 2) }}</div>
        </div>
        <div class="kpi-box green">
            <div class="kpi-label">Ingresos del año</div>
            <div class="kpi-value">${{ number_format($ingresos_ano, 2) }}</div>
        </div>
        <div class="kpi-box orange">
            <div class="kpi-label">Ventas este mes</div>
            <div class="kpi-value">{{ $ventas_mes }}</div>
        </div>
        <div class="kpi-box yellow">
            <div class="kpi-label">Ventas este año</div>
            <div class="kpi-value">{{ $ventas_ano }}</div>
        </div>
    </div>

    {{-- ── KPI Grid: Operaciones ───────────────────────── --}}
    <div class="section-title">Operaciones y Clientes</div>
    <div class="kpi-grid">
        <div class="kpi-box indigo">
            <div class="kpi-label">Cuentas activas</div>
            <div class="kpi-value">{{ $num_cuentas }}</div>
        </div>
        <div class="kpi-box teal">
            <div class="kpi-label">Espacios disponibles</div>
            <div class="kpi-value">{{ $espacios }}</div>
        </div>
        <div class="kpi-box orange">
            <div class="kpi-label">Pagos pendientes</div>
            <div class="kpi-value">{{ $usuarios_acobrar }}</div>
        </div>
        <div class="kpi-box red">
            <div class="kpi-label">Cuentas caídas</div>
            <div class="kpi-value">{{ $cuentas_caidas }}</div>
        </div>
        <div class="kpi-box blue">
            <div class="kpi-label">Clientes activos</div>
            <div class="kpi-value">{{ $clientes_activos }}</div>
        </div>
        <div class="kpi-box green">
            <div class="kpi-label">Usuarios activos</div>
            <div class="kpi-value">{{ $total_usuarios_activos }}</div>
        </div>
        <div class="kpi-box purple">
            <div class="kpi-label">Media pago/cliente</div>
            <div class="kpi-value">${{ number_format($promedio_pagos_mes, 2) }}</div>
        </div>
    </div>

    {{-- ── Resumen Financiero ──────────────────────────── --}}
    <div class="section-title">Resumen Financiero</div>
    <div class="finance-cols">
        <div class="finance-col">
            <table>
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th class="text-right">Monto</th>
                        <th class="text-right">%</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Ingresos</strong></td>
                        <td class="text-right positive">${{ number_format($ingresos_mes, 2) }}</td>
                        <td class="text-right">100%</td>
                    </tr>
                    <tr>
                        <td>Costos</td>
                        <td class="text-right">${{ number_format($costos_mes, 2) }}</td>
                        <td class="text-right">{{ number_format($costos_pct, 1) }}%</td>
                    </tr>
                    <tr>
                        <td>Gastos operativos</td>
                        <td class="text-right">${{ number_format($gastos_mes, 2) }}</td>
                        <td class="text-right">{{ number_format($gastos_pct, 1) }}%</td>
                    </tr>
                    <tr style="background:#1e3a8a;color:white;">
                        <td><strong>Utilidad real</strong></td>
                        <td class="text-right"><strong>${{ number_format($balance, 2) }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($balance_pct, 1) }}%</strong></td>
                    </tr>
                    <tr>
                        <td>Retiro / personal (informativo)</td>
                        <td class="text-right">${{ number_format($gastos_personal_mes ?? 0, 2) }}</td>
                        <td class="text-right">—</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="finance-col">
            <table>
                <thead>
                    <tr>
                        <th>Gasto</th>
                        <th class="text-right">Monto</th>
                        <th class="text-right">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gastos as $g)
                    <tr>
                        <td>{{ $g['concepto'] }}@if($g['excluido_de_ganancia'] ?? false) <span style="color:#64748b;font-size:8px;">(informativo)</span>@endif</td>
                        <td class="text-right">${{ number_format($g['total'], 2) }}</td>
                        <td class="text-right">{{ $g['porcentaje'] }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Gráficos ─────────────────────────────────────── --}}
    @if(!empty($chartArea) || !empty($chartBar) || !empty($chartPie))
    <div class="section-title">Gráficos del Período</div>

    @if(!empty($chartArea))
    <div class="chart-card" style="margin-bottom:10px;">
        <div class="chart-card-header">Progreso de Streamify — Ingresos, Costos, Gastos y Ganancias</div>
        <div class="chart-card-body">
            <img src="{{ $chartArea }}" alt="Gráfico de progreso" style="width:100%;max-height:200px;object-fit:contain;">
        </div>
    </div>
    @endif

    @if(!empty($chartBar) || !empty($chartPie))
    <div class="chart-row">
        @if(!empty($chartBar))
        <div class="chart-col-7">
            <div class="chart-card">
                <div class="chart-card-header">Finanzas por Servicio este Mes</div>
                <div class="chart-card-body">
                    <img src="{{ $chartBar }}" alt="Gráfico de barras" style="width:100%;max-height:180px;object-fit:contain;">
                </div>
            </div>
        </div>
        @endif
        @if(!empty($chartPie))
        <div class="chart-col-5">
            <div class="chart-card">
                <div class="chart-card-header">Ganancias por Servicio</div>
                <div class="chart-card-body">
                    <img src="{{ $chartPie }}" alt="Gráfico circular" style="width:100%;max-height:180px;object-fit:contain;">
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
    @endif

    {{-- ── Tabla de Resultados por Servicio ─────────────── --}}
    <div class="section-title">Resultados por Servicio</div>
    @php
    $servicios = [
        ['nombre'=>'Netflix',  'cuentas'=>$cuentas_netflix,  'usuarios'=>$usuarios_netflix,  'ing'=>$ingresos_netflix,  'cos'=>$costos_netflix],
        ['nombre'=>'Disney',   'cuentas'=>$cuentas_disney,   'usuarios'=>$usuarios_disney,   'ing'=>$ingresos_disney,   'cos'=>$costos_disney],
        ['nombre'=>'Prime',    'cuentas'=>$cuentas_prime,    'usuarios'=>$usuarios_prime,    'ing'=>$ingresos_prime,    'cos'=>$costos_prime],
        ['nombre'=>'Max',      'cuentas'=>$cuentas_max,      'usuarios'=>$usuarios_max,      'ing'=>$ingresos_max,      'cos'=>$costos_max],
        ['nombre'=>'Magis',    'cuentas'=>$cuentas_magis,    'usuarios'=>$usuarios_magis,    'ing'=>$ingresos_magis,    'cos'=>$costos_magis],
        ['nombre'=>'Crunchy',  'cuentas'=>$cuentas_crunchy,  'usuarios'=>$usuarios_crunchy,  'ing'=>$ingresos_crunchy,  'cos'=>$costos_crunchy],
        ['nombre'=>'Paramount','cuentas'=>$cuentas_paramount,'usuarios'=>$usuarios_paramount,'ing'=>$ingresos_paramount,'cos'=>$costos_paramount],
        ['nombre'=>'Spotify',  'cuentas'=>$cuentas_spotify,  'usuarios'=>$usuarios_spotify,  'ing'=>$ingresos_spotify,  'cos'=>$costos_spotify],
        ['nombre'=>'Otros',    'cuentas'=>$cuentas_otros,    'usuarios'=>$usuarios_otros,    'ing'=>$ingresos_otros,    'cos'=>$costos_otros],
    ];
    @endphp
    <table>
        <thead>
            <tr>
                <th>Servicio</th>
                <th class="text-center">Cuentas</th>
                <th class="text-center">Usuarios</th>
                <th class="text-center">Usu/Cta</th>
                <th class="text-right">Ingresos</th>
                <th class="text-right">Costos</th>
                <th class="text-right">Ganancias</th>
                <th class="text-center">Renta</th>
                <th class="text-right">Ing/Cli</th>
                <th class="text-right">Gan/Cli</th>
            </tr>
        </thead>
        <tbody>
            @foreach($servicios as $s)
            @php $gan = $s['ing'] - $s['cos']; @endphp
            <tr>
                <td><strong>{{ $s['nombre'] }}</strong></td>
                <td class="text-center">{{ $s['cuentas'] }}</td>
                <td class="text-center">{{ $s['usuarios'] }}</td>
                <td class="text-center">{{ $s['cuentas'] ? number_format($s['usuarios']/$s['cuentas'],1) : 0 }}</td>
                <td class="text-right">${{ number_format($s['ing'],2) }}</td>
                <td class="text-right">${{ number_format($s['cos'],2) }}</td>
                <td class="text-right {{ $gan >= 0 ? 'positive' : 'negative' }}">${{ number_format($gan,2) }}</td>
                <td class="text-center">{{ $s['cos'] ? number_format($s['ing']/$s['cos'],2) : '—' }}</td>
                <td class="text-right">{{ $s['usuarios'] ? '$'.number_format($s['ing']/$s['usuarios'],2) : '—' }}</td>
                <td class="text-right {{ $gan >= 0 ? 'positive' : 'negative' }}">{{ $s['usuarios'] ? '$'.number_format($gan/$s['usuarios'],2) : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
                $totIng = $ingresos_mes; $totCos = $costos_mes;
                $totGan = $totIng - $totCos;
            @endphp
            <tr>
                <td><strong>TOTALES</strong></td>
                <td class="text-center">{{ $num_cuentas }}</td>
                <td class="text-center">{{ $total_usuarios_activos }}</td>
                <td class="text-center">{{ $num_cuentas ? number_format($total_usuarios_activos/$num_cuentas,1) : 0 }}</td>
                <td class="text-right">${{ number_format($totIng,2) }}</td>
                <td class="text-right">${{ number_format($totCos,2) }}</td>
                <td class="text-right">${{ number_format($totGan,2) }}</td>
                <td class="text-center">{{ $totCos ? number_format($totIng/$totCos,2) : '—' }}</td>
                <td class="text-right">{{ $total_usuarios_activos ? '$'.number_format($totIng/$total_usuarios_activos,2) : '—' }}</td>
                <td class="text-right">{{ $total_usuarios_activos ? '$'.number_format($totGan/$total_usuarios_activos,2) : '—' }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="pdf-footer">
        Streamify HQ — Reporte Mensual {{ ucfirst($mes) }} {{ $year }} — Generado el {{ now()->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>
