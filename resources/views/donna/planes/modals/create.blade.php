<x-modal name="createDonnaPlanModal" :show="false" maxWidth="2xl">
    <div class="modal-header modal-header-donna-blue">
        <h5 class="modal-title">
            <i class="bi bi-robot me-2"></i>Nuevo Plan Donna
        </h5>
        <button type="button" class="btn-close btn-close-white"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createDonnaPlanModal' }))">
        </button>
    </div>
    <form id="createDonnaPlanForm" onsubmit="submitCreatePlan(event)">
        @csrf
        <div class="modal-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label required"><i class="fas fa-tag me-1"></i>Tipo de servicio</label>
                    <select name="service_type" id="create_service_type" class="form-control" required>
                        <option value="personal">Personal — Secretaria privada del dueño</option>
                        <option value="business">Business — Atiende a clientes finales</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label required"><i class="fas fa-barcode me-1"></i>Código único</label>
                    <input type="text" name="code" id="create_code" class="form-control"
                        placeholder="donna_personal" maxlength="50" required>
                    <small class="text-muted">Sin espacios. Ej: <code>donna_personal</code></small>
                </div>

                <div class="col-12">
                    <label class="form-label required"><i class="fas fa-heading me-1"></i>Nombre del plan</label>
                    <input type="text" name="name" id="create_name" class="form-control"
                        placeholder="Donna Personal" maxlength="100" required>
                </div>

                <div class="col-12">
                    <label class="form-label"><i class="fas fa-align-left me-1"></i>Descripción</label>
                    <textarea name="description" id="create_description" class="form-control" rows="2"
                        placeholder="Describe brevemente qué incluye este plan..."></textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label required"><i class="fas fa-dollar-sign me-1"></i>Precio</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="price" id="create_price" class="form-control"
                            step="0.01" min="0" placeholder="19.99" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label required"><i class="fas fa-sync me-1"></i>Ciclo de pago</label>
                    <select name="billing_cycle" id="create_billing_cycle" class="form-control" required>
                        <option value="monthly" selected>Mensual</option>
                        <option value="yearly">Anual</option>
                        <option value="one_time">Pago único</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label required"><i class="fas fa-toggle-on me-1"></i>Estado</label>
                    <select name="is_active" id="create_is_active" class="form-control" required>
                        <option value="1" selected>Activo (visible en /donna)</option>
                        <option value="0">Inactivo (oculto)</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-sort me-1"></i>Orden</label>
                    <input type="number" name="sort_order" id="create_sort_order" class="form-control" value="0" min="0">
                    <small class="text-muted">Menor = aparece primero</small>
                </div>

                <div class="col-12">
                    <label class="form-label"><i class="fas fa-list-ul me-1"></i>Características del plan</label>
                    <div id="create_features_list"></div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2"
                        onclick="addFeatureRowCreate()">
                        <i class="fas fa-plus me-1"></i> Agregar característica
                    </button>
                    <small class="text-muted d-block mt-1">Aparecen con ✓ en la página pública /donna.</small>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createDonnaPlanModal' }))">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Crear Plan
            </button>
        </div>
    </form>
</x-modal>
