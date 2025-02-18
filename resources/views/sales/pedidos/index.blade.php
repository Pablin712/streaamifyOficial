@extends('layouts.table')

@section('title')
    Gestión de Pedidos
@endsection

@section('h1', 'Gestión de Pedidos')
@section('breadcrumb')
    Pedidos
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Aquí puedes visualizar todos los pedidos realizados por los clientes y actualizar su estado.</p>
@endsection

@section('tablename', 'Pedidos de Clientes')

@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Producto</th>
                <th>Descripción</th>
                <th>Fecha del Pedido</th>
                <th>Estado</th>
                <th>Respuesta</th>
                @can('empleado.pedidos.update')
                    <th>Acciones</th>
                @endcan
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
                        <span
                            class="badge 
                            @if ($pedido->estado->nombre === 'Pendiente') bg-warning 
                            @elseif ($pedido->estado->nombre === 'Rechazado') bg-danger 
                            @elseif ($pedido->estado->nombre === 'Aprobado') bg-success @endif">
                            {{ ucfirst($pedido->estado->nombre) }}
                        </span>
                    </td>
                    <td>{{ $pedido->respuesta ?? 'Sin respuesta' }}</td>
                    @can('empleado.pedidos.update')
                        <td>
                            @if ($pedido->estado->nombre === 'Pendiente')
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#modalActualizarPedido-{{ $pedido->id }}">
                                    Responder
                                </button>
                            @else
                                <span class="text-muted">Sin acciones</span>
                            @endif
                        </td>
                    @endcan
                </tr>

                <!-- Modal para actualizar el pedido -->
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
        </tbody>
    </table>
@endsection
