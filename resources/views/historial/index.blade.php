@extends('layouts.table')
@section('title')
    Historial
@endsection
@section('h1')
    Historial
@endsection
@section('breadcrumb')
    Historial
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
@endsection
@section('tablename', 'Historial de Acciones')
@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Accion</th>
                <th>Descripcion</th>
                <th>Realizado Por</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($historial as $accion)
                <tr>
                    <td>{{ $accion->id }}</td>
                    <td>{{ $accion->accion }}</td>
                    <td>{{ $accion->descripcion }}</td> <!-- Mostrar el nombre del proveedor -->
                    <td>{{ $accion->realizado_por }}</td>
                    <td>{{ $accion->fecha }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
