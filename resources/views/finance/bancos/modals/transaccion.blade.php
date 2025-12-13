<x-modal name="registrar-transaccion" maxWidth="md">
    <div class="modal-header">
        <h5 class="modal-title">Registrar Transacción</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="transaccionForm" method="POST" action="">
        @csrf
        <input type="hidden" id="banco_id" name="banco_id">

        <div class="modal-body">
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo de Transacción <span class="text-danger">*</span></label>
                <select class="form-select" id="tipo" name="tipo" required>
                    <option value="">Seleccione...</option>
                    <option value="ingreso">Ingreso</option>
                    <option value="egreso">Egreso</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="monto_transaccion" class="form-label">Monto <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" id="monto_transaccion" name="monto_transaccion" required>
            </div>

            <div class="mb-3">
                <label for="referencia" class="form-label">Referencia</label>
                <input type="text" class="form-control" id="referencia" name="referencia" placeholder="Ej: Venta #123, Pago proveedor">
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">
                Cancelar
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check"></i> Registrar
            </button>
        </div>
    </form>
</x-modal>

<script>
    // Actualizar action del formulario cuando se abre el modal
    document.getElementById('transaccionForm').addEventListener('submit', function(e) {
        const bancoId = document.getElementById('banco_id').value;
        const routeTemplate = "{{ route('bancos.transacciones.store', ['banco_id' => ':ID:']) }}";
        this.action = routeTemplate.replace(':ID:', bancoId);
    });
</script>
