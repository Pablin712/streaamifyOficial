@extends('layouts.table')

@section('title')
    Roles
@endsection

@section('h1')
    Roles
@endsection
@section('breadcrumb')
    Roles
@endsection
@section('descripcion')
    <!-- Mostrar mensaje de éxito si existe -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <h3>Gestiona los roles</h3>
    <p>En esta pantalla se pueden visualizar y administrar los roles de los empleados.</p>
    <p>Incluye un CRUD para gestionar roles y su relación con permisos.</p>
@endsection

@section('tablename', 'Permisos')

@section('btncrear')
    @if (Auth::user()->hasPermissionTo('roles.store'))
        <a href="{{ route('roles.create') }}" class="btn btn-primary">Nuevo Rol</a>
    @endif
@endsection

@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Rol</th>
                @if (Auth::user()->hasAnyPermission(['roles.update', 'roles.destroy']))
                    <th colspan="2">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($roles as $rol)
                <tr>
                    <td>{{ $rol->id }}</td>
                    <td>{{ $rol->name }}</td>
                    @if (Auth::user()->hasAnyPermission(['roles.update', 'roles.destroy']))
                        <td>
                            @if (Auth::user()->hasPermissionTo('roles.update'))
                                <a href="{{ route('roles.edit', $rol->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            @if (Auth::user()->hasPermissionTo('roles.destroy'))
                                <form action="{{ route('roles.destroy', $rol->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle"
                                        onclick="return confirm('¿Eliminar este rol?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No hay permisos disponibles.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection