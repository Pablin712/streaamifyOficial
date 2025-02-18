@extends('layouts.static')

@section('title', 'Editar Rol')

@section('h1', 'Editar Rol')
@section('breadcrumb')
    <a href="{{ route('roles.index') }}">Roles</a>
@endsection
@section('breadcrumb2')
    Editar Rol
@endsection
@section('introduccion')
    Actualiza este rol con los nuevos datos. Por favor, revisa
    cuidadosamente los campos antes de guardar los cambios.
@endsection
@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <form action="{{ route('roles.update', $rol->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Sección Nombre --}}
        <div class="form-group mb-4">
            <label for="name"><strong>Nombre del Rol</strong></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Ingrese el nombre del rol"
                value="{{ old('name', $rol->name) }}">
            @error('name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- Sección Lista de Permisos --}}
        <h2 class="h3 mb-3">Lista de permisos</h2>

        <div class="row">
            @foreach ($permissions as $permission)
                <div class="col-md-4 mb-2">
                    <div class="form-check">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="form-check-input"
                            id="permission{{ $permission->id }}"
                            {{ $rol->permissions->contains($permission->id) ? 'checked' : '' }}>
                        <label class="form-check-label" for="permission{{ $permission->id }}">
                            {{ $permission->name }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Botón Actualizar Rol con separación --}}
        <div class="mt-4">
            <button type="submit" class="btn btn-success">Actualizar Rol</button>
        </div>
    </form>
@endsection
@section('pie')
    <p>¿No deseas realizar cambios? Regresa al listado de roles:</p>
    <a href="{{ route('roles.index') }}" class="btn btn-secondary">Volver a Roles</a>
@endsection
