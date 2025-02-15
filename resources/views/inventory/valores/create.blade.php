@extends('layouts.static')

@section('title', 'Crear Valor')
@section('h1','Crear Valor')
@section('breadcrumb')
    <a href="{{ route('valores') }}">Valores</a>
@endsection
@section('breadcrumb2')
    Registrar Valor de Servicio
@endsection
@section('introduccion')
    Aquí puedes guardar un nuevo valor de servicio. Por favor, llena todos los campos requeridos y asegúrate de que la información sea correcta.
@endsection
@section('content')
    <form action="{{ route('valores.store') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label for="idval">ID del Valor</label>
            <input type="text" name="idval" id="idval" class="form-control" maxlength="20" required>
        </div>
        <div class="form-group mb-3">
            <label for="idser">ID del Servicio</label>
            <select name="idser" id="idser" class="form-control" required>
                @foreach ($servicios as $servicio)
                    <option value="{{ $servicio->idser }}">{{ $servicio->nombreser }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="idpro">Proveedor</label>
            <select name="idpro" id="idpro" class="form-control" required>
                @foreach ($proveedores as $proveedor)
                    <option value="{{ $proveedor->idpro }}">{{ $proveedor->nombrepro }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="costoval">Costo</label>
            <input type="number" name="costoval" id="costoval" class="form-control" step="0.01" required>
        </div>
        <div class="form-group mb-3">
            <label for="pantminval">Pantallas Mínimas</label>
            <input type="number" name="pantminval" id="pantminval" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label for="pantmaxval">Pantallas Máximas</label>
            <input type="number" name="pantmaxval" id="pantmaxval" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label for="mesesval">Meses</label>
            <input type="number" name="mesesval" id="mesesval" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Guardar</button>
    </form>
@endsection
@section('pie')
    <p>¿No deseas guardar un valor de servicio? Vuelve a la página de listado:</p>
    <a href="{{ route('valores') }}" class="btn btn-secondary">Volver a Valores</a>
@endsection
