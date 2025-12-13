<x-modal name="crear-banco" maxWidth="md">
    <x-slot name="title">
        <i class="fas fa-university"></i> Crear Nuevo Banco
    </x-slot>

    <form id="crearBancoForm" method="POST" action="{{ route('bancos.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
            <div class="mb-3">
                <label for="crear_nombreban" class="form-label">Nombre del Banco <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="crear_nombreban" name="nombreban" required placeholder="Ej: Banco Pichincha">
            </div>

            <div class="mb-3">
                <label for="crear_tipoban" class="form-label">Tipo <span class="text-danger">*</span></label>
                <select class="form-select" id="crear_tipoban" name="tipoban" required>
                    <option value="">Seleccione un tipo</option>
                    <option value="Ahorros">Ahorros</option>
                    <option value="Corriente">Corriente</option>
                    <option value="Transaccional">Transaccional</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="crear_numeroban" class="form-label">Número de Cuenta <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="crear_numeroban" name="numeroban" required placeholder="1234567890">
            </div>

            <div class="mb-3">
                <label for="crear_propietarioban" class="form-label">Propietario <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="crear_propietarioban" name="propietarioban" required placeholder="Nombre del propietario">
            </div>

            <div class="mb-3">
                <label for="crear_cedulaban" class="form-label">Cédula <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="crear_cedulaban" name="cedulaban" required placeholder="0123456789">
            </div>

            <div class="mb-3">
                <label for="crear_detalleban" class="form-label">Descripción</label>
                <textarea class="form-control" id="crear_detalleban" name="detalleban" rows="3" placeholder="Descripción opcional del banco"></textarea>
            </div>

            <div class="mb-3">
                <label for="crear_foto" class="form-label">Foto</label>
                <input type="file" class="form-control" id="crear_foto" name="foto" accept="image/*">
                <small class="text-muted">Sube una imagen del banco (opcional)</small>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Nota:</strong> El banco se creará con un monto inicial de $0.00. Use transacciones para agregar fondos.
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'crear-banco' }))">
                Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Crear Banco
            </button>
        </div>
    </form>
</x-modal>
