<x-modal name="confirm-move-all-disperso" :show="false" maxWidth="md">
    <div class="modal-header" style="background-color: #ffc107; color: #000000;">
        <h5 class="modal-title">
            <i class="fas fa-random me-2"></i>
            Dispersar TODOS los Clientes
        </h5>
        <button type="button" class="btn-close" @click="$dispatch('close-modal', 'confirm-move-all-disperso')"></button>
    </div>
    <div class="modal-body">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>¡ATENCIÓN! Acción masiva irreversible</strong>
        </div>

        <p>Estás a punto de dispersar <strong>TODOS los clientes</strong> de la cuenta <strong id="confirm_move_all_disperso_cuenta"></strong> a otras cuentas disponibles.</p>

        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <ul class="mb-0">
                <li>El sistema buscará automáticamente cuentas con espacio disponible</li>
                <li>Los usuarios se distribuirán en diferentes cuentas del mismo servicio</li>
                <li>Esta acción NO se puede deshacer</li>
                <li>Si no hay suficientes cuentas, algunos usuarios podrían no moverse</li>
            </ul>
        </div>

        <form id="confirm_move_all_disperso_form" method="POST" action="{{ route('cuentas.moverClientesDisperso') }}">
            @csrf
            <input type="hidden" name="cuenta_origen" id="confirm_move_all_disperso_id">
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="$dispatch('close-modal', 'confirm-move-all-disperso')">
            <i class="fas fa-times me-1"></i>
            Cancelar
        </button>
        <button type="submit" form="confirm_move_all_disperso_form" class="btn btn-warning">
            <i class="fas fa-random me-1"></i>
            Sí, Dispersar Todos
        </button>
    </div>
</x-modal>
