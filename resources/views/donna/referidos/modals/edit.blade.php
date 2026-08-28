<x-modal name="editReferralPartnerModal" :show="false" maxWidth="lg">
    <div class="modal-header modal-header-donna-blue">
        <h5 class="modal-title">
            <i class="bi bi-person-badge me-2"></i>Editar Partner de Referido
        </h5>
        <button type="button" class="btn-close btn-close-white"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editReferralPartnerModal' }))">
        </button>
    </div>
    <form id="editReferralPartnerForm" onsubmit="submitEditPartner(event)">
        @csrf
        <input type="hidden" id="edit_partner_id">
        <div class="modal-body">
            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label required"><i class="fas fa-user me-1"></i>Cliente (partner)</label>
                    <select name="client_id" id="edit_partner_client_id" class="form-control" required>
                        <option value="">— Selecciona un cliente —</option>
                        @foreach ($clientes as $cli)
                            <option value="{{ $cli->idcli }}">{{ $cli->nombrecli }} ({{ $cli->telefonocli }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label required"><i class="fas fa-barcode me-1"></i>Código de referido</label>
                    <input type="text" name="code" id="edit_partner_code" class="form-control font-monospace text-uppercase" maxlength="30" required>
                </div>

                <div class="col-md-6"></div>

                <div class="col-md-6">
                    <label class="form-label required"><i class="fas fa-tags me-1"></i>Descuento para el referido</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="discount_amount" id="edit_partner_discount" class="form-control" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label required"><i class="fas fa-hand-holding-dollar me-1"></i>Comisión del partner</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="commission_amount" id="edit_partner_commission" class="form-control" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label required"><i class="fas fa-toggle-on me-1"></i>Estado</label>
                    <select name="is_active" id="edit_partner_is_active" class="form-control" required>
                        <option value="1">Activo (el código funciona)</option>
                        <option value="0">Inactivo (código deshabilitado)</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label"><i class="fas fa-sticky-note me-1"></i>Notas internas</label>
                    <textarea name="notes" id="edit_partner_notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="col-12">
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Cambiar el descuento/comisión aquí solo afecta a <strong>nuevas</strong> activaciones.
                        Las suscripciones ya contratadas conservan los montos con los que se activaron.
                    </div>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editReferralPartnerModal' }))">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Guardar Cambios
            </button>
        </div>
    </form>
</x-modal>
