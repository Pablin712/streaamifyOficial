@extends('layouts.table')
@section('title')
    Valores
@endsection
@section('h1')
    Valores de servicios
@endsection
@section('breadcrumb')
    Valores
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Revisa el inventario y crea nuevos posibles contratos para luego agregarlas a stock.</p>
@endsection
@section('btncrear')
    @if (Auth::user()->hasPermissionTo('valores.create'))
        <a href="{{ route('valores.create') }}" class="btn btn-primary mb-3">Crear Valor</a>
    @endif
    @if (Auth::user()->hasPermissionTo('servicios.create'))
        <a href="{{ route('servicios.create') }}" class="btn btn-primary mb-3">Nuevo Servicio</a>
    @endif
    @if (Auth::user()->hasPermissionTo('proveedores.create'))
        <a href="{{ route('proveedores.create') }}" class="btn btn-primary mb-3">Nuevo Proveedor</a>
    @endif
@endsection
@section('tablename', 'Valores')
@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Servicio</th>
                <th>Proveedor</th>
                <th>Costo</th>
                <th>Pantallas Min</th>
                <th>Pantallas Max</th>
                <th>Meses</th>
                @if (Auth::user()->hasAnyPermission(['valores.edit', 'valores.destroy']))
                    <th>Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($valores as $valor)
                <tr>
                    <td>{{ $valor->idval }}</td>
                    <td>{{ $valor->idser }}</td>
                    <td>{{ $valor->proveedor->nombrepro }}</td>
                    <td>${{ number_format($valor->costoval, 2) }}</td>
                    <td>{{ $valor->pantminval }}</td>
                    <td>{{ $valor->pantmaxval }}</td>
                    <td>{{ $valor->mesesval }}</td>
                    @if (Auth::user()->hasAnyPermission(['valores.edit', 'valores.destroy']))
                        <td>
                            @if (Auth::user()->hasPermissionTo('valores.edit'))
                                <a href="{{ route('valores.edit', $valor->idval) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            @if (Auth::user()->hasPermissionTo('valores.destroy'))
                                <form action="{{ route('valores.destroy', $valor->idval) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle"
                                        onclick="return confirm('¿Estás seguro?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection