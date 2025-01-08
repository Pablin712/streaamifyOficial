@extends('layouts.static')

@section('h1', 'Empleados')
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
        <h1>Actualizar Datos</h1>
        <form action="{{ route('empleados.update', $empleado->idemp) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="nombreemp" class="form-label">Nombre</label>
                <input type="text" name="nombreemp" id="nombreemp" class="form-control" value="{{ $empleado->nombreemp }}"
                    required>
            </div>
            <div class="mb-3">
                <label for="telefonoemp" class="form-label">Teléfono</label>
                <input type="text" name="telefonoemp" id="telefonoemp" class="form-control"
                    value="{{ $empleado->telefonoemp }}" required>
            </div>
            <div class="mb-3">
                <label for="usuarioemp" class="form-label">Usuario</label>
                <input type="text" name="usuarioemp" id="usuarioemp" class="form-control"
                    value="{{ $empleado->usuarioemp }}" required>
            </div>
            <div class="mb-3">
                <label for="passwordemp" class="form-label">Nueva Contraseña (opcional)</label>
                <input type="password" name="passwordemp" id="passwordemp" class="form-control">
            </div>
            @if (Auth::user()->idrol == 'administrador')
                <div class="mb-3">
                    <label for="idrol" class="form-label">Rol</label>
                    <select name="idrol" id="idrol" class="form-select" required>
                        <option value="vendedor" {{ $empleado->idrol === 'vendedor' ? 'selected' : '' }}>vendedor</option>
                        <option value="bodeguero" {{ $empleado->idrol === 'bodeguero' ? 'selected' : '' }}>bodeguero
                        </option>
                        <option value="contador" {{ $empleado->idrol === 'contador' ? 'selected' : '' }}>Contador</option>
                        <option value="tecnico" {{ $empleado->idrol === 'tecnico' ? 'selected' : '' }}>Tecnico</option>
                        <option value="administrador" {{ $empleado->idrol === 'administrador' ? 'selected' : '' }}>
                            Administrador</option>
                    </select>
                </div>
            @else
                <!-- Campo oculto para mantener el rol actual -->
                <input type="hidden" name="idrol" value="{{ $empleado->idrol }}">
            @endif
            <div class="mb-3">
                <label for="foto_url" class="form-label">Foto (opcional)</label>
                <input type="file" name="foto_url" id="foto_url" class="form-control">
                @if ($empleado->foto_url)
                    <div class="mt-2">
                        <img src="{{ $empleado->foto_url }}" alt="Foto de {{ $empleado->nombreemp }}"
                            class="img-fluid rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                    </div>
                @endif
            </div>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </form>
    </div>
@endsection
