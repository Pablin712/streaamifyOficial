@extends('layouts.static')

@section('title', 'Editar Mantenimiento')
@section('h1', 'Editar Mantenimiento')
@section('breadcrumb')
    <a href="{{ route('mantenimientos') }}">Mantenimientos</a>
@endsection
@section('breadcrumb2')
    Editar Mantenimiento
@endsection
@section('introduccion')
    Actualiza este mantenimiento con los nuevos datos. Por favor, revisa cuidadosamente los campos antes de guardar los cambios.
@endsection

@section('content')
    <form action="{{ route('mantenimientos.update', $mantenimiento->idman) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- ID de Cuenta (idcue) -->
        <div class="form-group mb-3">
            <label for="idcue">ID de Cuenta</label>
            <input type="text" name="idcue" id="idcue" class="form-control" value="{{ $mantenimiento->idcue }} - {{ $mantenimiento->cuenta->usuariocue }}" readonly>
        </div>

        <!-- Campo para la fecha de mantenimiento (fechaman) -->
        <div class="form-group mb-3">
            <label for="fechaman">Fecha de Mantenimiento</label>
            <input type="date" name="fechaman" id="fechaman" class="form-control" value="{{ $mantenimiento->fechaman }}" required>
        </div>

        <!-- Campo para la descripción del mantenimiento -->
        <div class="form-group mb-3">
            <label for="descripcionman">Descripción del Mantenimiento</label>
            <textarea name="descripcionman" id="descripcionman" class="form-control" required>{{ $mantenimiento->descripcionman }}</textarea>
        </div>

        <!-- Botón para actualizar el mantenimiento -->
        <button type="submit" class="btn btn-warning">Actualizar</button>
    </form>
@endsection

@section('pie')
    <p>¿No deseas realizar cambios? Regresa al listado de mantenimientos:</p>
    <a href="{{ route('mantenimientos') }}" class="btn btn-secondary">Volver a Mantenimientos</a>
@endsection
