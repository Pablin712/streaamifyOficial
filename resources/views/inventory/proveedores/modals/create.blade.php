<x-modal name="createProveedorModal" :show="false" maxWidth="md">
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-truck me-2"></i>Nuevo Proveedor
        </h5>
        <button type="button" class="btn-close" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createProveedorModal' }))"></button>
    </div>
    <form id="createProveedorForm" onsubmit="if (typeof submitCreateProveedor === 'function') { submitCreateProveedor(event); } else if (typeof submitCreate === 'function') { submitCreate(event); }">
        @csrf
        <div class="modal-body">
            <div class="row g-3">
                <!-- Nombre del Proveedor -->
                <div class="col-12">
                    <label for="create_nombrepro" class="form-label required">
                        <i class="fas fa-user me-1"></i>Nombre del Proveedor
                    </label>
                    <input type="text"
                           class="form-control"
                           id="create_nombrepro"
                           name="nombrepro"
                           maxlength="20"
                           required
                           placeholder="Ej: Juan Pérez">
                    <small class="text-muted">Máximo 20 caracteres</small>
                </div>

                <!-- Teléfono -->
                <div class="col-12">
                    <label for="create_telefonopro" class="form-label">
                        <i class="fas fa-phone me-1"></i>Teléfono
                    </label>
                    <input type="text"
                           class="form-control"
                           id="create_telefonopro"
                           name="telefonopro"
                           maxlength="15"
                           placeholder="Ej: 0999123456">
                    <small class="text-muted">Máximo 15 caracteres (opcional)</small>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createProveedorModal' }))">
                <i class="fas fa-times me-1"></i>Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Guardar Proveedor
            </button>
        </div>
    </form>
</x-modal>
