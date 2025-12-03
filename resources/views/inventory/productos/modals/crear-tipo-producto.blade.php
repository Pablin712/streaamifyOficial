<x-modal name="crear-tipo-producto">
    <div class="modal-header">
        <h5 class="modal-title">Crear Tipo de Producto</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="createTipoProductoForm" method="POST" action="{{ route('tipos_producto.store') }}">
        @csrf
        <div class="modal-body">
            <div class="form-group mb-3">
                <label for="tipo_nombre">Nombre</label>
                <input type="text" name="nombre" id="tipo_nombre" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="tipo_descripcion">Descripción</label>
                <textarea name="descripcion" id="tipo_descripcion" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</x-modal>
