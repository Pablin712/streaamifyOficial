<x-modal name="pagar-deuda">
    <div class="modal-header">
        <h5 class="modal-title">Pagar Deuda</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="pagarDeudaForm" method="POST" action="">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="alert alert-info">
                <strong>Deuda ID:</strong> <span id="deuda_id_display"></span><br>
                <strong>Monto Total:</strong> $<span id="monto_total_display"></span><br>
                <strong>Monto Restante:</strong> $<span id="monto_restante_display"></span>
            </div>

            <div class="form-group mb-3">
                <label for="monto_abono">Monto a Pagar <span class="text-danger">*</span></label>
                <input type="number" name="monto_abono" id="monto_abono" class="form-control" step="0.01" required>
                <small class="text-muted">Ingresa el monto que deseas abonar. Puede ser parcial o total.</small>
            </div>

            <div class="form-group mb-3">
                <label for="pagar_banco_id">Banco <span class="text-danger">*</span></label>
                <select id="pagar_banco_id" name="banco_id" class="form-control searchable-select" required
                        data-placeholder="Seleccione un banco...">
                    <option value="">-- Selecciona un Banco --</option>
                    @foreach($bancos as $banco)
                        <option value="{{ $banco->idban }}">{{ $banco->nombreban }} ({{ ucfirst($banco->tipoban) }}) - ${{ number_format($banco->monto, 2) }}</option>
                    @endforeach
                </select>
            </div>

            <input type="hidden" id="deuda_id_input" name="deuda_id">
            <input type="hidden" id="monto_restante_input" name="monto_restante">
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-dollar-sign"></i> Pagar
            </button>
        </div>
    </form>
</x-modal>
