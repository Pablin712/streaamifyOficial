<!DOCTYPE html>
{{-- data-theme  = tema GLOBAL que fija el administrador (lo ve todo el mundo).
     data-color-scheme = preferencia PERSONAL de claro/oscuro de quien mira.
     El atributo data-dark-mode lo resuelve partials/apariencia-esquema. --}}
<html lang="es" data-theme="{{ $apariencia['tema'] }}"
    data-color-scheme="{{ $apariencia['esquema'] }}"
    @if ($apariencia['esquema'] === 'dark') data-dark-mode="true" data-bs-theme="dark" @endif>

<head>
    @include('partials.apariencia-esquema')
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>@yield('title', 'Streamify')</title>

    {{-- La apariencia ya viene aplicada en el <html> desde el servidor, asi que
         aqui solo se entrega la configuracion a JavaScript. --}}
    @include('partials.apariencia-config')

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
    <link rel="icon" href="{{ asset('images/Icono.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}?v={{ filemtime(public_path('css/sidebar.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}?v={{ filemtime(public_path('css/navbar.css')) }}">

    {{-- Font Awesome 6, versión CSS. Sustituye a js/all.js, que pesaba ~1 MB y
         bloqueaba el renderizado desde dentro del <body>. --}}
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Sistema de Temas Dinámicos -->
    <link rel="stylesheet" href="{{ asset('css/themes.css') }}?v={{ filemtime(public_path('css/themes.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/enhanced-table-global.css') }}?v={{ filemtime(public_path('css/enhanced-table-global.css')) }}">
    {{-- modal-system.css quedo con cache de servidor (LSCache) que ignora el ?v= de cache-busting;
         se renombra el archivo fisico cada vez que cambia su contenido para forzar una URL nueva. --}}
    <link rel="stylesheet" href="{{ asset('css/modal-system.v2.css') }}?v={{ filemtime(public_path('css/modal-system.v2.css')) }}">

    {{-- Sistema de diseño Streamify: tipografía + tokens + puente Bootstrap.
         Va SIEMPRE al final de los CSS de framework y antes de @yield('styles')
         para que una vista pueda seguir sobrescribiendo puntualmente.
         Comparte tokens con el layout público (layouts/cliente.blade.php),
         así que panel y landing quedan visualmente idénticos. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet"
        href="{{ asset('css/streamify-ui.css') }}?v={{ filemtime(public_path('css/streamify-ui.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('css/streamify-themes.css') }}?v={{ filemtime(public_path('css/streamify-themes.css')) }}">

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
                @auth
                @php $concLocked = Auth::user()->hasRole('Trabajador externo'); @endphp
                @if($concLocked || session('modo_concentracion'))
                    <div class="d-flex align-items-center gap-2 mb-0 py-2 px-4 text-dark"
                         style="background:#fff3cd;border-bottom:2px solid #f0ad4e;">
                        <i class="fas fa-crosshairs text-warning"></i>
                        @if($concLocked)
                            <span><strong>Modo concentración activo</strong> — solo ves los registros de tus tareas asignadas.</span>
                            <span class="badge bg-warning text-dark ms-1">Rol: Trabajador externo</span>
                        @else
                            <span><strong>Modo concentración activo</strong> — solo ves los registros relacionados con tus tareas pendientes.</span>
                            <form method="POST" action="{{ route('concentracion.toggle') }}" class="ms-auto mb-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning py-0 px-2">Desactivar</button>
                            </form>
                        @endif
                    </div>
                @endif
                @endauth
                @yield('main')
            </main>
        </div>
    </div>

    <!-- Modales fuera del contenedor principal para que cubran toda la pantalla -->
    @yield('modals')

    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    {{-- Font Awesome se cargaba aquí como `js/all.js`: un script de ~1 MB, en
         medio del <body>, que sustituye cada <i> por un <svg> al ejecutarse.
         Hasta que terminaba, los iconos se veían vacíos — de ahí la sensación
         de que "no cargan". La hoja de estilos hace lo mismo, pesa una
         fracción y no bloquea el renderizado. El marcado <i class="fas fa-x">
         es idéntico en ambas versiones, así que no hay que tocar las vistas.
         Se carga en el <head> (ver arriba); esta línea queda como referencia
         del cambio. --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="{{ asset('js/scripts.js') }}?v={{ filemtime(public_path('js/scripts.js')) }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('js/sidebar.js') }}?v={{ filemtime(public_path('js/sidebar.js')) }}"></script>
    <script src="{{ asset('js/navbar.js') }}?v={{ filemtime(public_path('js/navbar.js')) }}"></script>

    {{-- Alpine NO se carga aquí a propósito.

         Livewire 3 ya trae Alpine incorporado y lo arranca él mismo en
         @livewireScripts (al final de este layout). Cargarlo además desde el
         CDN dejaba DOS instancias corriendo a la vez — la consola avisaba con
         "Detected multiple instances of Alpine running" — y entonces x-show
         dejaba de reaccionar de forma intermitente: los modales abrían unas
         veces sí y otras no, en todo el panel.

         Si alguna vez hace falta un plugin de Alpine, se registra con
         document.addEventListener('alpine:init', ...) antes de @livewireScripts,
         nunca volviendo a cargar Alpine por separado. --}}

    <script>
        /*
         * Abrir un modal desde la URL:  /admin/servicios?modal=createServicioModal
         *
         * La mayoría de los módulos ya no tienen vista `create` propia — se crea
         * desde un modal dentro del listado. Sin esto, un acceso directo de
         * "crear" solo podía llevar al listado y el usuario tenía que buscar el
         * botón. Los modales escuchan `open-modal` (ver components/modal),
         * así que basta con emitirlo cuando Alpine ya está listo.
         *
         * El parámetro se borra de la URL después: si no, recargar la página
         * volvería a abrir el modal.
         */
        (function () {
            const modal = new URLSearchParams(window.location.search).get('modal');
            if (!modal) return;

            let abierto = false;

            function abrir() {
                if (abierto) return;
                abierto = true;

                window.dispatchEvent(new CustomEvent('open-modal', { detail: modal }));

                const url = new URL(window.location);
                url.searchParams.delete('modal');
                window.history.replaceState({}, '', url);
            }

            // Alpine avisa cuando ya ha montado los componentes.
            document.addEventListener('alpine:initialized', abrir);

            // Reserva: si Alpine ya se había inicializado antes de registrar el
            // listener (Livewire también lo arranca), el evento no vuelve a
            // dispararse y el modal nunca se abriria.
            window.addEventListener('load', () => setTimeout(abrir, 300));
        })();
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <!-- Inicializador automático de searchable-select -->
    <script src="{{ asset('js/searchable-select.js') }}?v={{ filemtime(public_path('js/searchable-select.js')) }}"></script>

    <!-- Sistema de Temas Dinámicos -->
    <script src="{{ asset('js/decorations.js') }}?v={{ filemtime(public_path('js/decorations.js')) }}"></script>
    <script src="{{ asset('js/theme-manager.js') }}?v={{ filemtime(public_path('js/theme-manager.js')) }}"></script>

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
        /*
         * Ping de asistencia cada 5 minutos.
         *
         * El token CSRF se incrusta al cargar la página, así que en cuanto la
         * sesión caduca este ping empieza a devolver 419 indefinidamente. Antes
         * no se comprobaba la respuesta ni había `catch`, así que se acumulaban
         * fallos en la consola y en el log del servidor.
         */
        (function () {
            const RUTA_PING = @json(route('asistencias.ping'));
            const RUTA_LOGIN = @json(route('login'));
            let temporizador = null;

            async function registrarPresencia() {
                try {
                    const respuesta = await fetch(RUTA_PING, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': @json(csrf_token()),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ ruta_actual: window.location.pathname }),
                    });

                    // Sesión caducada: se deja de insistir y se va al login.
                    if (respuesta.status === 419 || respuesta.status === 401) {
                        clearInterval(temporizador);
                        window.location.href = RUTA_LOGIN;
                    }
                } catch (e) {
                    // Sin conexión o servidor caído: se ignora, el siguiente
                    // intento lo resolverá. No tiene sentido molestar por esto.
                }
            }

            // Sin sentido registrar presencia con la pestaña en segundo plano.
            temporizador = setInterval(() => {
                if (document.visibilityState === 'visible') registrarPresencia();
            }, 300000); // 5 minutos
        })();
    </script>
    {{-- js/sistema.js retirado: registraba un SEGUNDO manejador sobre el mismo
         botón #toggleDarkMode que navbar.js (doble alternancia) y llamaba a
         ThemeManager.setTheme('dark'), un tema que nunca existió en el
         catálogo. Su función la cubren navbar.js + theme-manager.js. --}}

    {{-- Livewire Scripts (REQUERIDO para notificador global y otros componentes) --}}
    @livewireScripts

    <script>
        /*
         * Livewire, cuando una petición falla, inyecta la página de error del
         * servidor en un modal a pantalla completa. Como el notificador sondea
         * en segundo plano, al caducar la sesión o al fallar la base de datos
         * ese cuadro le saltaba al empleado una y otra vez, y había que
         * cerrarlo a mano para poder seguir.
         *
         * Aquí se interceptan los fallos: si la sesión caducó se va al login
         * directamente; si es un problema temporal del servidor se ignora y el
         * siguiente sondeo lo reintenta solo.
         */
        document.addEventListener('livewire:init', () => {
            let avisoCaducada = false;

            Livewire.hook('request', ({ fail }) => {
                fail(({ status, preventDefault }) => {
                    // Sesión caducada: al login, sin cuadro de error.
                    if (status === 419 || status === 401) {
                        preventDefault();
                        if (avisoCaducada) return;
                        avisoCaducada = true;
                        window.location.href = @json(route('login'));
                        return;
                    }

                    // Caída temporal del servidor o de la base de datos: se
                    // ignora en silencio, el siguiente sondeo reintenta.
                    if (status === 0 || status >= 500) {
                        preventDefault();
                        console.warn('[Livewire] petición fallida (' + status + '); se reintentará.');
                    }
                });
            });
        });
    </script>

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
