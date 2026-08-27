<x-modal name="channelDonnaSubModal" :show="false" maxWidth="lg">
    <div class="modal-header modal-header-donna-blue">
        <h5 class="modal-title">
            <i class="bi bi-whatsapp me-2"></i>Canal WhatsApp — <span id="channel_cliente_nombre"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'channelDonnaSubModal' }))">
        </button>
    </div>
    <form id="channelDonnaSubForm" onsubmit="submitChannelSub(event)">
        @csrf
        <input type="hidden" id="channel_sub_id" name="subscription_id">
        <div class="modal-body">
            <div id="channel_status_banner" class="alert d-none mb-3"></div>

            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label required">Nombre de instancia</label>
                    <input type="text" name="instance_name" id="channel_instance_name"
                           class="form-control font-monospace" placeholder="bot-pagos" required>
                    <small class="text-muted">El valor de <code>body.instance</code> en los webhooks de Evo API.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Número de WhatsApp</label>
                    <input type="text" name="phone_number" id="channel_phone_number"
                           class="form-control" placeholder="593961412826">
                    <small class="text-muted">Sin el signo +.</small>
                </div>
                <div class="col-12">
                    <label class="form-label required">URL del servidor Evo API</label>
                    <input type="url" name="api_base_url" id="channel_api_base_url"
                           class="form-control" placeholder="https://evoapi.abigailsoft.com" required>
                </div>
                <div class="col-12">
                    <label class="form-label" id="channel_api_key_label">API Key de Evo API</label>
                    <input type="password" name="api_key" id="channel_api_key"
                           class="form-control font-monospace" placeholder="Clave de acceso a la instancia">
                    <small class="text-muted" id="channel_api_key_hint">Se guarda encriptada.</small>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'channelDonnaSubModal' }))">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Guardar canal
            </button>
        </div>
    </form>
</x-modal>
