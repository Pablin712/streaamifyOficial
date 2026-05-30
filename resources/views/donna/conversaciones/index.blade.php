@extends('layouts.table')

@section('title', 'Donna — Conversaciones')

@section('styles')
<style>
    .donna-nav {
        display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem;
    }
    .donna-nav a {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.45rem 1rem; border-radius: 999px;
        font-size: 0.85rem; font-weight: 600; text-decoration: none;
        background: #f0f2f8; color: #274698;
        transition: background 0.15s;
    }
    .donna-nav a:hover, .donna-nav a.active { background: #274698; color: #fff; }

    .conv-preview {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
        font-size: 0.8rem;
        color: #6c757d;
    }
    .badge-personal { background:#274698; color:#fff; }
    .badge-business { background:#E4B100; color:#1D1D1B; }
</style>
@endsection

@section('h1', 'Donna — Conversaciones')

@section('descripcion')
    <h5 class="mb-1 fw-bold">
        <i class="bi bi-chat-dots me-2" style="color:#274698;"></i>Conversaciones
    </h5>
    <p class="text-muted mb-0">
        Monitorea cómo responde Donna a los clientes finales de cada cliente SaaS.
    </p>
@endsection

@section('tablename')
    <i class="bi bi-chat-square-dots me-1"></i> Lista de conversaciones
@endsection

@section('table1')

    {{-- Donna Hub Nav --}}
    <div class="donna-nav">
        <a href="{{ route('donna.dashboard') }}"><i class="bi bi-speedometer2"></i>Dashboard</a>
        <a href="{{ route('donna.conversaciones.index') }}" class="active"><i class="bi bi-chat-dots"></i>Conversaciones</a>
        <a href="{{ route('donna.suscripciones.index') }}"><i class="bi bi-check2-circle"></i>Suscripciones</a>
        <a href="{{ route('donna.planes.index') }}"><i class="bi bi-card-list"></i>Planes</a>
        <a href="{{ route('donna.solicitudes.index') }}"><i class="bi bi-inbox"></i>Solicitudes</a>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('donna.conversaciones.index') }}" class="row g-2 mb-4 align-items-end">
        <div class="col-sm-5 col-lg-4">
            <label class="form-label fw-semibold small mb-1">
                <i class="fas fa-search text-primary me-1"></i>Buscar
            </label>
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Cliente, nombre del chat, número..."
                   value="{{ request('search') }}">
        </div>
        <div class="col-sm-3 col-lg-2">
            <label class="form-label fw-semibold small mb-1">Tipo</label>
            <select name="tipo" class="form-select form-select-sm">
                <option value="">Todos</option>
                <option value="telegram" @selected(request('tipo') === 'telegram')>Personal (Telegram)</option>
                <option value="whatsapp" @selected(request('tipo') === 'whatsapp')>Business (WhatsApp)</option>
            </select>
        </div>
        <div class="col-sm-3 col-lg-2">
            <label class="form-label fw-semibold small mb-1">Estado</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Todos</option>
                <option value="open" @selected(request('status') === 'open')>Abierta</option>
                <option value="human_takeover" @selected(request('status') === 'human_takeover')>Humano</option>
                <option value="closed" @selected(request('status') === 'closed')>Cerrada</option>
            </select>
        </div>
        <div class="col-sm-1 col-lg-2">
            <button type="submit" class="btn btn-primary btn-sm w-100">
                <i class="fas fa-search"></i><span class="d-none d-sm-inline ms-1">Filtrar</span>
            </button>
        </div>
        @if(request()->hasAny(['search','tipo','status']))
        <div class="col-auto">
            <a href="{{ route('donna.conversaciones.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x-circle me-1"></i>Limpiar
            </a>
        </div>
        @endif
    </form>

    {{-- Tabla --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle" style="font-size:0.88rem;">
            <thead class="table-light">
                <tr>
                    <th>Cliente SaaS</th>
                    <th>Chat con</th>
                    <th class="text-center">Tipo</th>
                    <th class="text-center">Msgs</th>
                    <th>Último mensaje</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Ver</th>
                </tr>
            </thead>
            <tbody>
                @forelse($conversaciones as $conv)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $conv->cliente?->nombrecli ?? '—' }}</div>
                        <div class="text-muted small">ID {{ $conv->client_id }}</div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $conv->sender_name ?: '—' }}</div>
                        <div class="text-muted small font-monospace" style="font-size:0.75rem;">
                            {{ $conv->external_chat_id }}
                        </div>
                    </td>
                    <td class="text-center">
                        @if($conv->channel?->channel_type === 'whatsapp')
                            <span class="badge badge-business">
                                <i class="bi bi-whatsapp me-1"></i>Business
                            </span>
                        @elseif($conv->channel?->channel_type === 'telegram')
                            <span class="badge badge-personal">
                                <i class="bi bi-telegram me-1"></i>Personal
                            </span>
                        @else
                            <span class="badge bg-secondary">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border">{{ $conv->messages_count }}</span>
                    </td>
                    <td>
                        @if($conv->last_message_at)
                            <div class="text-muted small">{{ $conv->last_message_at->diffForHumans() }}</div>
                        @endif
                        @if($conv->last_message_preview)
                            <div class="conv-preview">{{ $conv->last_message_preview }}</div>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($conv->status === 'open')
                            <span class="badge bg-success">Abierta</span>
                        @elseif($conv->status === 'human_takeover')
                            <span class="badge bg-warning text-dark">Humano</span>
                        @else
                            <span class="badge bg-secondary">Cerrada</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill"
                                style="font-size:0.78rem;"
                                onclick="verConversacion({{ $conv->id }})">
                            <i class="bi bi-eye me-1"></i>Ver
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-chat-dots fs-3 d-block mb-2 opacity-50"></i>
                        No hay conversaciones que coincidan con los filtros.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <div class="text-muted small">
            Mostrando {{ $conversaciones->firstItem() ?? 0 }}–{{ $conversaciones->lastItem() ?? 0 }}
            de {{ $conversaciones->total() }} conversaciones
        </div>
        {{ $conversaciones->links() }}
    </div>

    {{-- Modal --}}
    @include('donna.conversaciones._modal')

@endsection

@section('scripts')
<script>
    function verConversacion(id) {
        const body  = document.getElementById('donnaConvBody');
        const title = document.getElementById('donnaConvTitle');

        body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>';
        title.innerHTML = '<i class="bi bi-chat-dots me-2" style="color:#274698;"></i>Cargando...';

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'donnaConvModal' }));

        fetch(`/admin/donna/conversaciones/${id}/messages`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(data => renderConvModal(data))
        .catch(() => {
            document.getElementById('donnaConvBody').innerHTML =
                '<div class="alert alert-danger m-2">Error al cargar los mensajes.</div>';
        });
    }

    function renderConvModal(data) {
        const c     = data.conversation;
        const title = document.getElementById('donnaConvTitle');
        const body  = document.getElementById('donnaConvBody');

        const typeIcon = c.channel_type === 'whatsapp'
            ? '<i class="bi bi-whatsapp text-success me-1"></i>'
            : '<i class="bi bi-telegram me-1" style="color:#274698;"></i>';

        title.innerHTML = `${typeIcon}Chat con ${c.sender_name || '#' + c.id}`;

        let statusBadge = c.status === 'open'
            ? '<span class="badge bg-success">Abierta</span>'
            : (c.status === 'human_takeover'
                ? '<span class="badge bg-warning text-dark">Humano</span>'
                : '<span class="badge bg-secondary">Cerrada</span>');

        let html = `<div class="d-flex flex-wrap gap-2 align-items-center small text-muted mb-3 pb-2 border-bottom">
            ${typeIcon}<strong>${c.channel_type}</strong> &nbsp;·&nbsp;
            <strong>${c.cliente}</strong> &nbsp;·&nbsp; ${statusBadge}
            ${c.last_message_at ? `&nbsp;·&nbsp; ${c.last_message_at}` : ''}
        </div><div class="d-flex flex-column gap-2">`;

        if (data.messages.length === 0) {
            html += '<div class="text-center text-muted py-3">Sin mensajes visibles.</div>';
        } else {
            data.messages.forEach(m => {
                const isAI   = m.sender_type === 'ai_agent';
                const align  = isAI ? 'align-self-end' : 'align-self-start';
                const bg     = isAI ? '#f0f4ff' : '#e9f5e9';
                const label  = isAI
                    ? `<span class="badge" style="background:#274698;font-size:0.6rem;">Donna</span>`
                    : `<span class="badge bg-secondary" style="font-size:0.6rem;">Cliente</span>`;
                const content = m.content
                    ? m.content.replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>')
                    : `<em class="text-muted">[${m.message_type}]</em>`;

                html += `<div class="${align}" style="max-width:82%;">
                    <div class="d-flex align-items-center gap-1 mb-1">
                        ${label}
                        <span class="text-muted" style="font-size:0.7rem;">${m.created_at}</span>
                    </div>
                    <div class="p-2 px-3 rounded-3 small" style="background:${bg};border:1px solid #dee2e6;line-height:1.5;">${content}</div>
                </div>`;
            });
        }

        html += '</div>';
        body.innerHTML = html;
    }
</script>
@endsection
