<x-modal name="agregar-detalle-modal" :show="false" maxWidth="lg">
    <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
            <i class="fas fa-plus-circle me-2"></i>Agregar Detalle a la Venta
        </h5>
        <button type="button" class="btn-close btn-close-white"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'agregar-detalle-modal' }))">
        </button>
    </div>
    <div class="modal-body">
        <form id="formDetalle">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="selectCuenta" class="form-label fw-semibold">
                        <i class="fas fa-tv text-success me-1"></i>Cuenta *
                    </label>
                    <select class="form-select searchable-select" id="selectCuenta" required
                            data-placeholder="Buscar cuenta...">
                        <option value="">Buscar cuenta...</option>
                        @foreach ($cuentas as $cuenta)
                            <option value="{{ $cuenta->idcue }}">
                                {{ $cuenta->idcue }}: Oc: {{ $cuenta->usuarios_activos }} ::
                                @foreach ($cuenta->perfiles as $perfil)
                                    P{{ $perfil->numeroper }}: {{ $perfil->usuarios_activos }}&nbsp;&nbsp;
                                @endforeach
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="selectPerfil" class="form-label fw-semibold">
                        <i class="fas fa-user-circle text-success me-1"></i>Perfil *
                    </label>
                    <input type="number" class="form-control" id="selectPerfil"
                           min="1" max="7" placeholder="Número de perfil (1-7)" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fechaVencimiento" class="form-label fw-semibold">
                        <i class="fas fa-calendar-alt text-success me-1"></i>Fecha de Vencimiento *
                    </label>
                    <input type="date" class="form-control" id="fechaVencimiento" required>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="setVentaDetalleMonthsAhead(1)">
                            <i class="fas fa-plus me-1"></i>+1 Mes
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="setVentaDetalleMonthsAhead(2)">
                            <i class="fas fa-plus me-1"></i>+2 Meses
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="setVentaDetalleMonthsAhead(3)">
                            <i class="fas fa-plus me-1"></i>+3 Meses
                        </button>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="monto" class="form-label fw-semibold">
                        <i class="fas fa-dollar-sign text-success me-1"></i>Monto *
                    </label>
                    <input type="number" class="form-control" id="monto"
                           step="0.01" min="0" placeholder="0.00" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label fw-semibold">
                    <i class="fas fa-file-alt text-success me-1"></i>Descripción *
                </label>
                <input type="text" class="form-control" id="descripcion"
                       placeholder="Descripción del servicio" required>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'agregar-detalle-modal' }))">
            <i class="fas fa-times me-1"></i> Cancelar
        </button>
        <button type="button" class="btn btn-success" id="guardarDetalleBtn">
            <i class="fas fa-check me-1"></i> Guardar Detalle
        </button>
    </div>
</x-modal>

<script>
function setVentaDetalleMonthsAhead(months) {
    const field = document.getElementById('fechaVencimiento');
    if (!field) return;

    const baseDate = new Date();
    baseDate.setMonth(baseDate.getMonth() + months);

    const year = baseDate.getFullYear();
    const month = String(baseDate.getMonth() + 1).padStart(2, '0');
    const day = String(baseDate.getDate()).padStart(2, '0');

    field.value = `${year}-${month}-${day}`;
}
</script>
