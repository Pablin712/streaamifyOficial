<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de cuentas {{ $fecha }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2, h3, h4 { margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #333; padding: 4px 6px; text-align: center; }
        th { background-color: #f0f0f0; }
        .resumen { margin-bottom: 20px; }
        .subtotal { font-weight: bold; background-color: #e9e9e9; }
    </style>
</head>
<body>
    <h2>Reporte de cuentas {{ $fecha }}</h2>

    <div class="resumen">
        <p><strong>Streamify</strong></p>
        <p>Total de cuentas: {{ $cuentas->where('activocue', true)->count() }}</p>
        <p>Cuentas dañadas: {{ $cuentas->where('caidacue', true)->count() }}</p>
        <p>Total de proveedores: {{ $cuentas->pluck('valor.proveedor.idpro')->unique()->count() }}</p>
    </div>

    <h3>Lista de cuentas a renovar</h3>

    @forelse ($agrupadas as $proveedor => $cuentasProveedor)
        <h4>Proveedor: {{ $proveedor }}</h4>
        @php
            $cuentasPorServicio = $cuentasProveedor->groupBy(fn($c) => $c->valor->servicio->nombreser ?? 'Sin servicio');
            $totalProveedor = 0;
        @endphp

        @foreach ($cuentasPorServicio as $servicio => $cuentasServicio)
            <p><strong>Servicio:</strong> {{ $servicio }}</p>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Fecha vencimiento</th>
                        <th>Costo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cuentasServicio as $cuenta)
                        <tr>
                            <td>{{ $cuenta->idcue }}</td>
                            <td>{{ $cuenta->usuariocue }}</td>
                            <td>{{ $cuenta->fechavencue->format('Y/m/d') }}</td>
                            <td>${{ number_format($cuenta->costo_mes, 2) }}</td>
                        </tr>
                        @php $totalProveedor += $cuenta->costo_mes; @endphp
                    @endforeach
                    <tr class="subtotal">
                        <td colspan="2">Suma Total</td>
                        <td>${{ number_format($cuentasServicio->sum('costo_mes'), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endforeach

        <p style="text-align: right; font-weight: bold;">
            Total a pagar por {{ $proveedor }}: ${{ number_format($totalProveedor, 2) }}
        </p>
        <hr style="margin: 15px 0;">
    @empty
        <p>No hay cuentas convenientes a renovar este mes.</p>
    @endforelse

    <p style="text-align:right; font-size:11px;">Generado el {{ $fecha }}</p>
</body>
</html>