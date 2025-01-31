@extends('layouts.table')
@section('title')
    Clientes
@endsection
@section('h1', 'Clientes')
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <h3>Información de clientes</h3>
    <p>Muestra la tabla de clientes, el número de usuarios que posee y lo facturado en el mes actual.</p>
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
                <th>Correo</th>
                <th>Usuarios</th>
                <th>Facturado este mes</th>
                <th>Saldo</th>
                <th>Autenticado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->idcli }}</td>
                    <td>{{ $cliente->nombrecli }}</td>
                    <td>{{ $cliente->telefonocli }}</td>
                    <td>{{ $cliente->email ?? 'Ninguno' }}</td>
                    <td>{{ $cliente->usuarios }}</td>
                    <td>${{ $cliente->facturado }}</td>
                    <td>${{ $cliente->saldo}}</td>
                    <td>
                        @if($cliente->email && $cliente->password)
                            <span class="badge bg-success">Sí</span>
                        @else
                            <span class="badge bg-danger">No</span>
                        @endif
                    </td>                    
                    <td>
                        <a href="{{ route('clientes.edit', $cliente->idcli) }}" class="btn btn-warning  "><i
                                class="fas fa-edit"></i></a>
                        @if ($cliente->usuarios == 0)
                            <form action="{{ route('clientes.destroy', $cliente->idcli) }}" method="POST"
                                style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-circle"
                                    onclick="return confirm('¿Estás seguro?')"><i class="fas fa-trash"></i></button>
                            </form>
                        @endif

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
