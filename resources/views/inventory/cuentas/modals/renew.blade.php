<x-modal name="renewCuentaModal" :show="false" maxWidth="md">
    <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
            <i class="fas fa-sync-alt me-2"></i>Renovar Cuenta
        </h5>
        <button type="button" class="btn-close btn-close-white" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'renewCuentaModal' }))"></button>
    </div>
    <form id="renewCuentaForm" onsubmit="submitRenew(event)">
        @csrf
        <input type="hidden" id="renew_idcue" name="idcue">

        <div class="modal-body">
            <div class="alert alert-info mb-3">
                <h6 class="fw-bold mb-2">
                    <i class="fas fa-info-circle me-2"></i>Información de la Cuenta
                </h6>
                <div class="row">
                    <div class="col-5 fw-semibold">ID Cuenta:</div>
                    <div class="col-7" id="renew_cuenta_id"></div>
                </div>
                <div class="row">
                    <div class="col-5 fw-semibold">Servicio:</div>
                    <div class="col-7" id="renew_cuenta_servicio"></div>
                </div>
                <div class="row">
                    <div class="col-5 fw-semibold">Vence Actualmente:</div>
                    <div class="col-7">
                        <span id="renew_fecha_actual" class="badge bg-warning"></span>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="nuevafechavencue" class="form-label fw-semibold">
                    <i class="fas fa-calendar-check text-success me-1"></i>Nueva Fecha de Vencimiento *
                </label>
                <input type="date" name="nuevafechavencue" id="nuevafechavencue" class="form-control" required>
                <small class="text-muted">La fecha debe ser posterior a la fecha actual</small>
            </div>

            <div class="mb-3">
                <button type="button" class="btn btn-sm btn-outline-success" onclick="calculateMonthsAhead(1)">
                    <i class="fas fa-plus me-1"></i>+1 Mes
                </button>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="calculateMonthsAhead(2)">
                    <i class="fas fa-plus me-1"></i>+2 Meses
                </button>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="calculateMonthsAhead(3)">
                    <i class="fas fa-plus me-1"></i>+3 Meses
                </button>
            </div>

            <hr class="my-3">

            <h6 class="fw-bold text-secondary mb-3">
                <i class="fas fa-dollar-sign me-2"></i>Costo de Renovación *
            </h6>

            <div class="mb-3">
                <label for="renew_descripcioncos" class="form-label">Descripción del Costo</label>
                <input type="text" name="descripcioncos" id="renew_descripcioncos" class="form-control"
                    maxlength="50" required>
            </div>

            <div class="mb-3">
                <label for="renew_montocos" class="form-label">Monto ($) *</label>
                <input type="number" name="montocos" id="renew_montocos" class="form-control"
                    step="0.01" min="0" placeholder="0.00" required>
            </div>

            <div class="mb-3" id="banco-section-renew">
                <label for="renew_banco_id" class="form-label">Banco</label>
                <select name="banco_id" id="renew_banco_id" class="form-select searchable-select"
                        data-placeholder="Seleccione un banco...">
                    <option value="">Seleccione un banco...</option>
                    @foreach ($bancos ?? [] as $banco)
                        <option value="{{ $banco->idban }}">
                            {{ $banco->nombreban }} ({{ ucfirst($banco->tipoban) }}) - ${{ number_format($banco->monto, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Checkbox para indicar si se pagó -->
            <div class="form-check mb-3 text-start">
                <input style="width: 20px; height: 20px;" type="checkbox" id="se_pago_renew" name="se_pago" value="1" checked>
                <label class="form-check-label" for="se_pago_renew">
                    ¿Se pagó? <small class="text-muted">(Si no se marca, se creará una deuda pendiente)</small>
                </label>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeRenewModal()">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check me-1"></i> Renovar Cuenta
            </button>
        </div>
    </form>
</x-modal>

<script>
function calculateMonthsAhead(months) {
    const currentDateStr = document.getElementById('renew_fecha_actual').textContent;
    const currentDate = new Date(currentDateStr);

    if (isNaN(currentDate.getTime())) {
        // Si no hay fecha actual válida, usar la fecha de hoy
        currentDate = new Date();
    }

    currentDate.setMonth(currentDate.getMonth() + months);

    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
    const day = String(currentDate.getDate()).padStart(2, '0');

    document.getElementById('nuevafechavencue').value = `${year}-${month}-${day}`;
}

// Función para controlar el campo de banco según el checkbox
function toggleBancoFieldRenew() {
    const sePago = document.getElementById('se_pago_renew');
    const bancoField = document.getElementById('renew_banco_id');
    const bancoSection = document.getElementById('banco-section-renew');
    const bancoLabel = document.querySelector('label[for="renew_banco_id"]');

    if (sePago && bancoField && bancoSection) {
        if (sePago.checked) {
            // Si se pagó, el banco es requerido
            bancoField.required = true;
            if (bancoLabel) {
                bancoLabel.innerHTML = 'Banco <span class="text-danger">*</span>';
            }
            bancoSection.style.display = 'block';
        } else {
            // Si no se pagó (deuda), el banco no es requerido
            bancoField.required = false;
            bancoField.value = '';
            if (bancoLabel) {
                bancoLabel.textContent = 'Banco';
            }
            bancoSection.style.display = 'none';
        }
    }
}

// Event listener para el checkbox
document.addEventListener('DOMContentLoaded', function() {
    const sePagoCheckboxRenew = document.getElementById('se_pago_renew');
    if (sePagoCheckboxRenew) {
        sePagoCheckboxRenew.addEventListener('change', toggleBancoFieldRenew);
        // Ejecutar al cargar para estado inicial
        toggleBancoFieldRenew();
    }
});

// Validar que la nueva fecha sea mayor a la actual
document.getElementById('renewCuentaForm')?.addEventListener('submit', function(e) {
    const nuevaFecha = new Date(document.getElementById('nuevafechavencue').value);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    if (nuevaFecha <= hoy) {
        e.preventDefault();
        alert('La nueva fecha de vencimiento debe ser posterior a la fecha actual.');
        document.getElementById('nuevafechavencue').focus();
    }
});
</script>
