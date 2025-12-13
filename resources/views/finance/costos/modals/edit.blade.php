<x-modal name="editar-costo">
    <div class="modal-header">
        <h5 class="modal-title">Editar Costo</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="editCostoForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="form-group mb-3">
                <label for="edit_idcue">Cuenta</label>
                <!-- Campo solo lectura para la cuenta -->
                <input type="text" id="edit_idcue" class="form-control" readonly>
            </div>
            <div class="form-group mb-3">
                <label for="edit_descripcioncos">Descripción</label>
                <input type="text" name="descripcioncos" id="edit_descripcioncos" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="edit_montocos">Monto</label>
                <input type="number" name="montocos" id="edit_montocos" class="form-control" step="0.01" required>
            </div>
            <div class="form-group mb-3">
                <label for="edit_fechacos">Fecha</label>
                <input type="date" name="fechacos" id="edit_fechacos" class="form-control">
            </div>
            <div class="form-group mb-3">
                <label for="edit_banco_id">Banco <span class="text-danger">*</span></label>
                <select id="edit_banco_id" name="banco_id" class="form-control searchable-select" required
                        data-placeholder="Seleccione un banco...">
                    <option value="">-- Selecciona un Banco --</option>
                    @foreach($bancos as $banco)
                        <option value="{{ $banco->idban }}">{{ $banco->nombreban }} ({{ ucfirst($banco->tipoban) }}) - ${{ number_format($banco->monto, 2) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</x-modal>
