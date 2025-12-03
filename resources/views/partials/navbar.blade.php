<nav class="sb-topnav navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <!-- Logo / Nombre de la app -->
        <a class="navbar-brand ps-3" href="{{ route('inicio') }}">Streamify HQ</a>

        <!-- Botón para colapsar el sidebar -->
        <button class="btn btn-outline-primary" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Menú dropdown para controles del navbar en móvil -->
        <div class="dropdown ms-auto d-lg-none">
            <button class="btn btn-navbar-menu" type="button" id="navbarMenuDropdown"
                data-bs-toggle="dropdown" aria-expanded="false" title="Menú">
                <i class="fas fa-ellipsis-v fa-lg"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end navbar-mobile-menu shadow" aria-labelledby="navbarMenuDropdown">
                @auth
                    <!-- Notificaciones en móvil -->
                    <li class="dropdown-header">
                        <i class="fas fa-bell me-2"></i>Notificaciones
                        @if (Auth::user()->unreadNotifications->count() > 0)
                            <span class="badge bg-danger ms-2">
                                {{ Auth::user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </li>
                    @forelse (Auth::user()->unreadNotifications->take(3) as $notificacion)
                        <li>
                            <a class="dropdown-item small" href="{{ $notificacion->data['url'] ?? '#' }}">
                                <small class="text-muted d-block">{{ $notificacion->created_at->diffForHumans() }}</small>
                                {{ Str::limit($notificacion->data['mensaje'], 50) }}
                            </a>
                        </li>
                    @empty
                        <li><a class="dropdown-item text-muted small">No hay notificaciones</a></li>
                    @endforelse

                    @if (Auth::user()->unreadNotifications->count() > 3)
                        <li><a class="dropdown-item text-center small text-primary" href="#" id="verTodasNotif">Ver todas...</a></li>
                    @endif

                    <li><hr class="dropdown-divider"></li>

                    <!-- Dark Mode Toggle en móvil -->
                    <li>
                        <button class="dropdown-item" id="toggleDarkModeMobile">
                            <i class="fas fa-moon me-2" id="darkModeIconMobile"></i>
                            <span id="darkModeTextMobile">Modo Oscuro</span>
                        </button>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <!-- Usuario en móvil -->
                    <li class="dropdown-header">
                        <i class="fas fa-user me-2"></i>{{ Auth::user()->nombreemp }}
                    </li>
                    <li><a class="dropdown-item" href="{{ route('empleados.edit', Auth::user()->idemp) }}">
                            <i class="fas fa-cog me-2"></i> Ajustes
                        </a></li>
                    @if (Auth::user()->hasPermissionTo('historial'))
                        <li><a class="dropdown-item" href="{{ route('historial') }}">
                                <i class="fas fa-history me-2"></i> Actividad
                            </a></li>
                    @endif
                    @if (Auth::user()->hasRole('Admin'))
                        <li><a class="dropdown-item" href="{{ route('sistema.index') }}">
                                <i class="fas fa-palette me-2"></i> Sistema
                            </a></li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li>
                    <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                @endauth
            </ul>
        </div>

        <!-- Contenido del navbar (solo visible en desktop) -->
        <div class="d-none d-lg-flex ms-auto" id="navbarContent">
            <ul class="navbar-nav ms-auto">
                @auth
                    <!-- Notificaciones (Desktop) -->
                    <li class="nav-item dropdown">
                        <button class="nav-link navbar-icon-btn" id="notificacionesDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false" title="Notificaciones">
                            <i class="fas fa-bell fa-lg"></i>
                            @if (Auth::user()->unreadNotifications->count() > 0)
                                <span id="contadorNotificaciones"
                                    class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">
                                    {{ Auth::user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notificacionesDropdown">
                            <li class="dropdown-header">Notificaciones</li>

                            @forelse (Auth::user()->unreadNotifications as $notificacion)
                                <li>
                                    <a class="dropdown-item" href="{{ $notificacion->data['url'] ?? '#' }}">
                                        <small class="text-muted">{{ $notificacion->created_at->diffForHumans() }}</small>
                                        <br>
                                        {{ $notificacion->data['mensaje'] }}
                                    </a>
                                </li>
                            @empty
                                <li><a class="dropdown-item text-center text-muted">No hay notificaciones</a></li>
                            @endforelse

                            @if (Auth::user()->unreadNotifications->count() > 0)
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <button class="dropdown-item text-center" id="marcarLeidas">
                                        Marcar todas como leídas
                                    </button>
                                </li>
                            @endif
                        </ul>
                    </li>

                    <!-- Botón cambiar modo -->
                    <li class="nav-item">
                        <button class="nav-link btn btn-link navbar-icon-btn" id="toggleDarkMode" title="Cambiar modo de visualización">
                            <i class="fas fa-moon fa-lg" id="darkModeIcon"></i>
                        </button>
                    </li>

                    <!-- Menú de usuario (Desktop) -->
                    <li class="nav-item dropdown">
                        <button class="nav-link dropdown-toggle navbar-user-btn" id="userDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user fa-fw me-1"></i>
                            <span>{{ Auth::user()->nombreemp }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="{{ route('empleados.edit', Auth::user()->idemp) }}">
                                    <i class="fas fa-cog me-2"></i> Ajustes
                                </a></li>

                            @if (Auth::user()->hasPermissionTo('historial'))
                                <li><a class="dropdown-item" href="{{ route('historial') }}">
                                        <i class="fas fa-history me-2"></i> Actividad
                                    </a></li>
                            @endif

                            @if (Auth::user()->hasRole('Admin'))
                                <li><a class="dropdown-item" href="{{ route('sistema.index') }}">
                                        <i class="fas fa-palette me-2"></i> Sistema
                                    </a></li>
                            @endif

                            <li>
                                <hr class="dropdown-divider">
                            </li>

                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
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
