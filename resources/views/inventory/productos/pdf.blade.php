<!-- filepath: c:\xampp\htdocs\laravel\streaamifyOficial\resources\views\inventory\productos\pdf.blade.php -->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 10px;
            /* Reducir el tamaño de la fuente global */
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            color: #E4B100;
            font-size: 16px;
            /* Reducir el tamaño del título */
        }

        .tabla-productos {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
            /* Ajustar el ancho de las columnas */
        }

        .tabla-productos th,
        .tabla-productos td {
            border: 1px solid #ddd;
            padding: 5px;
            /* Reducir el padding */
            text-align: center;
            word-wrap: break-word;
            /* Ajustar el texto dentro de las celdas */
            font-size: 9px;
            /* Reducir el tamaño del texto en la tabla */
        }

        .tabla-productos th {
            background-color: #E4B100;
            color: black;
            font-size: 10px;
            /* Reducir el tamaño del texto en los encabezados */
        }

        .tabla-productos tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        .tabla-productos tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        .tabla-productos img {
            width: 40px;
            /* Reducir el tamaño de las imágenes */
            height: 40px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Catálogo de Productos</h1>
        <p>Listado de productos organizados en una tabla</p>
    </div>

    <table class="tabla-productos">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Categoría</th>
                <th>Tipo de Producto</th>
                <th>Estado</th>
                <th>Servicios</th>
                <th>Meses</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productosOrdenados as $index => $producto)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $producto->nombrepro }}</td>
                    <td>${{ number_format($producto->preciopro, 2) }}</td>
                    <td>{{ $producto->categoria->nombre }}</td>
                    <td>{{ $producto->tipoProducto->nombre }}</td>
                    <td>{{ $producto->activo ? 'Stock' : 'No Stock' }}</td>
                    <td>
                        @if ($producto->detalles->isNotEmpty())
                            {{ $producto->detalles->map(function ($detalle) {
                                    return $detalle->servicio->nombreser ?? 'Sin nombre';
                                })->implode(', ') }}
                        @else
                            Sin servicios
                        @endif
                    </td>
                    <td>
                        @if ($producto->detalles->isNotEmpty())
                            {{ $producto->detalles->first()->meses }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
