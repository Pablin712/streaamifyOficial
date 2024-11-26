@extends('layouts.static')

@section('title', 'Crear Cuenta')
@section('h1','Crear Cuenta')
@section('introduccion')
    Aquí puedes contratar una nueva cuenta para llenar stock del negocio. Por favor, llena todos los campos requeridos y asegúrate de que la información sea correcta.
@endsection
@section('content')
    <form action="{{ route('cuentas.store') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label for="idcue">ID de Cuenta</label>
            <input type="text" name="idcue" id="idcue" class="form-control" maxlength="20" required>
        </div>
        <div class="form-group mb-3">
            <label for="idval">ID del Valor</label>
            <select name="idval" id="idval" class="form-control" required>
                @foreach ($valores as $valor)
                    <option value="{{ $valores->idval }}">{{ $valor->idval }}</option>
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
    <p>¿No deseas agregar una cuenta al stock? Vuelve a la página de listado:</p>
    <a href="{{ route('cuentas') }}" class="btn btn-secondary">Volver a Cuentas</a>
@endsection