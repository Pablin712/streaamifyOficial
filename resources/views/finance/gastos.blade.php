@extends('layouts.table')

@section('title', 'Gastos')

@section('h1', 'Gastos')

@section('descripcion')
    <h3>Gestión de Gastos</h3>
    <p>Aquí puedes ver todos los gastos asociados al negocio y registrar nuevos gastos. Si deseas ver los gastos de un tipo de gasto específico, selecciona un tipo de gasto en el modal.</p>
@endsection

@section('btncrear')
    <!-- Botón para abrir el modal de creación de gasto -->
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#seleccionarTipoGastoModal">
        Crear Gasto
    </button>
@endsection

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
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editarGastoModal" 
                            data-id="{{ $gasto->idgas }}"
                            data-idtip="{{ $gasto->idtip }}"
                            data-descripciongas="{{ $gasto->descripciongas }}"
                            data-montogas="{{ $gasto->montogas }}"
                            data-fechagas="{{ $gasto->fechagas }}">
                            Editar
                        </button>
                        <!-- Eliminar gasto -->
                        <form action="{{ route('gastos.destroy', $gasto->idgas) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

<!-- Modal para crear un nuevo gasto -->
<div class="modal fade" id="seleccionarTipoGastoModal" tabindex="-1" aria-labelledby="seleccionarTipoGastoModalLabel" aria-hidden="true">
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
                        <input type="date" name="fechagas" id="fechagas" class="form-control" value="{{ now()->format('Y-m-d') }}">
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
<div class="modal fade" id="editarGastoModal" tabindex="-1" aria-labelledby="editarGastoModalLabel" aria-hidden="true">
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


@section('scripts')
<script>
    // Función para llenar el formulario del modal con los datos del gasto a editar
    $('#editarGastoModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Botón que abrió el modal
        var idgas = button.data('id');
        var idtip = button.data('idtip'); // ID del tipo de gasto
        var descripciongas = button.data('descripciongas');
        var montogas = button.data('montogas');
        var fechagas = button.data('fechagas');

        var modal = $(this);
        modal.find('#edit_idtip').val(idtip); // Mostrar el ID del tipo de gasto (campo solo lectura)
        modal.find('#edit_descripciongas').val(descripciongas);
        modal.find('#edit_montogas').val(montogas);
        modal.find('#edit_fechagas').val(fechagas);

        // Cambiar la acción del formulario de edición a la ruta del gasto específico
        var formAction = "{{ route('gastos.update', '') }}/" + idgas;
        modal.find('#editGastoForm').attr('action', formAction);
    });
</script>
@endsection
