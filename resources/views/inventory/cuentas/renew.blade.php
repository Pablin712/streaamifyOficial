@extends('layouts.static')

@section('title', 'Renovar Cuenta')

@section('h1', 'Renovar Cuenta')
@section('breadcrumb')
    <a href="{{ route('cuentas') }}">Cuentas</a>
@endsection
@section('breadcrumb2')
    Renovar Cuenta
@endsection
@section('introduccion')
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    Aquí puedes actualizar los detalles para la renovación de la cuenta seleccionada. Asegúrate de no modificar el ID de la
    cuenta ni el valor.
@endsection

@section('content')
    <form action="{{ route('cuentas.update', $cuenta->idcue) }}" method="POST">
        @csrf
        @method('PUT') <!-- Especificamos que es un formulario de actualización -->

        <!-- Campo para el ID de la cuenta (solo lectura) -->
        <div class="form-group mb-3">
            <label for="idcue">ID de Cuenta</label>
            <input type="text" name="idcue" id="idcue" class="form-control" maxlength="20"
                value="{{ old('idcue', $cuenta->idcue) }}" readonly>
        </div>

        <!-- Selección del Valor (solo lectura) -->
        <div class="form-group mb-3">
            <label for="idval">ID del Valor</label>
            <input type="text" name="idval" id="idval" class="form-control" value="{{ $cuenta->idval }}" readonly>
        </div>

        <!-- Campo para el nombre de usuario de la cuenta -->
        <div class="form-group mb-3">
            <label for="usuariocue">Usuario</label>
            <input type="text" name="usuariocue" id="usuariocue" class="form-control"
                value="{{ old('usuariocue', $cuenta->usuariocue) }}" required>
        </div>

        <!-- Campo para la contraseña de la cuenta -->
        <div class="form-group mb-3">
            <label for="contrasenacue">Contraseña</label>
            <input type="text" name="contrasenacue" id="contrasenacue" class="form-control"
                value="{{ old('contrasenacue', $cuenta->contrasenacue) }}" required>
            <small>Salta este apartado si no deseas cambiar la contraseña</small>
        </div>

        <!-- Fecha de vencimiento de la cuenta -->
        <div class="form-group mb-3">
            <label for="fechavencue">Fecha de Vencimiento</label>
            <input type="date" name="fechavencue" id="fechavencue" class="form-control"
                value="{{ old('fechavencue', \Carbon\Carbon::parse($cuenta->fechavencue)->addMonth()->format('Y-m-d')) }}"
                required>
        </div>

        <!-- Campo para indicar si la cuenta está activa -->
        <div class="form-group mb-3">
            <label for="caidacue">¿Cuenta Activa?</label>
            <select name="caidacue" id="caidacue" class="form-control" required>
                <option value="0" {{ $cuenta->caidacue == 0 ? 'selected' : '' }}>Sí</option>
                <option value="1" {{ $cuenta->caidacue == 1 ? 'selected' : '' }}>No</option>
            </select>
        </div>

        <!-- Botón para llenar el Costo (solo si se desea actualizar costo) -->
        <div class="form-group mb-3">
            <label for="caidacue"><strong>Agregar Costo de Cuenta</strong></label><br>
            <label for="descripcioncos">Descripción del Costo</label>
            <input type="text" name="descripcioncos" id="descripcioncos" class="form-control"
                value="{{ old('descripcioncos', $cuenta->costo->descripcioncos ?? '') }}">
            <label for="montocos">Monto del Costo</label>
            <input type="number" name="montocos" id="montocos" class="form-control"
                value="{{ old('montocos', $cuenta->costo->montocos ?? '') }}" step="0.01">
        </div>
        <button type="submit" class="btn btn-success">Renovar Cuenta</button>
    </form>
@endsection

@section('pie')
    <p>¿No deseas actualizar la cuenta? Vuelve a la página de listado:</p>
    <a href="{{ route('cuentas') }}" class="btn btn-secondary">Volver a Cuentas</a>
@endsection
