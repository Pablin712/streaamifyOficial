@extends('layouts.table')

@section('title', 'Cuentas')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <!-- Select2 v4.1.0-rc.0 con Bootstrap 5 theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <!-- Select2 Dark Mode -->
    <link rel="stylesheet" href="{{ asset('css/select2-dark-mode.css') }}">
    <style>
        /* Personalizando el fondo oscuro de las filas de la tabla a morado */
        .table-dark {
            background-color: #800080 !important;
            /* Color morado */
            color: white !important;
        }

        /* Personalizando el badge bg-dark a morado */
        .badge.bg-dark {
            background-color: #800080 !important;
            /* Color morado */
            color: white !important;
        }

        .badge.bg-dark:hover {
            background-color: #6a006a !important;
        }

        .btn-xs {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1;
            border-radius: 0.2rem;
        }

        /* Animación para cards */
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2) !important;
        }

        /* Animación para todos los botones de la sección btncrear */
        #btncrear .btn {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        #btncrear .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        #btncrear .btn:active {
            transform: translateY(0);
        }

        /* Efecto ripple para botones */
        #btncrear .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        #btncrear .btn:active::after {
            width: 300px;
            height: 300px;
        }

        /* ===================================
           ESTILOS PERSONALIZADOS PARA MODALES
           =================================== */

        /* Mejorar legibilidad del contenido del modal */
        .modal-body {
            background-color: #ffffff;
            color: #212529;
        }

        .modal-body .form-label {
            color: #495057;
            font-weight: 600;
        }

        .modal-body .form-control,
        .modal-body .form-select {
            background-color: #ffffff;
            color: #212529;
            border: 1px solid #ced4da;
        }

        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            background-color: #ffffff;
            color: #212529;
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Alerts en modales */
        .modal-body .alert {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }

        .modal-body .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }

        .modal-body .alert-warning {
            background-color: #fff3cd;
            border-color: #ffecb5;
            color: #664d03;
        }

        .modal-body .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c2c7;
            color: #842029;
        }

        /* Card dentro del modal */
        .modal-body .card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .modal-body .card-body {
            color: #212529;
        }

        .modal-body .card-title {
            color: #dc3545;
        }

        /* Columnas clickeables para copiar */
        .clickable-copy {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .clickable-copy:hover {
            background-color: rgba(13, 110, 253, 0.1);
        }

        /* Toast de notificación */
        .toast-success, .toast-error {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-weight: 600;
            font-size: 14px;
            animation: slideIn 0.3s ease-out;
        }

        .toast-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .toast-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .modal-body .fw-semibold {
            color: #495057;
        }

        .modal-body .text-muted {
            color: #6c757d !important;
        }

        /* Texto informativo en modales */
        .modal-body .text-primary {
            color: #0d6efd !important;
            font-weight: 600;
        }

        /* Span de ID en modal edit */
        #edit_idcue_display {
            background-color: #e7f1ff;
            padding: 2px 8px;
            border-radius: 4px;
        }

        /* Input groups en modales */
        .modal-body .input-group .btn-outline-secondary {
            background-color: #ffffff;
            color: #6c757d;
            border-color: #ced4da;
        }

        .modal-body .input-group .btn-outline-secondary:hover {
            background-color: #e9ecef;
            color: #495057;
        }

        /* Small text en modales */
        .modal-body small {
            color: #6c757d;
        }

        /* Headers de modal - asegurar buen contraste */
        .modal-header {
            border-bottom: 1px solid #dee2e6;
        }

        .modal-header .modal-title {
            font-weight: 600;
            font-size: 1.25rem;
        }

        /* Footer de modal */
        .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        /* Badges en modal */
        .modal-body .badge {
            font-weight: 600;
            padding: 0.35em 0.65em;
        }

        /* Rows en información del modal */
        .modal-body .row {
            margin-bottom: 0.5rem;
        }

        .modal-body .row:last-child {
            margin-bottom: 0;
        }

        /* H6 en modales */
        .modal-body h6 {
            color: #495057;
            font-weight: 700;
        }

        /* Botones outline en modales */
        .modal-body .btn-outline-success {
            color: #198754;
            border-color: #198754;
        }

        .modal-body .btn-outline-success:hover {
            background-color: #198754;
            color: #ffffff;
        }

        /* Select2 en modales */
        .modal-body .select2-container--default .select2-selection--single {
            background-color: #ffffff;
            border: 1px solid #ced4da;
        }
    </style>
@endsection
@section('h1', 'Cuentas')
@section('breadcrumb')
    Cuentas
@endsection
@section('descripcion')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Revisa las cuentas activas del <strong>Negocio</strong>. Aquí podrás gestionar las cuentas de usuario
        asociadas a los servicios de streaming pertenecientes a Streamify HQ.
    </p>
    <div class="row">
        @php
            $serviciosConfig = [
                'NETFLIX' => ['color' => 'danger', 'icon' => 'logo_netflix.png'],
                'DISNEYP' => ['color' => 'primary', 'icon' => 'espn.jpg'],
                'MAX' => ['color' => 'info', 'icon' => 'max.jpg'],
                'PRIME' => ['color' => 'success', 'icon' => 'fa-amazon'],
                'PARAMOUNT' => ['color' => 'primary', 'icon' => 'paramount.jpg'],
                'CRUNCHY' => ['color' => 'warning', 'icon' => 'crunchy.jpg'],
                'SPOTIFY' => ['color' => 'success', 'icon' => 'fa-spotify'],
                'MAGIS' => ['color' => 'dark', 'icon' => 'magis.jpg'],
            ];
        @endphp

        @foreach ($espacios_por_servicio as $servicio => $espacios)
            @if (isset($serviciosConfig[$servicio]))
                @php
                    $color = $serviciosConfig[$servicio]['color'];
                    $icono = $serviciosConfig[$servicio]['icon'];
                @endphp
                <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                    <div class="card border-left-{{ $color }} shadow h-100 py-1 small">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-{{ $color }} text-uppercase mb-1">
                                        {{ ucfirst($servicio) }}
                                    </div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        {{ $espacios }} puestos
                                    </div>
                                </div>
                                <div class="col-auto">
                                    @if (Str::startsWith($icono, 'fa-'))
                                        <i class="fab {{ $icono }} fa-2x text-gray-300"></i>
                                    @else
                                        <img src="{{ asset('images/' . $icono) }}" width="40" height="40">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/modal-system.css') }}">
@endsection

@section('btncrear')
    <!-- Alert Container para mensajes dinámicos -->
    <div id="alert-container"></div>

    <div id="btncrear" class="d-flex flex-wrap gap-2 align-items-center mb-3">
        @if (Auth::user()->hasPermissionTo('cuentas.create'))
            <button onclick="openCreateModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Crear Cuenta
            </button>
            <a href="{{ route('cuentas.pdf') }}" class="btn btn-outline-primary" target="_blank">
                <i class="fas fa-file-pdf"></i> Reporte PDF
            </a>
        @endif
        @if (Auth::user()->hasPermissionTo('valores.create'))
            <button onclick="abrirModalCrearValorDesdeCuentas()" class="btn btn-primary">
                <i class="fas fa-layer-group"></i> Crear Valor
            </button>
        @endif
        @if (Auth::user()->hasPermissionTo('spotify') || Auth::user()->hasPermissionTo('todas_las_cuentas'))
            <a href="{{ route('cuentas.spotify') }}" class="btn btn-success">
                <i class="fab fa-spotify"></i> Revisar Spotify
            </a>
        @endif
        @if (Auth::user()->hasPermissionTo('cuentas.mensaje'))
            <button type="button" id="btn-enviar-inventario-proveedor" class="btn btn-success" onclick="openMensajeProveedorInventarioModal()" disabled>
                <i class="fab fa-whatsapp"></i> Enviar inventario proveedor (0)
            </button>
        @endif
            @if (Auth::user()->hasPermissionTo('cuentas.destroy'))
                <button type="button" id="btn-eliminar-seleccionadas" class="btn btn-danger" onclick="openBulkDeleteModal()" disabled>
                    <i class="fas fa-trash"></i> Eliminar seleccionadas (0)
                </button>
            @endif
    </div>
@endsection

@section('tablename', 'Cuentas')

@section('table1')
    <div class="card shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="fw-semibold text-primary">
                    <i class="fas fa-filter me-1"></i> Filtrar por servicio:
                </span>
            </div>

            <div class="d-flex flex-wrap gap-2" id="service-filter-group">
                <div class="form-check form-check-inline">
                    <input class="form-check-input service-filter-input" type="radio" name="service_filter" id="service-todos" value="TODOS" checked>
                    <label class="form-check-label" for="service-todos">Todos</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input service-filter-input" type="radio" name="service_filter" id="service-netflix" value="NETFLIX">
                    <label class="form-check-label" for="service-netflix">Netflix</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input service-filter-input" type="radio" name="service_filter" id="service-disneyp" value="DISNEYP">
                    <label class="form-check-label" for="service-disneyp">Disney+</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input service-filter-input" type="radio" name="service_filter" id="service-spotify" value="SPOTIFY">
                    <label class="form-check-label" for="service-spotify">Spotify</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input service-filter-input" type="radio" name="service_filter" id="service-prime" value="PRIME">
                    <label class="form-check-label" for="service-prime">Prime</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input service-filter-input" type="radio" name="service_filter" id="service-max" value="MAX">
                    <label class="form-check-label" for="service-max">Max</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input service-filter-input" type="radio" name="service_filter" id="service-crunchyroll" value="CRUNCHYROLL">
                    <label class="form-check-label" for="service-crunchyroll">Crunchyroll</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input service-filter-input" type="radio" name="service_filter" id="service-paramount" value="PARAMOUNT">
                    <label class="form-check-label" for="service-paramount">Paramount</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input service-filter-input" type="radio" name="service_filter" id="service-magis" value="MAGIS">
                    <label class="form-check-label" for="service-magis">Magis / Flujo</label>
                </div>
            </div>

            @if (Auth::user()->hasPermissionTo('cuentas.mensaje'))
                <div class="row g-2 mt-2 align-items-end">
                    <div class="col-md-6 col-lg-4">
                        <label for="provider-filter-select" class="form-label mb-1 fw-semibold text-primary">
                            <i class="fas fa-truck me-1"></i>Proveedor:
                        </label>
                        <select id="provider-filter-select" class="form-select form-select-sm">
                            <option value="TODOS">Todos los proveedores</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-8">
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="selectFilteredAccountsFromActiveTab()">
                                <i class="fas fa-check-square me-1"></i>Seleccionar filtradas visibles
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearInventorySelection()">
                                <i class="fas fa-eraser me-1"></i>Limpiar selección
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <div id="service-filter-status" class="small text-muted mt-2"></div>
        </div>
    </div>

    <ul class="nav nav-tabs" id="cuentasTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="todas-tab" data-bs-toggle="tab" data-bs-target="#todas" type="button"
                role="tab">Todas</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="disponibles-tab" data-bs-toggle="tab" data-bs-target="#disponibles" type="button"
                role="tab">Disponibles</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="colapsadas-tab" data-bs-toggle="tab" data-bs-target="#colapsadas" type="button"
                role="tab">Colapsadas</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="sinocupar-tab" data-bs-toggle="tab" data-bs-target="#sinocupar" type="button"
                role="tab">Sin Ocupar</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="porvencer-tab" data-bs-toggle="tab" data-bs-target="#porvencer" type="button"
                role="tab">Por Vencer</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="cortarusers-tab" data-bs-toggle="tab" data-bs-target="#cortarusers" type="button"
                role="tab">Cortar Usuarios</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="caidas-tab" data-bs-toggle="tab" data-bs-target="#caidas" type="button"
                role="tab">Dañadas</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="mesa-tab" data-bs-toggle="tab" data-bs-target="#mesa" type="button"
                role="tab">Mesa de Trabajo</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="individuales-tab" data-bs-toggle="tab" data-bs-target="#individuales" type="button"
                role="tab">Individual</button>
        </li>
    </ul>

    <!-- Contenido de las pestañas -->
    <div class="tab-content mt-3" id="cuentasTabContent">
        <!-- Pestaña de Cuentas -->
        <div class="tab-pane fade show active" id="todas" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $cuentas, 'tableId' => 'cuentas-todas-table'])
        </div>

        <!-- Pestaña de Cuentas Disponibles -->
        <div class="tab-pane fade" id="disponibles" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $cuentasDisponibles, 'tableId' => 'cuentas-disponibles-table'])
        </div>

        <!-- Pestaña de Cuentas Colapsadas -->
        <div class="tab-pane fade" id="colapsadas" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $cuentasColapsadas, 'tableId' => 'cuentas-colapsadas-table'])
        </div>

        <!-- Pestaña de Cuentas Sin Ocupar -->
        <div class="tab-pane fade" id="sinocupar" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $cuentasSinOcupar, 'tableId' => 'cuentas-sinocupar-table'])
        </div>

        <!-- Pestaña de Cuentas Por Vencer -->
        <div class="tab-pane fade" id="porvencer" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $cuentasPorVencer, 'tableId' => 'cuentas-porvencer-table'])
        </div>

        <!-- Pestaña de Cortar Users (cuentas con usuarios vencidos) -->
        <div class="tab-pane fade" id="cortarusers" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $cuentasConUsuariosVencidos, 'tableId' => 'cuentas-cortarusers-table'])
        </div>

        <!-- Pestaña de Cuentas Dañadas -->
        <div class="tab-pane fade" id="caidas" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $cuentasCaidas, 'tableId' => 'cuentas-caidas-table'])
        </div>

        <!-- Pestaña de Mesa de Trabajo -->
        <div class="tab-pane fade" id="mesa" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $mesa, 'tableId' => 'cuentas-mesa-table'])
        </div>

        <!-- Pestaña de Cuentas Individuales -->
        <div class="tab-pane fade" id="individuales" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $cuentasIndividuales, 'tableId' => 'cuentas-individuales-table'])
        </div>

    </div>
@endsection

<!-- Modales fuera de la tabla para que cubran toda la pantalla -->
@section('modals')
    @include('inventory.cuentas.modals.create')
    @include('inventory.cuentas.modals.edit')
    @include('inventory.cuentas.modals.delete')
    @include('inventory.cuentas.modals.renew')
    @include('inventory.cuentas.modals.view-perfiles')
    @include('inventory.cuentas.modals.mensaje-clientes')
    @include('inventory.cuentas.modals.mensaje-proveedor')
    @include('inventory.cuentas.modals.mensaje-proveedor-inventario')
    @include('inventory.cuentas.modals.netflix-codigo')
    @if (Auth::user()->hasPermissionTo('cuentas.destroy'))
        @include('inventory.cuentas.modals.bulk-delete')
    @endif

    {{-- Modal de Crear Valor (compartido desde valores) --}}
    @include('inventory.valores.modals.create')

    {{-- Toast de notificación para copiar --}}
    <div id="toast-copy" class="toast-success" style="display: none;">
        ✅ Copiado
    </div>
@endsection
@section('scripts')
<!-- jQuery (requerido) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 v4.1.0-rc.0 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Select2 Spanish -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/es.js"></script>
<!-- Searchable Select Component -->
<script src="{{ asset('js/searchable-select.js') }}"></script>
<!-- Enhanced Table v2.0 -->
<script src="{{ asset('js/enhanced-table-v2.js') }}"></script>

<script>
console.log('Vista de cuentas cargada con Enhanced Table v2.0 + Modales');

// ============================================================================
// FUNCIÓN DE ALERTAS TEMPORALES
// ============================================================================
function showTemporaryAlert(message, type = 'success') {
    const alertId = 'temp-alert-' + Date.now();
    const alertHtml = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show position-fixed"
             role="alert" style="top: 80px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', alertHtml);

    // Auto-dismiss después de 3 segundos
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            const bsAlert = bootstrap.Alert.getInstance(alert) || new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 3000);
}

// ============================================================================
// TOGGLE ESTADO EN TIEMPO REAL (SIN RELOAD)
// ============================================================================
function toggleEstado(idcue, currentStatus) {
    // Siempre buscar el botón padre, sin importar dónde se hizo click
    const button = event.target.closest('button.btn');

    if (!button) {
        console.error('No se encontró el botón. Event target:', event.target);
        return;
    }

    const row = button.closest('tr');

    if (!row) {
        console.error('No se encontró la fila de la tabla');
        return;
    }

    const statusBadge = row.querySelector('.status-badge');

    if (!statusBadge) {
        console.error('No se encontró el badge de estado en la fila');
        return;
    }

    // Deshabilitar botón durante proceso
    button.disabled = true;

    const url = '{{ route("cuentas.status", ":idcue") }}'.replace(':idcue', idcue);

    fetch(url, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Respuesta del servidor:', text);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // El toggle debe estar ON (derecha) cuando está CAÍDA (dañada)
            // El toggle debe estar OFF (izquierda) cuando NO está caída (operativa)
            const iconClass = `fas fa-toggle-${data.cuenta.caidacue ? 'on' : 'off'} fa-xs`;

            // Color del botón según estado
            const buttonColor = data.cuenta.caidacue ? 'btn-danger' : 'btn-success';

            // Actualizar el contenido completo del botón
            button.innerHTML = `<i class="${iconClass}"></i>`;

            // Actualizar color del botón
            button.className = `btn ${buttonColor} btn-sm ms-1`;

            // Actualizar badge de estado
            statusBadge.className = `badge bg-${data.statusClass} status-badge`;
            statusBadge.textContent = data.statusText;

            // Mostrar confirmación temporal
            showTemporaryAlert(data.message || 'Estado actualizado correctamente', 'success');
        } else {
            showTemporaryAlert(data.message || 'Error al actualizar el estado', 'danger');
        }
        button.disabled = false;
    })
    .catch(error => {
        console.error('Error completo:', error);
        showTemporaryAlert('Error al actualizar el estado: ' + error.message, 'danger');
        button.disabled = false;
    });
}

// ============================================================================
// FUNCIONES DE MODAL - CREAR
// ============================================================================
function openCreateModal() {
    console.log('🔷 Abriendo modal de crear cuenta...');
    const form = document.getElementById('createCuentaForm');
    if (form) form.reset();

    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'createCuentaModal' }));
}

function closeCreateModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createCuentaModal' }));
}

async function submitCreate(event) {
    event.preventDefault();
    console.log('📤 Enviando formulario de crear cuenta...');

    const form = event.target;
    const formData = new FormData(form);

    // Log de datos que se enviarán
    console.log('📋 Datos del formulario:');
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: ${value}`);
    }

    try {
        const response = await fetch('{{ route("cuentas.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData,
            credentials: 'same-origin'
        });

        console.log('📡 Status de respuesta:', response.status);

        // Verificar si la respuesta es JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            console.error('❌ Respuesta no es JSON:', contentType);
            const text = await response.text();
            console.error('Contenido de respuesta:', text.substring(0, 500));
            throw new Error('La respuesta del servidor no es JSON válido');
        }

        const data = await response.json();
        console.log('📦 Datos recibidos:', data);

        if (response.ok && data.success) {
            console.log('✅ Cuenta creada exitosamente:', data);
            showTemporaryAlert(data.message || 'Cuenta creada exitosamente', 'success');
            closeCreateModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            console.error('❌ Error al crear:', data);
            const errorMessage = data.message || data.error || 'Error al crear la cuenta';

            // Mostrar errores de validación si existen
            if (data.errors) {
                const errorList = Object.values(data.errors).flat().join('\n');
                showTemporaryAlert(errorList, 'danger');
            } else {
                showTemporaryAlert(errorMessage, 'danger');
            }
        }
    } catch (error) {
        console.error('❌ Error de red o procesamiento:', error);
        showTemporaryAlert('Error de conexión. Por favor, intenta nuevamente.\n' + error.message, 'danger');
    }
}

// ============================================================================
// FUNCIONES DE MODAL - EDITAR
// ============================================================================
function openEditModal(idcue) {
    console.log('🔷 Abriendo modal de editar cuenta:', idcue);

    const url = '{{ route("cuentas.edit", ":idcue") }}'.replace(':idcue', idcue);

    fetch(url, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Validar elementos
            const elements = {
                edit_idcue: document.getElementById('edit_idcue'),
                edit_idcue_display: document.getElementById('edit_idcue_display'),
                edit_idval: document.getElementById('edit_idval'),
                edit_usuariocue: document.getElementById('edit_usuariocue'),
                edit_contrasenacue: document.getElementById('edit_contrasenacue'),
                edit_fechavencue: document.getElementById('edit_fechavencue'),
                edit_caidacue: document.getElementById('edit_caidacue')
            };

            // Verificar existencia
            for (const [key, element] of Object.entries(elements)) {
                if (!element) {
                    console.error(`Elemento ${key} no encontrado en el DOM`);
                    return;
                }
            }

            elements.edit_idcue.value = data.cuenta.idcue;
            elements.edit_idcue_display.textContent = data.cuenta.idcue;
            elements.edit_idval.value = data.cuenta.idval;
            elements.edit_usuariocue.value = data.cuenta.usuariocue;
            elements.edit_contrasenacue.value = data.cuenta.contrasenacue;
            elements.edit_fechavencue.value = data.cuenta.fechavencue;
            elements.edit_caidacue.value = data.cuenta.caidacue;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editCuentaModal' }));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showTemporaryAlert('Error al cargar los datos de la cuenta', 'danger');
    });
}

function closeEditModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editCuentaModal' }));
}

async function submitEdit(event) {
    event.preventDefault();
    console.log('📤 Actualizando cuenta...');

    const idcue = document.getElementById('edit_idcue').value;
    const formData = new FormData(event.target);
    formData.append('_method', 'PUT');

    const url = '{{ route("cuentas.update", ":idcue") }}'.replace(':idcue', idcue);

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            console.log('✅ Cuenta actualizada');
            showTemporaryAlert(data.message || 'Cuenta actualizada exitosamente', 'success');
            closeEditModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            console.error('❌ Error al actualizar:', data);
            showTemporaryAlert(data.message || 'Error al actualizar la cuenta', 'danger');
        }
    } catch (error) {
        console.error('❌ Error de red:', error);
        showTemporaryAlert('Error de conexión. Por favor, intenta nuevamente.', 'danger');
    }
}

// ============================================================================
// FUNCIONES DE MODAL - ELIMINAR
// ============================================================================
function openDeleteModal(idcue) {
    console.log('🔷 Abriendo modal de eliminar cuenta:', idcue);

    const url = '{{ route("cuentas.edit", ":idcue") }}'.replace(':idcue', idcue);

    fetch(url, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cuenta = data.cuenta;

            // Validar que los elementos existan antes de acceder a ellos
            const elements = {
                delete_idcue: document.getElementById('delete_idcue'),
                delete_cuenta_id: document.getElementById('delete_cuenta_id'),
                delete_cuenta_servicio: document.getElementById('delete_cuenta_servicio'),
                delete_cuenta_usuario: document.getElementById('delete_cuenta_usuario'),
                delete_cuenta_vencimiento: document.getElementById('delete_cuenta_vencimiento'),
                delete_usuarios_count: document.getElementById('delete_usuarios_count'),
                delete_cuenta_estado: document.getElementById('delete_cuenta_estado'),
                delete_warning_usuarios: document.getElementById('delete_warning_usuarios'),
                delete_usuarios_count_text: document.getElementById('delete_usuarios_count_text')
            };

            // Verificar que todos los elementos existan
            for (const [key, element] of Object.entries(elements)) {
                if (!element) {
                    console.error(`Elemento ${key} no encontrado en el DOM`);
                    return;
                }
            }

            elements.delete_idcue.value = cuenta.idcue;
            elements.delete_cuenta_id.textContent = cuenta.idcue;

            // Construir texto del servicio de forma segura
            const servicioText = cuenta.valor?.idser && cuenta.valor?.proveedor?.nombrepro
                ? `${cuenta.valor.idser} - ${cuenta.valor.proveedor.nombrepro}`
                : (cuenta.valor?.idser || 'Servicio no disponible');
            elements.delete_cuenta_servicio.textContent = servicioText;

            elements.delete_cuenta_usuario.textContent = cuenta.usuariocue || 'N/A';
            elements.delete_cuenta_vencimiento.textContent = cuenta.fechavencue || 'N/A';
            elements.delete_usuarios_count.textContent = cuenta.usuarios_activos || 0;
            elements.delete_usuarios_count.className = cuenta.usuarios_activos > 0 ? 'badge bg-danger' : 'badge bg-success';
            elements.delete_cuenta_estado.innerHTML = cuenta.caidacue ?
                '<span class="badge bg-danger">Dañada</span>' : '<span class="badge bg-success">Activa</span>';

            const submitBtn = document.getElementById('delete_submit_btn');

            // Mostrar advertencia y deshabilitar botón si hay usuarios activos
            if (cuenta.usuarios_activos > 0) {
                elements.delete_usuarios_count_text.textContent = cuenta.usuarios_activos;
                elements.delete_warning_usuarios.style.display = 'block';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('disabled');
                    submitBtn.title = 'No se puede eliminar una cuenta con usuarios activos';
                }
            } else {
                elements.delete_warning_usuarios.style.display = 'none';
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('disabled');
                    submitBtn.title = '';
                }
            }

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'deleteCuentaModal' }));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showTemporaryAlert('Error al cargar los datos de la cuenta', 'danger');
    });
}

function closeDeleteModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'deleteCuentaModal' }));
}

async function submitDelete(event) {
    event.preventDefault();
    console.log('🗑️ Eliminando cuenta...');

    const idcue = document.getElementById('delete_idcue').value;
    const usuariosActivos = parseInt(document.getElementById('delete_usuarios_count').textContent);

    // Bloquear eliminación si hay usuarios activos
    if (usuariosActivos > 0) {
        showTemporaryAlert('❌ No se puede eliminar una cuenta con usuarios activos. Primero mueva los usuarios a otra cuenta.', 'danger');
        return;
    }

    const url = '{{ route("cuentas.destroy", ":idcue") }}'.replace(':idcue', idcue);

    try {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            console.log('✅ Cuenta eliminada');
            showTemporaryAlert(data.message || 'Cuenta eliminada exitosamente', 'success');
            closeDeleteModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            console.error('❌ Error al eliminar:', data);
            showTemporaryAlert(data.message || 'Error al eliminar la cuenta', 'danger');
        }
    } catch (error) {
        console.error('❌ Error de red:', error);
        showTemporaryAlert('Error de conexión. Por favor, intenta nuevamente.', 'danger');
    }
}

// ============================================================================
// FUNCIONES DE MODAL - RENOVAR
// ============================================================================
function openRenewModal(idcue) {
    console.log('🔷 Abriendo modal de renovar cuenta:', idcue);

    const url = '{{ route("cuentas.edit", ":idcue") }}'.replace(':idcue', idcue);

    fetch(url, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cuenta = data.cuenta;

            // Validar elementos
            const elements = {
                renew_idcue: document.getElementById('renew_idcue'),
                renew_cuenta_id: document.getElementById('renew_cuenta_id'),
                renew_cuenta_servicio: document.getElementById('renew_cuenta_servicio'),
                renew_fecha_actual: document.getElementById('renew_fecha_actual'),
                renew_descripcioncos: document.getElementById('renew_descripcioncos')
            };

            // Verificar existencia
            for (const [key, element] of Object.entries(elements)) {
                if (!element) {
                    console.error(`Elemento ${key} no encontrado en el DOM`);
                    return;
                }
            }

            elements.renew_idcue.value = cuenta.idcue;
            elements.renew_cuenta_id.textContent = cuenta.idcue;

            // Construir texto del servicio de forma segura
            const servicioText = cuenta.valor?.idser && cuenta.valor?.proveedor?.nombrepro
                ? `${cuenta.valor.idser} - ${cuenta.valor.proveedor.nombrepro}`
                : (cuenta.valor?.idser || 'Servicio no disponible');
            elements.renew_cuenta_servicio.textContent = servicioText;

            elements.renew_fecha_actual.textContent = cuenta.fechavencue || 'N/A';

            // Prellenar descripción de costo de forma segura
            const servicioNombre = cuenta.valor?.idser || 'cuenta';
            elements.renew_descripcioncos.value = `Renovación de ${servicioNombre}`;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'renewCuentaModal' }));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showTemporaryAlert('Error al cargar los datos de la cuenta', 'danger');
    });
}

function closeRenewModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'renewCuentaModal' }));
}

async function submitRenew(event) {
    event.preventDefault();
    console.log('📤 Renovando cuenta...');

    const idcue = document.getElementById('renew_idcue').value;
    const formData = new FormData(event.target);

    const url = '{{ route("cuentas.saveRenew", ":idcue") }}'.replace(':idcue', idcue);

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            console.log('✅ Cuenta renovada');
            showTemporaryAlert(data.message || 'Cuenta renovada exitosamente', 'success');
            closeRenewModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            console.error('❌ Error al renovar:', data);
            showTemporaryAlert(data.message || 'Error al renovar la cuenta', 'danger');
        }
    } catch (error) {
        console.error('❌ Error de red:', error);
        showTemporaryAlert('Error de conexión. Por favor, intenta nuevamente.', 'danger');
    }
}

// ============================================================================
// FUNCIONES DE MODAL - MENSAJE PERSONALIZADO A CLIENTES
// ============================================================================
let mensajeClientesButtonRef = null;
let mensajeProveedorButtonRef = null;
let netflixCodigoCuentaRef = null;

function getSelectedInventoryAccounts() {
    return Array.from(document.querySelectorAll('.provider-inventory-checkbox:checked')).map((checkbox) => ({
        idcue: (checkbox.dataset.idcue || '').toString().trim(),
        usuario: (checkbox.dataset.usuario || '').toString().trim(),
        usuariosActivos: parseInt(checkbox.dataset.usuariosActivos || '0', 10),
        fechavencue: (checkbox.dataset.fechavencue || '').toString().trim(),
        servicioId: (checkbox.dataset.servicioId || '').toString().trim().toUpperCase(),
        servicioNombre: (checkbox.dataset.servicioNombre || '').toString().trim(),
        proveedorId: (checkbox.dataset.proveedorId || '').toString().trim(),
        proveedorNombre: (checkbox.dataset.proveedorNombre || '').toString().trim(),
        proveedorTelefono: (checkbox.dataset.proveedorTelefono || '').toString().trim(),
        tableId: (checkbox.dataset.tableId || '').toString().trim(),
        checkbox,
    }));
}

function getSelectedProviderFilter() {
    const providerSelect = document.getElementById('provider-filter-select');
    return providerSelect ? (providerSelect.value || 'TODOS') : 'TODOS';
}

function rowMatchesSelectedProvider(row, selectedProvider) {
    if (selectedProvider === 'TODOS') {
        return true;
    }

    const providerId = String(row?.dataset?.proveedorId || '').trim();
    return providerId === String(selectedProvider).trim();
}

function rowMatchesAllFilters(row, selectedService = null, selectedProvider = null) {
    const serviceToCheck = selectedService || getSelectedService();
    const providerToCheck = selectedProvider || getSelectedProviderFilter();

    return rowMatchesSelectedService(row, serviceToCheck) && rowMatchesSelectedProvider(row, providerToCheck);
}

function isRowVisible(row) {
    if (!row || row.dataset.emptyRow === '1') return false;
    return row.offsetParent !== null;
}

function getActiveTableId() {
    const activePane = document.querySelector('.tab-pane.show.active');
    const table = activePane ? activePane.querySelector('table[data-table]') : null;
    return table ? table.getAttribute('data-table') : null;
}

function getSelectableRowsForTable(tableId) {
    const table = tableId ? document.getElementById(tableId) : null;
    if (!table) return [];

    const selectedService = getSelectedService();
    const selectedProvider = getSelectedProviderFilter();
    const rows = Array.from(table.querySelectorAll('tbody tr[data-account-row="1"]'));

    return rows.filter((row) => isRowVisible(row) && rowMatchesAllFilters(row, selectedService, selectedProvider));
}

function selectFilteredAccountsFromActiveTab() {
    const activeTableId = getActiveTableId();
    if (!activeTableId) {
        showTemporaryAlert('No se pudo detectar la tabla activa.', 'danger');
        return;
    }

    const rowsToSelect = getSelectableRowsForTable(activeTableId);
    if (rowsToSelect.length === 0) {
        showTemporaryAlert('No hay cuentas visibles con los filtros actuales.', 'danger');
        return;
    }

    rowsToSelect.forEach((row) => {
        const checkbox = row.querySelector('.provider-inventory-checkbox');
        if (checkbox) {
            checkbox.checked = true;
            row.classList.add('table-warning');
        }
    });

    refreshSelectAllStateForTable(activeTableId);
    updateInventoryButtonState();
    showTemporaryAlert(`Se seleccionaron ${rowsToSelect.length} cuentas filtradas visibles.`, 'success');
}

function clearInventorySelection() {
    document.querySelectorAll('.provider-inventory-checkbox:checked').forEach((checkbox) => {
        checkbox.checked = false;
        const row = checkbox.closest('tr');
        if (row) {
            row.classList.remove('table-warning');
        }
    });

    document.querySelectorAll('.provider-inventory-select-all').forEach((selectAll) => {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    });

    updateInventoryButtonState();
}

function updateInventoryButtonState() {
    const selectedAccounts = getSelectedInventoryAccounts();
    const count = selectedAccounts.length;

    const inventoryButton = document.getElementById('btn-enviar-inventario-proveedor');
    if (inventoryButton) {
        inventoryButton.disabled = count === 0;
        inventoryButton.innerHTML = `<i class="fab fa-whatsapp"></i> Enviar inventario proveedor (${count})`;
    }

    const bulkDeleteButton = document.getElementById('btn-eliminar-seleccionadas');
    if (bulkDeleteButton) {
        bulkDeleteButton.disabled = count === 0;
        bulkDeleteButton.innerHTML = `<i class="fas fa-trash"></i> Eliminar seleccionadas (${count})`;
    }
}
function openBulkDeleteModal() {
        const selectedAccounts = getSelectedInventoryAccounts();
        if (selectedAccounts.length === 0) {
            showTemporaryAlert('No hay cuentas seleccionadas.', 'danger');
            return;
        }

        const listEl = document.getElementById('bulk-delete-list');
        const warningEl = document.getElementById('bulk-delete-warning');
        let html = '';
        let hasActiveUsers = false;

        selectedAccounts.forEach((acc) => {
            const usuarios = parseInt(acc.usuariosActivos || 0);
            if (usuarios > 0) hasActiveUsers = true;
            html += `<tr class="${usuarios > 0 ? 'table-danger' : ''}">
                <td>${acc.idcue}</td>
                <td>${acc.usuario}</td>
                <td>${acc.servicioId}</td>
                <td>${usuarios > 0 ? '<span class="badge bg-danger">' + usuarios + ' activo(s)</span>' : '<span class="badge bg-success">0</span>'}</td>
            </tr>`;
        });

        if (listEl) listEl.innerHTML = html;

        if (warningEl) {
            warningEl.style.display = hasActiveUsers ? 'block' : 'none';
        }

        const btnConfirm = document.getElementById('bulk-delete-confirm-btn');
        if (btnConfirm) btnConfirm.disabled = hasActiveUsers;

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'bulkDeleteCuentasModal' }));
    }

    function closeBulkDeleteModal() {
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'bulkDeleteCuentasModal' }));
    }

    async function submitBulkDelete() {
        const selectedAccounts = getSelectedInventoryAccounts();
        const ids = selectedAccounts.map((a) => a.idcue);

        if (ids.length === 0) return;

        const btnConfirm = document.getElementById('bulk-delete-confirm-btn');
        if (btnConfirm) { btnConfirm.disabled = true; btnConfirm.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...'; }

        try {
            const response = await fetch('{{ route("cuentas.bulkDestroy") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ids }),
            });

            const data = await response.json();

            if (data.success) {
                showTemporaryAlert(data.message || 'Cuentas eliminadas exitosamente', 'success');
                closeBulkDeleteModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                showTemporaryAlert(data.message || 'Error al eliminar las cuentas', 'danger');
                if (btnConfirm) { btnConfirm.disabled = false; btnConfirm.innerHTML = '<i class="fas fa-trash"></i> Sí, Eliminar todas'; }
            }
        } catch (error) {
            showTemporaryAlert('Error de conexión. Por favor, intenta nuevamente.', 'danger');
            if (btnConfirm) { btnConfirm.disabled = false; btnConfirm.innerHTML = '<i class="fas fa-trash"></i> Sí, Eliminar todas'; }
        }
    }

function refreshSelectAllStateForTable(tableId) {
    if (!tableId) return;

    const tableCheckboxes = getSelectableRowsForTable(tableId)
        .map((row) => row.querySelector('.provider-inventory-checkbox'))
        .filter(Boolean);

    const selectAll = document.querySelector(`.provider-inventory-select-all[data-table-id="${tableId}"]`);
    if (!selectAll) return;

    if (tableCheckboxes.length === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
        return;
    }

    const checkedCount = tableCheckboxes.filter((cb) => cb.checked).length;
    selectAll.checked = checkedCount > 0 && checkedCount === tableCheckboxes.length;
    selectAll.indeterminate = checkedCount > 0 && checkedCount < tableCheckboxes.length;
}

function bindInventorySelectionEvents() {
    document.querySelectorAll('.provider-inventory-select-all').forEach((selectAll) => {
        selectAll.addEventListener('change', function () {
            const tableId = this.dataset.tableId || '';
            const shouldCheck = this.checked;

            const rowsToToggle = getSelectableRowsForTable(tableId);
            rowsToToggle.forEach((row) => {
                const checkbox = row.querySelector('.provider-inventory-checkbox');
                if (!checkbox) return;
                checkbox.checked = shouldCheck;
                row.classList.toggle('table-warning', shouldCheck);
            });

            refreshSelectAllStateForTable(tableId);
            updateInventoryButtonState();
        });
    });

    document.querySelectorAll('.provider-inventory-checkbox').forEach((checkbox) => {
        checkbox.addEventListener('change', function () {
            const row = this.closest('tr');
            if (row) {
                row.classList.toggle('table-warning', this.checked);
            }

            refreshSelectAllStateForTable(this.dataset.tableId || '');
            updateInventoryButtonState();
        });
    });
}

function formatCooldownText(untilTimestamp) {
    const nowTs = Math.floor(Date.now() / 1000);
    const remaining = Math.max(0, Number(untilTimestamp || 0) - nowTs);

    const minutes = Math.floor(remaining / 60);
    const seconds = remaining % 60;
    return `${minutes}:${String(seconds).padStart(2, '0')}`;
}

function openMensajeClientesModal(button) {
    if (!button) return;

    mensajeClientesButtonRef = button;

    const idcue = button.dataset.idcue || '';
    const servicio = button.dataset.servicio || 'N/A';
    const cuentaUsuario = button.dataset.cuentaUsuario || 'N/A';
    const clientesActivos = button.dataset.clientesActivos || '0';
    const cooldownUntil = Number(button.dataset.cooldownUntil || 0);

    document.getElementById('mensaje_clientes_idcue').value = idcue;
    document.getElementById('mensaje_clientes_cuenta').textContent = `${idcue} (${cuentaUsuario})`;
    document.getElementById('mensaje_clientes_servicio').textContent = servicio;
    document.getElementById('mensaje_clientes_total').textContent = clientesActivos;

    const cooldownAlert = document.getElementById('mensaje_clientes_cooldown');
    const submitBtn = document.getElementById('mensaje_clientes_submit_btn');

    if (cooldownUntil > Math.floor(Date.now() / 1000)) {
        cooldownAlert.classList.remove('d-none');
        cooldownAlert.textContent = `Este envío está bloqueado temporalmente. Espera ${formatCooldownText(cooldownUntil)} minutos para volver a enviar.`;
        submitBtn.disabled = true;
    } else {
        cooldownAlert.classList.add('d-none');
        cooldownAlert.textContent = '';
        submitBtn.disabled = false;
    }

    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'mensajeClientesModal' }));
}

function closeMensajeClientesModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'mensajeClientesModal' }));
}

async function submitMensajeClientes(event) {
    event.preventDefault();

    const form = event.target;
    const idcue = document.getElementById('mensaje_clientes_idcue').value;
    const submitBtn = document.getElementById('mensaje_clientes_submit_btn');
    const formData = new FormData(form);
    const payload = {
        mensaje: (formData.get('mensaje') || '').toString().trim()
    };

    if (!payload.mensaje) {
        showTemporaryAlert('Debes escribir un mensaje para enviar.', 'danger');
        return;
    }

    submitBtn.disabled = true;

    try {
        const url = '{{ route("cuentas.enviarMensajeClientes", ":idcue") }}'.replace(':idcue', idcue);
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (response.ok && data.success) {
            showTemporaryAlert(data.message || 'Mensaje enviado correctamente', 'success');
            closeMensajeClientesModal();

            const cooldownUntilTs = data.cooldown_until
                ? Math.floor(new Date(data.cooldown_until).getTime() / 1000)
                : (Math.floor(Date.now() / 1000) + 600);

            if (mensajeClientesButtonRef) {
                mensajeClientesButtonRef.disabled = true;
                mensajeClientesButtonRef.dataset.cooldownUntil = String(cooldownUntilTs);
            }

            form.reset();
            return;
        }

        if (response.status === 429 && data.remaining_seconds && mensajeClientesButtonRef) {
            const cooldownTs = Math.floor(Date.now() / 1000) + Number(data.remaining_seconds);
            mensajeClientesButtonRef.disabled = true;
            mensajeClientesButtonRef.dataset.cooldownUntil = String(cooldownTs);
        }

        showTemporaryAlert(data.message || 'No se pudo enviar el mensaje', 'danger');
    } catch (error) {
        console.error('Error enviando mensaje personalizado:', error);
        showTemporaryAlert('Error de conexión al enviar mensaje a n8n.', 'danger');
    } finally {
        const cooldownUntil = Number((mensajeClientesButtonRef && mensajeClientesButtonRef.dataset.cooldownUntil) || 0);
        submitBtn.disabled = cooldownUntil > Math.floor(Date.now() / 1000);
    }
}

function openMensajeProveedorModal(button) {
    if (!button) return;

    mensajeProveedorButtonRef = button;

    const idcue = button.dataset.idcue || '';
    const servicio = button.dataset.servicio || 'Servicio';
    const cuentaUsuario = button.dataset.cuentaUsuario || '';
    const cuentaClave = button.dataset.cuentaClave || '';
    const proveedor = button.dataset.proveedor || 'Proveedor';
    const telefono = button.dataset.proveedorTelefono || '';
    const proveedorId = button.dataset.proveedorId || '';

    const mensajePredeterminado = [
        'Hola bro, quiero *tu petición* con esta cuenta de',
        servicio,
        cuentaUsuario,
        cuentaClave
    ].join('\n');

    document.getElementById('mensaje_proveedor_idcue').value = idcue;
    document.getElementById('mensaje_proveedor_proveedor_id').value = proveedorId;
    document.getElementById('mensaje_proveedor_telefono').value = telefono;
    document.getElementById('mensaje_proveedor_nombre_display').textContent = proveedor;
    document.getElementById('mensaje_proveedor_telefono_display').textContent = telefono || 'Sin teléfono';
    document.getElementById('mensaje_proveedor_cuenta_display').textContent = `${idcue} (${cuentaUsuario})`;
    document.getElementById('mensaje_proveedor_texto').value = mensajePredeterminado;

    // Mostrar info del proveedor si ya está seleccionado
    const proveedorInfo = document.getElementById('proveedor_info');
    if (proveedorId && proveedor !== 'Proveedor') {
        proveedorInfo.style.display = 'block';
        document.getElementById('mensaje_proveedor_submit_btn').disabled = false;
    } else {
        proveedorInfo.style.display = 'none';
        document.getElementById('mensaje_proveedor_submit_btn').disabled = true;
    }

    // Limpiar búsqueda
    document.getElementById('proveedor_search').value = '';
    document.getElementById('proveedor_results').style.display = 'none';

    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'mensajeProveedorModal' }));
}

function closeMensajeProveedorModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'mensajeProveedorModal' }));
}

function openMensajeProveedorInventarioModal() {
    const selectedAccounts = getSelectedInventoryAccounts();
    if (selectedAccounts.length === 0) {
        showTemporaryAlert('Selecciona al menos una cuenta para enviar inventario.', 'danger');
        return;
    }

    const providerIds = [...new Set(selectedAccounts.map((account) => account.proveedorId).filter(Boolean))];
    if (providerIds.length !== 1) {
        showTemporaryAlert('Las cuentas seleccionadas deben pertenecer al mismo proveedor.', 'danger');
        return;
    }

    const provider = selectedAccounts[0];
    if (!provider.proveedorTelefono) {
        showTemporaryAlert('El proveedor seleccionado no tiene teléfono registrado.', 'danger');
        return;
    }

    // Pre-seleccionar el proveedor
    document.getElementById('inventario_proveedor_id').value = provider.proveedorId;
    document.getElementById('inventario_proveedor_nombre').textContent = provider.proveedorNombre || 'Proveedor';
    document.getElementById('inventario_proveedor_telefono').textContent = provider.proveedorTelefono;
    document.getElementById('inventario_total_cuentas').textContent = String(selectedAccounts.length);

    // Mostrar info del proveedor
    document.getElementById('inventario_proveedor_info').style.display = 'block';

    const serviciosMap = new Map();
    selectedAccounts.forEach((account) => {
        const serviceKey = account.servicioId || 'SERVICIO';
        if (!serviciosMap.has(serviceKey)) {
            serviciosMap.set(serviceKey, {
                id: serviceKey,
                nombre: account.servicioNombre || serviceKey,
                total: 0,
            });
        }

        serviciosMap.get(serviceKey).total += 1;
    });

    const serviciosContainer = document.getElementById('inventario_servicios_container');
    const serviciosHtml = Array.from(serviciosMap.values())
        .sort((a, b) => a.id.localeCompare(b.id))
        .map((servicio, index) => `
            <div class="form-check mb-2">
                <input
                    class="form-check-input inventario-servicio-checkbox"
                    type="checkbox"
                    value="${servicio.id}"
                    id="inventario_servicio_${index}"
                    checked
                >
                <label class="form-check-label" for="inventario_servicio_${index}">
                    <strong>${servicio.nombre}</strong> (${servicio.total} cuentas)
                </label>
            </div>
        `)
        .join('');

    serviciosContainer.innerHTML = serviciosHtml || '<div class="text-muted small">No hay servicios disponibles.</div>';

    // Limpiar búsqueda
    document.getElementById('inventario_proveedor_search').value = '';
    document.getElementById('inventario_proveedor_results').style.display = 'none';

    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'mensajeProveedorInventarioModal' }));
}

function closeMensajeProveedorInventarioModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'mensajeProveedorInventarioModal' }));
}

async function submitMensajeProveedorInventario(event) {
    event.preventDefault();

    const submitBtn = document.getElementById('inventario_submit_btn');
    const selectedAccounts = getSelectedInventoryAccounts();
    const providerId = (document.getElementById('inventario_proveedor_id').value || '').toString().trim();
    const selectedServices = Array.from(document.querySelectorAll('.inventario-servicio-checkbox:checked'))
        .map((checkbox) => (checkbox.value || '').toString().trim().toUpperCase())
        .filter(Boolean);

    if (!providerId || selectedAccounts.length === 0) {
        showTemporaryAlert('No hay cuentas seleccionadas para enviar inventario.', 'danger');
        return;
    }

    if (selectedServices.length === 0) {
        showTemporaryAlert('Selecciona al menos un servicio.', 'danger');
        return;
    }

    submitBtn.disabled = true;

    try {
        const payload = {
            proveedor_id: providerId,
            cuentas: selectedAccounts.map((account) => account.idcue),
            servicios: selectedServices,
        };

        const response = await fetch('{{ route("cuentas.enviarInventarioProveedor") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'No se pudo enviar el inventario al proveedor.');
        }

        showTemporaryAlert(data.message || 'Inventario enviado correctamente.', 'success');
        closeMensajeProveedorInventarioModal();

        selectedAccounts.forEach((account) => {
            if (account.checkbox) {
                account.checkbox.checked = false;
                const row = account.checkbox.closest('tr');
                if (row) {
                    row.classList.remove('table-warning');
                }
            }
        });

        document.querySelectorAll('.provider-inventory-select-all').forEach((selectAll) => {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        });

        updateInventoryButtonState();
    } catch (error) {
        console.error('Error enviando inventario al proveedor:', error);
        showTemporaryAlert(error.message || 'No se pudo enviar el inventario al proveedor.', 'danger');
    } finally {
        submitBtn.disabled = false;
    }
}

async function submitMensajeProveedor(event) {
    event.preventDefault();

    const form = event.target;
    const idcue = document.getElementById('mensaje_proveedor_idcue').value;
    const proveedorId = document.getElementById('mensaje_proveedor_proveedor_id').value;
    const submitBtn = document.getElementById('mensaje_proveedor_submit_btn');
    const formData = new FormData(form);
    const payload = {
        proveedor_id: proveedorId,
        telefono: (formData.get('telefono') || '').toString(),
        mensaje: (formData.get('mensaje') || '').toString().trim()
    };

    if (!payload.proveedor_id || !payload.telefono || !payload.mensaje) {
        showTemporaryAlert('Falta seleccionar proveedor, teléfono o mensaje.', 'danger');
        return;
    }

    submitBtn.disabled = true;

    try {
        const url = '{{ route("cuentas.enviarMensajeProveedor", ":idcue") }}'.replace(':idcue', idcue);
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'No se pudo enviar el mensaje al proveedor.');
        }

        showTemporaryAlert(data.message || 'Mensaje enviado al proveedor correctamente.', 'success');
        closeMensajeProveedorModal();
        form.reset();
    } catch (error) {
        console.error('Error enviando mensaje a proveedor:', error);
        showTemporaryAlert(error.message || 'No se pudo enviar el mensaje al proveedor.', 'danger');
    } finally {
        submitBtn.disabled = false;
    }
}

function setNetflixCodigoModalState(state, payload = {}) {
    const requestState = document.getElementById('netflix_codigo_request_state');
    const loadingState = document.getElementById('netflix_codigo_loading_state');
    const resultState = document.getElementById('netflix_codigo_result_state');
    const confirmBtn = document.getElementById('netflix_codigo_confirm_btn');
    const modalTitle = document.getElementById('netflix_codigo_modal_title');

    requestState?.classList.toggle('d-none', state !== 'request');
    loadingState?.classList.toggle('d-none', state !== 'loading');
    resultState?.classList.toggle('d-none', state !== 'result');

    if (confirmBtn) {
        confirmBtn.classList.toggle('d-none', state === 'result');
        confirmBtn.disabled = state === 'loading';
    }

    if (modalTitle) {
        modalTitle.textContent = state === 'result' ? 'Solicitud enviada' : 'Pedir codigo de Netflix';
    }

    if (state === 'result') {
        document.getElementById('netflix_codigo_result_message').textContent = payload.message || 'listo, te llegará un código al whatsapp';
        document.getElementById('netflix_codigo_result_expiration').textContent = payload.expirationText || 'En 15 minutos vence.';
    }
}

function openNetflixCodigoModal(button) {
    if (!button) return;

    netflixCodigoCuentaRef = button;
    document.getElementById('netflix_codigo_cuenta').textContent = `${button.dataset.idcue || ''} (${button.dataset.cuentaUsuario || ''})`;
    document.getElementById('netflix_codigo_proveedor').textContent = button.dataset.proveedor || 'Proveedor';
    setNetflixCodigoModalState('request');

    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'netflixCodigoModal' }));
}

function closeNetflixCodigoModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'netflixCodigoModal' }));
}

async function confirmNetflixCodeRequest() {
    if (!netflixCodigoCuentaRef) {
        showTemporaryAlert('No se encontro la cuenta para pedir el codigo.', 'danger');
        return;
    }

    setNetflixCodigoModalState('loading');

    try {
        const url = '{{ route("cuentas.pedirCodigoNetflix", ":idcue") }}'.replace(':idcue', netflixCodigoCuentaRef.dataset.idcue || '');
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'No se pudo obtener el codigo de Netflix.');
        }

        setNetflixCodigoModalState('result', {
            message: data.message,
            expirationText: data.expires_in_minutes ? `En ${data.expires_in_minutes} minutos vence.` : 'En 15 minutos vence.',
        });
    } catch (error) {
        console.error('Error pidiendo codigo Netflix:', error);
        setNetflixCodigoModalState('request');
        showTemporaryAlert(error.message || 'No se pudo obtener el codigo de Netflix.', 'danger');
    }
}

// 🔷 Función para abrir modal de crear valor desde cuentas
function abrirModalCrearValorDesdeCuentas() {
    console.log('🔷 Abriendo modal de crear valor desde cuentas');
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'createValorModal' }));
}

// 🔷 Función para enviar formulario de crear valor
async function submitCreateValor(event) {
    event.preventDefault();
    console.log('📤 Enviando formulario de creación de valor');

    const form = event.target;
    const formData = new FormData(form);

    try {
        const response = await fetch('{{ route("valores.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            console.log('✅ Valor creado exitosamente');
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createValorModal' }));
            showTemporaryAlert('Valor creado con éxito', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            console.error('❌ Error al crear valor:', data);
            showTemporaryAlert(data.message || 'Error al crear el valor', 'danger');
        }
    } catch (error) {
        console.error('❌ Error en la petición:', error);
        showTemporaryAlert('Error al procesar la solicitud', 'danger');
    }
}

// 📋 Funciones para copiar al portapapeles
function copiarTexto(texto, tipo) {
    navigator.clipboard.writeText(texto).then(() => {
        mostrarToast(`✅ ${tipo} copiado`);
    }).catch(err => {
        console.error('Error al copiar:', err);
        mostrarToast('❌ Error al copiar', 'error');
    });
}

function copiarInfoCuenta(servicio, usuario, contrasena) {
    const mensaje = `*${servicio}*\n${usuario}\n${contrasena}`;
    navigator.clipboard.writeText(mensaje).then(() => {
        mostrarToast('✅ Información de cuenta copiada');
    }).catch(err => {
        console.error('Error al copiar:', err);
        mostrarToast('❌ Error al copiar', 'error');
    });
}

function mostrarToast(mensaje, tipo = 'success') {
    const toast = document.getElementById('toast-copy');
    if (!toast) return;

    toast.textContent = mensaje;
    toast.className = tipo === 'error' ? 'toast-error' : 'toast-success';
    toast.style.display = 'block';
    toast.style.opacity = '1';

    setTimeout(() => {
        toast.style.transition = 'opacity 0.5s ease';
        toast.style.opacity = '0';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 500);
    }, 2000);
}

// ============================================================================
// FILTRO DE SERVICIOS EN TIEMPO REAL (SIN RECARGA)
// ============================================================================
const SERVICE_TABLE_IDS = [
    'cuentas-todas-table',
    'cuentas-disponibles-table',
    'cuentas-colapsadas-table',
    'cuentas-sinocupar-table',
    'cuentas-porvencer-table',
    'cuentas-cortarusers-table',
    'cuentas-caidas-table',
    'cuentas-mesa-table',
    'cuentas-individuales-table'
];

const SERVICE_ALIASES = {
    NETFLIX: ['NETFLIX'],
    DISNEYP: ['DISNEYP', 'DISNEY'],
    SPOTIFY: ['SPOTIFY'],
    PRIME: ['PRIME'],
    MAX: ['MAX'],
    CRUNCHYROLL: ['CRUNCHYROLL', 'CRUNCHY'],
    PARAMOUNT: ['PARAMOUNT'],
    MAGIS: ['MAGIS', 'FLUJO']
};

function normalizeServiceCode(value) {
    return String(value || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
}

function getSelectedService() {
    const selected = document.querySelector('.service-filter-input:checked');
    return selected ? selected.value : 'TODOS';
}

function getServiceFilteredRows(config, selectedService, selectedProvider) {
    const accountRows = config.allRows.filter((row) => row.dataset.accountRow === '1');

    return accountRows.filter((row) => rowMatchesAllFilters(row, selectedService, selectedProvider));
}

function rowMatchesSelectedService(row, selectedService) {
    if (selectedService === 'TODOS') {
        return true;
    }

    const rowService = normalizeServiceCode(row.dataset.serviceCode || '');
    const aliases = SERVICE_ALIASES[selectedService] || [selectedService];

    return aliases.some((alias) => rowService.includes(normalizeServiceCode(alias)));
}

function applyServiceFilterToTable(tableId, selectedService, selectedProvider) {
    const table = document.getElementById(tableId);
    if (!table || !table._config) return;

    const config = table._config;
    const serviceRows = getServiceFilteredRows(config, selectedService, selectedProvider);

    if (config.searchTerm && config.searchTerm.trim()) {
        const tokens = typeof tokenize === 'function' ? tokenize(config.searchTerm) : [];

        config.filteredRows = serviceRows.filter((row) => {
            const normalizedText = config.normalizedCache.get(row) || normalizeText(row.innerText);
            return tokens.length === 0 || tokens.every((token) => normalizedText.includes(token));
        });
    } else {
        config.filteredRows = serviceRows;
    }

    config.currentPage = 1;
    renderClientPage(config);
}

function syncServiceFilterIntoTableConfigs() {
    const selectedService = getSelectedService();
    const selectedProvider = getSelectedProviderFilter();

    SERVICE_TABLE_IDS.forEach((tableId) => {
        const table = document.getElementById(tableId);

        if (table && table._config) {
            table._config.baseRows = getServiceFilteredRows(table._config, selectedService, selectedProvider);
        }
    });
}

function updateServiceFilterStatus(selectedService) {
    const status = document.getElementById('service-filter-status');
    if (!status) return;

    const current = selectedService || 'TODOS';
    const providerSelect = document.getElementById('provider-filter-select');
    const providerLabel = providerSelect
        ? providerSelect.options[providerSelect.selectedIndex]?.text || 'Todos los proveedores'
        : 'Todos los proveedores';

    status.textContent = `Filtros activos: Servicio = ${current} | Proveedor = ${providerLabel}`;
}

function applyServiceFilterRealtime() {
    const selectedService = getSelectedService();
    const selectedProvider = getSelectedProviderFilter();
    syncServiceFilterIntoTableConfigs();

    SERVICE_TABLE_IDS.forEach((tableId) => {
        applyServiceFilterToTable(tableId, selectedService, selectedProvider);
    });

    updateServiceFilterStatus(selectedService);

    SERVICE_TABLE_IDS.forEach((tableId) => {
        refreshSelectAllStateForTable(tableId);
    });
}

function initializeProviderFilterOptions() {
    const providerSelect = document.getElementById('provider-filter-select');
    if (!providerSelect) return;

    const providersMap = new Map();
    document.querySelectorAll('.provider-inventory-checkbox').forEach((checkbox) => {
        const providerId = String(checkbox.dataset.proveedorId || '').trim();
        const providerName = String(checkbox.dataset.proveedorNombre || '').trim();

        if (!providerId || !providerName || providersMap.has(providerId)) return;
        providersMap.set(providerId, providerName);
    });

    const optionsHtml = ['<option value="TODOS">Todos los proveedores</option>']
        .concat(Array.from(providersMap.entries())
            .sort((a, b) => a[1].localeCompare(b[1]))
            .map(([id, name]) => `<option value="${id}">${name}</option>`));

    providerSelect.innerHTML = optionsHtml.join('');
}

document.addEventListener('DOMContentLoaded', function () {
    bindInventorySelectionEvents();
    initializeProviderFilterOptions();
    updateInventoryButtonState();

    const filterInputs = document.querySelectorAll('.service-filter-input');

    filterInputs.forEach((input) => {
        input.addEventListener('change', function () {
            applyServiceFilterRealtime();
        });
    });

    const providerFilterSelect = document.getElementById('provider-filter-select');
    if (providerFilterSelect) {
        providerFilterSelect.addEventListener('change', function () {
            applyServiceFilterRealtime();
        });
    }

    document.querySelectorAll('[data-bs-toggle="tab"]').forEach((tabButton) => {
        tabButton.addEventListener('shown.bs.tab', function () {
            updateInventoryButtonState();
            const activeTableId = getActiveTableId();
            if (activeTableId) {
                refreshSelectAllStateForTable(activeTableId);
            }
        });
    });

    setTimeout(applyServiceFilterRealtime, 0);
});

// ============================================================================
// FUNCIONES PARA BÚSQUEDA DE PROVEEDORES EN MENSAJERÍA
// ============================================================================

let proveedorSearchTimeout = null;

document.getElementById('proveedor_search')?.addEventListener('input', function() {
    const query = this.value.trim();
    const resultsContainer = document.getElementById('proveedor_results');

    clearTimeout(proveedorSearchTimeout);

    if (query.length < 2) {
        resultsContainer.style.display = 'none';
        return;
    }

    proveedorSearchTimeout = setTimeout(() => {
        buscarProveedores(query);
    }, 300);
});

async function buscarProveedores(query) {
    const resultsContainer = document.getElementById('proveedor_results');

    try {
        const response = await fetch(`{{ route('api.tech-config.proveedores.listar') }}?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Error al buscar proveedores');
        }

        mostrarResultadosProveedores(data.proveedores);
    } catch (error) {
        console.error('Error buscando proveedores:', error);
        resultsContainer.innerHTML = '<div class="p-2 text-danger">Error al buscar proveedores</div>';
        resultsContainer.style.display = 'block';
    }
}

function mostrarResultadosProveedores(proveedores) {
    const resultsContainer = document.getElementById('proveedor_results');

    if (!proveedores || proveedores.length === 0) {
        resultsContainer.innerHTML = '<div class="p-2 text-muted">No se encontraron proveedores</div>';
        resultsContainer.style.display = 'block';
        return;
    }

    const html = proveedores.map(proveedor => `
        <div class="p-2 border-bottom proveedor-result-item" style="cursor: pointer;"
             data-id="${proveedor.idpro}"
             data-nombre="${proveedor.nombre}"
             data-telefono="${proveedor.telefono}"
             onmouseover="this.style.backgroundColor='#f8f9fa'"
             onmouseout="this.style.backgroundColor='transparent'"
             onclick="seleccionarProveedor(${proveedor.idpro}, '${proveedor.nombre.replace(/'/g, "\\'")}', '${proveedor.telefono}')">
            <div class="fw-semibold">${proveedor.nombre}</div>
            <div class="text-muted small">${proveedor.telefono || 'Sin teléfono'}</div>
        </div>
    `).join('');

    resultsContainer.innerHTML = html;
    resultsContainer.style.display = 'block';
}

function seleccionarProveedor(id, nombre, telefono) {
    document.getElementById('mensaje_proveedor_proveedor_id').value = id;
    document.getElementById('mensaje_proveedor_telefono').value = telefono;
    document.getElementById('mensaje_proveedor_nombre_display').textContent = nombre;
    document.getElementById('mensaje_proveedor_telefono_display').textContent = telefono || 'Sin teléfono';

    document.getElementById('proveedor_info').style.display = 'block';
    document.getElementById('proveedor_results').style.display = 'none';
    document.getElementById('proveedor_search').value = '';
    document.getElementById('mensaje_proveedor_submit_btn').disabled = false;
}

// ============================================================================
// FUNCIONES PARA BÚSQUEDA DE PROVEEDORES EN INVENTARIO
// ============================================================================

let inventarioProveedorSearchTimeout = null;

document.getElementById('inventario_proveedor_search')?.addEventListener('input', function() {
    const query = this.value.trim();
    const resultsContainer = document.getElementById('inventario_proveedor_results');

    clearTimeout(inventarioProveedorSearchTimeout);

    if (query.length < 2) {
        resultsContainer.style.display = 'none';
        return;
    }

    inventarioProveedorSearchTimeout = setTimeout(() => {
        buscarProveedoresInventario(query);
    }, 300);
});

async function buscarProveedoresInventario(query) {
    const resultsContainer = document.getElementById('inventario_proveedor_results');

    try {
        const response = await fetch(`{{ route('api.tech-config.proveedores.listar') }}?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Error al buscar proveedores');
        }

        mostrarResultadosProveedoresInventario(data.proveedores);
    } catch (error) {
        console.error('Error buscando proveedores:', error);
        resultsContainer.innerHTML = '<div class="p-2 text-danger">Error al buscar proveedores</div>';
        resultsContainer.style.display = 'block';
    }
}

function mostrarResultadosProveedoresInventario(proveedores) {
    const resultsContainer = document.getElementById('inventario_proveedor_results');

    if (!proveedores || proveedores.length === 0) {
        resultsContainer.innerHTML = '<div class="p-2 text-muted">No se encontraron proveedores</div>';
        resultsContainer.style.display = 'block';
        return;
    }

    const html = proveedores.map(proveedor => `
        <div class="p-2 border-bottom proveedor-result-item" style="cursor: pointer;"
             data-id="${proveedor.idpro}"
             data-nombre="${proveedor.nombre}"
             data-telefono="${proveedor.telefono}"
             onmouseover="this.style.backgroundColor='#f8f9fa'"
             onmouseout="this.style.backgroundColor='transparent'"
             onclick="seleccionarProveedorInventario(${proveedor.idpro}, '${proveedor.nombre.replace(/'/g, "\\'")}', '${proveedor.telefono}')">
            <div class="fw-semibold">${proveedor.nombre}</div>
            <div class="text-muted small">${proveedor.telefono || 'Sin teléfono'}</div>
        </div>
    `).join('');

    resultsContainer.innerHTML = html;
    resultsContainer.style.display = 'block';
}

function seleccionarProveedorInventario(id, nombre, telefono) {
    document.getElementById('inventario_proveedor_id').value = id;
    document.getElementById('inventario_proveedor_nombre').textContent = nombre;
    document.getElementById('inventario_proveedor_telefono').textContent = telefono || 'Sin teléfono';

    document.getElementById('inventario_proveedor_info').style.display = 'block';
    document.getElementById('inventario_proveedor_results').style.display = 'none';
    document.getElementById('inventario_proveedor_search').value = '';

    // Aquí podríamos recargar las cuentas del proveedor seleccionado
    // Por ahora, mantendremos las cuentas seleccionadas
    document.getElementById('inventario_submit_btn').disabled = false;
}

</script>
@endsection
