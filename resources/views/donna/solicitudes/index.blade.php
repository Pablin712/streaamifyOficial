@extends('layouts.table')

@section('title', 'Donna — Solicitudes')

@section('h1', 'Donna — Solicitudes')

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
        <i class="bi bi-robot me-2" style="color:#274698;"></i>Solicitudes de Donna
    </h5>
    <p class="text-muted mb-0">
        Revisa y aprueba las solicitudes de clientes que quieren contratar Donna.
        Al aprobar, se crea la suscripción automáticamente.
    </p>
@endsection

@section('tablename')
    <i class="bi bi-inbox me-1"></i> Solicitudes Donna
@endsection

@section('table1')
    <div class="row mb-3 align-items-end">
        <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
            <label for="donna-reqs-table-search" class="form-label fw-semibold">
                <i class="fas fa-search text-primary"></i> Buscar:
            </label>
            <input id="donna-reqs-table-search" type="text"
                   placeholder="Buscar cliente, plan, estado..." class="form-control">
        </div>
        <div class="col-lg-4 col-md-5 col-12">
            <label for="donna-reqs-table-rows-per-page" class="form-label fw-semibold">
                <i class="fas fa-list text-primary"></i> Mostrar:
            </label>
            <select id="donna-reqs-table-rows-per-page" class="form-select">
                <option value="5">5 registros</option>
                <option value="10" selected>10 registros</option>
                <option value="25">25 registros</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table id="donna-reqs-table" data-table="donna-reqs-table"
               class="table table-striped table-bordered align-middle">
            <thead>
                <tr>
                    <th class="sortable" data-type="number" data-col="0">ID <span class="sort-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg></span></th>
                    <th class="sortable" data-type="string" data-col="1">Cliente <span class="sort-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg></span></th>
                    <th class="sortable" data-type="string" data-col="2">Plan <span class="sort-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg></span></th>
                    <th>Tipo</th>
                    <th class="sortable" data-type="string" data-col="4">Estado <span class="sort-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg></span></th>
                    <th>Mensaje del cliente</th>
                    <th class="sortable" data-type="string" data-col="6">Fecha <span class="sort-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg></span></th>
                    @if (Auth::user()->hasPermissionTo('donna.solicitudes.manage'))
                        <th data-type="actions">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($solicitudes as $sol)
                    <tr>
                        <td>{{ $sol->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $sol->cliente?->nombrecli ?? 'ID '.$sol->client_id }}</div>
                            <div class="text-muted small">{{ $sol->cliente?->telefonocli }}</div>
                        </td>
                        <td>{{ $sol->plan?->name ?? 'ID '.$sol->plan_id }}</td>
                        <td>
                            @if ($sol->plan?->service_type === 'personal')
                                <span class="badge" style="background:#274698;">Personal</span>
                            @else
                                <span class="badge" style="background:#E4B100;color:#1D1D1B;">Business</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $sol->status_color }}">{{ $sol->status_label }}</span>
                            @if ($sol->employee_notes)
                                <div class="text-muted small mt-1">{{ Str::limit($sol->employee_notes, 50) }}</div>
                            @endif
                        </td>
                        <td class="small text-muted">{{ Str::limit($sol->message, 80) ?: '—' }}</td>
                        <td class="small">{{ $sol->created_at->format('d/m/Y H:i') }}</td>
                        @if (Auth::user()->hasPermissionTo('donna.solicitudes.manage'))
                            <td>
                                @if ($sol->status === 'pending')
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-success btn-sm"
                                            onclick="openApproveModal({{ $sol->id }}, '{{ addslashes($sol->cliente?->nombrecli ?? '') }}', '{{ addslashes($sol->plan?->name ?? '') }}')">
                                            <i class="fas fa-check" title="Aprobar"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm"
                                            onclick="openRejectModal({{ $sol->id }}, '{{ addslashes($sol->cliente?->nombrecli ?? '') }}')">
                                            <i class="fas fa-times" title="Rechazar"></i>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-muted small">
                                        {{ $sol->reviewedBy?->nombreemp ?? '—' }}<br>
                                        {{ $sol->reviewed_at?->format('d/m/Y') }}
                                    </span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3" style="opacity:0.3;"></i>
                            No hay solicitudes pendientes.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="row mt-3 align-items-center">
        <div class="col-md-6 col-12 mb-2 mb-md-0">
            <div id="donna-reqs-table-row-info" class="text-muted"></div>
        </div>
        <div class="col-md-6 col-12">
            <div id="donna-reqs-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
        </div>
    </div>

    {{-- Modales --}}
    @include('donna.solicitudes.modals.approve')
    @include('donna.solicitudes.modals.reject')
@endsection

@section('scripts')
<script src="{{ asset('js/enhanced-table-v2.js') }}?v={{ filemtime(public_path('js/enhanced-table-v2.js')) }}"></script>
<script>
function openApproveModal(id, cliente, plan) {
    document.getElementById('approve_req_id').value = id;
    document.getElementById('approve_req_cliente').textContent = cliente;
    document.getElementById('approve_req_plan').textContent = plan;
    document.getElementById('approve_employee_notes').value = '';
    document.getElementById('approve_expires_at').value = '';
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'approveDonnaReqModal' }));
}

async function submitApproveReq(event) {
    event.preventDefault();
    const id = document.getElementById('approve_req_id').value;
    const formData = new FormData(document.getElementById('approveDonnaReqForm'));

    try {
        const response = await fetch(`/admin/donna/solicitudes/${id}/approve`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'approveDonnaReqModal' }));
            setTimeout(() => location.reload(), 800);
        } else {
            alert(data.message || 'Error al aprobar.');
        }
    } catch (e) {
        alert('Error de conexión.');
    }
}

function openRejectModal(id, cliente) {
    document.getElementById('reject_req_id').value = id;
    document.getElementById('reject_req_cliente').textContent = cliente;
    document.getElementById('reject_employee_notes').value = '';
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'rejectDonnaReqModal' }));
}

async function submitRejectReq(event) {
    event.preventDefault();
    const id = document.getElementById('reject_req_id').value;
    const formData = new FormData(document.getElementById('rejectDonnaReqForm'));

    try {
        const response = await fetch(`/admin/donna/solicitudes/${id}/reject`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'rejectDonnaReqModal' }));
            setTimeout(() => location.reload(), 800);
        } else {
            alert(data.message || 'Error al rechazar.');
        }
    } catch (e) {
        alert('Error de conexión.');
    }
}
</script>
@endsection
