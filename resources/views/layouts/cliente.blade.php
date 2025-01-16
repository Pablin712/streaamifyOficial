<!DOCTYPE html>
<html lang="en">

<head>
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
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="{{ asset('css/styles2.css') }}" rel="stylesheet" />
    @yield('styles')
    <style>
        .btn-outline-primary {
            border-color: #274698;
            color: #274698;
            background-color: transparent;
        }

        .btn-outline-primary:hover {
            background-color: #274698;
            /* Color de fondo al pasar el mouse */
            color: white;
            /* Color del texto al pasar el mouse */
            border-color: #274698;
        }
    </style>
</head>

<body id="page-top">
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm" id="mainNav">
        <div class="container px-5">
            <a class="navbar-brand fw-bold" href="#page-top">Streamify HQ</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive"
                aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                Menu
                <i class="bi-list"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ms-auto me-4 my-3 my-lg-0">
                    <li class="nav-item"><a class="nav-link me-lg-3" href="{{ route('principal') }}#features">Fortalezas</a></li>
                    <li class="nav-item"><a class="nav-link me-lg-3" href="{{ route('principal') }}#combos">Combos</a></li>
                    <li class="nav-item"><a class="nav-link me-lg-3" href="{{ route('shop') }}#catalog">Catálogo</a></li>
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
                        <!-- Menú de usuario autenticado -->
                        <div class="dropdown">
                            <button class="btn btn-light border rounded-pill text-dark fw-bold dropdown-toggle"
                                type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false"
                                style="background-color: #E4B100;">
                                <i class="bi bi-person-circle me-2 text-dark"></i>{{ auth()->user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userMenu"
                                style="background-color: #FFFFFF; border-color: #E4B100;">
                                <li><a class="dropdown-item text-dark fw-semibold"
                                        href="{{ route('profile') }}">Perfil</a></li>
                                <li><a class="dropdown-item text-danger fw-semibold" href="{{ route('logout') }}">Cerrar
                                        sesión</a></li>
                            </ul>
                        </div>
                    @else
                        <!-- Opciones de Sign Up y Login -->
                        <a href="{{ route('cliente.login') }}" class="btn btn-outline-primary me-2 rounded-pill fw-bold"> {{-- style="border-color: #274698; color: #274698; --}}
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                        <a href="{{ route('register') }}" class="btn rounded-pill fw-bold text-white"
                            style="background-color: #D41216;">
                            <i class="bi bi-person-plus-fill me-1"></i>Sign Up
                        </a>
                    @endif
                </div>

            </div>
        </div>
    </nav>
    <header class="masthead">
        @yield('header')
    </header>
    @yield('sections')
    <!-- Mashead header-->
    <footer id="pie" class="bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-2">© 2024 Streamify. Todos los derechos reservados.</p>
            <p class="small">Diseñado por Pablo Jiménez</p>
            <div>
                <a href="https://www.facebook.com/share/1Cco5izY9Y/?mibextid=wwXIfr" class="text-white me-3"
                    target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook"></i></a>
                {{-- <a href="#" class="text-white me-3"><i class="bi bi-twitter"></i></a> --}}
                <a href="https://www.instagram.com/stribarra" class="text-white me-3" target="_blank"
                    rel="noopener noreferrer"><i class="bi bi-instagram"></i></a>
                <a href="https://www.tiktok.com/@lv_pablin" class="text-white" target="_blank"
                    rel="noopener noreferrer"><i class="bi bi-tiktok"></i></a>
            </div>
        </div>
    </footer>
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="{{ asset('js/scripts2.js') }}"></script>
    <!-- * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
    <!-- * *                               SB Forms JS                               * *-->
    <!-- * * Activate your form at https://startbootstrap.com/solution/contact-forms * *-->
    <!-- * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
    <script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>
    @yield('scripts')
</body>

</html>
