<x-modal name="mover-usuario">
    <div class="modal-header">
        <h5 class="modal-title">Mover Usuario</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="moverUsuarioForm" method="POST">
        @csrf
        <div class="modal-body">
            <p>¿Deseas mover este usuario a otra cuenta?</p>
            <p class="text-muted small">Esta acción moverá el usuario de forma automática.</p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-primary">Mover</button>
        </div>
    </form>
</x-modal>
