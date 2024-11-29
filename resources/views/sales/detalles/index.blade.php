@extends('layouts.table')

@section('title', 'Ventas')

@section('h1', 'Ventas')

@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <h3>Revisa las ventas realizadas</h3>
    <p>Aquí podrás gestionar las ventas realizadas, ver los detalles de productos vendidos y gestionar acciones asociadas a
        ellas.
    </p>
    <h4>Realizado por Pablo Jiménez, terminado por Andrés Rincón</h4>
    <br>
    <h5>Por completar:</h5>
    <p>
        <strong>1. Botón renovar (verde en columna acciones):</strong> Que habra una vista y permita renovar detalles de la
        venta.<br>
        <strong>2. Botón editar:</strong> que abre un modal que permita modificar la venta.<br>
        <strong>3. Botón cambiar estado:</strong> que cambie el estado de la venta (Ej. Entregado, Pendiente, etc.).<br>
        <strong>4. Botón ver detalles:</strong> Este permite ver los detalles de una venta, incluyendo los productos
        asociados.
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
                <th>ID</th>
                <th>Cliente</th>
                <th>Fecha de Venta</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ventas as $venta)
                @php
                    // Determinar el estado de la venta
                    if ($venta->estado == 'pendiente') {
                        $estadoClase = 'table-warning';
                    } elseif ($venta->estado == 'entregada') {
                        $estadoClase = 'table-success';
                    } else {
                        $estadoClase = 'table-danger';
                    }
                @endphp
                <tr class="{{ $estadoClase }}">
                    <td>{{ $venta->id }}</td>
                    <td>{{ $venta->cliente->nombre }}</td>
                    <td>{{ $venta->fecha_venta->format('d/m/Y') }}</td>
                    <td>${{ number_format($venta->total, 2) }}</td>
                    <td>
                        @if ($venta->estado == 'pendiente')
                            <span class="badge bg-warning">Pendiente</span>
                        @elseif ($venta->estado == 'entregada')
                            <span class="badge bg-success">Entregada</span>
                        @else
                            <span class="badge bg-danger">Cancelada</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('ventas.edit', $venta->id) }}" class="btn btn-warning"><i
                                class="fas fa-edit"></i></a>
                        <a href="{{ route('ventas.show', $venta->id) }}" class="btn btn-info"><i class="fas fa-eye"></i> Ver
                            Detalles</a>
                        <form action="{{ route('ventas.destroy', $venta->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-circle"
                                onclick="return confirm('¿Estás seguro?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('table2')
    <div class="card mb-4">
        <div class="card-body">
            Busca las ventas realizadas por un cliente específico.
            <div class="form-group mb-3">
                <label for="cliente_id">Seleccionar Cliente</label>
                <form method="GET" action="{{ route('ventas') }}#tabla-detalles">
                    <select name="cliente_id" id="cliente_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Selecciona un Cliente --</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id }}"
                                {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>

    @isset($ventaSeleccionada)
        <div id="tabla-detalles" class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                Detalles de la Venta {{ $ventaSeleccionada->id }}
            </div>
            <div class="card-body">
                <table id="datatablesSimple" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ventaSeleccionada->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->producto->nombre }}</td>
                                <td>{{ $detalle->cantidad }}</td>
                                <td>${{ number_format($detalle->precio_unitario, 2) }}</td>
                                <td>${{ number_format($detalle->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total de Venta:</strong></td>
                            <td><strong>${{ number_format($ventaSeleccionada->total, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endisset
@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (window.location.href.includes('#tabla-detalles')) {
                document.getElementById('tabla-detalles').scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    </script>
@endsection
