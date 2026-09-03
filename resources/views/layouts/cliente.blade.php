<!DOCTYPE html>
{{-- Mismo sistema de apariencia que el panel: el tema y el modo oscuro los
     define el administrador desde /admin/sistema y se pintan desde el servidor.
     Hasta ahora las vistas de cliente no tenian modo oscuro en absoluto. --}}
<html lang="es" data-theme="{{ $apariencia['tema'] }}"
    @if ($apariencia['modoOscuro']) data-dark-mode="true" data-bs-theme="dark" @else data-bs-theme="light" @endif>

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description"
        content="Adquiere tu suscripción premium
            para disfrutar de contenido exclusivo en Streamify." />
    <meta name="author" content="Pablo Jiménez" />
    <title>@yield('title')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/Icono.png') }}" />
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Google fonts-->
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,600;1,600&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,300;0,500;0,600;0,700;1,300;1,500;1,600;1,700&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,400;1,400&amp;display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="{{ asset('css/styles2.css') }}" rel="stylesheet" />
    <!-- Mundial 2026 -->
    <link href="{{ asset('css/mundial-2026.css') }}?v=1" rel="stylesheet" />

    {{-- Sistema de diseño Streamify: tipografía + tokens + puente Bootstrap.
         Va SIEMPRE al final de los CSS de framework y antes de @yield('styles')
         para que una vista pueda seguir sobrescribiendo puntualmente. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet"
        href="{{ asset('css/streamify-ui.css') }}?v={{ filemtime(public_path('css/streamify-ui.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('css/streamify-themes.css') }}?v={{ filemtime(public_path('css/streamify-themes.css')) }}">

    {{-- Aquí NO se incluye partials/apariencia-config ni theme-manager.js.
         El sitio público solo necesita ver el tema, no cambiarlo: los atributos
         del <html> ya vienen resueltos por el servidor en cada carga. Enviar el
         catálogo de temas y un sondeo periódico a cada visitante sería peso
         inútil en la parte del sitio que más tráfico recibe. --}}

    @yield('styles')
    {{-- Nota: el antiguo override de .btn-outline-primary con #274698 incrustado
         se eliminó — streamify-ui.css ya lo resuelve con el token --sf-brand. --}}
</head>

<body id="page-top">

    <!-- ⚽ Mundial 2026 — barra temporal (activada por JS según fecha) -->
    <div id="mundial-bar" role="banner" aria-label="FIFA World Cup 2026">
        <div class="mundial-bar-inner">
            <span class="mundial-icon">⚽</span>
            <span class="mundial-title">FIFA World Cup 2026™</span>
            <span class="mundial-flags">🇲🇽 🇺🇸 🇨🇦</span>
            <span class="mundial-sep">·</span>
            <span id="mundial-countdown" class="mundial-countdown">Cargando...</span>
        </div>
        <button class="mundial-close" type="button" title="Cerrar">✕</button>
    </div>

    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm" id="mainNav">
        <div class="container px-5">
            <a class="navbar-brand fw-bold" href="{{ route('principal') }}">Streamify HQ</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive"
                aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                Menu
                <i class="bi-list"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ms-auto me-4 my-3 my-lg-0">
                    @yield('menu')
                    <li class="nav-item"><a class="nav-link me-lg-3" href="{{ route('tutorial') }}">Tutorial</a>
                </ul>
                <a href="https://wa.me/593961412826" target="_blank" class="me-3">
                    <button class="btn btn-success rounded-pill px-3 mb-2 mb-lg-0">
                        <span class="d-flex align-items-center">
                            <!-- Icono de WhatsApp -->
                            <i class="bi bi-whatsapp me-2"></i>
                            <span class="small">Contáctanos</span>
                        </span>
                    </button>
                </a>
                <div class="d-flex align-items-center">
                    @if (Auth::guard('cliente')->check())
                        <!-- Mostrar saldo del cliente -->
                        <span class="me-3 sf-saldo">
                            Saldo: ${{ number_format(Auth::guard('cliente')->user()->saldo, 2) }}
                        </span>
                        <!-- Menú de usuario autenticado -->
                        <div class="dropdown">
                            <button class="btn border rounded-pill fw-bold dropdown-toggle sf-account-btn"
                                type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-2"></i>{{ Auth::guard('cliente')->user()->nombrecli }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userMenu">
                                <li><a class="dropdown-item fw-semibold"
                                        href="{{ route('cliente.perfil') }}">Perfil</a></li>
                                <li><a class="dropdown-item fw-semibold"
                                        href="{{ route('recargar.index') }}">Recargar saldo</a></li>
                                <li><a class="dropdown-item fw-semibold"
                                        href="{{ route('historial.cliente') }}">Actividad</a></li>
                                <li><a class="dropdown-item fw-semibold"
                                        href="{{ route('codigo.index') }}">Códigos</a></li>
                                <li><a class="dropdown-item text-danger fw-semibold"
                                        onclick="document.getElementById('logout-form').submit();">
                                        Cerrar sesión
                                    </a>
                                </li>
                                <form id="logout-form" action="{{ route('cliente.logout') }}" method="POST"
                                    class="d-none">
                                    @csrf
                                </form>
                            </ul>
                        </div>
                    @else
                        <!-- Opciones de Sign Up y Login -->
                        <a href="{{ route('cliente.login') }}"
                            class="btn btn-outline-primary me-2 rounded-pill fw-bold">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-danger rounded-pill fw-bold">
                            <i class="bi bi-person-plus-fill me-1"></i>Registrarme
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>
    @yield('mensaje')
    <header class="masthead">
        @yield('header')
    </header>
    @yield('sections')
    <!-- Mashead header-->
    <footer id="pie" class="text-center py-5">
        <div class="sf-container">
            <div class="mb-3">
                <a href="{{ route('donna') }}" class="sf-footer-highlight fw-semibold me-3">
                    <i class="bi bi-robot me-1"></i>Donna AI
                </a>
                <a href="{{ route('tutorial') }}">Tutorial</a>
            </div>
            <div class="mb-3">
                <a href="{{ route('legal.privacidad') }}" class="me-3">Política de Privacidad</a>
                <a href="{{ route('legal.terminos') }}">Condiciones de Servicio</a>
            </div>
            <div class="sf-footer-social mb-3">
                <a href="https://www.facebook.com/share/1Cco5izY9Y/?mibextid=wwXIfr" class="me-3" target="_blank"
                    rel="noopener noreferrer" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                <a href="https://www.instagram.com/stribarra" class="me-3" target="_blank"
                    rel="noopener noreferrer" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                <a href="https://www.tiktok.com/@lv_pablin" class="me-3" target="_blank"
                    rel="noopener noreferrer" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                <a href="https://t.me/Streamifyhq" target="_blank" rel="noopener noreferrer" aria-label="Telegram">
                    <i class="bi bi-telegram"></i>
                </a>
            </div>
            <p class="mb-1">© <span id="currentYear"></span> Streamify. Todos los derechos reservados.</p>
            <p class="mb-0 small">Diseñado por Pablo Jiménez</p>
        </div>
    </footer>
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')

    <!-- Core theme JS-->
    <script src="{{ asset('js/scripts2.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const yearSpan = document.getElementById("currentYear");
            if (yearSpan) {
                yearSpan.textContent = new Date().getFullYear();
            }
        });
    </script>

    <!-- Widget de Chat -->
    <div id="chat-widget-mount"></div>

    <script>
        // Configuración de rutas para el chat widget
        window.chatConfig = {
            @if (Auth::guard('cliente')->check())
                clienteId: {{ Auth::guard('cliente')->user()->idcli }},
                enviarUrl: "{{ route('api.chat.cliente.enviar') }}",
                conversacionUrl: "{{ route('api.chat.cliente.conversacion', ['idcli' => Auth::guard('cliente')->user()->idcli]) }}",
            @else
                clienteId: null,
                enviarUrl: "{{ route('api.chat.anonimo.enviar') }}",
                conversacionUrl: "{{ url('/api/v1/chat/anonimo') }}", // Se completará con /{sessionId}/conversacion
            @endif
            csrfToken: "{{ csrf_token() }}"
        };

        // Inicializar el widget cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                if (typeof window.initChatWidget === 'function') {
                    window.initChatWidget(window.chatConfig);
                } else {
                    console.error('Chat widget no disponible');
                }
            }, 200);
        });
    </script>

    <!-- Chat Widget JS (module) -->
    @vite(['resources/js/chat-widget.js'])

    <!-- Mundial 2026 -->
    <script src="{{ asset('js/mundial-2026.js') }}?v=1"></script>

    {{-- comentado por n8n
    <link href="https://cdn.jsdelivr.net/npm/@n8n/chat/dist/style.css" rel="stylesheet" />
    <script type="module">
        import {
            createChat
        } from 'https://cdn.jsdelivr.net/npm/@n8n/chat/dist/chat.bundle.es.js';

        createChat({
            webhookUrl: 'https://autobot.aaronsoft.es/webhook/0d88cdca-bd3d-4cc2-a36b-672c5ee16b0b/chat'
        });
    </script>--}}

</body>

</html>
