<x-modal name="mensajeClientesModal" :show="false" maxWidth="lg">
    <div class="modal-header modal-header-whatsapp">
        <h5 class="modal-title">
            <i class="fab fa-whatsapp me-2"></i>Enviar Mensaje a Clientes de Cuenta
        </h5>
        <button type="button" class="btn-close btn-close-white" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'mensajeClientesModal' }))"></button>
    </div>

    <form id="mensajeClientesForm" onsubmit="submitMensajeClientes(event)">
        @csrf
        <input type="hidden" id="mensaje_clientes_idcue" name="idcue">

        <div class="modal-body">
            <div class="alert alert-info">
                <div class="row">
                    <div class="col-md-4"><strong>Cuenta:</strong> <span id="mensaje_clientes_cuenta">-</span></div>
                    <div class="col-md-4"><strong>Servicio:</strong> <span id="mensaje_clientes_servicio">-</span></div>
                    <div class="col-md-4"><strong>Clientes activos:</strong> <span id="mensaje_clientes_total">0</span></div>
                </div>
            </div>

            <div id="mensaje_clientes_cooldown" class="alert alert-warning d-none"></div>

            <div class="mb-3">
                <label for="mensaje_clientes_texto" class="form-label fw-semibold">
                    Mensaje personalizado para clientes
                </label>
                <textarea
                    id="mensaje_clientes_texto"
                    name="mensaje"
                    class="form-control"
                    rows="6"
                    minlength="3"
                    maxlength="1200"
                    placeholder="Escribe el mensaje que se enviará por n8n a todos los clientes activos de esta cuenta..."
                    required
                ></textarea>
                <small class="text-muted">Se enviarán datos de cuenta y teléfonos de clientes al webhook configurado en n8n.</small>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeMensajeClientesModal()">
                <i class="fas fa-times me-1"></i>Cancelar
            </button>
            <button type="submit" class="btn btn-success" id="mensaje_clientes_submit_btn">
                <i class="fab fa-whatsapp me-1"></i>Enviar a n8n
            </button>
        </div>
    </form>
</x-modal>
