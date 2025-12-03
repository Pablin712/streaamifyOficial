<x-modal name="createCuentaModal" :show="false" maxWidth="lg">
    <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
            <i class="fas fa-plus-circle me-2"></i>Crear Nueva Cuenta
        </h5>
        <button type="button" class="btn-close btn-close-white" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createCuentaModal' }))"></button>
    </div>
    <form id="createCuentaForm" onsubmit="submitCreate(event)">
        @csrf
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="idcue" class="form-label fw-semibold">
                        <i class="fas fa-id-card text-primary me-1"></i>ID de Cuenta *
                    </label>
                    <input type="text" name="idcue" id="idcue" class="form-control" maxlength="20"
                        placeholder="Ej: NET001" required style="text-transform: uppercase;">
                    <small class="text-muted">Máximo 20 caracteres (se convertirá a mayúsculas)</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="idval" class="form-label fw-semibold">
                        <i class="fas fa-layer-group text-primary me-1"></i>Servicio/Valor *
                    </label>
                    <select name="idval" id="idval" class="form-select" required>
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
                    <label for="usuariocue" class="form-label fw-semibold">
                        <i class="fas fa-user text-primary me-1"></i>Usuario *
                    </label>
                    <input type="text" name="usuariocue" id="usuariocue" class="form-control"
                        maxlength="50" placeholder="Usuario de la cuenta" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="contrasenacue" class="form-label fw-semibold">
                        <i class="fas fa-lock text-primary me-1"></i>Contraseña *
                    </label>
                    <div class="input-group">
                        <input type="password" name="contrasenacue" id="contrasenacue" class="form-control"
                            maxlength="50" placeholder="Contraseña" required>
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('contrasenacue')">
                            <i class="fas fa-eye" id="contrasenacue-icon"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fechavencue" class="form-label fw-semibold">
                        <i class="fas fa-calendar-alt text-primary me-1"></i>Fecha de Vencimiento *
                    </label>
                    <input type="date" name="fechavencue" id="fechavencue" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="caidacue" class="form-label fw-semibold">
                        <i class="fas fa-heartbeat text-primary me-1"></i>Estado de la Cuenta
                    </label>
                    <select name="caidacue" id="caidacue" class="form-select" required>
                        <option value="0" selected>Activa (Funcionando)</option>
                        <option value="1">Dañada (No funciona)</option>
                    </select>
                </div>
            </div>

            <hr class="my-3">

            <h6 class="fw-bold text-secondary mb-3">
                <i class="fas fa-dollar-sign me-2"></i>Costo Inicial (Opcional)
            </h6>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="descripcioncos" class="form-label">Descripción del Costo</label>
                    <input type="text" name="descripcioncos" id="descripcioncos" class="form-control"
                        maxlength="50" placeholder="Ej: Compra de cuenta Netflix">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="montocos" class="form-label">Monto ($)</label>
                    <input type="number" name="montocos" id="montocos" class="form-control"
                        step="0.01" min="0" placeholder="0.00">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Guardar Cuenta
            </button>
        </div>
    </form>
</x-modal>

<script>
function closeCreateModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createCuentaModal' }));
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

// Auto-convertir idcue a mayúsculas
document.getElementById('idcue')?.addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});

// Validar que si hay monto, haya descripción
document.getElementById('createCuentaForm')?.addEventListener('submit', function(e) {
    const monto = document.getElementById('montocos').value;
    const descripcion = document.getElementById('descripcioncos').value;

    if (monto && !descripcion) {
        e.preventDefault();
        alert('Si ingresa un monto, debe proporcionar una descripción del costo.');
        document.getElementById('descripcioncos').focus();
    }
});
</script>
