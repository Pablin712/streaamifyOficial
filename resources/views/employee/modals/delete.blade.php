<x-modal name="deleteEmpleadoModal" :show="false" maxWidth="md">
    <div class="modal-header modal-header-danger">
        <h5 class="modal-title">
            <i class="fas fa-trash-alt me-2"></i>Eliminar Empleado
        </h5>
        <button type="button" class="btn-close btn-close-white" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'deleteEmpleadoModal' }))"></button>
    </div>
    <form onsubmit="submitDelete(event)">
        @csrf
        @method('DELETE')
        <input type="hidden" id="delete_empleado_id">
        <div class="modal-body">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>¿Estás seguro de que deseas eliminar este empleado?</strong>
                <p class="mb-0 mt-2">Esta acción no se puede deshacer.</p>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-3">Información del Empleado:</h6>
                    <div class="text-center mb-3">
                        <div id="delete_empleado_foto"></div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-12">
                            <strong><i class="fas fa-user me-1"></i>Nombre:</strong>
                            <span id="delete_empleado_nombre"></span>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-phone me-1"></i>Teléfono:</strong>
                            <span id="delete_empleado_telefono"></span>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-user-circle me-1"></i>Usuario:</strong>
                            <span id="delete_empleado_usuario"></span>
                        </div>
                        <div class="col-md-12">
                            <strong><i class="fas fa-envelope me-1"></i>Email:</strong>
                            <span id="delete_empleado_email"></span>
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
                <i class="fas fa-trash"></i> Eliminar Empleado
            </button>
        </div>
    </form>
</x-modal>
