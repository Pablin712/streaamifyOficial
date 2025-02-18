@extends('layouts.table')

@section('title')
    Mantenimientos
@endsection

@section('h1', 'Mantenimientos')
@section('breadcrumb')
    Mantenimientos
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Visualiza todos los mantenimientos registrados y realiza las acciones necesarias como editar o eliminar.</p>
@endsection

@section('tablename', 'Mantenimientos')

@section('table1')
    <h1>Mantenimientos</h1>
    @can('mantenimientos.create')
        <a href="{{ route('mantenimientos.create') }}" class="btn btn-primary mb-3">Crear Mantenimiento</a>
    @endcan
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID Cuenta</th>
                <th>Usuario</th>
                <th>Contraseña</th>
                <th>Fecha de Mantenimiento</th>
                <th>Descripción</th>
                @canany(['mantenimientos.edit', 'mantenimientos.destroy'])
                    <th>Acciones</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($mantenimientos as $mantenimiento)
                <tr>
                    <td>{{ $mantenimiento->idcue }}</td>
                    <td>{{ $mantenimiento->cuenta->usuariocue }}</td>
                    <td>{{ $mantenimiento->cuenta->contrasenacue }}</td>
                    <td>{{ $mantenimiento->fechaman }}</td>
                    <td>{{ $mantenimiento->descripcionman }}</td>
                    @canany(['mantenimientos.edit', 'mantenimientos.destroy'])
                        <td>
                            @can('mantenimientos.edit')
                                <a href="{{ route('mantenimientos.edit', $mantenimiento->idman) }}" class="btn btn-warning"><i
                                        class="fas fa-edit"></i></a>
                            @endcan
                            @can('mantenimientos.destroy')
                                <form action="{{ route('mantenimientos.destroy', $mantenimiento->idman) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle"
                                        onclick="return confirm('¿Estás seguro?')"><i class="fas fa-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    @endcanany
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
