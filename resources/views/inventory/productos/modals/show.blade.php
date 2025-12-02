<x-modal name="showProductoModal" :show="false" maxWidth="2xl">
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-eye me-2"></i>Detalles del Producto
        </h5>
        <button type="button" class="btn-close" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'showProductoModal' }))"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <!-- Información del Producto -->
            <div class="col-md-8">
                <h4 class="text-primary" id="show_producto_nombre"></h4>
                <p><strong>Código:</strong> <span id="show_producto_codigo"></span></p>
                <p><strong>Precio:</strong> <span id="show_producto_precio"></span></p>
                <p><strong>Calificación:</strong> <span id="show_producto_estrellas"></span></p>
                <p><strong>Descripción:</strong> <span id="show_producto_descripcion"></span></p>
                <p><strong>Categoría:</strong> <span id="show_producto_categoria"></span></p>
                <p><strong>Tipo de Producto:</strong> <span id="show_producto_tipo"></span></p>
                <p><strong>Estado:</strong> <span id="show_producto_estado"></span></p>
            </div>
            <!-- Imagen del Producto -->
            <div class="col-md-4 text-center">
                <div id="show_producto_foto"></div>
            </div>
        </div>

        <!-- Detalles del Producto -->
        <div class="mt-4">
            <h5>Detalles del Producto</h5>
            <div class="table-responsive">
                <table class="table table-hover table-bordered text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>ID Servicio</th>
                            <th>Descripción</th>
                            <th>Meses</th>
                        </tr>
                    </thead>
                    <tbody id="show_producto_detalles">
                        <!-- Detalles cargados dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'showProductoModal' }))">
            <i class="fas fa-times"></i> Cerrar
        </button>
    </div>
</x-modal>
