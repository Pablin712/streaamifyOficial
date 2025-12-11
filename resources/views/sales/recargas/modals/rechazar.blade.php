<x-modal name="confirmar-rechazar-recarga">
    <div class="modal-header">
        <h5 class="modal-title">Confirmar Rechazo</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="rechazarRecargaForm" method="POST">
        @csrf
        <input type="hidden" name="idestado" value="2">
        <div class="modal-body">
            <p>¿Estás seguro de que quieres rechazar esta recarga?</p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-danger">Rechazar</button>
        </div>
    </form>
</x-modal>
