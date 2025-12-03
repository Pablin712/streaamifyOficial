<x-modal name="editValorModal" :show="false" maxWidth="lg">
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-edit me-2"></i>Editar Valor de Servicio
        </h5>
        <button type="button" class="btn-close" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editValorModal' }))"></button>
    </div>
    <form id="editValorForm" onsubmit="submitEdit(event)">
        @csrf
        @method('PUT')
        <input type="hidden" id="edit_idval" name="idval">

        <div class="modal-body">
            <!-- ID Display (readonly) -->
            <div class="mb-3">
                <label class="form-label text-muted">
                    <i class="fas fa-hashtag me-1"></i>ID del Valor
                </label>
                <input type="text"
                       class="form-control bg-light"
                       id="edit_idval_display"
                       readonly>
            </div>

            <div class="row g-3">
                <!-- Servicio -->
                <div class="col-md-6">
                    <label for="edit_idser" class="form-label required">
                        <i class="fas fa-tv me-1"></i>Servicio
                    </label>
                    <select class="form-control searchable-select"
                            id="edit_idser"
                            name="idser"
                            required
                            data-placeholder="Seleccionar servicio...">
                        <option value="">Seleccionar servicio...</option>
                        @foreach ($servicios as $servicio)
                            <option value="{{ $servicio->idser }}">{{ $servicio->nombreser }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Proveedor -->
                <div class="col-md-6">
                    <label for="edit_idpro" class="form-label required">
                        <i class="fas fa-truck me-1"></i>Proveedor
                    </label>
                    <select class="form-control searchable-select"
                            id="edit_idpro"
                            name="idpro"
                            required
                            data-placeholder="Seleccionar proveedor...">
                        <option value="">Seleccionar proveedor...</option>
                        @foreach ($proveedores as $proveedor)
                            <option value="{{ $proveedor->idpro }}">{{ $proveedor->nombrepro }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Costo -->
                <div class="col-md-6">
                    <label for="edit_costoval" class="form-label required">
                        <i class="fas fa-dollar-sign me-1"></i>Costo
                    </label>
                    <input type="number"
                           class="form-control"
                           id="edit_costoval"
                           name="costoval"
                           step="0.01"
                           min="0"
                           required
                           placeholder="0.00">
                </div>

                <!-- Tipo de Valor -->
                <div class="col-md-6">
                    <label for="edit_tipoval" class="form-label required">
                        <i class="fas fa-tag me-1"></i>Tipo de Valor
                    </label>
                    <select class="form-control searchable-select"
                            id="edit_tipoval"
                            name="tipoval"
                            required
                            data-placeholder="Seleccionar tipo...">
                        <option value="completo">Completo</option>
                        <option value="individual">Individual</option>
                        <option value="hibrido">Híbrido</option>
                    </select>
                </div>

                <!-- Pantallas Mínimas -->
                <div class="col-md-4">
                    <label for="edit_pantminval" class="form-label required">
                        <i class="fas fa-tv me-1"></i>Pantallas Mín
                    </label>
                    <input type="number"
                           class="form-control"
                           id="edit_pantminval"
                           name="pantminval"
                           min="1"
                           required>
                </div>

                <!-- Pantallas Máximas -->
                <div class="col-md-4">
                    <label for="edit_pantmaxval" class="form-label required">
                        <i class="fas fa-tv me-1"></i>Pantallas Máx
                    </label>
                    <input type="number"
                           class="form-control"
                           id="edit_pantmaxval"
                           name="pantmaxval"
                           min="1"
                           required>
                </div>

                <!-- Meses -->
                <div class="col-md-4">
                    <label for="edit_mesesval" class="form-label required">
                        <i class="fas fa-calendar me-1"></i>Meses
                    </label>
                    <input type="number"
                           class="form-control"
                           id="edit_mesesval"
                           name="mesesval"
                           min="1"
                           required>
                </div>

                <!-- Bot (URL) -->
                <div class="col-12">
                    <label for="edit_bot" class="form-label">
                        <i class="fas fa-robot me-1"></i>Bot (URL)
                    </label>
                    <input type="text"
                           class="form-control"
                           id="edit_bot"
                           name="bot"
                           placeholder="https://...">
                    <small class="text-muted">URL del bot (opcional)</small>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editValorModal' }))">
                <i class="fas fa-times me-1"></i>Cancelar
            </button>
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save me-1"></i>Actualizar Valor
            </button>
        </div>
    </form>
</x-modal>
