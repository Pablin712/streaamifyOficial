@extends('layouts.table')

@section('title', 'Donna — Dashboard')

@section('styles')
<style>
    :root {
        --donna-blue: #274698;
        --donna-gold: #E4B100;
        --donna-nav-bg: #f0f2f8;
    }
    [data-dark-mode="true"] {
        --donna-nav-bg: rgba(39, 70, 152, 0.15);
    }
    .donna-kpi {
        border-radius: 18px;
        padding: 1.25rem 1.4rem;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .donna-kpi .kpi-icon {
        position: absolute;
        right: 1.2rem;
        bottom: 0.8rem;
        font-size: 3.2rem;
        opacity: 0.15;
    }
    .donna-kpi .kpi-val { font-size: 2.4rem; font-weight: 800; line-height: 1; }
    .donna-kpi .kpi-label { font-size: 0.85rem; opacity: 0.88; margin-top: 0.3rem; }
    .kpi-blue   { background: linear-gradient(135deg, var(--donna-blue), #3a5bbf); }
    .kpi-yellow { background: linear-gradient(135deg, #b88a00, var(--donna-gold)); color: #1D1D1B !important; }
    .kpi-green  { background: linear-gradient(135deg, #1a7a4a, var(--success-color, #28a745)); }
    .kpi-purple { background: linear-gradient(135deg, #5a3c9a, #7952cc); }
    .donna-kpi.kpi-yellow .kpi-label { color: #1D1D1B; opacity: 0.75; }

    .rank-badge {
        width: 28px; height: 28px;
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 0.8rem;
        background: var(--donna-nav-bg); color: var(--donna-blue);
    }
    .rank-badge.top1 { background: var(--donna-gold); color: #1D1D1B; }
    .rank-badge.top2 { background: var(--donna-blue); color: #fff; }
    .rank-badge.top3 { background: #5a3c9a; color: #fff; }

    .conv-preview {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        font-size: 0.82rem;
        color: var(--text-muted);
    }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .status-dot.open     { background: var(--success-color, #28a745); }
    .status-dot.takeover { background: var(--warning-color, #fd7e14); }
    .status-dot.closed   { background: var(--text-muted, #adb5bd); }

    .donna-nav { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem; }
    .donna-nav a {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.45rem 1rem; border-radius: 999px;
        font-size: 0.85rem; font-weight: 600; text-decoration: none;
        background: var(--donna-nav-bg); color: var(--donna-blue);
        transition: background 0.15s;
    }
    .donna-nav a:hover, .donna-nav a.active { background: var(--donna-blue); color: #fff; }
    .icon-donna-blue { color: var(--donna-blue); }
</style>
@endsection

@section('h1', 'Donna — Dashboard')

@section('descripcion')
    <h5 class="mb-1 fw-bold">
        <i class="bi bi-speedometer2 me-2 icon-donna-blue"></i>Dashboard Donna
    </h5>
    <p class="text-muted mb-0">Visión general de clientes activos, conversaciones y uso de Donna AI.</p>
@endsection

@section('tablename')
    <i class="bi bi-graph-up me-1"></i> Métricas generales
@endsection

@section('table1')

    {{-- Donna Hub Nav --}}
    <div class="donna-nav">
        <a href="{{ route('donna.dashboard') }}" class="active"><i class="bi bi-speedometer2"></i>Dashboard</a>
        <a href="{{ route('donna.conversaciones.index') }}"><i class="bi bi-chat-dots"></i>Conversaciones</a>
        <a href="{{ route('donna.suscripciones.index') }}"><i class="bi bi-check2-circle"></i>Suscripciones</a>
        <a href="{{ route('donna.planes.index') }}"><i class="bi bi-card-list"></i>Planes</a>
        <a href="{{ route('donna.solicitudes.index') }}"><i class="bi bi-inbox"></i>Solicitudes</a>
    </div>

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="donna-kpi kpi-blue">
                <div class="kpi-val">{{ $totalSubsActivas }}</div>
                <div class="kpi-label">Suscripciones activas</div>
                <i class="bi bi-stars kpi-icon"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="donna-kpi kpi-yellow">
                <div class="kpi-val">{{ $totalPersonal }} / {{ $totalBusiness }}</div>
                <div class="kpi-label">Personal / Business</div>
                <i class="bi bi-robot kpi-icon"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="donna-kpi kpi-green">
                <div class="kpi-val">{{ $convsHoy }}</div>
                <div class="kpi-label">Conversaciones hoy</div>
                <i class="bi bi-chat-dots kpi-icon"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="donna-kpi kpi-purple">
                <div class="kpi-val">{{ $mensajesHoy }}</div>
                <div class="kpi-label">Mensajes procesados hoy</div>
                <i class="bi bi-send kpi-icon"></i>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- Top clientes --}}
        <div class="col-lg-7">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0 icon-donna-blue">
                    <i class="bi bi-trophy me-1"></i>Clientes más activos
                </h6>
                <span class="text-muted small">{{ $totalConvs }} conversaciones en total</span>
            </div>

            @if($topClientes->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="bi bi-chat-square-dots fs-3 d-block mb-2"></i>
                    Aún no hay conversaciones registradas.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.9rem;">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Cliente</th>
                            <th class="text-center">Chats</th>
                            <th>Última actividad</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topClientes as $i => $item)
                        <tr>
                            <td>
                                <span class="rank-badge {{ $i === 0 ? 'top1' : ($i === 1 ? 'top2' : ($i === 2 ? 'top3' : '')) }}">
                                    {{ $i + 1 }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $item->cliente?->nombrecli ?? 'Cliente #'.$item->client_id }}</div>
                                <div class="text-muted small">ID {{ $item->client_id }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill" style="background:#274698;font-size:0.85rem;padding:0.35em 0.75em;">
                                    {{ $item->total_convs }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ $item->ultima_actividad ? \Carbon\Carbon::parse($item->ultima_actividad)->diffForHumans() : '—' }}
                            </td>
                            <td>
                                <a href="{{ route('donna.conversaciones.index', ['search' => $item->cliente?->nombrecli ?? $item->client_id]) }}"
                                   class="btn btn-outline-primary btn-sm rounded-pill" style="font-size:0.78rem;">
                                    Ver chats
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Conversaciones recientes --}}
        <div class="col-lg-5">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0 icon-donna-blue">
                    <i class="bi bi-clock-history me-1"></i>Actividad reciente
                </h6>
                <a href="{{ route('donna.conversaciones.index') }}" class="small fw-semibold" style="color:#274698;">
                    Ver todo <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            @if($conversacionesRecientes->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="bi bi-chat-dots fs-3 d-block mb-2"></i>
                    Sin actividad reciente.
                </div>
            @else
            <div class="d-grid gap-2">
                @foreach($conversacionesRecientes as $conv)
                <div class="d-flex gap-3 p-3 rounded-3" style="background:#f8f9fc;border:1px solid #e9ecef;cursor:pointer;"
                     onclick="verConversacion({{ $conv->id }})">
                    {{-- Canal icon --}}
                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle"
                         style="width:38px;height:38px;background:{{ $conv->channel?->channel_type === 'whatsapp' ? '#e8f5e9' : '#f0f4ff' }};">
                        <i class="bi bi-{{ $conv->channel?->channel_type === 'whatsapp' ? 'whatsapp text-success' : 'telegram' }}"
                           style="{{ $conv->channel?->channel_type !== 'whatsapp' ? 'color:#274698;' : '' }}"></i>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <span class="fw-semibold small text-truncate">
                                {{ $conv->sender_name ?: $conv->external_chat_id }}
                            </span>
                            <span class="status-dot {{ $conv->status === 'open' ? 'open' : ($conv->status === 'human_takeover' ? 'takeover' : 'closed') }} flex-shrink-0"></span>
                        </div>
                        <div class="text-muted" style="font-size:0.75rem;">
                            {{ $conv->cliente?->nombrecli ?? '—' }}
                            @if($conv->last_message_at)
                                · {{ $conv->last_message_at->diffForHumans() }}
                            @endif
                        </div>
                        @if($conv->last_message_preview)
                            <div class="conv-preview mt-1">{{ $conv->last_message_preview }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>

    {{-- Modal conversación --}}
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
