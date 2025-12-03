<x-modal name="actualizar-pedido">
    <div class="modal-header">
        <h5 class="modal-title" id="updatePedidoModalTitle">Actualizar Pedido</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="updatePedidoForm" method="POST">
        @csrf
        <div class="modal-body" style="padding-bottom: 200px;">
            <div class="form-group mb-3">
                <label for="respuesta" class="form-label">Respuesta:</label>
                <textarea name="respuesta" id="respuesta" class="form-control" rows="3" required></textarea>
            </div>

            <div class="form-group mb-4">
                <label for="idestado" class="form-label">Estado:</label>
                <select name="idestado" id="idestado" class="form-control" required>
                    <option value="">-- Selecciona un Estado --</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->idestado }}">{{ $estado->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-success">Guardar Cambios</button>
        </div>
    </form>
</x-modal>
