<nav class="sb-topnav navbar navbar-expand-lg navbar-light inverted-green-navbar">
    <div class="container-fluid">
        <!-- Logo / Nombre de la app -->
        <a class="navbar-brand ps-3" href="{{ route('inicio') }}">Streamify HQ</a>

        <!-- Botón para colapsar el sidebar -->
        <button class="btn btn-outline-primary me-auto order-2" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <!-- Contenido del navbar -->
        <div class="navbar-collapse justify-content-end order-3 main-header-right" id="navbarContent">
            <ul class="navbar-nav me-3 me-lg-4">
                @auth
                    <!-- Menú de usuario -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                            role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user fa-fw"></i>
                            {{ Auth::user()->nombreemp }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('empleados.edit', Auth::user()->idemp) }}">
                                <i class="fas fa-cog"></i> Ajustes
                            </a></li>
                            
                            @if (Auth::user()->hasPermissionTo('historial'))
                                <li><a class="dropdown-item" href="{{ route('historial') }}">
                                    <i class="fas fa-history"></i> Actividad
                                </a></li>
                            @endif

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </ul>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>