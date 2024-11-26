@extends('layouts.static')

@section('title', 'Crear Cuenta')

@section('h1', 'Crear Cuenta')

@section('introduccion')
    Aquí puedes agregar una nueva cuenta para llenar el stock de cuentas disponibles para los servicios. 
    Asegúrate de ingresar todos los campos correctamente.
@endsection

@section('content')
    <form action="{{ route('cuentas.store') }}" method="POST">
        @csrf

        <!-- Campo para el ID de la cuenta -->
        <div class="form-group mb-3">
            <label for="idcue">ID de Cuenta</label>
            <input type="text" name="idcue" id="idcue" class="form-control" maxlength="20" required>
        </div>

        <!-- Selección del Valor -->
        <div class="form-group mb-3">
            <label for="idval">ID del Valor</label>
            <select name="idval" id="idval" class="form-control" required>
                @foreach ($valores as $valor)
                    <option value="{{ $valor->idval }}">{{ $valor->idval }} - {{ $valor->idser }} ({{ $valor->proveedor->nombrepro }})</option>
                @endforeach
            </select>
        </div>

        <!-- Campo para el nombre de usuario de la cuenta -->
        <div class="form-group mb-3">
            <label for="usuariocue">Usuario</label>
            <input type="text" name="usuariocue" id="usuariocue" class="form-control" required>
        </div>

        <!-- Campo para la contraseña de la cuenta -->
        <div class="form-group mb-3">
            <label for="contrasenacue">Contraseña</label>
            <input type="password" name="contrasenacue" id="contrasenacue" class="form-control" required>
        </div>

        <!-- Fecha de vencimiento de la cuenta -->
        <div class="form-group mb-3">
            <label for="fechavencue">Fecha de Vencimiento</label>
            <input type="date" name="fechavencue" id="fechavencue" class="form-control" required>
        </div>

        <!-- Campo para indicar si la cuenta está activa -->
        <div class="form-group mb-3">
            <label for="caidacue">¿Cuenta Activa?</label>
            <select name="caidacue" id="caidacue" class="form-control" required>
                <option value="0">Sí</option>
                <option value="1">No</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Guardar</button>
    </form>
@endsection

@section('pie')
    <p>¿No deseas agregar una cuenta al stock? Vuelve a la página de listado:</p>
    <a href="{{ route('cuentas') }}" class="btn btn-secondary">Volver a Cuentas</a>
@endsection
