<x-modal name="suspendDonnaSubModal" :show="false" maxWidth="md">
    <div class="modal-header bg-secondary text-white">
        <h5 class="modal-title">
            <i class="fas fa-ban me-2"></i>Suspender Suscripción
        </h5>
        <button type="button" class="btn-close btn-close-white"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'suspendDonnaSubModal' }))">
        </button>
    </div>
    <form id="suspendDonnaSubForm" onsubmit="submitSuspendSub(event)">
        @csrf
        <input type="hidden" id="suspend_sub_id">
        <div class="modal-body">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Suspender la suscripción de <strong id="suspend_cliente_nombre"></strong>
                desactivará el acceso a Donna de este cliente.
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-comment me-1"></i>Razón (opcional)</label>
                <textarea name="reason" id="suspend_reason" class="form-control" rows="2"
                    placeholder="Ej: Falta de pago, solicitud del cliente..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'suspendDonnaSubModal' }))">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-dark">
                <i class="fas fa-ban me-1"></i> Suspender
            </button>
        </div>
    </form>
</x-modal>
