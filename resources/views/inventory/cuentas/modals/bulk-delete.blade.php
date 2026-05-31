<x-modal name="bulkDeleteCuentasModal" :show="false" maxWidth="lg">
    <div class="modal-header modal-header-danger">
        <h5 class="modal-title">
            <i class="fas fa-trash me-2"></i>Eliminar Cuentas Seleccionadas
        </h5>
        <button type="button" class="btn-close btn-close-white"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'bulkDeleteCuentasModal' }))"></button>
    </div>

    <div class="modal-body">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>¿Está seguro que desea eliminar las siguientes cuentas?</strong>
            <p class="mb-0 mt-1">Esta acción no se puede deshacer.</p>
        </div>

        <!-- Advertencia: alguna cuenta tiene usuarios activos -->
        <div id="bulk-delete-warning" class="alert alert-danger" style="display: none;">
            <i class="fas fa-ban me-2"></i>
            <strong>⛔ NO SE PUEDE ELIMINAR:</strong> Una o más cuentas tienen usuarios activos
            (marcadas en rojo). Primero mueva los usuarios a otra cuenta.
        </div>

        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Servicio</th>
                        <th>Clientes activos</th>
                    </tr>
                </thead>
                <tbody id="bulk-delete-list">
                    {{-- Rellenado por JS en openBulkDeleteModal() --}}
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeBulkDeleteModal()">
            <i class="fas fa-times me-1"></i> Cancelar
        </button>
        <button type="button" class="btn btn-danger" id="bulk-delete-confirm-btn" onclick="submitBulkDelete()">
            <i class="fas fa-trash me-1"></i> Sí, Eliminar todas
        </button>
    </div>
</x-modal>
