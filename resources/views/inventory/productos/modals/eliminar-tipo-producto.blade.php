<x-modal name="eliminar-tipo-producto">
    <div class="modal-header">
        <h5 class="modal-title">Confirmar Eliminación</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="deleteTipoProductoForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
            <p>¿Estás seguro de que deseas eliminar este tipo de producto?</p>
            <p class="text-muted small">Esta acción no se puede deshacer.</p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-danger">Eliminar</button>
        </div>
    </form>
</x-modal>
