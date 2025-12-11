<x-modal name="confirmar-aprobar-recarga">
    <div class="modal-header">
        <h5 class="modal-title">Confirmar Aprobación</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="aprobarRecargaForm" method="POST">
        @csrf
        <input type="hidden" name="idestado" value="3">
        <div class="modal-body">
            <p>¿Estás seguro de que quieres aprobar esta recarga?</p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-success">Aprobar</button>
        </div>
    </form>
</x-modal>
