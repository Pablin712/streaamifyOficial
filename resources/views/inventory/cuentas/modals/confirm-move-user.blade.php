<x-modal name="confirm-move-user" :show="false" maxWidth="md">
    <div class="modal-header" style="background-color: #6c757d; color: #ffffff;">
        <h5 class="modal-title">
            <i class="fas fa-exchange-alt me-2" style="color: #ffffff;"></i>
            Mover Usuario a Otra Cuenta
        </h5>
        <button type="button" class="btn-close btn-close-white" @click="$dispatch('close-modal', 'confirm-move-user')"></button>
    </div>
    <div class="modal-body">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>¿Estás seguro de mover este usuario?</strong>
        </div>

        <p>El usuario <strong id="confirm_move_user_name"></strong> será movido automáticamente a otra cuenta disponible con espacio libre.</p>

        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            El sistema buscará una cuenta disponible del mismo servicio con perfiles libres.
        </div>

        <form id="confirm_move_user_form" method="POST">
            @csrf
            <input type="hidden" id="confirm_move_user_id" name="iddet">
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="$dispatch('close-modal', 'confirm-move-user')">
            <i class="fas fa-times me-1"></i>
            Cancelar
        </button>
        <button type="submit" form="confirm_move_user_form" class="btn btn-dark">
            <i class="fas fa-exchange-alt me-1"></i>
            Sí, Mover Usuario
        </button>
    </div>
</x-modal>
