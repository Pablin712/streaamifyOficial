<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de cuentas {{ $fecha }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; background: #f8fafc; }
        h2, h3, h4 { margin-bottom: 5px; }
        .resumen { margin-bottom: 20px; }
        .total-mes {
            background: #2563eb;
            color: #fff;
            padding: 10px 0;
            font-size: 16px;
            text-align: center;
            border-radius: 6px;
            margin-bottom: 18px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #b6c2d1;
            padding: 6px 8px;
            text-align: center;
            word-break: break-word;
        }
        th {
            background-color: #2563eb;
            color: #fff;
            font-weight: bold;
        }
        tr:nth-child(even) td {
            background-color: #f1f5fb;
        }
        tr:nth-child(odd) td {
            background-color: #eaf0fa;
        }
        .subtotal {
            font-weight: bold;
            background-color: #dbeafe !important;
            color: #1e293b;
        }
        .proveedor-header {
            background: #f0f4f8;
            font-weight: bold;
            padding: 8px;
            font-size: 14px;
            border-radius: 4px;
            margin-bottom: 6px;
        }
        hr {
            border: none;
            border-top: 1.5px solid #2563eb;
            margin: 18px 0;
        }
    </style>
</head>
<body>
    <h2>Reporte de cuentas {{ $fecha }}</h2>

    @php
        $totalMes = $agrupadas->flatten()->sum('costo_mes');
    @endphp

    <div class="total-mes">
        Total a pagar este mes: ${{ number_format($totalMes, 2) }}
    </div>

    <div class="resumen">
        <p><strong>Streamify</strong></p>
        <p>Total de cuentas: {{ $cuentas->where('activocue', true)->count() }}</p>
        <p>Cuentas dañadas: {{ $cuentas->where('caidacue', true)->count() }}</p>
        <p>Total de proveedores: {{ $cuentas->pluck('valor.proveedor.idpro')->unique()->count() }}</p>
    </div>

    <h3>Lista de cuentas a renovar</h3>

    @forelse ($agrupadas as $proveedor => $cuentasProveedor)
        <div class="proveedor-header">Proveedor: {{ $proveedor }}</div>
        @php
            $cuentasPorServicio = $cuentasProveedor->groupBy(fn($c) => $c->valor->servicio->nombreser ?? 'Sin servicio');
            $totalProveedor = 0;
        @endphp

        @foreach ($cuentasPorServicio as $servicio => $cuentasServicio)
            <p><strong>Servicio:</strong> {{ $servicio }}</p>
            <table>
                <thead>
                    <tr>
                        <th style="width: 18%;">ID</th>
                        <th style="width: 22%;">Usuario</th>
                        <th style="width: 25%;">Fecha vencimiento</th>
                        <th style="width: 20%;">Costo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cuentasServicio as $cuenta)
                        <tr>
                            <td>{{ $cuenta->idcue }}</td>
                            <td>{{ $cuenta->usuariocue }}</td>
                            <td>{{ \Carbon\Carbon::parse($cuenta->fechavencue)->format('Y/m/d') }}</td>
                            <td>${{ number_format($cuenta->costo_mes, 2) }}</td>
                        </tr>
                        @php $totalProveedor += $cuenta->costo_mes; @endphp
                    @endforeach
                    <tr class="subtotal">
                        <td colspan="3">Suma Total Servicio</td>
                        <td>${{ number_format($cuentasServicio->sum('costo_mes'), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endforeach

        <p style="text-align: right; font-weight: bold;">
            Total a pagar por {{ $proveedor }}: ${{ number_format($totalProveedor, 2) }}
        </p>
        <hr>
    @empty
        <p>No hay cuentas convenientes a renovar este mes.</p>
    @endforelse

    <p style="text-align:right; font-size:11px;">Generado el {{ $fecha }}</p>
</body>
</html>