<x-modal name="editProveedorModal" :show="false" maxWidth="md">
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-edit me-2"></i>Editar Proveedor
        </h5>
        <button type="button" class="btn-close" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editProveedorModal' }))"></button>
    </div>
    <form id="editProveedorForm" onsubmit="submitEdit(event)">
        @csrf
        @method('PUT')
        <input type="hidden" id="edit_idpro" name="idpro">

        <div class="modal-body">
            <!-- ID Display (readonly) -->
            <div class="mb-3">
                <label class="form-label text-muted">
                    <i class="fas fa-hashtag me-1"></i>ID del Proveedor
                </label>
                <input type="text"
                       class="form-control bg-light"
                       id="edit_idpro_display"
                       readonly>
            </div>

            <div class="row g-3">
                <!-- Nombre del Proveedor -->
                <div class="col-12">
                    <label for="edit_nombrepro" class="form-label required">
                        <i class="fas fa-user me-1"></i>Nombre del Proveedor
                    </label>
                    <input type="text"
                           class="form-control"
                           id="edit_nombrepro"
                           name="nombrepro"
                           maxlength="20"
                           required
                           placeholder="Ej: Juan Pérez">
                    <small class="text-muted">Máximo 20 caracteres</small>
                </div>

                <!-- Teléfono -->
                <div class="col-12">
                    <label for="edit_telefonopro" class="form-label">
                        <i class="fas fa-phone me-1"></i>Teléfono
                    </label>
                    <input type="text"
                           class="form-control"
                           id="edit_telefonopro"
                           name="telefonopro"
                           maxlength="15"
                           placeholder="Ej: 0999123456">
                    <small class="text-muted">Máximo 15 caracteres (opcional)</small>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editProveedorModal' }))">
                <i class="fas fa-times me-1"></i>Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Actualizar Proveedor
            </button>
        </div>
    </form>
</x-modal>
