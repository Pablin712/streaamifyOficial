@extends('layouts.table')
@section('title', 'Historial')
@section('h1', 'Historial')
@section('breadcrumb', 'Historial')
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (Auth::user()->hasPermissionTo('historial.clear'))
        <!-- Formulario para borrar historial filtrado por fechas -->
        <form method="POST" action="{{ route('historial.clear') }}" class="mb-4">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-auto">
                    <label for="start_date" class="form-label">Fecha Inicio:</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" required>
                </div>
                <div class="col-auto">
                    <label for="end_date" class="form-label">Fecha Fin:</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-danger">Borrar Historial</button>
                </div>
            </div>
        </form>
    @endif
@endsection

@section('tablename', 'Historial de Acciones')
@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Acción</th>
                <th>Descripción</th>
                <th>Realizado Por</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($historial as $accion)
                <tr>
                    <td>{{ $accion->id }}</td>
                    <td>{{ $accion->accion }}</td>
                    <td>{{ $accion->descripcion }}</td>
                    <td>{{ $accion->realizado_por }}</td>
                    <td>{{ $accion->fecha }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
