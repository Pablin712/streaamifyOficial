<x-modal name="registrar-fondo-transaccion" maxWidth="md">
    <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-wallet me-2 text-primary"></i>Ajustar Fondo</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="fondoTransaccionForm" method="POST" action="">
        @csrf
        <div class="modal-body">
            <div class="alert alert-secondary py-2 px-3 mb-3">
                Fondo: <strong id="fondo_transaccion_nombre">—</strong>
            </div>

            <div class="mb-3">
                <label for="fondo_tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                <select class="form-select" id="fondo_tipo" name="tipo" required>
                    <option value="">Seleccione...</option>
                    <option value="ingreso">Ingreso (entra dinero al fondo)</option>
                    <option value="egreso">Egreso (sale dinero del fondo)</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="fondo_monto_transaccion" class="form-label">Monto <span class="text-danger">*</span></label>
                <input type="number" step="0.0001" min="0.0001" class="form-control" id="fondo_monto_transaccion" name="monto_transaccion" required>
            </div>

            <div class="mb-3">
                <label for="fondo_referencia" class="form-label">Referencia</label>
                <input type="text" class="form-control" id="fondo_referencia" name="referencia" placeholder="Ej: Ajuste de caja, conteo físico">
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check"></i> Registrar
            </button>
        </div>
    </form>
</x-modal>

<script>
    document.getElementById('fondoTransaccionForm').addEventListener('submit', function (e) {
        const fondoId = this.dataset.fondoId;
        const routeTemplate = "{{ route('fondos.transacciones.store', ['fondo_id' => ':ID:']) }}";
        this.action = routeTemplate.replace(':ID:', fondoId);
    });

    function openFondoTransaccionModal(fondoId, fondoNombre) {
        const form = document.getElementById('fondoTransaccionForm');
        form.dataset.fondoId = fondoId;
        document.getElementById('fondo_transaccion_nombre').textContent = fondoNombre;
        document.getElementById('fondo_tipo').value = '';
        document.getElementById('fondo_monto_transaccion').value = '';
        document.getElementById('fondo_referencia').value = '';
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'registrar-fondo-transaccion' }));
    }
</script>
