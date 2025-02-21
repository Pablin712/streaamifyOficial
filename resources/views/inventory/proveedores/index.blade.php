@extends('layouts.table')
@section('title')
    Proveedores
@endsection
@section('h1', 'Proveedores')
@section('breadcrumb')
    Proveedores
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Agrega un nuevo proveedor al negocio, para poder contactarlo y adquirir sus cuentas.</p>
@endsection
@section('tablename', 'Proveedores')
@section('table1')
    <h1>Proveedores</h1>
    @if (Auth::user()->hasPermissionTo('proveedores.create'))
        <a href="{{ route('proveedores.create') }}" class="btn btn-primary mb-3">Crear Proveedor</a>
    @endif
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                @if (Auth::user()->hasAnyPermission(['proveedores.edit', 'proveedores.destroy']))
                    <th>Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($proveedores as $proveedor)
                <tr>
                    <td>{{ $proveedor->idpro }}</td>
                    <td>{{ $proveedor->nombrepro }}</td>
                    <td>{{ $proveedor->telefonopro }}</td>
                    @if (Auth::user()->hasAnyPermission(['proveedores.edit', 'proveedores.destroy']))
                        <td>
                            @if (Auth::user()->hasPermissionTo('proveedores.edit'))
                                <a href="{{ route('proveedores.edit', $proveedor->idpro) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            @if (Auth::user()->hasPermissionTo('proveedores.destroy'))
                                <form action="{{ route('proveedores.destroy', $proveedor->idpro) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle" onclick="return confirm('¿Estás seguro?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection