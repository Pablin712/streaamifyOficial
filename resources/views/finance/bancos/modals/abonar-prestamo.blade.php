<x-modal name="abonar-prestamo" maxWidth="md">
    <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-hand-holding-dollar me-2 text-success"></i>Abonar Préstamo</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="abonarPrestamoForm" method="POST" action="">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="alert alert-info">
                <strong>Deudor:</strong> <span id="abono_deudor_display"></span><br>
                <strong>Monto Total:</strong> $<span id="abono_monto_total_display"></span><br>
                <strong>Restante:</strong> $<span id="abono_monto_restante_display"></span>
            </div>

            <div class="form-group mb-3">
                <label for="abono_monto">Monto a Abonar <span class="text-danger">*</span></label>
                <input type="number" name="monto_abono" id="abono_monto" class="form-control" step="0.01" required>
                <small class="text-muted">Puede ser parcial o total. No hace falta que vuelva al mismo origen del préstamo.</small>
            </div>

            <div class="mb-3">
                <label class="form-label d-block">¿Dónde entra el pago? <span class="text-danger">*</span></label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="abono_destino_tipo" id="abono_destino_banco" value="banco" checked>
                    <label class="btn btn-outline-primary btn-sm" for="abono_destino_banco"><i class="fas fa-university me-1"></i>Banco</label>

                    <input type="radio" class="btn-check" name="abono_destino_tipo" id="abono_destino_fondo" value="fondo">
                    <label class="btn btn-outline-primary btn-sm" for="abono_destino_fondo"><i class="fas fa-wallet me-1"></i>Efectivo</label>
                </div>
            </div>

            <div class="mb-3" id="abono_banco_wrap">
                <select id="abono_banco_id" name="banco_id" class="form-select">
                    <option value="">-- Selecciona un Banco --</option>
                    @foreach($bancos as $banco)
                        <option value="{{ $banco->idban }}">{{ $banco->nombreban }} — ${{ number_format($banco->monto, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3 d-none" id="abono_fondo_wrap">
                <select id="abono_fondo_id" name="fondo_id" class="form-select">
                    <option value="">-- Selecciona un Fondo --</option>
                    @foreach($fondos as $fondo)
                        <option value="{{ $fondo->id }}">{{ $fondo->nombre }} — ${{ number_format($fondo->saldo, 2) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancelar</button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-dollar-sign"></i> Abonar
            </button>
        </div>
    </form>
</x-modal>

<script>
    (function () {
        const bancoRadio = document.getElementById('abono_destino_banco');
        const fondoRadio = document.getElementById('abono_destino_fondo');
        const bancoWrap = document.getElementById('abono_banco_wrap');
        const fondoWrap = document.getElementById('abono_fondo_wrap');
        function syncDestino() {
            bancoWrap.classList.toggle('d-none', !bancoRadio.checked);
            fondoWrap.classList.toggle('d-none', !fondoRadio.checked);
            if (bancoRadio.checked) { document.getElementById('abono_fondo_id').value = ''; }
            else { document.getElementById('abono_banco_id').value = ''; }
        }
        bancoRadio.addEventListener('change', syncDestino);
        fondoRadio.addEventListener('change', syncDestino);
    })();

    window.abonarPrestamo = function (prestamoId, deudorNombre, montoTotal, montoRestante) {
        document.getElementById('abono_deudor_display').textContent = deudorNombre;
        document.getElementById('abono_monto_total_display').textContent = parseFloat(montoTotal).toFixed(2);
        document.getElementById('abono_monto_restante_display').textContent = parseFloat(montoRestante).toFixed(2);
        document.getElementById('abono_monto').value = '';
        document.getElementById('abono_monto').max = montoRestante;
        document.getElementById('abono_destino_banco').checked = true;
        document.getElementById('abono_banco_wrap').classList.remove('d-none');
        document.getElementById('abono_fondo_wrap').classList.add('d-none');

        const form = document.getElementById('abonarPrestamoForm');
        form.action = "{{ route('prestamos.abonar', ['id' => ':ID:']) }}".replace(':ID:', prestamoId);
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'abonar-prestamo' }));
    };
</script>
