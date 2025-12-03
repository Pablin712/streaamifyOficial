// Archivo compartido para manejar la creación de clientes desde vistas de ventas

// Función para manejar el submit del formulario de crear cliente
async function submitCreateClienteFromVenta(event) {
    event.preventDefault();
    console.log('📤 Creando cliente desde vista de ventas...');

    const formData = new FormData(event.target);

    try {
        const response = await fetch('/clientes', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            console.log('✅ Cliente creado:', data);

            // Agregar el nuevo cliente al select de clientes
            const selectCliente = document.getElementById('idcli');
            if (selectCliente) {
                const option = new Option(
                    `${data.cliente.nombrecli} - ${data.cliente.telefonocli}`,
                    data.cliente.idcli,
                    true,
                    true
                );
                selectCliente.add(option);

                // Trigger change event para Select2
                $(selectCliente).trigger('change');
            }

            // Mostrar mensaje de éxito
            showAlert('Cliente creado exitosamente', 'success');

            // Cerrar el modal
            closeCreateClienteModal();

            // Limpiar el formulario
            event.target.reset();
        } else {
            console.error('❌ Error al crear:', data);
            showAlert(data.message || 'Error al crear el cliente', 'danger');
        }
    } catch (error) {
        console.error('❌ Error de red:', error);
        showAlert('Error de conexión. Por favor, intenta nuevamente.', 'danger');
    }
}

// Función para cerrar el modal de crear cliente
function closeCreateClienteModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createClienteModal' }));
}

// Función para mostrar alertas
function showAlert(message, type = 'info') {
    // Buscar o crear contenedor de alertas
    let alertContainer = document.getElementById('alert-container-ventas');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alert-container-ventas';
        alertContainer.className = 'position-fixed top-0 end-0 p-3';
        alertContainer.style.zIndex = '9999';
        document.body.appendChild(alertContainer);
    }

    const alertId = 'alert-' + Date.now();
    const alertHtml = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    alertContainer.insertAdjacentHTML('beforeend', alertHtml);

    // Auto-remover después de 5 segundos
    setTimeout(() => {
        const alertElement = document.getElementById(alertId);
        if (alertElement) {
            alertElement.classList.remove('show');
            setTimeout(() => alertElement.remove(), 150);
        }
    }, 5000);
}
