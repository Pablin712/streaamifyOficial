<x-modal name="createProductoModal" :show="false" maxWidth="2xl">
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-box me-2"></i>Crear Nuevo Producto
        </h5>
        <button type="button" class="btn-close" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createProductoModal' }))"></button>
    </div>
    <form id="createProductoForm" onsubmit="submitCreate(event)" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
            <div class="row g-3">
                <!-- Código del Producto -->
                <div class="col-md-6">
                    <label for="create_codigopro" class="form-label required">
                        <i class="fas fa-barcode me-1"></i>Código del Producto
                    </label>
                    <input type="text"
                           class="form-control"
                           id="create_codigopro"
                           name="codigopro"
                           required
                           placeholder="Código único del producto">
                </div>

                <!-- Nombre del Producto -->
                <div class="col-md-6">
                    <label for="create_nombrepro" class="form-label required">
                        <i class="fas fa-tag me-1"></i>Nombre del Producto
                    </label>
                    <input type="text"
                           class="form-control"
                           id="create_nombrepro"
                           name="nombrepro"
                           required
                           placeholder="Nombre descriptivo">
                </div>

                <!-- Precio -->
                <div class="col-md-4">
                    <label for="create_preciopro" class="form-label required">
                        <i class="fas fa-dollar-sign me-1"></i>Precio
                    </label>
                    <input type="number"
                           class="form-control"
                           id="create_preciopro"
                           name="preciopro"
                           step="0.01"
                           min="0"
                           required
                           placeholder="0.00">
                </div>

                <!-- Estrellas -->
                <div class="col-md-4">
                    <label for="create_estrellaspro" class="form-label">
                        <i class="fas fa-star me-1"></i>Calificación (Estrellas)
                    </label>
                    <select class="form-control"
                            id="create_estrellaspro"
                            name="estrellaspro">
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
                    <label for="create_activo" class="form-label required">
                        <i class="fas fa-toggle-on me-1"></i>Estado
                    </label>
                    <select class="form-control"
                            id="create_activo"
                            name="activo"
                            required>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>

                <!-- Tipo de Producto -->
                <div class="col-md-6">
                    <label for="create_tipo_producto_id" class="form-label required">
                        <i class="fas fa-list me-1"></i>Tipo de Producto
                    </label>
                    <select class="form-control"
                            id="create_tipo_producto_id"
                            name="tipo_producto_id"
                            required>
                        <option value="">Seleccionar tipo...</option>
                        @foreach ($tiposProducto as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Categoría -->
                <div class="col-md-6">
                    <label for="create_categoria_id" class="form-label required">
                        <i class="fas fa-folder me-1"></i>Categoría
                    </label>
                    <select class="form-control"
                            id="create_categoria_id"
                            name="categoria_id"
                            required>
                        <option value="">Seleccionar categoría...</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Foto -->
                <div class="col-md-12">
                    <label for="create_foto" class="form-label">
                        <i class="fas fa-image me-1"></i>Foto del Producto
                    </label>
                    <input type="file"
                           class="form-control"
                           id="create_foto"
                           name="foto"
                           accept="image/jpeg,image/png,image/jpg,image/gif">
                    <small class="text-muted">Formatos permitidos: JPG, PNG, GIF (máx. 2MB)</small>
                </div>

                <!-- Descripción -->
                <div class="col-md-12">
                    <label for="create_descripcionpro" class="form-label">
                        <i class="fas fa-align-left me-1"></i>Descripción
                    </label>
                    <textarea class="form-control"
                              id="create_descripcionpro"
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
                                    onclick="openAgregarDetalleModal()">
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
                                        <th width="100">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="create_tabla_detalles">
                                    <!-- Detalles agregados dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Campo oculto para detalles -->
                <input type="hidden" name="detalles_producto" id="create_detalles_producto">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Crear Producto
            </button>
        </div>
    </form>
</x-modal>

<!-- Modal para agregar detalle -->
<x-modal name="agregarDetalleModal" :show="false" maxWidth="md">
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-plus me-2"></i>Agregar Detalle al Producto
        </h5>
        <button type="button" class="btn-close" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'agregarDetalleModal' }))"></button>
    </div>
    <form id="formAgregarDetalle" onsubmit="event.preventDefault(); guardarDetalle();">
        <div class="modal-body">
            <div class="row g-3">
                <!-- ID Servicio -->
                <div class="col-md-12">
                    <label for="detalle_idser" class="form-label required">
                        <i class="fas fa-tv me-1"></i>Servicio
                    </label>
                    <select class="form-control"
                            id="detalle_idser"
                            required>
                        <option value="">Seleccione un Servicio...</option>
                        @foreach ($servicios as $servicio)
                            <option value="{{ $servicio->idser }}">
                                {{ $servicio->idser }}: {{ $servicio->nombreser }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Descripción -->
                <div class="col-md-12">
                    <label for="detalle_descripcion" class="form-label required">
                        <i class="fas fa-align-left me-1"></i>Descripción
                    </label>
                    <textarea class="form-control"
                              id="detalle_descripcion"
                              rows="3"
                              required
                              placeholder="Descripción del detalle"></textarea>
                </div>

                <!-- Meses -->
                <div class="col-md-12">
                    <label for="detalle_meses" class="form-label required">
                        <i class="fas fa-calendar me-1"></i>Meses
                    </label>
                    <input type="number"
                           class="form-control"
                           id="detalle_meses"
                           min="1"
                           required
                           placeholder="Número de meses">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeAgregarDetalleModal()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Detalle
            </button>
        </div>
    </form>
</x-modal>

<!-- Modal para editar detalle -->
<x-modal name="editarDetalleModal" :show="false" maxWidth="md">
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-edit me-2"></i>Editar Detalle del Producto
        </h5>
        <button type="button" class="btn-close" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editarDetalleModal' }))"></button>
    </div>
    <form id="formEditarDetalle" onsubmit="event.preventDefault(); guardarCambiosDetalle();">
        <div class="modal-body">
            <input type="hidden" id="editar_detalle_index">

            <div class="row g-3">
                <!-- ID Servicio -->
                <div class="col-md-12">
                    <label for="editar_idser" class="form-label required">
                        <i class="fas fa-tv me-1"></i>Servicio
                    </label>
                    <select class="form-control"
                            id="editar_idser"
                            required>
                        <option value="">Seleccione un Servicio...</option>
                        @foreach ($servicios as $servicio)
                            <option value="{{ $servicio->idser }}">
                                {{ $servicio->idser }}: {{ $servicio->nombreser }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Descripción -->
                <div class="col-md-12">
                    <label for="editar_descripcion" class="form-label required">
                        <i class="fas fa-align-left me-1"></i>Descripción
                    </label>
                    <textarea class="form-control"
                              id="editar_descripcion"
                              rows="3"
                              required
                              placeholder="Descripción del detalle"></textarea>
                </div>

                <!-- Meses -->
                <div class="col-md-12">
                    <label for="editar_meses" class="form-label required">
                        <i class="fas fa-calendar me-1"></i>Meses
                    </label>
                    <input type="number"
                           class="form-control"
                           id="editar_meses"
                           min="1"
                           required
                           placeholder="Número de meses">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeEditarDetalleModal()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
        </div>
    </form>
</x-modal>
