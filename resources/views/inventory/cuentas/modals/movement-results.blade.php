<x-modal name="movement-results" :show="false" maxWidth="2xl">
    <div class="modal-header border-bottom">
        <h5 class="modal-title">
            <i class="fas fa-paper-plane me-2"></i>
            Mensajes de Entrega
        </h5>
        <button type="button" class="btn-close" onclick="closeMovementResultsModal()"></button>
    </div>
    <div class="modal-body bg-body">
        <div class="alert alert-info mb-3" id="movement-results-summary">
            Los usuarios fueron movidos correctamente. Copia el mensaje correspondiente para entregar al cliente.
        </div>

        <div id="movement-results-list" class="d-grid gap-3"></div>
    </div>
    <div class="modal-footer border-top">
        <button type="button" class="btn btn-secondary" onclick="closeMovementResultsModal()">
            Cerrar
        </button>
    </div>
</x-modal>

<script>
    window.movementResultsNeedsReload = false;

    function escapeHtml(value) {
        return (value ?? '').toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function showMovementToast(message, type = 'success') {
        const existing = document.getElementById('movement-results-toast');
        if (existing) {
            existing.remove();
        }

        const toast = document.createElement('div');
        toast.id = 'movement-results-toast';
        toast.className = `alert alert-${type} shadow-sm position-fixed top-0 end-0 m-3`;
        toast.style.zIndex = '200000';
        toast.style.minWidth = '280px';
        toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2"></i>${escapeHtml(message)}`;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('fade');
            toast.style.opacity = '0';
        }, 1400);

        setTimeout(() => {
            toast.remove();
        }, 1900);
    }

    function renderMovementResults(movements, summaryMessage) {
        const container = document.getElementById('movement-results-list');
        const summary = document.getElementById('movement-results-summary');

        if (!container || !summary) {
            return;
        }

        summary.textContent = summaryMessage || 'Los usuarios fueron movidos correctamente. Copia el mensaje correspondiente para entregar al cliente.';

        container.innerHTML = (movements || []).map((movement, index) => `
            <div class="card border shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                        <div>
                            <h6 class="mb-1">${escapeHtml(movement.cliente || 'Cliente')}</h6>
                            <div class="text-muted small">
                                ${escapeHtml(movement.servicio_origen || '')} → ${escapeHtml(movement.servicio_destino || '')}
                            </div>
                            <div class="small mt-1">
                                Cuenta destino: <strong>${escapeHtml(movement.usuario_destino || movement.cuenta_destino || '')}</strong>
                                ${movement.perfil_destino ? ` | Perfil ${escapeHtml(movement.perfil_destino)}` : ''}
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="copyMovementDeliveryMessage(${index})">
                            <i class="fas fa-copy me-1"></i>Copiar Mensaje
                        </button>
                    </div>
                    <textarea class="form-control font-monospace bg-body-secondary text-body border" rows="7" readonly data-movement-message-index="${index}">${escapeHtml(movement.mensaje_entrega || '')}</textarea>
                </div>
            </div>
        `).join('');
    }

    function openMovementResultsModal(payload) {
        if (!payload || !Array.isArray(payload.movements) || payload.movements.length === 0) {
            showMovementToast(payload?.message || 'No hay mensajes de entrega disponibles.', 'warning');
            return;
        }

        renderMovementResults(payload.movements, payload.message);
        window.movementResultsNeedsReload = true;
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'movement-results' }));
    }

    function copyMovementDeliveryMessage(index) {
        const textarea = document.querySelector(`[data-movement-message-index="${index}"]`);
        if (!textarea || !textarea.value) {
            showMovementToast('No se pudo obtener el mensaje.', 'warning');
            return;
        }

        navigator.clipboard.writeText(textarea.value)
            .then(() => showMovementToast('Mensaje copiado al portapapeles', 'success'))
            .catch(() => showMovementToast('No se pudo copiar el mensaje', 'warning'));
    }

    function closeMovementResultsModal() {
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'movement-results' }));

        if (window.movementResultsNeedsReload) {
            window.movementResultsNeedsReload = false;
            window.location.reload();
        }
    }
</script>
