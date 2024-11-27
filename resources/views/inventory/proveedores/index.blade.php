@extends('layouts.table')
@section('title')
Proveedores
@endsection
@section('h1','Proveedores')
@section('descripcion')
    <h3>Revisa el inventario</h3>
    <p>a esta pantalla acceden ciertos usuarios</p>
    <h3>Tabla de proveedores</h3>
    <p>muestra la tabla de proveedores, un crud</p>
@endsection
@section('tablename', 'Proveedores')
@section('table1')
<h1>Proveedores</h1>
<a href="{{ route('proveedores.create') }}" class="btn btn-primary mb-3">Crear Proveedor</a>
<table id="datatablesSimple" class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($proveedores as $proveedor)
            <tr>
                <td>{{ $proveedor->idpro }}</td>
                <td>{{ $proveedor->nombrepro }}</td>
                <td>{{ $proveedor->telefonopro }}</td>
                <td>
                    <a href="{{ route('proveedores.edit', $proveedor->idpro) }}" class="btn btn-warning  " ><i class="fas fa-edit"></i></a>
                    <form action="{{ route('proveedores.destroy', $proveedor->idpro) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-circle" onclick="return confirm('¿Estás seguro?')"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection