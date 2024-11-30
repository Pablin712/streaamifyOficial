@extends('layouts.static')

@section('title', 'Crear Cuenta')

@section('h1', 'Crear Cuenta')

@section('introduccion')
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    Aquí puedes agregar una nueva cuenta para llenar el stock de cuentas disponibles para los servicios.
    Asegúrate de ingresar todos los campos correctamente.
    En esta vista, se agrega una cuenta a la tabla cuentas.
    <h5>Por completar:</h5>
    <strong>input costo y descripcion costo: </strong> que al registrar la nueva cuenta, se pueda registrar de una vez el
    costo,
    solo con descripcion costo y el monto que se pagó, y se registra tanto la cuenta como el costo, esto puede ser
    solucionado
    con modal, inputs con sentencia. <Strong>Nota:</Strong> El registro del costo es voluntario, por lo que se puede
    registrar
    una cuenta sin necesidad de registrar el costo, solo la cuenta.
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
                    <option value="{{ $valor->idval }}">{{ $valor->idval }} - {{ $valor->idser }}
                        ({{ $valor->proveedor->nombrepro }})
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

        <!-- Botón para abrir el modal de Costo -->
        <div class="form-group mb-3">
            <label for="caidacue"><strong>Agregar Costo de Cuenta</strong></label><br>
            <label for="descripcioncos">Descripción del Costo</label>
            <input type="text" name="descripcioncos" id="descripcioncos" class="form-control">
            <label for="montocos">Monto del Costo</label>
            <input type="number" name="montocos" id="montocos" class="form-control" step="0.01">
        </div>
        <button type="submit" class="btn btn-success">Guardar Cuenta</button>
    </form>
@endsection


@section('pie')
    <p>¿No deseas agregar una cuenta al stock? Vuelve a la página de listado:</p>
    <a href="{{ route('cuentas') }}" class="btn btn-secondary">Volver a Cuentas</a>
@endsection
