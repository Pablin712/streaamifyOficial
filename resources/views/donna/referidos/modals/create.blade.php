<x-modal name="createReferralPartnerModal" :show="false" maxWidth="lg">
    <div class="modal-header modal-header-donna-blue">
        <h5 class="modal-title">
            <i class="bi bi-person-badge me-2"></i>Nuevo Partner de Referido
        </h5>
        <button type="button" class="btn-close btn-close-white"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createReferralPartnerModal' }))">
        </button>
    </div>
    <form id="createReferralPartnerForm" onsubmit="submitCreatePartner(event)">
        @csrf
        <div class="modal-body">
            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label required"><i class="fas fa-user me-1"></i>Cliente (partner)</label>
                    <select name="client_id" class="form-control" required>
                        <option value="">— Selecciona un cliente —</option>
                        @foreach ($clientes as $cli)
                            <option value="{{ $cli->idcli }}">{{ $cli->nombrecli }} ({{ $cli->telefonocli }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Su comisión se acredita a este cliente (saldo Streamify).</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label required"><i class="fas fa-barcode me-1"></i>Código de referido</label>
                    <input type="text" name="code" class="form-control font-monospace text-uppercase"
                        placeholder="ESTEBAN10" maxlength="30" required>
                    <small class="text-muted">Lo ingresa el cliente referido al contratar Donna.</small>
                </div>

                <div class="col-md-6"></div>

                <div class="col-md-6">
                    <label class="form-label required"><i class="fas fa-tags me-1"></i>Descuento para el referido</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="discount_amount" class="form-control" step="0.01" min="0" placeholder="5.00" required>
                    </div>
                    <small class="text-muted">Se resta del precio del plan que elija.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label required"><i class="fas fa-hand-holding-dollar me-1"></i>Comisión del partner</label>
                    <div class="input-group">
                        <input type="number" name="commission_percent" class="form-control" step="0.01" min="0.01" max="100" placeholder="15.00" required>
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">% de lo que el cliente paga (ya con descuento). Se acredita al saldo del partner en cada pago.</small>
                </div>

                <div class="col-12">
                    <label class="form-label required"><i class="fas fa-toggle-on me-1"></i>Estado</label>
                    <select name="is_active" class="form-control" required>
                        <option value="1" selected>Activo (el código funciona)</option>
                        <option value="0">Inactivo (código deshabilitado)</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label"><i class="fas fa-sticky-note me-1"></i>Notas internas</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Acuerdo, condiciones..."></textarea>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createReferralPartnerModal' }))">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Crear Partner
            </button>
        </div>
    </form>
</x-modal>
