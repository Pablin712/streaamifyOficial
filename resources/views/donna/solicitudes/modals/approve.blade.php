<x-modal name="approveDonnaReqModal" :show="false" maxWidth="md">
    <div class="modal-header modal-header-success">
        <h5 class="modal-title">
            <i class="fas fa-check-circle me-2"></i>Aprobar Solicitud Donna
        </h5>
        <button type="button" class="btn-close btn-close-white"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'approveDonnaReqModal' }))">
        </button>
    </div>
    <form id="approveDonnaReqForm" onsubmit="submitApproveReq(event)">
        @csrf
        <input type="hidden" id="approve_req_id">
        <div class="modal-body">
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted">Cliente</small><br>
                            <strong id="approve_req_cliente"></strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Plan</small><br>
                            <strong id="approve_req_plan"></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="fas fa-calendar-times me-1"></i>Fecha de vencimiento</label>
                <input type="date" name="expires_at" id="approve_expires_at" class="form-control">
                <small class="text-muted">Dejar vacío para usar el ciclo del plan automáticamente.</small>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="fas fa-sticky-note me-1"></i>Notas internas</label>
                <textarea name="employee_notes" id="approve_employee_notes" class="form-control" rows="2"
                    placeholder="Método de pago, observaciones..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'approveDonnaReqModal' }))">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check me-1"></i> Aprobar y Activar
            </button>
        </div>
    </form>
</x-modal>
