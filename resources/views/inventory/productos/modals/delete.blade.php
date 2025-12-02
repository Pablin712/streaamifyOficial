<x-modal name="deleteProductoModal" :show="false" maxWidth="md">
    <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
            <i class="fas fa-trash-alt me-2"></i>Eliminar Producto
        </h5>
        <button type="button" class="btn-close btn-close-white" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'deleteProductoModal' }))"></button>
    </div>
    <form id="deleteProductoForm" onsubmit="submitDelete(event)">
        @csrf
        @method('DELETE')
        <input type="hidden" name="id" id="delete_producto_id">

        <div class="modal-body">
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>¿Estás seguro de que deseas eliminar este producto?</strong>
                <p class="mb-0 mt-2">Esta acción eliminará el producto y todos sus detalles asociados. Esta operación no se puede deshacer.</p>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-3 text-muted">Información del producto a eliminar:</h6>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <strong>Código:</strong><br>
                            <span id="delete_producto_codigo"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Nombre:</strong><br>
                            <span id="delete_producto_nombre"></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Precio:</strong><br>
                            <span id="delete_producto_precio"></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Tipo:</strong><br>
                            <span id="delete_producto_tipo"></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Categoría:</strong><br>
                            <span id="delete_producto_categoria"></span>
                        </div>
                        <div class="col-md-12 mt-2">
                            <strong>Estado:</strong><br>
                            <span id="delete_producto_estado"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash-alt"></i> Eliminar Producto
            </button>
        </div>
    </form>
</x-modal>
