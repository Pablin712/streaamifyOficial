<x-modal name="editar-gasto">
    <div class="modal-header">
        <h5 class="modal-title">Editar Gasto</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="editarGastoForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <!-- Selector de Tipo de Gasto -->
            <div class="form-group mb-3">
                <label for="edit_idtip">Tipo de Gasto</label>
                <select id="edit_idtip" name="idtip" class="form-control searchable-select" required
                        data-placeholder="Seleccione un tipo de gasto...">
                    <option value="">-- Selecciona un Tipo de Gasto --</option>
                    @foreach($tipoGastos as $tipo)
                        <option value="{{ $tipo->idtip }}">{{ $tipo->detalletip }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Campos de Gasto -->
            <div class="form-group mb-3">
                <label for="edit_descripciongas">Descripción</label>
                <input type="text" name="descripciongas" id="edit_descripciongas" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="edit_montogas">Monto</label>
                <input type="number" name="montogas" id="edit_montogas" class="form-control" step="0.01" required>
            </div>
            <div class="form-group mb-3">
                <label for="edit_fechagas">Fecha</label>
                <input type="date" name="fechagas" id="edit_fechagas" class="form-control">
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>
</x-modal>
