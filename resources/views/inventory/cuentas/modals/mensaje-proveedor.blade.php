<x-modal name="mensajeProveedorModal" :show="false" maxWidth="lg">
    <div class="modal-header" style="background-color: #198754; color: #ffffff;">
        <h5 class="modal-title">
            <i class="fab fa-whatsapp me-2"></i>Enviar Mensaje al Proveedor
        </h5>
        <button type="button" class="btn-close btn-close-white" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'mensajeProveedorModal' }))"></button>
    </div>

    <form id="mensajeProveedorForm" onsubmit="submitMensajeProveedor(event)">
        @csrf
        <input type="hidden" id="mensaje_proveedor_idcue" name="idcue">
        <input type="hidden" id="mensaje_proveedor_nombre" name="proveedor">
        <input type="hidden" id="mensaje_proveedor_telefono" name="telefono">

        <div class="modal-body">
            <div class="alert alert-info">
                <div class="row g-2">
                    <div class="col-md-4"><strong>Proveedor:</strong> <span id="mensaje_proveedor_nombre_display">-</span></div>
                    <div class="col-md-4"><strong>Teléfono:</strong> <span id="mensaje_proveedor_telefono_display">-</span></div>
                    <div class="col-md-4"><strong>Cuenta:</strong> <span id="mensaje_proveedor_cuenta_display">-</span></div>
                </div>
            </div>

            <div class="mb-3">
                <label for="mensaje_proveedor_texto" class="form-label fw-semibold">Mensaje para proveedor</label>
                <textarea
                    id="mensaje_proveedor_texto"
                    name="mensaje"
                    class="form-control"
                    rows="8"
                    minlength="3"
                    maxlength="4000"
                    required
                ></textarea>
                <small class="text-muted">Puedes editar el texto antes de enviarlo.</small>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeMensajeProveedorModal()">
                <i class="fas fa-times me-1"></i>Cancelar
            </button>
            <button type="submit" class="btn btn-success" id="mensaje_proveedor_submit_btn">
                <i class="fab fa-whatsapp me-1"></i>Enviar al proveedor
            </button>
        </div>
    </form>
</x-modal>
