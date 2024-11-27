@extends('layouts.table')
@section('title')
    Servicios
@endsection
@section('h1')
    Services
@endsection
@section('descripcion')
    <h3>Revisa el inventario de servicios</h3>
    <p>A esta pantalla acceden ciertos usuarios</p>
    <p>Muestra tabla de todos los servicios y sus precios, un crud</p>
@endsection
@section('tablename', 'Servicios')
@section('btncrear')
<a href="{{ route('servicios.create') }}" class="btn btn-primary">Crear Servicio</a>
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
            <th>Acciones</th>
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
                <td>
                    <a href="{{ route('servicios.edit', $servicio->idser) }}" class="btn btn-warning  " ><i class="fas fa-edit"></i></a>
                    <form action="{{ route('servicios.destroy', $servicio->idser) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-circle" onclick="return confirm('¿Eliminar este servicio?')"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No hay servicios disponibles.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
