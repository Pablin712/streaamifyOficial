<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de tu compra</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 60%;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header img {
            max-width: 120px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .info {
            font-size: 14px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        .totales {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            margin-top: 20px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/TARJETA.png') }}" alt="Logo">
        </div>

        <h2>Recibo de Pago</h2>
        <p class="info"><strong>Fecha de Emisión:</strong> {{ $venta->fechaven->format('Y/m/d') }}</p>
        <p class="info"><strong>Recibo No:</strong> {{ $venta->idven }}</p>

        <h3>Datos del Cliente</h3>
        <p class="info"><strong>Razón Social / Nombre:</strong> {{ $venta->cliente->nombrecli }}</p>
        <p class="info"><strong>Facturado a:</strong> {{ $venta->cliente->email }}</p>

        <h3>Detalle del Recibo</h3>
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
                                <td>${{ number_format($detalle->montodet, 2) }}</td>
                            </tr>
                        @endif
                    @endforeach
                @else
                    <tr>
                        <td colspan="2">No hay detalles disponibles.</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <h3 class="totales">Total: ${{ number_format($total, 2) }}</h3>

        <div class="footer">
            <p>Gracias por tu compra. Si tienes dudas, contáctanos.</p>
        </div>
    </div>
</body>
</html>