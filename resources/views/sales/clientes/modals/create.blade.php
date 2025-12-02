<x-modal name="createClienteModal" :show="false" maxWidth="md">
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-user-plus me-2"></i>Crear Nuevo Cliente
        </h5>
        <button type="button" class="btn-close" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createClienteModal' }))"></button>
    </div>
    <form id="createClienteForm" onsubmit="submitCreate(event)">
        @csrf
        <div class="modal-body">
            <div class="row g-3">
                <!-- Nombre -->
                <div class="col-md-12">
                    <label for="create_nombrecli" class="form-label required">
                        <i class="fas fa-user me-1"></i>Nombre del Cliente
                    </label>
                    <input type="text" class="form-control" id="create_nombrecli" name="nombrecli" maxlength="20" required>
                </div>

                <!-- Teléfono -->
                <div class="col-md-12">
                    <label for="create_telefonocli" class="form-label required">
                        <i class="fas fa-phone me-1"></i>Teléfono
                    </label>
                    <input type="text" class="form-control" id="create_telefonocli" name="telefonocli" maxlength="50" required>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Crear Cliente
            </button>
        </div>
    </form>
</x-modal>
