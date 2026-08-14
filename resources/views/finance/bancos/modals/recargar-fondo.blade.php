<x-modal name="recargar-fondo" maxWidth="md">
    <x-slot name="title">
        <i class="fas fa-arrow-right-arrow-left"></i> Recargar Fondo
    </x-slot>

    <form id="recargarFondoForm" method="POST" action="{{ route('fondos.recargar') }}">
        @csrf
        <div class="modal-body">
            <div class="mb-3">
                <label for="recargar_fondo_id" class="form-label">Fondo a recargar <span class="text-danger">*</span></label>
                <select class="form-select" id="recargar_fondo_id" name="fondo_id" required>
                    <option value="">Seleccione el fondo</option>
                    @foreach ($fondos as $fondo)
                        <option value="{{ $fondo->id }}">{{ $fondo->nombre }} — saldo actual: ${{ number_format($fondo->saldo, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="recargar_banco_id" class="form-label">Banco de origen <span class="text-danger">*</span></label>
                <select class="form-select" id="recargar_banco_id" name="banco_id" required>
                    <option value="">Seleccione el banco</option>
                    @foreach ($bancos as $banco)
                        <option value="{{ $banco->idban }}">{{ $banco->nombreban }} — ${{ number_format($banco->monto, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="recargar_monto" class="form-label">Monto <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0.01" class="form-control" id="recargar_monto" name="monto" required>
            </div>
            <div class="mb-3">
                <label for="recargar_referencia" class="form-label">Referencia</label>
                <input type="text" class="form-control" id="recargar_referencia" name="referencia" placeholder="Opcional">
            </div>
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i> Se registra un egreso en el banco y un ingreso en el fondo, ambos con trazabilidad.
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'recargar-fondo' }))">
                Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Recargar
            </button>
        </div>
    </form>
</x-modal>

<script>
    function openRecargarFondoModal(fondoId) {
        if (fondoId) {
            document.getElementById('recargar_fondo_id').value = fondoId;
        }
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'recargar-fondo' }));
    }
</script>
