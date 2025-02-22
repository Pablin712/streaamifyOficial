@extends('layouts.table')

@section('title', 'Ventas')
@section('styles')
    <style>
        /* Estilos para la tabla SOLO en esta vista */
        #datatablesSimple {
            border-radius: 8px;
            overflow: hidden;
            background-color: white;
        }

        /* Encabezado con azul */
        #datatablesSimple thead th {
            background-color: #007bff !important;
            color: white !important;
            text-align: center;
            padding: 12px;
        }

        /* Filas impares con fondo gris claro */
        #datatablesSimple tbody tr:nth-child(odd) {
            background-color: #f8f9fa !important;
        }

        /* Filas pares con fondo blanco */
        #datatablesSimple tbody tr:nth-child(even) {
            background-color: white !important;
        }

        /* Hover en filas con azul suave */
        #datatablesSimple tbody tr:hover {
            background-color: #cce5ff !important;
        }

        /* Bordes más suaves */
        #datatablesSimple td,
        #datatablesSimple th {
            border: 1px solid #dee2e6 !important;
        }

        /* Alineación y padding de celdas */
        #datatablesSimple td {
            text-align: center;
            padding: 10px;
        }
    </style>
@endsection
@section('h1', 'Ventas')
@section('breadcrumb')
    Ventas
@endsection
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
                                Ventas Automatizadas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $ventasLaravel }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-robot fa-2x text-gray-300"></i>
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
    @if (Auth::user()->hasPermissionTo('ventas.create'))
        <a href="{{ route('ventas.create') }}" class="btn btn-primary mb-3">Crear Venta</a>
    @endif
@endsection

@section('tablename', 'Ventas')

@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th style="background-color: #007bff; color: white;">ID Recibo</th>
                <th style="background-color: #007bff; color: white;">Cliente</th>
                <th style="background-color: #007bff; color: white;">Empleado</th>
                <th style="background-color: #007bff; color: white;">Fecha</th>
                <th style="background-color: #007bff; color: white;">Total</th>
                @if (Auth::user()->hasAnyPermission(['ventas.edit', 'ventas.renew', 'ventas.sendInvoice', 'ventas.destroy']))
                    <th style="background-color: #007bff; color: white;">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($ventas as $venta)
                <tr>
                    <td>{{ $venta->idven }}</td>
                    <td>{{ $venta->cliente->nombrecli }}</td>
                    <td>{{ $venta->empleado->nombreemp }}</td>
                    <td>{{ $venta->fechaven->format('Y/m/d') }}</td>
                    <td>{{ $venta->totalpagoven }}</td>
                    @if (Auth::user()->hasAnyPermission(['ventas.edit', 'ventas.renew', 'ventas.sendInvoice', 'ventas.destroy']))
                        <td>
                            <!-- Botón para abrir el modal de detalles -->
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                data-bs-target="#ventaDetalleModal{{ $venta->idven }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <!-- Modal de Detalles de Venta -->
                            <div class="modal fade" id="ventaDetalleModal{{ $venta->idven }}" tabindex="-1"
                                aria-labelledby="ventaDetalleLabel{{ $venta->idven }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="ventaDetalleLabel{{ $venta->idven }}">
                                                Detalles de la Venta #{{ $venta->idven }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Cerrar"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Cliente:</strong> {{ $venta->cliente->nombrecli }}</p>
                                            <p><strong>Fecha de Venta:</strong> {{ $venta->fechaven->format('Y/m/d') }}</p>
                                            <p><strong>Total Pagado:</strong> ${{ number_format($venta->totalpagoven, 2) }}
                                            </p>

                                            <h5>Productos Comprados:</h5>
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Cuenta</th>
                                                        <th>Perfil</th>
                                                        <th>Descripción</th>
                                                        <th>Fecha de Vencimiento</th>
                                                        <th>Monto</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($venta->detalles_venta as $detalle)
                                                        <tr>
                                                            <td>{{ $detalle->perfil->idcue ?? 'N/A' }}</td>
                                                            <td>{{ $detalle->perfil->numeroper }}</td>
                                                            <td>{{ $detalle->descripciondet }}</td>
                                                            <td>{{ $detalle->fechavendet->format('Y-m-d') }}</td>
                                                            <td>${{ number_format($detalle->montodet, 2) }}</td>
                                                            <td>
                                                                @if ($detalle->activodet)
                                                                    <span class="badge bg-success">Activa</span>
                                                                @else
                                                                    <span class="badge bg-danger">Inactiva</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if (Auth::user()->hasPermissionTo('ventas.edit'))
                                <a href="{{ route('ventas.edit', $venta->idven) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            @if (Auth::user()->hasPermissionTo('ventas.renew'))
                                <a href="{{ route('ventas.renew', ['idcli' => $venta->cliente->idcli, 'idven' => $venta->idven]) }}"
                                    class="btn btn-success btn-sm">
                                    <i class="fas fa-sync-alt"></i>
                                </a>
                            @endif
                            @if (!empty($venta->cliente->email) && Auth::user()->hasPermissionTo('ventas.sendInvoice'))
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#previewInvoiceModal{{ $venta->idven }}">
                                    <i class="fas fa-file-invoice"></i>
                                </button>
                            @endif
                            @if (Auth::user()->hasPermissionTo('ventas.destroy'))
                                <form action="{{ route('ventas.destroy', $venta->idven) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle btn-sm"
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
                                            <h5 class="modal-title" id="previewInvoiceLabel{{ $venta->idven }}">
                                                Vista Previa de la Factura
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            @include('mail-format.factura', [
                                                'venta' => $venta,
                                                'cliente' => $venta->cliente,
                                            ])
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Cerrar</button>
                                            <form action="{{ route('ventas.sendInvoice', $venta->idven) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-primary">Enviar por Correo</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    @endif
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
        });
    </script>
    <script>
        // Inicializa Select2 en el select con el id 'idven'
        $(document).ready(function() {
            $('#idven').select2({
                placeholder: "Selecciona una Venta",
                allowClear: true
            });
        });
    </script>
@endsection
