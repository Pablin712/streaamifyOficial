@extends('layouts.static')

@section('title', 'Inicio')

@section('h1')
    <i class="fas fa-home text-primary me-2"></i> Panel de Control
@endsection

@section('breadcrumb')
    Inicio
@endsection

@section('introduccion')
    <div class="d-flex align-items-center">
        <div>
            <h3 class="mb-1">
                👋 ¡Bienvenido, <strong>{{ Auth::user()->nombreemp }}</strong>!
            </h3>
            <p class="mb-0 text-muted">
                Accede rápidamente a las funciones más utilizadas del sistema
            </p>
        </div>
    </div>
@endsection

@section('content')
    <style>
        .quick-action-card {
            transition: all 0.3s ease;
            border: none;
            height: 100%;
            cursor: pointer;
        }
        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
        }
        .quick-action-card .card-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .quick-action-card i {
            transition: transform 0.3s ease;
        }
        .quick-action-card:hover i {
            transform: scale(1.1);
        }
        .section-header {
            border-left: 4px solid var(--bs-primary);
            padding-left: 1rem;
            margin-bottom: 1.5rem;
        }
        .action-icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
    </style>

    <!-- Sección de Navegación Rápida -->
    <div class="mb-5">
        <div class="section-header">
            <h4 class="mb-1">
                <i class="fas fa-compass me-2"></i>Navegación Rápida
            </h4>
            <p class="text-muted mb-0 small">Accede a las diferentes secciones del sistema</p>
        </div>

        <div class="row g-3">
            @php
                $navegacion = [
                    ['icon' => 'fa-shopping-cart', 'color' => 'primary', 'title' => 'Ventas', 'route' => 'ventas', 'desc' => 'Gestionar ventas'],
                    ['icon' => 'fa-users', 'color' => 'success', 'title' => 'Usuarios', 'route' => 'usuarios', 'desc' => 'Ver usuarios'],
                    ['icon' => 'fa-chart-line', 'color' => 'warning', 'title' => 'Dashboard', 'route' => 'dashboard', 'desc' => 'Estadísticas'],
                    ['icon' => 'fa-tasks', 'color' => 'info', 'title' => 'Tareas', 'route' => 'tareas.index', 'desc' => 'Mis tareas'],
                    ['icon' => 'fa-user-tie', 'color' => 'secondary', 'title' => 'Empleados', 'route' => 'empleados', 'desc' => 'Gestionar empleados'],
                    ['icon' => 'fa-boxes', 'color' => 'dark', 'title' => 'Productos', 'route' => 'productos.index', 'desc' => 'Inventario'],
                    ['icon' => 'fa-user', 'color' => 'primary', 'title' => 'Clientes', 'route' => 'clientes', 'desc' => 'Base de clientes'],
                    ['icon' => 'fa-server', 'color' => 'info', 'title' => 'Servicios', 'route' => 'servicios', 'desc' => 'Catálogo de servicios'],
                    ['icon' => 'fa-dollar-sign', 'color' => 'success', 'title' => 'Recargas', 'route' => 'empleado.recargas.index', 'desc' => 'Gestionar recargas'],
                    ['icon' => 'fa-truck', 'color' => 'warning', 'title' => 'Pedidos', 'route' => 'empleado.pedidos.index', 'desc' => 'Ver pedidos'],
                    ['icon' => 'fa-tv', 'color' => 'dark', 'title' => 'Cuentas', 'route' => 'cuentas', 'desc' => 'Cuentas streaming'],
                    ['icon' => 'fa-history', 'color' => 'secondary', 'title' => 'Historial', 'route' => 'historial', 'desc' => 'Registro de actividad'],
                ];
            @endphp

            @foreach ($navegacion as $item)
                @if (Auth::user()->hasPermissionTo($item['route']))
                    <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                        <a href="{{ route($item['route']) }}" class="text-decoration-none">
                            <div class="card quick-action-card shadow-sm">
                                <div class="card-body">
                                    <div class="action-icon-wrapper bg-{{ $item['color'] }} bg-opacity-10">
                                        <i class="fas {{ $item['icon'] }} fa-2x text-{{ $item['color'] }}"></i>
                                    </div>
                                    <h6 class="card-title mb-1 fw-bold text-center" style="color: var(--text-primary);">
                                        {{ $item['title'] }}
                                    </h6>
                                    <small class="text-muted text-center d-block">{{ $item['desc'] }}</small>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Sección de Acciones Rápidas -->
    <div class="mb-4">
        <div class="section-header">
            <h4 class="mb-1">
                <i class="fas fa-bolt me-2"></i>Acciones Rápidas
            </h4>
            <p class="text-muted mb-0 small">Crea nuevos registros directamente desde aquí</p>
        </div>

        <div class="row g-3">
            @php
                $acciones = [
                    ['icon' => 'fa-shopping-cart', 'color' => 'primary', 'title' => 'Nueva Venta', 'route' => 'ventas.create', 'permission' => 'ventas.store'],
                    ['icon' => 'fa-tv', 'color' => 'dark', 'title' => 'Nueva Cuenta', 'route' => 'cuentas', 'permission' => 'cuentas.store', 'modal' => true],
                    ['icon' => 'fa-money-bill-wave', 'color' => 'warning', 'title' => 'Registrar Gasto', 'route' => 'gastos', 'permission' => 'gastos'],
                    ['icon' => 'fa-user', 'color' => 'success', 'title' => 'Nuevo Cliente', 'route' => 'clientes', 'permission' => 'clientes.store', 'modal' => true],
                    ['icon' => 'fa-concierge-bell', 'color' => 'info', 'title' => 'Nuevo Servicio', 'route' => 'servicios', 'permission' => 'servicios.store', 'modal' => true],
                    ['icon' => 'fa-truck', 'color' => 'danger', 'title' => 'Nuevo Proveedor', 'route' => 'proveedores', 'permission' => 'proveedores.store', 'modal' => true],
                    ['icon' => 'fa-coins', 'color' => 'warning', 'title' => 'Nuevo Valor', 'route' => 'valores', 'permission' => 'valores.store', 'modal' => true],
                    ['icon' => 'fa-box', 'color' => 'primary', 'title' => 'Nuevo Producto', 'route' => 'productos.index', 'permission' => 'productos.store', 'modal' => true],
                ];
            @endphp

            @foreach ($acciones as $accion)
                @if (Auth::user()->hasPermissionTo($accion['permission']))
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        @if (isset($accion['modal']) && $accion['modal'])
                            <a href="{{ route($accion['route']) }}" class="text-decoration-none">
                                <div class="card quick-action-card shadow-sm">
                                    <div class="card-body">
                                        <i class="fas {{ $accion['icon'] }} fa-3x text-{{ $accion['color'] }} mb-3"></i>
                                        <h6 class="card-title mb-2 fw-bold" style="color: var(--text-primary);">
                                            {{ $accion['title'] }}
                                        </h6>
                                        <button class="btn btn-sm btn-{{ $accion['color'] }} w-100">
                                            <i class="fas fa-plus me-1"></i> Ir y Crear
                                        </button>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle me-1"></i>Se abre modal en la vista
                                        </small>
                                    </div>
                                </div>
                            </a>
                        @else
                            <a href="{{ route($accion['route']) }}" class="text-decoration-none">
                                <div class="card quick-action-card shadow-sm">
                                    <div class="card-body">
                                        <i class="fas {{ $accion['icon'] }} fa-3x text-{{ $accion['color'] }} mb-3"></i>
                                        <h6 class="card-title mb-2 fw-bold" style="color: var(--text-primary);">
                                            {{ $accion['title'] }}
                                        </h6>
                                        <button class="btn btn-sm btn-{{ $accion['color'] }} w-100">
                                            <i class="fas fa-plus me-1"></i> Crear
                                        </button>
                                    </div>
                                </div>
                            </a>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endsection

@section('pie')
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <i class="fas fa-lightbulb text-warning me-2"></i>
            <strong>Tip:</strong> Usa los accesos rápidos para agilizar tu trabajo diario
        </div>
        <div class="text-muted">
            <i class="fas fa-clock me-1"></i>
            Última actividad: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
@endsection
