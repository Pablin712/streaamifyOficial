@extends('layouts.static')

@section('title', 'Crear Rol')

@section('h1')
    Crear Rol
@endsection
@section('breadcrumb')
    <a href="{{ route('roles.index') }}">Roles</a>
@endsection
@section('breadcrumb2')
    Crear Rol
@endsection
@section('introduccion')
    Aquí puedes crear un nuevo Rol. Por favor, llena todos los campos requeridos y
    asegúrate de que la información sea correcta.
@endsection
@section('content')
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <form action="{{ route('roles.store') }}" method="POST">
        @csrf
        <div class="form-group mb-4">
            <label for="name">Nombre</label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Ingrese el nombre del rol"
                value="{{ old('name') }}">
            @error('name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <h2 class="h3 mb-3">Lista de permisos</h2>
        @foreach ($permissions as $permission)
            <div class="col-md-4 mb-2">
                <label>
                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="form-check-input">
                    {{ $permission->name }}
                </label>
            </div>
        @endforeach
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Crear Rol</button>
        </div>
    </form>
@endsection

@section('pie')
    <p>¿No deseas crear un permiso? Vuelve a la página de listado:</p>
    <a href="{{ route('roles.index') }}" class="btn btn-secondary">Volver a Roles</a>
@endsection
