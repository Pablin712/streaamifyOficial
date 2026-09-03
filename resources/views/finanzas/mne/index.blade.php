@extends('layouts.navigation')

@section('title', 'Mi Negocio Efectivo')

@section('styles')
<style>
/* Los colores de las tarjetas KPI viven una sola vez en streamify-ui.css,
   derivados de tokens: siguen al tema y se invierten solos en oscuro.
   Aqui habia dos paletas fijas duplicadas (clara y oscura) que los dejaban
   fuera del tema. */
.kpi-card {
    border: none; border-radius: 14px; padding: 20px 22px;
    position: relative; overflow: hidden;
    transition: transform .2s, box-shadow .2s;
}
.kpi-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.12)!important; }
.kpi-card .kpi-icon {
    width:52px; height:52px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.4rem; flex-shrink:0;
}
.kpi-card .kpi-label { font-size:.7rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; opacity:.75; margin-bottom:4px; }
.kpi-card .kpi-value { font-size:1.65rem; font-weight:800; line-height:1.1; }
.kpi-card .kpi-sub   { font-size:.75rem; opacity:.6; margin-top:2px; }
.kpi-card::after {
    content:''; position:absolute; bottom:-16px; right:-16px;
    width:80px; height:80px; border-radius:50%; opacity:.07;
}

.fin-card { border:none; border-radius:14px; box-shadow:var(--shadow-md); background:var(--bg-card); }
.fin-card .fin-card-header {
    background:var(--bg-light);
    border-bottom:1px solid var(--border-color); border-radius:14px 14px 0 0;
    padding:16px 20px; font-weight:700; font-size:.88rem; color:var(--text-primary);
}
.ganancia-preview {
    border-radius:10px; padding:12px 16px; font-weight:700; font-size:1.1rem;
    background:var(--sf-good-soft); color:var(--sf-good); text-align:center;
}
.ganancia-preview.negativa { background:var(--sf-critical-soft); color:var(--sf-critical); }
</style>
@endsection

@section('main')
<div class="container-fluid px-4">

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert-flash');
            alerts.forEach(a => setTimeout(() => { const b = new bootstrap.Alert(a); b.close(); }, 5000));
            @if (session('success') || session('error'))
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'nueva-recarga' }));
            @endif
        });
    </script>
    @endpush

    <div class="d-flex justify-content-between align-items-center mt-4 mb-1 flex-wrap gap-2">
        <div>
            <h1 class="mb-0 fw-bold" style="font-size:1.6rem;">
                <i class="fas fa-sim-card text-primary me-2"></i> Mi Negocio Efectivo
            </h1>
            <ol class="breadcrumb mb-0 mt-1">
                <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('bancos.index') }}">Finanzas</a></li>
                <li class="breadcrumb-item active">Mi Negocio Efectivo</li>
            </ol>
        </div>
        @if(Auth::user()->hasPermissionTo('mne.store'))
        <button class="btn btn-primary btn-sm" onclick="openNuevaRecargaModal()">
            <i class="fas fa-plus me-1"></i> Registrar Recarga
        </button>
        @endif
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show alert-flash mt-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show alert-flash mt-3" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ── KPI Row ─────────────────────────────────────── --}}
    <div class="row g-3 mt-2 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card kpi-indigo shadow-sm d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fas fa-wallet"></i></div>
                <div>
                    <div class="kpi-label">Saldo del fondo</div>
                    <div class="kpi-value">${{ number_format($fondoMne->saldo, 2) }}</div>
                    <div class="kpi-sub">Dinero no disponible — dentro de la app</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card kpi-green shadow-sm d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fas fa-sack-dollar"></i></div>
                <div>
                    <div class="kpi-label">Ganancia hoy</div>
                    <div class="kpi-value">${{ number_format($gananciaHoy, 2) }}</div>
                    <div class="kpi-sub">{{ $recargasHoy }} recarga{{ $recargasHoy !== 1 ? 's' : '' }} hoy</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card kpi-teal shadow-sm d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fas fa-calendar-week"></i></div>
                <div>
                    <div class="kpi-label">Ganancia semana</div>
                    <div class="kpi-value">${{ number_format($gananciaSemana, 2) }}</div>
                    <div class="kpi-sub">Desde el lunes</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card kpi-amber shadow-sm d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fas fa-calendar-days"></i></div>
                <div>
                    <div class="kpi-label">Ganancia mes</div>
                    <div class="kpi-value">${{ number_format($gananciaMes, 2) }}</div>
                    <div class="kpi-sub">{{ now()->translatedFormat('F Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Historial ───────────────────────────────────── --}}
    <div class="fin-card card mb-4">
        <div class="fin-card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-clock-rotate-left text-primary me-2"></i> Historial de Recargas</span>
            <span class="badge bg-primary rounded-pill">{{ $recargas->count() }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead style="background:var(--bg-light);">
                        <tr>
                            <th>#</th>
                            <th>Operadora</th>
                            <th>Cliente</th>
                            <th>Cobrado</th>
                            <th>Costo fondo</th>
                            <th>Ganancia</th>
                            <th>Cobro</th>
                            <th>Fecha</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recargas as $recarga)
                        <tr class="{{ $recarga->anulada ? 'text-decoration-line-through text-muted' : '' }}">
                            <td class="fw-semibold text-muted">{{ $recarga->id }}</td>
                            <td>{{ $recarga->operadora }}</td>
                            <td>{{ $recarga->cliente_nombre ?? '—' }}</td>
                            <td>${{ number_format($recarga->valor_cobrado, 2) }}</td>
                            <td>${{ number_format($recarga->costo_fondo, 4) }}</td>
                            <td class="text-success fw-bold">${{ number_format($recarga->ganancia, 4) }}</td>
                            <td>
                                @if($recarga->banco_id)
                                    <span class="badge bg-light text-dark border"><i class="fas fa-university me-1"></i>{{ $recarga->banco->nombreban ?? '—' }}</span>
                                @elseif($recarga->fondo_cobro_id)
                                    <span class="badge bg-light text-dark border"><i class="fas fa-money-bill-wave me-1"></i>{{ $recarga->fondoCobro->nombre ?? 'Efectivo' }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ \Carbon\Carbon::parse($recarga->fecha)->format('d/m/Y H:i') }}</td>
                            <td>
                                @if(!$recarga->anulada && Auth::user()->hasPermissionTo('mne.store'))
                                <form method="POST" action="{{ route('mne.recargas.anular', $recarga->id) }}" onsubmit="return confirm('¿Anular esta recarga? Se revierten los saldos.');">
                                    @csrf @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Anular">
                                        <i class="fas fa-rotate-left"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center py-4 text-muted">
                            <i class="fas fa-sim-card fa-2x mb-2 opacity-25 d-block"></i> Aún no hay recargas registradas
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal: nueva recarga ──────────────────────────── --}}
<x-modal name="nueva-recarga" maxWidth="md">
    <x-slot name="title">
        <i class="fas fa-sim-card"></i> Registrar Recarga
    </x-slot>

    <form id="nuevaRecargaForm" method="POST" action="{{ route('mne.recargas.store') }}">
        @csrf
        <div class="modal-body">
            <div class="row">
                <div class="col-md-7 mb-3">
                    <label for="mne_operadora" class="form-label">Operadora <span class="text-danger">*</span></label>
                    <input type="text" list="mne_operadoras_list" class="form-control" id="mne_operadora" name="operadora" required placeholder="Ej: Tuenti, Claro, Movistar">
                    <datalist id="mne_operadoras_list">
                        <option value="Tuenti"><option value="Claro"><option value="Movistar">
                    </datalist>
                </div>
                <div class="col-md-5 mb-3">
                    <input type="hidden" name="fondo_id" value="{{ $fondoMne->id }}">
                    <label class="form-label">Fondo consumido</label>
                    <input type="text" class="form-control" value="{{ $fondoMne->nombre }}" disabled>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="mne_cliente_nombre" class="form-label">Cliente</label>
                    <input type="text" class="form-control" id="mne_cliente_nombre" name="cliente_nombre" placeholder="Opcional">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="mne_cliente_telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="mne_cliente_telefono" name="cliente_telefono" placeholder="Opcional">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="mne_valor_cobrado" class="form-label">Valor cobrado al cliente <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" class="form-control" id="mne_valor_cobrado" name="valor_cobrado" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="mne_costo_fondo" class="form-label">Costo real (consume el fondo) <span class="text-danger">*</span></label>
                    <input type="number" step="0.0001" min="0.0001" class="form-control" id="mne_costo_fondo" name="costo_fondo" required>
                </div>
            </div>

            <div class="ganancia-preview mb-3" id="mne_ganancia_preview">Ganancia: $0.00</div>

            <div class="mb-3">
                <label class="form-label d-block">¿Dónde entró el pago del cliente?</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="cobro_tipo" id="mne_cobro_ninguno" value="" checked>
                    <label class="btn btn-outline-secondary btn-sm" for="mne_cobro_ninguno">Sin registrar</label>

                    <input type="radio" class="btn-check" name="cobro_tipo" id="mne_cobro_banco" value="banco">
                    <label class="btn btn-outline-primary btn-sm" for="mne_cobro_banco"><i class="fas fa-university me-1"></i>Banco</label>

                    <input type="radio" class="btn-check" name="cobro_tipo" id="mne_cobro_fondo" value="fondo">
                    <label class="btn btn-outline-primary btn-sm" for="mne_cobro_fondo"><i class="fas fa-money-bill-wave me-1"></i>Efectivo</label>
                </div>
            </div>

            <div class="mb-3 d-none" id="mne_cobro_banco_wrap">
                <select class="form-select" id="mne_cobro_banco_id" name="cobro_banco_id">
                    <option value="">Seleccione el banco</option>
                    @foreach ($bancos as $banco)
                        <option value="{{ $banco->idban }}">{{ $banco->nombreban }} — ${{ number_format($banco->monto, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3 d-none" id="mne_cobro_fondo_wrap">
                <select class="form-select" id="mne_cobro_fondo_id" name="cobro_fondo_id">
                    <option value="">Seleccione el fondo</option>
                    @if($fondoEfectivo)
                        <option value="{{ $fondoEfectivo->id }}">{{ $fondoEfectivo->nombre }} — ${{ number_format($fondoEfectivo->saldo, 2) }}</option>
                    @endif
                </select>
            </div>

            <div class="mb-1">
                <label for="mne_notas" class="form-label">Notas</label>
                <input type="text" class="form-control" id="mne_notas" name="notas" placeholder="Opcional">
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'nueva-recarga' }))">
                Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Registrar
            </button>
        </div>
    </form>
</x-modal>

<script>
    (function () {
        const valor = document.getElementById('mne_valor_cobrado');
        const costo = document.getElementById('mne_costo_fondo');
        const preview = document.getElementById('mne_ganancia_preview');

        function updateGanancia() {
            const v = parseFloat(valor.value) || 0;
            const c = parseFloat(costo.value) || 0;
            const g = v - c;
            preview.textContent = 'Ganancia: $' + g.toFixed(4);
            preview.classList.toggle('negativa', g < 0);
        }
        valor.addEventListener('input', updateGanancia);
        costo.addEventListener('input', updateGanancia);

        const radios = document.querySelectorAll('input[name="cobro_tipo"]');
        const bancoWrap = document.getElementById('mne_cobro_banco_wrap');
        const fondoWrap = document.getElementById('mne_cobro_fondo_wrap');
        radios.forEach(r => r.addEventListener('change', function () {
            bancoWrap.classList.toggle('d-none', this.value !== 'banco');
            fondoWrap.classList.toggle('d-none', this.value !== 'fondo');
            if (this.value !== 'banco') document.getElementById('mne_cobro_banco_id').value = '';
            if (this.value !== 'fondo') document.getElementById('mne_cobro_fondo_id').value = '';
        }));
    })();

    function openNuevaRecargaModal() {
        document.getElementById('nuevaRecargaForm').reset();
        document.getElementById('mne_ganancia_preview').textContent = 'Ganancia: $0.00';
        document.getElementById('mne_ganancia_preview').classList.remove('negativa');
        document.getElementById('mne_cobro_banco_wrap').classList.add('d-none');
        document.getElementById('mne_cobro_fondo_wrap').classList.add('d-none');
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'nueva-recarga' }));
    }
</script>
@endsection
