@extends('layouts.static')

@section('title', 'Editar Proveedor')
@section('h1','Editar Proveedor')
@section('introduccion')
    Actualiza este proveedor con los nuevos datos. Por favor, revisa cuidadosamente los campos antes de guardar los cambios.
@endsection
@section('content')
    <form action="{{ route('proveedores.update', $proveedor->idpro) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group mb-3">
            <label for="nombrepro">Nombre</label>
            <input type="text" name="nombrepro" id="nombrepro" class="form-control" value="{{ $proveedor->nombrepro }}" maxlength="20" required>
        </div>
        <div class="form-group mb-3">
            <label for="telefonopro">Teléfono</label>
            <input type="text" name="telefonopro" id="telefonopro" class="form-control" value="{{ $proveedor->telefonopro }}" maxlength="15" required>
        </div>
        <button type="submit" class="btn btn-warning">Actualizar</button>
    </form>
@endsection
@section('pie')
    <p>¿No deseas realizar cambios? Regresa al listado de proveedores:</p>
    <a href="{{ route('proveedores') }}" class="btn btn-secondary">Volver a Proveedores</a>
@endsection