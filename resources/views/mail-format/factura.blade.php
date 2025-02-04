<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura de tu compra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            background-color: #F3F7E8;
            margin: 20px;
        }
        .outer-container {
            background-color: #F3F7E8;
            padding: 20px;
            border-radius: 8px;
        }
        .container {
            width: 80%;
            margin: auto;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            background-color: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 150px;
        }
        h2, h3 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .totales {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="outer-container">
        <div class="header">
            <img src="{{ asset('images/TARJETA.png') }}" alt="Tarjeta">
        </div>
        <div class="container">
            <h2>Factura Electrónica</h2>
            <p><strong>Fecha de Emisión:</strong> {{ $venta->fechaven->format('Y/m/d') }}</p>
            <p><strong>Factura No:</strong> {{ $venta->idven }}</p>

            <h3>Datos del Cliente</h3>
            <p><strong>Razon Social/ Nombre:</strong> {{ $venta->cliente->nombrecli }}</p>
            <p><strong>Facturado a:</strong> {{ $venta->cliente->email }}</p>

            <h3>Detalle de la Factura</h3>
            <table>
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th>P. Unitario</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total = 0;
                    @endphp
                    @if (!is_null($venta->detalles_venta) && count($venta->detalles_venta) > 0)
                        @foreach ($venta->detalles_venta as $detalle)
                            @if ($detalle->activodet)
                                @php
                                    $total += $detalle->montodet;
                                @endphp
                                <tr>
                                    <td>Acceso a {{ explode('-', $detalle->idper)[0] }}</td>
                                    <td>{{ $detalle->montodet }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4">No hay detalles disponibles.</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <h3 class="totales">Total: ${{ number_format($total, 2) }}</h3>
        </div>
    </div>
</body>
</html>