<x-modal name="confirm-acomodar-usuarios" :show="false" maxWidth="md">
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-sort-amount-down me-2"></i>
            Acomodar Usuarios a Otra Cuenta
        </h5>
        <button type="button" class="btn-close" @click="$dispatch('close-modal', 'confirm-acomodar-usuarios')"></button>
    </div>
    <div class="modal-body">
        <p>
            Se moverán los usuarios excedentes de la cuenta
            <strong id="confirm_acomodar_cuenta_nombre"></strong>
            a otras cuentas disponibles del mismo servicio.
        </p>

        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>
            <ul class="mb-0">
                <li>Se priorizan los usuarios que comparten perfil con otro usuario</li>
                <li>El sistema busca cuentas disponibles del mismo servicio automáticamente</li>
                <li>Esta acción no se puede deshacer</li>
            </ul>
        </div>

        <form id="confirm_acomodar_form" method="POST" action="">
            @csrf
            <input type="hidden" name="cuenta_id" id="confirm_acomodar_cuenta_id">
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="$dispatch('close-modal', 'confirm-acomodar-usuarios')">
            <i class="fas fa-times me-1"></i>Cancelar
        </button>
        <button type="submit" form="confirm_acomodar_form" class="btn btn-primary">
            <i class="fas fa-sort-amount-down me-1"></i>Sí, Acomodar Usuarios
        </button>
    </div>
</x-modal>
