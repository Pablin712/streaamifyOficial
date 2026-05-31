<x-modal name="mensajeProveedorModal" :show="false" maxWidth="lg">
    <div class="modal-header modal-header-success">
        <h5 class="modal-title">
            <i class="fab fa-whatsapp me-2"></i>Enviar Mensaje al Proveedor
        </h5>
        <button type="button" class="btn-close btn-close-white" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'mensajeProveedorModal' }))"></button>
    </div>

    <form id="mensajeProveedorForm" onsubmit="submitMensajeProveedor(event)">
        @csrf
        <input type="hidden" id="mensaje_proveedor_idcue" name="idcue">
        <input type="hidden" id="mensaje_proveedor_proveedor_id" name="proveedor_id">
        <input type="hidden" id="mensaje_proveedor_telefono" name="telefono">

        <div class="modal-body">
            <!-- Selector de Proveedor -->
            <div class="mb-3">
                <label for="proveedor_search" class="form-label fw-semibold">Buscar Proveedor</label>
                <input
                    type="text"
                    id="proveedor_search"
                    class="form-control"
                    placeholder="Escribe el nombre o teléfono del proveedor..."
                    autocomplete="off"
                >
                <div id="proveedor_results" class="mt-2" style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.375rem; display: none;">
                    <!-- Resultados de búsqueda aparecerán aquí -->
                </div>
            </div>

            <div class="alert alert-info" id="proveedor_info" style="display: none;">
                <div class="row g-2">
                    <div class="col-md-6"><strong>Proveedor:</strong> <span id="mensaje_proveedor_nombre_display">-</span></div>
                    <div class="col-md-6"><strong>Teléfono:</strong> <span id="mensaje_proveedor_telefono_display">-</span></div>
                </div>
                <div class="mt-2">
                    <strong>Cuenta:</strong> <span id="mensaje_proveedor_cuenta_display">-</span>
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
            <button type="submit" class="btn btn-success" id="mensaje_proveedor_submit_btn" disabled>
                <i class="fab fa-whatsapp me-1"></i>Enviar al proveedor
            </button>
        </div>
    </form>
</x-modal>
