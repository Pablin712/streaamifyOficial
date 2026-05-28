@extends('layouts.cliente')

@section('title', 'Condiciones de Servicio — Streamify')

@section('sections')
<section class="py-5 bg-light">
    <div class="container px-4 px-lg-5" style="max-width:860px;">

        <div class="mb-5">
            <h1 class="fw-bold">Condiciones de Servicio</h1>
            <p class="text-muted small">Última actualización: 27 de mayo de 2026</p>
            <hr>
        </div>

        <div class="mb-4">
            <p>
                Bienvenido a <strong>Streamify</strong> (<strong>streamify.aaronsoft.es</strong>), operado
                por <strong>Pablo Jiménez</strong>. Al acceder o usar nuestros servicios, aceptas estas
                Condiciones de Servicio. Si no estás de acuerdo, no uses los servicios.
            </p>
        </div>

        {{-- 1 --}}
        <div class="mb-4">
            <h4 class="fw-bold">1. Descripción del servicio</h4>
            <p>Streamify ofrece dos categorías de servicios:</p>
            <ul>
                <li>
                    <strong>Suscripciones de streaming:</strong> Acceso a cuentas de plataformas de
                    entretenimiento digital (Netflix, Spotify, Disney+, etc.) mediante perfiles compartidos
                    o cuentas individuales, según el plan contratado.
                </li>
                <li>
                    <strong>Donna — Secretaria Virtual IA:</strong> Servicio de asistente inteligente
                    que atiende mensajes por WhatsApp o Telegram, gestiona agendas en Google Calendar
                    y registra datos en Google Sheets, en nombre del cliente contratante.
                </li>
            </ul>
        </div>

        {{-- 2 --}}
        <div class="mb-4">
            <h4 class="fw-bold">2. Registro y cuenta</h4>
            <ul>
                <li>Debes proporcionar información verdadera al registrarte.</li>
                <li>Eres responsable de mantener la confidencialidad de tus credenciales de acceso.</li>
                <li>No puedes compartir tu cuenta con terceros no autorizados.</li>
                <li>Streamify puede suspender cuentas que incumplan estas condiciones sin previo aviso.</li>
            </ul>
        </div>

        {{-- 3 --}}
        <div class="mb-4">
            <h4 class="fw-bold">3. Pagos y saldo</h4>
            <ul>
                <li>Los servicios se pagan mediante recargas de saldo previas al consumo.</li>
                <li>Los precios publicados en el sitio son en dólares estadounidenses (USD) y pueden cambiar sin previo aviso.</li>
                <li>El saldo no consumido no tiene fecha de vencimiento, salvo que la cuenta sea eliminada por incumplimiento.</li>
                <li>No se realizan devoluciones de saldo una vez activado un servicio, salvo falla técnica atribuible a Streamify.</li>
            </ul>
        </div>

        {{-- 4 --}}
        <div class="mb-4">
            <h4 class="fw-bold">4. Servicio Donna — condiciones específicas</h4>
            <ul>
                <li>
                    Para activar Donna debes conectar una cuenta de Google con los permisos de
                    <strong>Google Calendar</strong> y <strong>Google Sheets</strong>. Al hacerlo,
                    autorizas a Streamify a actuar en tu nombre dentro del alcance estrictamente
                    necesario para prestar el servicio.
                </li>
                <li>
                    Streamify actuará únicamente según las instrucciones configuradas en Donna
                    (prompts, reglas y herramientas activadas). No accederemos a datos de Google
                    fuera de ese alcance.
                </li>
                <li>
                    Puedes revocar el acceso a Google en cualquier momento. Al hacerlo, Donna dejará
                    de funcionar hasta que autorices de nuevo.
                </li>
                <li>
                    Donna es un asistente automatizado. Streamify no garantiza que todas las
                    respuestas sean perfectas y no se hace responsable de decisiones tomadas con base
                    en respuestas generadas por IA.
                </li>
                <li>
                    La suscripción a Donna se renueva según el ciclo contratado (mensual, anual o
                    pago único). Streamify notificará el vencimiento próximo.
                </li>
            </ul>
        </div>

        {{-- 5 --}}
        <div class="mb-4">
            <h4 class="fw-bold">5. Uso aceptable</h4>
            <p>Está prohibido usar nuestros servicios para:</p>
            <ul>
                <li>Actividades ilegales o que infrinjan derechos de terceros.</li>
                <li>Distribución no autorizada de contenido de las plataformas de streaming.</li>
                <li>Usar Donna para enviar spam, estafas o mensajes masivos no autorizados.</li>
                <li>Intentar acceder a sistemas de Streamify de forma no autorizada.</li>
            </ul>
        </div>

        {{-- 6 --}}
        <div class="mb-4">
            <h4 class="fw-bold">6. Suspensión y cancelación</h4>
            <ul>
                <li>Puedes cancelar tu suscripción en cualquier momento desde tu panel de cliente.</li>
                <li>Streamify puede suspender o cancelar el servicio por incumplimiento de estas condiciones, sin derecho a reembolso.</li>
                <li>Si una plataforma de streaming cambia sus políticas y afecta la disponibilidad del servicio, Streamify informará a los afectados y ofrecerá alternativas.</li>
            </ul>
        </div>

        {{-- 7 --}}
        <div class="mb-4">
            <h4 class="fw-bold">7. Limitación de responsabilidad</h4>
            <p>
                Streamify no se responsabiliza por interrupciones del servicio causadas por terceros
                (plataformas de streaming, proveedores de WhatsApp/Telegram, Google, caídas de internet).
                El servicio se ofrece "tal cual está" y nos esforzamos por mantener la mayor disponibilidad posible.
            </p>
        </div>

        {{-- 8 --}}
        <div class="mb-4">
            <h4 class="fw-bold">8. Propiedad intelectual</h4>
            <p>
                El nombre Streamify, el servicio Donna, el diseño del sitio y todo el contenido
                propio son propiedad de Pablo Jiménez. No puedes reproducirlos sin autorización escrita.
            </p>
        </div>

        {{-- 9 --}}
        <div class="mb-4">
            <h4 class="fw-bold">9. Cambios a las condiciones</h4>
            <p>
                Podemos modificar estas condiciones. Te avisaremos por WhatsApp o dentro del panel de
                cliente. Si continúas usando el servicio después de la notificación, aceptas las nuevas condiciones.
            </p>
        </div>

        {{-- 10 --}}
        <div class="mb-5">
            <h4 class="fw-bold">10. Contacto</h4>
            <p>Para consultas sobre estas condiciones:</p>
            <ul>
                <li><strong>WhatsApp:</strong> <a href="https://wa.me/593961412826" target="_blank">+593 961 412 826</a></li>
                <li><strong>Sitio web:</strong> <a href="{{ route('principal') }}">streamify.aaronsoft.es</a></li>
            </ul>
        </div>

        <div class="d-flex gap-3">
            <a href="{{ route('legal.privacidad') }}" class="btn btn-outline-secondary btn-sm">
                Política de Privacidad
            </a>
            <a href="{{ route('principal') }}" class="btn btn-outline-primary btn-sm">
                Volver al inicio
            </a>
        </div>

    </div>
</section>
@endsection
