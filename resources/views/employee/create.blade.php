@extends('layouts.static')

@section('h1', 'Empleados')
@section('breadcrumb')
    <a href="{{ route('empleados') }}">Empleados</a>
@endsection
@section('breadcrumb2')
    Crear Empleado
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
        <h1>Crear Empleado</h1>
        <form action="{{ route('empleados.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="nombreemp" class="form-label">Nombre</label>
                <input type="text" name="nombreemp" id="nombreemp" class="form-control" value="" required>
            </div>
            <div class="mb-3">
                <label for="telefonoemp" class="form-label">Teléfono</label>
                <input type="text" name="telefonoemp" id="telefonoemp" class="form-control" value="" required>
            </div>
            <div class="mb-3">
                <label for="usuarioemp" class="form-label">Usuario</label>
                <input type="text" name="usuarioemp" id="usuarioemp" class="form-control" value="" required>
            </div>
            <div class="mb-3">
                <label for="passwordemp" class="form-label">Contraseña</label>
                <input type="password" name="passwordemp" id="passwordemp" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="idrol" class="form-label">Rol</label>
                <select name="idrol" id="idrol" class="form-select" required>
                    <option value="vendedor" {{  old('idrol') === 'vendedor' ? 'selected' : '' }}>Vendedor</option>
                    <option value="bodeguero" {{  old('idrol') === 'bodeguero' ? 'selected' : '' }}>bodeguero</option>
                    <option value="contador" {{ old('idrol') === 'contador' ? 'selected' : '' }}>Contador</option>
                    <option value="tecnico" {{  old('idrol') ==='tecnico' ? 'selected' : '' }}>Tecnico</option>
                    <option value="administrador" {{ old('idrol') === 'administrador' ? 'selected' : '' }}>Administrador</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="foto_url" class="form-label">Foto (opcional)</label>
                <input type="file" name="foto_url" id="foto_url" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
    </div>
@endsection
