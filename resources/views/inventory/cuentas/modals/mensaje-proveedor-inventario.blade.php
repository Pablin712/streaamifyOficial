<x-modal name="mensajeProveedorInventarioModal" :show="false" maxWidth="lg">
    <div class="modal-header" style="background-color: #198754; color: #ffffff;">
        <h5 class="modal-title">
            <i class="fab fa-whatsapp me-2"></i>Enviar Inventario al Proveedor
        </h5>
        <button type="button" class="btn-close btn-close-white" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'mensajeProveedorInventarioModal' }))"></button>
    </div>

    <form id="mensajeProveedorInventarioForm" onsubmit="submitMensajeProveedorInventario(event)">
        @csrf
        <input type="hidden" id="inventario_proveedor_id" name="proveedor_id">

        <div class="modal-body">
            <div class="alert alert-info mb-3">
                <div class="row g-2">
                    <div class="col-md-5"><strong>Proveedor:</strong> <span id="inventario_proveedor_nombre">-</span></div>
                    <div class="col-md-4"><strong>Teléfono:</strong> <span id="inventario_proveedor_telefono">-</span></div>
                    <div class="col-md-3"><strong>Cuentas:</strong> <span id="inventario_total_cuentas">0</span></div>
                </div>
            </div>

            <div class="mb-2">
                <h6 class="mb-2">Servicios a incluir</h6>
                <p class="text-muted mb-2">Selecciona uno o más servicios. El mensaje se enviará en bloques por servicio con formato usuario y fecha de vencimiento.</p>
            </div>

            <div id="inventario_servicios_container" class="border rounded p-2" style="max-height: 260px; overflow-y: auto;">
                <div class="text-muted small">Selecciona cuentas para cargar servicios.</div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeMensajeProveedorInventarioModal()">
                <i class="fas fa-times me-1"></i>Cancelar
            </button>
            <button type="submit" class="btn btn-success" id="inventario_submit_btn">
                <i class="fab fa-whatsapp me-1"></i>Enviar Inventario
            </button>
        </div>
    </form>
</x-modal>
