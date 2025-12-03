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
                <label for="idcue">Seleccionar Cuenta</label>
                <select id="idcue" name="idcue" class="form-control" required>
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
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</x-modal>
