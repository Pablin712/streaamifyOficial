@extends('layouts.cliente')

@section('title', 'Política de Privacidad — Streamify')

@section('sections')
<section class="py-5 bg-light">
    <div class="container px-4 px-lg-5" style="max-width:860px;">

        <div class="mb-5">
            <h1 class="fw-bold">Política de Privacidad</h1>
            <p class="text-muted small">Última actualización: 27 de mayo de 2026</p>
            <hr>
        </div>

        <div class="mb-4">
            <p>
                Streamify (<strong>streamify.aaronsoft.es</strong>), operado por <strong>Pablo Jiménez</strong>
                ("nosotros", "nuestro"), se compromete a proteger tu privacidad. Esta Política de Privacidad
                describe cómo recopilamos, usamos y protegemos tu información personal cuando usas nuestros
                servicios, incluyendo la plataforma de suscripciones de streaming y el servicio de secretaria
                virtual <strong>Donna</strong>.
            </p>
        </div>

        {{-- 1 --}}
        <div class="mb-4">
            <h4 class="fw-bold">1. Información que recopilamos</h4>
            <p>Recopilamos la siguiente información cuando te registras o usas nuestros servicios:</p>
            <ul>
                <li><strong>Datos de cuenta:</strong> nombre, número de teléfono, contraseña cifrada.</li>
                <li><strong>Datos de uso:</strong> suscripciones adquiridas, historial de compras, recargas de saldo.</li>
                <li><strong>Datos de soporte:</strong> mensajes enviados a través de nuestro sistema de soporte.</li>
                <li><strong>Datos de navegación:</strong> dirección IP, tipo de dispositivo y navegador (únicamente para seguridad y diagnóstico).</li>
            </ul>
        </div>

        {{-- 2 --}}
        <div class="mb-4">
            <h4 class="fw-bold">2. Servicio Donna — Integración con Google</h4>
            <p>
                El servicio <strong>Donna</strong> es una secretaria virtual con inteligencia artificial.
                Para funcionar, Donna requiere acceso a determinadas APIs de Google. Al conectar tu cuenta
                de Google a Donna, nosotros:
            </p>
            <ul>
                <li>Solicitamos acceso a <strong>Google Calendar</strong> para agendar, consultar y gestionar eventos en tu calendario.</li>
                <li>Solicitamos acceso a <strong>Google Sheets</strong> para registrar datos, leads y notas en hojas de cálculo de tu propiedad.</li>
                <li>Almacenamos los tokens de acceso de forma <strong>cifrada</strong> en nuestros servidores, usando cifrado AES-256.</li>
                <li><strong>Nunca</strong> accedemos a tu cuenta de Google fuera de las acciones que tú o Donna ejecutan explícitamente.</li>
                <li><strong>Nunca</strong> compartimos tus tokens ni datos de Google con terceros.</li>
                <li>Puedes <strong>revocar el acceso</strong> en cualquier momento desde la página <a href="{{ route('donna') }}">streamify.aaronsoft.es/donna</a> o directamente desde <a href="https://myaccount.google.com/permissions" target="_blank" rel="noopener">myaccount.google.com/permissions</a>.</li>
            </ul>
            <p>
                El uso que hacemos de la información obtenida de las APIs de Google cumple con la
                <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener">
                    Política de Datos de Usuario de los Servicios de API de Google
                </a>, incluidos los requisitos de Uso Limitado.
            </p>
        </div>

        {{-- 3 --}}
        <div class="mb-4">
            <h4 class="fw-bold">3. Cómo usamos tu información</h4>
            <ul>
                <li>Para gestionar tu cuenta y suscripciones.</li>
                <li>Para procesar pagos y recargas de saldo.</li>
                <li>Para prestar el servicio Donna (agendamiento, registros en Sheets, respuestas automatizadas).</li>
                <li>Para enviarte notificaciones relacionadas con tu servicio (vencimientos, confirmaciones).</li>
                <li>Para mejorar nuestros servicios y detectar actividad fraudulenta.</li>
            </ul>
        </div>

        {{-- 4 --}}
        <div class="mb-4">
            <h4 class="fw-bold">4. Almacenamiento y seguridad</h4>
            <p>
                Tu información se almacena en servidores seguros. Las contraseñas se guardan con hash
                bcrypt. Los tokens de Google y claves de API de terceros se cifran con AES-256 antes
                de ser guardados. Aplicamos controles de acceso para que solo el personal autorizado
                pueda acceder a los datos.
            </p>
        </div>

        {{-- 5 --}}
        <div class="mb-4">
            <h4 class="fw-bold">5. Compartición de datos con terceros</h4>
            <p>No vendemos ni alquilamos tu información personal. Podemos compartir datos con:</p>
            <ul>
                <li><strong>Google LLC</strong>: para ejecutar las integraciones de Calendar y Sheets autorizadas por ti.</li>
                <li><strong>Proveedores de mensajería</strong> (WhatsApp / Telegram): para que Donna pueda responder mensajes en tu nombre, si contrataste ese servicio.</li>
                <li><strong>Autoridades legales</strong>: únicamente si lo exige la ley aplicable.</li>
            </ul>
        </div>

        {{-- 6 --}}
        <div class="mb-4">
            <h4 class="fw-bold">6. Retención de datos</h4>
            <p>
                Conservamos tus datos mientras tu cuenta esté activa. Si solicitas la eliminación de tu
                cuenta, borraremos tus datos personales en un plazo de 30 días, excepto cuando debamos
                conservarlos por obligaciones legales o contables.
            </p>
        </div>

        {{-- 7 --}}
        <div class="mb-4">
            <h4 class="fw-bold">7. Tus derechos</h4>
            <ul>
                <li>Acceder a los datos que tenemos sobre ti.</li>
                <li>Solicitar la corrección o eliminación de tus datos.</li>
                <li>Revocar el acceso de Streamify a tu cuenta de Google en cualquier momento.</li>
                <li>Solicitar la portabilidad de tus datos.</li>
            </ul>
            <p>Para ejercer estos derechos, contáctanos por WhatsApp o al correo indicado abajo.</p>
        </div>

        {{-- 8 --}}
        <div class="mb-4">
            <h4 class="fw-bold">8. Cookies</h4>
            <p>
                Usamos cookies de sesión necesarias para el funcionamiento del sitio (autenticación).
                No usamos cookies de rastreo publicitario.
            </p>
        </div>

        {{-- 9 --}}
        <div class="mb-4">
            <h4 class="fw-bold">9. Cambios a esta política</h4>
            <p>
                Podemos actualizar esta política ocasionalmente. Te notificaremos los cambios importantes
                por WhatsApp o a través del panel de cliente. La versión vigente siempre estará disponible
                en <a href="{{ route('legal.privacidad') }}">streamify.aaronsoft.es/politica-de-privacidad</a>.
            </p>
        </div>

        {{-- 10 --}}
        <div class="mb-5">
            <h4 class="fw-bold">10. Contacto</h4>
            <p>
                Si tienes preguntas sobre esta Política de Privacidad, puedes contactarnos:
            </p>
            <ul>
                <li><strong>WhatsApp:</strong> <a href="https://wa.me/593961412826" target="_blank">+593 961 412 826</a></li>
                <li><strong>Sitio web:</strong> <a href="{{ route('principal') }}">streamify.aaronsoft.es</a></li>
            </ul>
        </div>

        <div class="d-flex gap-3">
            <a href="{{ route('legal.terminos') }}" class="btn btn-outline-secondary btn-sm">
                Condiciones de Servicio
            </a>
            <a href="{{ route('principal') }}" class="btn btn-outline-primary btn-sm">
                Volver al inicio
            </a>
        </div>

    </div>
</section>
@endsection
