@extends('layouts.static')

@section('h1', 'Administrador de Roles y Permisos')
@section('breadcrumb')
    <a href="{{ route('empleados') }}">Empleados</a>
@endsection
@section('breadcrumb2')
    Roles y Permisos
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container">
        <h2>Editar Roles de {{ $empleado->nombreemp }}</h2>
        <form action="{{ route('empleados.updateRoles', $empleado->idemp) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Roles Disponibles</label>
                <div class="row">
                    @foreach ($roles as $rol)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" name="roles[]" value="{{ $rol->name }}" 
                                    id="role_{{ $rol->id }}" 
                                    class="form-check-input"
                                    {{ $empleado->hasRole($rol->name) ? 'checked' : '' }}>
                                <label for="role_{{ $rol->id }}" class="form-check-label">{{ ucfirst($rol->name) }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Actualizar Roles</button>
        </form>
    </div>
@endsection
