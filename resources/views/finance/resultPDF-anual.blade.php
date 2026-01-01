<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Anual {{ $year }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        .header {
            width: 100%;
            display: table;
            margin-bottom: 30px;
            border-bottom: 3px solid #4e73df;
            padding-bottom: 15px;
        }

        .header-left {
            display: table-cell;
            text-align: left;
            width: 60%;
        }

        .header-right {
            display: table-cell;
            text-align: right;
            width: 40%;
            vertical-align: middle;
        }

        h1 {
            font-size: 28px;
            font-weight: bold;
            color: #4e73df;
            margin: 0;
        }

        .subtitle {
            font-size: 18px;
            margin: 5px 0;
            color: #666;
        }

        .company {
            font-size: 16px;
            color: #888;
        }

        .balance {
            font-size: 36px;
            font-weight: bold;
            color: {{ $datosAnuales['balance_anual'] >= 0 ? '#28a745' : '#dc3545' }};
        }

        .balance-label {
            font-size: 14px;
            color: #666;
        }

        .summary-cards {
            width: 100%;
            margin-bottom: 30px;
        }

        .card {
            display: inline-block;
            width: 23%;
            margin: 1%;
            vertical-align: top;
            border: 1px solid #e3e6f0;
            border-left: 5px solid #4e73df;
            border-radius: 5px;
            padding: 15px;
            box-sizing: border-box;
        }

        .card.success {
            border-left-color: #1cc88a;
        }

        .card.danger {
            border-left-color: #e74a3b;
        }

        .card.warning {
            border-left-color: #f6c23e;
        }

        .card.info {
            border-left-color: #36b9cc;
        }

        .card-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #858796;
            margin-bottom: 5px;
        }

        .card-value {
            font-size: 18px;
            font-weight: bold;
            color: #5a5c69;
        }

        .monthly-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .monthly-table th {
            background-color: #4e73df;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }

        .monthly-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e3e6f0;
            font-size: 11px;
        }

        .monthly-table tbody tr:hover {
            background-color: #f8f9fc;
        }

        .monthly-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .total-row {
            font-weight: bold;
            background-color: #e7e9f0 !important;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .positive {
            color: #1cc88a;
        }

        .negative {
            color: #e74a3b;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #4e73df;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #4e73df;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e3e6f0;
            text-align: center;
            font-size: 10px;
            color: #858796;
        }

        .stats-row {
            width: 100%;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <h1>REPORTE ANUAL {{ $year }}</h1>
            <p class="subtitle">Consolidado de 12 Meses</p>
            <p class="company">Streamify HQ</p>
        </div>
        <div class="header-right">
            <div class="balance">${{ number_format($datosAnuales['balance_anual'], 2) }}</div>
            <div class="balance-label">Balance Anual</div>
        </div>
    </div>

    <!-- Resumen de Totales -->
    <div class="section-title">Resumen Financiero del Año</div>
    <div class="summary-cards">
        <div class="card success">
            <div class="card-title">Ingresos Totales</div>
            <div class="card-value">${{ number_format($datosAnuales['ingresos_totales'], 2) }}</div>
        </div>
        <div class="card danger">
            <div class="card-title">Costos Totales</div>
            <div class="card-value">${{ number_format($datosAnuales['costos_totales'], 2) }}</div>
        </div>
        <div class="card warning">
            <div class="card-title">Gastos Totales</div>
            <div class="card-value">${{ number_format($datosAnuales['gastos_totales'], 2) }}</div>
        </div>
        <div class="card info">
            <div class="card-title">Ventas Totales</div>
            <div class="card-value">{{ number_format($datosAnuales['ventas_totales']) }}</div>
        </div>
    </div>

    <!-- Estadísticas Operativas -->
    <div class="section-title">Estadísticas Operativas</div>
    <div class="summary-cards stats-row">
        <div class="card">
            <div class="card-title">Clientes Activos</div>
            <div class="card-value">{{ $clientes_activos }}</div>
        </div>
        <div class="card">
            <div class="card-title">Usuarios Activos</div>
            <div class="card-value">{{ $total_usuarios_activos }}</div>
        </div>
        <div class="card">
            <div class="card-title">Total Cuentas</div>
            <div class="card-value">{{ $num_cuentas }}</div>
        </div>
        <div class="card danger">
            <div class="card-title">Cuentas Caídas</div>
            <div class="card-value">{{ $cuentas_caidas }}</div>
        </div>
    </div>

    <!-- Tabla Mensual Detallada -->
    <div class="section-title">Desglose Mensual</div>
    <table class="monthly-table">
        <thead>
            <tr>
                <th>Mes</th>
                <th class="text-right">Ingresos</th>
                <th class="text-right">Costos</th>
                <th class="text-right">Gastos</th>
                <th class="text-center">Ventas</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datosAnuales['meses_data'] as $mesData)
            <tr>
                <td><strong>{{ $mesData['mes'] }}</strong></td>
                <td class="text-right positive">${{ number_format($mesData['ingresos'], 2) }}</td>
                <td class="text-right negative">${{ number_format($mesData['costos'], 2) }}</td>
                <td class="text-right negative">${{ number_format($mesData['gastos'], 2) }}</td>
                <td class="text-center">{{ $mesData['ventas'] }}</td>
                <td class="text-right {{ $mesData['balance'] >= 0 ? 'positive' : 'negative' }}">
                    ${{ number_format($mesData['balance'], 2) }}
                </td>
            </tr>
            @endforeach

            <!-- Fila de Totales -->
            <tr class="total-row">
                <td><strong>TOTAL {{ $year }}</strong></td>
                <td class="text-right">${{ number_format($datosAnuales['ingresos_totales'], 2) }}</td>
                <td class="text-right">${{ number_format($datosAnuales['costos_totales'], 2) }}</td>
                <td class="text-right">${{ number_format($datosAnuales['gastos_totales'], 2) }}</td>
                <td class="text-center">{{ number_format($datosAnuales['ventas_totales']) }}</td>
                <td class="text-right {{ $datosAnuales['balance_anual'] >= 0 ? 'positive' : 'negative' }}">
                    ${{ number_format($datosAnuales['balance_anual'], 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Análisis de Rentabilidad -->
    <div class="section-title">Análisis de Rentabilidad</div>
    <div class="summary-cards">
        <div class="card">
            <div class="card-title">Margen de Costos</div>
            <div class="card-value">
                {{ $datosAnuales['ingresos_totales'] > 0 ? number_format(($datosAnuales['costos_totales'] / $datosAnuales['ingresos_totales']) * 100, 2) : 0 }}%
            </div>
        </div>
        <div class="card">
            <div class="card-title">Margen de Gastos</div>
            <div class="card-value">
                {{ $datosAnuales['ingresos_totales'] > 0 ? number_format(($datosAnuales['gastos_totales'] / $datosAnuales['ingresos_totales']) * 100, 2) : 0 }}%
            </div>
        </div>
        <div class="card {{ $datosAnuales['balance_anual'] >= 0 ? 'success' : 'danger' }}">
            <div class="card-title">Margen de Ganancia</div>
            <div class="card-value">
                {{ $datosAnuales['ingresos_totales'] > 0 ? number_format(($datosAnuales['balance_anual'] / $datosAnuales['ingresos_totales']) * 100, 2) : 0 }}%
            </div>
        </div>
        <div class="card info">
            <div class="card-title">Ingreso Promedio/Mes</div>
            <div class="card-value">${{ number_format($datosAnuales['ingresos_totales'] / 12, 2) }}</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Reporte generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Streamify HQ - Sistema de Gestión Financiera</p>
    </div>
</body>

</html>
