// Archivo compartido para manejar la creación de clientes desde vistas de ventas

// Función para manejar el submit del formulario de crear cliente
async function submitCreateClienteFromVenta(event) {
    event.preventDefault();
    console.log('📤 Creando cliente desde vista de ventas...');

    const form = event.target;
    const formData = new FormData(form);

    // Log de datos que se enviarán
    console.log('📋 Datos del formulario:');
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: ${value}`);
    }

    try {
        // Obtener la URL de la ruta desde el atributo data-store-url del formulario
        const storeUrl = form.getAttribute('data-store-url');
        console.log('🔗 URL de envío:', storeUrl);

        if (!storeUrl) {
            throw new Error('No se encontró la URL para guardar el cliente');
        }

        const response = await fetch(storeUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData,
            credentials: 'same-origin'
        });

        console.log('📡 Status de respuesta:', response.status);

        // Verificar si la respuesta es JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            console.error('❌ Respuesta no es JSON:', contentType);
            const text = await response.text();
            console.error('Contenido de respuesta:', text.substring(0, 500));
            throw new Error('La respuesta del servidor no es JSON válido');
        }

        const data = await response.json();
        console.log('📦 Datos recibidos:', data);

        if (response.ok && data.success) {
            console.log('✅ Cliente creado exitosamente:', data);

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

                // Trigger change event para Select2 si existe
                if (typeof $ !== 'undefined' && $.fn.select2) {
                    $(selectCliente).trigger('change');
                }
            }

            // Mostrar mensaje de éxito
            showAlert('Cliente creado exitosamente', 'success');

            // Cerrar el modal
            closeCreateClienteModal();

            // Limpiar el formulario
            form.reset();
        } else {
            console.error('❌ Error al crear:', data);
            const errorMessage = data.message || data.error || 'Error al crear el cliente';

            // Mostrar errores de validación si existen
            if (data.errors) {
                const errorList = Object.values(data.errors).flat().join('\n');
                showAlert(errorList, 'danger');
            } else {
                showAlert(errorMessage, 'danger');
            }
        }
    } catch (error) {
        console.error('❌ Error de red o procesamiento:', error);
        showAlert('Error de conexión. Por favor, intenta nuevamente.\n' + error.message, 'danger');
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
