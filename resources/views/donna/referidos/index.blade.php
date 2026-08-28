@extends('layouts.table')

@section('title', 'Donna — Referidos')

@section('styles')
<style>
    :root { --donna-blue: #274698; --donna-gold: #E4B100; }
    .icon-donna-blue { color: var(--donna-blue); }
</style>
@endsection

@section('h1', 'Donna — Referidos')

@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <h5 class="mb-1 fw-bold">
        <i class="bi bi-people me-2 icon-donna-blue"></i>Programa de referidos Donna
    </h5>
    <p class="text-muted mb-0">
        Un partner tiene un código que sus referidos pueden usar al contratar Donna: el referido paga
        menos, y el partner gana una comisión que se acredita a su saldo cada vez que paga
        (activación y cada renovación).
    </p>
@endsection

@section('btncrear')
    @if (Auth::user()->hasPermissionTo('donna.referidos.store'))
        <button type="button" class="btn btn-primary" onclick="openCreatePartnerModal()">
            <i class="fas fa-plus me-1"></i> Nuevo Partner
        </button>
    @endif
@endsection

@section('tablename')
    <i class="bi bi-people me-1"></i> Referidos Donna
@endsection

@section('table1')
    <ul class="nav nav-tabs mb-3" id="donnaReferidosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="ref-partners-tab" data-bs-toggle="tab" data-bs-target="#ref-partners" type="button" role="tab">
                <i class="bi bi-person-badge me-1"></i> Partners <span class="badge bg-secondary ms-1">{{ $partners->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="ref-earnings-tab" data-bs-toggle="tab" data-bs-target="#ref-earnings" type="button" role="tab">
                <i class="bi bi-cash-coin me-1"></i> Comisiones <span class="badge bg-secondary ms-1">{{ $earnings->count() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ── Tab Partners ─────────────────────────────────────────────── --}}
        <div class="tab-pane fade show active" id="ref-partners" role="tabpanel">
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="donna-referidos-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search text-primary"></i> Buscar:
                    </label>
                    <input id="donna-referidos-table-search" type="text"
                           placeholder="Buscar código, cliente..." class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="donna-referidos-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list text-primary"></i> Mostrar:
                    </label>
                    <select id="donna-referidos-table-rows-per-page" class="form-select">
                        <option value="5">5 registros</option>
                        <option value="10" selected>10 registros</option>
                        <option value="25">25 registros</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table id="donna-referidos-table" data-table="donna-referidos-table"
                       class="table table-striped table-bordered align-middle">
                    <thead>
                        <tr>
                            <th class="sortable" data-type="number" data-col="0">ID</th>
                            <th class="sortable" data-type="string" data-col="1">Partner</th>
                            <th class="sortable" data-type="string" data-col="2">Código</th>
                            <th class="sortable" data-type="number" data-col="3">Descuento cliente</th>
                            <th class="sortable" data-type="number" data-col="4">Comisión partner</th>
                            <th class="sortable" data-type="number" data-col="5">Total ganado</th>
                            <th class="sortable" data-type="string" data-col="6">Estado</th>
                            @if (Auth::user()->hasAnyPermission(['donna.referidos.store', 'donna.referidos.destroy']))
                                <th data-type="actions">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($partners as $partner)
                            <tr>
                                <td>{{ $partner->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $partner->cliente?->nombrecli ?? 'ID '.$partner->client_id }}</div>
                                    <div class="text-muted small">{{ $partner->cliente?->telefonocli }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border font-monospace">{{ $partner->code }}</span></td>
                                <td>${{ number_format($partner->discount_amount, 2) }}</td>
                                <td>${{ number_format($partner->commission_amount, 2) }}</td>
                                <td class="fw-bold text-success">
                                    ${{ number_format($partner->earnings_sum_commission_amount ?? 0, 2) }}
                                    <div class="text-muted small fw-normal">{{ $partner->earnings_count }} pago(s)</div>
                                </td>
                                <td>
                                    @if ($partner->is_active)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>
                                @if (Auth::user()->hasAnyPermission(['donna.referidos.store', 'donna.referidos.destroy']))
                                    <td>
                                        <div class="d-flex gap-1 flex-wrap">
                                            @if (Auth::user()->hasPermissionTo('donna.referidos.store'))
                                                <button type="button" class="btn btn-warning btn-sm"
                                                    onclick="openEditPartnerModal({{ $partner->id }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endif
                                            @if (Auth::user()->hasPermissionTo('donna.referidos.destroy'))
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="deletePartner({{ $partner->id }}, '{{ addslashes($partner->code) }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-3" style="color:#274698;opacity:0.3;"></i>
                                    No hay partners de referido aún.<br>
                                    <small>Usa el botón <strong>Nuevo Partner</strong> para empezar.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="row mt-3 align-items-center">
                <div class="col-md-6 col-12 mb-2 mb-md-0">
                    <div id="donna-referidos-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6 col-12">
                    <div id="donna-referidos-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                </div>
            </div>
        </div>

        {{-- ── Tab Comisiones (ledger, solo lectura) ────────────────────────── --}}
        <div class="tab-pane fade" id="ref-earnings" role="tabpanel">
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="donna-earnings-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search text-primary"></i> Buscar:
                    </label>
                    <input id="donna-earnings-table-search" type="text"
                           placeholder="Buscar partner, cliente, evento..." class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="donna-earnings-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list text-primary"></i> Mostrar:
                    </label>
                    <select id="donna-earnings-table-rows-per-page" class="form-select">
                        <option value="5">5 registros</option>
                        <option value="10" selected>10 registros</option>
                        <option value="25">25 registros</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table id="donna-earnings-table" data-table="donna-earnings-table"
                       class="table table-striped table-bordered align-middle">
                    <thead>
                        <tr>
                            <th class="sortable" data-type="number" data-col="0">ID</th>
                            <th class="sortable" data-type="string" data-col="1">Partner</th>
                            <th class="sortable" data-type="string" data-col="2">Cliente referido</th>
                            <th class="sortable" data-type="string" data-col="3">Evento</th>
                            <th class="sortable" data-type="number" data-col="4">Pagó el cliente</th>
                            <th class="sortable" data-type="number" data-col="5">Comisión acreditada</th>
                            <th class="sortable" data-type="string" data-col="6">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($earnings as $earning)
                            <tr>
                                <td>{{ $earning->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $earning->partner?->cliente?->nombrecli ?? '—' }}</div>
                                    <div class="text-muted small font-monospace">{{ $earning->partner?->code }}</div>
                                </td>
                                <td>{{ $earning->cliente?->nombrecli ?? 'ID '.$earning->client_id }}</td>
                                <td>
                                    @if ($earning->event_type === 'activation')
                                        <span class="badge bg-primary">Activación</span>
                                    @else
                                        <span class="badge bg-info text-dark">Renovación</span>
                                    @endif
                                </td>
                                <td>${{ number_format($earning->payment_amount, 2) }}</td>
                                <td class="fw-bold text-success">${{ number_format($earning->commission_amount, 2) }}</td>
                                <td class="small">{{ $earning->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-cash-coin fs-1 d-block mb-3" style="color:#274698;opacity:0.3;"></i>
                                    Aún no se ha acreditado ninguna comisión.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="row mt-3 align-items-center">
                <div class="col-md-6 col-12 mb-2 mb-md-0">
                    <div id="donna-earnings-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6 col-12">
                    <div id="donna-earnings-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                </div>
            </div>
        </div>

    </div>

    {{-- Modales --}}
    @include('donna.referidos.modals.create')
    @include('donna.referidos.modals.edit')
@endsection

@section('scripts')
<script src="{{ asset('js/enhanced-table-v2.js') }}?v={{ filemtime(public_path('js/enhanced-table-v2.js')) }}"></script>
<script>
// ── Modal CREAR ────────────────────────────────────────────────────────────
function openCreatePartnerModal() {
    document.getElementById('createReferralPartnerForm').reset();
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'createReferralPartnerModal' }));
}

async function submitCreatePartner(event) {
    event.preventDefault();
    const form = document.getElementById('createReferralPartnerForm');
    const formData = new FormData(form);

    try {
        const response = await fetch('{{ route("donna.referidos.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createReferralPartnerModal' }));
            setTimeout(() => location.reload(), 800);
        } else {
            alert(data.message || 'Error al crear el partner.');
        }
    } catch (e) {
        alert('Error de conexión.');
    }
}

// ── Modal EDITAR ───────────────────────────────────────────────────────────
function openEditPartnerModal(id) {
    fetch(`/admin/donna/referidos/${id}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(partner => {
        document.getElementById('edit_partner_id').value = partner.id;
        document.getElementById('edit_partner_client_id').value = partner.client_id;
        document.getElementById('edit_partner_code').value = partner.code;
        document.getElementById('edit_partner_discount').value = partner.discount_amount;
        document.getElementById('edit_partner_commission').value = partner.commission_amount;
        document.getElementById('edit_partner_is_active').value = partner.is_active ? '1' : '0';
        document.getElementById('edit_partner_notes').value = partner.notes ?? '';
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editReferralPartnerModal' }));
    })
    .catch(() => alert('Error al cargar los datos del partner.'));
}

async function submitEditPartner(event) {
    event.preventDefault();
    const id = document.getElementById('edit_partner_id').value;
    const formData = new FormData(document.getElementById('editReferralPartnerForm'));
    formData.set('_method', 'PUT');

    try {
        const response = await fetch(`/admin/donna/referidos/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editReferralPartnerModal' }));
            setTimeout(() => location.reload(), 800);
        } else {
            alert(data.message || 'Error al actualizar el partner.');
        }
    } catch (e) {
        alert('Error de conexión.');
    }
}

// ── Eliminar ───────────────────────────────────────────────────────────────
async function deletePartner(id, codigo) {
    if (!confirm(`¿Eliminar el partner de referido "${codigo}"? Su código dejará de funcionar para nuevos clientes.`)) {
        return;
    }
    try {
        const response = await fetch(`/admin/donna/referidos/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al eliminar el partner.');
        }
    } catch (e) {
        alert('Error de conexión.');
    }
}
</script>
@endsection
