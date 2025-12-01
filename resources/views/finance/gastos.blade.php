@extends('layouts.table')

@section('title', 'Gastos')
@section('styles')
    <link rel="icon" href="{{ asset('images/Icono.png') }}" type="image/x-icon">
@endsection
@section('h1', 'Gastos')
@section('breadcrumb')
    Gastos
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Aquí puedes ver todos los gastos asociados al negocio y registrar nuevos gastos. Si deseas ver los gastos de un tipo
        de gasto específico, selecciona un tipo de gasto en el modal.</p>
@endsection

@section('btncrear')
    <!-- Botón para abrir el modal de creación de gasto -->
    @if (Auth::user()->hasPermissionTo('gastos.store'))
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#seleccionarTipoGastoModal">
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
                                <button type="button" class="btn btn-warning fas fa-edit" data-bs-toggle="modal"
                                    data-bs-target="#editarGastoModal" data-id="{{ $gasto->idgas }}"
                                    data-idtip="{{ $gasto->idtip }}" data-descripciongas="{{ $gasto->descripciongas }}"
                                    data-montogas="{{ $gasto->montogas }}" data-fechagas="{{ $gasto->fechagas }}">
                                    Editar
                                </button>
                            @endif
                            @if (Auth::user()->hasPermissionTo('gastos.destroy'))
                                <!-- Eliminar gasto -->
                                <form action="{{ route('gastos.destroy', $gasto->idgas) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('¿Estás seguro?')"><i class="fas fa-trash"></i></button>
                                </form>
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
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#crearTipoGastoModal">
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
                            @if (Auth::user()->hasAnyPermission(['tipos.update', 'tipos.destroy']))
                                <td>
                                    @if (Auth::user()->hasPermissionTo('tipos.update'))
                                        <!-- Editar Tipo de Gasto -->
                                        <button type="button" class="btn btn-warning fas fa-edit" data-bs-toggle="modal"
                                            data-bs-target="#editarTipoGastoModal" data-idtip="{{ $tipoGasto->idtip }}"
                                            data-detalletip="{{ $tipoGasto->detalletip }}">
                                            Editar
                                        </button>
                                    @endif
                                    @if (Auth::user()->hasPermissionTo('tipos.destroy'))
                                        <!-- Eliminar Tipo de Gasto -->
                                        <form action="{{ route('tipos.destroy', $tipoGasto->idtip) }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger"
                                                onclick="return confirm('¿Estás seguro?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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

<!-- Modal para crear un nuevo gasto -->
<div class="modal fade" id="seleccionarTipoGastoModal" tabindex="-1" aria-labelledby="seleccionarTipoGastoModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="seleccionarTipoGastoModalLabel">Seleccionar Tipo de Gasto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('gastos.store') }}" method="POST">
                    @csrf
                    <!-- Selector de Tipo de Gasto -->
                    <div class="form-group mb-3">
                        <label for="idtip">Seleccionar Tipo de Gasto</label>
                        <select name="idtip" id="idtip" class="form-control" required>
                            <option value="">-- Selecciona un Tipo de Gasto --</option>
                            @foreach ($tipoGastos as $tipoGasto)
                                <option value="{{ $tipoGasto->idtip }}">
                                    {{ $tipoGasto->detalletip }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Campos del Gasto -->
                    <div class="form-group mb-3">
                        <label for="descripciongas">Descripción</label>
                        <input type="text" name="descripciongas" id="descripciongas" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="montogas">Monto</label>
                        <input type="number" name="montogas" id="montogas" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="fechagas">Fecha</label>
                        <input type="date" name="fechagas" id="fechagas" class="form-control"
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

<!-- Modal para Editar Gasto -->
<div class="modal fade" id="editarGastoModal" tabindex="-1" aria-labelledby="editarGastoModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarGastoModalLabel">Editar Gasto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="#" method="POST" id="editarGastoForm">
                    @csrf
                    @method('PUT')
                    <!-- Selector de Tipo de Gasto -->
                    <div class="form-group mb-3">
                        <label for="idtip">Seleccionar Tipo de Gasto</label>
                        <select name="idtip" id="edit_idtip" class="form-control" required>
                            <option value="">-- Selecciona un Tipo de Gasto --</option>
                            @foreach ($tipoGastos as $tipoGasto)
                                <option value="{{ $tipoGasto->idtip }}">
                                    {{ $tipoGasto->detalletip }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Campos de Gasto -->
                    <div class="form-group mb-3">
                        <label for="descripciongas">Descripción</label>
                        <input type="text" name="descripciongas" id="edit_descripciongas" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="montogas">Monto</label>
                        <input type="number" name="montogas" id="edit_montogas" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="fechagas">Fecha</label>
                        <input type="date" name="fechagas" id="edit_fechagas" class="form-control">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear un nuevo Tipo de Gasto -->
<div class="modal fade" id="crearTipoGastoModal" tabindex="-1" aria-labelledby="crearTipoGastoModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crearTipoGastoModalLabel">Crear Tipo de Gasto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('tipos.store') }}" method="POST">
                    @csrf
                    <!-- Campo para el detalle del tipo de gasto -->
                    <div class="form-group mb-3">
                        <label for="detalletip">Detalle del Tipo de Gasto</label>
                        <input type="text" name="detalletip" id="detalletip" class="form-control" required>
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

<!-- Modal para Editar Tipo de Gasto -->
<div class="modal fade" id="editarTipoGastoModal" tabindex="-1" aria-labelledby="editarTipoGastoModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarTipoGastoModalLabel">Editar Tipo de Gasto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="#" method="POST" id="editarTipoGastoForm">
                    @csrf
                    @method('PUT')
                    <!-- Campo para el detalle del tipo de gasto -->
                    <div class="form-group mb-3">
                        <label for="edit-detalletip">Detalle del Tipo de Gasto</label>
                        <input type="text" name="detalletip" id="edit-detalletip" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
@section('scripts')
    <script>
        $(document).ready(function() {
            // Función para rellenar el formulario del modal al abrirlo (Editar Gasto)
            $('#editarGastoModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var idgas = button.data('id');
                var idtip = button.data('idtip');
                var descripciongas = button.data('descripciongas');
                var montogas = button.data('montogas');
                var fechagas = button.data('fechagas');

                console.log('ID Gasto:', idgas);
                console.log('ID Tipo:', idtip);
                console.log('Descripción:', descripciongas);
                console.log('Monto:', montogas);
                console.log('Fecha:', fechagas);

                var modal = $(this);
                modal.find('#edit_idtip').val(idtip);
                modal.find('#edit_descripciongas').val(descripciongas);
                modal.find('#edit_montogas').val(montogas);
                modal.find('#edit_fechagas').val(fechagas);

                var formAction = "{{ route('gastos.update', '') }}/" + idgas;
                modal.find('#editarGastoForm').attr('action', formAction);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Función para rellenar el formulario del modal al abrirlo (Editar Tipo de Gasto)
            $('#editarTipoGastoModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var idtip = button.data('idtip');
                var detalletip = button.data('detalletip');

                console.log('ID Tipo de Gasto:', idtip);
                console.log('Detalle Tipo de Gasto:', detalletip);

                var modal = $(this);
                modal.find('#edit-detalletip').val(detalletip);

                var formAction = "{{ route('tipos.update', '') }}/" + idtip;
                modal.find('#editarTipoGastoForm').attr('action', formAction);
            });
        });
    </script>

    {{-- Enhanced Table v2 --}}
    <script src="{{ asset('js/enhanced-table-v2.js') }}"></script>
@endsection
