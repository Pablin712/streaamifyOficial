<x-modal name="delete-servicio" maxWidth="md" :closeable="true">
    <div class="modal-header p-4">
        <h5 class="modal-title fw-bold">
            <i class="fas fa-exclamation-triangle text-danger"></i> Confirmar Eliminación
        </h5>
        <button type="button" class="btn-close" x-on:click="$dispatch('close-modal', 'delete-servicio')"></button>
    </div>

    <div class="modal-body p-4">
        <div class="text-center mb-3">
            <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
            <p class="mb-2 fw-semibold">¿Estás seguro de que deseas eliminar este servicio?</p>
            <p class="text-muted small mb-0">Esta acción no se puede deshacer.</p>
        </div>

        <div class="alert alert-warning mb-0" role="alert">
            <strong><i class="fas fa-info-circle"></i> Información del servicio:</strong>
            <div class="mt-2">
                <p class="mb-1"><strong>Código:</strong> <span id="delete-idser-info">-</span></p>
                <p class="mb-1"><strong>Nombre:</strong> <span id="delete-nombreser-info">-</span></p>
                <p class="mb-1"><strong>Precio Completo:</strong> $<span id="delete-completoser-info">-</span></p>
                <p class="mb-0"><strong>Precio Individual:</strong> $<span id="delete-precioser-info">-</span></p>
            </div>
        </div>

        <input type="hidden" id="delete-idser" value="">
    </div>

    <div class="modal-footer p-4">
        <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'delete-servicio')">
            <i class="fas fa-times"></i> Cancelar
        </button>
        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
            <i class="fas fa-trash"></i> Eliminar
        </button>
    </div>
</x-modal>
