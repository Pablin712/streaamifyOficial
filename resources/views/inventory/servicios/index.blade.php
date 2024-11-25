@extends('layouts.app')

@section('content')
    <h1>Servicios</h1>
    <a href="{{ route('servicios.create') }}" class="btn btn-primary">Crear Servicio</a>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($servicios as $servicio)
                <tr>
                    <td>{{ $servicio->idser }}</td>
                    <td>{{ $servicio->nombreser }}</td>
                    <td>{{ $servicio->descripcion }}</td>
                    <td>${{ number_format($servicio->precio, 2) }}</td>
                    <td>
                        <a href="{{ route('servicios.edit', $servicio->id) }}" class="btn btn-warning">Editar</a>
                        <form action="{{ route('servicios.destroy', $servicio->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('¿Eliminar este servicio?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
