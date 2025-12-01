@extends('layouts.navigation')

@section('title', 'Gestión de Pedidos')

@section('main')
<div class="container-fluid px-4">
    <!-- Título y breadcrumb -->
    <h1 class="mt-4">Gestión de Pedidos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Pedidos</li>
    </ol>

    <!-- Descripción y alertas -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-4">
        <h3 class="text-primary">Gestión de Pedidos</h3>
        <p class="text-muted">Aquí puedes visualizar todos los pedidos realizados por los clientes y actualizar su estado.</p>
    </div>

    <!-- Tabla Enhanced v2 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Pedidos de Clientes
            </h6>
        </div>
        <div class="card-body">
            <!-- Encabezado: Búsqueda y Registros por página -->
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="pedidos-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search text-primary"></i> Buscar:
                    </label>
                    <input id="pedidos-table-search"
                           type="text"
                           placeholder="Buscar por cliente, producto, estado..."
                           class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="pedidos-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list text-primary"></i> Mostrar:
                    </label>
                    <select id="pedidos-table-rows-per-page" class="form-select">
                        <option value="5" selected>5 registros</option>
                        <option value="10">10 registros</option>
                        <option value="20">20 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="pedidos-table"
                       data-table="pedidos-table"
                       class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="sortable" data-type="number" data-col="0">
                                ID
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
                                Producto
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="3">
                                Descripción
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="4">
                                Fecha del Pedido
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="5">
                                Estado
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="6">
                                Respuesta
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            @if (Auth::user()->hasPermissionTo('empleado.pedidos.update'))
                                <th data-type="actions">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pedidos as $pedido)
                            <tr>
                                <td>{{ $pedido->id }}</td>
                                <td>{{ $pedido->cliente->nombrecli }}</td>
                                <td>{{ $pedido->producto->nombrepro }}</td>
                                <td>{{ $pedido->producto->descripcionpro }}</td>
                                <td>{{ $pedido->fechapedido->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge
                                        @if ($pedido->estado->nombre === 'Pendiente') bg-warning
                                        @elseif ($pedido->estado->nombre === 'Rechazado') bg-danger
                                        @elseif ($pedido->estado->nombre === 'Aprobado') bg-success
                                        @endif">
                                        {{ ucfirst($pedido->estado->nombre) }}
                                    </span>
                                </td>
                                <td>{{ $pedido->respuesta ?? 'Sin respuesta' }}</td>
                                @if (Auth::user()->hasPermissionTo('empleado.pedidos.update'))
                                    <td>
                                        @if ($pedido->estado->nombre === 'Pendiente')
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#modalActualizarPedido-{{ $pedido->id }}" title="Responder">
                                                <i class="fas fa-reply"></i> Responder
                                            </button>
                                        @else
                                            <span class="text-muted">Sin acciones</span>
                                        @endif
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
                    <div id="pedidos-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6">
                    <div id="pedidos-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales de actualizar pedido -->
    @foreach ($pedidos as $pedido)
        <div class="modal fade" id="modalActualizarPedido-{{ $pedido->id }}" tabindex="-1"
            aria-labelledby="modalActualizarPedidoLabel-{{ $pedido->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalActualizarPedidoLabel-{{ $pedido->id }}">
                            Actualizar Pedido #{{ $pedido->id }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form action="{{ route('empleado.pedidos.update', $pedido->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <label for="respuesta" class="form-label">Respuesta:</label>
                            <textarea name="respuesta" id="respuesta" class="form-control" rows="3" required>{{ $pedido->respuesta }}</textarea>

                            <label for="idestado" class="form-label mt-3">Estado:</label>
                            <select name="idestado" class="form-select" required>
                                @foreach ($estados as $estado)
                                    <option value="{{ $estado->idestado }}"
                                        {{ $pedido->idestado == $estado->idestado ? 'selected' : '' }}>
                                        {{ ucfirst($estado->nombre) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Guardar Cambios</button>
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

</div>
@endsection

@section('scripts')
<!-- Enhanced Table v2.0 -->
<script src="{{ asset('js/enhanced-table-v2.js') }}"></script>

<script>
    console.log('Vista de pedidos cargada con Enhanced Table v2.0');
    console.log('Total de pedidos en la tabla:', {{ $pedidos->count() }});
</script>
@endsection
