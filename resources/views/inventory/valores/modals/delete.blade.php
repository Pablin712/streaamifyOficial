<x-modal name="deleteValorModal" :show="false" maxWidth="sm">
    <div class="modal-header modal-header-danger">
        <h5 class="modal-title">
            <i class="fas fa-trash-alt me-2"></i>Eliminar Valor
        </h5>
        <button type="button" class="btn-close btn-close-white" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'deleteValorModal' }))"></button>
    </div>
    <form id="deleteValorForm" onsubmit="confirmDelete(event)">
        @csrf
        @method('DELETE')
        <input type="hidden" id="delete_idval" name="idval">

        <div class="modal-body">
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    <strong>¡Advertencia!</strong> Esta acción desactivará el valor.
                </div>
            </div>

            <p class="mb-3">¿Está seguro que desea eliminar el siguiente valor?</p>

            <div class="card">
                <div class="card-body">
                    <p class="mb-2">
                        <strong><i class="fas fa-hashtag me-1"></i>ID:</strong>
                        <span id="delete_idval_display"></span>
                    </p>
                    <p class="mb-2">
                        <strong><i class="fas fa-tv me-1"></i>Servicio:</strong>
                        <span id="delete_servicio_display"></span>
                    </p>
                    <p class="mb-2">
                        <strong><i class="fas fa-truck me-1"></i>Proveedor:</strong>
                        <span id="delete_proveedor_display"></span>
                    </p>
                    <p class="mb-2">
                        <strong><i class="fas fa-dollar-sign me-1"></i>Costo:</strong>
                        $<span id="delete_costo_display"></span>
                    </p>
                    <p class="mb-0">
                        <strong><i class="fas fa-tag me-1"></i>Tipo:</strong>
                        <span id="delete_tipo_display"></span>
                    </p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'deleteValorModal' }))">
                <i class="fas fa-times me-1"></i>Cancelar
            </button>
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash-alt me-1"></i>Sí, Eliminar
            </button>
        </div>
    </form>
</x-modal>
