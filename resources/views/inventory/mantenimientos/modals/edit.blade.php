<x-modal name="edit-mantenimiento" maxWidth="lg" :closeable="true">
    <div class="modal-header p-4">
        <h5 class="modal-title fw-bold">
            <i class="fas fa-edit"></i> Editar Mantenimiento
        </h5>
        <button type="button" class="btn-close" x-on:click="$dispatch('close-modal', 'edit-mantenimiento')"></button>
    </div>
    <form id="edit-form" onsubmit="submitEdit(event)">
        <div class="modal-body p-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-idman" name="idman">

            <div class="mb-3">
                <label for="edit-cuenta-info" class="form-label fw-semibold">Cuenta</label>
                <input type="text" id="edit-cuenta-info" class="form-control" readonly>
            </div>

            <div class="mb-3">
                <label for="edit-fechaman" class="form-label fw-semibold">Fecha de Mantenimiento</label>
                <input type="date" name="fechaman" id="edit-fechaman" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="edit-descripcionman" class="form-label fw-semibold">Descripción</label>
                <textarea name="descripcionman" id="edit-descripcionman" class="form-control" rows="3" required></textarea>
            </div>
        </div>
        <div class="modal-footer p-4">
            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'edit-mantenimiento')">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save"></i> Actualizar
            </button>
        </div>
    </form>
</x-modal>
