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
@section('btncrear')
    <a href="{{ route('valores.create') }}" class="btn btn-primary mb-3">Crear Valor</a>
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
                <th>Acciones</th>
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
                    <td>
                        <a href="{{ route('valores.edit', $valor->idval) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('valores.destroy', $valor->idval) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
