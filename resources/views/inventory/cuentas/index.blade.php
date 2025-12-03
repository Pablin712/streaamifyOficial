@extends('layouts.table')

@section('title', 'Cuentas')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
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
                'DISNEYS' => ['color' => 'primary', 'icon' => 'disneyP.jpg'],
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
            <a href="{{ route('valores.create') }}" class="btn btn-primary">
                <i class="fas fa-layer-group"></i> Crear Valor
            </a>
        @endif
        @if (Auth::user()->hasPermissionTo('spotify') || Auth::user()->hasPermissionTo('todas_las_cuentas'))
            <a href="{{ route('cuentas.spotify') }}" class="btn btn-success">
                <i class="fab fa-spotify"></i> Revisar Spotify
            </a>
        @endif
    </div>
@endsection

@section('tablename', 'Cuentas')

@section('table1')
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
            <button class="nav-link" id="caidas-tab" data-bs-toggle="tab" data-bs-target="#caidas" type="button"
                role="tab">Dañadas</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="caidas-tab" data-bs-toggle="tab" data-bs-target="#mesa" type="button"
                role="tab">Mesa de Trabajo</button>
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

        <!-- Pestaña de Cuentas Dañadas -->
        <div class="tab-pane fade" id="caidas" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $cuentasCaidas, 'tableId' => 'cuentas-caidas-table'])
        </div>

        <!-- Pestaña de Mesa de Trabajo -->
        <div class="tab-pane fade" id="mesa" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $mesa, 'tableId' => 'cuentas-mesa-table'])
        </div>

    </div>
@endsection

<!-- Modales fuera de la tabla para que cubran toda la pantalla -->
@section('modals')
    @include('inventory.cuentas.modals.create')
    @include('inventory.cuentas.modals.edit')
    @include('inventory.cuentas.modals.delete')
    @include('inventory.cuentas.modals.renew')
@endsection
@section('scripts')
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

    const formData = new FormData(event.target);

    try {
        const response = await fetch('{{ route("cuentas.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            console.log('✅ Cuenta creada:', data);
            showTemporaryAlert(data.message || 'Cuenta creada exitosamente', 'success');
            closeCreateModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            console.error('❌ Error al crear:', data);
            showTemporaryAlert(data.message || 'Error al crear la cuenta', 'danger');
        }
    } catch (error) {
        console.error('❌ Error de red:', error);
        showTemporaryAlert('Error de conexión. Por favor, intenta nuevamente.', 'danger');
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
</script>
@endsection
