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
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #4CAF50;
        }
        .producto {
            border: 1px solid #ddd;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        .producto img {
            max-width: 150px;
            height: auto;
            border-radius: 5px;
        }
        .producto h3 {
            color: #4CAF50;
        }
        .producto p {
            margin: 5px 0;
        }
        .detalle-tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .detalle-tabla th, .detalle-tabla td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .detalle-tabla th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Catálogo de Productos</h1>
        <p>Listado de productos organizados por categoría o tipo</p>
    </div>

    @foreach ($productosOrdenados as $producto)
        <div class="producto">
            <div style="display: flex; align-items: center;">
                <div>
                    @if ($producto->foto)
                        <img src="{{ public_path('public/' . $producto->foto) }}" alt="Foto del Producto">
                    @else
                        <p>Sin imagen disponible</p>
                    @endif
                </div>
                <div style="margin-left: 20px;">
                    <h3>{{ $producto->nombrepro }}</h3>
                    <p><strong>Código:</strong> {{ $producto->codigopro }}</p>
                    <p><strong>Precio:</strong> ${{ number_format($producto->preciopro, 2) }}</p>
                    <p><strong>Categoría:</strong> {{ $producto->categoria->nombre }}</p>
                    <p><strong>Tipo de Producto:</strong> {{ $producto->tipoProducto->nombre }}</p>
                    <p><strong>Estado:</strong> {{ $producto->activo ? 'Activo' : 'Inactivo' }}</p>
                </div>
            </div>

            @if ($producto->detalles->isNotEmpty())
                <table class="detalle-tabla">
                    <thead>
                        <tr>
                            <th>ID Servicio</th>
                            <th>Descripción</th>
                            <th>Meses</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($producto->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->idser }}</td>
                                <td>{{ $detalle->descripcion }}</td>
                                <td>{{ $detalle->meses }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach
</body>
</html>