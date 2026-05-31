<x-modal name="confirm-move-user-mesa" :show="false" maxWidth="md">
    <div class="modal-header modal-header-info">
        <h5 class="modal-title">
            <i class="fas fa-arrow-right-to-bracket me-2"></i>
            Mover Usuario a Mesa de Trabajo
        </h5>
        <button type="button" class="btn-close btn-close-white" @click="$dispatch('close-modal', 'confirm-move-user-mesa')"></button>
    </div>
    <div class="modal-body">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>¿Estás seguro de mover este usuario a la mesa de trabajo?</strong>
        </div>

        <p>El usuario <strong id="confirm_move_mesa_user_name"></strong> será movido a la cuenta de <strong>Mesa de Trabajo</strong>.</p>

        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            La Mesa de Trabajo es un espacio temporal donde puedes organizar usuarios antes de asignarlos a cuentas definitivas.
        </div>

        <form id="confirm_move_mesa_form" method="POST">
            @csrf
            <input type="hidden" id="confirm_move_mesa_user_id" name="iddet">
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="$dispatch('close-modal', 'confirm-move-user-mesa')">
            <i class="fas fa-times me-1"></i>
            Cancelar
        </button>
        <button type="submit" form="confirm_move_mesa_form" class="btn btn-info">
            <i class="fas fa-arrow-right-to-bracket me-1"></i>
            Sí, Mover a Mesa
        </button>
    </div>
</x-modal>
