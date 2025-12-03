<x-modal name="editar-tipo-gasto">
    <div class="modal-header">
        <h5 class="modal-title">Editar Tipo de Gasto</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="editarTipoGastoForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <!-- Campo para el detalle del tipo de gasto -->
            <div class="form-group mb-3">
                <label for="edit_detalletip">Detalle del Tipo de Gasto</label>
                <input type="text" name="detalletip" id="edit_detalletip" class="form-control" required>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>
</x-modal>
