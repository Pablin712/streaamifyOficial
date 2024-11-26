@extends('layouts.static')

@section('title', 'Editar Cliente')
@section('h1','Editar Cliente')
@section('introduccion')
    Actualiza este cliente con los nuevos datos. Por favor, revisa cuidadosamente los campos antes de guardar los cambios.
@endsection
@section('content')
    <form action="{{ route('clientes.update', $cliente->idcli) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group mb-3">
            <label for="nombrecli">Nombre</label>
            <input type="text" name="nombrecli" id="nombrecli" class="form-control" value="{{ $cliente->nombrecli }}" maxlength="20" required>
        </div>
        <div class="form-group mb-3">
            <label for="telefonocli">Teléfono</label>
            <input type="text" name="telefonocli" id="telefonocli" class="form-control" value="{{ $cliente->telefonocli }}" maxlength="15" required>
        </div>
        <button type="submit" class="btn btn-warning">Actualizar</button>
    </form>
@endsection
@section('pie')
    <p>¿No deseas realizar cambios? Regresa al listado de clientes:</p>
    <a href="{{ route('clientes') }}" class="btn btn-secondary">Volver a Clientes</a>
@endsection