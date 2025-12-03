<x-modal name="editar-mail">
    <div class="modal-header">
        <h5 class="modal-title">Editar Buzón</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="editMailForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="form-group mb-3">
                <label for="edit_email">Email</label>
                <input type="email" name="email" id="edit_email" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="edit_password">Contraseña</label>
                <input type="text" name="password" id="edit_password" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="edit_host">Host</label>
                <input type="text" name="host" id="edit_host" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="edit_description">Descripción</label>
                <input type="text" name="description" id="edit_description" class="form-control">
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-success">Guardar</button>
        </div>
    </form>
</x-modal>
