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
    <h3>Revisa las ventas activas y detalles de los servicios vendidos.</h3>
    <p>Aquí podrás gestionar las ventas y los detalles asociados, ver los usuarios activos y las opciones de renovación.
        Es necesario realizar más de 5 ventas al día.
    </p>
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Ingresos (Hoy)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ $ingresos_dia }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Ventas (Hoy)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $ventas_dia }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Ingresos/Venta</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if ($ventas_dia > 0)
                                    ${{ number_format($ingresos_dia / $ventas_dia, 2) }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Rendimiento del Día -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Rendimiento del Día</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $rendimiento = '';
                                    if ($ventas_dia < 4) {
                                        $rendimiento = 'Bajo';
                                    } elseif ($ventas_dia <= 7) {
                                        $rendimiento = 'Regular';
                                    } elseif ($ventas_dia <= 10) {
                                        $rendimiento = 'Bueno';
                                    } else {
                                        $rendimiento = 'Excelente';
                                    }
                                @endphp
                                {{ $rendimiento }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Clientes Autenticados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $autenticados }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-id-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Recargas pendientes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $recargasPendientes }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Pedidos pendientes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pedidosPendientes }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                    <td>{{ $venta->fechaven->format('Y/m/d') }}</td>
                    <td>
                        <!-- Botón para editar venta -->
                        <a href="{{ route('ventas.edit', $venta->idven) }}" class="btn btn-warning"><i
                                class="fas fa-edit"></i></a>

                        <a href="{{ route('ventas.renew', ['idcli' => $venta->cliente->idcli, 'idven' => $venta->idven]) }}"
                            class="btn btn-success">
                            {{--  --}}
                            <i class="fas fa-sync-alt"></i>
                        </a>
                        @if (!empty($venta->cliente->email))
                        <!-- Botón para vista previa de factura -->
                        <button class="btn btn-info" data-bs-toggle="modal"
                            data-bs-target="#previewInvoiceModal{{ $venta->idven }}">
                            <i class="fas fa-file-invoice"></i>
                        </button>
                        @endif
                      
                        <!-- Eliminar venta -->
                        @if (Auth::user()->idrol == 'administrador' || Auth::user()->idrol == 'vendedor')
                            <form action="{{ route('ventas.destroy', $venta->idven) }}" method="POST"
                                style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-circle"
                                    onclick="return confirm('¿Estás seguro?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                         <!-- Modal de vista previa de factura -->
     <div class="modal fade" id="previewInvoiceModal{{ $venta->idven }}" tabindex="-1"
        aria-labelledby="previewInvoiceLabel{{ $venta->idven }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewInvoiceLabel{{ $venta->idven }}">Vista Previa de la Factura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Aquí se incluye el contenido de la factura -->
                    @include('mail-format.factura', ['venta' => $venta, 'cliente' => $venta->cliente])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <form action="{{ route('ventas.sendInvoice', $venta->idven) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">Enviar por Correo</button>
                    </form>
                </div>
            </div>
        </div>
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
