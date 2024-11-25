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
    <thead>
        <tr>
            <td>Código</td>
            <td>Nombre</td>
            <td>PVP completo</td>
            <td>PVP individual</td>
            <td>PVP combo</td>
            <td>Reventa 1 pant</td>
            <td>Reventa 1 cuent</td>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td>Código</td>
            <td>Nombre</td>
            <td>PVP completo</td>
            <td>PVP individual</td>
            <td>PVP combo</td>
            <td>Reventa 1 pant</td>
            <td>Reventa 1 cuent</td>
        </tr>
    </tfoot>
    <tbody>
        @foreach ($servicios as $servicio)
                <tr>
                    <td>{{ $servicio->idser }}</td>
                    <td>{{ $servicio->nombreser }}</td>
                    <td>{{ $servicio->completoser }}</td>
                    <td>${{ number_format($servicio->precio, 2) }}</td>
                    
                </tr>
            @endforeach
    </tbody>
@endsection