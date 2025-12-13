<x-modal name="editar-banco" maxWidth="md">
    <x-slot name="title">
        <i class="fas fa-edit"></i> Editar Banco
    </x-slot>

    <form id="editarBancoForm" method="POST" action="" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="mb-3">
                <label for="edit_nombreban" class="form-label">Nombre del Banco <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_nombreban" name="nombreban" required>
            </div>

            <div class="mb-3">
                <label for="edit_tipoban" class="form-label">Tipo <span class="text-danger">*</span></label>
                <select class="form-select" id="edit_tipoban" name="tipoban" required>
                    <option value="">Seleccione un tipo</option>
                    <option value="Ahorros">Ahorros</option>
                    <option value="Corriente">Corriente</option>
                    <option value="Efectivo">Efectivo</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="edit_detalleban" class="form-label">Descripción</label>
                <textarea class="form-control" id="edit_detalleban" name="detalleban" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label for="edit_foto" class="form-label">Foto</label>
                <input type="file" class="form-control" id="edit_foto" name="foto" accept="image/*">
                <small class="text-muted">Sube una nueva imagen del banco (opcional)</small>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Nota:</strong> El monto del banco no se puede editar directamente. Se actualiza automáticamente con las transacciones.
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editar-banco' }))">
                Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
        </div>
    </form>
</x-modal>
