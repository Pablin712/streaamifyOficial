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
            <h2 class="text-center my-4">Acciones Frecuentes</h2>
            <div class="row text-center">
                @php
                    $acciones = [
                        [
                            'icon' => 'fa-shopping-cart',
                            'color' => 'primary',
                            'title' => 'Ventas',
                            'route' => 'ventas',
                        ],
                        [
                            'icon' => 'fa-users',
                            'color' => 'success',
                            'title' => 'Usuarios',
                            'route' => 'usuarios',
                        ],
                        [
                            'icon' => 'fa-wallet',
                            'color' => 'warning',
                            'title' => 'Contabilidad',
                            'route' => 'dashboard',
                        ],
                        [
                            'icon' => 'fa-tasks',
                            'color' => 'info',
                            'title' => 'Tareas',
                            'route' => 'tareas.index',
                        ],
                        [
                            'icon' => 'fa-user-tie',
                            'color' => 'secondary',
                            'title' => 'Empleados',
                            'route' => 'empleados',
                        ],
                        [
                            'icon' => 'fa-boxes',
                            'color' => 'dark',
                            'title' => 'Productos',
                            'route' => 'productos.index',
                        ],
                        [
                            'icon' => 'fa-user',
                            'color' => 'primary',
                            'title' => 'Clientes',
                            'route' => 'clientes',
                        ],
                        [
                            'icon' => 'fa-server',
                            'color' => 'info',
                            'title' => 'Servicios',
                            'route' => 'servicios',
                        ],
                        [
                            'icon' => 'fa-dollar-sign',
                            'color' => 'success',
                            'title' => 'Recargas',
                            'route' => 'empleado.recargas.index',
                        ],
                        [
                            'icon' => 'fa-truck',
                            'color' => 'warning',
                            'title' => 'Pedidos',
                            'route' => 'empleado.pedidos.index',
                        ],
                        [
                            'icon' => 'fa-building',
                            'color' => 'dark',
                            'title' => 'Cuentas',
                            'route' => 'cuentas',
                        ],
                        [
                            'icon' => 'fa-history',
                            'color' => 'secondary',
                            'title' => 'Historial',
                            'route' => 'historial',
                        ]
                    ];
                @endphp

                @foreach ($acciones as $accion)
                    @if (Auth::user()->hasPermissionTo($accion['route']))
                        <div class="col-md-3 mb-3">
                            <div class="card shadow-sm">
                                <div class="card-body p-3">
                                    <i class="fas {{ $accion['icon'] }} fa-3x text-{{ $accion['color'] }} mb-2"></i>
                                    <h6 class="card-title mb-2">{{ $accion['title'] }}</h6>
                                    <a href="{{ route($accion['route']) }}" class="btn btn-sm btn-{{ $accion['color'] }}">
                                        <i class="fas fa-eye"></i> Ver {{ $accion['title'] }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="container mt-4">
            <div class="row">
                @php
                    $acciones_registro = [
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

                @foreach ($acciones_registro as $accion)
                    @if (Auth::user()->hasPermissionTo($accion['route']))
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
                    @endif
                @endforeach
            </div>
        </div>
        {{--
        <h3>Mapa de ERP</h3>
        <img src="{{ asset('images/models.png') }}" alt="imagen de mapa" class="d-block mx-auto"> --}}
    </div>
@endsection

@section('pie')
    Realiza las tareas
@endsection
