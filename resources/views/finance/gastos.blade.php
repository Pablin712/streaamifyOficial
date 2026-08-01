@extends('layouts.table')

@section('title', 'Gastos')
@section('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Select2 Dark Mode -->
    <link href="{{ asset('css/select2-dark-mode.css') }}" rel="stylesheet" />

    <link rel="icon" href="{{ asset('images/Icono.png') }}" type="image/x-icon">
@endsection
@section('h1', 'Gastos')
@section('breadcrumb')
    Gastos
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
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
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'crear-gasto' }));
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editar-gasto' }));
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'crear-tipo-gasto' }));
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editar-tipo-gasto' }));
            @endif
        });
    </script>

    <p>Aquí puedes ver todos los gastos asociados al negocio y registrar nuevos gastos. Si deseas ver los gastos de un tipo
        de gasto específico, selecciona un tipo de gasto en el modal.</p>
@endsection

@section('btncrear')
    <!-- Botón para abrir el modal de creación de gasto -->
    @if (Auth::user()->hasPermissionTo('gastos.store'))
        <button type="button" class="btn btn-primary mb-3" onclick="openCreateGastoModal()">
            Crear Gasto
        </button>
    @endif
@endsection
@section('tablename', 'Gastos')
@section('table1')
    <!-- Controles de búsqueda y registros -->
    <div class="row mb-3 align-items-end">
        <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
            <label for="gastos-table-search" class="form-label fw-semibold">
                <i class="fas fa-search text-primary"></i> Buscar:
            </label>
            <input id="gastos-table-search"
                   type="text"
                   placeholder="Buscar gasto..."
                   class="form-control">
        </div>
        <div class="col-lg-4 col-md-5 col-12">
            <label for="gastos-table-rows-per-page" class="form-label fw-semibold">
                <i class="fas fa-list text-primary"></i> Mostrar:
            </label>
            <select id="gastos-table-rows-per-page" class="form-select">
                <option value="5">5 registros</option>
                <option value="10" selected>10 registros</option>
                <option value="20">20 registros</option>
                <option value="50">50 registros</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table id="gastos-table" data-table="gastos-table" class="table table-striped table-bordered">
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
                    Tipo de Gasto
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
                @if (Auth::user()->hasAnyPermission(['gastos.update', 'gastos.destroy']))
                    <th data-type="actions">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($gastos as $gasto)
                <tr>
                    <td>{{ $gasto->idgas }}</td>
                    <td>{{ $gasto->tipoGasto->detalletip }}</td>
                    <td>{{ $gasto->fechagas }}</td>
                    <td>{{ $gasto->descripciongas }}</td>
                    <td>${{ number_format($gasto->montogas, 2) }}</td>
                    @if (Auth::user()->hasAnyPermission(['gastos.update', 'gastos.destroy']))
                        <td>
                            @if (Auth::user()->hasPermissionTo('gastos.update'))
                                <!-- Editar gasto (abre el modal con los datos del gasto) -->
                                <button type="button"
                                    class="btn btn-warning fas fa-edit"
                                    onclick="editarGasto({{ $gasto->idgas }}, {{ $gasto->idtip }}, '{{ $gasto->descripciongas }}', {{ $gasto->montogas }}, '{{ $gasto->fechagas }}', {{ $gasto->transaccion ? $gasto->transaccion->banco_id : 'null' }})">
                                    Editar
                                </button>
                            @endif
                            @if (Auth::user()->hasPermissionTo('gastos.destroy'))
                                <button type="button" class="btn btn-danger"
                                    onclick="confirmarEliminarGasto({{ $gasto->idgas }})"
                                    title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    <!-- Información de paginación y controles -->
    <div class="row mt-3 align-items-center">
        <div class="col-md-6 col-12 mb-2 mb-md-0">
            <div id="gastos-table-row-info" class="text-muted"></div>
        </div>
        <div class="col-md-6 col-12">
            <div id="gastos-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
        </div>
    </div>
@endsection

@section('table2')
    <div class="card mb-4">
        <div class="card-body">
            <h3>Gestión de Tipos de Gastos</h3>
            <h4>Realizado por Pablo Jiménez</h4>
            <p>Aquí puedes ver todos los tipos de gastos, describe el tipo de gasto en el modal.</p>
            @if (Auth::user()->hasPermissionTo('tipos.store'))
                <div class="form-group mb-3">
                    <!-- Botón para abrir el modal -->
                    <button class="btn btn-success" onclick="openCreateTipoGastoModal()">
                        Crear Tipo de Gasto
                    </button>
                </div>
            @endif
        </div>
    </div>
    <div id="tabla-tipogasto" class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tipos de gasto
        </div>
        <div class="card-body">
            <!-- Controles de búsqueda y registros -->
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="tipos-gastos-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search text-primary"></i> Buscar:
                    </label>
                    <input id="tipos-gastos-table-search"
                           type="text"
                           placeholder="Buscar tipo de gasto..."
                           class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="tipos-gastos-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list text-primary"></i> Mostrar:
                    </label>
                    <select id="tipos-gastos-table-rows-per-page" class="form-select">
                        <option value="5">5 registros</option>
                        <option value="10" selected>10 registros</option>
                        <option value="20">20 registros</option>
                        <option value="50">50 registros</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tipos-gastos-table" data-table="tipos-gastos-table" class="table table-striped table-bordered">
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
                            Detalle
                            <span class="sort-arrow">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                </svg>
                            </span>
                        </th>
                        <th>Afecta utilidad</th>
                        @if (Auth::user()->hasAnyPermission(['tipos.update', 'tipos.destroy']))
                            <th data-type="actions">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tipoGastos as $tipoGasto)
                        <tr>
                            <td>{{ $tipoGasto->idtip }}</td>
                            <td>{{ $tipoGasto->detalletip }}</td>
                            <td>
                                @if ($tipoGasto->excluir_de_ganancia)
                                    <span class="badge bg-secondary">No (informativo)</span>
                                @else
                                    <span class="badge bg-success">Sí (operativo)</span>
                                @endif
                            </td>
                            @if (Auth::user()->hasAnyPermission(['tipos.update', 'tipos.destroy']))
                                <td>
                                    @if (Auth::user()->hasPermissionTo('tipos.update'))
                                        <!-- Editar Tipo de Gasto -->
                                        <button type="button"
                                            class="btn btn-warning fas fa-edit"
                                            onclick="editarTipoGasto({{ $tipoGasto->idtip }}, '{{ $tipoGasto->detalletip }}', {{ $tipoGasto->excluir_de_ganancia ? 'true' : 'false' }})">
                                            Editar
                                        </button>
                                    @endif
                                    @if (Auth::user()->hasPermissionTo('tipos.destroy'))
                                        <button type="button" class="btn btn-danger"
                                            onclick="confirmarEliminarTipoGasto({{ $tipoGasto->idtip }})"
                                            title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            <!-- Información de paginación y controles -->
            <div class="row mt-3 align-items-center">
                <div class="col-md-6 col-12 mb-2 mb-md-0">
                    <div id="tipos-gastos-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6 col-12">
                    <div id="tipos-gastos-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

<!-- Modales -->
@include('finance.gastos.modals.create')
@include('finance.gastos.modals.edit')
@include('finance.gastos.modals.create-tipo')
@include('finance.gastos.modals.edit-tipo')
@include('finance.gastos.modals.delete')
@include('finance.gastos.modals.delete-tipo')

{{-- Scripts --}}
@section('scripts')
    <!-- jQuery (debe cargarse primero) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 (debe cargarse después de jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Inicializador de searchable-selects -->
    <script src="{{ asset('js/searchable-select.js') }}"></script>

    {{-- Enhanced Table v2 --}}
    <script src="{{ asset('js/enhanced-table-v2.js') }}?v={{ filemtime(public_path('js/enhanced-table-v2.js')) }}"></script>

    <script>
        console.log('Vista de gastos cargada con Enhanced Table v2.0');

        // ============================================================================
        // FUNCIONES DE MODAL - CREAR GASTO
        // ============================================================================
        function openCreateGastoModal() {
            console.log('🔷 Abriendo modal de crear gasto...');
            const form = document.getElementById('createGastoForm');
            if (form) form.reset();

            // Resetear fecha a hoy (hora local, no UTC)
            const fechaInput = document.getElementById('fechagas');
            if (fechaInput) {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                fechaInput.value = `${year}-${month}-${day}`;
            }

            // Abrir el modal
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'crear-gasto' }));

            // Inicializar Select2 después de abrir el modal
            setTimeout(function() {
                const $select = $('#idtip');

                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                $select.select2({
                    theme: 'bootstrap-5',
                    placeholder: '-- Selecciona un Tipo de Gasto --',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('.modal-overlay:visible .modal-content'),
                    language: {
                        noResults: function() { return "No se encontraron resultados"; },
                        searching: function() { return "Buscando..."; }
                    }
                });

                console.log('✅ Select2 inicializado en modal crear-gasto');
            }, 400);
        }

        // ============================================================================
        // FUNCIONES DE MODAL - EDITAR GASTO
        // ============================================================================
        window.editarGasto = function(idgas, idtip, descripciongas, montogas, fechagas, bancoId) {
            console.log('🔷 Abriendo modal de editar gasto:', idgas);

            // Llenar el formulario
            document.getElementById('edit_descripciongas').value = descripciongas;
            document.getElementById('edit_montogas').value = montogas;
            document.getElementById('edit_fechagas').value = fechagas;

            // Seleccionar banco
            const bancoSelect = document.getElementById('edit_banco_id');
            if (bancoSelect && bancoId) {
                bancoSelect.value = bancoId;
            }

            // Actualizar la acción del formulario
            const form = document.getElementById('editarGastoForm');
            form.action = "{{ route('gastos.update', '') }}/" + idgas;

            // Abrir modal
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editar-gasto' }));

            // Inicializar Select2 y establecer valor
            setTimeout(function() {
                const $select = $('#edit_idtip');

                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                $select.select2({
                    theme: 'bootstrap-5',
                    placeholder: '-- Selecciona un Tipo de Gasto --',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('.modal-overlay:visible .modal-content'),
                    language: {
                        noResults: function() { return "No se encontraron resultados"; },
                        searching: function() { return "Buscando..."; }
                    }
                });

                // Establecer el valor después de inicializar
                $select.val(idtip).trigger('change');

                console.log('✅ Select2 inicializado en modal editar-gasto');
            }, 400);
        };

        // ============================================================================
        // FUNCIONES DE MODAL - CREAR TIPO GASTO
        // ============================================================================
        function openCreateTipoGastoModal() {
            console.log('🔷 Abriendo modal de crear tipo gasto...');
            const form = document.getElementById('createTipoGastoForm');
            if (form) form.reset();

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'crear-tipo-gasto' }));
        }

        // ============================================================================
        // FUNCIONES DE MODAL - EDITAR TIPO GASTO
        // ============================================================================
        window.editarTipoGasto = function(idtip, detalletip, excluirDeGanancia) {
            console.log('🔷 Abriendo modal de editar tipo gasto:', idtip);

            // Llenar el formulario
            document.getElementById('edit_detalletip').value = detalletip;
            document.getElementById('edit_excluir_de_ganancia').checked = !!excluirDeGanancia;

            // Actualizar la acción del formulario
            const form = document.getElementById('editarTipoGastoForm');
            form.action = "{{ route('tipos.update', '') }}/" + idtip;

            // Abrir modal
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editar-tipo-gasto' }));
        };

        // ============================================================================
        // FUNCIONES DE MODAL - ELIMINAR GASTO
        // ============================================================================
        function confirmarEliminarGasto(idgas) {
            console.log('🔷 Abriendo modal de eliminar gasto:', idgas);

            const form = document.getElementById('deleteGastoForm');
            form.action = "{{ route('gastos.destroy', '') }}/" + idgas;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'eliminar-gasto' }));
        }

        // ============================================================================
        // FUNCIONES DE MODAL - ELIMINAR TIPO GASTO
        // ============================================================================
        function confirmarEliminarTipoGasto(idtip) {
            console.log('🔷 Abriendo modal de eliminar tipo gasto:', idtip);

            const form = document.getElementById('deleteTipoGastoForm');
            form.action = "{{ route('tipos.destroy', '') }}/" + idtip;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'eliminar-tipo-gasto' }));
        }
    </script>
@endsection

