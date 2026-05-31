<x-modal name="confirm-delete-venta" :show="false" maxWidth="md">
    <div class="modal-header modal-header-danger">
        <h5 class="modal-title">
            <i class="fas fa-exclamation-triangle me-2"></i>Eliminar Venta
        </h5>
        <button type="button" class="btn-close btn-close-white"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirm-delete-venta' }))">
        </button>
    </div>

    <div class="modal-body">
        <div class="alert alert-danger mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>¡ATENCIÓN!</strong> Esta acción es <strong>IRREVERSIBLE</strong>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title">Información de la Venta a Eliminar:</h6>
                <ul class="list-unstyled mb-0">
                    <li><strong>Venta #:</strong> <span id="delete_venta_number"></span></li>
                    <li><strong>Cliente:</strong> <span id="delete_cliente_nombre"></span></li>
                    <li><strong>Total:</strong> <span id="delete_venta_total" class="text-success"></span></li>
                </ul>
            </div>
        </div>

        <div class="alert alert-warning mb-0" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Al eliminar esta venta:</strong>
            <ul class="mb-0 mt-2 ps-3">
                <li>Se eliminarán <strong>TODOS los detalles</strong> asociados a la venta</li>
                <li>Esta acción <strong>NO se puede recuperar</strong></li>
                <li>El registro será <strong>eliminado permanentemente</strong> del sistema</li>
            </ul>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirm-delete-venta' }))">
            <i class="fas fa-times me-1"></i> Cancelar
        </button>
        <form id="delete_venta_form" onsubmit="submitDeleteVenta(event)" style="display: inline;">
            @csrf
            @method('DELETE')
            <input type="hidden" id="delete_venta_id" name="idven">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash-alt me-1"></i> Sí, Eliminar Venta
            </button>
        </form>
    </div>
</x-modal>
