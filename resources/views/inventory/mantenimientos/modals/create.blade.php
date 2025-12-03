<x-modal name="create-mantenimiento" maxWidth="lg" :closeable="true">
    <div class="modal-header p-4">
        <h5 class="modal-title fw-bold">
            <i class="fas fa-plus-circle"></i> Crear Mantenimiento
        </h5>
        <button type="button" class="btn-close" x-on:click="$dispatch('close-modal', 'create-mantenimiento')"></button>
    </div>
    <form id="create-form" onsubmit="submitCreate(event)">
        <div class="modal-body p-4">
            @csrf

            <div class="mb-3">
                <label for="create-idcue" class="form-label required">Cuenta</label>
                <select name="idcue" id="create-idcue" class="form-control searchable-select" required
                        data-placeholder="Seleccionar cuenta...">
                    <option value="">Seleccione una cuenta</option>
                    @foreach ($cuentas as $cuenta)
                        <option value="{{ $cuenta->idcue }}">{{ $cuenta->idcue }} - {{ $cuenta->usuariocue }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="create-fechaman" class="form-label fw-semibold">Fecha de Mantenimiento</label>
                <input type="date" name="fechaman" id="create-fechaman" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="create-descripcionman" class="form-label fw-semibold">Descripción</label>
                <textarea name="descripcionman" id="create-descripcionman" class="form-control" rows="3" required></textarea>
            </div>
        </div>
        <div class="modal-footer p-4">
            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'create-mantenimiento')">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </form>
</x-modal>
