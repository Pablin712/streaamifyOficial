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

    <!-- CSS para notificaciones de chat (solo si tiene permiso) -->
    @if(Auth::check() && Auth::user()->can('chat.ver'))
        <link rel="stylesheet" href="{{ asset('css/chat-system.css') }}?v={{ filemtime(public_path('css/chat-system.css')) }}">
    @endif

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
        $(document).ready(function() {
            $("#marcarLeidas").click(function() {
                $.ajax({
                    url: "{{ route('notificaciones.leer') }}",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#contadorNotificaciones").remove(); // Ocultar contador
                        }
                    }
                });
            });
        });
        $(document).ready(function() {
            $('.idcue').select2({
                placeholder: "Selecciona una cuenta",
                allowClear: true // Permite borrar la selección
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
</body>

</html>
