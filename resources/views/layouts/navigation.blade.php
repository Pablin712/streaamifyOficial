<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>@yield('title', 'Streamify')</title>

    <!-- ⚡ APLICACIÓN INMEDIATA DE DARK MODE (antes de cargar CSS) -->
    <script>
        (function() {
            try {
                const darkMode = localStorage.getItem('streamify_dark_mode');
                const theme = localStorage.getItem('streamify_theme');
                if (darkMode === 'true') {
                    document.documentElement.setAttribute('data-dark-mode', 'true');
                }
                if (theme) {
                    document.documentElement.setAttribute('data-theme', theme);
                }
            } catch (e) {}
        })();
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
    <link rel="icon" href="{{ asset('images/Icono.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">

    <!-- Sistema de Temas Dinámicos -->
    <link rel="stylesheet" href="{{ asset('css/themes.css') }}?v={{ filemtime(public_path('css/themes.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/enhanced-table-global.css') }}?v={{ filemtime(public_path('css/enhanced-table-global.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/modal-system.css') }}?v={{ filemtime(public_path('css/modal-system.css')) }}">

    @yield('styles')
    @livewireStyles
</head>

<body class="sb-nav-fixed">
    <!-- Notificador Global de Chat (solo para empleados con permiso) -->
    @if(Auth::check() && Auth::user()->can('chat.ver'))
        @livewire('chat.notificador-global')
    @endif

    @include('partials.navbar')

    <div id="layoutSidenav">
        @include('partials.sidebar')

        <div id="layoutSidenav_content" style="min-height: 100vh; display: flex; flex-direction: column;">
            <main style="flex: 1;">
                @yield('main')
            </main>
        </div>
    </div>

    <!-- Modales fuera del contenedor principal para que cubran toda la pantalla -->
    @yield('modals')

    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script src="{{ asset('js/navbar.js') }}"></script>

    <!-- Alpine.js para modales - DEBE cargarse ANTES de jQuery y Select2 -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <!-- Inicializador automático de searchable-select -->
    <script src="{{ asset('js/searchable-select.js') }}"></script>

    <!-- Sistema de Temas Dinámicos -->
    <script src="{{ asset('js/decorations.js') }}"></script>
    <script src="{{ asset('js/theme-manager.js') }}"></script>

    @yield('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            function updateNotificationCounter(unreadCount) {
                const counter = document.getElementById('contadorNotificaciones');

                if (!unreadCount || unreadCount <= 0) {
                    if (counter) {
                        counter.remove();
                    }
                    return;
                }

                if (counter) {
                    counter.textContent = unreadCount;
                    return;
                }

                const bellButton = document.getElementById('notificacionesDropdown');
                if (!bellButton) {
                    return;
                }

                const badge = document.createElement('span');
                badge.id = 'contadorNotificaciones';
                badge.className = 'badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill';
                badge.textContent = unreadCount;
                bellButton.appendChild(badge);
            }

            async function postNotification(url) {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                return response.json();
            }

            document.querySelectorAll('.js-mark-all-notifications').forEach(function(button) {
                button.addEventListener('click', async function() {
                    try {
                        const response = await postNotification("{{ route('notificaciones.leer') }}");
                        if (response.success) {
                            updateNotificationCounter(response.unread_count ?? 0);
                            document.querySelectorAll('.notification-link').forEach(link => link.remove());
                        }
                    } catch (error) {
                        console.error('No se pudieron marcar las notificaciones.', error);
                    }
                });
            });

            document.querySelectorAll('.notification-link').forEach(function(link) {
                link.addEventListener('click', async function(event) {
                    const notificationId = link.dataset.notificationId;
                    const destinationUrl = link.dataset.url || link.getAttribute('href') || '#';

                    if (!notificationId) {
                        return;
                    }

                    event.preventDefault();

                    try {
                        const response = await postNotification("{{ route('notificaciones.leer.una', ':id') }}".replace(':id', notificationId));
                        if (response.success) {
                            updateNotificationCounter(response.unread_count ?? 0);
                        }
                    } catch (error) {
                        console.error('No se pudo marcar la notificación como leída.', error);
                    } finally {
                        if (destinationUrl && destinationUrl !== '#') {
                            window.location.href = destinationUrl;
                        }
                    }
                });
            });

            $('.idcue').select2({
                placeholder: 'Selecciona una cuenta',
                allowClear: true
            });
        });
    </script>
    <script>
        setInterval(() => {
            fetch("{{ route('asistencias.ping') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    ruta_actual: window.location.pathname
                })
            });
        }, 300000); // 5 minutos = 300,000 ms
    </script>
    <script src="{{asset('js/sistema.js')}}"></script>

    {{-- Livewire Scripts (REQUERIDO para notificador global y otros componentes) --}}
    @livewireScripts

    {{-- Widget de Chat con IA (solo para empleados) --}}
    @if(Auth::check())
        <div id="chat-ai-widget-mount"></div>

        <script>
            // ID del empleado autenticado (para tracking en n8n)
            window.empleadoId = {{ Auth::id() }};

            // Configuración del chat con IA para empleados
            window.chatAIConfig = {
                apiUrl: "{{ route('api.v2.chat.ai.send') }}",
                csrfToken: "{{ csrf_token() }}"
            };

            // Inicializar el widget cuando el DOM esté listo
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    if (typeof window.initChatWidgetAI === 'function') {
                        window.initChatWidgetAI(window.chatAIConfig);
                    } else {
                        console.error('Chat AI widget no disponible');
                    }
                }, 300);
            });
        </script>

        <!-- Chat Widget AI JS (module) -->
        @vite(['resources/js/chat-widget-ai.js'])
    @endif
</body>

</html>
