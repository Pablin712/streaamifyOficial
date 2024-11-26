

@extends('layouts.table')
@section('title')
    Servicios
@endsection
@section('h1')
    Services
@endsection
@section('descripcion')
    <h3>Revisa el inventario</h3>
    <p>a esta pantalla acceden ciertos usuarios</p>
    <h3>Tabla de servicios</h3>
    <p>muestra tabla de todos los servicios y sus precios, un crud</p>
    <h3>Tabla de Valores</h3>
    <p>muestra tabla de los valores que hay para adquirir (se puede agregar botón adquirir, y se agregaría a cuentas y se
        registra nuevo costo)</p>
    <h3>Tabla de proveedores</h3>
    <p>muestra la tabla de proveedores, un crud</p>
@endsection
@section('tablename', 'Servicios')
@section('btncrear')
{{-- <a href="{{ route('servicios.create') }}" class="btn btn-primary">Crear Servicio</a> --}}
@endsection
@section('table1')
<table id="datatablesSimple" class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>PVP completo</th>
            <th>PVP individual</th>
            <th>PVP combo</th>
            <th>Reventa 1 pant</th>
            <th>Reventa 1 cuent</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($servicios as $servicio)
            <tr>
                <td>{{ $servicio->idser }}</td>
                <td>{{ $servicio->nombreser }}</td>
                <td>${{ number_format($servicio->completoser, 2) }}</td>
                <td>${{ number_format($servicio->precioser, 2) }}</td>
                <td>${{ number_format($servicio->comboser, 2) }}</td>
                <td>${{ number_format($servicio->reventaser, 2) }}</td>
                <td>${{ number_format($servicio->revcompser, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No hay servicios disponibles.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
