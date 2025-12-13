@extends('layouts.navigation')

@section('title', 'Costos')

@section('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Select2 Dark Mode -->
    <link href="{{ asset('css/select2-dark-mode.css') }}" rel="stylesheet" />
@endsection

@section('main')
<div class="container-fluid px-4">
    <!-- Título y breadcrumb -->
    <h1 class="mt-4">Costos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Costos</li>
    </ol>

    <!-- Descripción y alertas -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <script>
        // Auto-dismiss mensajes y cerrar modales
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });

            @if (session('success') || session('error'))
                // Cerrar modales
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'crear-costo' }));
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editar-costo' }));
            @endif
        });
    </script>

    <div class="mb-4">
        <h3 class="text-primary">Gestión de Costos</h3>
        <p class="text-muted">Aquí puedes ver todos los costos asociados al negocio y registrar nuevos costos. Si deseas ver los costos de una
        cuenta específica, selecciona una cuenta en el modal.</p>
    </div>

    <!-- Botón crear costo -->
    <div class="mb-3">
        <button type="button" class="btn btn-primary" onclick="openCreateCostoModal()">
            <i class="fas fa-plus"></i> Crear Costo
        </button>
    </div>

    <!-- Tabla Enhanced v2 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Lista de Costos
            </h6>
        </div>
        <div class="card-body">
            <!-- Encabezado: Búsqueda y Registros por página -->
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="costos-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search text-primary"></i> Buscar:
                    </label>
                    <input id="costos-table-search"
                           type="text"
                           placeholder="Buscar por cuenta, descripción, monto..."
                           class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="costos-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list text-primary"></i> Mostrar:
                    </label>
                    <select id="costos-table-rows-per-page" class="form-select">
                        <option value="5" selected>5 registros</option>
                        <option value="10">10 registros</option>
                        <option value="20">20 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="costos-table"
                       data-table="costos-table"
                       data-server-side="true"
                       data-search-url="{{ route('costos') }}"
                       class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="sortable" data-type="number" data-col="0">
                                ID
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="1">
                                Cuenta
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="2">
                                Fecha
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="3">
                                Descripción
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="4">
                                Monto
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            @if (Auth::user()->hasAnyPermission(['costos.update', 'costos.destroy']))
                                <th data-type="actions">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center p-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer: Info y paginación -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="costos-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6">
                    <div id="costos-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales -->
@include('finance.costos.modals.create')
@include('finance.costos.modals.edit')
@include('finance.costos.modals.delete')
@endsection

@section('scripts')
<!-- jQuery (debe cargarse primero) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 (debe cargarse después de jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Inicializador de searchable-selects -->
<script src="{{ asset('js/searchable-select.js') }}"></script>

<!-- Enhanced Table v2.0 -->
<script src="{{ asset('js/enhanced-table-v2.js') }}"></script>

<script>
    console.log('Vista de costos cargada con Enhanced Table v2.0 Server-side');

    // ============================================================================
    // FUNCIONES DE MODAL - CREAR COSTO
    // ============================================================================
    function openCreateCostoModal() {
        console.log('🔷 Abriendo modal de crear costo...');
        const form = document.getElementById('createCostoForm');
        if (form) form.reset();

        // Resetear fecha a hoy
        const fechaInput = document.getElementById('fechacos');
        if (fechaInput) {
            fechaInput.value = new Date().toISOString().split('T')[0];
        }

        // Marcar checkbox como pagado por defecto
        const sePagoCheckbox = document.getElementById('se_pago');
        if (sePagoCheckbox) {
            sePagoCheckbox.checked = true;
            toggleBancoField();
        }

        // Abrir el modal
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'crear-costo' }));

        // Inicializar Select2 después de abrir el modal
        setTimeout(function() {
            const $select = $('#idcue');

            // Destruir Select2 si ya existe
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
                    noResults: function() { return "No se encontraron resultados"; },
                    searching: function() { return "Buscando..."; }
                }
            });

            console.log('✅ Select2 inicializado en modal crear-costo');
        }, 400);
    }

    // Función para controlar el campo de banco según el checkbox
    function toggleBancoField() {
        const sePago = document.getElementById('se_pago');
        const bancoField = document.getElementById('banco_id');
        const bancoLabel = document.querySelector('label[for="banco_id"]');

        if (sePago && bancoField) {
            if (sePago.checked) {
                // Si se pagó, el banco es requerido
                bancoField.required = true;
                if (bancoLabel) {
                    bancoLabel.innerHTML = 'Banco <span class="text-danger">*</span>';
                }
                bancoField.parentElement.style.display = 'block';
            } else {
                // Si no se pagó (deuda), el banco no es requerido
                bancoField.required = false;
                bancoField.value = '';
                if (bancoLabel) {
                    bancoLabel.textContent = 'Banco';
                }
                bancoField.parentElement.style.display = 'none';
            }
        }
    }

    // Event listener para el checkbox
    document.addEventListener('DOMContentLoaded', function() {
        const sePagoCheckbox = document.getElementById('se_pago');
        if (sePagoCheckbox) {
            sePagoCheckbox.addEventListener('change', toggleBancoField);
        }
    });

    // ============================================================================
    // FUNCIONES DE MODAL - EDITAR COSTO
    // ============================================================================
    window.editarCosto = function(idcos, idcue, descripcioncos, montocos, fechacos, bancoId) {
        console.log('🔷 Abriendo modal de editar costo:', idcos);

        // Llenar el formulario
        document.getElementById('edit_idcue').value = idcue;
        document.getElementById('edit_descripcioncos').value = descripcioncos;
        document.getElementById('edit_montocos').value = montocos;
        document.getElementById('edit_fechacos').value = fechacos;

        // Seleccionar banco
        const bancoSelect = document.getElementById('edit_banco_id');
        if (bancoSelect && bancoId) {
            bancoSelect.value = bancoId;
        }

        // Actualizar la acción del formulario
        const form = document.getElementById('editCostoForm');
        form.action = "{{ route('costos.update', '') }}/" + idcos;

        // Abrir modal
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editar-costo' }));
    };

    // ============================================================================
    // FUNCIONES DE MODAL - ELIMINAR COSTO
    // ============================================================================
    window.confirmarEliminarCosto = function(idcos) {
        console.log('🔷 Abriendo modal de eliminar costo:', idcos);

        // Actualizar la acción del formulario
        const form = document.getElementById('deleteCostoForm');
        form.action = "{{ route('costos.destroy', '') }}/" + idcos;

        // Abrir modal
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'eliminar-costo' }));
    };
</script>
@endsection
