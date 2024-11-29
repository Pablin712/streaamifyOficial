@extends('layouts.table')

@section('title', 'Gastos')

@section('h1', 'Gastos')

@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <h3>Gestión de Gastos</h3>
    <h4>Iniciado por Pablo Jiménez, terminado por Andrés Rincón</h4>
    <p>Aquí puedes ver todos los gastos asociados al negocio y registrar nuevos gastos. Si deseas ver los gastos de un tipo
        de gasto específico, selecciona un tipo de gasto en el modal.</p>
@endsection

@section('btncrear')
    <!-- Botón para abrir el modal de creación de gasto -->
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#seleccionarTipoGastoModal">
        Crear Gasto
    </button>
@endsection
@section('tablename', 'Gastos')
@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tipo de Gasto</th>
                <th>Fecha</th>
                <th>Descripción</th>
                <th>Monto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($gastos as $gasto)
                <tr>
                    <td>{{ $gasto->idgas }}</td>
                    <td>{{ $gasto->tipoGasto->detalletip }}</td>
                    <td>{{ \Carbon\Carbon::parse($gasto->fechagas)->format('d/m/Y') }}</td>
                    <td>{{ $gasto->descripciongas }}</td>
                    <td>${{ number_format($gasto->montogas, 2) }}</td>
                    <td>
                        <!-- Editar gasto (abre el modal con los datos del gasto) -->
                        <button type="button" class="btn btn-warning fas fa-edit" data-bs-toggle="modal"
                            data-bs-target="#editarGastoModal" data-id="{{ $gasto->idgas }}"
                            data-idtip="{{ $gasto->idtip }}" data-descripciongas="{{ $gasto->descripciongas }}"
                            data-montogas="{{ $gasto->montogas }}" data-fechagas="{{ $gasto->fechagas }}">

                            Editar
                        </button>
                        <!-- Eliminar gasto -->
                        <form action="{{ route('gastos.destroy', $gasto->idgas) }}" method="POST"
                            style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro?')"><i
                                    class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
@section('table2')
    <div class="card mb-4">
        <div class="card-body">
            <h3>Gestión de Tipos de Gastos</h3>
            <h4>Realizado por Pablo Jiménez</h4>
            <p>Aquí puedes ver todos los tipos de gastos, describe el tipo de gasto en el modal.</p>
            <div class="form-group mb-3">
                <!-- Botón para abrir el modal -->
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#crearTipoGastoModal">Crear Tipo de
                    Gasto</button>
            </div>
        </div>
    </div>
    <div id="tabla-tipogasto" class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tipos de gasto
        </div>
        <div class="card-body">
            {{-- aqui empieza la tabla, se modifica, en cualquier tabla
        se debe poner con style id="datatablesSimple"
        example: <table id="datatablesSimple"> --}}
            <table id="datatablesSimple" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Detalle</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tipoGastos as $tipoGasto)
                        <tr>
                            <td>{{ $tipoGasto->idtip }}</td>
                            <td>{{ $tipoGasto->detalletip }}</td>
                            <td>
                                <!-- Editar Tipo de Gasto -->
                                <button type="button" class="btn btn-warning fas fa-edit" data-bs-toggle="modal"
                                    data-bs-target="#editarTipoGastoModal" data-idtip="{{ $tipoGasto->idtip }}"
                                    data-detalletip="{{ $tipoGasto->detalletip }}">
                                    Editar
                                </button>

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
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
                        <input type="number" name="montogas" id="montogas" class="form-control" step="0.01"
                            required>
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
                    @method('PUT') <!-- Asegúrate de incluir esto para el método PUT -->
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
                        <input type="text" name="descripciongas" id="edit_descripciongas" class="form-control"
                            required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="montogas">Monto</label>
                        <input type="number" name="montogas" id="edit_montogas" class="form-control"
                            step="0.01" required>
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
                    @method('PUT') <!-- Asegúrate de incluir esto para el método PUT -->

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






{{-- 

scripts

--}}
@section('scripts')
    <script>
        $(document).ready(function() {
            // Función para rellenar el formulario del modal al abrirlo
            $('#editarGastoModal').on('show.bs.modal', function(event) {
                // Obtén el elemento que activó el modal (el SVG en este caso)
                var button = $(event.relatedTarget);

                // Extraer los datos del atributo data-*
                var idgas = button.data('id');
                var idtip = button.data('idtip');
                var descripciongas = button.data('descripciongas');
                var montogas = button.data('montogas');
                var fechagas = button.data('fechagas');

                // Imprime en la consola para depuración
                console.log('ID Gasto:', idgas);
                console.log('ID Tipo:', idtip);
                console.log('Descripción:', descripciongas);
                console.log('Monto:', montogas);
                console.log('Fecha:', fechagas);

                // Obtener el modal y rellenar los campos
                var modal = $(this);
                modal.find('#edit_idtip').val(idtip);
                modal.find('#edit_descripciongas').val(descripciongas);
                modal.find('#edit_montogas').val(montogas);
                modal.find('#edit_fechagas').val(fechagas);

                // Cambiar la acción del formulario para enviar al endpoint correcto
                var formAction = "{{ route('gastos.update', '') }}/" + idgas;
                modal.find('#editarGastoForm').attr('action', formAction);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Función para rellenar el formulario del modal al abrirlo (Editar Tipo de Gasto)
            $('#editarTipoGastoModal').on('show.bs.modal', function(event) {
                // Obtén el elemento que activó el modal (el botón de editar tipo de gasto)
                var button = $(event.relatedTarget);

                // Extraer los datos del atributo data-*
                var idtip = button.data('idtip');
                var detalletip = button.data('detalletip');

                // Imprime en la consola para depuración
                console.log('ID Tipo de Gasto:', idtip);
                console.log('Detalle Tipo de Gasto:', detalletip);

                // Obtener el modal y rellenar los campos
                var modal = $(this);
                modal.find('#edit-detalletip').val(detalletip);

                // Cambiar la acción del formulario para enviar al endpoint correcto
                var formAction = "{{ route('tipos.update', '') }}/" + idtip;
                modal.find('#editarTipoGastoForm').attr('action', formAction);
            });
        });
    </script>



@endsection
