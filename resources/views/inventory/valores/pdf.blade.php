<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>TopValores-{{ $fecha }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        .table-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 2%;
        }

        .service-table {
            flex-basis: 48%;
            margin-bottom: 20px;
            border: 2px solid #ccc;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.07);
            background: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 6px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #2d3e50;
            color: white;
        }

        .titulo-servicio {
            background-color: #f0f4f8;
            font-weight: bold;
            padding: 8px;
            text-align: center;
            font-size: 14px;
            border-bottom: 1px solid #ddd;
        }

        .oro {
            background-color: #fff8dc;
        }

        /* Oro suave */
        .plata {
            background-color: #f0f0f0;
        }

        /* Plata suave */
        .bronce {
            background-color: #fbeee6;
        }

        /* Bronce suave */
        .premio {
            font-weight: bold;
            font-size: 15px;
        }
    </style>
</head>

<body>
    <h2>Reporte Top Valores - {{ $fecha }}</h2>
    <p style="text-align:center;">Premiación a los 3 mejores valores por servicio (último mes)</p>

    <div class="table-container">
        @php
            $agrupados = $mejoresValores->groupBy('idser');
            $premios = [1, 2, 3];
            $clases = ['oro', 'plata', 'bronce'];
            $contador = 0;
        @endphp

        @forelse($agrupados as $idser => $valoresServicio)

            <div class="service-table" style="flex:1;">
                <div class="titulo-servicio">
                    {{ $valoresServicio->first()->servicio->nombreser ?? $idser }}
                </div>
                <table>
                    <thead>
                        <tr>
                            <th class="premio">#</th>
                            <th>Proveedor</th>
                            <th>Tipo</th>
                            <th>Meses</th>
                            <th>Costo</th>
                            <th>ID Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($valoresServicio->take(3)->values() as $index => $valor)
                            <tr class="{{ $clases[$index] ?? '' }}">
                                <td class="premio">{{ $premios[$index] ?? '-' }}</td>
                                <td>{{ $valor->proveedor->nombrepro ?? $valor->idpro }}</td>
                                <td>{{ ucfirst($valor->tipoval) }}</td>
                                <td>{{ $valor->mesesval }}</td>
                                <td>${{ number_format($valor->costoval, 2) }}</td>
                                <td>{{ $valor->idval }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <p>No hay valores para mostrar.</p>
        @endforelse
    </div>

    <p style="font-size:11px; text-align:right;">Generado el {{ $fecha }}</p>
</body>

</html>
