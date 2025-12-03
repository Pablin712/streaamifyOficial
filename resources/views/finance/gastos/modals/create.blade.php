<x-modal name="crear-gasto">
    <div class="modal-header">
        <h5 class="modal-title">Seleccionar Tipo de Gasto</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="createGastoForm" method="POST" action="{{ route('gastos.store') }}">
        @csrf
        <div class="modal-body">
            <!-- Selector de Tipo de Gasto -->
            <div class="form-group mb-3">
                <label for="idtip">Seleccionar Tipo de Gasto</label>
                <select id="idtip" name="idtip" class="form-control searchable-select" required
                        data-placeholder="Seleccione un tipo de gasto...">
                    <option value="">-- Selecciona un Tipo de Gasto --</option>
                    @foreach($tipoGastos as $tipo)
                        <option value="{{ $tipo->idtip }}">{{ $tipo->detalletip }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Campos del Gasto -->
            <div class="form-group mb-3">
                <label for="descripciongas">Descripción</label>
                <input type="text" name="descripciongas" id="descripciongas" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="montogas">Monto</label>
                <input type="number" name="montogas" id="montogas" class="form-control" step="0.01" required>
            </div>
            <div class="form-group mb-3">
                <label for="fechagas">Fecha</label>
                <input type="date" name="fechagas" id="fechagas" class="form-control" value="{{ now()->format('Y-m-d') }}">
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</x-modal>
