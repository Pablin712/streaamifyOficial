<x-modal name="cambiar-usuario" maxWidth="2xl">
    <div class="modal-header">
        <h5 class="modal-title" id="changeUsuarioModalTitle">Actualizar Usuario</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="changeUsuarioForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body" style="padding-bottom: 200px;">
            <!-- Nombre del Cliente (readonly) -->
            <div class="form-group mb-3">
                <label for="change_nombrecli">Nombre del Cliente</label>
                <input type="text" id="change_nombrecli" class="form-control" readonly>
            </div>

            <!-- ID de Venta (readonly) -->
            <div class="form-group mb-3">
                <label for="change_idven">ID de Venta</label>
                <input type="text" id="change_idven" class="form-control" readonly>
            </div>

            <!-- Selector de Cuenta -->
            <div class="form-group mb-4">
                <label for="change_idcue">Cuenta</label>
                <select name="idcue" id="change_idcue" class="form-control searchable-select" required
                        data-placeholder="Seleccione una cuenta...">
                    <option value="">-- Selecciona una Cuenta --</option>
                    @foreach ($cuentas as $cuenta)
                        <option value="{{ $cuenta->idcue }}">{{ $cuenta->usuariocue }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Número de Perfil -->
            <div class="form-group mb-3">
                <label for="change_perfil">Número de Perfil</label>
                <input type="number" name="perfil" id="change_perfil" class="form-control" min="1" max="7" required>
            </div>

            <!-- Fecha de Vencimiento -->
            <div class="form-group mb-3">
                <label for="change_fecha_vencimiento">Fecha de Vencimiento</label>
                <input type="date" name="fecha_vencimiento" id="change_fecha_vencimiento" class="form-control" required>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-primary">Cambiar Usuario</button>
        </div>
    </form>
</x-modal>
