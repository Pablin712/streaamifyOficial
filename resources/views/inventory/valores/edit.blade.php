@extends('layouts.static')
@section('title', 'Editar Valor')

@section('h1', 'Editar Valor de Servicio')
@section('breadcrumb')
    <a href="{{ route('valores') }}">Valores</a>
@endsection
@section('breadcrumb2')
    Editar Valor de Servicio
@endsection
@section('introduccion')
    Actualiza este valor de servicio con los nuevos datos. Por favor, revisa cuidadosamente los campos antes de guardar los
    cambios.
@endsection

@section('content')
    <form action="{{ route('valores.update', $valor->idval) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Campo para ID del Valor (solo lectura) -->
        <div class="form-group mb-3">
            <label for="idval">ID del Valor</label>
            <input type="text" name="idval" id="idval" class="form-control" value="{{ $valor->idval }}" readonly>
        </div>

        <!-- Campo para ID del Servicio -->
        <div class="form-group mb-3">
            <label for="idser">ID del Servicio</label>
            <select name="idser" id="idser" class="form-control" required>
                @foreach ($servicios as $servicio)
                    <option value="{{ $servicio->idser }}" {{ $valor->idser == $servicio->idser ? 'selected' : '' }}>
                        {{ $servicio->nombreser }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Campo para seleccionar el Proveedor -->
        <div class="form-group mb-3">
            <label for="idpro">Proveedor</label>
            <select name="idpro" id="idpro" class="form-control" required>
                @foreach ($proveedores as $proveedor)
                    <option value="{{ $proveedor->idpro }}" {{ $valor->idpro == $proveedor->idpro ? 'selected' : '' }}>
                        {{ $proveedor->nombrepro }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Campo para Costo -->
        <div class="form-group mb-3">
            <label for="costoval">Costo</label>
            <input type="number" name="costoval" id="costoval" class="form-control" value="{{ $valor->costoval }}"
                step="0.01" required>
        </div>

        <!-- Campo para Pantallas Mínimas -->
        <div class="form-group mb-3">
            <label for="pantminval">Pantallas Mínimas</label>
            <input type="number" name="pantminval" id="pantminval" class="form-control" value="{{ $valor->pantminval }}"
                required>
        </div>

        <!-- Campo para Pantallas Máximas -->
        <div class="form-group mb-3">
            <label for="pantmaxval">Pantallas Máximas</label>
            <input type="number" name="pantmaxval" id="pantmaxval" class="form-control" value="{{ $valor->pantmaxval }}"
                required>
        </div>

        <!-- Campo para Meses -->
        <div class="form-group mb-3">
            <label for="mesesval">Meses</label>
            <input type="number" name="mesesval" id="mesesval" class="form-control" value="{{ $valor->mesesval }}"
                required>
        </div>

        <!-- Campo para Bot -->
        <div class="form-group mb-3">
            <label for="bot">Bot (URL)</label>
            <input type="text" name="bot" id="bot" class="form-control" value="{{ $valor->bot }}"
                placeholder="Ingresa la URL del bot (opcional)">
        </div>

        <!-- Botón para enviar el formulario -->
        <button type="submit" class="btn btn-warning">Actualizar</button>
    </form>
@endsection
@section('pie')
    <p>¿No deseas realizar cambios? Regresa al listado de valores:</p>
    <a href="{{ route('valores') }}" class="btn btn-secondary">Volver a Valores</a>
@endsection