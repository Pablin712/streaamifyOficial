<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte del Mes</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .card-container {
            width: 100%;
            text-align: left;
        }

        .card {
            display: inline-block;
            width: 23%;
            margin: 1%;
            vertical-align: top;
            border: 1px solid #ccc;
            border-left: 5px solid #4e73df;
            border-radius: 5px;
            padding: 10px;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        .card-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #4e73df;
            margin-bottom: 5px;
        }

        .card-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .icon {
            float: right;
            font-size: 18px;
            color: #ccc;
        }
    </style>
</head>

<body>
    <div class="card"
        style="width: 100%; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #4e73df; padding-bottom: 10px;">

        <!-- Columna izquierda: título y detalles -->
        <div style="text-align: left;">
            <h1 style="font-size: 28px; font-weight: bold; color: #4e73df; margin: 0;">REPORTE MENSUAL</h1>
            <p style="font-size: 18px; margin: 5px 0;">{{ $mes }} - {{ $year }}</p>
            <p style="font-size: 16px; color: #888;">Streamify HQ</p>
        </div>

        <!-- Columna derecha: balance -->
        <div style="text-align: right;">
            <span style="font-size: 32px; font-weight: bold; color: {{ $balance >= 0 ? '#28a745' : '#dc3545' }};">
                {{ number_format($balance, 2) }}
            </span>
            <div style="font-size: 14px; color: #666;">Balance</div>
        </div>
    </div>
    <div class="card-container">
        <div class="card">
            <div class="card-title">Ingresos (Mensual)</div>
            <div class="card-value">${{ $ingresos_mes }}</div>
        </div>
        <div class="card">
            <div class="card-title">Ingresos (Anual)</div>
            <div class="card-value">${{ $ingresos_ano }}</div>
        </div>
        <div class="card">
            <div class="card-title">Ventas este mes</div>
            <div class="card-value">{{ $ventas_mes }}</div>
        </div>
        <div class="card">
            <div class="card-title">Ventas este año</div>
            <div class="card-value">{{ $ventas_ano }}</div>
        </div>
        <div class="card">
            <div class="card-title">Cuentas Activas</div>
            <div class="card-value">{{ $num_cuentas }}</div>
        </div>
        <div class="card">
            <div class="card-title">Espacios disponibles</div>
            <div class="card-value">{{ $espacios }}</div>
        </div>
        <div class="card">
            <div class="card-title">Pagos pendientes</div>
            <div class="card-value">{{ $usuarios_acobrar }}</div>
        </div>
        <div class="card">
            <div class="card-title">Cuentas caídas</div>
            <div class="card-value">{{ $cuentas_caidas }}</div>
        </div>
        <div class="card">
            <div class="card-title">Clientes activos</div>
            <div class="card-value">{{ $clientes_activos }}</div>
        </div>
        <div class="card">
            <div class="card-title">Usuarios activos</div>
            <div class="card-value">{{ $total_usuarios_activos }}</div>
        </div>
        <div class="card">
            <div class="card-title">Media de pago por cliente</div>
            <div class="card-value">${{ number_format($promedio_pagos_mes, 2) }}</div>
        </div>
        <div class="card">
            <div class="card-title">Cliente más facturado</div>
            <div class="card-value">
                {{ $cliente_mas_facturado->nombre_cliente ?? 'No encontrado' }}
                ${{ $cliente_mas_facturado->facturado ?? 0 }}
            </div>
        </div>
    </div>
    {{-- Finanzas --}}
    <div style="width: 100%; border: 1px solid #ccc; padding: 15px; margin-top: 20px; border-radius: 5px;">
        <h3 style="margin-bottom: 15px; color: #4e73df;">Resumen financiero</h3>

        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background-color: #f0f0f0; text-align: center;">
                    <th style="padding: 8px; border: 1px solid #ccc;">Concepto</th>
                    <th style="padding: 8px; border: 1px solid #ccc;">Ingresos</th>
                    <th style="padding: 8px; border: 1px solid #ccc;">Costos</th>
                    <th style="padding: 8px; border: 1px solid #ccc;">Gastos</th>
                    <th style="padding: 8px; border: 1px solid #ccc;">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr style="text-align: center;">
                    <td style="padding: 6px; border: 1px solid #ccc; font-weight: bold;">Monto</td>
                    <td style="padding: 6px; border: 1px solid #ccc;">{{ number_format($ingresos_mes, 2) }}</td>
                    <td style="padding: 6px; border: 1px solid #ccc;">{{ number_format($costos_mes, 2) }}</td>
                    <td style="padding: 6px; border: 1px solid #ccc;">{{ number_format($gastos_mes, 2) }}</td>
                    <td
                        style="padding: 6px; border: 1px solid #ccc; font-weight: bold; color: {{ $balance >= 0 ? '#28a745' : '#dc3545' }};">
                        {{ number_format($balance, 2) }}
                    </td>
                </tr>
                <tr style="text-align: center;">
                    <td style="padding: 6px; border: 1px solid #ccc; font-weight: bold;">Porcentaje</td>
                    <td style="padding: 6px; border: 1px solid #ccc;">100%</td>
                    <td style="padding: 6px; border: 1px solid #ccc;">{{ number_format($costos_pct, 2) }}%</td>
                    <td style="padding: 6px; border: 1px solid #ccc;">{{ number_format($gastos_pct, 2) }}%</td>
                    <td
                        style="padding: 6px; border: 1px solid #ccc; font-weight: bold; color: {{ $balance >= 0 ? '#28a745' : '#dc3545' }};">
                        {{ number_format($balance_pct, 2) }}%
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Resumen de gastos -->
        <h4 style="margin-top: 20px; color: #4e73df;">Resumen de Gastos</h4>
        <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px;">
            <thead>
                <tr style="background-color: #f0f0f0; text-align: center;">
                    <th style="padding: 8px; border: 1px solid #ccc;">Concepto</th>
                    <th style="padding: 8px; border: 1px solid #ccc;">Monto</th>
                    <th style="padding: 8px; border: 1px solid #ccc;">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($gastos as $gasto)
                    <tr style="text-align: center;">
                        <td style="padding: 6px; border: 1px solid #ccc;">{{ $gasto['concepto'] }}</td>
                        <td style="padding: 6px; border: 1px solid #ccc;">{{ number_format($gasto['total'], 2) }}</td>
                        <td style="padding: 6px; border: 1px solid #ccc;">{{ $gasto['porcentaje'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{-- tabla de resultados --}}
    <div class="card"
        style="width: 100%; text-align: center; margin-top: 30px; border-bottom: 2px solid #4e73df; padding-bottom: 10px;">
        <h1 style="font-size: 28px; font-weight: bold; color: #4e73df; margin: 0;">RESULTADOS</h1>
    </div>
    {{-- encerrar la tabla en un estilo agradable --}}
    <table
        style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; text-align: center; border: 1px solid #ddd;">
        <thead style="background-color: #4e73df; color: white;">
            <tr>
                <th style="padding: 10px; border: 1px solid #ddd;">Servicio</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Cta</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Usuarios</th>
                <th style="padding: 10px; border: 1px solid #ddd;">usu/cue</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Ingresos</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Costos</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Ganancias</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Renta</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Ing/Cli</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Cos/Cli</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Gan/Cli</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">Netflix</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $cuentas_netflix }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $usuarios_netflix }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($cuentas_netflix != 0)
                        {{ number_format($usuarios_netflix / $cuentas_netflix, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_netflix }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $costos_netflix }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_netflix - $costos_netflix }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($costos_netflix != 0)
                        {{ number_format($ingresos_netflix / $costos_netflix, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_netflix != 0)
                        {{ number_format($ingresos_netflix / $usuarios_netflix, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_netflix != 0)
                        {{ number_format($costos_netflix / $usuarios_netflix, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_netflix != 0)
                        {{ number_format(($ingresos_netflix - $costos_netflix) / $usuarios_netflix, 2) }}
                    @else
                        0
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">Disney</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $cuentas_disney }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $usuarios_disney }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($cuentas_disney != 0)
                        {{ number_format($usuarios_disney / $cuentas_disney, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_disney }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $costos_disney }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_disney - $costos_disney }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($costos_disney != 0)
                        {{ number_format($ingresos_disney / $costos_disney, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_disney != 0)
                        {{ number_format($ingresos_disney / $usuarios_disney, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_disney != 0)
                        {{ number_format($costos_disney / $usuarios_disney, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_disney != 0)
                        {{ number_format(($ingresos_disney - $costos_disney) / $usuarios_disney, 2) }}
                    @else
                        0
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">Prime</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $cuentas_prime }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $usuarios_prime }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($cuentas_prime != 0)
                        {{ number_format($usuarios_prime / $cuentas_prime, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_prime }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $costos_prime }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_prime - $costos_prime }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($costos_prime != 0)
                        {{ number_format($ingresos_prime / $costos_prime, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_prime != 0)
                        {{ number_format($ingresos_prime / $usuarios_prime, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_prime != 0)
                        {{ number_format($costos_prime / $usuarios_prime, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_prime != 0)
                        {{ number_format(($ingresos_prime - $costos_prime) / $usuarios_prime, 2) }}
                    @else
                        0
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">Max</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $cuentas_max }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $usuarios_max }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($cuentas_max != 0)
                        {{ number_format($usuarios_max / $cuentas_max, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_max }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $costos_max }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_max - $costos_max }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($costos_max != 0)
                        {{ number_format($ingresos_max / $costos_max, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_max != 0)
                        {{ number_format($ingresos_max / $usuarios_max, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_max != 0)
                        {{ number_format($costos_max / $usuarios_max, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_max != 0)
                        {{ number_format(($ingresos_max - $costos_max) / $usuarios_max, 2) }}
                    @else
                        0
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">Magis</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $cuentas_magis }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $usuarios_magis }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($cuentas_magis != 0)
                        {{ number_format($usuarios_magis / $cuentas_magis, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_magis }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $costos_magis }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_magis - $costos_magis }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($costos_magis != 0)
                        {{ number_format($ingresos_magis / $costos_magis, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_magis != 0)
                        {{ number_format($ingresos_magis / $usuarios_magis, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_magis != 0)
                        {{ number_format($costos_magis / $usuarios_magis, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_magis != 0)
                        {{ number_format(($ingresos_magis - $costos_magis) / $usuarios_magis, 2) }}
                    @else
                        0
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">Crunchy</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $cuentas_crunchy }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $usuarios_crunchy }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($cuentas_crunchy != 0)
                        {{ number_format($usuarios_crunchy / $cuentas_crunchy, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_crunchy }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $costos_crunchy }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_crunchy - $costos_crunchy }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($costos_crunchy != 0)
                        {{ number_format($ingresos_crunchy / $costos_crunchy, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_crunchy != 0)
                        {{ number_format($ingresos_crunchy / $usuarios_crunchy, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_crunchy != 0)
                        {{ number_format($costos_crunchy / $usuarios_crunchy, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_crunchy != 0)
                        {{ number_format(($ingresos_crunchy - $costos_crunchy) / $usuarios_crunchy, 2) }}
                    @else
                        0
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">Paramount</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $cuentas_paramount }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $usuarios_paramount }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($cuentas_paramount != 0)
                        {{ number_format($usuarios_paramount / $cuentas_paramount, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_paramount }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $costos_paramount }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_paramount - $costos_paramount }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($costos_paramount != 0)
                        {{ number_format($ingresos_paramount / $costos_paramount, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_paramount != 0)
                        {{ number_format($ingresos_paramount / $usuarios_paramount, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_paramount != 0)
                        {{ number_format($costos_paramount / $usuarios_paramount, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_paramount != 0)
                        {{ number_format(($ingresos_paramount - $costos_paramount) / $usuarios_paramount, 2) }}
                    @else
                        0
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">Spotify</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $cuentas_spotify }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $usuarios_spotify }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($cuentas_spotify != 0)
                        {{ number_format($usuarios_spotify / $cuentas_spotify, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_spotify }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $costos_spotify }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_spotify - $costos_spotify }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($costos_spotify != 0)
                        {{ number_format($ingresos_spotify / $costos_spotify, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_spotify != 0)
                        {{ number_format($ingresos_spotify / $usuarios_spotify, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_spotify != 0)
                        {{ number_format($costos_spotify / $usuarios_spotify, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_spotify != 0)
                        {{ number_format(($ingresos_spotify - $costos_spotify) / $usuarios_spotify, 2) }}
                    @else
                        0
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">Otros</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $cuentas_otros }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $usuarios_otros }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($cuentas_otros != 0)
                        {{ number_format($usuarios_otros / $cuentas_otros, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_otros }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $costos_otros }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $ingresos_otros - $costos_otros }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($costos_otros != 0)
                        {{ number_format($ingresos_otros / $costos_otros, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_otros != 0)
                        {{ number_format($ingresos_otros / $usuarios_otros, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_otros != 0)
                        {{ number_format($costos_otros / $usuarios_otros, 2) }}
                    @else
                        0
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($usuarios_otros != 0)
                        {{ number_format(($ingresos_otros - $costos_otros) / $usuarios_otros, 2) }}
                    @else
                        0
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>Totales</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>{{ $num_cuentas }}</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>{{ $total_usuarios_activos }}</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($num_cuentas != 0)
                        <strong>{{ number_format($total_usuarios_activos / $num_cuentas, 2) }}</strong>
                    @else
                        <strong>0</strong>
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>{{ $ingresos_mes }}</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>{{ $costos_mes }}</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>{{ $ingresos_mes - $costos_mes }}</strong>
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($costos_mes != 0)
                        <strong>{{ number_format($ingresos_mes / $costos_mes, 2) }}</strong>
                    @else
                        <strong>0</strong>
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($total_usuarios_activos != 0)
                        <strong>{{ number_format($ingresos_mes / $total_usuarios_activos, 2) }}</strong>
                    @else
                        <strong>0</strong>
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($total_usuarios_activos != 0)
                        <strong>{{ number_format($costos_mes / $total_usuarios_activos, 2) }}</strong>
                    @else
                        <strong>0</strong>
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if ($total_usuarios_activos != 0)
                        <strong>{{ number_format(($ingresos_mes - $costos_mes) / $total_usuarios_activos, 2) }}</strong>
                    @else
                        <strong>0</strong>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

</body>

</html>
