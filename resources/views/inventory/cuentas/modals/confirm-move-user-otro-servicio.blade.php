<x-modal name="confirm-move-user-otro-servicio" :show="false" maxWidth="lg">
    <div class="modal-header modal-header-pink">
        <h5 class="modal-title">
            <i class="fas fa-exchange-alt me-2"></i>
            Mover Cliente a Otro Servicio
        </h5>
        <button type="button" class="btn-close btn-close-white" @click="$dispatch('close-modal', 'confirm-move-user-otro-servicio')"></button>
    </div>
    <div class="modal-body">
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>¡ATENCIÓN!</strong> Esta acción moverá el cliente a un servicio completamente diferente.
        </div>

        <p>Cliente: <strong id="confirm_move_otro_servicio_user_name"></strong></p>
        <p>Servicio actual: <strong id="confirm_move_otro_servicio_actual"></strong></p>

        <form id="confirm_move_otro_servicio_form" method="POST">
            @csrf
            <input type="hidden" id="confirm_move_otro_servicio_user_id" name="iddet">

            <div class="mb-3">
                <label for="idser_destino" class="form-label">
                    <i class="fas fa-tv me-2"></i>
                    Selecciona el servicio destino:
                </label>
                <select class="form-select" id="idser_destino" name="idser_destino" required>
                    <option value="">-- Selecciona un servicio --</option>
                    @foreach(['NETFLIX', 'DISNEYP', 'DISNEYS', 'MAX', 'PRIME', 'PARAMOUNT', 'CRUNCHY', 'SPOTIFY', 'MAGIS'] as $servicio)
                        <option value="{{ $servicio }}">{{ $servicio }}</option>
                    @endforeach
                </select>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                El sistema buscará automáticamente una cuenta disponible del servicio seleccionado con perfiles libres.
            </div>

            <div class="alert alert-warning">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Importante:</strong> Asegúrate de notificar al cliente sobre el cambio de servicio y enviarle las nuevas credenciales.
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="$dispatch('close-modal', 'confirm-move-user-otro-servicio')">
            <i class="fas fa-times me-1"></i>
            Cancelar
        </button>
        <button type="submit" form="confirm_move_otro_servicio_form" class="btn btn-danger">
            <i class="fas fa-exchange-alt me-1"></i>
            Sí, Mover a Otro Servicio
        </button>
    </div>
</x-modal>
