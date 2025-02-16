@extends('layouts.static')
@section('title')
    Inicio
@endsection
@section('breadcrumb')
    Acciones Frecuentes
@endsection
@section('introduccion')
    <h1>Bienvenido, {{ Auth::user()->nombreemp }}</h1>
@endsection

@section('content')
    <div class="container">
        <div class="container mt-4">
            <h2 class="text-center my-4">Acciones Frecuentes</h1>
            <div class="row text-center">
                <!-- Ver Ventas -->
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body p-3">
                            <i class="fas fa-shopping-cart fa-3x text-primary mb-2"></i>
                            <h6 class="card-title mb-2">Ventas</h6>
                            <a href="{{ route('ventas') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Ver Ventas
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Administrar Usuarios -->
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body p-3">
                            <i class="fas fa-users fa-3x text-success mb-2"></i>
                            <h6 class="card-title mb-2">Usuarios</h6>
                            <a href="{{ route('usuarios') }}" class="btn btn-sm btn-success">
                                <i class="fas fa-cogs"></i> Administrar Usuarios
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Gestionar Contabilidad -->
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body p-3">
                            <i class="fas fa-wallet fa-3x text-warning mb-2"></i>
                            <h6 class="card-title mb-2">Contabilidad</h6>
                            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-chart-line"></i> Ver Contabilidad
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Ver Tareas -->
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body p-3 text-center">
                            <i class="fas fa-tasks fa-3x text-info mb-2"></i>
                            <h6 class="card-title mb-2">Tareas</h6>
                            <a href="{{ route('tareas.index') }}" class="btn btn-sm btn-info">
                                <i class="fas fa-list"></i> Ver Tareas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mt-4">
            <div class="row">
                @php
                    $acciones = [
                        [
                            'icon' => 'fa-shopping-cart',
                            'color' => 'primary',
                            'title' => 'Registrar venta',
                            'route' => 'ventas.create',
                        ],
                        [
                            'icon' => 'fa-tv',
                            'color' => 'dark',
                            'title' => 'Registrar cuenta',
                            'route' => 'cuentas.create',
                        ],
                        [
                            'icon' => 'fa-money-bill-wave',
                            'color' => 'warning',
                            'title' => 'Registrar gasto',
                            'route' => 'gastos',
                        ],
                        [
                            'icon' => 'fa-user',
                            'color' => 'success',
                            'title' => 'Registrar cliente',
                            'route' => 'clientes.create',
                        ],
                        [
                            'icon' => 'fa-concierge-bell',
                            'color' => 'danger',
                            'title' => 'Registrar servicio',
                            'route' => 'servicios.create',
                        ],
                        [
                            'icon' => 'fa-truck',
                            'color' => 'danger',
                            'title' => 'Registrar proveedor',
                            'route' => 'proveedores.create',
                        ],
                        [
                            'icon' => 'fa-coins',
                            'color' => 'warning',
                            'title' => 'Registrar valor',
                            'route' => 'valores.create',
                        ],
                    ];
                @endphp

                @foreach ($acciones as $accion)
                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm text-center">
                            <div class="card-body p-3">
                                <i class="fas {{ $accion['icon'] }} fa-3x text-{{ $accion['color'] }} mb-2"></i>
                                <h6 class="card-title mb-2">{{ $accion['title'] }}</h6>
                                <a href="{{ route($accion['route']) }}" class="btn btn-sm btn-{{ $accion['color'] }}">
                                    <i class="fas fa-plus"></i> Registrar
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <h3>Mapa de ERP</h3>
        <img src="{{ asset('images/models.png') }}" alt="imagen de mapa" class="d-block mx-auto">
    </div>
@endsection
@section('pie')
    Realiza las tareas
@endsection
