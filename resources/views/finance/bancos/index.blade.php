@extends('layouts.navigation')

@section('title', 'Gestión Financiera')

@section('styles')
<style>
/* ── Bancos — variables locales con soporte dark mode ── */
:root {
    --kpi-green-bg1: #e3f9ee; --kpi-green-bg2: #d1f5e4;
    --kpi-green-icon: #bbf0d6; --kpi-green-text: #057a55;
    --kpi-red-bg1: #fef2f2;   --kpi-red-bg2: #fee2e2;
    --kpi-red-icon: #fecaca;   --kpi-red-text: #b91c1c;
    --kpi-teal-bg1: #f0fdfa;  --kpi-teal-bg2: #ccfbf1;
    --kpi-teal-icon: #99f6e4;  --kpi-teal-text: #0f766e;
    --kpi-indigo-bg1: #eef2ff; --kpi-indigo-bg2: #e0e7ff;
    --kpi-indigo-icon: #c7d2fe; --kpi-indigo-text: #4338ca;
    /* Bank card colors (brand identity — light only) */
    --bancos-ahorros-bg:   linear-gradient(135deg,#1e3a8a,#3b82f6);
    --bancos-corriente-bg: linear-gradient(135deg,#4c1d95,#7c3aed);
    --bancos-efectivo-bg:  linear-gradient(135deg,#065f46,#059669);
    /* Fondos — dinero NO disponible: paleta deslavada + candado, a proposito distinta de bancos */
    --fondo-card-bg: linear-gradient(135deg,#475569,#64748b);
}
[data-dark-mode="true"] {
    --kpi-green-bg1: #0d2e1a; --kpi-green-bg2: #0a2415;
    --kpi-green-icon: #164a28; --kpi-green-text: #4ade80;
    --kpi-red-bg1: #2d0e0e;   --kpi-red-bg2: #3a1010;
    --kpi-red-icon: #5a1a1a;   --kpi-red-text: #f87171;
    --kpi-teal-bg1: #0b2929;  --kpi-teal-bg2: #0d3535;
    --kpi-teal-icon: #164040;  --kpi-teal-text: #2dd4bf;
    --kpi-indigo-bg1: #1e1b4b; --kpi-indigo-bg2: #201c50;
    --kpi-indigo-icon: #312e81; --kpi-indigo-text: #a5b4fc;
    --fondo-card-bg: linear-gradient(135deg,#334155,#475569);
}

/* ── KPI Cards ──────────────────────────────────── */
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
.kpi-card .kpi-label {
    font-size:.7rem; font-weight:700; letter-spacing:.06em;
    text-transform:uppercase; opacity:.75; margin-bottom:4px;
}
.kpi-card .kpi-value { font-size:1.65rem; font-weight:800; line-height:1.1; }
.kpi-card .kpi-sub   { font-size:.75rem; opacity:.6; margin-top:2px; }
.kpi-card::after {
    content:''; position:absolute; bottom:-16px; right:-16px;
    width:80px; height:80px; border-radius:50%; opacity:.07;
}
.kpi-green  { background:linear-gradient(135deg,var(--kpi-green-bg1),var(--kpi-green-bg2)); color:var(--kpi-green-text); }
.kpi-green  .kpi-icon { background:var(--kpi-green-icon); }
.kpi-green::after  { background:var(--kpi-green-text); }
.kpi-red    { background:linear-gradient(135deg,var(--kpi-red-bg1),var(--kpi-red-bg2)); color:var(--kpi-red-text); }
.kpi-red    .kpi-icon { background:var(--kpi-red-icon); }
.kpi-red::after    { background:var(--kpi-red-text); }
.kpi-teal   { background:linear-gradient(135deg,var(--kpi-teal-bg1),var(--kpi-teal-bg2)); color:var(--kpi-teal-text); }
.kpi-teal   .kpi-icon { background:var(--kpi-teal-icon); }
.kpi-teal::after   { background:var(--kpi-teal-text); }
.kpi-indigo { background:linear-gradient(135deg,var(--kpi-indigo-bg1),var(--kpi-indigo-bg2)); color:var(--kpi-indigo-text); }
.kpi-indigo .kpi-icon { background:var(--kpi-indigo-icon); }
.kpi-indigo::after { background:var(--kpi-indigo-text); }

/* ── Bank Cards ─────────────────────────────────── */
.bank-card {
    border:none; border-radius:18px; padding:22px;
    color:#fff; position:relative; overflow:hidden;
    transition: transform .2s, box-shadow .2s;
    min-height:180px;
}
.bank-card:hover { transform:translateY(-4px); box-shadow:0 16px 40px rgba(0,0,0,.18)!important; }
.bank-card::before {
    content:''; position:absolute; top:-30px; right:-30px;
    width:110px; height:110px; border-radius:50%;
    background:rgba(255,255,255,.08);
}
.bank-card::after {
    content:''; position:absolute; bottom:-20px; left:-20px;
    width:80px; height:80px; border-radius:50%;
    background:rgba(255,255,255,.06);
}
.bank-card.type-ahorros   { background: var(--bancos-ahorros-bg); }
.bank-card.type-corriente { background: var(--bancos-corriente-bg); }
.bank-card.type-efectivo  { background: var(--bancos-efectivo-bg); }
.bank-card .bank-logo {
    width:40px; height:40px; border-radius:10px;
    background:rgba(255,255,255,.2); display:flex;
    align-items:center; justify-content:center; font-size:1.2rem;
}
.bank-card .bank-amount {
    font-size:1.8rem; font-weight:800; letter-spacing:-.02em;
    margin:10px 0 4px;
}
.bank-card .bank-name   { font-size:.85rem; font-weight:600; opacity:.9; }
.bank-card .bank-number { font-size:.72rem; opacity:.65; letter-spacing:.06em; font-family:monospace; }
.bank-card .bank-type   {
    display:inline-block; background:rgba(255,255,255,.18);
    padding:2px 10px; border-radius:20px; font-size:.68rem;
    font-weight:700; text-transform:uppercase; letter-spacing:.06em;
}
.bank-card .card-actions { position:relative; z-index:2; }

/* ── Fondo Cards (dinero NO disponible) ────────────
   Misma familia visual que bank-card, pero deslavada + candado:
   a proposito se ve "menos liquida" que un banco real. ── */
.fondo-card {
    border:none; border-radius:18px; padding:22px;
    color:#fff; position:relative; overflow:hidden;
    background: var(--fondo-card-bg);
    transition: transform .2s, box-shadow .2s;
    min-height:180px;
}
.fondo-card:hover { transform:translateY(-4px); box-shadow:0 16px 40px rgba(0,0,0,.18)!important; }
.fondo-card::before {
    content:''; position:absolute; top:-30px; right:-30px;
    width:110px; height:110px; border-radius:50%;
    background:rgba(255,255,255,.07);
}
.fondo-card .fondo-lock {
    width:40px; height:40px; border-radius:10px;
    background:rgba(255,255,255,.15); display:flex;
    align-items:center; justify-content:center; font-size:1.1rem;
}
.fondo-card .fondo-amount { font-size:1.8rem; font-weight:800; letter-spacing:-.02em; margin:10px 0 4px; }
.fondo-card .fondo-name   { font-size:.85rem; font-weight:600; opacity:.9; }
.fondo-card .fondo-desc   { font-size:.72rem; opacity:.65; }
.fondo-card .fondo-badge  {
    display:inline-block; background:rgba(255,255,255,.16);
    padding:2px 10px; border-radius:20px; font-size:.65rem;
    font-weight:700; text-transform:uppercase; letter-spacing:.06em;
}
.fondo-card .card-actions { position:relative; z-index:2; }

/* ── Tabs ───────────────────────────────────────── */
.fin-tabs .nav-link {
    border:none; border-bottom:3px solid transparent;
    color:var(--text-muted, #64748b); font-weight:600; font-size:.85rem;
    padding:10px 18px; border-radius:0;
    transition:color .15s, border-color .15s;
}
.fin-tabs .nav-link.active, .fin-tabs .nav-link:hover {
    color:var(--primary-color); border-bottom-color:var(--primary-color); background:transparent;
}
.fin-tabs { border-bottom: 2px solid var(--border-color); }

/* ── Section card ───────────────────────────────── */
.fin-card { border:none; border-radius:14px; box-shadow:var(--shadow-md); background:var(--bg-card); }
.fin-card .fin-card-header {
    background:var(--bg-light);
    border-bottom:1px solid var(--border-color); border-radius:14px 14px 0 0;
    padding:16px 20px; font-weight:700; font-size:.88rem; color:var(--text-primary);
}

/* ── Filter bar ─────────────────────────────────── */
.filter-bar { background:var(--bg-light); border:1px solid var(--border-color); border-radius:10px; padding:14px 16px; }
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
                ['crear-banco','editar-banco','registrar-transaccion','pagar-deuda','transferir','registrar-fondo-transaccion','recargar-fondo','nuevo-prestamo','abonar-prestamo'].forEach(m =>
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: m }))
                );
            @endif
        });
    </script>
    @endpush

    {{-- ── Encabezado ─────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mt-4 mb-1 flex-wrap gap-2">
        <div>
            <h1 class="mb-0 fw-bold" style="font-size:1.6rem;">
                <i class="fas fa-university text-primary me-2"></i> Gestión Financiera
            </h1>
            <ol class="breadcrumb mb-0 mt-1">
                <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Finanzas / Bancos</li>
            </ol>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if(Auth::user()->hasPermissionTo('bancos.store'))
            <button class="btn btn-primary btn-sm" onclick="openCreateBancoModal()">
                <i class="fas fa-plus me-1"></i> Nuevo Banco
            </button>
            <button class="btn btn-outline-secondary btn-sm" onclick="openTransferirFondosModal()">
                <i class="fas fa-arrows-alt-h me-1"></i> Transferir
            </button>
            @endif
            @if(Auth::user()->hasPermissionTo('fondos.transacciones.store'))
            <button class="btn btn-outline-secondary btn-sm" onclick="openRecargarFondoModal()">
                <i class="fas fa-wallet me-1"></i> Recargar Fondo
            </button>
            @endif
            @if(Auth::user()->hasPermissionTo('prestamos.store'))
            <button class="btn btn-outline-secondary btn-sm" onclick="openNuevoPrestamoModal()">
                <i class="fas fa-hand-holding-dollar me-1"></i> Nuevo Préstamo
            </button>
            @endif
            <button class="btn btn-outline-primary btn-sm" onclick="window.dispatchEvent(new CustomEvent('open-modal',{detail:'exportar-transacciones'}))">
                <i class="fas fa-file-export me-1"></i> Exportar
            </button>
        </div>
    </div>

    {{-- Flash messages --}}
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

    {{-- ── KPI Row — modelo "disponible vs no disponible", ver docs/finanzas/modeloFinanciero.md ── --}}
    <div class="row g-3 mt-2 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card kpi-green shadow-sm d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fas fa-coins"></i></div>
                <div>
                    <div class="kpi-label">Disponible</div>
                    <div class="kpi-value">${{ number_format($totalDisponible ?? 0, 2) }}</div>
                    <div class="kpi-sub">{{ $bancos->count() }} banco{{ $bancos->count() !== 1 ? 's' : '' }} — se puede usar ya</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card kpi-indigo shadow-sm d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fas fa-lock"></i></div>
                <div>
                    <div class="kpi-label">No disponible</div>
                    <div class="kpi-value">${{ number_format($totalNoDisponible ?? 0, 2) }}</div>
                    <div class="kpi-sub">Fondos ${{ number_format($totalFondos ?? 0, 2) }} · Prestado ${{ number_format($totalPrestado ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card kpi-red shadow-sm d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <div class="kpi-label">Deudas pendientes</div>
                    <div class="kpi-value">${{ number_format($totalDeudasPendientes ?? 0, 2) }}</div>
                    <div class="kpi-sub">{{ $deudas->where('estado','pendiente')->count() }} deuda{{ $deudas->where('estado','pendiente')->count() !== 1 ? 's' : '' }} por pagar</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            @php $bal = $patrimonioTotal ?? 0; @endphp
            <div class="kpi-card {{ $bal >= 0 ? 'kpi-teal' : 'kpi-red' }} shadow-sm d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fas fa-balance-scale"></i></div>
                <div>
                    <div class="kpi-label">Patrimonio total</div>
                    <div class="kpi-value">${{ number_format($bal, 2) }}</div>
                    <div class="kpi-sub">Disponible + no disponible − deudas</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tabs ────────────────────────────────────────── --}}
    <ul class="nav fin-tabs mb-4" id="financeTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="bancos-tab" data-bs-toggle="tab" data-bs-target="#bancos" type="button" role="tab">
                <i class="fas fa-university me-1"></i> Bancos
                <span class="badge bg-primary ms-1 rounded-pill" style="font-size:.65rem;">{{ $bancos->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="fondos-tab" data-bs-toggle="tab" data-bs-target="#fondos" type="button" role="tab">
                <i class="fas fa-wallet me-1"></i> Fondos
                <span class="badge bg-secondary ms-1 rounded-pill" style="font-size:.65rem;">{{ $fondos->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="deudores-tab" data-bs-toggle="tab" data-bs-target="#deudores" type="button" role="tab">
                <i class="fas fa-hand-holding-dollar me-1"></i> Deudores
                <span class="badge bg-secondary ms-1 rounded-pill" style="font-size:.65rem;">{{ $deudoresConDeuda->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="deudas-tab" data-bs-toggle="tab" data-bs-target="#deudas" type="button" role="tab">
                <i class="fas fa-file-invoice-dollar me-1"></i> Deudas
                <span class="badge bg-danger ms-1 rounded-pill" style="font-size:.65rem;">{{ $deudas->where('estado','pendiente')->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="transacciones-tab" data-bs-toggle="tab" data-bs-target="#transacciones" type="button" role="tab">
                <i class="fas fa-exchange-alt me-1"></i> Transacciones
            </button>
        </li>
    </ul>

    <div class="tab-content" id="financeTabsContent">

        {{-- ═══ TAB BANCOS ══════════════════════════════════ --}}
        <div class="tab-pane fade show active" id="bancos" role="tabpanel">
            @if($bancos->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-university fa-3x mb-3 opacity-25"></i>
                <p class="mb-0">No hay bancos registrados</p>
                @if(Auth::user()->hasPermissionTo('bancos.store'))
                <button class="btn btn-primary mt-3" onclick="openCreateBancoModal()">
                    <i class="fas fa-plus me-1"></i> Crear primer banco
                </button>
                @endif
            </div>
            @else
            <div class="row g-4">
                @foreach($bancos as $banco)
                @php
                    $typeClass = match(strtolower($banco->tipoban ?? '')) {
                        'corriente' => 'type-corriente',
                        'efectivo'  => 'type-efectivo',
                        default     => 'type-ahorros',
                    };
                    $typeIcon = match(strtolower($banco->tipoban ?? '')) {
                        'corriente' => 'fa-building-columns',
                        'efectivo'  => 'fa-money-bill-wave',
                        default     => 'fa-piggy-bank',
                    };
                    $numMasked = strlen($banco->numeroban ?? '') > 4
                        ? '•••• ' . substr($banco->numeroban, -4)
                        : ($banco->numeroban ?? '——');
                @endphp
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="bank-card {{ $typeClass }} shadow h-100">
                        {{-- Top row --}}
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="bank-logo">
                                @if($banco->foto)
                                    <img src="{{ route('bancos.foto', ['path' => $banco->foto]) }}"
                                         alt="{{ $banco->nombreban }}"
                                         style="width:36px;height:36px;object-fit:cover;border-radius:8px;">
                                @else
                                    <i class="fas {{ $typeIcon }}"></i>
                                @endif
                            </div>
                            <span class="bank-type">{{ $banco->tipoban }}</span>
                        </div>
                        {{-- Amount --}}
                        <div class="bank-amount">${{ number_format($banco->monto, 2) }}</div>
                        <div class="bank-name mb-1">{{ $banco->nombreban }}</div>
                        <div class="bank-number mb-3">{{ $numMasked }}</div>
                        {{-- Actions --}}
                        <div class="card-actions d-flex gap-2 flex-wrap">
                            @if(Auth::user()->hasPermissionTo('bancos.update'))
                            <button class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3);"
                                    onclick="editarBanco({{ $banco->idban }},'{{ addslashes($banco->nombreban) }}','{{ $banco->tipoban }}','{{ addslashes($banco->detalleban ?? '') }}')"
                                    title="Editar">
                                <i class="fas fa-edit me-1"></i> Editar
                            </button>
                            @endif
                            @if(Auth::user()->hasPermissionTo('bancos.transacciones.store'))
                            <button class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3);"
                                    onclick="openTransaccionModal({{ $banco->idban }})"
                                    title="Registrar transacción">
                                <i class="fas fa-plus-circle me-1"></i> Transacción
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ═══ TAB FONDOS (dinero NO disponible) ═══════════ --}}
        <div class="tab-pane fade" id="fondos" role="tabpanel">
            <div class="alert alert-secondary d-flex align-items-center gap-2 mb-4">
                <i class="fas fa-circle-info"></i>
                <div>Este dinero es de Pablo, pero no está en un banco: efectivo en caja o saldo dentro de una app. No cuenta como "Disponible".</div>
            </div>
            @if($fondos->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-wallet fa-3x mb-3 opacity-25"></i>
                <p class="mb-0">No hay fondos registrados</p>
            </div>
            @else
            <div class="row g-4">
                @foreach($fondos as $fondo)
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="fondo-card shadow h-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="fondo-lock"><i class="fas fa-lock"></i></div>
                            <span class="fondo-badge">No disponible</span>
                        </div>
                        <div class="fondo-amount">${{ number_format($fondo->saldo, 2) }}</div>
                        <div class="fondo-name mb-1">{{ $fondo->nombre }}</div>
                        <div class="fondo-desc mb-3">{{ $fondo->descripcion ?? '—' }}</div>
                        <div class="card-actions d-flex gap-2 flex-wrap">
                            @if(Auth::user()->hasPermissionTo('fondos.transacciones.store'))
                            <button class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3);"
                                    onclick="openFondoTransaccionModal({{ $fondo->id }}, '{{ addslashes($fondo->nombre) }}')" title="Ajustar saldo">
                                <i class="fas fa-plus-circle me-1"></i> Ajustar
                            </button>
                            <button class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3);"
                                    onclick="openRecargarFondoModal({{ $fondo->id }})" title="Recargar desde un banco">
                                <i class="fas fa-arrow-right-arrow-left me-1"></i> Recargar
                            </button>
                            @endif
                            @if($fondo->nombre === 'Mi Negocio Efectivo' && Auth::user()->hasPermissionTo('mne.index'))
                            <a href="{{ route('mne.index') }}" class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3);" title="Ver panel completo">
                                <i class="fas fa-chart-line me-1"></i> Panel
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ═══ TAB DEUDORES (prestamos personales pendientes de cobro) ═══ --}}
        <div class="tab-pane fade" id="deudores" role="tabpanel">
            <div class="fin-card card mb-4">
                <div class="fin-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-hand-holding-dollar text-primary me-2"></i> Préstamos Pendientes de Cobro</span>
                    <span class="badge bg-primary rounded-pill">{{ $deudoresConDeuda->count() }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3 align-items-end">
                        <div class="col-lg-8 col-md-7">
                            <label class="form-label fw-semibold small"><i class="fas fa-search text-primary me-1"></i> Buscar</label>
                            <input id="deudores-table-search" type="text" placeholder="Deudor, monto..." class="form-control">
                        </div>
                        <div class="col-lg-4 col-md-5">
                            <label class="form-label fw-semibold small"><i class="fas fa-list text-primary me-1"></i> Mostrar</label>
                            <select id="deudores-table-rows-per-page" class="form-select">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="20">20</option>
                                <option value="all">Todos</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="deudores-table" data-table="deudores-table" class="table table-hover align-middle">
                            <thead style="background:var(--bg-light);">
                                <tr>
                                    <th class="sortable" data-type="number" data-col="0">#<span class="sort-arrow"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg></span></th>
                                    <th>Deudor</th>
                                    <th>Préstamos</th>
                                    <th>Prestado (total)</th>
                                    <th>Pagado (total)</th>
                                    <th>Pendiente (total)</th>
                                    <th>Último préstamo</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deudoresConDeuda as $d)
                                <tr>
                                    <td class="fw-semibold text-muted">{{ $d['deudor']->id ?? '' }}</td>
                                    <td>
                                        {{ $d['deudor']->nombre ?? 'N/A' }}
                                        @if($d['deudor']?->telefono)
                                            <div class="text-muted small">{{ $d['deudor']->telefono }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $d['prestamos']->count() }}</span>
                                        <span class="text-muted small">{{ $d['prestamos']->pluck('origen_nombre')->unique()->implode(', ') }}</span>
                                    </td>
                                    <td>${{ number_format($d['total_prestado'], 2) }}</td>
                                    <td class="text-success fw-semibold">${{ number_format($d['total_pagado'], 2) }}</td>
                                    <td class="text-danger fw-bold">${{ number_format($d['total_pendiente'], 2) }}</td>
                                    <td class="text-muted small">{{ \Carbon\Carbon::parse($d['ultima_fecha'])->format('d/m/Y') }}</td>
                                    <td>
                                        @if(Auth::user()->hasPermissionTo('prestamos.abonar'))
                                        <button class="btn btn-sm btn-success"
                                                onclick="abonarDeudorTotal({{ $d['deudor']->id }}, '{{ addslashes($d['deudor']->nombre ?? '') }}', {{ $d['total_prestado'] }}, {{ $d['total_pendiente'] }})">
                                            <i class="fas fa-dollar-sign me-1"></i> Abonar
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-check-circle text-success me-2"></i> Sin préstamos pendientes de cobro
                                </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="row mt-3 align-items-center">
                        <div class="col-md-6"><div id="deudores-table-row-info" class="text-muted small"></div></div>
                        <div class="col-md-6"><div id="deudores-table-pagination" class="d-flex justify-content-end flex-wrap"></div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ TAB DEUDAS ══════════════════════════════════ --}}
        <div class="tab-pane fade" id="deudas" role="tabpanel">
            <div class="fin-card card mb-4">
                <div class="fin-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-file-invoice-dollar text-danger me-2"></i> Lista de Deudas Pendientes</span>
                    <span class="badge bg-danger rounded-pill">{{ $deudas->where('estado','pendiente')->count() }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3 align-items-end">
                        <div class="col-lg-8 col-md-7">
                            <label class="form-label fw-semibold small"><i class="fas fa-search text-primary me-1"></i> Buscar</label>
                            <input id="deudas-table-search" type="text" placeholder="Proveedor, monto..." class="form-control">
                        </div>
                        <div class="col-lg-4 col-md-5">
                            <label class="form-label fw-semibold small"><i class="fas fa-list text-primary me-1"></i> Mostrar</label>
                            <select id="deudas-table-rows-per-page" class="form-select">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="20">20</option>
                                <option value="all">Todos</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="deudas-table" data-table="deudas-table" class="table table-hover align-middle">
                            <thead style="background:var(--bg-light);">
                                <tr>
                                    <th class="sortable" data-type="number" data-col="0">#<span class="sort-arrow"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg></span></th>
                                    <th>Proveedor</th>
                                    <th>Monto Total</th>
                                    <th>Pagado</th>
                                    <th>Pendiente</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deudas as $deuda)
                                <tr>
                                    <td class="fw-semibold text-muted">{{ $deuda->id }}</td>
                                    <td>{{ $deuda->proveedor->nombrepro ?? 'N/A' }}</td>
                                    <td>${{ number_format($deuda->monto, 2) }}</td>
                                    <td class="text-success fw-semibold">${{ number_format($deuda->monto_pagado ?? 0, 2) }}</td>
                                    <td class="text-danger fw-bold">${{ number_format($deuda->monto_restante, 2) }}</td>
                                    <td>
                                        <span class="badge rounded-pill bg-{{ $deuda->estado === 'pendiente' ? 'warning text-dark' : 'success' }}">
                                            {{ ucfirst($deuda->estado) }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ \Carbon\Carbon::parse($deuda->updated_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($deuda->estado === 'pendiente' && Auth::user()->hasPermissionTo('bancos.transacciones.store'))
                                        <button class="btn btn-sm btn-success" onclick="pagarDeuda({{ $deuda->id }}, {{ $deuda->monto_restante }})">
                                            <i class="fas fa-dollar-sign me-1"></i> Pagar
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-check-circle text-success me-2"></i> Sin deudas pendientes
                                </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="row mt-3 align-items-center">
                        <div class="col-md-6"><div id="deudas-table-row-info" class="text-muted small"></div></div>
                        <div class="col-md-6"><div id="deudas-table-pagination" class="d-flex justify-content-end flex-wrap"></div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ TAB TRANSACCIONES ═══════════════════════════ --}}
        <div class="tab-pane fade" id="transacciones" role="tabpanel">
            <div class="fin-card card mb-4">
                <div class="fin-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span><i class="fas fa-exchange-alt text-primary me-2"></i> Historial de Transacciones</span>
                    <button class="btn btn-sm btn-outline-primary"
                            onclick="window.dispatchEvent(new CustomEvent('open-modal',{detail:'exportar-transacciones'}))">
                        <i class="fas fa-file-export me-1"></i> Exportar PDF / Excel
                    </button>
                </div>
                <div class="card-body">

                    {{-- Filtros rápidos --}}
                    <div class="filter-bar mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-semibold small mb-1">
                                    <i class="fas fa-search text-primary me-1"></i> Buscar
                                </label>
                                <input id="transacciones-table-search" type="text"
                                       placeholder="Banco, referencia..." class="form-control form-control-sm">
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <label class="form-label fw-semibold small mb-1">
                                    <i class="fas fa-list text-primary me-1"></i> Mostrar
                                </label>
                                <select id="transacciones-table-rows-per-page" class="form-select form-select-sm">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="transacciones-table"
                               data-table="transacciones-table"
                               data-server-side="true"
                               data-search-url="{{ route('bancos.index') }}"
                               data-rows-per-page="25"
                               class="table table-hover align-middle">
                            <thead style="background:var(--bg-light);">
                                <tr>
                                    <th class="sortable" data-type="number" data-col="0">
                                        #<span class="sort-arrow"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg></span>
                                    </th>
                                    <th>Banco</th>
                                    <th>Tipo</th>
                                    <th>Monto Anterior</th>
                                    <th>Transacción</th>
                                    <th>Monto Actualizado</th>
                                    <th>Referencia</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="text-center p-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="row mt-3 align-items-center">
                        <div class="col-md-6"><div id="transacciones-table-row-info" class="text-muted small"></div></div>
                        <div class="col-md-6"><div id="transacciones-table-pagination" class="d-flex justify-content-end flex-wrap"></div></div>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /tab-content --}}
</div>{{-- /container-fluid --}}

{{-- Modales --}}
@include('finance.bancos.modals.transaccion')
@include('finance.bancos.modals.crear')
@include('finance.bancos.modals.editar')
@include('finance.bancos.modals.pagar-deuda')
@include('finance.bancos.modals.transferir')
@include('finance.bancos.modals.exportar')
@include('finance.bancos.modals.fondo-transaccion')
@include('finance.bancos.modals.recargar-fondo')
@include('finance.bancos.modals.nuevo-prestamo')
@include('finance.bancos.modals.abonar-prestamo')

<script src="{{ asset('js/enhanced-table-v2.js') }}?v={{ filemtime(public_path('js/enhanced-table-v2.js')) }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.enhancedTable !== 'undefined') {
        window.enhancedTable.init('deudas-table');
        window.enhancedTable.init('deudores-table');
        window.enhancedTable.init('transacciones-table');
    }
});

// ── Modales ──────────────────────────────────────
function openTransaccionModal(bancoId) {
    document.getElementById('banco_id').value = bancoId;
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'registrar-transaccion' }));
}

function openCreateBancoModal() {
    ['crear_nombreban','crear_tipoban','crear_numeroban','crear_propietarioban','crear_cedulaban','crear_detalleban','crear_foto']
        .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'crear-banco' }));
}

function editarBanco(idban, nombreban, tipoban, detalleban) {
    document.getElementById('edit_nombreban').value  = nombreban;
    document.getElementById('edit_tipoban').value    = tipoban;
    document.getElementById('edit_detalleban').value = detalleban || '';
    const form = document.getElementById('editarBancoForm');
    form.action = "{{ route('bancos.update', ['id' => ':ID:']) }}".replace(':ID:', idban);
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editar-banco' }));
}

window.pagarDeuda = function (deudaId, montoRestante) {
    const deudas = @json($deudas);
    const deuda  = deudas.find(d => d.id === deudaId);
    if (!deuda) { alert('Deuda no encontrada'); return; }

    const montoTotal  = parseFloat(deuda.monto);
    const montoPagado = parseFloat(deuda.monto_pagado) || 0;
    const restante    = montoTotal - montoPagado;

    document.getElementById('deuda_id_display').textContent      = deuda.id;
    document.getElementById('monto_total_display').textContent   = montoTotal.toFixed(2);
    document.getElementById('monto_restante_display').textContent = restante.toFixed(2);
    document.getElementById('deuda_id_input').value   = deuda.id;
    document.getElementById('monto_restante_input').value = restante;
    document.getElementById('monto_abono').value = '';
    document.getElementById('monto_abono').max   = restante;

    const form = document.getElementById('pagarDeudaForm');
    form.action = "{{ route('bancos.pagar-deuda', ['id' => ':ID:']) }}".replace(':ID:', deuda.id);
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'pagar-deuda' }));
};

function openTransferirFondosModal() {
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'transferir' }));
}
</script>
@endsection
