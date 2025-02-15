@extends('layouts.table')

@section('title', 'Productos')
@section('styles')
    <style>
        /* Personalizando el fondo oscuro de las filas de la tabla */
        .table-dark {
            background-color: #4CAF50 !important; /* Verde personalizado */
            color: white !important;
        }

        /* Personalizando el badge bg-dark */
        .badge.bg-dark {
            background-color: #4CAF50 !important;
            color: white !important;
        }

        .badge.bg-dark:hover {
            background-color: #388E3C !important;
        }
    </style>
@endsection
@section('h1', 'Productos')
@section('breadcrumb')
    Productos
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <h3>Gestiona los productos disponibles en tu inventario.</h3>
    <p>Aquí puedes gestionar tus productos, ver detalles asociados y mantener el catálogo actualizado.</p>
@endsection

@section('btncrear')
    <a href="{{ route('productos.create') }}" class="btn btn-primary mb-3">Crear Producto</a>
@endsection

@section('tablename', 'Productos')

@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID Producto</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Categoría</th>
                <th>Tipo</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $producto)
                <tr>
                    <td>{{ $producto->id }}</td>
                    <td>{{ $producto->codigopro }}</td>
                    <td>{{ $producto->nombrepro }}</td>
                    <td>${{ number_format($producto->preciopro, 2) }}</td>
                    <td>{{ $producto->categoria->nombre }}</td>
                    <td>{{ $producto->tipoProducto->nombre }}</td>
                    <td>
                        @if ($producto->activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-danger">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <!-- Botón para ver detalles del producto -->
                        <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <!-- Botón para editar producto -->
                        <a href="{{ route('productos.edit', $producto->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <!-- Eliminar producto -->
                        <form action="{{ route('productos.destroy', $producto->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"
                                onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializa DataTables
            $('#datatablesSimple').DataTable();

            // Script adicional si deseas agregar eventos específicos
        });
    </script>
@endsection
