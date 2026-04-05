@extends('layouts.static')

@section('title', 'Crear Venta')

@section('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Select2 Dark Mode -->
    <link href="{{ asset('css/select2-dark-mode.css') }}" rel="stylesheet" />
@endsection

@section('h1', 'Crear Venta')
@section('breadcrumb')
    <a href="{{ route('ventas') }}">Ventas</a>
@endsection
@section('breadcrumb2')
    Registrar Venta
@endsection
@section('introduccion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    Aquí puedes agregar una nueva venta.
@endsection
@section('content')
    <div class="container">
        <!-- Alerta Informativa -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>¡Importante!</strong> Los cambios realizados en esta página NO se guardarán hasta que presiones el botón
            <strong>"Registrar Venta"</strong> al final del formulario.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <h2>Crear Nueva Venta</h2>
        <form id="form-venta" method="POST" action="{{ route('ventas.store') }}">
            @csrf
            <div class="form-group mb-3">
                <label for="idcli">Seleccionar Cliente</label>
                <select name="idcli" id="idcli" class="form-control searchable-select" required
                        data-placeholder="Buscar cliente por nombre o teléfono...">
                    <option value="">-- Selecciona un Cliente --</option>
                    @foreach ($clientes as $cliente)
                        <option value="{{ $cliente->idcli }}" {{ request('idcli') == $cliente->idcli ? 'selected' : '' }}>
                            {{ $cliente->nombrecli }} - {{ $cliente->telefonocli }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="btn btn-primary mb-3"
                onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'createClienteModal' }))">
                <i class="fas fa-user-plus me-1"></i> Nuevo Cliente
            </button>

            <!-- Checkbox: ¿Se pagó? -->
            <div class="form-group mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="se_pago" id="se_pago_create" value="1" checked>
                    <label class="form-check-label" for="se_pago_create">
                        ¿Se pagó?
                    </label>
                </div>
            </div>

            <!-- Campo Banco (visible solo si se marcó como pagado) -->
            <div class="form-group mb-3" id="banco_field_create">
                <label for="banco_id">Banco <span class="text-danger">*</span></label>
                <select name="banco_id" id="banco_id" class="form-control searchable-select"
                        data-placeholder="Seleccione un banco...">
                    <option value="">-- Selecciona un Banco --</option>
                    @foreach ($bancos as $banco)
                        <option value="{{ $banco->idban }}">
                            {{ $banco->nombreban }} ({{ ucfirst($banco->tipoban) }}) - ${{ number_format($banco->monto, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mt-4">
                <h4>Detalles de Venta</h4>
                <button type="button" class="btn btn-success"
                    onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'agregar-detalle-modal' }))">
                    <i class="fas fa-plus-circle me-1"></i> Agregar Detalle
                </button>

                <table class="table table-bordered mt-3" id="detalles-venta">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Perfil</th>
                            <th>Descripción</th>
                            <th>Fecha de Vencimiento</th>
                            <th>Monto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-detalles">
                        <!-- Los detalles se agregarán aquí mediante JavaScript -->
                    </tbody>
                </table>

                <div class="text-end">
                    <strong>Total Venta: $<span id="total-venta">0.00</span></strong>
                </div>
            </div>
            <!-- Campo oculto para enviar los detalles de la venta -->
            <input type="hidden" name="detalles_venta" id="detalles_venta">
            {{-- Campo oculto para enviar la información del empleado que registró la venta (CON LOGIN) --}}
            <input type="hidden" name="idemp" id="idemp" value={{ Auth::user()->idemp }}>
            <button type="submit" class="btn btn-primary mt-4" id="registrar-venta">Registrar Venta</button>
        </form>
    </div>
    <br>

    @include('sales.clientes.modals.create')
    @include('shared.modals.venta-agregar-detalle')
    @include('shared.modals.venta-editar-detalle')

    @if (session('mensaje_entrega'))
        @php($mensajeEntregaCliente = session('mensaje_entrega_cliente', []))
        <x-modal name="mensaje-entrega-venta" :show="false" maxWidth="2xl">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">
                    <i class="fas fa-comment-dots me-2"></i>Mensaje de Entrega
                </h5>
                <button type="button" class="btn-close"
                    onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'mensaje-entrega-venta' }))">
                </button>
            </div>

            <div class="modal-body bg-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    La venta fue registrada correctamente. Copia el mensaje para enviarlo al cliente.
                </div>
                <div class="small text-muted mb-3">
                    Cliente: {{ $mensajeEntregaCliente['cliente'] ?? 'Cliente' }}
                    @if (!empty($mensajeEntregaCliente['telefono']))
                        | Tel: {{ $mensajeEntregaCliente['telefono'] }}
                    @endif
                </div>
                <textarea id="mensaje_entrega_venta_text" class="form-control font-monospace bg-body-secondary text-body border" rows="10" readonly>{{ session('mensaje_entrega') }}</textarea>
            </div>

            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-primary" onclick="copyMensajeEntregaVenta()">
                    <i class="fas fa-copy me-1"></i>Copiar Mensaje
                </button>
                <button
                    type="button"
                    class="btn btn-success"
                    onclick="sendMensajeEntregaVenta()"
                    @if (empty($mensajeEntregaCliente['telefono'])) disabled @endif
                >
                    <i class="fab fa-whatsapp me-1"></i>Enviar WhatsApp
                </button>
                <button type="button" class="btn btn-secondary"
                    onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'mensaje-entrega-venta' }))">
                    Cerrar
                </button>
            </div>
        </x-modal>
    @endif
@endsection
@section('pie')
    <p>¿No deseas agregar una cuenta al stock? Vuelve a la página de listado:</p>
    <a href="{{ route('ventas') }}" class="btn btn-secondary">Volver a Ventas</a>
@endsection

@section('scripts')
    <!-- jQuery (debe cargarse primero) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 (debe cargarse después de jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Inicializador de searchable-selects -->
    <script src="{{asset('js/searchable-select.js')}}"></script>

    <!-- Scripts específicos de la vista -->
    <script src="{{asset('js/ventasClienteHelper.js')}}"></script>
    <script src="{{asset('js/createVenta.js')}}"></script>

    <!-- Toggle del campo Banco según checkbox "¿Se pagó?" -->
    <script>
        function mostrarConfirmacionCopiado(tipo = 'success', mensaje = 'Mensaje copiado al portapapeles') {
            const existing = document.getElementById('copy-confirm-toast');
            if (existing) {
                existing.remove();
            }

            const toast = document.createElement('div');
            toast.id = 'copy-confirm-toast';
            toast.className = `alert alert-${tipo} shadow-sm position-fixed top-0 end-0 m-3`;
            toast.style.zIndex = '1080';
            toast.style.minWidth = '260px';
            toast.innerHTML = `<i class="fas ${tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2"></i>${mensaje}`;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('fade');
                toast.style.opacity = '0';
            }, 1400);

            setTimeout(() => {
                toast.remove();
            }, 1800);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const sePagoCheckboxCreate = document.getElementById('se_pago_create');
            const bancoFieldCreate = document.getElementById('banco_field_create');
            const bancoSelectCreate = document.getElementById('banco_id');

            function toggleBancoFieldCreate() {
                if (sePagoCheckboxCreate && sePagoCheckboxCreate.checked) {
                    bancoFieldCreate.style.display = 'block';
                    bancoSelectCreate.required = true;
                } else {
                    bancoFieldCreate.style.display = 'none';
                    bancoSelectCreate.required = false;
                    bancoSelectCreate.value = '';
                }
            }

            if (sePagoCheckboxCreate) {
                sePagoCheckboxCreate.addEventListener('change', toggleBancoFieldCreate);
                toggleBancoFieldCreate(); // Ejecutar al cargar
            }

            @if (session('mensaje_entrega'))
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'mensaje-entrega-venta' }));
            @endif
        });

        function copyMensajeEntregaVenta() {
            const message = document.getElementById('mensaje_entrega_venta_text')?.value || '';

            if (!message) {
                return;
            }

            navigator.clipboard.writeText(message)
                .then(() => {
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'mensaje-entrega-venta' }));
                    mostrarConfirmacionCopiado();
                })
                .catch(() => {
                    mostrarConfirmacionCopiado('warning', 'No se pudo copiar el mensaje');
                });
        }

        async function sendMensajeEntregaVenta() {
            const message = document.getElementById('mensaje_entrega_venta_text')?.value || '';
            const cliente = @json($mensajeEntregaCliente['cliente'] ?? 'Cliente');
            const telefono = @json($mensajeEntregaCliente['telefono'] ?? null);
            const idCliente = @json($mensajeEntregaCliente['idcli'] ?? null);

            if (!message || !telefono) {
                mostrarConfirmacionCopiado('warning', 'No hay teléfono o mensaje para enviar');
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
                        mensaje: message
                    })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'No se pudo enviar el mensaje');
                }

                mostrarConfirmacionCopiado('success', data.message || 'Mensaje enviado al cliente');
            } catch (error) {
                mostrarConfirmacionCopiado('warning', error.message || 'No se pudo enviar el mensaje');
            }
        }
    </script>
@endsection
