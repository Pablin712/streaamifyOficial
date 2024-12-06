@extends('layouts.table')

@section('title', 'Ventas')
@section('styles')
    <style>
        /* Personalizando el fondo oscuro de las filas de la tabla a morado */
        .table-dark {
            background-color: #800080 !important;
            color: white !important;
        }

        /* Personalizando el badge bg-dark a morado */
        .badge.bg-dark {
            background-color: #800080 !important;
            color: white !important;
        }

        .badge.bg-dark:hover {
            background-color: #6a006a !important;
        }
    </style>
@endsection

@section('h1', 'Ventas')
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <h3>Revisa las ventas activas y detalles de los productos vendidos.</h3>
    <p>Aquí podrás gestionar las ventas y los detalles asociados, ver los usuarios activos y las opciones de renovación.</p>
    <h4>Desarrollado por Pablo Jiménez, terminado por Andrés Rincón</h4>
    <br>
    <h5>Por completar:</h5>
    <p>
        <strong>1. Botón renovar:</strong> Permite renovar la venta extendiendo la fecha de vencimiento. <br>
        <strong>2. Botón editar perfil:</strong> Cambiar PIN del perfil de la venta. <br>
        <strong>3. Usuarios activos en las ventas:</strong> Ya implementado, muestra los usuarios actuales en cada
        venta.<br>
        <strong>4. Cambiar estado de la venta:</strong> Botón que cambia el estado de la venta (activa/inactiva).
    </p>
@endsection

@section('btncrear')
    <a href="{{ route('ventas.create') }}" class="btn btn-primary mb-3">Crear Venta</a>
@endsection

@section('tablename', 'Ventas')

@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID Venta</th>
                <th>Cliente</th>
                <th>Empleado</th>
                <th>Fecha de Venta</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ventas as $venta)
                <tr>
                    <td>{{ $venta->idven }}</td>
                    <td>{{ $venta->cliente->nombrecli }}</td>
                    <td>{{ $venta->empleado->nombreemp }}</td>
                    <td>{{ $venta->fechaven->format('d/m/Y') }}</td>
                    <td>
                        <!-- Botón para editar venta -->
                        <a href="{{ route('ventas.edit', $venta->idven) }}" class="btn btn-warning"><i
                                class="fas fa-edit"></i></a>

                        <a href="{{ route('ventas.renew', $venta->idven) }}" class="btn btn-success">
                            {{--  --}}
                            <i class="fas fa-sync-alt"></i>
                        </a>
                        <!-- Eliminar venta
                            <form action="{ route('ventas.destroy', $venta->idven) }}" method="POST" style="display: inline;">
                                @ csrf
                                @ method('DELETE')
                                <button type="submit" class="btn btn-danger btn-circle"
                                    onclick="return confirm('¿Estás seguro?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            -->
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Script para mostrar los detalles de la venta en un modal
            $('#viewDetailModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var detalleId = button.data('id');
                var descripcion = button.data('descripcion');

                var modal = $(this);
                modal.find('#detailDescripcion').text(descripcion);
                modal.find('#detailCosto').text("Costo: " +
                    detalleId); // Ajusta esto según la información que necesites
            });
        });
    </script>
    <script>
        // Inicializa Select2 en el select con el id 'idven'
        $(document).ready(function() {
            $('#idven').select2({
                placeholder: "Selecciona una Venta",
                allowClear: true // Permite borrar la selección
            });
        });
    </script>
@endsection
