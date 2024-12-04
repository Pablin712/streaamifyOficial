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

@section('table2')
    <div class="card mb-4">
        <div class="card-body">
            Busca una venta específica para ver sus detalles.
            <div class="form-group mb-3">
                <label for="idven">Seleccionar Venta</label>
                <form method="GET" action="{{ route('ventas') }}#tabla-detalles">
                    <select name="idven" id="idven" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Selecciona una Venta --</option>
                        @foreach ($ventas as $venta)
                            <option value="{{ $venta->idven }}" {{ request('idven') == $venta->idven ? 'selected' : '' }}>
                                {{ $venta->idven }} - {{ $venta->cliente->nombrecli }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>

    <div id="tabla-detalles" class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Detalles de Venta de {{ $idvenSeleccionada }}
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID de Cuenta</th>
                        <th>Perfil</th>
                        <th>Descripcion</th>
                        <th>Vencimiento</th>
                        <th>Monto</th>
                        <th>Activa</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($detalles_venta as $detalle)
                        <tr>
                            <td>{{ $detalle->perfil->cuenta->idcue }}</td>
                            <td>{{ $detalle->perfil->numeroper }}</td>
                            <td>{{ $detalle->descripciondet }}</td>
                            <td>{{ $detalle->fechavendet }}</td>
                            <td>{{ $detalle->montodet }}</td>
                            <td>
                                @if ($detalle->activodet)
                                    <span class="badge bg-success">Activa</span>
                                @else
                                    <span class="badge bg-danger">Vencida</span>
                                @endif
                                <!-- Botón para cambiar estado -->
                                <form action="{{ route('ventas.status', $detalle->iddet) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-dark btn-sm">
                                        @if ($detalle->activodet)
                                            <i class="fas fa-toggle-on fa-xs"></i>
                                        @else
                                            <i class="fas fa-toggle-off fa-xs"></i>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#editDetalleModal" data-id="{{ $detalle->iddet }}">
                                    <i class="fas fa-edit">Editar</i>
                                </button>
                                <!-- Botón Eliminar -->
                                <form action="{{ route('ventas.destroy', $detalle->iddet) }}" method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar este detalle?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para ver detalles de la venta -->
    <div class="modal fade" id="viewDetailModal" tabindex="-1" aria-labelledby="viewDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDetailModalLabel">Detalles de la Venta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="detailDescripcion"></p>
                    <p id="detailCosto"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
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
        // Inicializa Select2 en el select con el id 'idcue'
        $(document).ready(function() {
            $('#idven').select2({
                placeholder: "Selecciona una Venta",
                allowClear: true // Permite borrar la selección
            });
        });
    </script>
@endsection
