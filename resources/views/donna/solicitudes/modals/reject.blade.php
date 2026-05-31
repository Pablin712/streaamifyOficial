<x-modal name="rejectDonnaReqModal" :show="false" maxWidth="md">
    <div class="modal-header modal-header-danger">
        <h5 class="modal-title">
            <i class="fas fa-times-circle me-2"></i>Rechazar Solicitud Donna
        </h5>
        <button type="button" class="btn-close btn-close-white"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'rejectDonnaReqModal' }))">
        </button>
    </div>
    <form id="rejectDonnaReqForm" onsubmit="submitRejectReq(event)">
        @csrf
        <input type="hidden" id="reject_req_id">
        <div class="modal-body">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Vas a rechazar la solicitud de <strong id="reject_req_cliente"></strong>.
                El cliente no será notificado automáticamente.
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-comment me-1"></i>Motivo del rechazo</label>
                <textarea name="employee_notes" id="reject_employee_notes" class="form-control" rows="2"
                    placeholder="Ej: Saldo pendiente, información incompleta..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'rejectDonnaReqModal' }))">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-times me-1"></i> Rechazar
            </button>
        </div>
    </form>
</x-modal>
