<x-modal name="confirm-delete-user" :show="false" maxWidth="md">
    <div class="modal-header modal-header-danger">
        <h5 class="modal-title">
            <i class="fas fa-trash me-2"></i>
            Eliminar Usuario
        </h5>
        <button type="button" class="btn-close btn-close-white" @click="$dispatch('close-modal', 'confirm-delete-user')"></button>
    </div>
    <div class="modal-body">
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>¡Atención! Esta acción desactivará el usuario</strong>
        </div>

        <p>El usuario <strong id="confirm_delete_user_name"></strong> será <strong>desactivado</strong> (no eliminado permanentemente).</p>

        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            El usuario quedará inactivo pero sus datos se conservarán en el sistema. Podrás reactivarlo posteriormente si es necesario.
        </div>

        <form id="confirm_delete_user_form" method="POST">
            @csrf
            @method('DELETE')
            <input type="hidden" id="confirm_delete_user_id" name="iddet">
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="$dispatch('close-modal', 'confirm-delete-user')">
            <i class="fas fa-times me-1"></i>
            Cancelar
        </button>
        <button type="submit" form="confirm_delete_user_form" class="btn btn-danger">
            <i class="fas fa-trash me-1"></i>
            Sí, Desactivar Usuario
        </button>
    </div>
</x-modal>
