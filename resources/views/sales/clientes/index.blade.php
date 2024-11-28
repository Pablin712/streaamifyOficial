@extends('layouts.table')
@section('title')
Clientes
@endsection
@section('h1','Clientes')
@section('descripcion')
    <h3>Revisa el inventario</h3>
    <p>A esta pantalla acceden ciertos usuarios. Muestra la tabla de clientes, un crud</p>
    <h4>Realizado por Pablo Jiménez, terminado por Andrés Rincón</h4>
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
                    <a href="{{ route('clientes.edit', $cliente->idcli) }}" class="btn btn-warning  " ><i class="fas fa-edit"></i></a>
                    <form action="{{ route('clientes.destroy', $cliente->idcli) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-circle" onclick="return confirm('¿Estás seguro?')"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection