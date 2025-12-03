<x-modal name="confirm-send-invoice" :show="false" maxWidth="md">
    <div class="modal-header" style="background-color: #6c757d; color: white;">
        <h5 class="modal-title">
            <i class="fas fa-envelope me-2"></i>Enviar Factura por Email
        </h5>
        <button type="button" class="btn-close btn-close-white"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirm-send-invoice' }))">
        </button>
    </div>

    <div class="modal-body">
        <div class="alert alert-info mb-3" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            ¿Deseas enviar la factura de esta venta por correo electrónico?
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title">Información de la Venta:</h6>
                <ul class="list-unstyled mb-0">
                    <li><strong>Venta #:</strong> <span id="send_venta_number"></span></li>
                    <li><strong>Cliente:</strong> <span id="send_cliente_nombre"></span></li>
                    <li><strong>Total:</strong> <span id="send_venta_total" class="text-success"></span></li>
                </ul>
            </div>
        </div>

        <div class="alert alert-warning mb-3" role="alert">
            <i class="fas fa-envelope-open-text me-2"></i>
            <strong>Email de destino:</strong> <span id="send_cliente_email"></span>
        </div>

        <div class="alert alert-success mb-0" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            Se enviará la factura completa con todos los detalles de la compra al cliente.
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirm-send-invoice' }))">
            <i class="fas fa-times me-1"></i> Cancelar
        </button>
        <form id="send_invoice_form" onsubmit="submitSendInvoice(event)" style="display: inline;">
            @csrf
            <input type="hidden" id="send_venta_id" name="idven">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane me-1"></i> Sí, Enviar Factura
            </button>
        </form>
    </div>
</x-modal>
