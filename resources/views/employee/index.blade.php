@extends('layouts.table')

@section('title', 'Empleados')
@section('h1', 'Empleados')
@section('breadcrumb', 'Empleados')

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
    @if (Auth::user()->hasPermissionTo('empleados.store'))
        <a href="{{ route('empleados.create') }}" class="btn btn-primary mb-3">Crear Empleado</a>
    @endif
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
                            <strong>Correo:</strong> {{ $empleado->email }}<br>
                            <strong>Ventas este mes:</strong> {{ $empleado->ventas_mes_actual }}<br>
                            <strong>Total de Ventas:</strong> {{ $empleado->ventas_count }}<br>
                            <strong>Rol:</strong>
                            @if ($empleado->roles->isNotEmpty())
                                {{ implode(', ', $empleado->roles->pluck('name')->toArray()) }}
                            @else
                                Sin rol asignado
                            @endif
                        </p>
                        @if (Auth::user()->hasPermissionTo('empleados.updateRol'))
                            <a href="{{ route('empleados.editRoles', $empleado->idemp) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar Roles
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
