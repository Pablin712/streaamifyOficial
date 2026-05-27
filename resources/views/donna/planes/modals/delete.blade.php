<x-modal name="deleteDonnaPlanModal" :show="false" maxWidth="md">
    <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
            <i class="fas fa-trash-alt me-2"></i>Eliminar Plan Donna
        </h5>
        <button type="button" class="btn-close btn-close-white"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'deleteDonnaPlanModal' }))">
        </button>
    </div>
    <form id="deleteDonnaPlanForm" onsubmit="submitDeletePlan(event)">
        @csrf
        @method('DELETE')
        <input type="hidden" id="delete_plan_id">
        <div class="modal-body">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>¿Eliminar este plan?</strong>
                <p class="mb-0 mt-1">Esta acción no se puede deshacer.</p>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <strong>Plan:</strong><br>
                            <span id="delete_plan_nombre"></span>
                        </div>
                        <div class="col-6">
                            <strong>Precio:</strong><br>
                            <span id="delete_plan_precio"></span>
                        </div>
                        <div class="col-6">
                            <strong>Tipo:</strong><br>
                            <span id="delete_plan_tipo"></span>
                        </div>
                        <div class="col-6">
                            <strong>Estado:</strong><br>
                            <span id="delete_plan_estado"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'deleteDonnaPlanModal' }))">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash-alt me-1"></i> Eliminar Plan
            </button>
        </div>
    </form>
</x-modal>
