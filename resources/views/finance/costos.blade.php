@extends('layouts.navigation')

@section('title', 'Costos')

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

    <div class="mb-4">
        <h3 class="text-primary">Gestión de Costos</h3>
        <p class="text-muted">Aquí puedes ver todos los costos asociados al negocio y registrar nuevos costos. Si deseas ver los costos de una
        cuenta específica, selecciona una cuenta en el modal.</p>
    </div>

    <!-- Botón crear costo -->
    <div class="mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#seleccionarCuentaModal">
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

<!-- Modal para crear un nuevo costo -->
<div class="modal fade" id="seleccionarCuentaModal" tabindex="-1" aria-labelledby="seleccionarCuentaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="seleccionarCuentaModalLabel">Crear Costo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('costos.store') }}" method="POST">
                    @csrf
                    <!-- Selector de Cuentas -->
                    <div class="form-group mb-3">
                        <label for="idcue">Seleccionar Cuenta</label>
                        <select name="idcue" id="idcue" class="form-control" required>
                            <option value="">-- Selecciona una Cuenta --</option>
                            @foreach ($cuentas as $cuenta)
                                <option value="{{ $cuenta->idcue }}">
                                    {{ $cuenta->idcue }} - {{ $cuenta->usuariocue }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Campos del Costo -->
                    <div class="form-group mb-3">
                        <label for="descripcioncos">Descripción</label>
                        <input type="text" name="descripcioncos" id="descripcioncos" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="montocos">Monto</label>
                        <input type="number" name="montocos" id="montocos" class="form-control" step="0.01"
                            required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="fechacos">Fecha</label>
                        <input type="date" name="fechacos" id="fechacos" class="form-control"
                            value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal para editar el costo -->
<div class="modal fade" id="editarCostoModal" tabindex="-1" aria-labelledby="editarCostoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarCostoModalLabel">Editar Costo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="editCostoForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group mb-3">
                        <label for="edit_idcue">Cuenta</label>
                        <!-- Campo solo lectura para la cuenta -->
                        <input type="text" id="edit_idcue" class="form-control" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_descripcioncos">Descripción</label>
                        <input type="text" name="descripcioncos" id="edit_descripcioncos" class="form-control"
                            required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_montocos">Monto</label>
                        <input type="number" name="montocos" id="edit_montocos" class="form-control"
                            step="0.01" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_fechacos">Fecha</label>
                        <input type="date" name="fechacos" id="edit_fechacos" class="form-control">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Enhanced Table v2.0 -->
<script src="{{ asset('js/enhanced-table-v2.js') }}"></script>

<script>
    // Función para llenar el formulario del modal con los datos del costo a editar
    $('#editarCostoModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Botón que abrió el modal
        var idcos = button.data('id');
        var idcue = button.data('idcue'); // ID de la cuenta seleccionada
        var descripcioncos = button.data('descripcioncos');
        var montocos = button.data('montocos');
        var fechacos = button.data('fechacos');

        var modal = $(this);
        modal.find('#edit_idcue').val(idcue); // Mostrar el ID de la cuenta asociada (campo solo lectura)
        modal.find('#edit_descripcioncos').val(descripcioncos);
        modal.find('#edit_montocos').val(montocos);
        modal.find('#edit_fechacos').val(fechacos);

        // Cambiar la acción del formulario de edición a la ruta del costo específico
        var formAction = "{{ route('costos.update', '') }}/" + idcos;
        modal.find('#editCostoForm').attr('action', formAction);
    });
</script>
<script>
    $(document).ready(function() {
        // Inicializar Select2 en el modal cuando se abra
        $("#seleccionarCuentaModal").on("shown.bs.modal", function() {
            $("#idcue").select2({
                dropdownParent: $(
                "#seleccionarCuentaModal"), // Esto es clave para que funcione dentro del modal
                placeholder: "Seleccione una cuenta",
                allowClear: true,
            });
        });
    });
</script>

<script>
    console.log('Vista de costos cargada con Enhanced Table v2.0 Server-side');
</script>
@endsection

<!-- Modal para crear un nuevo costo -->
<div class="modal fade" id="seleccionarCuentaModal" tabindex="-1" aria-labelledby="seleccionarCuentaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="seleccionarCuentaModalLabel">Crear Costo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('costos.store') }}" method="POST">
                    @csrf
                    <!-- Selector de Cuentas -->
                    <div class="form-group mb-3">
                        <label for="idcue">Seleccionar Cuenta</label>
                        <select name="idcue" id="idcue" class="form-control" required>
                            <option value="">-- Selecciona una Cuenta --</option>
                            @foreach ($cuentas as $cuenta)
                                <option value="{{ $cuenta->idcue }}">
                                    {{ $cuenta->idcue }} - {{ $cuenta->usuariocue }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Campos del Costo -->
                    <div class="form-group mb-3">
                        <label for="descripcioncos">Descripción</label>
                        <input type="text" name="descripcioncos" id="descripcioncos" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="montocos">Monto</label>
                        <input type="number" name="montocos" id="montocos" class="form-control" step="0.01"
                            required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="fechacos">Fecha</label>
                        <input type="date" name="fechacos" id="fechacos" class="form-control"
                            value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal para editar el costo -->
<div class="modal fade" id="editarCostoModal" tabindex="-1" aria-labelledby="editarCostoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarCostoModalLabel">Editar Costo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="editCostoForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group mb-3">
                        <label for="edit_idcue">Cuenta</label>
                        <!-- Campo solo lectura para la cuenta -->
                        <input type="text" id="edit_idcue" class="form-control" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_descripcioncos">Descripción</label>
                        <input type="text" name="descripcioncos" id="edit_descripcioncos" class="form-control"
                            required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_montocos">Monto</label>
                        <input type="number" name="montocos" id="edit_montocos" class="form-control"
                            step="0.01" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_fechacos">Fecha</label>
                        <input type="date" name="fechacos" id="edit_fechacos" class="form-control">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        // Función para llenar el formulario del modal con los datos del costo a editar
        $('#editarCostoModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Botón que abrió el modal
            var idcos = button.data('id');
            var idcue = button.data('idcue'); // ID de la cuenta seleccionada
            var descripcioncos = button.data('descripcioncos');
            var montocos = button.data('montocos');
            var fechacos = button.data('fechacos');

            var modal = $(this);
            modal.find('#edit_idcue').val(idcue); // Mostrar el ID de la cuenta asociada (campo solo lectura)
            modal.find('#edit_descripcioncos').val(descripcioncos);
            modal.find('#edit_montocos').val(montocos);
            modal.find('#edit_fechacos').val(fechacos);

            // Cambiar la acción del formulario de edición a la ruta del costo específico
            var formAction = "{{ route('costos.update', '') }}/" + idcos;
            modal.find('#editCostoForm').attr('action', formAction);
        });
    </script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 en el modal cuando se abra
            $("#seleccionarCuentaModal").on("shown.bs.modal", function() {
                $("#idcue").select2({
                    dropdownParent: $(
                    "#seleccionarCuentaModal"), // Esto es clave para que funcione dentro del modal
                    placeholder: "Seleccione una cuenta",
                    allowClear: true,
                });
            });
        });
    </script>
@endsection
