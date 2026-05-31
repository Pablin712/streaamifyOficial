<x-modal name="renewDonnaSubModal" :show="false" maxWidth="md">
    <div class="modal-header modal-header-success">
        <h5 class="modal-title">
            <i class="fas fa-redo me-2"></i>Renovar Suscripción
        </h5>
        <button type="button" class="btn-close btn-close-white"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'renewDonnaSubModal' }))">
        </button>
    </div>
    <form id="renewDonnaSubForm" onsubmit="submitRenewSub(event)">
        @csrf
        <input type="hidden" id="renew_sub_id">
        <div class="modal-body">
            <p class="mb-3">
                Renovando suscripción de: <strong id="renew_cliente_nombre"></strong>
            </p>
            <div class="mb-3">
                <label class="form-label required"><i class="fas fa-calendar me-1"></i>Nueva fecha de vencimiento</label>
                <input type="date" name="expires_at" id="renew_expires_at" class="form-control" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'renewDonnaSubModal' }))">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-redo me-1"></i> Renovar
            </button>
        </div>
    </form>
</x-modal>
