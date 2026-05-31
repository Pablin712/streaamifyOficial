<x-modal name="edit-profile" :show="false" maxWidth="md">
    <div class="modal-header modal-header-primary">
        <h5 class="modal-title">
            <i class="fas fa-edit me-2"></i>
            Editar PIN del Perfil
        </h5>
        <button type="button" class="btn-close btn-close-white" @click="$dispatch('close-modal', 'edit-profile')"></button>
    </div>
    <div class="modal-body">
        <form id="edit_profile_form">
            <input type="hidden" id="edit_profile_id">

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Modifica el PIN de este perfil de la cuenta.
            </div>

            <div class="mb-3">
                <label for="edit_profile_numero" class="form-label">
                    <i class="fas fa-hashtag text-primary me-2"></i>
                    Número de Perfil
                </label>
                <input
                    type="text"
                    class="form-control"
                    id="edit_profile_numero"
                    readonly
                    disabled
                >
            </div>

            <div class="mb-3">
                <label for="edit_profile_pin" class="form-label">
                    <i class="fas fa-key text-primary me-2"></i>
                    PIN del Perfil
                </label>
                <input
                    type="text"
                    class="form-control"
                    id="edit_profile_pin"
                    name="pinper"
                    required
                    maxlength="50"
                >
                <div class="form-text">Ingresa el nuevo PIN para este perfil.</div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="$dispatch('close-modal', 'edit-profile')">
            <i class="fas fa-times me-1"></i>
            Cancelar
        </button>
        <button type="button" class="btn btn-primary" onclick="submitEditProfile()" style="background-color: #0d6efd; color: white;">
            <i class="fas fa-save me-1"></i>
            Guardar Cambios
        </button>
    </div>
</x-modal>
