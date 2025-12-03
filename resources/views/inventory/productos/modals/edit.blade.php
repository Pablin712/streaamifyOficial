<x-modal name="editProductoModal" :show="false" maxWidth="2xl">
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-edit me-2"></i>Editar Producto
        </h5>
        <button type="button" class="btn-close" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editProductoModal' }))"></button>
    </div>
    <form id="editProductoForm" onsubmit="submitEdit(event)" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="id" id="edit_producto_id">

        <div class="modal-body">
            <div class="row g-3">
                <!-- Código del Producto -->
                <div class="col-md-6">
                    <label for="edit_codigopro" class="form-label required">
                        <i class="fas fa-barcode me-1"></i>Código del Producto
                    </label>
                    <input type="text"
                           class="form-control"
                           id="edit_codigopro"
                           name="codigopro"
                           required
                           placeholder="Código único del producto">
                </div>

                <!-- Nombre del Producto -->
                <div class="col-md-6">
                    <label for="edit_nombrepro" class="form-label required">
                        <i class="fas fa-tag me-1"></i>Nombre del Producto
                    </label>
                    <input type="text"
                           class="form-control"
                           id="edit_nombrepro"
                           name="nombrepro"
                           required
                           placeholder="Nombre descriptivo">
                </div>

                <!-- Precio -->
                <div class="col-md-4">
                    <label for="edit_preciopro" class="form-label required">
                        <i class="fas fa-dollar-sign me-1"></i>Precio
                    </label>
                    <input type="number"
                           class="form-control"
                           id="edit_preciopro"
                           name="preciopro"
                           step="0.01"
                           min="0"
                           required
                           placeholder="0.00">
                </div>

                <!-- Estrellas -->
                <div class="col-md-4">
                    <label for="edit_estrellaspro" class="form-label">
                        <i class="fas fa-star me-1"></i>Calificación (Estrellas)
                    </label>
                    <select class="form-control searchable-select"
                            id="edit_estrellaspro"
                            name="estrellaspro"
                            data-placeholder="Seleccionar calificación...">
                        <option value="">Sin calificación</option>
                        <option value="1">⭐ 1 Estrella</option>
                        <option value="2">⭐⭐ 2 Estrellas</option>
                        <option value="3">⭐⭐⭐ 3 Estrellas</option>
                        <option value="4">⭐⭐⭐⭐ 4 Estrellas</option>
                        <option value="5">⭐⭐⭐⭐⭐ 5 Estrellas</option>
                    </select>
                </div>

                <!-- Estado -->
                <div class="col-md-4">
                    <label for="edit_activo" class="form-label required">
                        <i class="fas fa-toggle-on me-1"></i>Estado
                    </label>
                    <select class="form-control searchable-select"
                            id="edit_activo"
                            name="activo"
                            required
                            data-placeholder="Seleccionar estado...">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>

                <!-- Tipo de Producto -->
                <div class="col-md-6">
                    <label for="edit_tipo_producto_id" class="form-label required">
                        <i class="fas fa-list me-1"></i>Tipo de Producto
                    </label>
                    <select class="form-control searchable-select"
                            id="edit_tipo_producto_id"
                            name="tipo_producto_id"
                            required
                            data-placeholder="Seleccionar tipo de producto...">
                        <option value="">Seleccionar tipo...</option>
                        @foreach ($tiposProducto as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Categoría -->
                <div class="col-md-6">
                    <label for="edit_categoria_id" class="form-label required">
                        <i class="fas fa-folder me-1"></i>Categoría
                    </label>
                    <select class="form-control searchable-select"
                            id="edit_categoria_id"
                            name="categoria_id"
                            required
                            data-placeholder="Seleccionar categoría...">
                        <option value="">Seleccionar categoría...</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Foto -->
                <div class="col-md-12">
                    <label for="edit_foto" class="form-label">
                        <i class="fas fa-image me-1"></i>Foto del Producto
                    </label>
                    <input type="file"
                           class="form-control"
                           id="edit_foto"
                           name="foto"
                           accept="image/jpeg,image/png,image/jpg,image/gif">
                    <small class="text-muted">Formatos permitidos: JPG, PNG, GIF (máx. 2MB)</small>
                    <div id="edit_foto_preview" class="mt-2"></div>
                </div>

                <!-- Descripción -->
                <div class="col-md-12">
                    <label for="edit_descripcionpro" class="form-label">
                        <i class="fas fa-align-left me-1"></i>Descripción
                    </label>
                    <textarea class="form-control"
                              id="edit_descripcionpro"
                              name="descripcionpro"
                              rows="3"
                              placeholder="Descripción detallada del producto"></textarea>
                </div>

                <!-- Detalles del Producto -->
                <div class="col-md-12 mt-3">
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">
                                <i class="fas fa-list-ul me-1"></i>Detalles del Producto
                            </h6>
                            <button type="button"
                                    class="btn btn-success btn-sm"
                                    onclick="openAgregarDetalleModalEdit()">
                                <i class="fas fa-plus-circle"></i> Agregar Detalle
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID Servicio</th>
                                        <th>Descripción</th>
                                        <th>Meses</th>
                                        <th width="120">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="edit_tabla_detalles">
                                    <!-- Detalles cargados dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Campo oculto para detalles -->
                <input type="hidden" name="detalles_producto" id="edit_detalles_producto">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Producto
            </button>
        </div>
    </form>
</x-modal>
