<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Transacciones</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1e293b; background: #fff; }

        .header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            padding: 20px 24px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .header h1 { font-size: 22px; font-weight: bold; margin-bottom: 4px; }
        .header .subtitle { font-size: 12px; opacity: .85; }
        .header-meta { margin-top: 8px; font-size: 11px; opacity: .75; }

        .kpi-row { display: flex; gap: 12px; margin-bottom: 20px; }
        .kpi-box {
            flex: 1;
            border-radius: 8px;
            padding: 12px 16px;
            border-left: 4px solid;
        }
        .kpi-box.green  { background: #f0fdf4; border-color: #22c55e; }
        .kpi-box.red    { background: #fef2f2; border-color: #ef4444; }
        .kpi-box.blue   { background: #eff6ff; border-color: #3b82f6; }
        .kpi-box.yellow { background: #fffbeb; border-color: #f59e0b; }
        .kpi-label  { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .05em; color: #64748b; margin-bottom: 4px; }
        .kpi-value  { font-size: 18px; font-weight: bold; }
        .kpi-box.green  .kpi-value { color: #166534; }
        .kpi-box.red    .kpi-value { color: #991b1b; }
        .kpi-box.blue   .kpi-value { color: #1e3a8a; }
        .kpi-box.yellow .kpi-value { color: #92400e; }

        .filters-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 10px;
            color: #475569;
        }
        .filters-bar span { font-weight: bold; color: #1e3a8a; }

        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        thead tr { background: #1e3a8a; color: white; }
        thead th { padding: 9px 8px; text-align: left; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .04em; }
        tbody tr:nth-child(even) { background: #eff6ff; }
        tbody tr:nth-child(odd)  { background: #ffffff; }
        tbody td { padding: 7px 8px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }

        .badge-ingreso { background: #dcfce7; color: #166534; padding: 2px 7px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-egreso  { background: #fee2e2; color: #991b1b; padding: 2px 7px; border-radius: 10px; font-size: 9px; font-weight: bold; }

        .total-row td { font-weight: bold; background: #1e3a8a !important; color: white; padding: 9px 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>REPORTE DE TRANSACCIONES</h1>
        <div class="subtitle">Streamify HQ — Gestión Financiera</div>
        <div class="header-meta">Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <!-- KPI boxes -->
    @php
        $totalIngresos  = $transacciones->where('tipo', 'ingreso')->sum('monto_transaccion');
        $totalEgresos   = $transacciones->where('tipo', 'egreso')->sum('monto_transaccion');
        $balance        = $totalIngresos - $totalEgresos;
        $totalRegistros = $transacciones->count();
    @endphp
    <div class="kpi-row">
        <div class="kpi-box blue">
            <div class="kpi-label">Total Registros</div>
            <div class="kpi-value">{{ $totalRegistros }}</div>
        </div>
        <div class="kpi-box green">
            <div class="kpi-label">Total Ingresos</div>
            <div class="kpi-value">${{ number_format($totalIngresos, 2) }}</div>
        </div>
        <div class="kpi-box red">
            <div class="kpi-label">Total Egresos</div>
            <div class="kpi-value">${{ number_format($totalEgresos, 2) }}</div>
        </div>
        <div class="kpi-box {{ $balance >= 0 ? 'green' : 'red' }}">
            <div class="kpi-label">Balance Neto</div>
            <div class="kpi-value">${{ number_format($balance, 2) }}</div>
        </div>
    </div>

    <!-- Filtros aplicados -->
    <div class="filters-bar">
        Filtros aplicados:
        <span>Banco:</span> {{ $banco ? $banco->nombreban : 'Todos' }} &nbsp;|&nbsp;
        <span>Tipo:</span> {{ $tipo ? ucfirst($tipo) : 'Todos' }} &nbsp;|&nbsp;
        <span>Período:</span>
        {{ $fechaInicio ? \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') : 'Sin límite' }}
        —
        {{ $fechaFin ? \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') : 'Sin límite' }}
    </div>

    <!-- Tabla -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Banco</th>
                <th class="text-center">Tipo</th>
                <th class="text-right">Monto Anterior</th>
                <th class="text-right">Monto Transacción</th>
                <th class="text-right">Monto Actualizado</th>
                <th>Referencia</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transacciones as $t)
            <tr>
                <td>{{ $t->id }}</td>
                <td>{{ $t->banco->nombreban ?? 'N/A' }}</td>
                <td class="text-center">
                    <span class="badge-{{ $t->tipo }}">{{ ucfirst($t->tipo) }}</span>
                </td>
                <td class="text-right">${{ number_format($t->monto_anterior, 2) }}</td>
                <td class="text-right">${{ number_format($t->monto_transaccion, 2) }}</td>
                <td class="text-right">${{ number_format($t->monto_actualizado, 2) }}</td>
                <td>{{ $t->referencia ?? '—' }}</td>
                <td>{{ \Carbon\Carbon::parse($t->fecha)->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding:20px; color:#94a3b8;">
                    No hay transacciones para los filtros seleccionados
                </td>
            </tr>
            @endforelse
            @if($transacciones->count() > 0)
            <tr class="total-row">
                <td colspan="4">TOTALES</td>
                <td class="text-right">${{ number_format($totalIngresos - $totalEgresos, 2) }}</td>
                <td></td>
                <td colspan="2">{{ $totalRegistros }} registros</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Streamify HQ — Reporte generado automáticamente — {{ now()->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>
