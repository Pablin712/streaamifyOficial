@extends('layouts.cliente')
@section('title', 'Streamify HQ — Streaming premium, entrega inmediata')

@section('styles')
    {{-- Estilos propios SOLO de la landing.
         Todo lo demás (tipografía, colores, tarjetas, botones) viene de
         public/css/streamify-ui.css — no redefinir colores aquí a mano. --}}
    <style>
        /* El layout envuelve @yield('header') en <header class="masthead">.
           Se neutraliza el masthead del tema New Age y se reconstruye el hero. */
        .masthead {
            background: var(--sf-surface-page);
            padding: 0;
            min-height: 0;
        }

        .hero {
            position: relative;
            overflow: hidden;
            padding-block: calc(var(--sf-space-8) + 40px) var(--sf-space-8);
            background:
                radial-gradient(60rem 32rem at 82% -10%, var(--sf-brand-soft), transparent 70%),
                radial-gradient(44rem 26rem at 6% 108%, var(--sf-gold-soft), transparent 70%),
                var(--sf-surface-page);
            border-bottom: 1px solid var(--sf-border);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: var(--sf-space-7);
            align-items: center;
        }

        .hero-title {
            font-family: var(--sf-font-display);
            font-size: clamp(2.4rem, 5.2vw, 4rem);
            font-weight: 600;
            line-height: 1.04;
            letter-spacing: -0.03em;
            margin: 0;
        }

        .hero-title em {
            font-style: italic;
            font-weight: 400;
            color: var(--sf-brand);
            /* La cursiva de Fraunces se inclina sobre la palabra siguiente. */
            padding-right: 0.1em;
        }

        .hero-lead {
            font-size: var(--sf-text-lg);
            color: var(--sf-ink-secondary);
            max-width: 34rem;
            margin-top: var(--sf-space-4);
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: var(--sf-space-3);
            margin-top: var(--sf-space-6);
        }

        /* Tira de confianza: cifras del negocio en mono tabular */
        .hero-proof {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: var(--sf-space-5);
            margin-top: var(--sf-space-7);
            padding-top: var(--sf-space-5);
            border-top: 1px solid var(--sf-border);
        }

        .hero-proof-value {
            font-family: var(--sf-font-mono);
            font-variant-numeric: tabular-nums;
            font-size: var(--sf-text-xl);
            font-weight: 600;
            letter-spacing: -0.02em;
            display: block;
        }

        .hero-proof-label {
            font-size: var(--sf-text-xs);
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--sf-ink-muted);
            font-weight: 600;
        }

        /* Marco de la imagen promocional: mismo lenguaje que los paneles */
        .hero-figure {
            border: 1px solid var(--sf-border);
            border-radius: var(--sf-radius-lg);
            background: var(--sf-surface-card);
            padding: var(--sf-space-3);
            box-shadow: var(--sf-shadow-lg);
        }

        .hero-figure img {
            display: block;
            width: 100%;
            height: auto;
            border-radius: var(--sf-radius);
        }

        /* Tira de plataformas */
        .platform-strip {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: var(--sf-space-2) var(--sf-space-3);
        }

        .platform-chip {
            font-size: var(--sf-text-sm);
            font-weight: 600;
            color: var(--sf-ink-secondary);
            background: var(--sf-surface-card);
            border: 1px solid var(--sf-border);
            border-radius: var(--sf-radius-pill);
            padding: 6px 14px;
            white-space: nowrap;
        }

        /* Combos: la tarjeta es el producto, el precio manda */
        .combo-card {
            display: flex;
            flex-direction: column;
            background: var(--sf-surface-card);
            border: 1px solid var(--sf-border);
            border-radius: var(--sf-radius);
            overflow: hidden;
            height: 100%;
            transition: border-color var(--sf-transition), transform var(--sf-transition), box-shadow var(--sf-transition);
        }

        .combo-card:hover {
            border-color: var(--sf-brand);
            transform: translateY(-3px);
            box-shadow: var(--sf-shadow);
        }

        .combo-card img {
            width: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            display: block;
        }

        .combo-body {
            padding: var(--sf-space-4) var(--sf-space-5) var(--sf-space-5);
            display: flex;
            flex-direction: column;
            gap: var(--sf-space-2);
            flex: 1;
        }

        .combo-name {
            font-family: var(--sf-font-sans);
            font-weight: 600;
            font-size: var(--sf-text-base);
            margin: 0;
            flex: 1;
        }

        .combo-price {
            font-family: var(--sf-font-mono);
            font-variant-numeric: tabular-nums;
            font-size: var(--sf-text-xl);
            font-weight: 600;
            letter-spacing: -0.02em;
            color: var(--sf-ink);
        }

        .combo-price span {
            font-family: var(--sf-font-sans);
            font-size: var(--sf-text-sm);
            font-weight: 500;
            color: var(--sf-ink-muted);
        }

        /* Donna: maqueta de conversación */
        .donna-chat {
            border: 1px solid rgba(228, 177, 0, 0.28);
            border-radius: var(--sf-radius-lg);
            background: rgba(255, 255, 255, 0.05);
            padding: var(--sf-space-5);
            max-width: 380px;
            margin-inline: auto;
        }

        .donna-chat-head {
            display: flex;
            align-items: center;
            gap: var(--sf-space-3);
            padding-bottom: var(--sf-space-4);
            margin-bottom: var(--sf-space-4);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .donna-avatar {
            width: 44px;
            height: 44px;
            flex: none;
            border-radius: 50%;
            background: var(--sf-gold);
            color: var(--sf-gold-contrast);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .donna-thread {
            display: flex;
            flex-direction: column;
            gap: var(--sf-space-3);
        }

        .donna-msg {
            font-size: var(--sf-text-sm);
            line-height: 1.45;
            padding: 9px 13px;
            border-radius: var(--sf-radius);
            max-width: 82%;
        }

        .donna-msg--in {
            align-self: flex-end;
            background: var(--sf-gold);
            color: var(--sf-gold-contrast);
            border-bottom-right-radius: 4px;
        }

        .donna-msg--out {
            align-self: flex-start;
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            border-bottom-left-radius: 4px;
        }

        /* Redes: enlaces como filas, no como botones de colores */
        .social-link {
            display: flex;
            align-items: center;
            gap: var(--sf-space-3);
            padding: var(--sf-space-3) var(--sf-space-4);
            border: 1px solid var(--sf-border);
            border-radius: var(--sf-radius);
            background: var(--sf-surface-card);
            color: var(--sf-ink);
            font-weight: 600;
            font-size: var(--sf-text-sm);
            transition: border-color var(--sf-transition), background-color var(--sf-transition);
        }

        .social-link:hover {
            border-color: var(--sf-brand);
            background: var(--sf-brand-soft);
            color: var(--sf-ink);
        }

        .social-link i {
            font-size: 1.15rem;
            color: var(--sf-ink-secondary);
        }

        .media-frame {
            border: 1px solid var(--sf-border);
            border-radius: var(--sf-radius);
            overflow: hidden;
            background: var(--sf-surface-card);
        }

        .media-frame img {
            display: block;
            width: 100%;
            height: auto;
        }

        @media (max-width: 991.98px) {
            .hero-grid {
                grid-template-columns: 1fr;
                gap: var(--sf-space-6);
            }

            .hero {
                padding-block: calc(var(--sf-space-7) + 40px) var(--sf-space-7);
            }
        }

        @media (max-width: 575.98px) {
            .hero-proof {
                grid-template-columns: 1fr 1fr;
                gap: var(--sf-space-4);
            }
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
            <li><a class="dropdown-item" href="#features">Fortalezas</a></li>
            <li><a class="dropdown-item" href="#combos">Promociones</a></li>
            <li><a class="dropdown-item" href="#registro">Registro</a></li>
            <li><a class="dropdown-item" href="#servicios">Otros Servicios</a></li>
            <li><a class="dropdown-item" href="#redes">Redes Sociales</a></li>
            <li><a class="dropdown-item" href="#faq">Preguntas Frecuentes</a></li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item fw-bold" href="{{ route('donna') }}">
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
            <li><a class="dropdown-item" href="{{ route('shop') }}#inmediata-individual">Entrega Inmediata — Individual</a>
            </li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#combos">Entrega Inmediata — Combos</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#pedidos">Pedidos</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#personalizadas">Personalizadas</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#completos">Cuentas completas</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#juegos">Juegos</a></li>
        </ul>
    </div>
@endsection

@section('header')
    <div class="hero">
        <div class="sf-container">
            <div class="hero-grid">
                <div>
                    <span class="sf-eyebrow">Suscripciones premium · Ecuador</span>
                    <h1 class="hero-title">Más que ver,<br><em>vivir</em> el streaming premium</h1>
                    <p class="hero-lead">
                        Netflix, Disney+, HBO Max, Prime Video, Spotify y más — al precio de un café,
                        activados en minutos y con garantía real si algo falla.
                    </p>

                    <div class="hero-actions">
                        <a href="{{ route('shop') }}" class="sf-btn sf-btn--primary sf-btn--lg">
                            <i class="bi bi-collection-play"></i> Ver catálogo
                        </a>
                        <a href="https://wa.me/593961412826?text=Hola%20quiero%20m%C3%A1s%20informaci%C3%B3n%20sobre%20el%20servicio%20de"
                            target="_blank" rel="noopener" class="sf-btn sf-btn--ghost sf-btn--lg">
                            <i class="bi bi-whatsapp"></i> Escríbenos por WhatsApp
                        </a>
                    </div>

                    <div class="hero-proof">
                        <div>
                            <span class="hero-proof-value">+200</span>
                            <span class="hero-proof-label">Clientes activos</span>
                        </div>
                        <div>
                            <span class="hero-proof-value">&lt; 10 min</span>
                            <span class="hero-proof-label">Entrega promedio</span>
                        </div>
                        <div>
                            <span class="hero-proof-value">24 / 7</span>
                            <span class="hero-proof-label">Soporte directo</span>
                        </div>
                    </div>
                </div>

                <div class="hero-figure">
                    <img src="{{ asset('images/cuadrado/combos/33.png') }}"
                        alt="Combos de suscripciones premium de Streamify" loading="eager" width="600" height="600" />
                </div>
            </div>
        </div>
    </div>
@endsection

@section('sections')

    {{-- ── Plataformas disponibles ─────────────────────────────────────── --}}
    <section class="sf-section sf-section--sunken" style="padding-block: var(--sf-space-6);">
        <div class="sf-container">
            <p class="sf-eyebrow" style="text-align: center;">Plataformas disponibles</p>
            <div class="platform-strip">
                <span class="platform-chip">Netflix</span>
                <span class="platform-chip">Disney+</span>
                <span class="platform-chip">HBO Max</span>
                <span class="platform-chip">Prime Video</span>
                <span class="platform-chip">Spotify</span>
                <span class="platform-chip">Paramount+</span>
                <span class="platform-chip">Crunchyroll</span>
                <span class="platform-chip">Magis TV</span>
                <span class="platform-chip">Vix</span>
                <span class="platform-chip">Canva Pro</span>
            </div>
        </div>
    </section>

    {{-- ── Fortalezas ──────────────────────────────────────────────────── --}}
    <section id="features" class="sf-section">
        <div class="sf-container">
            <div class="sf-section-head sf-section-head--center">
                <span class="sf-eyebrow">Por qué Streamify</span>
                <h2 class="sf-title">Todo resuelto antes de que preguntes</h2>
                <p class="sf-lead">Ocho razones por las que más de doscientas personas nos siguen comprando cada mes.</p>
            </div>

            <div class="sf-grid sf-grid--4">
                <div class="sf-card">
                    <span class="sf-card-icon sf-card-icon--good"><i class="bi bi-lightning-charge-fill"></i></span>
                    <h3 class="sf-card-title">Entrega inmediata</h3>
                    <p class="sf-card-text">Recibes tus accesos pocos minutos después de confirmar el pago.</p>
                </div>
                <div class="sf-card">
                    <span class="sf-card-icon"><i class="bi bi-headset"></i></span>
                    <h3 class="sf-card-title">Atención 24/7</h3>
                    <p class="sf-card-text">Escribes por WhatsApp y te responde una persona, no un formulario.</p>
                </div>
                <div class="sf-card">
                    <span class="sf-card-icon sf-card-icon--good"><i class="bi bi-shield-check"></i></span>
                    <h3 class="sf-card-title">Con garantía</h3>
                    <p class="sf-card-text">Si una cuenta falla, se repone o se compensan los días perdidos.</p>
                </div>
                <div class="sf-card">
                    <span class="sf-card-icon"><i class="bi bi-ui-checks-grid"></i></span>
                    <h3 class="sf-card-title">Fácil de usar</h3>
                    <p class="sf-card-text">Configuras tu suscripción siguiendo pasos claros, sin complicaciones.</p>
                </div>
                <div class="sf-card">
                    <span class="sf-card-icon"><i class="bi bi-globe"></i></span>
                    <h3 class="sf-card-title">Plataforma propia</h3>
                    <p class="sf-card-text">Compras, recargas e historial en un panel web hecho a la medida.</p>
                </div>
                <div class="sf-card">
                    <span class="sf-card-icon sf-card-icon--gold"><i class="bi bi-people-fill"></i></span>
                    <h3 class="sf-card-title">+200 clientes activos</h3>
                    <p class="sf-card-text">Cientos de personas confían en Streamify y nos recomiendan.</p>
                </div>
                <div class="sf-card">
                    <span class="sf-card-icon"><i class="bi bi-cart-check-fill"></i></span>
                    <h3 class="sf-card-title">Ventas automatizadas</h3>
                    <p class="sf-card-text">El sistema procesa tu compra al instante, a cualquier hora del día.</p>
                </div>
                <div class="sf-card">
                    <span class="sf-card-icon sf-card-icon--gold"><i class="bi bi-gift-fill"></i></span>
                    <h3 class="sf-card-title">Servicios adicionales</h3>
                    <p class="sf-card-text">Recargas móviles y pago de servicios básicos desde la misma cuenta.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Combos ──────────────────────────────────────────────────────── --}}
    <section id="combos" class="sf-section sf-section--sunken">
        <div class="sf-container">
            <div class="sf-section-head sf-section-head--center">
                <span class="sf-eyebrow">Promociones</span>
                <h2 class="sf-title">Nuestros combos</h2>
                <p class="sf-lead">Varias plataformas en un solo pago mensual. Los precios más bajos del catálogo.</p>
            </div>

            <div class="sf-grid sf-grid--3">
                <article class="combo-card">
                    <img src="{{ asset('images/cuadrado/combos/32.png') }}"
                        alt="Combo Básico: Spotify, Disney+ y Prime Video" loading="lazy">
                    <div class="combo-body">
                        <span class="sf-tag sf-tag--brand" style="align-self: flex-start;">Combo Básico</span>
                        <h3 class="combo-name">Spotify + Disney+ + Prime Video</h3>
                        <p class="combo-price">$6.00 <span>/ mes</span></p>
                    </div>
                </article>

                <article class="combo-card">
                    <img src="{{ asset('images/cuadrado/combos/34.png') }}"
                        alt="Combo Maratón: Netflix, Disney+ y HBO Max" loading="lazy">
                    <div class="combo-body">
                        <span class="sf-tag sf-tag--gold" style="align-self: flex-start;">Combo Maratón</span>
                        <h3 class="combo-name">Netflix + Disney+ + HBO Max</h3>
                        <p class="combo-price">$7.50 <span>/ mes</span></p>
                    </div>
                </article>

                <article class="combo-card">
                    <img src="{{ asset('images/cuadrado/combos/36.png') }}"
                        alt="Super Combo: HBO Max, Disney+, Paramount+ y Crunchyroll" loading="lazy">
                    <div class="combo-body">
                        <span class="sf-tag sf-tag--good" style="align-self: flex-start;">Super Combo</span>
                        <h3 class="combo-name">HBO Max + Disney+ + Paramount+ + Crunchyroll</h3>
                        <p class="combo-price">$7.00 <span>/ mes</span></p>
                    </div>
                </article>
            </div>

            <div style="text-align: center; margin-top: var(--sf-space-6);">
                <a href="{{ route('shop') }}#combos" class="sf-btn sf-btn--primary">
                    Ver todos los combos <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- ── Registro ────────────────────────────────────────────────────── --}}
    <section id="registro" class="sf-section">
        <div class="sf-container">
            <div class="sf-panel sf-panel--accent" style="padding: var(--sf-space-7);">
                <div class="sf-split">
                    <div>
                        <span class="sf-eyebrow" style="color: var(--sf-brand);">Cuenta gratuita</span>
                        <h2 class="sf-title">¡Forma parte de Streamify!</h2>
                        <p class="sf-lead">
                            Regístrate sin costo, recarga saldo y compra cuando quieras.
                            Tu historial, tus códigos y tus renovaciones quedan guardados en un solo lugar.
                        </p>
                        <div class="hero-actions">
                            <a href="{{ route('register') }}" class="sf-btn sf-btn--primary sf-btn--lg">
                                <i class="bi bi-person-plus-fill"></i> Registrarme ahora
                            </a>
                            <a href="{{ route('cliente.login') }}" class="sf-btn sf-btn--ghost sf-btn--lg">
                                Ya tengo cuenta
                            </a>
                        </div>
                    </div>
                    <div class="media-frame">
                        <img src="{{ asset('images/shopweb.png') }}" alt="Panel de compras de Streamify" loading="lazy">
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Servicios adicionales ───────────────────────────────────────── --}}
    <section id="servicios" class="sf-section sf-section--sunken">
        <div class="sf-container">
            <div class="sf-split">
                <div class="media-frame">
                    <img src="{{ asset('images/tuenti.jpg') }}" alt="Recargas y pago de servicios en Streamify"
                        loading="lazy">
                </div>
                <div>
                    <span class="sf-eyebrow">Más que streaming</span>
                    <h2 class="sf-title">Servicios adicionales</h2>
                    <p class="sf-lead">Usa el mismo saldo de tu cuenta Streamify para resolver otros pagos del mes.</p>

                    <div class="sf-rows" style="margin-top: var(--sf-space-5);">
                        <div class="sf-row">
                            <div class="sf-status"><span class="sf-dot sf-dot--good"></span>
                                <span class="sf-row-name">Recargas de saldo móvil</span>
                            </div>
                            <span class="sf-row-meta">Todas las operadoras</span>
                        </div>
                        <div class="sf-row">
                            <div class="sf-status"><span class="sf-dot sf-dot--good"></span>
                                <span class="sf-row-name">Pago de luz y agua</span>
                            </div>
                            <span class="sf-row-meta">Servicios básicos</span>
                        </div>
                        <div class="sf-row">
                            <div class="sf-status"><span class="sf-dot sf-dot--good"></span>
                                <span class="sf-row-name">Pago de internet</span>
                            </div>
                            <span class="sf-row-meta">Proveedores locales</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Donna AI ────────────────────────────────────────────────────── --}}
    <section id="donna" class="sf-section sf-section--inverse">
        <div class="sf-container">
            <div class="sf-split">
                <div>
                    <span class="sf-tag sf-tag--gold" style="margin-bottom: var(--sf-space-4);">Nuevo servicio</span>
                    <h2 class="sf-title" style="color: #fff;">Conoce a Donna</h2>
                    <p class="sf-lead">
                        Tu secretaria inteligente impulsada por IA. Automatiza la atención al cliente de tu
                        negocio o tu productividad personal, directo desde WhatsApp.
                    </p>

                    <div class="sf-grid sf-grid--2" style="margin-top: var(--sf-space-6);">
                        <div style="display: flex; gap: var(--sf-space-3);">
                            <i class="bi bi-person-check-fill" style="color: var(--sf-gold); font-size: 1.1rem;"></i>
                            <div>
                                <div style="font-weight: 600; color: #fff; font-size: var(--sf-text-sm);">Donna Personal
                                </div>
                                <div class="sf-row-meta">Secretaria privada para el dueño del negocio</div>
                            </div>
                        </div>
                        <div style="display: flex; gap: var(--sf-space-3);">
                            <i class="bi bi-building-check" style="color: var(--sf-gold); font-size: 1.1rem;"></i>
                            <div>
                                <div style="font-weight: 600; color: #fff; font-size: var(--sf-text-sm);">Donna Business
                                </div>
                                <div class="sf-row-meta">Asesora que atiende a tus clientes finales</div>
                            </div>
                        </div>
                        <div style="display: flex; gap: var(--sf-space-3);">
                            <i class="bi bi-calendar-check" style="color: var(--sf-gold); font-size: 1.1rem;"></i>
                            <div>
                                <div style="font-weight: 600; color: #fff; font-size: var(--sf-text-sm);">Google Calendar
                                </div>
                                <div class="sf-row-meta">Agenda citas automáticamente</div>
                            </div>
                        </div>
                        <div style="display: flex; gap: var(--sf-space-3);">
                            <i class="bi bi-whatsapp" style="color: var(--sf-gold); font-size: 1.1rem;"></i>
                            <div>
                                <div style="font-weight: 600; color: #fff; font-size: var(--sf-text-sm);">WhatsApp nativo
                                </div>
                                <div class="sf-row-meta">Responde por tu número de negocio</div>
                            </div>
                        </div>
                    </div>

                    <div class="hero-actions">
                        <a href="{{ route('donna') }}" class="sf-btn sf-btn--gold sf-btn--lg">
                            <i class="bi bi-robot"></i> Conocer Donna
                        </a>
                        <a href="https://wa.me/593961412826?text=Hola%2C%20quiero%20informaci%C3%B3n%20sobre%20Donna%20AI"
                            target="_blank" rel="noopener" class="sf-btn sf-btn--onDark sf-btn--lg">
                            <i class="bi bi-whatsapp"></i> Consultar precio
                        </a>
                    </div>
                </div>

                <div class="donna-chat">
                    <div class="donna-chat-head">
                        <div class="donna-avatar"><i class="bi bi-robot"></i></div>
                        <div>
                            <div style="font-weight: 600; color: #fff;">Donna</div>
                            <div class="sf-status" style="color: var(--sf-gold); font-size: var(--sf-text-xs);">
                                <span class="sf-dot" style="background: var(--sf-good);"></span> Secretaria IA · En línea
                            </div>
                        </div>
                    </div>
                    <div class="donna-thread">
                        <div class="donna-msg donna-msg--in">Hola, quiero una cita para mañana</div>
                        <div class="donna-msg donna-msg--out">¡Hola! Tengo disponible a las 10:00 o las 15:30. ¿Cuál te
                            viene mejor?</div>
                        <div class="donna-msg donna-msg--in">Las 10:00 perfecto</div>
                        <div class="donna-msg donna-msg--out">Listo, tu cita está agendada para mañana a las 10:00. Te
                            confirmaré por aquí.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Redes sociales ──────────────────────────────────────────────── --}}
    <section id="redes" class="sf-section">
        <div class="sf-container">
            <div class="sf-split">
                <div>
                    <span class="sf-eyebrow">Comunidad</span>
                    <h2 class="sf-title">Síguenos en redes</h2>
                    <p class="sf-lead">Publicamos promociones, stock nuevo y avisos de mantenimiento antes que en
                        ningún otro lado.</p>

                    <div class="sf-grid sf-grid--2" style="margin-top: var(--sf-space-5);">
                        <a href="https://www.facebook.com/share/1Cco5izY9Y/?mibextid=wwXIfr" target="_blank"
                            rel="noopener" class="social-link"><i class="bi bi-facebook"></i> Facebook</a>
                        <a href="https://www.instagram.com/stribarra" target="_blank" rel="noopener"
                            class="social-link"><i class="bi bi-instagram"></i> Instagram</a>
                        <a href="https://www.tiktok.com/@lv_pablin" target="_blank" rel="noopener"
                            class="social-link"><i class="bi bi-tiktok"></i> TikTok</a>
                        <a href="https://t.me/Streamifyhq" target="_blank" rel="noopener" class="social-link"><i
                                class="bi bi-telegram"></i> Telegram</a>
                    </div>
                </div>
                <div class="media-frame">
                    <img src="{{ asset('images/redes.png') }}" alt="Redes sociales de Streamify" loading="lazy">
                </div>
            </div>
        </div>
    </section>

    {{-- ── Preguntas frecuentes ────────────────────────────────────────── --}}
    <section id="faq" class="sf-section sf-section--sunken">
        <div class="sf-container">
            <div class="sf-split">
                <div>
                    <span class="sf-eyebrow">Dudas comunes</span>
                    <h2 class="sf-title">Preguntas frecuentes</h2>

                    <div class="accordion" id="faqAccordion" style="margin-top: var(--sf-space-5);">
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="faq1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqContent1" aria-expanded="true" aria-controls="faqContent1">
                                    ¿Cómo funciona Streamify?
                                </button>
                            </h3>
                            <div id="faqContent1" class="accordion-collapse collapse show" aria-labelledby="faq1"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Streamify te da acceso a suscripciones premium a precios accesibles. Recargas saldo,
                                    eliges tu suscripción y la recibes activada en minutos.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="faq2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqContent2" aria-expanded="false" aria-controls="faqContent2">
                                    ¿Cómo puedo pagar?
                                </button>
                            </h3>
                            <div id="faqContent2" class="accordion-collapse collapse" aria-labelledby="faq2"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Por transferencia bancaria o con el saldo digital que ya tengas recargado en tu
                                    cuenta de Streamify.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="faq3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqContent3" aria-expanded="false" aria-controls="faqContent3">
                                    ¿Es seguro recargar saldo aquí?
                                </button>
                            </h3>
                            <div id="faqContent3" class="accordion-collapse collapse" aria-labelledby="faq3"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Sí. La plataforma está construida siguiendo prácticas de ingeniería de software
                                    probadas, con sesiones seguras y registro de cada movimiento de tu saldo.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="faq4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqContent4" aria-expanded="false" aria-controls="faqContent4">
                                    Si una suscripción falla, ¿tengo garantía?
                                </button>
                            </h3>
                            <div id="faqContent4" class="accordion-collapse collapse" aria-labelledby="faq4"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Por supuesto. Se te compensan los días perdidos y te acompañamos con las políticas
                                    de cada plataforma. Solo sigue las reglas —son sencillas— y escríbenos con calma.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="media-frame">
                    <img src="{{ asset('images/fac.png') }}" alt="Preguntas frecuentes de Streamify" loading="lazy">
                </div>
            </div>
        </div>
    </section>

    {{-- ── CTA final ───────────────────────────────────────────────────── --}}
    <section class="sf-section" style="padding-block: var(--sf-space-7);">
        <div class="sf-container" style="text-align: center;">
            <h2 class="sf-title">¿Listo para empezar?</h2>
            <p class="sf-lead" style="margin-inline: auto;">Elige tu plataforma favorita y tenla activa hoy mismo.</p>
            <div class="hero-actions" style="justify-content: center;">
                <a href="{{ route('shop') }}" class="sf-btn sf-btn--primary sf-btn--lg">Ir al catálogo</a>
                <a href="{{ route('tutorial') }}" class="sf-btn sf-btn--ghost sf-btn--lg">Ver tutorial</a>
            </div>
        </div>
    </section>

@endsection
