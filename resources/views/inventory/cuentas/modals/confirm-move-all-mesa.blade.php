<x-modal name="confirm-move-all-mesa" :show="false" maxWidth="md">
    <div class="modal-header" style="background-color: #dc3545; color: #ffffff;">
        <h5 class="modal-title">
            <i class="fas fa-users me-2" style="color: #ffffff;"></i>
            Mover TODOS los Clientes a Mesa de Trabajo
        </h5>
        <button type="button" class="btn-close btn-close-white" @click="$dispatch('close-modal', 'confirm-move-all-mesa')"></button>
    </div>
    <div class="modal-body">
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>¡ATENCIÓN! Acción masiva irreversible</strong>
        </div>

        <p>Estás a punto de mover <strong>TODOS los clientes</strong> de la cuenta <strong id="confirm_move_all_mesa_cuenta"></strong> a la Mesa de Trabajo.</p>

        <div class="alert alert-warning">
            <i class="fas fa-info-circle me-2"></i>
            <ul class="mb-0">
                <li>Se moverán todos los usuarios sin excepción</li>
                <li>La cuenta quedará completamente libre</li>
                <li>Esta acción NO se puede deshacer</li>
            </ul>
        </div>

        <form id="confirm_move_all_mesa_form" method="POST" action="{{ route('cuentas.moverClientes') }}">
            @csrf
            <input type="hidden" name="cuenta_origen" id="confirm_move_all_mesa_id">
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="$dispatch('close-modal', 'confirm-move-all-mesa')">
            <i class="fas fa-times me-1"></i>
            Cancelar
        </button>
        <button type="submit" form="confirm_move_all_mesa_form" class="btn btn-danger">
            <i class="fas fa-users me-1"></i>
            Sí, Mover Todos a Mesa
        </button>
    </div>
</x-modal>
