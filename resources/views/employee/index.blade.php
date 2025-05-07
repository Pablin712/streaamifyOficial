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
        @foreach ($datos as $dato)
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="{{ $dato['empleado']->foto_url ? asset('public/storage/' . $dato['empleado']->foto_url) : 'https://via.placeholder.com/100/007bff/ffffff?text=Usuario' }}"
                            alt="Foto de {{ $dato['empleado']->nombreemp }}" class="img-fluid rounded-circle mb-3"
                            style="width: 100px; height: 100px; object-fit: cover;">
                        <h5 class="card-title">{{ $dato['empleado']->nombreemp }}</h5>
                        <p class="card-text">
                            <strong>Teléfono:</strong> {{ $dato['empleado']->telefonoemp }}<br>
                            <strong>Usuario:</strong> {{ $dato['empleado']->usuarioemp }}<br>
                            <strong>Correo:</strong> {{ $dato['empleado']->email }}<br>
                            <strong>Ventas hoy:</strong> {{ $dato['gestionVentasHoy'] }}<br>
                            <strong>Total de Ventas:</strong> {{ $dato['empleado']->ventas_count }}<br>
                            <strong>Total de Conexión:</strong> {{ $dato['total'] }} minutos<br>
                            <strong>Gestión de Clientes:</strong> {{ $dato['gestionClientesHoy'] }}<br>
                            <strong>Gestión de Cuentas:</strong> {{ $dato['gestionCuentasHoy'] }}<br>
                            <strong>Gestión de Inventario:</strong> {{ $dato['gestionInventarioHoy'] }}<br>
                            <strong>Gestión de Tareas:</strong> {{ $dato['gestionTareasHoy'] }}<br>
                            <strong>Gestión de Recargas:</strong> {{ $dato['gestionRecargasHoy'] }}<br>
                            <strong>Gestión de Productos:</strong> {{ $dato['gestionProductosHoy'] }}<br>
                            <strong>Gestión de Costos:</strong> {{ $dato['gestionCostosHoy'] }}<br>
                            <strong>Rol:</strong>
                            @if ($dato['empleado']->roles->isNotEmpty())
                                {{ implode(', ', $dato['empleado']->roles->pluck('name')->toArray()) }}
                            @else
                                Sin rol asignado
                            @endif
                        </p>
                        @if (Auth::user()->hasPermissionTo('empleados.updateRol'))
                            <a href="{{ route('empleados.editRoles', $dato['empleado']->idemp) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar Roles
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
