<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Accede al panel de administración de Streamify." />
    <meta name="author" content="Pablo Jiménez" />
    <title>Streamify - Administración</title>
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
</head>

<body id="page-top">
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm" id="mainNav">
        <div class="container px-5">
            <a class="navbar-brand fw-bold" href="#page-top">Streamify HQ - Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive"
                aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                Menu
                <i class="bi-list"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ms-auto me-4 my-3 my-lg-0">
                    <li class="nav-item"><a class="nav-link me-lg-3" href="{{ route('principal')}}">Volver al Inicio</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <header class="masthead">
        <div class="container px-5">
            <div class="row gx-5 align-items-center">
                <div class="col-lg-6">
                    <div class="mb-5 mb-lg-0 text-center text-lg-start">
                        <h1 class="display-3 lh-1 mb-3">Accede al Panel de Administración</h1>
                        <p class="lead fw-normal text-muted mb-5">Inicia sesión o regístrate para acceder a las
                            herramientas administrativas de Streamify HQ.</p>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                            {{--  
                            <a href="{{ route('login') }}" class="btn btn-primary rounded-pill px-4">Iniciar Sesión</a>
                            <a href="{{ route('register') }}"
                                class="btn btn-outline-secondary rounded-pill px-4">Registrarse</a>
                            --}}
                            <a href="{{ route('login') }}" class="btn rounded-pill px-4"
                                style="background-color: #E4B100; color: #1D1D1B; border: none;">Iniciar Sesión</a>
                            <a href="{{ route('register') }}" class="btn rounded-pill px-4"
                                style="background-color: #575756; color: #FFFFFF; border: none;">Registrarse</a>

                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-flex align-items-center justify-content-center">
                    <div class="masthead-device-mockup">
                        <img src="{{ asset('images/1.png') }}" alt="Acceso de Empleados" class="rounded shadow-lg"
                            style="width: 80%; max-width: 600px; height: auto; object-fit: cover;" />
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Footer -->
    <footer id="pie" class="bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-2">© 2024 Streamify HQ. Todos los derechos reservados.</p>
            <p class="small">Diseñado por Pablo Jiménez</p>
            <div>
                <a href="https://www.facebook.com/share/1Cco5izY9Y/?mibextid=wwXIfr" class="text-white me-3"
                    target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook"></i></a>
                <a href="https://www.instagram.com/stribarra" class="text-white me-3" target="_blank"
                    rel="noopener noreferrer"><i class="bi bi-instagram"></i></a>
                <a href="https://www.tiktok.com/@lv_pablin" class="text-white" target="_blank"
                    rel="noopener noreferrer"><i class="bi bi-tiktok"></i></a>
            </div>
        </div>
    </footer>

    <!-- Bootstrap core JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS -->
    <script src="{{ asset('js/scripts2.js') }}"></script>
</body>

</html>
