@extends('layouts.static')

@section('title', 'Crear Cuenta')

@section('h1', 'Crear Cuenta')
@section('breadcrumb')
    <a href="{{ route('cuentas') }}">Cuentas</a>
@endsection
@section('breadcrumb2')
    Registrar Cuenta
@endsection
@section('introduccion')
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    Aquí puedes agregar una nueva cuenta para llenar el stock de cuentas disponibles para los servicios.
    Asegúrate de ingresar todos los campos correctamente.
    En esta vista, se agrega una cuenta a la tabla cuentas.
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
                    <option value="{{ $valor->idval }}">{{ $valor->idser }} - 
                        {{ $valor->proveedor->nombrepro }} ({{ $valor->mesesval }}m)
                    </option>
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
            <input type="text" name="contrasenacue" id="contrasenacue" class="form-control" required>
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

        <!-- Botón para abrir el modal de Costo -->
        <div class="form-group mb-3">
            <label for="caidacue"><strong>Agregar Costo de Cuenta</strong></label><br>
            <label for="descripcioncos">Descripción del Costo</label>
            <input type="text" name="descripcioncos" id="descripcioncos" class="form-control">
            <label for="montocos">Monto del Costo</label>
            <input type="number" name="montocos" id="montocos" class="form-control" step="0.01" min=0>
        </div>
        <button type="submit" class="btn btn-success">Guardar Cuenta</button>
    </form>
@endsection
@section('pie')
    <p>¿No deseas agregar una cuenta al stock? Vuelve a la página de listado:</p>
    <a href="{{ route('cuentas') }}" class="btn btn-secondary">Volver a Cuentas</a>
@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#idval').select2({
                placeholder: "Selecciona un valor",
                width: '100%'
            });
        });
    </script>
@endsection
