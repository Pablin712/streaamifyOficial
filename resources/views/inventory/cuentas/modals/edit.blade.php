<x-modal name="editCuentaModal" :show="false" maxWidth="lg">
    <div class="modal-header" style="background-color: #fd7e14; color: #ffffff;">
        <h5 class="modal-title">
            <i class="fas fa-edit me-2"></i>Editar Cuenta
        </h5>
        <button type="button" class="btn-close btn-close-white" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editCuentaModal' }))"></button>
    </div>
    <form id="editCuentaForm" onsubmit="submitEdit(event)">
        @csrf
        @method('PUT')
        <input type="hidden" id="edit_idcue" name="idcue">

        <div class="modal-body">
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Cuenta:</strong> <span id="edit_idcue_display" class="text-primary"></span>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="edit_idval" class="form-label fw-semibold">
                        <i class="fas fa-layer-group text-primary me-1"></i>Servicio/Valor *
                    </label>
                    <select name="idval" id="edit_idval" class="form-select searchable-select" required
                            data-placeholder="Seleccione un valor...">
                        <option value="">Seleccione un valor...</option>
                        @foreach ($valores ?? [] as $valor)
                            <option value="{{ $valor->idval }}">
                                {{ $valor->idser }} - {{ $valor->proveedor->nombrepro }} ({{ $valor->mesesval }}m)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="edit_usuariocue" class="form-label fw-semibold">
                        <i class="fas fa-user me-1" style="color: #fd7e14;"></i>Usuario *
                    </label>
                    <input type="text" name="usuariocue" id="edit_usuariocue" class="form-control"
                        maxlength="50" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="edit_contrasenacue" class="form-label fw-semibold">
                        <i class="fas fa-lock me-1" style="color: #fd7e14;"></i>Contraseña *
                    </label>
                    <div class="input-group">
                        <input type="password" name="contrasenacue" id="edit_contrasenacue" class="form-control"
                            maxlength="50" required>
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('edit_contrasenacue')">
                            <i class="fas fa-eye" id="edit_contrasenacue-icon"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="edit_fechavencue" class="form-label fw-semibold">
                        <i class="fas fa-calendar-alt me-1" style="color: #fd7e14;"></i>Fecha de Vencimiento *
                    </label>
                    <input type="date" name="fechavencue" id="edit_fechavencue" class="form-control" required>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="setEditCuentaMonthsAhead(1)">
                            <i class="fas fa-plus me-1"></i>+1 Mes
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="setEditCuentaMonthsAhead(2)">
                            <i class="fas fa-plus me-1"></i>+2 Meses
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="setEditCuentaMonthsAhead(3)">
                            <i class="fas fa-plus me-1"></i>+3 Meses
                        </button>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="edit_caidacue" class="form-label fw-semibold">
                        <i class="fas fa-heartbeat text-primary me-1"></i>Estado de la Cuenta
                    </label>
                    <select name="caidacue" id="edit_caidacue" class="form-select searchable-select" required
                            data-placeholder="Seleccionar estado...">
                        <option value="0">Activa (Funcionando)</option>
                        <option value="1">Dañada (No funciona)</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn" style="background-color: #fd7e14; color: white;">
                <i class="fas fa-save me-1"></i> Actualizar Cuenta
            </button>
        </div>
    </form>
</x-modal>

<script>
function setEditCuentaMonthsAhead(months) {
    const field = document.getElementById('edit_fechavencue');
    if (!field) return;

    const baseDate = new Date();
    baseDate.setMonth(baseDate.getMonth() + months);

    const year = baseDate.getFullYear();
    const month = String(baseDate.getMonth() + 1).padStart(2, '0');
    const day = String(baseDate.getDate()).padStart(2, '0');

    field.value = `${year}-${month}-${day}`;
}

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');

    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
