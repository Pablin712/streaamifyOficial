<x-modal name="crear-mail">
    <div class="modal-header">
        <h5 class="modal-title">Crear Buzón</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="createMailForm" method="POST" action="{{ route('mails.store') }}">
        @csrf
        <div class="modal-body">
            <div class="form-group mb-3">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="password">Contraseña</label>
                <input type="text" name="password" id="password" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="host">Host</label>
                <input type="text" name="host" id="host" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="description">Descripción</label>
                <input type="text" name="description" id="description" class="form-control">
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-primary">Crear</button>
        </div>
    </form>
</x-modal>
