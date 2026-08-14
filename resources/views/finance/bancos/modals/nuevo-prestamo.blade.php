<x-modal name="nuevo-prestamo" maxWidth="md">
    <x-slot name="title">
        <i class="fas fa-hand-holding-dollar"></i> Nuevo Préstamo
    </x-slot>

    <form id="nuevoPrestamoForm" method="POST" action="{{ route('prestamos.store') }}">
        @csrf
        <div class="modal-body">

            <div class="mb-2 form-check">
                <input type="checkbox" class="form-check-input" id="prestamo_deudor_nuevo_toggle">
                <label class="form-check-label" for="prestamo_deudor_nuevo_toggle">Es un deudor nuevo</label>
            </div>

            <div class="mb-3" id="prestamo_deudor_existente_wrap">
                <label for="prestamo_deudor_id" class="form-label">Deudor <span class="text-danger">*</span></label>
                <select class="form-select searchable-select" id="prestamo_deudor_id" name="deudor_id"
                        data-placeholder="Seleccione un deudor...">
                    <option value="">-- Selecciona un deudor --</option>
                    @foreach ($deudores as $deudor)
                        <option value="{{ $deudor->id }}">{{ $deudor->nombre }}{{ $deudor->telefono ? ' — '.$deudor->telefono : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="row d-none" id="prestamo_deudor_nuevo_wrap">
                <div class="col-md-7 mb-3">
                    <label for="prestamo_deudor_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="prestamo_deudor_nombre" name="deudor_nombre" placeholder="Nombre completo">
                </div>
                <div class="col-md-5 mb-3">
                    <label for="prestamo_deudor_telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="prestamo_deudor_telefono" name="deudor_telefono" placeholder="09...">
                </div>
            </div>

            <div class="mb-3">
                <label for="prestamo_monto" class="form-label">Monto a prestar <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0.01" class="form-control" id="prestamo_monto" name="monto" required>
            </div>

            <div class="mb-3">
                <label class="form-label d-block">Origen del dinero <span class="text-danger">*</span></label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="origen_tipo" id="prestamo_origen_banco" value="banco" checked>
                    <label class="btn btn-outline-primary btn-sm" for="prestamo_origen_banco"><i class="fas fa-university me-1"></i>Banco</label>

                    <input type="radio" class="btn-check" name="origen_tipo" id="prestamo_origen_fondo" value="fondo">
                    <label class="btn btn-outline-primary btn-sm" for="prestamo_origen_fondo"><i class="fas fa-wallet me-1"></i>Efectivo</label>
                </div>
            </div>

            <div class="mb-3" id="prestamo_banco_wrap">
                <select class="form-select" id="prestamo_banco_id" name="banco_id">
                    <option value="">Seleccione el banco</option>
                    @foreach ($bancos as $banco)
                        <option value="{{ $banco->idban }}">{{ $banco->nombreban }} — ${{ number_format($banco->monto, 2) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3 d-none" id="prestamo_fondo_wrap">
                <select class="form-select" id="prestamo_fondo_id" name="fondo_id">
                    <option value="">Seleccione el fondo</option>
                    @foreach ($fondos as $fondo)
                        <option value="{{ $fondo->id }}">{{ $fondo->nombre }} — ${{ number_format($fondo->saldo, 2) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-1">
                <label for="prestamo_motivo" class="form-label">Motivo</label>
                <input type="text" class="form-control" id="prestamo_motivo" name="motivo" placeholder="Opcional">
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'nuevo-prestamo' }))">
                Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Otorgar Préstamo
            </button>
        </div>
    </form>
</x-modal>

<script>
    (function () {
        const toggle = document.getElementById('prestamo_deudor_nuevo_toggle');
        const existenteWrap = document.getElementById('prestamo_deudor_existente_wrap');
        const nuevoWrap = document.getElementById('prestamo_deudor_nuevo_wrap');
        toggle.addEventListener('change', function () {
            existenteWrap.classList.toggle('d-none', this.checked);
            nuevoWrap.classList.toggle('d-none', !this.checked);
            document.getElementById('prestamo_deudor_id').value = '';
            if (!this.checked) {
                document.getElementById('prestamo_deudor_nombre').value = '';
                document.getElementById('prestamo_deudor_telefono').value = '';
            }
        });

        const bancoRadio = document.getElementById('prestamo_origen_banco');
        const fondoRadio = document.getElementById('prestamo_origen_fondo');
        const bancoWrap = document.getElementById('prestamo_banco_wrap');
        const fondoWrap = document.getElementById('prestamo_fondo_wrap');
        function syncOrigen() {
            bancoWrap.classList.toggle('d-none', !bancoRadio.checked);
            fondoWrap.classList.toggle('d-none', !fondoRadio.checked);
            if (bancoRadio.checked) { document.getElementById('prestamo_fondo_id').value = ''; }
            else { document.getElementById('prestamo_banco_id').value = ''; }
        }
        bancoRadio.addEventListener('change', syncOrigen);
        fondoRadio.addEventListener('change', syncOrigen);
    })();

    function openNuevoPrestamoModal() {
        document.getElementById('nuevoPrestamoForm').reset();
        document.getElementById('prestamo_deudor_existente_wrap').classList.remove('d-none');
        document.getElementById('prestamo_deudor_nuevo_wrap').classList.add('d-none');
        document.getElementById('prestamo_banco_wrap').classList.remove('d-none');
        document.getElementById('prestamo_fondo_wrap').classList.add('d-none');
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'nuevo-prestamo' }));
    }
</script>
