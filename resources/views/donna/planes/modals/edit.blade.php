<x-modal name="editDonnaPlanModal" :show="false" maxWidth="2xl">
    <div class="modal-header" style="background-color:#E4B100;">
        <h5 class="modal-title text-dark">
            <i class="fas fa-edit me-2"></i>Editar Plan Donna
        </h5>
        <button type="button" class="btn-close"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editDonnaPlanModal' }))">
        </button>
    </div>
    <form id="editDonnaPlanForm" onsubmit="submitEditPlan(event)">
        @csrf
        @method('PUT')
        <input type="hidden" id="edit_plan_id">
        <div class="modal-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label required"><i class="fas fa-tag me-1"></i>Tipo de servicio</label>
                    <select name="service_type" id="edit_service_type" class="form-control" required>
                        <option value="personal">Personal — Secretaria privada del dueño</option>
                        <option value="business">Business — Atiende a clientes finales</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label required"><i class="fas fa-barcode me-1"></i>Código único</label>
                    <input type="text" name="code" id="edit_code" class="form-control" maxlength="50" required>
                </div>

                <div class="col-12">
                    <label class="form-label required"><i class="fas fa-heading me-1"></i>Nombre del plan</label>
                    <input type="text" name="name" id="edit_name" class="form-control" maxlength="100" required>
                </div>

                <div class="col-12">
                    <label class="form-label"><i class="fas fa-align-left me-1"></i>Descripción</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label required"><i class="fas fa-dollar-sign me-1"></i>Precio</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="price" id="edit_price" class="form-control"
                            step="0.01" min="0" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label required"><i class="fas fa-sync me-1"></i>Ciclo de pago</label>
                    <select name="billing_cycle" id="edit_billing_cycle" class="form-control" required>
                        <option value="monthly">Mensual</option>
                        <option value="yearly">Anual</option>
                        <option value="one_time">Pago único</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label required"><i class="fas fa-toggle-on me-1"></i>Estado</label>
                    <select name="is_active" id="edit_is_active" class="form-control" required>
                        <option value="1">Activo (visible en /donna)</option>
                        <option value="0">Inactivo (oculto)</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-sort me-1"></i>Orden</label>
                    <input type="number" name="sort_order" id="edit_sort_order" class="form-control" min="0">
                </div>

                <div class="col-12">
                    <label class="form-label"><i class="fas fa-list-ul me-1"></i>Características del plan</label>
                    <div id="edit_features_list"></div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2"
                        onclick="addFeatureRowEdit()">
                        <i class="fas fa-plus me-1"></i> Agregar característica
                    </button>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editDonnaPlanModal' }))">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-warning fw-bold">
                <i class="fas fa-save me-1"></i> Guardar Cambios
            </button>
        </div>
    </form>
</x-modal>
