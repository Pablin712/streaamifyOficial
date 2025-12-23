<x-modal name="transferir" maxWidth="md">
    <x-slot name="title">
        <i class="fas fa-university"></i> Transferir Fondos
    </x-slot>

    <form id="transferirFondosForm" method="POST" action="{{ route('bancos.transferir') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">

            <div class="mb-3">
                <label for="crear_tipoban_origen" class="form-label">Banco de origen <span class="text-danger">*</span></label>
                <select class="form-select" id="crear_tipoban_origen" name="banco_origen_id" required>
                    <option value="">Seleccione el banco de origen</option>
                    @foreach ($bancos as $banco)
                        <option value="{{ $banco->idban }}">{{ $banco->nombreban }} - {{ $banco->numeroban }}: ${{ number_format($banco->monto, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="crear_tipoban_destino" class="form-label">Banco de destino <span class="text-danger">*</span></label>
                <select class="form-select" id="crear_tipoban_destino" name="banco_destino_id" required>
                    <option value="">Seleccione el banco de destino</option>
                    @foreach ($bancos as $banco)
                        <option value="{{ $banco->idban }}">{{ $banco->nombreban }} - {{ $banco->numeroban }}: ${{ number_format($banco->monto, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="crear_monto_transferir" class="form-label">Monto a transferir <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" id="crear_monto_transferir" name="monto_transferir" required>
            </div>

            <div class="mb-3">
                <label for="crear_tipoban_destino" class="form-label">Banco de donde se cobra el fees<span class="text-danger">*</span></label>
                <select class="form-select" id="fees_bank" name="fees_bank" required>
                    <option value="">Seleccione el banco a cobrar del fees</option>
                    <option value="origen">Banco de origen</option>
                    <option value="destino" selected>Banco de destino</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="crear_fees_transferir" class="form-label">Fees / Costo de transacción (Opcional)</label>
                <input type="number" step="0.01" min="0" class="form-control" id="crear_fees_transferir" name="fees" placeholder="0.00" value="0">
                <small class="text-muted">Este monto será descontado del banco de origen y registrado como gasto.</small>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Nota:</strong> Asegúrese de que el banco de origen tenga fondos suficientes para cubrir el monto a transferir más los fees.
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'transferir' }))">
                Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Transferir Fondos
            </button>
        </div>
    </form>
</x-modal>
