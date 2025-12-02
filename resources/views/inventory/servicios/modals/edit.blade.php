<x-modal name="edit-servicio" maxWidth="lg" :closeable="true">
    <div class="modal-header p-4">
        <h5 class="modal-title fw-bold">
            <i class="fas fa-edit"></i> Editar Servicio
        </h5>
        <button type="button" class="btn-close" x-on:click="$dispatch('close-modal', 'edit-servicio')"></button>
    </div>
    <form id="edit-form" onsubmit="submitEdit(event)">
        <div class="modal-body p-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-idser" name="idser">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="edit-idser-display" class="form-label fw-semibold">
                        <i class="fas fa-barcode"></i> ID del Servicio
                    </label>
                    <input type="text" id="edit-idser-display" class="form-control" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="edit-nombreser" class="form-label fw-semibold">
                        <i class="fas fa-signature"></i> Nombre del Servicio
                    </label>
                    <input type="text" name="nombreser" id="edit-nombreser" class="form-control" maxlength="20" required>
                    <small class="text-muted">Máximo 20 caracteres</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="edit-completoser" class="form-label fw-semibold">
                        <i class="fas fa-dollar-sign"></i> Precio Completo
                    </label>
                    <input type="number" name="completoser" id="edit-completoser" class="form-control" step="0.01" min="0">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="edit-precioser" class="form-label fw-semibold">
                        <i class="fas fa-dollar-sign"></i> Precio Individual
                    </label>
                    <input type="number" name="precioser" id="edit-precioser" class="form-control" step="0.01" min="0">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="edit-comboser" class="form-label fw-semibold">
                        <i class="fas fa-tags"></i> Precio Combo
                    </label>
                    <input type="number" name="comboser" id="edit-comboser" class="form-control" step="0.01" min="0">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="edit-reventaser" class="form-label fw-semibold">
                        <i class="fas fa-tv"></i> Reventa Pantalla
                    </label>
                    <input type="number" name="reventaser" id="edit-reventaser" class="form-control" step="0.01" min="0">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="edit-revcompser" class="form-label fw-semibold">
                        <i class="fas fa-user-circle"></i> Reventa Cuenta
                    </label>
                    <input type="number" name="revcompser" id="edit-revcompser" class="form-control" step="0.01" min="0">
                </div>
            </div>
        </div>
        <div class="modal-footer p-4">
            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'edit-servicio')">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save"></i> Actualizar
            </button>
        </div>
    </form>
</x-modal>
