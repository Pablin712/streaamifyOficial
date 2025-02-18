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
    @can('valores.create')
        <a href="{{ route('valores.create') }}" class="btn btn-primary mb-3">Crear Valor</a>
    @endcan
    @can('servicios.create')
        <a href="{{ route('servicios.create') }}" class="btn btn-primary mb-3">Nuevo Servicio</a>
    @endcan
    @can('proveedores.create')
        <a href="{{ route('proveedores.create') }}" class="btn btn-primary mb-3">Nuevo Proveedor</a>
    @endcan
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
                @canany(['valores.edit', 'valores.destroy'])
                    <th>Acciones</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($valores as $valor)
                <tr>
                    <td>{{ $valor->idval }}</td>
                    <td>{{ $valor->idser }}</td>
                    <td>{{ $valor->proveedor->nombrepro }}</td> <!-- Mostrar el nombre del proveedor -->
                    <td>${{ number_format($valor->costoval, 2) }}</td>
                    <td>{{ $valor->pantminval }}</td>
                    <td>{{ $valor->pantmaxval }}</td>
                    <td>{{ $valor->mesesval }}</td>
                    @canany(['valores.edit', 'valores.destroy'])
                        <td>
                            @can('valores.edit')
                                <a href="{{ route('valores.edit', $valor->idval) }}" class="btn btn-warning  "><i
                                        class="fas fa-edit"></i></a>
                            @endcan
                            @can('valores.destroy')
                                <form action="{{ route('valores.destroy', $valor->idval) }}" method="POST"
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
