<x-modal name="eliminar-mail">
    <div class="modal-header">
        <h5 class="modal-title">Eliminar Buzón</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="deleteMailForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
            <p>¿Seguro que deseas eliminar este buzón?</p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-danger">Eliminar</button>
        </div>
    </form>
</x-modal>
