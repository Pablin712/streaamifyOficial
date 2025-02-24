@extends('layouts.cliente')

@section('title', 'Tutorial de Uso')

@section('styles')
    <style>
        .step-card {
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.2s ease-in-out;
        }

        .step-card:hover {
            transform: scale(1.05);
        }

        .step-number {
            font-size: 24px;
            font-weight: bold;
            width: 50px;
            height: 50px;
            background-color: #274698;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .bg-streamify-blue {
            background-color: #274698;
            color: #FFFFFF;
        }

        .bg-streamify-yellow {
            background-color: #E4B100;
            color: #1D1D1B;
        }
    </style>
    <!-- Estilos para hacer las flechas más visibles -->
    <style>
        .custom-carousel-control {
            width: 50px;
            height: 50px;
            background-color: rgba(0, 0, 0, 0.5);
            /* Fondo semi-transparente */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-carousel-control span {
            filter: brightness(2);
            /* Hace las flechas más visibles */
            width: 30px;
            height: 30px;
        }

        .carousel-indicators button {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .carousel-indicators .active {
            background-color: #007bff;
            /* Color azul para el indicador activo */
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
        </ul>
    </div>
@endsection
@section('header')
    <div class="container px-5">
        <div class="row gx-5 align-items-center">
            <!-- Sección de Texto -->
            <div class="col-lg-6">
                <div class="mb-5 mb-lg-0 text-center text-lg-start">
                    <h1 class="display-4 lh-1 mb-3">📱 ¡Gestiona tu cuenta fácil y rápido!</h1>
                    <p class="lead fw-normal text-muted mb-4">
                        Con nuestra aplicación, puedes comprar productos, renovar suscripciones, recargar saldo y consultar
                        el historial de compras en un solo lugar.
                        Disfruta de <strong>todas tus suscripciones sin complicaciones</strong>. 🚀
                    </p>
                    <p class="text-muted">
                        🔹 Compra y renueva en segundos. <br>
                        🔹 Revisa fechas de vencimiento y transacciones. <br>
                        🔹 Recarga saldo de manera segura y automática.
                    </p>
                </div>
            </div>

            <!-- Sección de Imagen -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="masthead-device-mockup">
                    <div class="text-center">
                        <img src="{{ asset('images/app.png') }}" alt="App de Cliente" class="rounded shadow-lg"
                            style="width: 100%; max-width: 350px; height: auto; object-fit: contain;" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('sections')
    <div class="container py-5">
        <h1 class="text-center mb-4">Cómo Usar Streamify</h1>
        <p class="text-center text-muted">Sigue estos pasos para registrarte, recargar saldo, comprar y gestionar tu cuenta.
        </p>

        <div class="row">
            <!-- PASO 1: Registrarse e Iniciar Sesión -->
            <div class="col-md-6 mb-4">
                <div class="card step-card shadow-sm p-3">
                    <div class="text-center">
                        <div class="step-number">1</div>
                        <h4 class="fw-bold">Registrarse e Iniciar Sesión</h4>
                        <p class="text-muted text-start">
                            1️⃣ <strong>Regístrate</strong>: Ingresa tus datos personales como nombres, apellidos, número de
                            teléfono válido (para WhatsApp), correo electrónico y una contraseña segura con mínimo 6
                            caracteres, un símbolo y un número. <br><br>
                            2️⃣ <strong>Inicia Sesión</strong>: Accede a tu cuenta en cualquier momento para gestionar tu
                            actividad como cliente, comprar productos y combos, renovar cuentas y recargar saldo.
                            <strong>Todo de forma automática</strong>.
                        </p>

                        <!-- Contenedor de Imágenes -->
                        <div class="d-flex justify-content-center gap-3 mb-3">
                            <img src="{{ asset('images/registro.png') }}" class="img-fluid rounded shadow" alt="Registro"
                                style="width: 45%;">
                            <img src="{{ asset('images/login.png') }}" class="img-fluid rounded shadow" alt="Ingreso"
                                style="width: 45%;">
                        </div>

                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-center gap-3">
                            <a href="{{ route('cliente.register') }}" class="btn btn-primary">Registrarse</a>
                            <a href="{{ route('cliente.login') }}" class="btn btn-success">Iniciar Sesión</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 2: Recargar Saldo -->
            <div class="col-md-6 mb-4">
                <div class="card step-card shadow-sm p-3">
                    <div class="text-center">
                        <div class="step-number">2</div>
                        <h4 class="fw-bold">Recargar Saldo</h4>
                        <p class="text-muted text-start">
                            🔹 <strong>Ir a la sección de Recarga</strong>: Dirígete a tu perfil y selecciona la opción
                            "Recargar Saldo".<br>
                            🔹 <strong>Escoge un banco</strong>: Verás varias opciones de bancos disponibles para depositar
                            o transferir.<br>
                            🔹 <strong>Sube los datos requeridos</strong>: Ingresa el valor pagado, sube la foto del
                            comprobante y escribe el número de comprobante.<br>
                        </p>

                        <!-- Contenedor de Imágenes -->
                        <div class="d-flex justify-content-center gap-3 mb-3">
                            <img src="{{ asset('images/abrirPerfil.png') }}" class="img-fluid rounded shadow"
                                alt="Perfil" style="width: 45%;">
                            <img src="{{ asset('images/bancos.png') }}" class="img-fluid rounded shadow"
                                alt="Recarga de saldo" style="width: 45%;">
                        </div>

                        <!-- Botón de Acción -->
                        <div class="d-flex justify-content-center">
                            <a href="{{ route('recargar.index') }}" class="btn btn-warning text-dark">Ir a Recargar
                                Saldo</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 3: Comprar un Producto o Combo -->
            <div class="col-md-6 mb-4">
                <div class="card step-card shadow-sm p-3">
                    <div class="text-center">
                        <div class="step-number">3</div>
                        <h4 class="fw-bold">Comprar Productos o Combos</h4>
                        <p class="text-muted text-start">
                            Con nuestra plataforma, puedes comprar productos y combos fácilmente:
                            <br><br>
                            🛍️ <strong>Compra rápida y sencilla:</strong> Elige tu producto favorito y adquiérelo en
                            segundos. <br>
                            📦 <strong>Gestiona tus pedidos:</strong> Sigue el estado de tus compras en tiempo real.<br>
                            🛒 <strong>Agrega productos al carrito:</strong> Planifica tus compras antes de pagar.<br>
                            ✅ <strong>Recibe tu compra de inmediato:</strong> Productos listos para ser disfrutados.<br>
                        </p>

                        <!-- Carrusel de Imágenes -->
                        <div id="compraCarousel" class="carousel slide mb-3" data-bs-ride="carousel" data-bs-wrap="true">
                            <!-- Indicadores de Puntos -->
                            <div class="carousel-indicators">
                                <button type="button" data-bs-target="#compraCarousel" data-bs-slide-to="0"
                                    class="active"></button>
                                <button type="button" data-bs-target="#compraCarousel" data-bs-slide-to="1"></button>
                                <button type="button" data-bs-target="#compraCarousel" data-bs-slide-to="2"></button>
                                <button type="button" data-bs-target="#compraCarousel" data-bs-slide-to="3"></button>
                                <button type="button" data-bs-target="#compraCarousel" data-bs-slide-to="4"></button>
                            </div>
                            <div class="carousel-inner">
                                <!-- Imagen 1 -->
                                <div class="carousel-item active">
                                    <p class="mt-2 text-muted"><strong>Paso 1:</strong> Selecciona el producto o combo que
                                        deseas comprar.</p>
                                    <img src="{{ asset('images/paso1.png') }}" class="d-block w-100 rounded shadow"
                                        alt="Elegir producto">
                                </div>
                                <!-- Imagen 2 -->
                                <div class="carousel-item">
                                    <p class="mt-2 text-muted"><strong>Paso 2:</strong> Agrega el producto al carrito si
                                        deseas comprar algunos servicios.</p>
                                    <img src="{{ asset('images/carrito.png') }}" class="d-block w-100 rounded shadow"
                                        alt="Añadir al carrito">
                                </div>
                                <!-- Imagen 3 -->
                                <div class="carousel-item">
                                    <p class="mt-2 text-muted"><strong>Paso 3:</strong> Confirma tu compra y paga con tu
                                        saldo disponible.</p>
                                    <img src="{{ asset('images/confirmar.png') }}" class="d-block w-100 rounded shadow"
                                        alt="Finalizar compra">
                                </div>
                                <!-- Imagen 4 -->
                                <div class="carousel-item">
                                    <p class="mt-2 text-muted"><strong>Posible error:</strong> Si no tienes suficiente
                                        saldo te saldrá
                                        lo siguiente, asegúrate de tener saldo antes de realizar tu compra.</p>
                                    <img src="{{ asset('images/errorCompra.png') }}" class="d-block w-100 rounded shadow"
                                        alt="Recibir compra">
                                </div>
                                <!-- Imagen 5 -->
                                <div class="carousel-item">
                                    <p class="mt-2 text-muted"><strong>Compra exitosa:</strong> Si todo salió bien,
                                        recibirás tu compra.
                                        Si revisas tu email, te llegó ahí tambíen un recibo que comprueba que has realizado
                                        tu compra, te puede servir como garantía.</p>
                                    <img src="{{ asset('images/exito.png') }}" class="d-block w-100 rounded shadow"
                                        alt="Recibir compra">
                                </div>
                            </div>

                            <!-- Controles de Navegación con Mayor Visibilidad -->
                            <button class="carousel-control-prev custom-carousel-control" type="button"
                                data-bs-target="#compraCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            </button>
                            <button class="carousel-control-next custom-carousel-control" type="button"
                                data-bs-target="#compraCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            </button>
                        </div>

                        <!-- Botón para ir a la Tienda -->
                        <div class="d-flex justify-content-center">
                            <a href="{{ route('shop') }}" class="btn btn-primary">Ir a la Tienda</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 4: Ver Actividad del Cliente -->
            <div class="col-md-6 mb-4">
                <div class="card step-card shadow-sm p-3">
                    <div class="text-center">
                        <div class="step-number">4</div>
                        <h4 class="fw-bold">Ver Mi Actividad</h4>
                        <p class="text-muted text-start">
                            En tu perfil, selecciona Actividad en el menú desplegable. 
                            Accede a tu historial de cuentas activas, compras, pedidos y recargas.
                            Gestiona tus suscripciones y revisa el estado de tus transacciones con facilidad.
                        </p>

                        <!-- Carrusel de Pestañas -->
                        <div id="actividadCarousel" class="carousel slide mb-3" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <!-- Cuentas Activas -->
                                <div class="carousel-item active">
                                    <p class="mt-2 text-muted"><strong>Cuentas Activas:</strong> Consulta tus credenciales
                                        y fechas de vencimiento.
                                        <br> Usa el botón <strong>"Renovar"</strong> si tienes saldo disponible.
                                    </p>
                                    <img src="{{ asset('images/cuentas.png') }}" class="d-block w-100 rounded shadow"
                                        alt="Cuentas Activas">
                                </div>
                                <!-- Historial de Compras -->
                                <div class="carousel-item">
                                    <p class="mt-2 text-muted"><strong>Historial de Compras:</strong> Revisa los productos
                                        o combos que has adquirido.</p>
                                    <img src="{{ asset('images/compras.png') }}" class="d-block w-100 rounded shadow"
                                        alt="Historial de Compras">

                                </div>
                                <!-- Historial de Pedidos -->
                                <div class="carousel-item">
                                    <p class="mt-2 text-muted"><strong>Historial de Pedidos:</strong> Verifica los pedidos
                                        personalizados que has realizado.</p>
                                    <img src="{{ asset('images/pedidos.png') }}" class="d-block w-100 rounded shadow"
                                        alt="Historial de Pedidos">
                                </div>
                                <!-- Historial de Recargas -->
                                <div class="carousel-item">
                                    <p class="mt-2 text-muted"><strong>Historial de Recargas:</strong> Consulta si tu
                                        recarga fue <span class="text-success fw-bold">Aprobada</span> o <span
                                            class="text-danger fw-bold">Rechazada</span>.</p>
                                    <img src="{{ asset('images/recargas.png') }}" class="d-block w-100 rounded shadow"
                                        alt="Historial de Recargas">
                                </div>
                            </div>

                            <!-- Controles de Navegación -->
                            <button class="carousel-control-prev custom-carousel-control" type="button"
                                data-bs-target="#actividadCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            </button>
                            <button class="carousel-control-next custom-carousel-control" type="button"
                                data-bs-target="#actividadCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            </button>
                        </div>

                        <!-- Botón para ver la Actividad Completa -->
                        <div class="d-flex justify-content-center">
                            <a href="{{ route('historial.cliente') }}" class="btn btn-primary">Ver Mi Actividad</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Estilos para mejorar el diseño -->
            <style>
                .custom-carousel-control {
                    width: 50px;
                    height: 50px;
                    background-color: rgba(0, 0, 0, 0.5);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .custom-carousel-control span {
                    filter: brightness(2);
                    width: 30px;
                    height: 30px;
                }

                .carousel-indicators button {
                    width: 10px;
                    height: 10px;
                    border-radius: 50%;
                    background-color: rgba(0, 0, 0, 0.5);
                }

                .carousel-indicators .active {
                    background-color: #007bff;
                }
            </style>

            <!-- PASO 5: Renovar Cuenta o Combo -->
            <div class="col-md-6 mb-4">
                <div class="card step-card shadow-sm p-3">
                    <div class="text-center">
                        <div class="step-number">5</div>
                        <h4 class="fw-bold">Renovar Cuenta o Combo</h4>
                        <p class="text-muted">Extiende la duración de tu suscripción sin complicaciones.</p>
                        <div class="d-flex justify-content-center gap-3 mb-3">
                            <img src="{{ asset('images/renovar.png') }}" class="img-fluid rounded shadow" alt="Registro"
                                style="width: 45%;">
                            <img src="{{ asset('images/renovar2.png') }}" class="img-fluid rounded shadow" alt="Ingreso"
                                style="width: 45%;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
