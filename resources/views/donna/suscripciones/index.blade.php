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
    @php
        $subsActivas = $suscripciones->where('status', 'active')->values();
        $subsHistorial = $suscripciones->whereNotIn('status', ['active'])->values();
    @endphp

    <ul class="nav nav-tabs mb-3" id="donnaSubsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="subs-activas-tab" data-bs-toggle="tab" data-bs-target="#subs-activas" type="button" role="tab">
                <i class="bi bi-check-circle me-1"></i> Activas <span class="badge bg-secondary ms-1">{{ $subsActivas->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="subs-historial-tab" data-bs-toggle="tab" data-bs-target="#subs-historial" type="button" role="tab">
                <i class="bi bi-archive me-1"></i> Historial <span class="badge bg-secondary ms-1">{{ $subsHistorial->count() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="subs-activas" role="tabpanel">
            @include('donna.suscripciones._tabla', [
                'tableId' => 'donna-subs-table',
                'rows' => $subsActivas,
                'integraciones' => $integraciones,
                'placeholder' => 'No hay suscripciones activas. Usa el botón Nueva Suscripción para empezar.',
            ])
        </div>
        <div class="tab-pane fade" id="subs-historial" role="tabpanel">
            @include('donna.suscripciones._tabla', [
                'tableId' => 'donna-subs-table-hist',
                'rows' => $subsHistorial,
                'integraciones' => $integraciones,
                'placeholder' => 'No hay suscripciones suspendidas, vencidas o canceladas.',
            ])
        </div>
    </div>

    {{-- Modales --}}
    @include('donna.suscripciones.modals.create')
    @include('donna.suscripciones.modals.renew')
    @include('donna.suscripciones.modals.suspend')
    @include('donna.suscripciones.modals.channel')
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

// ── Modal CANAL WHATSAPP ───────────────────────────────────────────────────
async function openChannelModal(id, nombre) {
    document.getElementById('channel_sub_id').value = id;
    document.getElementById('channel_cliente_nombre').textContent = nombre;
    document.getElementById('channelDonnaSubForm').reset();
    document.getElementById('channel_sub_id').value = id;

    const banner = document.getElementById('channel_status_banner');
    banner.className = 'alert alert-secondary mb-3';
    banner.textContent = 'Cargando...';
    banner.classList.remove('d-none');

    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'channelDonnaSubModal' }));

    try {
        const response = await fetch(`/admin/donna/suscripciones/${id}/channel`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();

        document.getElementById('channel_instance_name').value = data.instance_name || '';
        document.getElementById('channel_api_base_url').value = data.api_base_url || '';
        document.getElementById('channel_phone_number').value = data.phone_number || '';

        const keyHint = document.getElementById('channel_api_key_hint');
        if (data.has_api_key) {
            keyHint.textContent = 'Ya hay una API Key guardada. Déjalo vacío para mantenerla, o escribe una nueva para reemplazarla.';
        } else {
            keyHint.textContent = 'Se guarda encriptada.';
        }
        document.getElementById('channel_api_key').required = !data.has_api_key;

        if (!data.exists) {
            banner.className = 'alert alert-warning mb-3';
            banner.textContent = 'Este canal aún no está configurado. Completa los datos de Evo API para activarlo.';
        } else if (data.status === 'pending') {
            banner.className = 'alert alert-warning mb-3';
            banner.textContent = 'Canal pendiente de configuración manual. Completa los datos y se activará al guardar.';
        } else if (data.status === 'active') {
            banner.className = 'alert alert-success mb-3';
            banner.textContent = 'Canal activo.';
        } else {
            banner.className = 'alert alert-secondary mb-3';
            banner.textContent = 'Estado del canal: ' + data.status;
        }
    } catch (e) {
        banner.className = 'alert alert-danger mb-3';
        banner.textContent = 'No se pudo cargar la información del canal.';
    }
}

async function submitChannelSub(event) {
    event.preventDefault();
    const id = document.getElementById('channel_sub_id').value;
    const formData = new FormData(document.getElementById('channelDonnaSubForm'));

    try {
        const response = await fetch(`/admin/donna/suscripciones/${id}/channel`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'channelDonnaSubModal' }));
            setTimeout(() => location.reload(), 800);
        } else {
            alert(data.message || 'Error al guardar el canal.');
        }
    } catch (e) {
        alert('Error de conexión.');
    }
}

// ── Eliminar canal WhatsApp ────────────────────────────────────────────────
async function deleteChannel(id, nombre) {
    if (!confirm(`¿Eliminar el canal de WhatsApp de ${nombre}? Donna dejará de responder por ese número hasta reconfigurarlo.`)) {
        return;
    }

    try {
        const response = await fetch(`/admin/donna/suscripciones/${id}/channel`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al eliminar el canal.');
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
