@extends('layouts.table')

@section('title')
    Permisos
@endsection

@section('h1')
    Permisos
@endsection

@section('descripcion')
    <!-- Mostrar mensaje de éxito si existe -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <h3>Gestiona los permisos asignados</h3>
    <p>En esta pantalla se pueden visualizar y administrar los permisos asignados a cada rol.</p>
    <p>Incluye un CRUD para gestionar permisos y su relación con roles.</p>
@endsection

@section('tablename', 'Permisos')

@section('btncrear')
    <a href="{{ route('permisos.create') }}" class="btn btn-primary">Crear Permiso</a>
@endsection

@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Rol</th>
                <th>Tabla</th>
                <th>Acción</th>
                <th>Permitido</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($permisos as $permiso)
                <tr>
                    <td>{{ $permiso->idperm }}</td>
                    <td>{{ $permiso->rol->detallerol }}</td>
                    <td>{{ $permiso->name_table }}</td>
                    <td>{{ $permiso->accion }}</td>
                    <td>{{ $permiso->allowed ? 'Sí' : 'No' }}</td>
                    <td>
                        <a href="{{ route('permisos.edit', $permiso->idperm) }}" class="btn btn-warning"><i
                                class="fas fa-edit"></i></a>
                        <form action="{{ route('permisos.destroy', $permiso->idperm) }}" method="POST"
                            style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-circle"
                                onclick="return confirm('¿Eliminar este permiso?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No hay permisos disponibles.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
