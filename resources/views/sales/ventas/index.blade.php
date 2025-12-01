@extends('layouts.navigation')

@section('title', 'Ventas')

@section('styles')
    <style>
        /* Estilos personalizados para la tabla de ventas */
        #ventas-table {
            border-radius: 8px;
            overflow: hidden;
        }

        #ventas-table thead th {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            color: white !important;
            text-align: center;
            padding: 14px 12px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        #ventas-table tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }

        #ventas-table tbody tr:nth-child(even) {
            background-color: white;
        }

        #ventas-table tbody tr:hover {
            background-color: #e3f2fd !important;
            transform: scale(1.001);
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.15);
            transition: all 0.2s ease;
        }

        #ventas-table td {
            text-align: center;
            padding: 12px 10px;
            vertical-align: middle;
        }

        /* Estilo para botones de acción */
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            margin: 2px;
        }

        /* Cards de estadísticas */
        .stats-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
    </style>
@endsection
@section('main')
<div class="container-fluid px-4">
    <!-- Título y breadcrumb -->
    <h1 class="mt-4">Ventas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Ventas</li>
    </ol>

    <!-- Descripción y alertas -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-4">
        <h3 class="text-primary">Gestión de Ventas</h3>
        <p class="text-muted">
            Revisa las ventas activas y detalles de los servicios vendidos.
            Aquí podrás gestionar las ventas y los detalles asociados, ver los usuarios activos y las opciones de renovación.
            <strong>Es necesario realizar más de 5 ventas al día.</strong>
        </p>
    </div>
    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
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
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
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
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
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
            <div class="card border-left-warning shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Rendimiento del Día</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $rendimiento = '';
                                    if ($ventas_dia < 5) {
                                        $rendimiento = 'Bajo';
                                    } elseif ($ventas_dia <= 10) {
                                        $rendimiento = 'Regular';
                                    } elseif ($ventas_dia <= 25) {
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
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
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
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
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
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
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
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
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

    <!-- Botón crear venta -->
    @if (Auth::user()->hasPermissionTo('ventas.create'))
        <div class="mb-3">
            <a href="{{ route('ventas.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Crear Venta
            </a>
        </div>
    @endif

    <!-- Tabla Enhanced v2 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Lista de Ventas
            </h6>
        </div>
        <div class="card-body">
            <!-- Encabezado: Búsqueda y Registros por página -->
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="ventas-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search text-primary"></i> Buscar:
                    </label>
                    <input id="ventas-table-search"
                           type="text"
                           placeholder="Buscar por cliente, empleado, ID..."
                           class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="ventas-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list text-primary"></i> Mostrar:
                    </label>
                    <select id="ventas-table-rows-per-page" class="form-select">
                        <option value="5" selected>5 registros</option>
                        <option value="10">10 registros</option>
                        <option value="20">20 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="ventas-table"
                       data-table="ventas-table"
                       class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="sortable" data-type="number" data-col="0">
                                ID Recibo
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="1">
                                Cliente
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="2">
                                Empleado
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="3">
                                Fecha
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="4">
                                Total
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            @if (Auth::user()->hasAnyPermission(['ventas.edit', 'ventas.renew', 'ventas.sendInvoice', 'ventas.destroy']))
                                <th data-type="actions">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ventas as $venta)
                            <tr>
                                <td><strong>#{{ $venta->idven }}</strong></td>
                                <td>{{ $venta->cliente->nombrecli }}</td>
                                <td>{{ $venta->empleado->nombreemp }}</td>
                                <td>{{ $venta->fechaven->format('Y/m/d') }}</td>
                                <td><strong>${{ number_format($venta->totalpagoven, 2) }}</strong></td>
                                @if (Auth::user()->hasAnyPermission(['ventas.edit', 'ventas.renew', 'ventas.sendInvoice', 'ventas.destroy']))
                                    <td>
                                        <div class="action-buttons">
                                            <!-- Ver detalles -->
                                            <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#ventaDetalleModal{{ $venta->idven }}"
                                                title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            @if (Auth::user()->hasPermissionTo('ventas.edit'))
                                                <a href="{{ route('ventas.edit', $venta->idven) }}"
                                                   class="btn btn-warning btn-sm"
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif

                                            @if (Auth::user()->hasPermissionTo('ventas.renew'))
                                                <a href="{{ route('ventas.renew', ['idcli' => $venta->cliente->idcli, 'idven' => $venta->idven]) }}"
                                                   class="btn btn-success btn-sm"
                                                   title="Renovar">
                                                    <i class="fas fa-sync-alt"></i>
                                                </a>
                                            @endif

                                            @if (Auth::user()->hasPermissionTo('ventas.destroy'))
                                                <form action="{{ route('ventas.destroy', $venta->idven) }}"
                                                      method="POST"
                                                      style="display: inline;"
                                                      onsubmit="return confirm('¿Estás seguro de eliminar esta venta?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer: Info y paginación -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="ventas-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6">
                    <div id="ventas-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales de detalles -->
    @foreach ($ventas as $venta)
        <div class="modal fade" id="ventaDetalleModal{{ $venta->idven }}" tabindex="-1"
            aria-labelledby="ventaDetalleLabel{{ $venta->idven }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ventaDetalleLabel{{ $venta->idven }}">
                            Detalles de la Venta #{{ $venta->idven }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <p><strong>Cliente:</strong><br>{{ $venta->cliente->nombrecli }}</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Fecha de Venta:</strong><br>{{ $venta->fechaven->format('d/m/Y') }}</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Total Pagado:</strong><br>
                                    <span class="badge bg-success">${{ number_format($venta->totalpagoven, 2) }}</span>
                                </p>
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-shopping-cart"></i> Productos Comprados
                        </h6>

                        <div class="table-responsive">
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

</div>
@endsection

@section('scripts')
<!-- Enhanced Table v2.0 -->
<script src="{{ asset('js/enhanced-table-v2.js') }}"></script>

<!-- Opcional: Librerías para exportación -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<script>
    console.log('Vista de ventas cargada con Enhanced Table v2.0');
    console.log('Total de ventas en la tabla:', {{ $ventas->count() }});
</script>
@endsection
