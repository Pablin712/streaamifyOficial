<x-modal name="createServicioModal" maxWidth="lg" :closeable="true">
    <div class="modal-header p-4">
        <h5 class="modal-title fw-bold">
            <i class="fas fa-plus-circle"></i> Crear Servicio
        </h5>
        <button type="button" class="btn-close" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createServicioModal' }))"></button>
    </div>
    <form id="createServicioForm" onsubmit="if (typeof submitCreateServicio === 'function') { submitCreateServicio(event); } else if (typeof submitCreate === 'function') { submitCreate(event); }">
        <div class="modal-body p-4">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="create-idser" class="form-label fw-semibold">
                        <i class="fas fa-barcode"></i> ID del Servicio
                    </label>
                    <input type="text" name="idser" id="create-idser" class="form-control" maxlength="10" required placeholder="Ej: NETFLIX">
                    <small class="text-muted">Máximo 10 caracteres</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="create-nombreser" class="form-label fw-semibold">
                        <i class="fas fa-signature"></i> Nombre del Servicio
                    </label>
                    <input type="text" name="nombreser" id="create-nombreser" class="form-control" maxlength="20" required placeholder="Ej: Netflix">
                    <small class="text-muted">Máximo 20 caracteres</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="create-completoser" class="form-label fw-semibold">
                        <i class="fas fa-dollar-sign"></i> Precio Completo
                    </label>
                    <input type="number" name="completoser" id="create-completoser" class="form-control" step="0.01" min="0" placeholder="0.00">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="create-precioser" class="form-label fw-semibold">
                        <i class="fas fa-dollar-sign"></i> Precio Individual
                    </label>
                    <input type="number" name="precioser" id="create-precioser" class="form-control" step="0.01" min="0" placeholder="0.00">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="create-comboser" class="form-label fw-semibold">
                        <i class="fas fa-tags"></i> Precio Combo
                    </label>
                    <input type="number" name="comboser" id="create-comboser" class="form-control" step="0.01" min="0" placeholder="0.00">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="create-reventaser" class="form-label fw-semibold">
                        <i class="fas fa-tv"></i> Reventa Pantalla
                    </label>
                    <input type="number" name="reventaser" id="create-reventaser" class="form-control" step="0.01" min="0" placeholder="0.00">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="create-revcompser" class="form-label fw-semibold">
                        <i class="fas fa-user-circle"></i> Reventa Cuenta
                    </label>
                    <input type="number" name="revcompser" id="create-revcompser" class="form-control" step="0.01" min="0" placeholder="0.00">
                </div>
            </div>
        </div>
        <div class="modal-footer p-4">
            <button type="button" class="btn btn-secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createServicioModal' }))">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </form>
</x-modal>
