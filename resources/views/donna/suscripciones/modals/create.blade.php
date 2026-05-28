<x-modal name="createDonnaSubModal" :show="false" maxWidth="lg">
    <div class="modal-header" style="background-color:#274698;">
        <h5 class="modal-title text-white">
            <i class="bi bi-robot me-2"></i>Nueva Suscripción Donna
        </h5>
        <button type="button" class="btn-close btn-close-white"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createDonnaSubModal' }))">
        </button>
    </div>
    <form id="createDonnaSubForm" onsubmit="submitCreateSub(event)">
        @csrf
        <div class="modal-body">
            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label required"><i class="fas fa-user me-1"></i>Cliente</label>
                    <select name="client_id" id="create_sub_client_id" class="form-control" required>
                        <option value="">— Selecciona un cliente —</option>
                        @foreach ($clientes as $cli)
                            <option value="{{ $cli->idcli }}">{{ $cli->nombrecli }} ({{ $cli->telefonocli }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label required"><i class="bi bi-robot me-1"></i>Plan Donna</label>
                    <select name="plan_id" id="create_sub_plan_id" class="form-control" required>
                        <option value="">— Selecciona un plan —</option>
                        @foreach ($planes as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} — ${{ number_format($plan->price, 2) }} / {{ $plan->billing_cycle_label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label required"><i class="fas fa-calendar me-1"></i>Fecha de inicio</label>
                    <input type="datetime-local" name="starts_at" id="create_sub_starts_at"
                        class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label"><i class="fas fa-calendar-times me-1"></i>Fecha de vencimiento</label>
                    <input type="datetime-local" name="expires_at" id="create_sub_expires_at" class="form-control">
                    <small class="text-muted">Dejar vacío para sin vencimiento.</small>
                </div>

                <div class="col-12">
                    <label class="form-label"><i class="fas fa-sticky-note me-1"></i>Notas internas</label>
                    <textarea name="notes" class="form-control" rows="2"
                        placeholder="Método de pago, observaciones..."></textarea>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createDonnaSubModal' }))">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Crear Suscripción
            </button>
        </div>
    </form>
</x-modal>
