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
                    <select class="form-control searchable-select"
                            id="detalle_idser"
                            required
                            data-placeholder="Seleccionar servicio...">
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
                    <select class="form-control searchable-select"
                            id="editar_idser"
                            required
                            data-placeholder="Seleccionar servicio...">
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
