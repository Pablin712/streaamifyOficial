@extends('layouts.table')
@section('title')
Proveedores
@endsection
@section('h1')
Proveedores
@endsection
@section('descripcion')
    <h3>Revisa el inventario</h3>
    <p>a esta pantalla acceden ciertos usuarios</p>
    <h3>Tabla de proveedores</h3>
    <p>muestra la tabla de proveedores, un crud</p>
@endsection
@section('tablename', 'Proveedores')
@section('table1')
    <thead>
        <tr>
            <td>Nombre</td>
            <td>Telefono</td>
            <td>Actualizar</td>
            <td>Eliminar</td>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td>Nombre</td>
            <td>Telefono</td>
            <td>Actualizar</td>
            <td>Eliminar</td>
        </tr>
    </tfoot>
    <tbody>

    </tbody>
@endsection