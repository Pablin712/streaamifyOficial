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
                    <label for="tipo_cuenta" class="form-label fw-semibold">
                        <i class="fas fa-layer-group text-primary me-1"></i>Tipo de Cuenta *
                    </label>
                    <select name="tipo_cuenta" id="tipo_cuenta" class="form-select" required>
                        <option value="">Seleccione tipo...</option>
                        <option value="completa" selected>Completa (Ej: NETFLIX-1)</option>
                        <option value="individual">Individual (Ej: IND.NETFLIX-1)</option>
                    </select>
                    <small class="text-muted">El ID se generará automáticamente</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="idval" class="form-label fw-semibold">
                        <i class="fas fa-cube text-primary me-1"></i>Servicio/Valor *
                    </label>
                    <select name="idval" id="idval" class="form-select searchable-select" required
                            data-placeholder="Seleccione un valor...">
                        <option value="">Seleccione un valor...</option>
                        @foreach ($valores ?? [] as $valor)
                            <option value="{{ $valor->idval }}" data-servicio="{{ $valor->idser }}">
                                {{ $valor->idser }} - {{ $valor->proveedor->nombrepro }} ({{ $valor->mesesval }}m)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Previsualización del ID que se generará -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info py-2">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>ID a generar:</strong>
                        <span id="preview_idcue" class="fw-bold">Seleccione servicio y tipo</span>
                    </div>
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
                    <select name="caidacue" id="caidacue" class="form-select searchable-select" required
                            data-placeholder="Seleccionar estado...">
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

            <div class="row" id="banco-section-create">
                <div class="col-12 mb-3">
                    <label for="banco_id" class="form-label">Banco</label>
                    <select name="banco_id" id="banco_id" class="form-select searchable-select"
                            data-placeholder="Seleccione un banco...">
                        <option value="">Seleccione un banco...</option>
                        @foreach ($bancos ?? [] as $banco)
                            <option value="{{ $banco->idban }}">
                                {{ $banco->nombreban }} ({{ ucfirst($banco->tipoban) }}) - ${{ number_format($banco->monto, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Checkbox para indicar si se pagó -->
            <div class="form-check mb-3 text-start">
                <input style="width: 20px; height: 20px;" type="checkbox" id="se_pago_create" name="se_pago" value="1" checked>
                <label class="form-check-label" for="se_pago_create">
                    ¿Se pagó? <small class="text-muted">(Si no se marca, se creará una deuda pendiente)</small>
                </label>
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

// Función para controlar el campo de banco según el checkbox
function toggleBancoFieldCreate() {
    const sePago = document.getElementById('se_pago_create');
    const bancoField = document.getElementById('banco_id');
    const bancoSection = document.getElementById('banco-section-create');
    const bancoLabel = document.querySelector('label[for="banco_id"]');

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
    const sePagoCheckboxCreate = document.getElementById('se_pago_create');
    if (sePagoCheckboxCreate) {
        sePagoCheckboxCreate.addEventListener('change', toggleBancoFieldCreate);
        // Ejecutar al cargar para estado inicial
        toggleBancoFieldCreate();
    }

    // Previsualización del ID de cuenta
    const tipoCuentaSelect = document.getElementById('tipo_cuenta');
    const idvalSelect = document.getElementById('idval');
    const previewElement = document.getElementById('preview_idcue');

    function actualizarPreview() {
        const tipoCuenta = tipoCuentaSelect?.value;
        const idvalOption = idvalSelect?.options[idvalSelect.selectedIndex];
        const servicio = idvalOption?.getAttribute('data-servicio');

        if (!tipoCuenta || !servicio) {
            if (previewElement) {
                previewElement.textContent = 'Seleccione servicio y tipo';
                previewElement.style.color = '#6c757d';
            }
            return;
        }

        const prefijo = tipoCuenta === 'individual' ? 'IND.' : '';
        const preview = `${prefijo}${servicio}-[AUTO]`;

        if (previewElement) {
            previewElement.textContent = preview;
            previewElement.style.color = '#0d6efd';
        }
    }

    if (tipoCuentaSelect) {
        tipoCuentaSelect.addEventListener('change', actualizarPreview);
    }
    if (idvalSelect) {
        idvalSelect.addEventListener('change', actualizarPreview);
    }
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
