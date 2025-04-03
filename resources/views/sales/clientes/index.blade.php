@extends('layouts.table')
@section('title')
    Clientes
@endsection
@section('h1', 'Clientes')
@section('breadcrumb')
    Clientes
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <h3>Información de clientes</h3>
    <p>Muestra la tabla de clientes, el número de usuarios que posee y lo facturado en el mes actual.</p>
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
    </div>
@endsection
@section('tablename', 'Clientes')
@section('btncrear')
    @if (Auth::user()->hasPermissionTo('clientes.create'))
        <a href="{{ route('clientes.create') }}" class="btn btn-primary mb-3">Crear Cliente</a>
        <a href="{{ route('clientes.export') }}" class="btn btn-primary mb-3">Exportar CSV</a>
    @endif
@endsection
@section('table1')
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
                @if (Auth::user()->hasAnyPermission(['clientes.edit', 'clientes.destroy']))
                    <th>Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->idcli }}</td>
                    <td>{{ $cliente->nombrecli }}</td>
                    <td>{{ $cliente->telefonocli }}</td>
                    <td>{{ $cliente->email ?? 'Ninguno' }}</td>
                    <td>{{ $cliente->viewClienteUsuario->usuarios ?? 0 }}</td>
                    <td>${{ $cliente->viewClienteUsuario->facturado ?? 0 }}</td>
                    <td>${{ $cliente->saldo }}</td>
                    <td>
                        @if ($cliente->email && $cliente->password)
                            <span class="badge bg-success">Sí</span>
                        @else
                            <span class="badge bg-danger">No</span>
                        @endif
                    </td>
                    @if (Auth::user()->hasAnyPermission(['clientes.edit', 'clientes.destroy']))
                        <td>
                            @if (Auth::user()->hasPermissionTo('clientes.edit'))
                                <a href="{{ route('clientes.edit', $cliente->idcli) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            @if ($cliente->usuarios->isEmpty())
                                @if (Auth::user()->hasPermissionTo('clientes.destroy'))
                                    <form action="{{ route('clientes.destroy', $cliente->idcli) }}" method="POST"
                                        style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-circle"
                                            onclick="return confirm('¿Estás seguro?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection