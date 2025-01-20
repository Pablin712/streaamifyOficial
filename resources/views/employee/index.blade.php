@extends('layouts.table')
@section('title')
    Empleados
@endsection
@section('h1', 'Empleados')
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <h3>Información de Empleados</h3>
@endsection
@section('table1')
    <a href="{{ route('empleados.create') }}" class="btn btn-primary mb-3">Crear Empleado</a>
    <div class="row">
        @foreach ($empleados as $empleado)
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="{{ $empleado->foto_url ? asset('public/storage/' . $empleado->foto_url) : 'https://via.placeholder.com/100/007bff/ffffff?text=Usuario' }}"
                            alt="Foto de {{ $empleado->nombreemp }}" class="img-fluid rounded-circle mb-3"
                            style="width: 100px; height: 100px; object-fit: cover;">
                        <h5 class="card-title">{{ $empleado->nombreemp }}</h5>
                        <p class="card-text">
                            <strong>Teléfono:</strong> {{ $empleado->telefonoemp }}<br>
                            <strong>Usuario:</strong> {{ $empleado->usuarioemp }}<br>
                            <strong>Rol:</strong> {{ $empleado->idrol }}<br>
                            <strong>Ventas este mes:</strong> {{ $empleado->ventas_mes_actual }}<br>
                            <strong>Total de Ventas:</strong> {{ $empleado->ventas_count }}<br>
                            <strong>Correo:</strong> {{ $empleado->email }}<br>
                        </p>
                        <a href="{{ route('empleados.edit', $empleado->idemp) }}" class="btn btn-warning"><i
                                class="fas fa-edit"></i> Editar</a>
                        <form action="{{ route('empleados.destroy', $empleado->idemp) }}" method="POST"
                            style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro?')"><i
                                    class="fas fa-trash"></i> Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
