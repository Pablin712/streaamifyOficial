@extends('layouts.static')

@section('title', 'Editar Cuenta')

@section('h1', 'Editar Cuenta de suscripción')
@section('breadcrumb')
    <a href="{{ route('cuentas') }}">Cuentas</a>
@endsection
@section('breadcrumb2')
    Editar Cuenta
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
    Actualiza los datos de esta cuenta. Por favor, revisa cuidadosamente todos los campos antes de guardar los cambios.
@endsection

@section('content')
    <form action="{{ route('cuentas.update', $cuenta->idcue) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Campo para el ID de la Cuenta (solo lectura) -->
        <div class="form-group mb-3">
            <label for="idcue">ID de Cuenta</label>
            <input type="text" name="idcue" id="idcue" class="form-control" value="{{ $cuenta->idcue }}" readonly>
        </div>

        <!-- Campo para el ID del Valor -->
        <div class="form-group mb-3">
            <label for="idval">ID del Valor</label>
            <select name="idval" id="idval" class="form-control" required>
                @foreach ($valores as $valor)
                    <option value="{{ $valor->idval }}" {{ $cuenta->idval == $valor->idval ? 'selected' : '' }}>
                        {{ $valor->idval }} - {{ $valor->idser }} ({{ $valor->proveedor->nombrepro }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Campo para el nombre de usuario -->
        <div class="form-group mb-3">
            <label for="usuariocue">Usuario</label>
            <input type="text" name="usuariocue" id="usuariocue" class="form-control" value="{{ $cuenta->usuariocue }}"
                required>
        </div>

        <!-- Campo para la contraseña -->
        <div class="form-group mb-3">
            <label for="contrasenacue">Contraseña</label>
            <input type="password" name="contrasenacue" id="contrasenacue" class="form-control"
                value="{{ $cuenta->contrasenacue }}" required>
        </div>

        <!-- Campo para la fecha de vencimiento -->
        <div class="form-group mb-3">
            <label for="fechavencue">Fecha de Vencimiento</label>
            <input type="date" name="fechavencue" id="fechavencue" class="form-control"
                value="{{ $cuenta->fechavencue->format('Y-m-d') }}" required>
        </div>

        <!-- Campo para indicar si la cuenta está activa -->
        <div class="form-group mb-3">
            <label for="caidacue">¿Cuenta Activa?</label>
            <select name="caidacue" id="caidacue" class="form-control" required>
                <option value="0" {{ $cuenta->caidacue == 0 ? 'selected' : '' }}>Sí</option>
                <option value="1" {{ $cuenta->caidacue == 1 ? 'selected' : '' }}>No</option>
            </select>
        </div>

        <!-- Botón para guardar los cambios -->
        <button type="submit" class="btn btn-warning">Actualizar</button>
    </form>
@endsection

@section('pie')
    <p>¿No deseas realizar cambios? Regresa al listado de cuentas:</p>
    <a href="{{ route('cuentas') }}" class="btn btn-secondary">Volver a Cuentas</a>
@endsection
