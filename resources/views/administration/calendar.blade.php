@extends('layouts.static')
@section('title', 'Calendario — Panel')
@section('h1', 'Calendario')
@section('breadcrumb')
    <a href="{{ route('inicio') }}">Inicio</a>
@endsection
@section('breadcrumb2', 'Calendario')
@section('introduccion')
    Visualiza vencimientos, ventas, gastos y estadísticas por día. Activa o desactiva filtros con los botones superiores y haz clic en cualquier día para ver el detalle.
@endsection

@section('styles')
<style>
/* ── Variables ──────────────────────────────────────────── */
:root {
    --cal-clientes:  #0d9488;
    --cal-cuentas:   #f97316;
    --cal-ventas:    #3b82f6;
    --cal-gastos:    #ef4444;
    --cal-costos:    #8b5cf6;
    --cal-stats:     #f59e0b;
    --cal-tareas:    #ec4899;
}

/* ── Filtros ────────────────────────────────────────────── */
.cal-filter-btn {
    border: 2px solid transparent;
    border-radius: 999px;
    padding: 6px 16px;
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--bs-body-bg);
}
.cal-filter-btn .dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.cal-filter-btn.active  { color: #fff; }
.cal-filter-btn.active.f-clientes { background: var(--cal-clientes); border-color: var(--cal-clientes); }
.cal-filter-btn.active.f-cuentas  { background: var(--cal-cuentas);  border-color: var(--cal-cuentas); }
.cal-filter-btn.active.f-ventas   { background: var(--cal-ventas);   border-color: var(--cal-ventas); }
.cal-filter-btn.active.f-gastos   { background: var(--cal-gastos);   border-color: var(--cal-gastos); }
.cal-filter-btn.active.f-costos   { background: var(--cal-costos);   border-color: var(--cal-costos); }
.cal-filter-btn.active.f-stats    { background: var(--cal-stats);    border-color: var(--cal-stats); }
.cal-filter-btn.active.f-tareas   { background: var(--cal-tareas);   border-color: var(--cal-tareas); }

.cal-filter-btn:not(.active) { border-color: #dee2e6; color: var(--bs-body-color); opacity: .65; }
.cal-filter-btn:not(.active) .dot { background: #aaa; }

.cal-filter-btn.active.f-clientes .dot { background: #fff; }
.cal-filter-btn.active.f-cuentas  .dot { background: #fff; }
.cal-filter-btn.active.f-ventas   .dot { background: #fff; }
.cal-filter-btn.active.f-gastos   .dot { background: #fff; }
.cal-filter-btn.active.f-costos   .dot { background: #fff; }
.cal-filter-btn.active.f-stats    .dot { background: #fff; }
.cal-filter-btn.active.f-tareas   .dot { background: #fff; }

.cal-filter-btn:not(.active).f-clientes .dot { background: var(--cal-clientes); }
.cal-filter-btn:not(.active).f-cuentas  .dot { background: var(--cal-cuentas); }
.cal-filter-btn:not(.active).f-ventas   .dot { background: var(--cal-ventas); }
.cal-filter-btn:not(.active).f-gastos   .dot { background: var(--cal-gastos); }
.cal-filter-btn:not(.active).f-costos   .dot { background: var(--cal-costos); }
.cal-filter-btn:not(.active).f-stats    .dot { background: var(--cal-stats); }
.cal-filter-btn:not(.active).f-tareas   .dot { background: var(--cal-tareas); }

/* ── Grid del calendario ─────────────────────────────────── */
.cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--bs-border-color);
}

.cal-weekday {
    background: var(--bs-tertiary-bg);
    padding: 10px 4px;
    text-align: center;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: var(--bs-secondary-color);
    border-bottom: 1px solid var(--bs-border-color);
}

.cal-day {
    min-height: 96px;
    padding: 8px 6px 6px;
    border-right: 1px solid var(--bs-border-color);
    border-bottom: 1px solid var(--bs-border-color);
    position: relative;
    cursor: pointer;
    transition: background .15s;
    background: var(--bs-body-bg);
}
.cal-day:nth-child(7n) { border-right: none; }
.cal-day:hover { background: var(--bs-tertiary-bg); }
.cal-day.other-month { opacity: .35; pointer-events: none; }
.cal-day.today .day-num {
    background: var(--bs-primary);
    color: #fff;
    border-radius: 50%;
    width: 26px; height: 26px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.cal-day.has-events { cursor: pointer; }

.day-num {
    font-size: .82rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 4px;
    display: inline-block;
}

/* ── Burbujas ────────────────────────────────────────────── */
.bubbles {
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
    margin-top: 2px;
}
.bubble {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    font-size: .65rem;
    font-weight: 700;
    min-width: 20px;
    height: 20px;
    padding: 0 5px;
    color: #fff;
    line-height: 1;
}
.bubble.b-clientes { background: var(--cal-clientes); }
.bubble.b-cuentas  { background: var(--cal-cuentas); }
.bubble.b-ventas   { background: var(--cal-ventas); }
.bubble.b-gastos   { background: var(--cal-gastos); }
.bubble.b-costos   { background: var(--cal-costos); }
.bubble.b-stats    { background: var(--cal-stats); color: #000; }
.bubble.b-tareas   { background: var(--cal-tareas); }
.bubble.b-vencido  { background: #dc2626 !important; }

/* ── Leyenda ─────────────────────────────────────────────── */
.cal-legend span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: .72rem;
}
.cal-legend .dot {
    width: 10px; height: 10px;
    border-radius: 50%;
}

/* ── Modal ───────────────────────────────────────────────── */
.modal-header-cal {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
    color: #fff;
}
.detail-section-title {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    padding: 6px 12px;
    border-radius: 6px;
    display: inline-block;
    margin-bottom: 8px;
}
.detail-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 8px;
    margin-bottom: 5px;
    font-size: .82rem;
}
.detail-item:last-child { margin-bottom: 0; }
.detail-badge {
    width: 28px; height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .85rem;
    flex-shrink: 0;
}
.stat-card {
    border-radius: 10px;
    padding: 10px 14px;
    font-size: .82rem;
}
.stat-card .stat-val {
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1;
}
.empty-day {
    text-align: center;
    padding: 32px 16px;
    color: var(--bs-secondary-color);
    font-size: .85rem;
}
</style>
@endsection

@section('content')

{{-- ── Datos del backend ──────────────────────────────────── --}}
@php
    $usuariosVenc = $usuarios->map(fn($u) => [
        'fecha'   => $u->fecha_vencimiento,
        'nombre'  => $u->nombre_cliente,
        'idcli'   => $u->idcli,
        'idcue'   => $u->idcue,
        'perfil'  => $u->perfil,
        'vencido' => $u->fecha_vencimiento && \Carbon\Carbon::parse($u->fecha_vencimiento)->isPast(),
    ])->values();

    $cuentasVenc = collect($cuentas)->map(fn($c) => [
        'fecha'   => $c->fechavencue,
        'idcue'   => $c->idcue,
        'usuario' => $c->usuariocue,
        'vencido' => $c->fechavencue && \Carbon\Carbon::parse($c->fechavencue)->isPast(),
    ])->values();

    $statsJson = $estadisticas->map(fn($s) => [
        'active_users'      => $s->active_users,
        'new_customers'     => $s->new_customers,
        'daily_revenue'     => $s->daily_revenue,
        'daily_cost'        => $s->daily_cost,
        'daily_sales'       => $s->daily_sales,
        'danger_accounts'   => $s->danger_accounts,
        'pending_payments'  => $s->pending_payments,
    ])->toArray();

    $tareasJson = $tareas->map(fn($t) => [
        'id'     => $t->id,
        'nombre' => $t->nombretarea,
        'fecha'  => $t->fechalimit,
    ])->values();
@endphp

<div class="card shadow-sm border-0 rounded-4 p-3 p-md-4">

    {{-- ── Cabecera: navegación de mes + filtros ─────────── --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">

        {{-- Navegación mes --}}
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary rounded-circle" id="prevMonth" title="Mes anterior">
                <i class="bi bi-chevron-left"></i>
            </button>
            <h5 class="mb-0 fw-bold" id="monthTitle" style="min-width:160px;text-align:center;"></h5>
            <button class="btn btn-sm btn-outline-secondary rounded-circle" id="nextMonth" title="Mes siguiente">
                <i class="bi bi-chevron-right"></i>
            </button>
            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 ms-1" id="todayBtn">Hoy</button>
        </div>

        {{-- Filtros --}}
        <div class="d-flex flex-wrap gap-2">
            <button class="cal-filter-btn f-clientes active" data-filter="clientes">
                <span class="dot"></span>Clientes
            </button>
            <button class="cal-filter-btn f-cuentas active" data-filter="cuentas">
                <span class="dot"></span>Cuentas
            </button>
            <button class="cal-filter-btn f-ventas active" data-filter="ventas">
                <span class="dot"></span>Ventas
            </button>
            <button class="cal-filter-btn f-gastos active" data-filter="gastos">
                <span class="dot"></span>Gastos
            </button>
            <button class="cal-filter-btn f-costos active" data-filter="costos">
                <span class="dot"></span>Costos
            </button>
            <button class="cal-filter-btn f-stats active" data-filter="stats">
                <span class="dot"></span>Estadísticas
            </button>
            <button class="cal-filter-btn f-tareas active" data-filter="tareas">
                <span class="dot"></span>Tareas
            </button>
        </div>
    </div>

    {{-- ── Grid del calendario ────────────────────────────── --}}
    <div class="cal-grid" id="calGrid">
        <div class="cal-weekday">Lun</div>
        <div class="cal-weekday">Mar</div>
        <div class="cal-weekday">Mié</div>
        <div class="cal-weekday">Jue</div>
        <div class="cal-weekday">Vie</div>
        <div class="cal-weekday">Sáb</div>
        <div class="cal-weekday">Dom</div>
    </div>

    {{-- ── Leyenda ─────────────────────────────────────────── --}}
    <div class="cal-legend d-flex flex-wrap gap-3 mt-3 px-1">
        <span><span class="dot" style="background:var(--cal-clientes);"></span>Venc. clientes</span>
        <span><span class="dot" style="background:var(--cal-cuentas);"></span>Venc. cuentas</span>
        <span><span class="dot" style="background:var(--cal-ventas);"></span>Ventas</span>
        <span><span class="dot" style="background:var(--cal-gastos);"></span>Gastos</span>
        <span><span class="dot" style="background:var(--cal-costos);"></span>Costos</span>
        <span><span class="dot" style="background:var(--cal-stats);"></span>Estadísticas</span>
        <span><span class="dot" style="background:var(--cal-tareas);"></span>Tareas</span>
        <span><span class="dot" style="background:#dc2626;"></span>Vencido</span>
    </div>
</div>

{{-- ── Modal detalle de día — patrón Alpine igual que cuentas/index ── --}}
<x-modal name="modalDia" maxWidth="lg">
    <div class="modal-header modal-header-cal border-0 py-3 px-4">
        <div>
            <h5 class="modal-title fw-bold mb-0" id="modalDiaTitle">Detalle del día</h5>
            <small style="opacity:.7;" id="modalDiaSubtitle"></small>
        </div>
        <button type="button" class="btn-close btn-close-white" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>
    <div class="modal-body p-4" id="modalDiaBody"></div>
</x-modal>

@endsection

@section('scripts')
<script>
(function () {
    /* ── Datos desde PHP ───────────────────────────────── */
    const DATA = {
        clientes:  @json($usuariosVenc),
        cuentas:   @json($cuentasVenc),
        ventas:    @json($ventas),
        gastos:    @json($gastos),
        costos:    @json($costos),
        stats:     @json($statsJson),
        tareas:    @json($tareasJson),
        nuevos:    @json($clientesNuevos),
    };

    /* ── Estado ────────────────────────────────────────── */
    const today   = new Date();
    let   current = new Date(today.getFullYear(), today.getMonth(), 1);
    const active  = { clientes: true, cuentas: true, ventas: true, gastos: true, costos: true, stats: true, tareas: true };

    /* ── Helpers ────────────────────────────────────────── */
    const fmt  = d => d ? d.substring(0, 10) : null;
    const pad  = n => String(n).padStart(2, '0');
    const ymd  = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
    const MESES = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

    /* ── Índice de eventos por fecha ────────────────────── */
    function buildIndex() {
        const idx = {};
        const add = (date, key, item) => {
            if (!date) return;
            const d = fmt(date);
            if (!d) return;
            if (!idx[d]) idx[d] = { clientes:[], cuentas:[], ventas:[], gastos:[], costos:[], stats:null, tareas:[], nuevos:[] };
            idx[d][key].push(item);
        };

        DATA.clientes.forEach(u => add(u.fecha, 'clientes', u));
        DATA.cuentas .forEach(c => add(c.fecha, 'cuentas',  c));
        DATA.ventas  .forEach(v => add(v.fecha,  'ventas',   v));
        DATA.gastos  .forEach(g => add(g.fecha,  'gastos',   g));
        DATA.costos  .forEach(c => add(c.fecha,  'costos',   c));
        DATA.tareas  .forEach(t => add(t.fecha,  'tareas',   t));
        DATA.nuevos  .forEach(n => add(n.fecha,  'nuevos',   n));

        Object.entries(DATA.stats).forEach(([d, s]) => {
            if (!idx[d]) idx[d] = { clientes:[], cuentas:[], ventas:[], gastos:[], costos:[], stats:null, tareas:[], nuevos:[] };
            idx[d].stats = s;
        });

        return idx;
    }

    /* ── Renderizar mes ─────────────────────────────────── */
    function render() {
        const idx    = buildIndex();
        const year   = current.getFullYear();
        const month  = current.getMonth();
        const first  = new Date(year, month, 1);
        const last   = new Date(year, month + 1, 0);
        const todayS = ymd(today);

        document.getElementById('monthTitle').textContent = `${MESES[month]} ${year}`;

        const grid = document.getElementById('calGrid');
        // Quitar celdas de días anteriores
        [...grid.querySelectorAll('.cal-day')].forEach(el => el.remove());

        // Día de la semana del 1° (0=dom → ajustar a lunes=0)
        let startDow = (first.getDay() + 6) % 7;
        // Días del mes anterior para rellenar
        for (let i = 0; i < startDow; i++) {
            const prev = new Date(year, month, -startDow + i + 1);
            grid.appendChild(makeDay(ymd(prev), prev.getDate(), idx, true));
        }
        // Días del mes
        for (let d = 1; d <= last.getDate(); d++) {
            const date = new Date(year, month, d);
            const ds   = ymd(date);
            grid.appendChild(makeDay(ds, d, idx, false, ds === todayS));
        }
        // Días del mes siguiente para completar filas
        const total   = startDow + last.getDate();
        const remain  = (7 - (total % 7)) % 7;
        for (let i = 1; i <= remain; i++) {
            const next = new Date(year, month + 1, i);
            grid.appendChild(makeDay(ymd(next), i, idx, true));
        }
    }

    function makeDay(dateStr, dayNum, idx, otherMonth, isToday = false) {
        const ev   = idx[dateStr] || {};
        const cell = document.createElement('div');
        cell.className = 'cal-day' + (otherMonth ? ' other-month' : '') + (isToday ? ' today' : '');

        const numEl = document.createElement('div');
        numEl.className = 'day-num';
        numEl.textContent = dayNum;
        cell.appendChild(numEl);

        const bubblesEl = document.createElement('div');
        bubblesEl.className = 'bubbles';

        if (active.clientes && ev.clientes?.length) {
            const hasVenc = ev.clientes.some(u => u.vencido);
            bubblesEl.appendChild(makeBubble(ev.clientes.length, hasVenc ? 'b-vencido' : 'b-clientes', 'Clientes'));
        }
        if (active.cuentas && ev.cuentas?.length) {
            const hasVenc = ev.cuentas.some(c => c.vencido);
            bubblesEl.appendChild(makeBubble(ev.cuentas.length, hasVenc ? 'b-vencido' : 'b-cuentas', 'Cuentas'));
        }
        if (active.ventas && ev.ventas?.length) {
            bubblesEl.appendChild(makeBubble(ev.ventas.length, 'b-ventas', 'Ventas'));
        }
        if (active.gastos && ev.gastos?.length) {
            bubblesEl.appendChild(makeBubble(ev.gastos.length, 'b-gastos', 'Gastos'));
        }
        if (active.costos && ev.costos?.length) {
            bubblesEl.appendChild(makeBubble(ev.costos.length, 'b-costos', 'Costos'));
        }
        if (active.stats && ev.stats) {
            const nc = ev.stats.new_customers ?? 0;
            if (nc > 0) bubblesEl.appendChild(makeBubble(nc, 'b-stats', 'Clientes nuevos'));
        }
        if (active.tareas && ev.tareas?.length) {
            bubblesEl.appendChild(makeBubble(ev.tareas.length, 'b-tareas', 'Tareas'));
        }

        cell.appendChild(bubblesEl);

        const hasData = Object.keys(ev).some(k => k === 'stats' ? ev[k] : ev[k]?.length);
        if (hasData && !otherMonth) {
            cell.classList.add('has-events');
            cell.addEventListener('click', () => openModal(dateStr, ev));
        }
        return cell;
    }

    function makeBubble(count, cls, title) {
        const b = document.createElement('span');
        b.className = `bubble ${cls}`;
        b.textContent = count;
        b.title = `${count} ${title}`;
        return b;
    }

    /* ── Modal ──────────────────────────────────────────── */
    function openModal(dateStr, ev) {
        const parts = dateStr.split('-');
        const label = `${parseInt(parts[2])} de ${MESES[parseInt(parts[1])-1]} de ${parts[0]}`;
        document.getElementById('modalDiaTitle').textContent    = label;
        document.getElementById('modalDiaSubtitle').textContent = resumenDia(ev);
        document.getElementById('modalDiaBody').innerHTML       = buildModalBody(ev);
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'modalDia' }));
    }

    function resumenDia(ev) {
        const parts = [];
        if (ev.clientes?.length) parts.push(`${ev.clientes.length} clientes`);
        if (ev.cuentas?.length)  parts.push(`${ev.cuentas.length} cuentas`);
        if (ev.ventas?.length)   parts.push(`${ev.ventas.length} ventas`);
        if (ev.gastos?.length)   parts.push(`${ev.gastos.length} gastos`);
        if (ev.costos?.length)   parts.push(`${ev.costos.length} costos`);
        if (ev.tareas?.length)   parts.push(`${ev.tareas.length} tareas`);
        return parts.join(' · ') || 'Sin eventos';
    }

    function money(n) {
        return '$' + parseFloat(n || 0).toLocaleString('es-EC', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function buildModalBody(ev) {
        let html = '';

        /* ── Estadísticas ─── */
        if (active.stats && ev.stats) {
            const s = ev.stats;
            html += `
            <div class="mb-4">
                <div class="detail-section-title" style="background:#fef3c7;color:#92400e;">
                    <i class="bi bi-bar-chart-fill me-1"></i> Estadísticas del día
                </div>
                <div class="row g-2">
                    ${statCard('bi-people-fill','#fef3c7','#92400e','Usuarios activos', s.active_users ?? '—')}
                    ${statCard('bi-person-plus-fill','#d1fae5','#065f46','Clientes nuevos', s.new_customers ?? '—')}
                    ${statCard('bi-cash-stack','#dbeafe','#1e40af','Ingresos', money(s.daily_revenue))}
                    ${statCard('bi-receipt','#ede9fe','#4c1d95','Costos', money(s.daily_cost))}
                    ${statCard('bi-bag-check-fill','#fce7f3','#831843','Ventas', s.daily_sales ?? '—')}
                    ${statCard('bi-exclamation-triangle-fill','#fee2e2','#7f1d1d','Cuentas riesgo', s.danger_accounts ?? '—')}
                </div>
            </div>`;
        }

        /* ── Clientes nuevos del día ─── */
        if (active.clientes && ev.nuevos?.length) {
            html += sectionHtml(
                'bi-person-plus','#ccfbf1','#065f46','Clientes nuevos registrados',
                ev.nuevos.map(n => `
                    <div class="detail-item" style="background:#f0fdf4;">
                        <div class="detail-badge" style="background:#dcfce7;color:#15803d;"><i class="bi bi-person-check-fill"></i></div>
                        <div><div class="fw-semibold">${esc(n.nombre)}</div><small class="text-muted">ID: ${n.id}</small></div>
                    </div>`).join('')
            );
        }

        /* ── Vencimientos de clientes ─── */
        if (active.clientes && ev.clientes?.length) {
            html += sectionHtml(
                'bi-people','#ccfbf1','#0f766e','Vencimientos de usuarios',
                ev.clientes.map(u => `
                    <div class="detail-item" style="background:${u.vencido?'#fef2f2':'#f0fdf4'};">
                        <div class="detail-badge" style="background:${u.vencido?'#fee2e2':'#dcfce7'};color:${u.vencido?'#dc2626':'#15803d'};">
                            <i class="bi bi-${u.vencido?'exclamation-triangle-fill':'check-circle-fill'}"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">${esc(u.nombre)}</div>
                            <small class="text-muted">Cuenta: ${u.idcue} · Perfil: ${esc(u.perfil||'—')}</small>
                        </div>
                        ${u.vencido ? '<span class="badge bg-danger ms-auto">VENCIDO</span>' : ''}
                    </div>`).join('')
            );
        }

        /* ── Vencimientos de cuentas ─── */
        if (active.cuentas && ev.cuentas?.length) {
            html += sectionHtml(
                'bi-sim','#ffedd5','#c2410c','Vencimientos de cuentas',
                ev.cuentas.map(c => `
                    <div class="detail-item" style="background:${c.vencido?'#fef2f2':'#fff7ed'};">
                        <div class="detail-badge" style="background:${c.vencido?'#fee2e2':'#fed7aa'};color:${c.vencido?'#dc2626':'#c2410c'};">
                            <i class="bi bi-${c.vencido?'exclamation-triangle-fill':'sim-fill'}"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">Cuenta #${c.idcue}</div>
                            <small class="text-muted">Usuario: ${esc(c.usuario||'—')}</small>
                        </div>
                        ${c.vencido ? '<span class="badge bg-danger ms-auto">VENCIDA</span>' : ''}
                    </div>`).join('')
            );
        }

        /* ── Ventas ─── */
        if (active.ventas && ev.ventas?.length) {
            const total = ev.ventas.reduce((s, v) => s + (v.monto||0), 0);
            html += sectionHtml(
                'bi-bag-check','#dbeafe','#1e40af',`Ventas — Total: ${money(total)}`,
                ev.ventas.map(v => `
                    <div class="detail-item" style="background:#eff6ff;">
                        <div class="detail-badge" style="background:#dbeafe;color:#1d4ed8;"><i class="bi bi-bag-fill"></i></div>
                        <div>
                            <div class="fw-semibold">${money(v.monto)}</div>
                            <small class="text-muted">Cliente: ${esc(v.cliente)} · #${esc(v.id)}</small>
                        </div>
                    </div>`).join('')
            );
        }

        /* ── Gastos ─── */
        if (active.gastos && ev.gastos?.length) {
            const total = ev.gastos.reduce((s, g) => s + (g.monto||0), 0);
            html += sectionHtml(
                'bi-receipt','#fee2e2','#991b1b',`Gastos — Total: ${money(total)}`,
                ev.gastos.map(g => `
                    <div class="detail-item" style="background:#fef2f2;">
                        <div class="detail-badge" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-receipt-cutoff"></i></div>
                        <div>
                            <div class="fw-semibold">${money(g.monto)} <span class="text-muted fw-normal">· ${esc(g.tipo)}</span></div>
                            <small class="text-muted">${esc(g.descripcion||'Sin descripción')}</small>
                        </div>
                    </div>`).join('')
            );
        }

        /* ── Costos ─── */
        if (active.costos && ev.costos?.length) {
            const total = ev.costos.reduce((s, c) => s + (c.monto||0), 0);
            html += sectionHtml(
                'bi-currency-dollar','#ede9fe','#4c1d95',`Costos — Total: ${money(total)}`,
                ev.costos.map(c => `
                    <div class="detail-item" style="background:#f5f3ff;">
                        <div class="detail-badge" style="background:#ede9fe;color:#7c3aed;"><i class="bi bi-arrow-down-right-circle-fill"></i></div>
                        <div>
                            <div class="fw-semibold">${money(c.monto)}</div>
                            <small class="text-muted">Cuenta: ${c.cuenta} · ${esc(c.usuario||'—')} · ${esc(c.servicio||'—')}</small>
                        </div>
                    </div>`).join('')
            );
        }

        /* ── Tareas ─── */
        if (active.tareas && ev.tareas?.length) {
            html += sectionHtml(
                'bi-check2-square','#fce7f3','#831843','Tareas pendientes',
                ev.tareas.map(t => `
                    <div class="detail-item" style="background:#fdf4ff;">
                        <div class="detail-badge" style="background:#fce7f3;color:#be185d;"><i class="bi bi-list-task"></i></div>
                        <div><div class="fw-semibold">${esc(t.nombre)}</div></div>
                        <a href="/admin/tareas/${t.id}" class="btn btn-sm btn-outline-secondary ms-auto">Ver</a>
                    </div>`).join('')
            );
        }

        if (!html) {
            html = `<div class="empty-day"><i class="bi bi-calendar-x fs-2 d-block mb-2 opacity-50"></i>No hay eventos registrados para este día.</div>`;
        }

        return html;
    }

    function sectionHtml(icon, bg, color, title, items) {
        return `
        <div class="mb-4">
            <div class="detail-section-title" style="background:${bg};color:${color};">
                <i class="bi ${icon} me-1"></i>${esc(title)}
            </div>
            ${items}
        </div>`;
    }

    function statCard(icon, bg, color, label, val) {
        return `
        <div class="col-6 col-sm-4">
            <div class="stat-card" style="background:${bg};">
                <div class="stat-val" style="color:${color};">${val}</div>
                <div class="small text-muted"><i class="bi ${icon} me-1" style="color:${color};"></i>${label}</div>
            </div>
        </div>`;
    }

    function esc(s) {
        if (s == null) return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ── Controles de navegación ─────────────────────────── */
    document.getElementById('prevMonth').addEventListener('click', () => {
        current.setMonth(current.getMonth() - 1);
        render();
    });
    document.getElementById('nextMonth').addEventListener('click', () => {
        current.setMonth(current.getMonth() + 1);
        render();
    });
    document.getElementById('todayBtn').addEventListener('click', () => {
        current = new Date(today.getFullYear(), today.getMonth(), 1);
        render();
    });

    /* ── Filtros ─────────────────────────────────────────── */
    document.querySelectorAll('.cal-filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const f = btn.dataset.filter;
            active[f] = !active[f];
            btn.classList.toggle('active', active[f]);
            render();
        });
    });

    /* ── Arranque ────────────────────────────────────────── */
    render();
})();
</script>
@endsection
