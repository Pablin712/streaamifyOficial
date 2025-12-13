<x-modal name="crear-costo">
    <div class="modal-header">
        <h5 class="modal-title">Crear Costo</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="createCostoForm" method="POST" action="{{ route('costos.store') }}">
        @csrf
        <div class="modal-body">
            <!-- Selector de Cuentas -->
            <div class="form-group mb-3">
                <label for="idcue">Cuenta</label>
                <select id="idcue" name="idcue" class="form-control searchable-select" required
                        data-placeholder="Seleccione una cuenta...">
                    <option value="">-- Selecciona una Cuenta --</option>
                    @foreach($cuentas as $cuenta)
                        <option value="{{ $cuenta->idcue }}">{{ $cuenta->usuariocue }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Campos del Costo -->
            <div class="form-group mb-3">
                <label for="descripcioncos">Descripción</label>
                <input type="text" name="descripcioncos" id="descripcioncos" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="montocos">Monto</label>
                <input type="number" name="montocos" id="montocos" class="form-control" step="0.01" required>
            </div>
            <div class="form-group mb-3">
                <label for="fechacos">Fecha</label>
                <input type="date" name="fechacos" id="fechacos" class="form-control" value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="form-group mb-3">
                <label for="banco_id">Banco <span class="text-danger">*</span></label>
                <select id="banco_id" name="banco_id" class="form-control searchable-select"
                        data-placeholder="Seleccione un banco...">
                    <option value="">-- Selecciona un Banco --</option>
                    @foreach($bancos as $banco)
                        <option value="{{ $banco->idban }}">{{ $banco->nombreban }} ({{ ucfirst($banco->tipoban) }}) - ${{ number_format($banco->monto, 2) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Checkbox para indicar si se pagó -->
            <div class="form-check mb-3 text-start">
                <input style="width: 20px; height: 20px;" type="checkbox" id="se_pago" name="se_pago" value="1" checked>
                <label class="form-check-label" for="se_pago">
                    ¿Se pagó? <small class="text-muted">(Si no se marca, se creará una deuda pendiente)</small>
                </label>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</x-modal>
