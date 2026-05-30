<x-modal name="exportar-transacciones" maxWidth="lg">
    <div class="modal-header" style="background: linear-gradient(135deg,#1e3a8a,#3b82f6); color:white;">
        <h5 class="modal-title fw-bold">
            <i class="fas fa-file-export me-2"></i> Exportar Transacciones
        </h5>
        <button type="button" class="btn-close btn-close-white"
                onclick="window.dispatchEvent(new CustomEvent('close-modal',{detail:'exportar-transacciones'}))"></button>
    </div>
    <div class="modal-body p-4">

        <!-- Filtros -->
        <p class="text-muted small mb-3">
            <i class="fas fa-filter me-1"></i> Aplica los filtros y descarga en el formato que prefieras.
        </p>

        <div class="row g-3">
            <!-- Banco -->
            <div class="col-md-6">
                <label class="form-label fw-semibold small">
                    <i class="fas fa-university text-primary me-1"></i> Banco
                </label>
                <select id="exp_banco_id" class="form-select">
                    <option value="">Todos los bancos</option>
                    @foreach($bancos as $b)
                        <option value="{{ $b->idban }}">{{ $b->nombreban }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tipo -->
            <div class="col-md-6">
                <label class="form-label fw-semibold small">
                    <i class="fas fa-exchange-alt text-primary me-1"></i> Tipo de transacción
                </label>
                <select id="exp_tipo" class="form-select">
                    <option value="todos">Todos</option>
                    <option value="ingreso">Solo ingresos</option>
                    <option value="egreso">Solo egresos</option>
                </select>
            </div>

            <!-- Fecha inicio -->
            <div class="col-md-6">
                <label class="form-label fw-semibold small">
                    <i class="fas fa-calendar-alt text-primary me-1"></i> Fecha inicio
                </label>
                <input type="date" id="exp_fecha_inicio" class="form-control"
                       value="{{ now()->startOfMonth()->format('Y-m-d') }}">
            </div>

            <!-- Fecha fin -->
            <div class="col-md-6">
                <label class="form-label fw-semibold small">
                    <i class="fas fa-calendar-check text-primary me-1"></i> Fecha fin
                </label>
                <input type="date" id="exp_fecha_fin" class="form-control"
                       value="{{ now()->format('Y-m-d') }}">
            </div>
        </div>

        <!-- Preview badge -->
        <div class="alert alert-light border mt-3 mb-0 d-flex align-items-center gap-2">
            <i class="fas fa-info-circle text-primary"></i>
            <small>El reporte incluirá las transacciones que coincidan con los filtros seleccionados, ordenadas por fecha descendente.</small>
        </div>
    </div>

    <div class="modal-footer d-flex justify-content-between align-items-center">
        <button type="button" class="btn btn-outline-secondary btn-sm"
                onclick="window.dispatchEvent(new CustomEvent('close-modal',{detail:'exportar-transacciones'}))">
            Cancelar
        </button>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-danger" onclick="descargarExportacion('pdf')">
                <i class="fas fa-file-pdf me-1"></i> Descargar PDF
            </button>
            <button type="button" class="btn btn-success" onclick="descargarExportacion('excel')">
                <i class="fas fa-file-excel me-1"></i> Descargar Excel
            </button>
        </div>
    </div>
</x-modal>

<script>
function descargarExportacion(formato) {
    const bancoId     = document.getElementById('exp_banco_id').value;
    const tipo        = document.getElementById('exp_tipo').value;
    const fechaInicio = document.getElementById('exp_fecha_inicio').value;
    const fechaFin    = document.getElementById('exp_fecha_fin').value;

    if (fechaInicio && fechaFin && fechaInicio > fechaFin) {
        alert('La fecha inicio no puede ser mayor que la fecha fin.');
        return;
    }

    const params = new URLSearchParams();
    if (bancoId)     params.set('banco_id', bancoId);
    if (tipo)        params.set('tipo', tipo);
    if (fechaInicio) params.set('fecha_inicio', fechaInicio);
    if (fechaFin)    params.set('fecha_fin', fechaFin);

    const baseUrl = formato === 'pdf'
        ? "{{ route('bancos.transacciones.exportar-pdf') }}"
        : "{{ route('bancos.transacciones.exportar-excel') }}";

    window.open(baseUrl + '?' + params.toString(), '_blank');
}
</script>
