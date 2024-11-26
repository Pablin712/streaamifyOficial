@extends('layouts.table')
@section('title')
Clientes
@endsection
@section('h1','Clientes')
@section('descripcion')
    <h3>Revisa el inventario</h3>
    <p>a esta pantalla acceden ciertos usuarios</p>
    <h3>Tabla de clientes</h3>
    <p>muestra la tabla de clientes, un crud</p>
@endsection
@section('tablename', 'Clientes')
@section('table1')
<h1>Clientes</h1>
<a href="{{ route('clientes.create') }}" class="btn btn-primary mb-3">Crear Cliente</a>
<table id="datatablesSimple" class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($clientes as $cliente)
            <tr>
                <td>{{ $cliente->idcli }}</td>
                <td>{{ $cliente->nombrecli }}</td>
                <td>{{ $cliente->telefonocli }}</td>
                <td>
                    <a href="{{ route('clientes.edit', $cliente->idcli) }}" class="btn btn-warning btn-sm">Editar</a>
                    <form action="{{ route('clientes.destroy', $cliente->idcli) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection