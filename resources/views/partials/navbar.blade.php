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
                    @php
                        $unreadNotifications = Auth::user()->unreadNotifications->sortByDesc('created_at')->values();
                        $visibleNotifications = $unreadNotifications->take(10);
                        $hiddenNotifications = max($unreadNotifications->count() - $visibleNotifications->count(), 0);
                    @endphp
                    <!-- Notificaciones en móvil -->
                    <li class="dropdown-header">
                        <i class="fas fa-bell me-2"></i>Notificaciones
                        @if ($unreadNotifications->count() > 0)
                            <span class="badge bg-danger ms-2">
                                {{ $unreadNotifications->count() }}
                            </span>
                        @endif
                    </li>

                    @if ($visibleNotifications->isNotEmpty())
                        <li>
                            <div class="px-2 pb-2" style="max-height: 52vh; overflow-y: auto; min-width: 320px;">
                                @foreach ($visibleNotifications as $notificacion)
                                    <a class="dropdown-item small rounded-3 mb-1 notification-link"
                                        href="{{ $notificacion->data['url'] ?? '#' }}"
                                        data-notification-id="{{ $notificacion->id }}"
                                        data-url="{{ $notificacion->data['url'] ?? '#' }}">
                                        <small class="text-muted d-block">{{ $notificacion->created_at->diffForHumans() }}</small>
                                        {{ Str::limit($notificacion->data['mensaje'], 90) }}
                                    </a>
                                @endforeach
                            </div>
                        </li>
                    @else
                        <li><span class="dropdown-item text-muted small">No hay notificaciones</span></li>
                    @endif

                    @if ($hiddenNotifications > 0)
                        <li><span class="dropdown-item-text text-muted small">Mostrando las últimas 10. Quedan {{ $hiddenNotifications }} más.</span></li>
                    @endif

                    @if ($unreadNotifications->count() > 0)
                        <li>
                            <button class="dropdown-item text-center js-mark-all-notifications" type="button">
                                Marcar todas como leídas
                            </button>
                        </li>
                    @endif

                    <li><hr class="dropdown-divider"></li>

                    <!-- Dark Mode Toggle en móvil -->
                    <li>
                        <button class="dropdown-item" id="toggleDarkModeMobile">
                            <i class="fas fa-moon me-2" id="darkModeIconMobile"></i>
                            <span id="darkModeTextMobile">Modo Oscuro</span>
                        </button>
                    </li>

                    <!-- Modo Concentración en móvil -->
                    @auth
                    @if(Auth::user()->hasRole('Trabajador externo'))
                    <li>
                        <span class="dropdown-item text-warning fw-semibold" style="cursor:default;">
                            <i class="fas fa-crosshairs me-2 text-warning"></i>
                            Modo Concentración <span class="badge bg-warning text-dark ms-1">Siempre activo</span>
                        </span>
                    </li>
                    @else
                    <li>
                        <form method="POST" action="{{ route('concentracion.toggle') }}">
                            @csrf
                            <button type="submit" class="dropdown-item {{ session('modo_concentracion') ? 'text-warning fw-semibold' : '' }}">
                                <i class="fas fa-crosshairs me-2 {{ session('modo_concentracion') ? 'text-warning' : '' }}"></i>
                                Modo Concentración {{ session('modo_concentracion') ? '(ON)' : '' }}
                            </button>
                        </form>
                    </li>
                    @endif
                    @endauth

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
                    @php
                        $unreadNotifications = Auth::user()->unreadNotifications->sortByDesc('created_at')->values();
                        $visibleNotifications = $unreadNotifications->take(10);
                        $hiddenNotifications = max($unreadNotifications->count() - $visibleNotifications->count(), 0);
                    @endphp
                    <!-- Notificaciones (Desktop) -->
                    <li class="nav-item dropdown">
                        <button class="nav-link navbar-icon-btn" id="notificacionesDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false" title="Notificaciones">
                            <i class="fas fa-bell fa-lg"></i>
                            @if ($unreadNotifications->count() > 0)
                                <span id="contadorNotificaciones"
                                    class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">
                                    {{ $unreadNotifications->count() }}
                                </span>
                            @endif
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow p-0 overflow-hidden" aria-labelledby="notificacionesDropdown"
                            style="min-width: 360px;">
                            <li class="dropdown-header">Notificaciones</li>

                            @if ($visibleNotifications->isNotEmpty())
                                <li>
                                    <div style="max-height: min(70vh, 460px); overflow-y: auto;">
                                        @foreach ($visibleNotifications as $notificacion)
                                            <a class="dropdown-item py-3 notification-link"
                                                href="{{ $notificacion->data['url'] ?? '#' }}"
                                                data-notification-id="{{ $notificacion->id }}"
                                                data-url="{{ $notificacion->data['url'] ?? '#' }}">
                                                <small class="text-muted d-block mb-1">{{ $notificacion->created_at->diffForHumans() }}</small>
                                                {{ Str::limit($notificacion->data['mensaje'], 120) }}
                                            </a>
                                        @endforeach
                                    </div>
                                </li>
                            @else
                                <li><span class="dropdown-item text-center text-muted">No hay notificaciones</span></li>
                            @endif

                            @if ($hiddenNotifications > 0)
                                <li><span class="dropdown-item-text small text-muted">Mostrando las últimas 10. Quedan {{ $hiddenNotifications }} más.</span></li>
                            @endif

                            @if ($unreadNotifications->count() > 0)
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <button class="dropdown-item text-center js-mark-all-notifications" type="button">
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

                    <!-- Modo Concentración (Desktop) -->
                    @if(Auth::user()->hasRole('Trabajador externo'))
                    <li class="nav-item position-relative">
                        <span class="nav-link navbar-icon-btn text-warning" style="cursor:default;"
                              title="Modo concentración siempre activo para tu rol">
                            <i class="fas fa-crosshairs fa-lg"></i>
                            <span class="badge bg-warning text-dark position-absolute"
                                  style="top:2px;right:2px;font-size:0.55rem;padding:2px 4px;border-radius:3px;">🔒</span>
                        </span>
                    </li>
                    @else
                    <li class="nav-item position-relative">
                        <form method="POST" action="{{ route('concentracion.toggle') }}" class="d-inline">
                            @csrf
                            <button type="submit"
                                class="nav-link btn btn-link navbar-icon-btn {{ session('modo_concentracion') ? 'text-warning' : '' }}"
                                title="{{ session('modo_concentracion') ? 'Modo concentración activo — clic para desactivar' : 'Activar modo concentración' }}">
                                <i class="fas fa-crosshairs fa-lg"></i>
                                @if(session('modo_concentracion'))
                                    <span class="badge bg-warning text-dark position-absolute"
                                          style="top:2px;right:2px;font-size:0.55rem;padding:2px 4px;border-radius:3px;">ON</span>
                                @endif
                            </button>
                        </form>
                    </li>
                    @endif

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
