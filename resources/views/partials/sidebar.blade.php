<div id="layoutSidenav">
    <!-- Botón para mostrar/ocultar en móviles -->
    <button id="toggleSidebar" class="btn btn-light d-md-none">
        <i class="fas fa-bars"></i>
    </button>

    <div id="layoutSidenav_nav" class="sidebar">
        <nav class="sb-sidenav accordion" id="sidenavAccordion">

            <div class="sb-sidenav-menu">
                <div class="nav">
                    <div class="sb-sidenav-menu-heading">Principal</div>
                    <a class="nav-link" href="{{ route('tareas.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-tasks"></i></div>
                        Tareas
                    </a>
                    {{-- Reemplazamos @can('dashboard') --}}
                    @if (Auth::user()->hasPermissionTo('dashboard'))
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                            Dashboard
                        </a>
                    @endif
                    {{-- Reemplazamos @can('calendario') --}}
                    @if (Auth::user()->hasPermissionTo('cuentas'))
                        <a class="nav-link" href="{{ route('calendario') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-calendar"></i></div>
                            Calendario
                        </a>
                    @endif
                    @if (Auth::user()->hasPermissionTo('chat.ver'))
                        <a class="nav-link" href="{{ url('/chat/whatsapp') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-comments"></i></div>
                            Chat WhatsApp
                        </a>
                        <a class="nav-link" href="{{ route('chat.panel') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-headset"></i></div>
                            Panel Chat
                        </a>
                    @endif
                    <div class="sb-sidenav-menu-heading">Negocio</div>
                    {{-- Reemplazamos @canany(['empleados', 'roles.index']) --}}
                    @if (Auth::user()->hasAnyPermission(['empleados', 'roles.index']))
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#collapseAdministration" aria-expanded="false"
                            aria-controls="collapseAdministration">
                            <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                            Administracion
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseAdministration" aria-labelledby="headingAdministration"
                            data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                @if (Auth::user()->hasPermissionTo('empleados'))
                                    <a class="nav-link" href="{{ route('empleados') }}">Empleados</a>
                                    <a class="nav-link" href="{{ route('asistencias.index') }}">Control</a>
                                @endif
                                @if (Auth::user()->hasPermissionTo('roles.index'))
                                    <a class="nav-link" href="{{ route('roles.index') }}">Roles</a>
                                @endif
                            </nav>
                        </div>
                    @endif
                    {{-- Reemplazamos @canany(['bancos.index', 'empleado.recargas.index', 'costos', 'gastos']) --}}
                    @if (Auth::user()->hasAnyPermission(['bancos.index', 'empleado.recargas.index', 'costos', 'gastos']))
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#collapseFinance" aria-expanded="false" aria-controls="collapseFinance">
                            <div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
                            Finanza
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseFinance" aria-labelledby="headingFinance"
                            data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                @if (Auth::user()->hasPermissionTo('bancos.index'))
                                    <a class="nav-link" href="{{ route('bancos.index') }}">Bancos</a>
                                @endif
                                @if (Auth::user()->hasPermissionTo('empleado.recargas.index'))
                                    <a class="nav-link" href="{{ route('empleado.recargas.index') }}">Recargas</a>
                                @endif
                                @if (Auth::user()->hasPermissionTo('costos'))
                                    <a class="nav-link" href="{{ route('costos') }}">Costos</a>
                                @endif
                                @if (Auth::user()->hasPermissionTo('gastos'))
                                    <a class="nav-link" href="{{ route('gastos') }}">Gastos</a>
                                @endif
                            </nav>
                        </div>
                    @endif
                    {{-- Reemplazamos @canany(['ventas', 'empleados.pedidos.index', 'clientes', 'gestion.index', 'productos.index']) --}}
                    @if (Auth::user()->hasAnyPermission([
                            'ventas',
                            'empleados.pedidos.index',
                            'clientes',
                            'gestion.index',
                            'productos.index',
                        ]))
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                            Comercio
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne"
                            data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                @if (Auth::user()->hasPermissionTo('ventas'))
                                    <a class="nav-link" href="{{ route('ventas') }}">Ventas</a>
                                @endif
                                @if (Auth::user()->hasPermissionTo('empleado.pedidos.index'))
                                    <a class="nav-link" href="{{ route('empleado.pedidos.index') }}">Pedidos</a>
                                @endif
                                @if (Auth::user()->hasPermissionTo('clientes'))
                                    <a class="nav-link" href="{{ route('clientes') }}">Clientes</a>
                                @endif
                                @if (Auth::user()->hasPermissionTo('gestion'))
                                    <a class="nav-link" href="{{ route('gestion.index') }}">Gestión de Productos</a>
                                @endif
                                @if (Auth::user()->hasPermissionTo('productos.index'))
                                    <a class="nav-link" href="{{ route('productos.index') }}">Productos</a>
                                @endif
                            </nav>
                        </div>
                    @endif
                    {{-- Reemplazamos @canany(['cuentas', 'usuarios', 'mantenimientos']) --}}
                    @if (Auth::user()->hasAnyPermission(['cuentas', 'usuarios', 'mantenimientos', 'soportes']))
                        <!-- Accounts collapsible -->
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#collapseAccounts" aria-expanded="false" aria-controls="collapseAccounts">
                            <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                            Cuentas
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseAccounts" aria-labelledby="headingAccounts"
                            data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                @if (Auth::user()->hasPermissionTo('cuentas'))
                                    <a class="nav-link" href="{{ route('cuentas') }}">Cuentas y Perfiles</a>
                                @endif
                                @if (Auth::user()->hasPermissionTo('usuarios'))
                                    <a class="nav-link" href="{{ route('usuarios') }}">Usuarios Activos</a>
                                @endif
                                @if (Auth::user()->hasPermissionTo('mantenimientos'))
                                    <a class="nav-link" href="{{ route('mantenimientos') }}">Mantenimientos</a>
                                @endif
                                @if (Auth::user()->hasPermissionTo('soportes'))
                                    <a class="nav-link" href="{{ route('soportes.index') }}">Soportes</a>
                                @endif
                            </nav>
                        </div>
                    @endif
                    {{-- Reemplazamos @canany(['servicios', 'proveedores', 'valores']) --}}
                    @if (Auth::user()->hasAnyPermission(['servicios', 'proveedores', 'valores', 'mails.index']))
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#collapseStock" aria-expanded="false" aria-controls="collapseStock">
                            <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                            Inventario
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseStock" aria-labelledby="headingStock"
                            data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                @if (Auth::user()->hasPermissionTo('servicios'))
                                    <a class="nav-link" href="{{ route('servicios') }}">Servicios</a>
                                @endif
                                @if (Auth::user()->hasPermissionTo('proveedores'))
                                    <a class="nav-link" href="{{ route('proveedores') }}">Proveedores</a>
                                @endif
                                @if (Auth::user()->hasPermissionTo('valores'))
                                    <a class="nav-link" href="{{ route('valores') }}">Valores</a>
                                @endif
                                @if (Auth::user()->hasPermissionTo('mails.index'))
                                    <a class="nav-link" href="{{ route('mails.index') }}">Correos</a>
                                @endif
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
            <div class="sb-sidenav-footer">
                <div class="small">Iniciado como:</div>
                @if (Auth::user()->roles->isNotEmpty())
                    {{ implode(', ', Auth::user()->roles->pluck('name')->toArray()) }}
                @else
                    Sin rol asignado
                @endif
            </div>
        </nav>
    </div>
</div>
