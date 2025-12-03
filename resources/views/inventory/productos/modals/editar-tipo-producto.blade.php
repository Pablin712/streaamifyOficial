<x-modal name="editar-tipo-producto">
    <div class="modal-header">
        <h5 class="modal-title">Editar Tipo de Producto</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="editTipoProductoForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="form-group mb-3">
                <label for="edit_tipo_nombre">Nombre</label>
                <input type="text" name="nombre" id="edit_tipo_nombre" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="edit_tipo_descripcion">Descripción</label>
                <textarea name="descripcion" id="edit_tipo_descripcion" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</x-modal>
