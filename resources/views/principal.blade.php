<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Adquiere tu suscripción premium 
            para disfrutar de contenido exclusivo en Streamify." />
        <meta name="author" content="Pablo Jiménez" />
        <title>Promociones Streamify</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('images/Icono.png') }}" />
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Google fonts-->
        <link rel="preconnect" href="https://fonts.gstatic.com" />
        <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,600;1,600&amp;display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,300;0,500;0,600;0,700;1,300;1,500;1,600;1,700&amp;display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,400;1,400&amp;display=swap" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="{{ asset('css/styles2.css') }}" rel="stylesheet" />
    </head>
    <body id="page-top">
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm" id="mainNav">
            <div class="container px-5">
                <a class="navbar-brand fw-bold" href="#page-top">Streamify HQ</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu
                    <i class="bi-list"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ms-auto me-4 my-3 my-lg-0">
                        <li class="nav-item"><a class="nav-link me-lg-3" href="#features">Fortalezas</a></li>
                        <li class="nav-item"><a class="nav-link me-lg-3" href="#combos">Combos</a></li>
                    </ul>
                    <a href="https://wa.me/593961412826" target="_blank">
                        <button class="btn btn-success rounded-pill px-3 mb-2 mb-lg-0">
                            <span class="d-flex align-items-center">
                                <!-- Icono de WhatsApp -->
                                <i class="bi bi-whatsapp me-2"></i>
                                <span class="small">Contacta por WhatsApp</span>
                            </span>
                        </button>
                    </a>                    
                </div>
            </div>
        </nav>
        <!-- Mashead header-->
        <header class="masthead">
            <div class="container px-5">
                <div class="row gx-5 align-items-center">
                    <div class="col-lg-6">
                        <!-- Masthead text and app badges-->
                        <div class="mb-5 mb-lg-0 text-center text-lg-start">
                            <h1 class="display-1 lh-1 mb-3">Más que ver, vivir el Streaming Premium</h1>
                            <p class="lead fw-normal text-muted mb-5">Únete a nuestras suscripciones premium y disfruta de acceso ilimitado a las mejores plataformas de streaming. 
                                Experimenta contenido exclusivo, calidad superior y la comodidad de ver lo que amas, cuando quieras.</p>
                            <!-- Botón WhatsApp -->
                            <div class="mt-4">
                                {{--  
                                <a href="https://wa.me/593961412826" target="_blank" class="btn btn-whatsapp rounded-pill px-3 mb-2 mb-lg-0">
                                    <span class="d-flex align-items-center">
                                        <!-- Icono de WhatsApp -->
                                        <i class="bi bi-whatsapp me-2"></i>
                                        <span class="small">Contáctanos por WhatsApp</span>
                                    </span>
                                </a>
                                --}}
                                <a href="https://wa.me/593961412826" target="_blank" class="btn btn-whatsapp rounded-pill px-3 mb-2 mb-lg-0">
                                    <button class="btn btn-success rounded-pill px-3 mb-2 mb-lg-0">
                                        <span class="d-flex align-items-center">
                                            <!-- Icono de WhatsApp -->
                                            <i class="bi bi-whatsapp me-2"></i>
                                            <span class="small">Contacta por WhatsApp</span>
                                        </span>
                                    </button>
                                </a>  
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 d-flex align-items-center justify-content-center">
                        <!-- Reemplazamos el dispositivo con una imagen promocional -->
                        <div class="masthead-device-mockup">
                            <div class="text-center">
                                <!-- Aquí va la imagen promocional -->
                                <img 
                                    {{-- src="{{ asset('images/tik tok miniatura.png') }}" --}}  
                                    src="{{ asset('images/cuadrado/combos/33.png') }}"
                                    alt="Promoción de Streaming" 
                                    class="rounded shadow-lg"
                                    style="width: 80%; max-width: 600px; height: auto; object-fit: cover;"
                                />
                            </div>
                        </div>
                    </div>                                                       
                </div>
            </div>
        </header>
        <section id="features" class="py-5 bg-light"> {{-- style="background-color: #E4B100;" --}}
            <div class="container">
                <h2 class="text-center fw-bold mb-5">¿Por qué elegir Streamify?</h2>
                <div class="row text-center">
                    <!-- Característica 1 -->
                    <div class="col-md-3 mb-4">
                        <div class="feature-box shadow-sm p-4 rounded">
                            <i class="bi bi-lightning-charge-fill text-success display-4 mb-3"></i>
                            <h5 class="fw-bold">Entrega Inmediata</h5>
                            <p class="text-muted">Accede a tus suscripciones en pocos minutos después de realizar el pago.</p>
                        </div>
                    </div>
                    <!-- Característica 2 -->
                    <div class="col-md-3 mb-4">
                        <div class="feature-box shadow-sm p-4 rounded">
                            <i class="bi bi-headset text-success display-4 mb-3"></i>
                            <h5 class="fw-bold">Atención al Cliente</h5>
                            <p class="text-muted">Estamos disponibles 24/7 para resolver cualquier duda o problema.</p>
                        </div>
                    </div>
                    <!-- Característica 3 -->
                    <div class="col-md-3 mb-4">
                        <div class="feature-box shadow-sm p-4 rounded">
                            <i class="bi bi-check-circle-fill text-success display-4 mb-3"></i>
                            <h5 class="fw-bold">Confiable</h5>
                            <p class="text-muted">Garantizamos calidad y seguridad en cada una de tus suscripciones.</p>
                        </div>
                    </div>
                    <!-- Característica 4 -->
                    <div class="col-md-3 mb-4">
                        <div class="feature-box shadow-sm p-4 rounded">
                            <i class="bi bi-ui-checks-grid text-success display-4 mb-3"></i>
                            <h5 class="fw-bold">Fácil de Usar</h5>
                            <p class="text-muted">Configura tus suscripciones fácilmente sin complicaciones.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Sección de combos -->
        <section id="combos" class="py-5 bg-light">
            <div class="container">
                <h2 class="text-center fw-bold mb-5">Nuestros Combos</h2>
                <div class="row">
                    <div class="col-md-4">
                        <div class="combo-box text-center p-3 shadow-sm rounded">
                            <img src="{{ asset('images/cuadrado/combos/32.png') }}" alt="Combo 1" class="img-fluid rounded mb-3">
                            <h5 class="fw-bold">Combo Básico: Spotify + Disney + Prime Video</h5>
                            <p class="text-muted">Solo por $6.00/mes</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="combo-box text-center p-3 shadow-sm rounded">
                            <img src="{{ asset('images/cuadrado/combos/34.png') }}" alt="Combo 2" class="img-fluid rounded mb-3">
                            <h5 class="fw-bold">Combo Maratón: Netflix + Disney + HBO Max</h5>
                            <p class="text-muted">Solo por $7.50/mes</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="combo-box text-center p-3 shadow-sm rounded">
                            <img src="{{ asset('images/cuadrado/combos/36.png') }}" alt="Combo 3" class="img-fluid rounded mb-3">
                            <h5 class="fw-bold">Super Combo: HBO Max + Disney + Paramount + Crunchyroll</h5>
                            <p class="text-muted">Solo por $7.00/mes</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <footer id="pie" class="bg-dark text-white text-center py-4">
            <div class="container">
                <p class="mb-2">© 2024 Streamify. Todos los derechos reservados.</p>
                <p class="small">Diseñado por Pablo Jiménez</p>
                <div>
                    <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white"><i class="bi bi-linkedin"></i></a>
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
    </body>
</html>
