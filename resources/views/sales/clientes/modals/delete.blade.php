<x-modal name="deleteClienteModal" :show="false" maxWidth="md">
    <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
            <i class="fas fa-trash-alt me-2"></i>Eliminar Cliente
        </h5>
        <button type="button" class="btn-close btn-close-white" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'deleteClienteModal' }))"></button>
    </div>
    <form onsubmit="submitDelete(event)">
        @csrf
        @method('DELETE')
        <input type="hidden" id="delete_cliente_id">
        <div class="modal-body">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>¿Estás seguro de que deseas eliminar este cliente?</strong>
                <p class="mb-0 mt-2">Esta acción no se puede deshacer.</p>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-3">Información del Cliente:</h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <strong><i class="fas fa-hashtag me-1"></i>ID:</strong>
                            <span id="delete_cliente_idcli"></span>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-user me-1"></i>Nombre:</strong>
                            <span id="delete_cliente_nombre"></span>
                        </div>
                        <div class="col-md-12">
                            <strong><i class="fas fa-phone me-1"></i>Teléfono:</strong>
                            <span id="delete_cliente_telefono"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Eliminar Cliente
            </button>
        </div>
    </form>
</x-modal>
