@extends('layouts.table')
@section('title')
    Usuarios
@endsection
@section('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Select2 Dark Mode -->
    <link href="{{ asset('css/select2-dark-mode.css') }}" rel="stylesheet" />

    <style>
        .btn-rosa-1 {
            background: #f8bbd0;
            color: #fff;
        }

        .btn-rosa-2 {
            background: #f48fb1;
            color: #fff;
        }

        .btn-rosa-3 {
            background: #f06292;
            color: #fff;
        }

        .btn-rosa-4 {
            background: #ec407a;
            color: #fff;
        }

        .btn-rosa-5 {
            background: #ad1457;
            color: #fff;
        }

        .btn-rosa-1:hover,
        .btn-rosa-2:hover,
        .btn-rosa-3:hover,
        .btn-rosa-4:hover,
        .btn-rosa-5:hover {
            filter: brightness(0.95);
        }

        /* Toggle Switch para estado de cobro */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background-color: #28a745;
        }

        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        .toggle-slider.disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* Fila clicable para copiar mensaje */
        tbody tr.clickable-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        tbody tr.clickable-row:hover {
            background-color: rgba(248, 187, 208, 0.2) !important;
        }

        tbody tr.clickable-row td {
            position: relative;
        }

        /* Evitar que los clicks en botones activen la fila */
        tbody tr.clickable-row .btn,
        tbody tr.clickable-row .toggle-switch,
        tbody tr.clickable-row input[type="checkbox"] {
            pointer-events: auto;
        }

        .btn-whatsapp-green {
            background: linear-gradient(135deg, #1fbf63, #128c7e);
            border-color: #128c7e;
            color: #ffffff;
        }

        .btn-whatsapp-green:hover,
        .btn-whatsapp-green:focus {
            background: linear-gradient(135deg, #22d06d, #0f7a6e);
            border-color: #0f7a6e;
            color: #ffffff;
        }

        .btn-whatsapp-blue {
            background: linear-gradient(135deg, #1b7cff, #0d5bd7);
            border-color: #0d5bd7;
            color: #ffffff;
            box-shadow: 0 10px 22px rgba(13, 91, 215, 0.18);
        }

        .btn-whatsapp-blue:hover,
        .btn-whatsapp-blue:focus {
            background: linear-gradient(135deg, #2a89ff, #0a4db8);
            border-color: #0a4db8;
            color: #ffffff;
        }

        .wa-compose-modal {
            background:
                radial-gradient(circle at top right, rgba(17, 163, 127, 0.16), transparent 32%),
                radial-gradient(circle at bottom left, rgba(27, 124, 255, 0.14), transparent 30%),
                #ffffff;
        }

        .wa-compose-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.35rem 1.5rem 1rem;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
        }

        .wa-compose-hero-copy {
            min-width: 0;
        }

        .wa-compose-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }

        .wa-compose-title {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
            line-height: 1.2;
            color: #f8fafc !important;
        }

        .wa-compose-subtitle {
            margin: 0.4rem 0 0;
            color: rgba(248, 250, 252, 0.8) !important;
            font-size: 0.92rem;
        }

        .wa-compose-channel {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 0.9rem;
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
            font-weight: 600;
            white-space: nowrap;
        }

        .wa-compose-channel-dot {
            width: 0.7rem;
            height: 0.7rem;
            border-radius: 50%;
            background: #25d366;
            box-shadow: 0 0 0 4px rgba(37, 211, 102, 0.18);
        }

        .wa-compose-channel-dot.is-blue {
            background: #1b7cff;
            box-shadow: 0 0 0 4px rgba(27, 124, 255, 0.18);
        }

        .wa-compose-body {
            padding: 1.35rem 1.5rem 1.5rem;
        }

        .wa-compose-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.85rem;
            margin-bottom: 1rem;
        }

        .wa-compose-card {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1rem;
            background: rgba(248, 250, 252, 0.92);
            padding: 0.95rem 1rem;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
        }

        .wa-compose-label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }

        .wa-compose-value {
            margin: 0;
            color: #0f172a;
            font-weight: 600;
            line-height: 1.4;
            word-break: break-word;
        }

        .wa-compose-panel {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.1rem;
            background: #ffffff;
            box-shadow: 0 18px 30px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .wa-compose-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.95rem 1rem;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            background: rgba(248, 250, 252, 0.92);
        }

        .wa-compose-panel-title {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 700;
            color: #0f172a;
        }

        .wa-compose-counter {
            font-size: 0.82rem;
            font-weight: 600;
            color: #64748b;
        }

        .wa-compose-textarea {
            min-height: 220px;
            border: 0;
            border-radius: 0;
            resize: vertical;
            padding: 1rem;
            font-size: 0.96rem;
            line-height: 1.6;
            box-shadow: none !important;
        }

        .wa-compose-help {
            margin: 0;
            padding: 0.85rem 1rem 1rem;
            font-size: 0.84rem;
            color: #64748b;
        }

        @media (max-width: 767.98px) {
            .wa-compose-hero {
                flex-direction: column;
                align-items: stretch;
            }

            .wa-compose-body {
                padding: 1rem;
            }
        }
    </style>
@endsection
@section('h1')
    Usuarios
@endsection
@section('breadcrumb')
    <a href="{{ route('cuentas') }}">Cuentas</a>
@endsection
@section('breadcrumb2')
    Usuarios Activos
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Muestra vista de todos los usuarios y sus fecha de caducidad para controlar actividad.</p>
@endsection
@section('tablename', 'Usuarios')
@section('btncrear')
    @if (Auth::user()->hasPermissionTo('ventas.create'))
        <a href="{{ route('ventas.create') }}" class="btn btn-primary">Nueva Venta</a>
    @endif
@endsection
@section('table1')
    <!-- Controles de búsqueda y registros -->
    <div class="row mb-3 align-items-end">
        <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
            <label for="usuarios-table-search" class="form-label fw-semibold">
                <i class="fas fa-search text-primary"></i> Buscar:
            </label>
            <input id="usuarios-table-search"
                   type="text"
                   placeholder="Buscar usuario..."
                   class="form-control">
        </div>
        <div class="col-lg-4 col-md-5 col-12">
            <label for="usuarios-table-rows-per-page" class="form-label fw-semibold">
                <i class="fas fa-list text-primary"></i> Mostrar:
            </label>
            <select id="usuarios-table-rows-per-page" class="form-select">
                <option value="5">5 registros</option>
                <option value="10" selected>10 registros</option>
                <option value="20">20 registros</option>
                <option value="50">50 registros</option>
            </select>
        </div>
    </div>

    <form id="form-borrar-usuarios" action="{{ route('usuarios.destroyMultiple') }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="mb-2">
            Marcar todos
            <input type="checkbox" id="check-todos">
            <button type="submit" class="btn btn-danger btn-sm" id="btn-borrar-seleccionados" disabled>
                <i class="fas fa-trash"></i> Borrar seleccionados
            </button>
        </div>

        <div class="table-responsive">
            <table id="usuarios-table"
                   data-table="usuarios-table"
                   data-server-side="true"
                   data-search-url="{{ route('usuarios') }}"
                   class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th data-type="actions">
                        ✅
                    </th>
                    <th class="sortable" data-type="string" data-col="1">
                        Cliente
                        <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th class="sortable" data-type="string" data-col="2">
                        Teléfono
                        <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th class="sortable" data-type="number" data-col="3">
                        ID Cuenta
                        <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th class="sortable" data-type="string" data-col="4">
                        Usuario Cuenta
                        <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th class="sortable" data-type="string" data-col="5">
                        Perfil
                        <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th class="sortable" data-type="string" data-col="6">
                        Vencimiento
                        <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th class="sortable" data-type="string" data-col="7">
                        Estado
                        <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th class="sortable" data-type="string" data-col="8">
                        Cobro
                        <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    @if (Auth::user()->hasAnyPermission(['usuarios.change', 'ventas.renew', 'usuarios.destroy']))
                        <th data-type="actions">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @include('inventory.usuarios.partials.table-rows', ['usuarios' => $usuarios])
            </tbody>
        </table>
        </div>

        <!-- Información de paginación y controles -->
        <div class="row mt-3 align-items-center">
            <div class="col-md-6 col-12 mb-2 mb-md-0">
                <div id="usuarios-table-row-info" class="text-muted"></div>
            </div>
            <div class="col-md-6 col-12">
                <div id="usuarios-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
            </div>
        </div>
    </form>

    <div id="toast-mensaje"
        style="
    display: none;
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    padding: 12px 16px;
    border-radius: 6px;
    z-index: 9999;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    font-weight: bold;
    ">
        ✅ Mensaje copiado
    </div>

<!-- Modales -->
@include('inventory.usuarios.modals.change')
@include('inventory.usuarios.modals.delete')
@include('inventory.usuarios.modals.mover')
@include('inventory.cuentas.modals.movement-results')
@include('inventory.cuentas.modals.confirm-move-user-otro-servicio')

<x-modal name="confirmar-marcar-cuenta-danada" :show="false" maxWidth="lg">
    <div class="modal-header border-bottom" style="background-color: #b02a37; color: #ffffff;">
        <h5 class="modal-title">
            <i class="fas fa-triangle-exclamation me-2"></i>Confirmar Cuenta Danada
        </h5>
        <button type="button" class="btn-close btn-close-white"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirmar-marcar-cuenta-danada' }))"></button>
    </div>
    <div class="modal-body">
        <div class="alert alert-warning mb-3">
            <i class="fas fa-exclamation-circle me-2"></i>
            Esta accion marcara la cuenta del cliente como danada.
        </div>
        <p class="mb-1">Cliente: <strong id="confirm_danada_nombre_cliente"></strong></p>
        <p class="mb-0">Cuenta: <strong id="confirm_danada_idcue"></strong></p>
        <input type="hidden" id="confirm_danada_iddet" value="">
    </div>
    <div class="modal-footer border-top">
        <button type="button" class="btn btn-secondary"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirmar-marcar-cuenta-danada' }))">
            Cancelar
        </button>
        <button type="button" class="btn btn-danger" onclick="confirmarMarcarCuentaDanada()">
            <i class="fas fa-check me-1"></i>Si, marcar danada
        </button>
    </div>
</x-modal>

<x-modal name="confirmar-borrar-usuarios-seleccionados" :show="false" maxWidth="lg">
    <div class="modal-header border-bottom" style="background-color: #dc3545; color: #ffffff;">
        <h5 class="modal-title">
            <i class="fas fa-trash me-2"></i>Confirmar Borrado Masivo
        </h5>
        <button type="button" class="btn-close btn-close-white"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirmar-borrar-usuarios-seleccionados' }))"></button>
    </div>
    <div class="modal-body">
        <p class="mb-0">Se eliminaran los usuarios seleccionados. Esta accion no se puede deshacer.</p>
    </div>
    <div class="modal-footer border-top">
        <button type="button" class="btn btn-secondary"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirmar-borrar-usuarios-seleccionados' }))">
            Cancelar
        </button>
        <button type="button" class="btn btn-danger" onclick="confirmarBorradoUsuariosSeleccionados()">
            <i class="fas fa-trash me-1"></i>Si, eliminar
        </button>
    </div>
</x-modal>

@if (session('mensaje_renovacion'))
    @php($mensajeRenovacionCliente = session('mensaje_renovacion_cliente', []))
    <x-modal name="mensaje-renovacion-usuarios" :show="false" maxWidth="2xl">
        <div class="modal-header border-bottom">
            <h5 class="modal-title">
                <i class="fas fa-comment-dots me-2"></i>Mensaje de Renovacion
            </h5>
            <button type="button" class="btn-close"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'mensaje-renovacion-usuarios' }))">
            </button>
        </div>

        <div class="modal-body bg-body">
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>
                La renovacion fue registrada correctamente. Copia el mensaje para enviarlo al cliente.
            </div>
            <div class="small text-muted mb-3">
                Cliente: {{ $mensajeRenovacionCliente['cliente'] ?? 'Cliente' }}
                @if (!empty($mensajeRenovacionCliente['telefono']))
                    | Tel: {{ $mensajeRenovacionCliente['telefono'] }}
                @endif
            </div>
            <textarea id="mensaje_renovacion_usuarios_text" class="form-control font-monospace bg-body-secondary text-body border" rows="8" readonly>{{ session('mensaje_renovacion') }}</textarea>
        </div>

        <div class="modal-footer border-top">
            <button type="button" class="btn btn-outline-primary" onclick="copyMensajeRenovacionUsuarios()">
                <i class="fas fa-copy me-1"></i>Copiar Mensaje
            </button>
            <button
                type="button"
                class="btn btn-success"
                onclick="sendMensajeRenovacionUsuarios('verde')"
                @if (empty($mensajeRenovacionCliente['telefono'])) disabled @endif
            >
                <i class="fab fa-whatsapp me-1"></i>WhatsApp verde
            </button>
            <button
                type="button"
                class="btn btn-outline-success"
                onclick="sendMensajeRenovacionUsuarios('alterno')"
                @if (empty($mensajeRenovacionCliente['telefono'])) disabled @endif
            >
                <i class="fab fa-whatsapp me-1"></i>WhatsApp alterno
            </button>
            <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'mensaje-renovacion-usuarios' }))">
                Cerrar
            </button>
        </div>
    </x-modal>
@endif

<x-modal name="enviar-whatsapp-usuario" :show="false" maxWidth="2xl">
    <div class="wa-compose-modal">
        <div class="wa-compose-hero">
            <div class="wa-compose-hero-copy">
                <div class="wa-compose-kicker">
                    <i class="fab fa-whatsapp"></i>
                    Mensajeria directa
                </div>
                <h5 class="wa-compose-title">Enviar WhatsApp al cliente</h5>
                <p class="wa-compose-subtitle">Redacta un mensaje claro y envialo desde el canal correcto sin salir de la tabla.</p>
            </div>
            <div class="wa-compose-channel" id="wa_usuario_channel_badge">
                <span class="wa-compose-channel-dot" id="wa_usuario_channel_dot"></span>
                <span id="wa_usuario_channel_label">WhatsApp verde</span>
            </div>
        </div>

        <div class="wa-compose-body">
            <div class="wa-compose-meta">
                <div class="wa-compose-card">
                    <span class="wa-compose-label">Cliente</span>
                    <p class="wa-compose-value" id="wa_usuario_cliente">Cliente</p>
                </div>
                <div class="wa-compose-card">
                    <span class="wa-compose-label">Telefono</span>
                    <p class="wa-compose-value" id="wa_usuario_telefono">Sin telefono</p>
                </div>
            </div>

            <div class="wa-compose-panel">
                <div class="wa-compose-panel-head">
                    <h6 class="wa-compose-panel-title">Mensaje</h6>
                    <span class="wa-compose-counter" id="wa_usuario_counter">0 caracteres</span>
                </div>
                <textarea
                    id="wa_usuario_mensaje"
                    class="form-control wa-compose-textarea"
                    placeholder="Escribe un mensaje breve, claro y profesional..."
                ></textarea>
                <p class="wa-compose-help">Tip: usa un mensaje corto, orientado a accion y con contexto suficiente para el cliente.</p>
            </div>
        </div>

        <div class="modal-footer border-top px-4 pb-4 pt-0">
            <button
                type="button"
                class="btn btn-light border"
                onclick="cerrarModalWhatsAppUsuario()"
            >
                Cancelar
            </button>
            <button
                type="button"
                class="btn btn-whatsapp-green"
                id="wa_usuario_send_button"
                onclick="confirmarEnvioWhatsAppUsuario()"
            >
                <i class="fab fa-whatsapp me-1"></i>
                <span id="wa_usuario_send_label">Enviar por WhatsApp verde</span>
            </button>
        </div>
    </div>
</x-modal>
@endsection
@section('scripts')
    <!-- jQuery (debe cargarse primero) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 (debe cargarse después de jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Inicializador de searchable-selects -->
    <script src="{{ asset('js/searchable-select.js') }}"></script>

    @parent
    <script>
        const whatsappUsuarioState = {
            idCliente: null,
            cliente: 'Cliente',
            telefono: '',
            channelPreference: 'verde'
        };

        function showUsuariosToast(message, type = 'success') {
            const toast = document.getElementById('toast-mensaje');
            if (!toast) {
                return;
            }

            const visualConfig = {
                success: {
                    backgroundColor: '#d4edda',
                    color: '#155724',
                    borderColor: '#c3e6cb',
                    prefix: '✅ '
                },
                warning: {
                    backgroundColor: '#fff3cd',
                    color: '#856404',
                    borderColor: '#ffeeba',
                    prefix: '⚠️ '
                },
                danger: {
                    backgroundColor: '#f8d7da',
                    color: '#721c24',
                    borderColor: '#f5c6cb',
                    prefix: '❌ '
                }
            };

            const style = visualConfig[type] || visualConfig.success;
            toast.style.backgroundColor = style.backgroundColor;
            toast.style.color = style.color;
            toast.style.border = `1px solid ${style.borderColor}`;
            toast.textContent = `${style.prefix}${message}`;
            toast.style.display = 'block';
            toast.style.opacity = 1;

            setTimeout(() => {
                toast.style.transition = 'opacity 0.5s ease';
                toast.style.opacity = 0;
                setTimeout(() => toast.style.display = 'none', 500);
            }, 2200);
        }

        function actualizarContadorWhatsAppUsuario() {
            const textarea = document.getElementById('wa_usuario_mensaje');
            const counter = document.getElementById('wa_usuario_counter');

            if (!textarea || !counter) {
                return;
            }

            const total = textarea.value.trim().length;
            counter.textContent = `${total} ${total === 1 ? 'caracter' : 'caracteres'}`;
        }

        function cerrarModalWhatsAppUsuario() {
            const textarea = document.getElementById('wa_usuario_mensaje');
            const sendButton = document.getElementById('wa_usuario_send_button');

            if (textarea) {
                textarea.value = '';
            }

            if (sendButton) {
                sendButton.disabled = false;
            }

            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'enviar-whatsapp-usuario' }));
            actualizarContadorWhatsAppUsuario();
        }

        function enviarWhatsAppUsuario({ idCliente = null, cliente = 'Cliente', telefono = '', channelPreference = 'verde' }) {
            if (!telefono) {
                showUsuariosToast('El usuario no tiene un teléfono válido.', 'warning');
                return;
            }

            const channelLabel = channelPreference === 'azul' ? 'WhatsApp azul' : 'WhatsApp verde';
            const channelBadge = document.getElementById('wa_usuario_channel_badge');
            const channelDot = document.getElementById('wa_usuario_channel_dot');
            const channelText = document.getElementById('wa_usuario_channel_label');
            const clienteText = document.getElementById('wa_usuario_cliente');
            const telefonoText = document.getElementById('wa_usuario_telefono');
            const textarea = document.getElementById('wa_usuario_mensaje');
            const sendButton = document.getElementById('wa_usuario_send_button');
            const sendLabel = document.getElementById('wa_usuario_send_label');

            whatsappUsuarioState.idCliente = idCliente;
            whatsappUsuarioState.cliente = cliente;
            whatsappUsuarioState.telefono = telefono;
            whatsappUsuarioState.channelPreference = channelPreference;

            if (channelText) {
                channelText.textContent = channelLabel;
            }

            if (clienteText) {
                clienteText.textContent = cliente;
            }

            if (telefonoText) {
                telefonoText.textContent = telefono;
            }

            if (textarea) {
                textarea.value = '';
            }

            if (sendButton) {
                sendButton.disabled = false;
                sendButton.classList.remove('btn-whatsapp-green', 'btn-whatsapp-blue');
                sendButton.classList.add(channelPreference === 'azul' ? 'btn-whatsapp-blue' : 'btn-whatsapp-green');
            }

            if (sendLabel) {
                sendLabel.textContent = `Enviar por ${channelLabel}`;
            }

            if (channelBadge) {
                channelBadge.style.background = channelPreference === 'azul'
                    ? 'rgba(27, 124, 255, 0.14)'
                    : 'rgba(37, 211, 102, 0.14)';
            }

            if (channelDot) {
                channelDot.classList.toggle('is-blue', channelPreference === 'azul');
            }

            actualizarContadorWhatsAppUsuario();
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'enviar-whatsapp-usuario' }));

            setTimeout(() => {
                textarea?.focus();
            }, 120);
        }

        async function confirmarEnvioWhatsAppUsuario() {
            const channelLabel = whatsappUsuarioState.channelPreference === 'azul' ? 'WhatsApp azul' : 'WhatsApp verde';
            const textarea = document.getElementById('wa_usuario_mensaje');
            const sendButton = document.getElementById('wa_usuario_send_button');
            const sendLabel = document.getElementById('wa_usuario_send_label');
            const contenido = textarea?.value.trim() || '';

            if (contenido.length < 3) {
                showUsuariosToast('El mensaje debe tener al menos 3 caracteres.', 'warning');
                return;
            }

            if (sendButton) {
                sendButton.disabled = true;
            }

            if (sendLabel) {
                sendLabel.textContent = 'Enviando...';
            }

            try {
                const response = await fetch('{{ route("usuarios.enviarMensajeCliente") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_cliente: whatsappUsuarioState.idCliente,
                        cliente: whatsappUsuarioState.cliente,
                        telefono: whatsappUsuarioState.telefono,
                        mensaje: contenido,
                        channel_preference: whatsappUsuarioState.channelPreference
                    })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'No se pudo enviar el mensaje.');
                }

                cerrarModalWhatsAppUsuario();
                showUsuariosToast(data.message || `Mensaje enviado por ${channelLabel}.`, 'success');
            } catch (error) {
                showUsuariosToast(error.message || 'No se pudo enviar el mensaje.', 'warning');
            } finally {
                if (sendButton) {
                    sendButton.disabled = false;
                }

                if (sendLabel) {
                    sendLabel.textContent = `Enviar por ${channelLabel}`;
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const textarea = document.getElementById('wa_usuario_mensaje');

            if (textarea) {
                textarea.addEventListener('input', actualizarContadorWhatsAppUsuario);
            }
        });

        // ========================================================================
        // FUNCIÓN DE MODAL - CAMBIAR USUARIO
        // ========================================================================
        function cambiarUsuario(iddet, nombrecli, idven, idcue, perfil, fechaVencimiento) {
            console.log('🔷 Abriendo modal de cambiar usuario:', iddet);

            document.getElementById('changeUsuarioModalTitle').textContent = 'Actualizar Usuario - ' + nombrecli;
            document.getElementById('change_nombrecli').value = nombrecli;
            document.getElementById('change_idven').value = idven;
            document.getElementById('change_perfil').value = perfil;
            document.getElementById('change_fecha_vencimiento').value = fechaVencimiento;

            const form = document.getElementById('changeUsuarioForm');
            form.action = "{{ route('usuarios.update', '') }}/" + iddet;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'cambiar-usuario' }));

            // Inicializar Select2 después de abrir el modal
            setTimeout(function() {
                const $select = $('#change_idcue');

                // Destruir instancia previa si existe
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                // Inicializar Select2
                $select.select2({
                    theme: 'bootstrap-5',
                    placeholder: '-- Selecciona una Cuenta --',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('.modal-overlay:visible .modal-content'),
                    language: {
                        noResults: function() {
                            return "No se encontraron resultados";
                        }
                    }
                });

                // Setear el valor seleccionado
                $select.val(idcue).trigger('change');

                // Asegurar que el dropdown se posicione correctamente
                $select.on('select2:open', function() {
                    $('.select2-dropdown').css('z-index', 99999);
                });
            }, 400);
        }

        // Event listener para botones de cambiar usuario
        document.addEventListener('DOMContentLoaded', function() {
            const changeForm = document.getElementById('changeUsuarioForm');

            if (changeForm && changeForm.dataset.ajaxBound !== 'true') {
                changeForm.dataset.ajaxBound = 'true';
                changeForm.addEventListener('submit', function(event) {
                    event.preventDefault();

                    fetch(changeForm.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: new FormData(changeForm)
                    })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'No se pudo actualizar el usuario.');
                        }
                        return data;
                    })
                    .then(data => {
                        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'cambiar-usuario' }));
                        openMovementResultsModal(data);
                    })
                    .catch(error => {
                        if (typeof showMovementToast === 'function') {
                            showMovementToast(error.message || 'No se pudo actualizar el usuario.', 'warning');
                        } else {
                            showUsuariosToast(error.message || 'No se pudo actualizar el usuario.', 'danger');
                        }
                    });
                });
            }

            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-cambiar-usuario')) {
                    const btn = e.target.closest('.btn-cambiar-usuario');
                    const iddet = btn.dataset.iddet;
                    const nombrecli = btn.dataset.nombrecli;
                    const idven = btn.dataset.idven;
                    const idcue = btn.dataset.idcue;
                    const perfil = btn.dataset.perfil;
                    const fecha = btn.dataset.fecha;
                    cambiarUsuario(iddet, nombrecli, idven, idcue, perfil, fecha);
                }
            });
        });

        function abrirModalMoverOtroServicio(event, button) {
            event.stopPropagation();

            const iddet = button.getAttribute('data-iddet');
            const nombre = button.getAttribute('data-nombre');
            const servicio = (button.getAttribute('data-servicio') || '').toUpperCase();

            document.getElementById('confirm_move_otro_servicio_user_id').value = iddet;
            document.getElementById('confirm_move_otro_servicio_user_name').textContent = nombre;
            document.getElementById('confirm_move_otro_servicio_actual').textContent = servicio;
            document.getElementById('confirm_move_otro_servicio_form').action =
                "{{ route('usuarios.moverUsuarioOtroServicio', ':id') }}".replace(':id', iddet);

            const selectServicio = document.getElementById('idser_destino');
            if (selectServicio) {
                Array.from(selectServicio.options).forEach(option => {
                    if (option.value === servicio) {
                        option.disabled = true;
                        option.textContent = option.value + ' (Servicio actual)';
                    } else {
                        option.disabled = false;
                        if (option.value !== '') {
                            option.textContent = option.value;
                        }
                    }
                });
                selectServicio.value = '';
            }

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-move-user-otro-servicio' }));
        }

        function abrirModalMarcarCuentaDanada(event, iddet, nombreCliente, idcue) {
            event.stopPropagation();
            document.getElementById('confirm_danada_iddet').value = iddet;
            document.getElementById('confirm_danada_nombre_cliente').textContent = nombreCliente;
            document.getElementById('confirm_danada_idcue').textContent = idcue;
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirmar-marcar-cuenta-danada' }));
        }

        function confirmarMarcarCuentaDanada() {
            const iddet = document.getElementById('confirm_danada_iddet').value;
            const url = "{{ route('usuarios.marcarCuentaDanada', ':id') }}".replace(':id', iddet);

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'No se pudo marcar la cuenta como danada.');
                }

                const row = document.querySelector(`tr[data-iddet="${iddet}"]`);
                if (row) {
                    const flagsContainer = row.querySelector('.cuenta-flags');
                    if (flagsContainer && !flagsContainer.querySelector('.cuenta-flag-danada')) {
                        flagsContainer.insertAdjacentHTML('afterbegin', '<span class="badge bg-danger cuenta-flag-danada">Dañada</span>');
                    }

                    const botonMarcarDanada = row.querySelector('.btn-marcar-cuenta-danada');
                    if (botonMarcarDanada) {
                        botonMarcarDanada.disabled = true;
                        botonMarcarDanada.title = 'La cuenta ya está marcada como dañada';
                        botonMarcarDanada.classList.remove('btn-outline-danger');
                        botonMarcarDanada.classList.add('btn-danger');
                    }
                }

                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirmar-marcar-cuenta-danada' }));
                showUsuariosToast(data.message || 'Cuenta marcada como danada.', 'success');
            })
            .catch(error => {
                showUsuariosToast(error.message || 'No se pudo marcar la cuenta como danada.', 'danger');
            });
        }

        async function confirmarBorradoUsuariosSeleccionados() {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirmar-borrar-usuarios-seleccionados' }));
            const form = document.getElementById('form-borrar-usuarios');
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.message || 'Error al eliminar usuarios');

                (data.ids || []).forEach(id => {
                    const row = document.querySelector(`tr[data-iddet="${id}"]`);
                    if (row) row.remove();
                });

                // Deselect checkboxes
                document.querySelectorAll('.check-usuario').forEach(chk => { chk.checked = false; });
                const checkTodos = document.getElementById('check-todos');
                if (checkTodos) checkTodos.checked = false;
                const btnBorrar = document.getElementById('btn-borrar-seleccionados');
                if (btnBorrar) btnBorrar.disabled = true;

                showUsuariosToast(data.message || 'Usuarios eliminados correctamente.', 'success');
            } catch (error) {
                showUsuariosToast(error.message || 'No se pudieron eliminar los usuarios.', 'danger');
            }
        }

        // ========================================================================
        // FUNCIÓN DE MODAL - MOVER USUARIO
        // ========================================================================
        function abrirModalMoverUsuario(iddet) {
            console.log('🔷 Abriendo modal de mover usuario:', iddet);

            const form = document.getElementById('moverUsuarioForm');
            form.action = "{{ url('admin/usuarios') }}/" + iddet + "/mover";

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'mover-usuario' }));
        }

        document.addEventListener('DOMContentLoaded', function() {
            const moveForm = document.getElementById('moverUsuarioForm');

            if (moveForm) {
                moveForm.addEventListener('submit', function(event) {
                    event.preventDefault();

                    fetch(moveForm.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: new FormData(moveForm)
                    })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'No se pudo mover el usuario');
                        }
                        return data;
                    })
                    .then(data => {
                        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'mover-usuario' }));
                        openMovementResultsModal(data);
                    })
                    .catch(error => {
                        showMovementToast(error.message || 'No se pudo mover el usuario', 'warning');
                    });
                });
            }
        });

        // ========================================================================
        // FUNCIÓN DE MODAL - ELIMINAR USUARIO
        // ========================================================================
        function confirmarEliminarUsuario(iddet) {
            const form = document.getElementById('deleteUsuarioForm');
            form.dataset.iddet = iddet;
            form.action = "{{ route('usuarios.destroy', '') }}/" + iddet;
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirmar-eliminar-usuario' }));
        }
        // Interceptar el form de eliminar usuario individual con AJAX
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForm = document.getElementById('deleteUsuarioForm');
            if (deleteForm) {
                deleteForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const iddet = deleteForm.dataset.iddet;
                    const formData = new FormData(deleteForm);

                    try {
                        const response = await fetch(deleteForm.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });
                        const data = await response.json();
                        if (!response.ok || !data.success) throw new Error(data.message || 'Error al eliminar usuario');

                        const row = document.querySelector(`tr[data-iddet="${iddet}"]`);
                        if (row) row.remove();

                        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirmar-eliminar-usuario' }));
                        showUsuariosToast(data.message || 'Usuario eliminado con éxito.', 'success');
                    } catch (error) {
                        showUsuariosToast(error.message || 'No se pudo eliminar el usuario.', 'danger');
                    }
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const checkTodos = document.getElementById('check-todos');
            const btnBorrar = document.getElementById('btn-borrar-seleccionados');
            const form = document.getElementById('form-borrar-usuarios');

            function getCheckUsuarios() {
                // Solo checkboxes habilitados (los visibles)
                return document.querySelectorAll('.check-usuario:not([disabled])');
            }

            function actualizarBoton() {
                const checkUsuarios = getCheckUsuarios();
                btnBorrar.disabled = !Array.from(checkUsuarios).some(chk => chk.checked);
            }

            // Seleccionar/deseleccionar todos
            if (checkTodos) {
                checkTodos.addEventListener('change', function() {
                    const checkUsuarios = getCheckUsuarios();
                    checkUsuarios.forEach(chk => {
                        chk.checked = checkTodos.checked;
                    });
                    actualizarBoton();
                });
            }

            // Si se desmarca algún checkbox individual, desmarca el "check-todos"
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('check-usuario')) {
                    actualizarBoton();
                    const checkUsuarios = getCheckUsuarios();
                    // Si todos están marcados, marca el check-todos; si no, desmárcalo
                    checkTodos.checked = Array.from(checkUsuarios).every(chk => chk.checked);
                }
            });

            // Confirmación al borrar
            form.addEventListener('submit', function(e) {
                if (!Array.from(getCheckUsuarios()).some(chk => chk.checked)) {
                    e.preventDefault();
                    showUsuariosToast('Debes seleccionar al menos un usuario para borrar.', 'warning');
                    return;
                }

                e.preventDefault();
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirmar-borrar-usuarios-seleccionados' }));
            });

            // Inicializar estado del botón y del check-todos al cargar
            actualizarBoton();
            // Si todos los checkboxes están marcados al cargar, marca el check-todos
            const checkUsuarios = getCheckUsuarios();
            checkTodos.checked = checkUsuarios.length > 0 && Array.from(checkUsuarios).every(chk => chk.checked);
        });
    </script>
    <script>
        function copiarMensaje(servicio, cuenta, contrasenaCuenta, perfilNumero, fecha, estado, pinper = '') {
            const servicioTexto = (servicio || '').toString().trim();
            const esSpotify = servicioTexto.toUpperCase().includes('SPOTIFY');
            let mensaje = `Hola, tu cuenta de *${servicio}* - *${cuenta}* vence el *${fecha}* y su estado es *${estado}*`;

            if (esSpotify) {
                if (Number(perfilNumero) === 1) {
                    mensaje = `Hola, tu cuenta de *${servicio}* vence el *${fecha}* y su estado es *${estado}*\n👤 Usuario: *${cuenta}*\n🔑 Clave: *${contrasenaCuenta}*`;
                } else {
                const pinTexto = (pinper || '').toString().trim();

                if (pinTexto) {
                    let usuarioSpotify = pinTexto;
                    let claveSpotify = pinTexto;

                    if (pinTexto.includes('|')) {
                        const credenciales = pinTexto.split('|');
                        usuarioSpotify = (credenciales[0] || '').trim();
                        claveSpotify = (credenciales[1] || '').trim();
                    }

                    mensaje = `Hola, tu cuenta de *${servicio}* vence el *${fecha}* y su estado es *${estado}*\n👤 Usuario: *${usuarioSpotify}*\n🔑 Clave: *${claveSpotify}*`;
                } else {
                    mensaje = `Hola, tu cuenta de *${servicio}* vence el *${fecha}* y su estado es *${estado}*\n⚠️ No hay pinper configurado para este perfil.`;
                }
                }
            }

            navigator.clipboard.writeText(mensaje).then(() => {
                const toast = document.getElementById('toast-mensaje');
                toast.textContent = '✅ Mensaje copiado';
                toast.style.display = 'block';
                toast.style.opacity = 1;

                setTimeout(() => {
                    toast.style.transition = 'opacity 0.5s ease';
                    toast.style.opacity = 0;
                    setTimeout(() => toast.style.display = 'none', 500);
                }, 2000);
            });
        }

        function copiarTelefono(telefono) {
            navigator.clipboard.writeText(telefono).then(() => {
                const toast = document.getElementById('toast-mensaje');
                toast.textContent = '✅ Teléfono copiado';
                toast.style.display = 'block';
                toast.style.opacity = 1;

                setTimeout(() => {
                    toast.style.transition = 'opacity 0.5s ease';
                    toast.style.opacity = 0;
                    setTimeout(() => toast.style.display = 'none', 500);
                }, 2000);
            });
        }

        // ========================================================================
        // FUNCIONALIDAD: CLICK EN FILA PARA COPIAR MENSAJE
        // ========================================================================
        document.addEventListener('DOMContentLoaded', function() {
            // Manejar click en filas para copiar mensaje
            document.addEventListener('click', function(e) {
                if (e.target.closest('button, a, input, label, .toggle-switch')) {
                    return;
                }

                const row = e.target.closest('tr.clickable-row');
                if (row) {
                    const servicio = row.dataset.servicio;
                    const cuenta = row.dataset.cuenta;
                    const contrasenaCuenta = row.dataset.contrasena || '';
                    const perfilNumero = row.dataset.perfil || '';
                    const pinper = row.dataset.pinper || '';
                    const fecha = row.dataset.fecha;
                    const estado = row.dataset.estado;
                    copiarMensaje(servicio, cuenta, contrasenaCuenta, perfilNumero, fecha, estado, pinper);
                }
            });

            const formMoverOtroServicio = document.getElementById('confirm_move_otro_servicio_form');
            if (formMoverOtroServicio && formMoverOtroServicio.dataset.ajaxBound !== 'true') {
                formMoverOtroServicio.dataset.ajaxBound = 'true';
                formMoverOtroServicio.addEventListener('submit', function(event) {
                    event.preventDefault();

                    fetch(formMoverOtroServicio.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: new FormData(formMoverOtroServicio)
                    })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'No se pudo mover el usuario a otro servicio.');
                        }
                        return data;
                    })
                    .then(data => {
                        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirm-move-user-otro-servicio' }));
                        openMovementResultsModal(data);
                    })
                    .catch(error => {
                        if (typeof showMovementToast === 'function') {
                            showMovementToast(error.message || 'No se pudo mover el usuario a otro servicio.', 'danger');
                        } else {
                            showUsuariosToast(error.message || 'No se pudo mover el usuario a otro servicio.', 'danger');
                        }
                    });
                });
            }

            // ========================================================================
            // FUNCIONALIDAD: TOGGLE ESTADO DE COBRO CON AJAX
            // ========================================================================
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('toggle-estado-cobro')) {
                    const toggle = e.target;
                    const iddet = toggle.dataset.iddet;
                    const nuevoEstado = toggle.checked ? 'COBRADO' : 'PENDIENTE';
                    const slider = toggle.nextElementSibling;

                    // Buscar el elemento small que muestra el estado (puede estar dentro o fuera del label)
                    const toggleLabel = toggle.closest('label');
                    const labelEstado = toggleLabel.parentElement.querySelector('small');

                    // Deshabilitar el toggle mientras se procesa
                    toggle.disabled = true;
                    slider.classList.add('disabled');

                    // Construir la URL con el ID dinámico
                    const url = "{{ route('usuarios.actualizarEstadoCobro', ['id' => '__ID__']) }}".replace('__ID__', iddet);

                    // Hacer petición AJAX
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            estado: nuevoEstado
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Actualizar el label del estado si existe
                            if (labelEstado) {
                                labelEstado.textContent = nuevoEstado;
                                labelEstado.className = `d-block mt-1 text-${nuevoEstado === 'COBRADO' ? 'success' : 'warning'}`;
                            }

                            // Mostrar toast de éxito
                            const toast = document.getElementById('toast-mensaje');
                            toast.textContent = `✅ Estado actualizado a ${nuevoEstado}`;
                            toast.style.display = 'block';
                            toast.style.opacity = 1;

                            setTimeout(() => {
                                toast.style.transition = 'opacity 0.5s ease';
                                toast.style.opacity = 0;
                                setTimeout(() => toast.style.display = 'none', 500);
                            }, 2000);
                        } else {
                            // Revertir el toggle si hay error
                            toggle.checked = !toggle.checked;
                            showUsuariosToast('Error al actualizar el estado: ' + (data.message || 'Error desconocido'), 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Revertir el toggle si hay error
                        toggle.checked = !toggle.checked;
                        showUsuariosToast('Error al actualizar el estado. Por favor, intenta de nuevo.', 'danger');
                    })
                    .finally(() => {
                        // Re-habilitar el toggle
                        toggle.disabled = false;
                        slider.classList.remove('disabled');
                    });
                }
            });
        });
    </script>

    {{-- Enhanced Table v2 --}}
    <script src="{{ asset('js/enhanced-table-v2.js') }}?v={{ filemtime(public_path('js/enhanced-table-v2.js')) }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('mensaje_renovacion'))
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'mensaje-renovacion-usuarios' }));
            @endif
        });

        function copyMensajeRenovacionUsuarios() {
            const message = document.getElementById('mensaje_renovacion_usuarios_text')?.value || '';
            if (!message) {
                return;
            }

            navigator.clipboard.writeText(message)
                .then(() => {
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'mensaje-renovacion-usuarios' }));
                    const toast = document.getElementById('toast-mensaje');
                    toast.textContent = '✅ Mensaje de renovacion copiado';
                    toast.style.display = 'block';
                    toast.style.opacity = 1;

                    setTimeout(() => {
                        toast.style.transition = 'opacity 0.5s ease';
                        toast.style.opacity = 0;
                        setTimeout(() => toast.style.display = 'none', 500);
                    }, 2000);
                })
                .catch(() => {
                    const toast = document.getElementById('toast-mensaje');
                    toast.textContent = '⚠️ No se pudo copiar el mensaje';
                    toast.style.display = 'block';
                    toast.style.opacity = 1;

                    setTimeout(() => {
                        toast.style.transition = 'opacity 0.5s ease';
                        toast.style.opacity = 0;
                        setTimeout(() => toast.style.display = 'none', 500);
                    }, 2000);
                });
        }

        async function sendMensajeRenovacionUsuarios(channelPreference = 'verde') {
            const message = document.getElementById('mensaje_renovacion_usuarios_text')?.value || '';
            const cliente = @json($mensajeRenovacionCliente['cliente'] ?? 'Cliente');
            const telefono = @json($mensajeRenovacionCliente['telefono'] ?? null);
            const idCliente = @json($mensajeRenovacionCliente['idcli'] ?? null);

            if (!message || !telefono) {
                showUsuariosToast('No hay teléfono o mensaje para enviar.', 'warning');
                return;
            }

            try {
                const response = await fetch('{{ route("usuarios.enviarMensajeCliente") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_cliente: idCliente,
                        cliente: cliente,
                        telefono: telefono,
                        mensaje: message,
                        channel_preference: channelPreference
                    })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'No se pudo enviar el mensaje');
                }

                showUsuariosToast(data.message || 'Mensaje enviado al cliente.', 'success');
            } catch (error) {
                showUsuariosToast(error.message || 'No se pudo enviar el mensaje.', 'warning');
            }
        }
    </script>
@endsection
