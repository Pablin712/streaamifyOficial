@extends('layouts.table')

@section('title', 'Donna — Suscripciones')

@section('styles')
<style>
    :root { --donna-blue: #274698; --donna-gold: #E4B100; }
    .badge-donna-personal { background-color: var(--donna-blue); color: #fff; }
    .badge-donna-business { background-color: var(--donna-gold); color: #1D1D1B; }
    .icon-donna-blue { color: var(--donna-blue); }
</style>
@endsection

@section('h1', 'Donna — Suscripciones')

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
        <i class="bi bi-robot me-2 icon-donna-blue"></i>Suscripciones Donna
    </h5>
    <p class="text-muted mb-0">
        Gestiona las suscripciones activas, suspendidas y vencidas de clientes con servicio Donna.
    </p>
@endsection

@section('btncrear')
    @if (Auth::user()->hasPermissionTo('donna.suscripciones.store'))
        <button type="button" class="btn btn-primary" onclick="openCreateSubModal()">
            <i class="fas fa-plus me-1"></i> Nueva Suscripción
        </button>
    @endif
@endsection

@section('tablename')
    <i class="bi bi-robot me-1"></i> Suscripciones Donna
@endsection

@section('table1')
    <div class="row mb-3 align-items-end">
        <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
            <label for="donna-subs-table-search" class="form-label fw-semibold">
                <i class="fas fa-search text-primary"></i> Buscar:
            </label>
            <input id="donna-subs-table-search" type="text"
                   placeholder="Buscar cliente, plan, estado..." class="form-control">
        </div>
        <div class="col-lg-4 col-md-5 col-12">
            <label for="donna-subs-table-rows-per-page" class="form-label fw-semibold">
                <i class="fas fa-list text-primary"></i> Mostrar:
            </label>
            <select id="donna-subs-table-rows-per-page" class="form-select">
                <option value="5">5 registros</option>
                <option value="10" selected>10 registros</option>
                <option value="25">25 registros</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table id="donna-subs-table" data-table="donna-subs-table"
               class="table table-striped table-bordered align-middle">
            <thead>
                <tr>
                    <th class="sortable" data-type="number" data-col="0">ID <span class="sort-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg></span></th>
                    <th class="sortable" data-type="string" data-col="1">Cliente <span class="sort-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg></span></th>
                    <th class="sortable" data-type="string" data-col="2">Plan <span class="sort-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg></span></th>
                    <th>Tipo</th>
                    <th>Google</th>
                    <th class="sortable" data-type="string" data-col="5">Estado <span class="sort-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg></span></th>
                    <th>Vencimiento</th>
                    <th>Días restantes</th>
                    @if (Auth::user()->hasPermissionTo('donna.suscripciones.store'))
                        <th data-type="actions">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($suscripciones as $sub)
                    @php
                        $dias = $sub->daysRemaining();
                        $badgeColor = $sub->status_color;
                        if ($sub->status === 'active' && $dias !== null && $dias <= 7) $badgeColor = 'warning';
                        $integ = $integraciones->get($sub->client_id);
                        $avatar = $integ?->metadata_json['avatar'] ?? null;
                    @endphp
                    <tr>
                        <td>{{ $sub->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($avatar)
                                    <img src="{{ $avatar }}" alt=""
                                         class="rounded-circle flex-shrink-0"
                                         style="width:36px;height:36px;object-fit:cover;border:2px solid #dee2e6;"
                                         onerror="this.replaceWith(this.nextElementSibling)">
                                    <span class="rounded-circle bg-light border d-none flex-shrink-0 align-items-center justify-content-center" style="width:36px;height:36px;">
                                        <i class="bi bi-person text-muted"></i>
                                    </span>
                                @else
                                    <span class="rounded-circle bg-light border d-flex flex-shrink-0 align-items-center justify-content-center" style="width:36px;height:36px;">
                                        <i class="bi bi-person text-muted"></i>
                                    </span>
                                @endif
                                <div>
                                    <div class="fw-semibold">{{ $sub->cliente?->nombrecli ?? 'ID '.$sub->client_id }}</div>
                                    <div class="text-muted small">{{ $sub->cliente?->telefonocli }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $sub->plan?->name ?? 'ID '.$sub->plan_id }}</div>
                            <div class="text-muted small">${{ number_format($sub->price_paid, 2) }} {{ $sub->currency }}</div>
                        </td>
                        <td>
                            @if ($sub->service_type === 'personal')
                                <span class="badge badge-donna-personal">Personal</span>
                            @else
                                <span class="badge badge-donna-business">Business</span>
                            @endif
                        </td>
                        <td>
                            @if($integ && $integ->isActive())
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Conectado</span>
                                </div>
                                <div class="text-muted small mt-1" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                    title="{{ $integ->metadata_json['email'] ?? '' }}">
                                    {{ $integ->metadata_json['email'] ?? '' }}
                                </div>
                                @if($integ->isTokenExpired())
                                    <span class="badge bg-warning text-dark mt-1"><i class="bi bi-exclamation-triangle me-1"></i>Token expirado</span>
                                @endif
                            @elseif($integ && $integ->status === 'revoked')
                                <span class="badge bg-secondary">Revocado</span>
                            @else
                                <span class="badge bg-light text-muted border">Sin conectar</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $badgeColor }}">{{ $sub->status_label }}</span>
                            @if (!$sub->is_enabled && $sub->status === 'suspended')
                                <div class="text-muted small mt-1">{{ Str::limit($sub->suspended_reason, 40) }}</div>
                            @endif
                        </td>
                        <td class="small">
                            {{ $sub->expires_at?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="text-center">
                            @if ($dias === null)
                                <span class="text-muted small">Sin límite</span>
                            @elseif ($dias < 0)
                                <span class="badge bg-danger">Vencida</span>
                            @elseif ($dias <= 7)
                                <span class="badge bg-warning text-dark">{{ $dias }} días</span>
                            @else
                                <span class="text-success fw-semibold">{{ $dias }} días</span>
                            @endif
                        </td>
                        @if (Auth::user()->hasPermissionTo('donna.suscripciones.store'))
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="{{ route('donna.suscripciones.config', $sub->id) }}" class="btn btn-outline-primary btn-sm" title="Configurar agente">
                                        <i class="bi bi-sliders"></i>
                                    </a>
                                    <button type="button" class="btn btn-success btn-sm"
                                        onclick="openRenewModal({{ $sub->id }}, '{{ addslashes($sub->cliente?->nombrecli ?? '') }}', '{{ $sub->expires_at?->format('Y-m-d') }}')">
                                        <i class="fas fa-redo" title="Renovar"></i>
                                    </button>
                                    @if ($sub->status === 'active')
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            onclick="openSuspendModal({{ $sub->id }}, '{{ addslashes($sub->cliente?->nombrecli ?? '') }}')">
                                            <i class="fas fa-ban" title="Suspender"></i>
                                        </button>
                                    @endif
                                    @if($integ && $integ->isActive())
                                        <form method="POST" action="{{ route('donna.integraciones.revoke', $integ->id) }}"
                                            onsubmit="return confirm('¿Revocar Google de {{ addslashes($sub->cliente?->nombrecli ?? '') }}?')">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Revocar Google">
                                                <i class="bi bi-google"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-robot fs-1 d-block mb-3" style="color:#274698;opacity:0.3;"></i>
                            No hay suscripciones registradas.<br>
                            <small>Usa el botón <strong>Nueva Suscripción</strong> para empezar.</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="row mt-3 align-items-center">
        <div class="col-md-6 col-12 mb-2 mb-md-0">
            <div id="donna-subs-table-row-info" class="text-muted"></div>
        </div>
        <div class="col-md-6 col-12">
            <div id="donna-subs-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
        </div>
    </div>

    {{-- Modales --}}
    @include('donna.suscripciones.modals.create')
    @include('donna.suscripciones.modals.renew')
    @include('donna.suscripciones.modals.suspend')
@endsection

@section('scripts')
<script src="{{ asset('js/enhanced-table-v2.js') }}?v={{ filemtime(public_path('js/enhanced-table-v2.js')) }}"></script>
<script>
// ── Modal CREAR ────────────────────────────────────────────────────────────
function openCreateSubModal() {
    document.getElementById('createDonnaSubForm').reset();
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'createDonnaSubModal' }));
}

async function submitCreateSub(event) {
    event.preventDefault();
    const form = document.getElementById('createDonnaSubForm');
    const formData = new FormData(form);

    try {
        const response = await fetch('{{ route("donna.suscripciones.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createDonnaSubModal' }));
            setTimeout(() => location.reload(), 800);
        } else {
            alert(data.message || 'Error al crear la suscripción.');
        }
    } catch (e) {
        alert('Error de conexión.');
    }
}

// ── Modal RENOVAR ──────────────────────────────────────────────────────────
function openRenewModal(id, nombre, expiresAt) {
    document.getElementById('renew_sub_id').value      = id;
    document.getElementById('renew_cliente_nombre').textContent = nombre;
    if (expiresAt) document.getElementById('renew_expires_at').value = expiresAt;
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'renewDonnaSubModal' }));
}

async function submitRenewSub(event) {
    event.preventDefault();
    const id = document.getElementById('renew_sub_id').value;
    const formData = new FormData(document.getElementById('renewDonnaSubForm'));

    try {
        const response = await fetch(`/admin/donna/suscripciones/${id}/renew`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'renewDonnaSubModal' }));
            setTimeout(() => location.reload(), 800);
        } else {
            alert(data.message || 'Error al renovar.');
        }
    } catch (e) {
        alert('Error de conexión.');
    }
}

// ── Modal SUSPENDER ────────────────────────────────────────────────────────
function openSuspendModal(id, nombre) {
    document.getElementById('suspend_sub_id').value = id;
    document.getElementById('suspend_cliente_nombre').textContent = nombre;
    document.getElementById('suspend_reason').value = '';
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'suspendDonnaSubModal' }));
}

async function submitSuspendSub(event) {
    event.preventDefault();
    const id = document.getElementById('suspend_sub_id').value;
    const formData = new FormData(document.getElementById('suspendDonnaSubForm'));

    try {
        const response = await fetch(`/admin/donna/suscripciones/${id}/suspend`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'suspendDonnaSubModal' }));
            setTimeout(() => location.reload(), 800);
        } else {
            alert(data.message || 'Error al suspender.');
        }
    } catch (e) {
        alert('Error de conexión.');
    }
}
</script>
@endsection
