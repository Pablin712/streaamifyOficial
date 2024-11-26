@extends('layouts.static')

@section('title', 'Editar Servicio')

@section('h1', 'Editar Servicio')

@section('introduccion')
    Actualiza este servicio con los nuevos datos. Por favor, revisa cuidadosamente los campos antes de guardar los cambios.
@endsection

@section('content')
    <form action="{{ route('servicios.update', $servicio->idser) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Campo para ID del Servicio (Solo lectura) -->
        <div class="form-group mb-3">
            <label for="idser">ID del Servicio</label>
            <input type="text" name="idser" id="idser" class="form-control" value="{{ $servicio->idser }}" readonly>
        </div>

        <!-- Campo para Nombre del Servicio -->
        <div class="form-group mb-3">
            <label for="nombreser">Nombre del Servicio (Máx: 20 caracteres)</label>
            <input type="text" name="nombreser" id="nombreser" class="form-control" value="{{ $servicio->nombreser }}" maxlength="20" required>
        </div>

        <!-- Campo para Precio Completo -->
        <div class="form-group mb-3">
            <label for="completoser">Precio Completo</label>
            <input type="number" name="completoser" id="completoser" class="form-control" value="{{ $servicio->completoser }}" step="0.01">
        </div>

        <!-- Campo para Precio Individual -->
        <div class="form-group mb-3">
            <label for="precioser">Precio Individual</label>
            <input type="number" name="precioser" id="precioser" class="form-control" value="{{ $servicio->precioser }}" step="0.01">
        </div>

        <!-- Campo para Precio Combo -->
        <div class="form-group mb-3">
            <label for="comboser">Precio Combo</label>
            <input type="number" name="comboser" id="comboser" class="form-control" value="{{ $servicio->comboser }}" step="0.01">
        </div>

        <!-- Campo para Precio Reventa Pantalla -->
        <div class="form-group mb-3">
            <label for="reventaser">Reventa Pantalla</label>
            <input type="number" name="reventaser" id="reventaser" class="form-control" value="{{ $servicio->reventaser }}" step="0.01">
        </div>

        <!-- Campo para Precio Reventa Cuenta -->
        <div class="form-group mb-3">
            <label for="revcompser">Reventa Cuenta</label>
            <input type="number" name="revcompser" id="revcompser" class="form-control" value="{{ $servicio->revcompser }}" step="0.01">
        </div>

        <!-- Botón para enviar el formulario -->
        <button type="submit" class="btn btn-warning">Actualizar</button>
    </form>
@endsection
@section('pie')
    <p>¿No deseas realizar cambios? Regresa al listado de servicios:</p>
    <a href="{{ route('servicios') }}" class="btn btn-secondary">Volver a Servicios</a>
@endsection
