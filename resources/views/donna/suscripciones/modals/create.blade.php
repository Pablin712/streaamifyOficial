<x-modal name="createDonnaSubModal" :show="false" maxWidth="lg">
    <div class="modal-header modal-header-donna-blue">
        <h5 class="modal-title">
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
                    <select name="plan_id" id="create_sub_plan_id" class="form-control" required
                            onchange="toggleBusinessFields(this)">
                        <option value="">— Selecciona un plan —</option>
                        @foreach ($planes as $plan)
                            <option value="{{ $plan->id }}" data-type="{{ $plan->service_type }}">
                                {{ $plan->name }} — ${{ number_format($plan->price, 2) }} / {{ $plan->billing_cycle_label }}
                                ({{ $plan->service_type === 'business' ? 'Business' : 'Personal' }})
                            </option>
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

                {{-- Campos exclusivos para Donna Business --}}
                <div id="business-whatsapp-fields" class="col-12" style="display:none;">
                    <div class="p-3 rounded-3 border" style="background:#fffbea;border-color:#E4B100 !important;">
                        <div class="fw-semibold mb-3" style="color:#c9890a;">
                            <i class="bi bi-whatsapp me-1"></i>Configuración WhatsApp — Evo API
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Nombre de instancia <span class="text-danger">*</span></label>
                                <input type="text" name="instance_name" id="create_instance_name"
                                       class="form-control font-monospace"
                                       placeholder="bot-pagos">
                                <small class="text-muted">El valor de <code>body.instance</code> en los webhooks de Evo API.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Número de WhatsApp</label>
                                <input type="text" name="phone_number" id="create_phone_number"
                                       class="form-control"
                                       placeholder="593961412826">
                                <small class="text-muted">Número del WhatsApp del negocio (sin +).</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">URL del servidor Evo API <span class="text-danger">*</span></label>
                                <input type="url" name="api_base_url" id="create_api_base_url"
                                       class="form-control"
                                       placeholder="https://evoapi.abigailsoft.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label">API Key de Evo API <span class="text-danger">*</span></label>
                                <input type="password" name="api_key" id="create_api_key"
                                       class="form-control font-monospace"
                                       placeholder="Clave de acceso a la instancia">
                                <small class="text-muted">Se guarda encriptada. Necesaria para que Donna envíe mensajes.</small>
                            </div>
                        </div>
                    </div>
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

<script>
function toggleBusinessFields(select) {
    const type = select.options[select.selectedIndex]?.dataset?.type;
    const container = document.getElementById('business-whatsapp-fields');
    const instanceInput = document.getElementById('create_instance_name');
    const urlInput = document.getElementById('create_api_base_url');
    const keyInput = document.getElementById('create_api_key');

    if (type === 'business') {
        container.style.display = '';
        instanceInput.required = true;
        urlInput.required = true;
        keyInput.required = true;
    } else {
        container.style.display = 'none';
        instanceInput.required = false;
        urlInput.required = false;
        keyInput.required = false;
    }
}
</script>
