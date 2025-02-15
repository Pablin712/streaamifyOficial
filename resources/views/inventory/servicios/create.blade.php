@extends('layouts.static')

@section('title', 'Crear Servicio')

@section('h1')
    Crear Servicio
@endsection
@section('breadcrumb')
    <a href="{{ route('servicios') }}">Servicios</a>
@endsection
@section('breadcrumb2')
    Registrar Servicio
@endsection
@section('introduccion')
    Aquí puedes crear un nuevo servicio. Por favor, llena todos los campos requeridos y asegúrate de que la información sea correcta.
@endsection

@section('content')
    <form action="{{ route('servicios.store') }}" method="POST">
        @csrf
        <!-- Campo para ID del Servicio -->
        <div class="form-group mb-3">
            <label for="idser">ID del Servicio (Máx: 10 caracteres)</label>
            <input type="text" name="idser" id="idser" class="form-control" maxlength="10" required placeholder="Ingresa el ID del servicio">
        </div>

        <!-- Campo para Nombre del Servicio -->
        <div class="form-group mb-3">
            <label for="nombreser">Nombre del Servicio (Máx: 20 caracteres)</label>
            <input type="text" name="nombreser" id="nombreser" class="form-control" maxlength="20" required placeholder="Ejemplo: Netflix">
        </div>

        <!-- Campo para Precio Completo -->
        <div class="form-group mb-3">
            <label for="completoser">Precio Completo</label>
            <input type="number" name="completoser" id="completoser" class="form-control" step="0.01" placeholder="Ingresa el precio completo">
        </div>

        <!-- Campo para Precio Individual -->
        <div class="form-group mb-3">
            <label for="precioser">Precio Individual</label>
            <input type="number" name="precioser" id="precioser" class="form-control" step="0.01" placeholder="Ingresa el precio individual">
        </div>

        <!-- Campo para Precio Combo -->
        <div class="form-group mb-3">
            <label for="comboser">Precio Combo</label>
            <input type="number" name="comboser" id="comboser" class="form-control" step="0.01" placeholder="Ingresa el precio combo">
        </div>

        <!-- Campo para Precio Reventa Pantalla -->
        <div class="form-group mb-3">
            <label for="reventaser">Reventa Pantalla</label>
            <input type="number" name="reventaser" id="reventaser" class="form-control" step="0.01" placeholder="Precio por pantalla en reventa">
        </div>

        <!-- Campo para Precio Reventa Cuenta -->
        <div class="form-group mb-3">
            <label for="revcompser">Reventa Cuenta</label>
            <input type="number" name="revcompser" id="revcompser" class="form-control" step="0.01" placeholder="Precio por cuenta en reventa">
        </div>

        <!-- Botón para enviar el formulario -->
        <button type="submit" class="btn btn-success">Guardar</button>
    </form>
@endsection

@section('pie')
    <p>¿No deseas crear un servicio? Vuelve a la página de listado:</p>
    <a href="{{ route('servicios') }}" class="btn btn-secondary">Volver a Servicios</a>
@endsection
