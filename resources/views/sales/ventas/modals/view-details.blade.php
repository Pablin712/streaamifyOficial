<x-modal name="view-venta-details" :show="false" maxWidth="2xl">
    <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white;">
        <h5 class="modal-title">
            <i class="fas fa-receipt me-2"></i>Detalles de Venta #<span id="view_venta_number"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'view-venta-details' }))">
        </button>
    </div>

    <div class="modal-body">
        <!-- Loading State -->
        <div id="view_details_loading" style="display: none;">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3 text-muted">Cargando detalles de la venta...</p>
            </div>
        </div>

        <!-- Content State -->
        <div id="view_details_content" style="display: none;">
            <!-- Información General -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-primary h-100">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-user me-2"></i>Cliente
                        </div>
                        <div class="card-body">
                            <p class="mb-2"><strong>Nombre:</strong> <span id="view_cliente_nombre"></span></p>
                            <p class="mb-2"><strong>Teléfono:</strong> <span id="view_cliente_telefono"></span></p>
                            <p class="mb-0"><strong>Email:</strong> <span id="view_cliente_email"></span></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-success h-100">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-user-tie me-2"></i>Empleado
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><strong>Vendedor:</strong> <span id="view_empleado_nombre"></span></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-info h-100">
                        <div class="card-header bg-info text-white">
                            <i class="fas fa-shopping-cart me-2"></i>Venta
                        </div>
                        <div class="card-body">
                            <p class="mb-2"><strong>Fecha:</strong> <span id="view_venta_fecha"></span></p>
                            <p class="mb-0"><strong>Método Pago:</strong> <span id="view_venta_metodo_pago"></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalles de la Venta -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>Detalles de Productos/Servicios</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Cuenta</th>
                                    <th>Perfil</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="view_details_items">
                                <!-- Se llenará dinámicamente -->
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="5" class="text-end">TOTAL:</th>
                                    <th class="text-end">
                                        <span class="text-success fs-5" id="view_venta_total"></span>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div id="view_details_error" style="display: none;">
            <div class="alert alert-danger mb-0" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error al cargar los detalles</strong>
                <p class="mb-0 mt-2" id="view_details_error_message"></p>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'view-venta-details' }))">
            <i class="fas fa-times me-1"></i> Cerrar
        </button>
    </div>
</x-modal>
