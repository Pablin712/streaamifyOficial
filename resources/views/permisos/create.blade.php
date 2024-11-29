@extends('layouts.static')

@section('title', 'Crear Permiso')

@section('h1')
    Crear Permiso
@endsection

@section('introduccion')
    Aquí puedes crear un nuevo permiso. Por favor, llena todos los campos requeridos y asegúrate de que la información sea correcta.
@endsection
@section('content')
@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif
    <form action="{{ route('permisos.store') }}" method="POST">
        @csrf
        <!-- Campo para seleccionar Rol -->
        <div class="form-group mb-3">
            <label for="idrol">Rol</label>
            <select name="idrol" id="idrol" class="form-control" required>
                <option value="" disabled selected>Selecciona un rol</option>
                @foreach($roles as $rol)
                    <option value="{{ $rol->idrol }}">{{ $rol->detallerol }}</option>
                @endforeach
            </select>
        </div>

        <!-- Campo para Nombre de la Tabla -->
        <div class="form-group mb-3">
            <label for="name_table">Tabla</label>
            <input type="text" name="name_table" id="name_table" class="form-control" maxlength="50" required placeholder="Ejemplo: empleados">
        </div>

        <!-- Campo para Acción -->
        <div class="form-group mb-3">
            <label for="accion">Acción</label>
            <input type="text" name="accion" id="accion" class="form-control" maxlength="50" required placeholder="Ejemplo: crear, editar, eliminar">
        </div>

        <!-- Campo para Permitido -->
        <div class="form-group mb-3">
            <label for="allowed">¿Permitido?</label>
            <select name="allowed" id="allowed" class="form-control" required>
                <option value="" disabled selected>Selecciona</option>
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </div>

        <!-- Botón para enviar el formulario -->
        <button type="submit" class="btn btn-success">Guardar</button>
    </form>
@endsection

@section('pie')
    <p>¿No deseas crear un permiso? Vuelve a la página de listado:</p>
    <a href="{{ route('permisos') }}" class="btn btn-secondary">Volver a Permisos</a>
@endsection
