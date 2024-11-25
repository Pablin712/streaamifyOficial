@extends('layouts.table')
@section('title')
Valores
@endsection
@section('h1')
Valores de servicios
@endsection
@section('descripcion')
    <h3>Revisa el inventario</h3>
    <p>a esta pantalla acceden ciertos usuarios</p>
    <h3>Tabla de Valores de servicios</h3>
    <p>muestra tabla de los valores que hay para adquirir (se puede agregar botón adquirir, y se agregaría a cuentas y se
        registra nuevo costo)</p>
@endsection
@section('tablename', 'Valores')
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