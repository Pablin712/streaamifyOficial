<x-modal name="crear-costo">
    <div class="modal-header">
        <h5 class="modal-title">Crear Costo</h5>
        <button type="button" class="btn-close" @click="$dispatch('close-modal', 'crear-costo')" aria-label="Cerrar"></button>
    </div>

    <form id="createCostoForm" onsubmit="submitCreateCosto(event)">
        @csrf
        <div class="modal-body">
            <!-- Selector de Cuentas -->
            <div class="form-group mb-3">
                <label for="create_idcue">Cuenta <span class="text-danger">*</span></label>
                <select id="create_idcue" name="idcue" class="form-control searchable-select" required
                        data-placeholder="Seleccione una cuenta...">
                    <option value="">-- Selecciona una Cuenta --</option>
                    @foreach($cuentas as $cuenta)
                        <option value="{{ $cuenta->idcue }}">{{ $cuenta->usuariocue }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Campos del Costo -->
            <div class="form-group mb-3">
                <label for="create_descripcioncos">Descripción <span class="text-danger">*</span></label>
                <input type="text" name="descripcioncos" id="create_descripcioncos" class="form-control" required maxlength="50">
            </div>
            <div class="form-group mb-3">
                <label for="create_montocos">Monto <span class="text-danger">*</span></label>
                <input type="number" name="montocos" id="create_montocos" class="form-control" step="0.01" min="0" required>
            </div>
            <div class="form-group mb-3">
                <label for="create_fechacos">Fecha</label>
                <input type="date" name="fechacos" id="create_fechacos" class="form-control" value="{{ now()->format('Y-m-d') }}">
            </div>

            <!-- Checkbox para indicar si se pagó -->
            <div class="form-check mb-3 text-start">
                <input style="width: 20px; height: 20px;" type="checkbox" id="create_se_pago" name="se_pago" value="1" checked onchange="toggleBancoField()">
                <label class="form-check-label" for="create_se_pago">
                    ¿Se pagó? <small class="text-muted">(Si no se marca, se creará una deuda pendiente)</small>
                </label>
            </div>

            <div class="form-group mb-3" id="banco_field_container">
                <label for="create_banco_id">Banco <span class="text-danger">*</span></label>
                <select id="create_banco_id" name="banco_id" class="form-control searchable-select"
                        data-placeholder="Seleccione un banco...">
                    <option value="">-- Selecciona un Banco --</option>
                    @foreach($bancos as $banco)
                        <option value="{{ $banco->idban }}">{{ $banco->nombreban }} ({{ ucfirst($banco->tipoban) }}) - ${{ number_format($banco->monto, 2) }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Requerido solo si se marca como pagado</small>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="$dispatch('close-modal', 'crear-costo')">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btn-submit-create-costo">
                <span id="btn-text-create-costo">Guardar</span>
                <span id="btn-spinner-create-costo" class="spinner-border spinner-border-sm d-none" role="status">
                    <span class="visually-hidden">Guardando...</span>
                </span>
            </button>
        </div>
    </form>
</x-modal>

<script>
function toggleBancoField() {
    const sePago = document.getElementById('create_se_pago').checked;
    const bancoContainer = document.getElementById('banco_field_container');
    const bancoSelect = document.getElementById('create_banco_id');

    if (sePago) {
        bancoContainer.style.display = 'block';
        bancoSelect.required = true;
    } else {
        bancoContainer.style.display = 'none';
        bancoSelect.required = false;
        bancoSelect.value = '';
    }
}

function submitCreateCosto(event) {
    event.preventDefault();

    const form = document.getElementById('createCostoForm');
    if (!form) {
        console.error('Formulario no encontrado');
        return;
    }

    const formData = new FormData(form);
    const submitBtn = document.getElementById('btn-submit-create-costo');
    const btnText = document.getElementById('btn-text-create-costo');
    const btnSpinner = document.getElementById('btn-spinner-create-costo');

    // Deshabilitar botón
    if (submitBtn) {
        submitBtn.disabled = true;
    }
    if (btnText) {
        btnText.classList.add('d-none');
    }
    if (btnSpinner) {
        btnSpinner.classList.remove('d-none');
    }

    fetch('{{ route("costos.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            if (typeof showTemporaryAlert === 'function') {
                showTemporaryAlert('success', data.message);
            }

            // Cerrar modal
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'crear-costo' }));

            // Resetear formulario
            form.reset();

            // Recargar la página después de un breve delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Mostrar errores
            let errorMessage = data.message || 'Error al crear el costo';

            if (data.errors) {
                errorMessage += '\n\n';
                Object.values(data.errors).forEach(errors => {
                    errors.forEach(error => {
                        errorMessage += '- ' + error + '\n';
                    });
                });
            }

            if (typeof showTemporaryAlert === 'function') {
                showTemporaryAlert('danger', errorMessage);
            } else {
                alert(errorMessage);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const errorMsg = 'Error al procesar la solicitud. Por favor intenta nuevamente.';

        if (typeof showTemporaryAlert === 'function') {
            showTemporaryAlert('danger', errorMsg);
        } else {
            alert(errorMsg);
        }
    })
    .finally(() => {
        // Habilitar botón
        if (submitBtn) {
            submitBtn.disabled = false;
        }
        if (btnText) {
            btnText.classList.remove('d-none');
        }
        if (btnSpinner) {
            btnSpinner.classList.add('d-none');
        }
    });
}

// Inicializar estado del campo banco al cargar
document.addEventListener('DOMContentLoaded', function() {
    toggleBancoField();
});
</script>

