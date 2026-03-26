<x-modal name="createValorModal" :show="false" maxWidth="lg">
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-dollar-sign me-2"></i>Nuevo Valor de Servicio
        </h5>
        <button type="button" class="btn-close" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createValorModal' }))"></button>
    </div>
    <form id="createValorForm" onsubmit="if (typeof submitCreateValor === 'function') { submitCreateValor(event); } else if (typeof submitCreate === 'function') { submitCreate(event); }">
        @csrf
        <div class="modal-body">
            <div class="row g-3">
                <!-- Servicio -->
                <div class="col-md-6">
                    <label for="create_idser" class="form-label required">
                        <i class="fas fa-tv me-1"></i>Servicio
                    </label>
                    <select class="form-control searchable-select"
                            id="create_idser"
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
                    <label for="create_idpro" class="form-label required">
                        <i class="fas fa-truck me-1"></i>Proveedor
                    </label>
                    <select class="form-control searchable-select"
                            id="create_idpro"
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
                    <label for="create_costoval" class="form-label required">
                        <i class="fas fa-dollar-sign me-1"></i>Costo
                    </label>
                    <input type="number"
                           class="form-control"
                           id="create_costoval"
                           name="costoval"
                           step="0.01"
                           min="0"
                           required
                           placeholder="0.00">
                </div>

                <!-- Tipo de Valor -->
                <div class="col-md-6">
                    <label for="create_tipoval" class="form-label required">
                        <i class="fas fa-tag me-1"></i>Tipo de Valor
                    </label>
                    <select class="form-control searchable-select"
                            id="create_tipoval"
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
                    <label for="create_pantminval" class="form-label required">
                        <i class="fas fa-tv me-1"></i>Pantallas Mín
                    </label>
                    <input type="number"
                           class="form-control"
                           id="create_pantminval"
                           name="pantminval"
                           min="1"
                           required
                           placeholder="1">
                </div>

                <!-- Pantallas Máximas -->
                <div class="col-md-4">
                    <label for="create_pantmaxval" class="form-label required">
                        <i class="fas fa-tv me-1"></i>Pantallas Máx
                    </label>
                    <input type="number"
                           class="form-control"
                           id="create_pantmaxval"
                           name="pantmaxval"
                           min="1"
                           required
                           placeholder="4">
                </div>

                <!-- Meses -->
                <div class="col-md-4">
                    <label for="create_mesesval" class="form-label required">
                        <i class="fas fa-calendar me-1"></i>Meses
                    </label>
                    <input type="number"
                           class="form-control"
                           id="create_mesesval"
                           name="mesesval"
                           min="1"
                           required
                           placeholder="1">
                </div>

                <!-- Bot (URL) -->
                <div class="col-12">
                    <label for="create_bot" class="form-label">
                        <i class="fas fa-robot me-1"></i>Bot (URL)
                    </label>
                    <input type="text"
                           class="form-control"
                           id="create_bot"
                           name="bot"
                           placeholder="https://...">
                    <small class="text-muted">URL del bot (opcional)</small>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createValorModal' }))">
                <i class="fas fa-times me-1"></i>Cancelar
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save me-1"></i>Guardar Valor
            </button>
        </div>
    </form>
</x-modal>
