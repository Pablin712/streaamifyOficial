<x-modal name="netflixCodigoModal" :show="false" maxWidth="md">
    <div class="modal-header border-bottom">
        <h5 class="modal-title">
            <i class="fas fa-key me-2 text-danger"></i>
            <span id="netflix_codigo_modal_title">Pedir codigo de Netflix</span>
        </h5>
        <button type="button" class="btn-close" onclick="closeNetflixCodigoModal()"></button>
    </div>

    <div class="modal-body bg-body">
        <div id="netflix_codigo_request_state">
            <div class="alert alert-warning mb-3">
                Vas a solicitar un codigo de Netflix para esta cuenta. Si el pedido se procesa correctamente, recibiras la confirmacion aqui y el codigo llegara por WhatsApp.
            </div>
            <div class="small text-muted mb-3">
                <div><strong>Cuenta:</strong> <span id="netflix_codigo_cuenta">-</span></div>
                <div><strong>Proveedor:</strong> <span id="netflix_codigo_proveedor">-</span></div>
            </div>
        </div>

        <div id="netflix_codigo_loading_state" class="text-center py-4 d-none">
            <div class="spinner-border text-danger mb-3" role="status"></div>
            <div class="fw-semibold">Solicitando codigo de Netflix...</div>
            <div class="text-muted small">Espera mientras el webhook responde.</div>
        </div>

        <div id="netflix_codigo_result_state" class="d-none text-center py-2">
            <div class="alert alert-success mb-3">
                <div class="fw-bold" id="netflix_codigo_result_message">listo, te llegará un código al whatsapp</div>
                <div class="small text-muted" id="netflix_codigo_result_expiration">En 15 minutos vence.</div>
            </div>
        </div>
    </div>

    <div class="modal-footer border-top">
        <button type="button" id="netflix_codigo_cancel_btn" class="btn btn-secondary" onclick="closeNetflixCodigoModal()">
            Cerrar
        </button>
        <button type="button" id="netflix_codigo_confirm_btn" class="btn btn-danger" onclick="confirmNetflixCodeRequest()">
            <i class="fas fa-key me-1"></i>Confirmar solicitud
        </button>
    </div>
</x-modal>
