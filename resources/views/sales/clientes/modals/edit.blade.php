<x-modal name="editClienteModal" :show="false" maxWidth="md">
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-user-edit me-2"></i>Editar Cliente
        </h5>
        <button type="button" class="btn-close" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editClienteModal' }))"></button>
    </div>
    <form id="editClienteForm" onsubmit="submitEdit(event)">
        @csrf
        @method('PUT')
        <input type="hidden" name="idcli" id="edit_cliente_id">
        <div class="modal-body">
            <div class="row g-3">
                <!-- Nombre -->
                <div class="col-md-12">
                    <label for="edit_nombrecli" class="form-label required">
                        <i class="fas fa-user me-1"></i>Nombre del Cliente
                    </label>
                    <input type="text" class="form-control" id="edit_nombrecli" name="nombrecli" maxlength="20" required>
                </div>

                <!-- Teléfono -->
                <div class="col-md-12">
                    <label for="edit_telefonocli" class="form-label required">
                        <i class="fas fa-phone me-1"></i>Teléfono
                    </label>
                    <input type="text" class="form-control" id="edit_telefonocli" name="telefonocli" maxlength="20" required>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Cliente
            </button>
        </div>
    </form>
</x-modal>
