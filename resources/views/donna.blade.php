@extends('layouts.cliente')
@section('title', 'Donna AI — Tu Secretaria Inteligente | Streamify')
@section('styles')
    <style>
        :root {
            --donna-yellow: #E4B100;
            --donna-red: #D41216;
            --donna-blue: #274698;
            --donna-dark: #1D1D1B;
        }

        .donna-hero {
            background: linear-gradient(135deg, var(--donna-dark) 0%, #1a2d6b 100%);
            min-height: 90vh;
            display: flex;
            align-items: center;
            padding-top: 80px;
        }

        .badge-new {
            background-color: var(--donna-yellow);
            color: var(--donna-dark);
            font-size: 0.75rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .donna-card {
            border: none;
            border-radius: 1.5rem;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            overflow: hidden;
        }

        .donna-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15) !important;
        }

        .donna-card-personal {
            border-top: 5px solid var(--donna-blue);
        }

        .donna-card-business {
            border-top: 5px solid var(--donna-yellow);
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .step-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .chat-bubble-out {
            background-color: var(--donna-yellow);
            color: var(--donna-dark);
            border-radius: 18px 18px 4px 18px;
            padding: 10px 16px;
            max-width: 260px;
            font-size: 0.875rem;
            margin-left: auto;
        }

        .chat-bubble-in {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 18px 18px 18px 4px;
            padding: 10px 16px;
            max-width: 280px;
            font-size: 0.875rem;
        }

        .chat-bubble-in-light {
            background: #f0f4ff;
            color: var(--donna-dark);
            border-radius: 18px 18px 18px 4px;
            padding: 10px 16px;
            max-width: 280px;
            font-size: 0.875rem;
        }

        .pill-tag {
            background-color: rgba(39, 70, 152, 0.08);
            color: var(--donna-blue);
            border-radius: 100px;
            padding: 4px 14px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .donna-divider {
            width: 60px;
            height: 4px;
            border-radius: 4px;
        }

        .cta-section {
            background: linear-gradient(135deg, var(--donna-blue) 0%, var(--donna-dark) 100%);
        }

        .accordion-button:not(.collapsed) {
            background-color: #f0f4ff;
            color: var(--donna-blue);
            font-weight: 600;
        }

        .accordion-button:focus {
            box-shadow: 0 0 0 0.2rem rgba(39, 70, 152, 0.2);
        }

        .integration-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 12px;
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
            <li><a class="dropdown-item" href="{{ route('principal') }}#registro">Registro</a></li>
            <li><a class="dropdown-item" href="{{ route('principal') }}#features">Fortalezas</a></li>
            <li><a class="dropdown-item" href="{{ route('principal') }}#combos">Promociones</a></li>
            <li><a class="dropdown-item" href="{{ route('principal') }}#servicios">Otros Servicios</a></li>
            <li><a class="dropdown-item" href="{{ route('principal') }}#redes">Redes Sociales</a></li>
            <li><a class="dropdown-item" href="{{ route('principal') }}#faq">Preguntas Frecuentes</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item fw-bold active" href="{{ route('donna') }}" style="color: #274698;">
                <i class="bi bi-robot me-1"></i> Donna AI
            </a></li>
        </ul>
    </div>

    <!-- Menú Desplegable Catálogo -->
    <div class="dropdown me-lg-3">
        <button class="btn btn-light border rounded-pill dropdown-toggle fw-bold" type="button" id="dropdownCatalogo"
            data-bs-toggle="dropdown" aria-expanded="false">
            Catálogo
        </button>
        <ul class="dropdown-menu shadow" aria-labelledby="dropdownCatalogo">
            <li><a class="dropdown-item" href="{{ route('shop') }}#inmediata-individual">Entrega Inmediata - Individual</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#combos">Entrega Inmediata - Combos</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#pedidos">Pedidos</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#personalizadas">Personalizadas</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#completos">Cuentas completas</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#juegos">Juegos</a></li>
        </ul>
    </div>
@endsection
@section('header')
    <!-- Hero -->
    <div class="donna-hero">
        <div class="container px-5">
            <div class="row gx-5 align-items-center">
                <!-- Texto hero -->
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <span class="badge badge-new rounded-pill px-3 py-2 mb-3 d-inline-block">
                        <i class="bi bi-stars me-1"></i> Nuevo Servicio SaaS
                    </span>
                    <h1 class="display-4 fw-bold text-white lh-1 mb-4">
                        Conoce a <span style="color: var(--donna-yellow);">Donna</span><br>
                        Tu secretaria<br>con inteligencia artificial
                    </h1>
                    <p class="lead text-white-50 mb-5">
                        Donna gestiona tu agenda, atiende a tus clientes por WhatsApp y automatiza las tareas
                        repetitivas de tu negocio — sin que tengas que estar presente.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#planes" class="btn btn-lg fw-bold px-4 rounded-pill"
                            style="background-color: var(--donna-yellow); color: var(--donna-dark);">
                            <i class="bi bi-rocket-takeoff me-2"></i>Ver planes
                        </a>
                        <a href="https://wa.me/593961412826?text=Hola%2C%20quiero%20información%20sobre%20Donna%20AI"
                            target="_blank" class="btn btn-outline-light btn-lg fw-bold px-4 rounded-pill">
                            <i class="bi bi-whatsapp me-2"></i>Consultar
                        </a>
                    </div>
                </div>

                <!-- Chat demo dark -->
                <div class="col-lg-6 d-flex justify-content-center">
                    <div class="rounded-4 shadow-lg p-4 w-100" style="background:rgba(255,255,255,0.06);border:1px solid rgba(228,177,0,0.25);max-width:420px;">
                        <!-- Header chat -->
                        <div class="d-flex align-items-center gap-3 mb-4 pb-3" style="border-bottom:1px solid rgba(255,255,255,0.08);">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                style="width:46px;height:46px;background-color:var(--donna-yellow);flex-shrink:0;">
                                <i class="bi bi-robot text-dark fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-white">Donna — Clínica Dental</div>
                                <div class="small" style="color:var(--donna-yellow);">
                                    <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>En línea
                                </div>
                            </div>
                        </div>
                        <!-- Mensajes demo -->
                        <div class="d-flex flex-column gap-3">
                            <div class="chat-bubble-out">Hola, quiero una cita para mañana</div>
                            <div class="chat-bubble-in">¡Hola! Con gusto. Tengo disponible a las 10:00 o a las 15:30. ¿Cuál te viene mejor?</div>
                            <div class="chat-bubble-out">Las 10:00 perfecto</div>
                            <div class="chat-bubble-in">Listo, tu cita está agendada para mañana a las 10:00 con el Dr. Fernández. Te recuerdo una hora antes.</div>
                            <div class="chat-bubble-out">¿Cuánto cuesta la consulta?</div>
                            <div class="chat-bubble-in">La consulta inicial es $25. Si requiere tratamiento, el Dr. te indicará el plan y costos en la cita.</div>
                        </div>
                        <!-- Typing indicator -->
                        <div class="d-flex align-items-center gap-2 mt-3 pt-3" style="border-top:1px solid rgba(255,255,255,0.08);">
                            <div class="rounded-circle" style="width:28px;height:28px;background:rgba(228,177,0,0.2);display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-robot" style="color:var(--donna-yellow);font-size:0.75rem;"></i>
                            </div>
                            <span class="text-white-50 small fst-italic">Donna está respondiendo...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('sections')

    <!-- Estadísticas rápidas -->
    <section class="py-4 bg-white border-bottom">
        <div class="container px-5">
            <div class="row text-center g-4">
                <div class="col-6 col-md-3">
                    <div class="fw-bold display-6" style="color:var(--donna-blue);">24/7</div>
                    <div class="text-muted small">Disponibilidad total</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="fw-bold display-6" style="color:var(--donna-blue);">< 2s</div>
                    <div class="text-muted small">Tiempo de respuesta</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="fw-bold display-6" style="color:var(--donna-blue);">0</div>
                    <div class="text-muted small">Configuraciones técnicas para el cliente</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="fw-bold display-6" style="color:var(--donna-blue);">∞</div>
                    <div class="text-muted small">Conversaciones simultáneas</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Planes: Personal vs Business -->
    <section id="planes" class="py-5 bg-light">
        <div class="container px-5">
            <div class="text-center mb-5">
                <span class="pill-tag mb-3 d-inline-block">Elige tu plan</span>
                <h2 class="fw-bold display-6 mb-3">Donna se adapta a tu necesidad</h2>
                <p class="text-muted lead">Elige el servicio que mejor se ajuste a ti o a tu negocio. Puedes contratar
                    ambos.</p>
                <div class="donna-divider bg-primary mx-auto mt-3"></div>
            </div>

            <div class="row g-4 justify-content-center">

                <!-- Donna Personal -->
                <div class="col-md-6 col-lg-5">
                    <div class="donna-card donna-card-personal card shadow h-100 p-4">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="feature-icon" style="background:#eef2ff;">
                                <i class="bi bi-person-circle" style="color:var(--donna-blue);"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0">Donna Personal</h3>
                                <span class="small text-muted">Tu secretaria privada</span>
                            </div>
                        </div>
                        <p class="text-muted mb-4">Donna es tu asistente personal. Hablas directamente con ella por
                            WhatsApp o Telegram y ella gestiona tu agenda, recordatorios y notas.</p>

                        <ul class="list-unstyled d-flex flex-column gap-3 mb-4">
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill mt-1 flex-shrink-0" style="color:var(--donna-blue);"></i>
                                <span>Agenda reuniones y citas en Google Calendar</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill mt-1 flex-shrink-0" style="color:var(--donna-blue);"></i>
                                <span>Registra notas y seguimientos en Google Sheets</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill mt-1 flex-shrink-0" style="color:var(--donna-blue);"></i>
                                <span>Envía recordatorios automáticos</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill mt-1 flex-shrink-0" style="color:var(--donna-blue);"></i>
                                <span>Solo responde al dueño o contactos autorizados</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill mt-1 flex-shrink-0" style="color:var(--donna-blue);"></i>
                                <span>Disponible por WhatsApp o Telegram</span>
                            </li>
                        </ul>

                        <div class="alert border-0 rounded-3 mb-4" style="background:#eef2ff;">
                            <p class="small mb-1 fst-italic text-muted">"Donna, agenda una reunión con Fernando mañana a
                                las 10."</p>
                            <p class="small mb-0 fw-semibold" style="color:var(--donna-blue);">→ Donna crea el evento
                                en tu Google Calendar al instante.</p>
                        </div>

                        <a href="https://wa.me/593961412826?text=Quiero%20información%20sobre%20Donna%20Personal"
                            target="_blank" class="btn fw-bold w-100 rounded-pill py-2"
                            style="background-color:var(--donna-blue);color:#fff;">
                            <i class="bi bi-whatsapp me-2"></i>Consultar Donna Personal
                        </a>
                    </div>
                </div>

                <!-- Donna Business -->
                <div class="col-md-6 col-lg-5">
                    <div class="donna-card donna-card-business card shadow h-100 p-4">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="feature-icon" style="background:#fffbea;">
                                <i class="bi bi-building" style="color:var(--donna-yellow);"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0">Donna Business</h3>
                                <span class="small text-muted">La voz de tu negocio</span>
                            </div>
                        </div>
                        <p class="text-muted mb-4">Donna atiende a los clientes de tu negocio directamente desde tu
                            número de WhatsApp. Responde, agenda y escala — sin que tengas que estar.</p>

                        <ul class="list-unstyled d-flex flex-column gap-3 mb-4">
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill mt-1 flex-shrink-0" style="color:#c9890a;"></i>
                                <span>Atiende a todos tus clientes simultáneamente</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill mt-1 flex-shrink-0" style="color:#c9890a;"></i>
                                <span>Usa tu base de conocimiento: precios, servicios, políticas</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill mt-1 flex-shrink-0" style="color:#c9890a;"></i>
                                <span>Agenda citas en Google Calendar del negocio</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill mt-1 flex-shrink-0" style="color:#c9890a;"></i>
                                <span>Registra leads y datos en Google Sheets</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill mt-1 flex-shrink-0" style="color:#c9890a;"></i>
                                <span>Escala a humano cuando no puede resolver</span>
                            </li>
                        </ul>

                        <div class="alert border-0 rounded-3 mb-4" style="background:#fffbea;">
                            <p class="small mb-1 fst-italic text-muted">"¿Cuánto cuesta el servicio de limpieza dental?"
                            </p>
                            <p class="small mb-0 fw-semibold" style="color:#c9890a;">→ Donna responde con los precios de tu negocio,
                                24/7.</p>
                        </div>

                        <a href="https://wa.me/593961412826?text=Quiero%20información%20sobre%20Donna%20Business"
                            target="_blank" class="btn fw-bold w-100 rounded-pill py-2"
                            style="background-color:var(--donna-yellow);color:var(--donna-dark);">
                            <i class="bi bi-whatsapp me-2"></i>Consultar Donna Business
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Cómo funciona -->
    <section id="como-funciona" class="py-5 bg-white">
        <div class="container px-5">
            <div class="text-center mb-5">
                <span class="pill-tag mb-3 d-inline-block">Simple y rápido</span>
                <h2 class="fw-bold display-6 mb-3">¿Cómo funciona?</h2>
                <p class="text-muted">Configuras una vez. Donna trabaja siempre.</p>
                <div class="donna-divider" style="background-color:var(--donna-yellow);margin:12px auto 0;"></div>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="text-center">
                        <div class="step-circle mx-auto mb-3" style="background:var(--donna-blue);color:#fff;">1</div>
                        <h6 class="fw-bold mb-2">Contratas Donna</h6>
                        <p class="text-muted small">Eliges si quieres Donna Personal, Business o ambas. Sin instalaciones
                            técnicas.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="text-center">
                        <div class="step-circle mx-auto mb-3" style="background:var(--donna-blue);color:#fff;">2</div>
                        <h6 class="fw-bold mb-2">Conectas Google y WhatsApp</h6>
                        <p class="text-muted small">Solo presionas "Conectar Google" y enlazas tu WhatsApp desde el panel.
                            Sin tecnicismos.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="text-center">
                        <div class="step-circle mx-auto mb-3" style="background:var(--donna-blue);color:#fff;">3</div>
                        <h6 class="fw-bold mb-2">Configuras a Donna</h6>
                        <p class="text-muted small">Le dices cómo se llama, qué hace tu negocio, cómo debe responder y cuál
                            es tu horario.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="text-center">
                        <div class="step-circle mx-auto mb-3" style="background:var(--donna-yellow);color:var(--donna-dark);">4</div>
                        <h6 class="fw-bold mb-2">Donna trabaja por ti</h6>
                        <p class="text-muted small">Desde ese momento, Donna atiende, agenda y responde de forma autónoma
                            por WhatsApp.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Integraciones -->
    <section class="py-5 bg-light">
        <div class="container px-5">
            <div class="text-center mb-5">
                <span class="pill-tag mb-3 d-inline-block">Integraciones incluidas</span>
                <h2 class="fw-bold mb-2">Se conecta con tus herramientas</h2>
                <p class="text-muted">Donna se integra nativamente con las plataformas que ya usas.</p>
            </div>
            <div class="row g-4 text-center">
                <div class="col-6 col-md-3">
                    <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                        <div class="integration-icon" style="background:#e8f4ea;">
                            <i class="bi bi-whatsapp" style="color:#25D366;"></i>
                        </div>
                        <h6 class="fw-bold mb-1">WhatsApp</h6>
                        <p class="text-muted small mb-0">Conversaciones con clientes desde tu número de negocio</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                        <div class="integration-icon" style="background:#e8eefa;">
                            <i class="bi bi-telegram" style="color:#229ED9;"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Telegram</h6>
                        <p class="text-muted small mb-0">Canal personal alternativo para Donna Personal</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                        <div class="integration-icon" style="background:#fce8e8;">
                            <i class="bi bi-calendar-check" style="color:#EA4335;"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Google Calendar</h6>
                        <p class="text-muted small mb-0">Agenda citas y reuniones automáticamente</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                        <div class="integration-icon" style="background:#e8faee;">
                            <i class="bi bi-table" style="color:#0F9D58;"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Google Sheets</h6>
                        <p class="text-muted small mb-0">Registra clientes, leads y datos en tu hoja de cálculo</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Casos de uso reales -->
    <section class="py-5 bg-white">
        <div class="container px-5">
            <div class="text-center mb-5">
                <span class="pill-tag mb-3 d-inline-block">Casos de uso</span>
                <h2 class="fw-bold display-6 mb-2">Donna funciona en cualquier negocio</h2>
                <p class="text-muted">Estas son conversaciones reales que Donna puede manejar.</p>
            </div>

            <div class="row g-4">
                <!-- Demo Business -->
                <div class="col-md-6 col-lg-4">
                    <div class="bg-light rounded-4 p-4 h-100">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <div class="feature-icon" style="background:var(--donna-blue);width:36px;height:36px;border-radius:10px;font-size:1rem;">
                                <i class="bi bi-heart-pulse text-white"></i>
                            </div>
                            <span class="fw-bold small">Clínica Médica</span>
                            <span class="badge rounded-pill ms-auto" style="background:#eef2ff;color:var(--donna-blue);">Business</span>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <div class="chat-bubble-out small">Hola, necesito una cita urgente</div>
                            <div class="chat-bubble-in-light small">Hola, claro que sí. El Dr. Torres tiene disponible hoy a las 16:30 o mañana a las 09:00. ¿Cuál prefieres?</div>
                            <div class="chat-bubble-out small">Hoy a las 16:30</div>
                            <div class="chat-bubble-in-light small">Listo, tu cita está confirmada. Te la agrego al calendar. ¿Tienes algún seguro médico?</div>
                        </div>
                    </div>
                </div>

                <!-- Demo Restaurante -->
                <div class="col-md-6 col-lg-4">
                    <div class="bg-light rounded-4 p-4 h-100">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <div class="feature-icon" style="background:var(--donna-red);width:36px;height:36px;border-radius:10px;font-size:1rem;">
                                <i class="bi bi-shop text-white"></i>
                            </div>
                            <span class="fw-bold small">Restaurante</span>
                            <span class="badge rounded-pill ms-auto" style="background:#eef2ff;color:var(--donna-blue);">Business</span>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <div class="chat-bubble-out small">¿Tienen mesa disponible el sábado para 4 personas?</div>
                            <div class="chat-bubble-in-light small">¡Hola! Claro. El sábado tenemos mesas disponibles a las 13:00 o 19:30. ¿Cuál prefieres?</div>
                            <div class="chat-bubble-out small">A las 19:30 perfecto</div>
                            <div class="chat-bubble-in-light small">Reserva confirmada para 4 personas el sábado a las 19:30. ¿A nombre de quién hacemos la reserva?</div>
                        </div>
                    </div>
                </div>

                <!-- Demo Personal -->
                <div class="col-md-6 col-lg-4">
                    <div class="bg-light rounded-4 p-4 h-100">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <div class="feature-icon" style="background:var(--donna-yellow);width:36px;height:36px;border-radius:10px;font-size:1rem;">
                                <i class="bi bi-person-check" style="color:var(--donna-dark);"></i>
                            </div>
                            <span class="fw-bold small">Emprendedor</span>
                            <span class="badge rounded-pill ms-auto" style="background:#fffbea;color:#c9890a;">Personal</span>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <div class="chat-bubble-out small">Donna, recuérdame llamar a María el viernes a las 10</div>
                            <div class="chat-bubble-in-light small">Recordatorio creado. Te avisaré el viernes a las 10:00 para llamar a María. ¿Agrego algo en tu calendario?</div>
                            <div class="chat-bubble-out small">Sí, anota también en mi hoja de clientes</div>
                            <div class="chat-bubble-in-light small">Listo, lo registré en tu Google Sheets en la hoja "Clientes". ¿Algo más?</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Donna -->
    <section id="faq-donna" class="py-5 bg-light">
        <div class="container px-5">
            <div class="row gx-5 align-items-start">
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <span class="pill-tag mb-3 d-inline-block">Preguntas frecuentes</span>
                    <h2 class="fw-bold display-6 mb-3">¿Tienes dudas sobre Donna?</h2>
                    <p class="text-muted">Si no encuentras tu respuesta aquí, escríbenos por WhatsApp.</p>
                    <a href="https://wa.me/593961412826?text=Tengo%20una%20pregunta%20sobre%20Donna%20AI"
                        target="_blank" class="btn fw-bold rounded-pill px-4"
                        style="background-color:var(--donna-blue);color:#fff;">
                        <i class="bi bi-whatsapp me-2"></i>Preguntar ahora
                    </a>
                </div>
                <div class="col-lg-8">
                    <div class="accordion" id="faqDonna">
                        <div class="accordion-item border-0 rounded-3 mb-3 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button rounded-3 fw-semibold" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#faqD1">
                                    ¿Necesito crear una cuenta de Google Cloud o configurar APIs?
                                </button>
                            </h2>
                            <div id="faqD1" class="accordion-collapse collapse show" data-bs-parent="#faqDonna">
                                <div class="accordion-body text-muted">
                                    No. Streamify/Aaronsoft se encarga de toda la infraestructura técnica. Tú solo presionas
                                    "Conectar Google" y autorizas los permisos de Calendar y Sheets. Nada más.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 rounded-3 mb-3 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed rounded-3 fw-semibold" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#faqD2">
                                    ¿Donna puede atender a varios clientes al mismo tiempo?
                                </button>
                            </h2>
                            <div id="faqD2" class="accordion-collapse collapse" data-bs-parent="#faqDonna">
                                <div class="accordion-body text-muted">
                                    Sí. Donna Business está diseñada para atender múltiples conversaciones simultáneas sin
                                    demoras. No importa cuántos clientes escriban a la vez.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 rounded-3 mb-3 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed rounded-3 fw-semibold" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#faqD3">
                                    ¿Qué pasa si Donna no sabe cómo responder?
                                </button>
                            </h2>
                            <div id="faqD3" class="accordion-collapse collapse" data-bs-parent="#faqDonna">
                                <div class="accordion-body text-muted">
                                    Donna puede escalar la conversación a un humano cuando no tiene suficiente información
                                    para responder correctamente. Configuras el mensaje de escalado y a quién notificar.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 rounded-3 mb-3 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed rounded-3 fw-semibold" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#faqD4">
                                    ¿Mis datos y los de mis clientes están seguros?
                                </button>
                            </h2>
                            <div id="faqD4" class="accordion-collapse collapse" data-bs-parent="#faqDonna">
                                <div class="accordion-body text-muted">
                                    Sí. Todas las credenciales (tokens de Google, API keys) se almacenan cifradas. Los datos
                                    de cada cliente están completamente aislados. Nunca compartimos información entre negocios.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 rounded-3 mb-3 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed rounded-3 fw-semibold" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#faqD5">
                                    ¿Puedo contratar Donna Personal y Business al mismo tiempo?
                                </button>
                            </h2>
                            <div id="faqD5" class="accordion-collapse collapse" data-bs-parent="#faqDonna">
                                <div class="accordion-body text-muted">
                                    Sí. Puedes tener los dos servicios activos simultáneamente. Cada uno tiene su propio
                                    canal, configuración, historial e integraciones. No se mezclan.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-5 cta-section">
        <div class="container px-5 text-center">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <i class="bi bi-robot display-4 mb-4 d-block" style="color:var(--donna-yellow);"></i>
                    <h2 class="fw-bold display-6 text-white mb-3">
                        ¿Listo para que Donna trabaje por ti?
                    </h2>
                    <p class="lead text-white-50 mb-5">
                        Automatiza la atención, agenda citas y libera tu tiempo. Escríbenos hoy y activamos Donna para
                        tu negocio.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="https://wa.me/593961412826?text=Quiero%20contratar%20Donna%20AI%20para%20mi%20negocio"
                            target="_blank" class="btn btn-lg fw-bold rounded-pill px-5"
                            style="background-color:var(--donna-yellow);color:var(--donna-dark);">
                            <i class="bi bi-whatsapp me-2"></i>Contratar Donna
                        </a>
                        <a href="{{ route('principal') }}" class="btn btn-outline-light btn-lg fw-bold rounded-pill px-5">
                            <i class="bi bi-house me-2"></i>Volver al inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
