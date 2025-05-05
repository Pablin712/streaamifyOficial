@extends('layouts.cliente')
@section('title', 'Streamify HQ')
@section('styles')
    <style>
        /* Opción 1: Amarillo (Principal) */
        .bg-streamify-yellow {
            background-color: #E4B100;
            color: #1D1D1B;
            /* Texto oscuro para contraste */
        }

        /* Opción 2: Rojo (Llamativo) */
        .bg-streamify-red {
            background-color: #D41216;
            color: #FFFFFF;
            /* Texto blanco para buen contraste */
        }

        /* Opción 3: Azul Corporativo */
        .bg-streamify-blue {
            background-color: #274698;
            color: #FFFFFF;
            /* Texto blanco para contraste */
        }

        /* Opción 4: Negro Elegante */
        .bg-streamify-black {
            background-color: #1D1D1B;
            color: #FFFFFF;
            /* Texto blanco para legibilidad */
        }

        /* Opción 5: Gris Sofisticado */
        .bg-streamify-gray {
            background-color: #575756;
            color: #FFFFFF;
            /* Texto blanco para contraste */
        }
    </style>
@endsection
@section('menu')
    <!-- Menú Desplegable Acerca de -->
    <div class="dropdown me-lg-3">
        <button class="btn btn-light border rounded-pill dropdown-toggle fw-bold" type="button" id="dropdownAcerca"
            data-bs-toggle="dropdown" aria-expanded="false">
            Acerca de
        </button>
        <ul class="dropdown-menu shadow" aria-labelledby="dropdownAcerca">
            <li><a class="dropdown-item" href="#registro">Registro</a></li>
            <li><a class="dropdown-item" href="#features">Fortalezas</a></li>
            <li><a class="dropdown-item" href="#combos">Promociones</a></li>
            <li><a class="dropdown-item" href="#servicios">Otros Servicios</a></li>
            <li><a class="dropdown-item" href="#redes">Redes Sociales</a></li>
            <li><a class="dropdown-item" href="#faq">Preguntas Frecuentes</a></li>
        </ul>
    </div>

    <!-- Menú Desplegable Catálogo -->
    <div class="dropdown me-lg-3">
        <button class="btn btn-light border rounded-pill dropdown-toggle fw-bold" type="button" id="dropdownCatalogo"
            data-bs-toggle="dropdown" aria-expanded="false">
            Catálogo
        </button>
        <ul class="dropdown-menu shadow" aria-labelledby="dropdownCatalogo">
            <li><a class="dropdown-item" href="{{ route('shop') }}#inmediata-individual">Entrega Inmediata - Individual</a>
            </li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#combos">Entrega Inmediata - Combos</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#pedidos">Pedidos</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#personalizadas">Personalizadas</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#completos">Cuentas completas</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#juegos">Juegos</a></li>
        </ul>
    </div>
@endsection
@section('header')
    <div class="container px-5">
        <div class="row gx-5 align-items-center">
            <div class="col-lg-6">
                <!-- Masthead text and app badges-->
                <div class="mb-5 mb-lg-0 text-center text-lg-start">
                    <h1 class="display-1 lh-1 mb-3">Más que ver, vivir el Streaming Premium</h1>
                    <p class="lead fw-normal text-muted mb-5">Únete a nuestras suscripciones premium y disfruta de acceso
                        ilimitado a las mejores plataformas de streaming.
                        Experimenta contenido exclusivo, calidad superior y la comodidad de ver lo que amas, cuando
                        quieras.</p>
                    <!-- Botón WhatsApp -->
                    <div class="mt-4">
                        <a href="https://wa.me/593961412826?text=Hola%20quiero%20más%20información%20sobre%20el%20servicio%20de"
                            target="_blank" class="btn btn-whatsapp rounded-pill px-3 mb-2 mb-lg-0">
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
                        <img src="{{ asset('images/cuadrado/combos/33.png') }}" alt="Promoción de Streaming"
                            class="rounded shadow-lg"
                            style="width: 80%; max-width: 600px; height: auto; object-fit: cover;" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('sections')
    {{-- destrezas fortalezas --}}
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
                <!-- Página Web -->
                <div class="col-md-3 mb-4">
                    <div class="feature-box shadow-sm p-4 rounded">
                        <i class="bi bi-globe text-success display-4 mb-3"></i>
                        <h5 class="fw-bold">Plataforma Online</h5>
                        <p class="text-muted">Con esta página web moderna podrás gestionar tus compras rápidamente.
                        </p>
                    </div>
                </div>
                <!-- Clientes Satisfechos -->
                <div class="col-md-3 mb-4">
                    <div class="feature-box shadow-sm p-4 rounded">
                        <i class="bi bi-people-fill text-success display-4 mb-3"></i>
                        <h5 class="fw-bold">+200 clientes activos</h5>
                        <p class="text-muted">Cientos de clientes confían en nuestros servicios y recomiendan Streamify.</p>
                    </div>
                </div>
                <!-- Ventas Automatizadas -->
                <div class="col-md-3 mb-4">
                    <div class="feature-box shadow-sm p-4 rounded">
                        <i class="bi bi-cart-check-fill text-success display-4 mb-3"></i>
                        <h5 class="fw-bold">Ventas Automatizadas</h5>
                        <p class="text-muted">Nuestro sistema 🤖 de eCommerce procesa compras rápido y
                            eficiente.
                        </p>
                    </div>
                </div>
                <!-- Servicios Adicionales -->
                <div class="col-md-3 mb-4">
                    <div class="feature-box shadow-sm p-4 rounded">
                        <i class="bi bi-gift-fill text-success display-4 mb-3"></i>
                        <h5 class="fw-bold">Servicios Adicionales</h5>
                        <p class="text-muted">Otros servicios como pago de tarjetas,
                            instituciones y recargas móviles.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- 📝 Únete a Streamify -->
    <section id="registro" class="py-5 bg-streamify-blue">
        <div class="container px-5">
            <div class="row gx-5 align-items-center">
                <div class="col-lg-6">
                    <h2 class="fw-bold">¡Forma parte de Streamify!</h2>
                    <p class="lead">Regístrate gratis y accede a suscripciones premium, recargas y más.</p>
                    <a href="{{ route('cliente.register') }}" class="btn btn-light btn-lg">Registrarse Ahora</a>
                </div>
                <div class="col-lg-6 d-flex justify-content-center">
                    <img src="{{ asset('images/shopweb.png') }}" class="rounded shadow-lg img-fluid"
                        alt="Regístrate en Streamify">
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
                        <img src="{{ asset('images/cuadrado/combos/32.png') }}" alt="Combo 1"
                            class="img-fluid rounded mb-3">
                        <h5 class="fw-bold">Combo Básico: Spotify + Disney + Prime Video</h5>
                        <p class="text-muted">Solo por $6.00/mes</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="combo-box text-center p-3 shadow-sm rounded">
                        <img src="{{ asset('images/cuadrado/combos/34.png') }}" alt="Combo 2"
                            class="img-fluid rounded mb-3">
                        <h5 class="fw-bold">Combo Maratón: Netflix + Disney + HBO Max</h5>
                        <p class="text-muted">Solo por $7.50/mes</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="combo-box text-center p-3 shadow-sm rounded">
                        <img src="{{ asset('images/cuadrado/combos/36.png') }}" alt="Combo 3"
                            class="img-fluid rounded mb-3">
                        <h5 class="fw-bold">Super Combo: HBO Max + Disney + Paramount + Crunchyroll</h5>
                        <p class="text-muted">Solo por $7.00/mes</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 💳 Servicios Adicionales -->
    <section id="servicios" class="py-5 bg-light">
        <div class="container px-5">
            <div class="row gx-5 align-items-center">
                <div class="col-lg-6 d-flex justify-content-center">
                    <img src="{{ asset('images/tuenti.jpg') }}" class="rounded shadow-lg img-fluid"
                        alt="Servicios Streamify">
                </div>
                <div class="col-lg-6">
                    <h2 class="fw-bold">Más que Streaming: Servicios Adicionales</h2>
                    <p class="text-muted">Ofrecemos recargas de saldo y pagos de servicios básicos como luz, agua e
                        internet.</p>
                    <ul>
                        <li><i class="bi bi-cash-stack text-success"></i> Recargas de Saldo</li>
                        <li><i class="bi bi-lightbulb-fill text-success"></i> Pago de Luz y Agua</li>
                        <li><i class="bi bi-wifi text-success"></i> Pago de Internet</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- 📱 Redes Sociales -->
    <section id="redes" class="py-5">
        <div class="container px-5">
            <div class="row gx-5 align-items-center">
                <div class="col-lg-6">
                    <h2 class="fw-bold">Síguenos en Redes Sociales</h2>
                    <p class="text-muted">Mantente informado sobre promociones y novedades.</p>
                    <div class="d-flex flex-column gap-3">
                        <a href="https://www.facebook.com/share/1Cco5izY9Y/?mibextid=wwXIfr" class="btn btn-primary"><i
                                class="bi bi-facebook"></i>
                            Facebook</a>
                        <a href="https://www.instagram.com/stribarra" class="btn btn-danger"><i
                                class="bi bi-instagram"></i>
                            Instagram</a>
                        <a href="https://www.tiktok.com/@lv_pablin" class="btn btn-dark"><i class="bi bi-tiktok"></i>
                            TikTok</a>
                        <a href="https://t.me/Streamifyhq" class="btn btn-info"><i class="bi bi-telegram"></i>
                            Telegram</a>
                    </div>
                </div>
                <div class="col-lg-6 d-flex justify-content-center">
                    <img src="{{ asset('images/redes.png') }}" class="rounded shadow-lg img-fluid"
                        alt="Síguenos en redes">
                </div>
            </div>
        </div>
    </section>

    <!-- ⭐ Testimonios de Clientes -->
    {{-- <section id="testimonios" class="py-5 bg-light">
        <div class="container px-5">
            <h2 class="text-center fw-bold">Lo que dicen nuestros clientes</h2>
            <div class="row gx-5 align-items-center">
                <div class="col-lg-6 d-flex justify-content-center">
                    <img src="{{ asset('images/banner/testimonios.png') }}" class="rounded shadow-lg img-fluid"
                        alt="Clientes Felices">
                </div>
                <div class="col-lg-6">
                    <div class="testimonial-box shadow-sm p-4 rounded mb-3">
                        <i class="bi bi-person-circle display-4 mb-3"></i>
                        <p class="fst-italic">"Streamify me ha facilitado el acceso a mis plataformas favoritas. ¡100%
                            recomendado!"</p>
                        <h5 class="fw-bold">Carlos M.</h5>
                    </div>
                    <div class="testimonial-box shadow-sm p-4 rounded">
                        <i class="bi bi-person-circle display-4 mb-3"></i>
                        <p class="fst-italic">"Puedo pagar mi internet sin salir de casa. ¡Muy conveniente!"</p>
                        <h5 class="fw-bold">Luis G.</h5>
                    </div>
                </div>
            </div>
        </div>
    </section> --}}

    <!-- ❓ Preguntas Frecuentes -->
    <section id="faq" class="py-5">
        <div class="container px-5">
            <div class="row gx-5 align-items-center">
                <div class="col-lg-6">
                    <h2 class="fw-bold">Preguntas Frecuentes</h2>
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqContent1">
                                    ¿Cómo funciona Streamify?
                                </button>
                            </h2>
                            <div id="faqContent1" class="accordion-collapse collapse show">
                                <div class="accordion-body">
                                    Streamify te ofrece acceso a suscripciones premium a precios accesibles. Solo recarga
                                    saldo, elige tu suscripción y disfruta.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqContent2">
                                    ¿Cómo puedo pagar?
                                </button>
                            </h2>
                            <div id="faqContent2" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    Puedes pagar mediante transferencias bancarias o saldo digital recargado en tu cuenta de
                                    Streamify.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqContent3">
                                    ¿Es seguro recargar saldo en esta app web?
                                </button>
                            </h2>
                            <div id="faqContent3" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    Totalmente, esta aplicación web está diseñado con estudio de ingeniería de software,
                                    siguiendo protocolos seguros y probados.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqContent4">
                                    Si una suscripción me falla, ¿tendré garantía?
                                </button>
                            </h2>
                            <div id="faqContent4" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    Por supuesto, se te recompensa por los días perdidos, se te asiste a cualquier política
                                    de cada plataforma streaming, solo sigue las reglas que son sencillas, y cálmate, la
                                    amabilidad será tu mayor amigo.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-flex justify-content-center">
                    <img src="{{ asset('images/fac.png') }}" class="rounded shadow-lg img-fluid"
                        alt="Preguntas Frecuentes">
                </div>
            </div>
        </div>
    </section>

@endsection
