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
            <div class="form-check mb-2">
                <input type="checkbox" name="excluir_de_ganancia" id="edit_excluir_de_ganancia" class="form-check-input" value="1">
                <label class="form-check-label" for="edit_excluir_de_ganancia">
                    Excluir de la utilidad del negocio
                </label>
                <div class="form-text">Úsalo para pagos que no son gasto operativo (ej: pago de personal, tu retiro/sueldo como dueño). No se restará de la ganancia mostrada en el dashboard, solo aparecerá como dato informativo aparte.</div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>
</x-modal>
