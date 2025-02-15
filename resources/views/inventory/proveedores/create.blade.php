@extends('layouts.static')

@section('title', 'Guardar Proveedor')
@section('h1','Guardar nuevo Proveedor')
@section('breadcrumb')
    <a href="{{ route('proveedores') }}">Proveedores</a>
@endsection
@section('breadcrumb2')
    Registrar Proveedor
@endsection
@section('introduccion')
    Aquí puedes guardar un nuevo proveedor. Por favor, llena todos los campos requeridos y asegúrate de que la información sea correcta.
@endsection
@section('content')
    <form action="{{ route('proveedores.store') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label for="nombrepro">Nombre</label>
            <input type="text" name="nombrepro" id="nombrepro" class="form-control" maxlength="20" required>
        </div>
        <div class="form-group mb-3">
            <label for="telefonopro">Teléfono</label>
            <input type="text" name="telefonopro" id="telefonopro" class="form-control" maxlength="15" required>
        </div>
        <button type="submit" class="btn btn-success">Guardar</button>
    </form>
@endsection
@section('pie')
    <p>¿No deseas guardar un proveedor? Vuelve a la página de listado:</p>
    <a href="{{ route('proveedores') }}" class="btn btn-secondary">Volver a Proveedores</a>
@endsection