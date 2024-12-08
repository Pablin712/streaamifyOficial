@extends('layouts.static')

@section('title', 'Registrar Cliente')
@section('h1', 'Registrar nuevo Cliente')
@section('introduccion')
    Aquí puedes registrar un nuevo cliente. Por favor, llena todos los campos requeridos y asegúrate de que la información
    sea correcta.
@endsection
@section('content')
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <form method="POST" action="{{ route('clientes.store') }}">
        @csrf
        <div class="form-group mb-3">
            <label for="nombrecli">Nombre</label>
            <input type="text" name="nombrecli" id="nombrecli" class="form-control" maxlength="20" required>
        </div>
        <div class="form-group mb-3">
            <label for="telefonocli">Teléfono</label>
            <input type="text" name="telefonocli" id="telefonocli" class="form-control" maxlength="15" required>
        </div>
        <button type="submit" class="btn btn-success">Guardar</button>
    </form>
@endsection
@section('pie')
    <p>¿No deseas guardar un cliente? Vuelve a la página de listado:</p>
    <a href="{{ route('clientes') }}" class="btn btn-secondary">Volver a Clientes</a>
@endsection
