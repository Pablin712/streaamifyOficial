@extends('layouts.static')

@section('title', 'Editar Permiso')

@section('h1', 'Editar Permiso')

@section('introduccion')
    Actualiza este permiso con los nuevos datos. Por favor, revisa cuidadosamente los campos antes de guardar los cambios.
@endsection

@section('content')
    <form action="{{ route('permisos.update', $permiso->idperm) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Campo para ID del Permiso (Solo lectura) -->
        <div class="form-group mb-3">
            <label for="idperm">ID del Permiso</label>
            <input type="text" name="idperm" id="idperm" class="form-control" value="{{ $permiso->idperm }}" readonly>
        </div>

        <!-- Campo para Rol -->
        <div class="form-group mb-3">
            <label for="idrol">Rol</label>
            <select name="idrol" id="idrol" class="form-control" required>
                @foreach($roles as $rol)
                    <option value="{{ $rol->idrol }}" {{ $permiso->idrol === $rol->idrol ? 'selected' : '' }}>
                        {{ $rol->detallerol }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Campo para Nombre de la Tabla -->
        <div class="form-group mb-3">
            <label for="name_table">Tabla</label>
            <input type="text" name="name_table" id="name_table" class="form-control" value="{{ $permiso->name_table }}" maxlength="50" required>
        </div>

        <!-- Campo para Acción -->
        <div class="form-group mb-3">
            <label for="accion">Acción</label>
            <input type="text" name="accion" id="accion" class="form-control" value="{{ $permiso->accion }}" maxlength="50" required>
        </div>

        <!-- Campo para Permitido -->
        <div class="form-group mb-3">
            <label for="allowed">¿Permitido?</label>
            <select name="allowed" id="allowed" class="form-control" required>
                <option value="1" {{ $permiso->allowed ? 'selected' : '' }}>Sí</option>
                <option value="0" {{ !$permiso->allowed ? 'selected' : '' }}>No</option>
            </select>
        </div>

        <!-- Botón para enviar el formulario -->
        <button type="submit" class="btn btn-warning">Actualizar</button>
    </form>
@endsection

@section('pie')
    <p>¿No deseas realizar cambios? Regresa al listado de permisos:</p>
    <a href="{{ route('permisos') }}" class="btn btn-secondary">Volver a Permisos</a>
@endsection
