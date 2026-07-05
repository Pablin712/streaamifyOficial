@extends('layouts.static')
@section('title', 'Calendario — Panel')
@section('h1', 'Calendario')
@section('breadcrumb')
    <a href="{{ route('inicio') }}">Inicio</a>
@endsection
@section('breadcrumb2', 'Calendario')
@section('introduccion')
    @if($isLocked)
        Visualiza los vencimientos y tareas asignadas a ti. Haz clic en cualquier día para ver el detalle.
    @else
        Visualiza vencimientos, ventas, gastos y estadísticas por día. Activa o desactiva filtros con los botones superiores y haz clic en cualquier día para ver el detalle.
    @endif
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
    --h-prog:        #fbbf24;
    --h-conf:        #22c55e;
    --h-ause:        #ef4444;
    --h-extr:        #3b82f6;
}

/* ── Tabs ────────────────────────────────────────────────── */
.cal-tab-nav { border-bottom: 2px solid var(--bs-border-color); margin-bottom: 1.5rem; }
.cal-tab-btn {
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 10px 20px;
    font-weight: 600;
    font-size: .88rem;
    color: var(--bs-secondary-color);
    margin-bottom: -2px;
    cursor: pointer;
    transition: all .2s;
    border-radius: 0;
}
.cal-tab-btn.active { border-bottom-color: var(--bs-primary); color: var(--bs-primary); }
.cal-tab-btn:hover:not(.active) { color: var(--bs-body-color); }

/* ── Filtros (tab 1) ─────────────────────────────────────── */
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
.cal-filter-btn .dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
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
.cal-filter-btn.active.f-clientes .dot,
.cal-filter-btn.active.f-cuentas  .dot,
.cal-filter-btn.active.f-ventas   .dot,
.cal-filter-btn.active.f-gastos   .dot,
.cal-filter-btn.active.f-costos   .dot,
.cal-filter-btn.active.f-stats    .dot,
.cal-filter-btn.active.f-tareas   .dot { background: #fff; }
.cal-filter-btn:not(.active).f-clientes .dot { background: var(--cal-clientes); }
.cal-filter-btn:not(.active).f-cuentas  .dot { background: var(--cal-cuentas); }
.cal-filter-btn:not(.active).f-ventas   .dot { background: var(--cal-ventas); }
.cal-filter-btn:not(.active).f-gastos   .dot { background: var(--cal-gastos); }
.cal-filter-btn:not(.active).f-costos   .dot { background: var(--cal-costos); }
.cal-filter-btn:not(.active).f-stats    .dot { background: var(--cal-stats); }
.cal-filter-btn:not(.active).f-tareas   .dot { background: var(--cal-tareas); }

/* ── Grid mensual (tab 1) ───────────────────────────────── */
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
.day-num { font-size: .82rem; font-weight: 700; line-height: 1; margin-bottom: 4px; display: inline-block; }
.bubbles { display: flex; flex-wrap: wrap; gap: 3px; margin-top: 2px; }
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

/* ── Leyenda (tab 1) ─────────────────────────────────────── */
.cal-legend span { display: inline-flex; align-items: center; gap: 4px; font-size: .72rem; }
.cal-legend .dot { width: 10px; height: 10px; border-radius: 50%; }

/* ── Modal detalle (tab 1) ───────────────────────────────── */
.modal-header-cal { background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); color: #fff; }
.detail-section-title {
    font-size: .7rem; font-weight: 700; letter-spacing: .08em;
    text-transform: uppercase; padding: 6px 12px; border-radius: 6px;
    display: inline-block; margin-bottom: 8px;
}
.detail-item {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 8px 10px; border-radius: 8px; margin-bottom: 5px; font-size: .82rem;
}
.detail-item:last-child { margin-bottom: 0; }
.detail-badge {
    width: 28px; height: 28px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; flex-shrink: 0;
}
.stat-card { border-radius: 10px; padding: 10px 14px; font-size: .82rem; }
.stat-card .stat-val { font-size: 1.5rem; font-weight: 800; line-height: 1; }
.empty-day { text-align: center; padding: 32px 16px; color: var(--bs-secondary-color); font-size: .85rem; }

/* ── Grid semanal — línea de tiempo por horas (tab 2) ───── */
:root {
    --hg-row-h: 36px;
    /* Paleta categórica por empleado — 8 colores fijos, no cíclicos entre sí.
       Fondos SÓLIDOS (no transparentes): las franjas van en capa superior al
       mapa de calor y no deben mezclarse ópticamente con él. */
    --emp-1-border:#2a78d6; --emp-1-bg:#d2e2f4;
    --emp-2-border:#1baf7a; --emp-2-bg:#cfede1;
    --emp-3-border:#eda100; --emp-3-bg:#f9eac9;
    --emp-4-border:#008300; --emp-4-bg:#cae4c9;
    --emp-5-border:#4a3aa7; --emp-5-bg:#d8d5ea;
    --emp-6-border:#e34948; --emp-6-bg:#f7d8d7;
    --emp-7-border:#e87ba4; --emp-7-bg:#f8e2ea;
    --emp-8-border:#eb6834; --emp-8-bg:#f9ded3;
}
[data-bs-theme="dark"] {
    --emp-1-border:#3987e5; --emp-1-bg:#243d5a;
    --emp-2-border:#199e70; --emp-2-bg:#1a4435;
    --emp-3-border:#c98500; --emp-3-bg:#523c11;
    --emp-4-border:#008300; --emp-4-bg:#123c11;
    --emp-5-border:#9085e9; --emp-5-bg:#403c5c;
    --emp-6-border:#e66767; --emp-6-bg:#5b3332;
    --emp-7-border:#d55181; --emp-7-bg:#562c3a;
    --emp-8-border:#d95926; --emp-8-bg:#572e1d;
}
.hg-outer { border: 1px solid var(--bs-border-color); border-radius: 12px; overflow: hidden; }
.hg-hscroll { overflow-x: auto; }
.hg-header, .hg-allday, .hg-body { min-width: 830px; }
.hg-header {
    display: grid;
    grid-template-columns: 52px repeat(7, minmax(110px, 1fr));
    border-bottom: 1px solid var(--bs-border-color);
    background: var(--bs-tertiary-bg);
}
.hg-corner { border-right: 1px solid var(--bs-border-color); }
.hg-day-head {
    padding: 8px 6px;
    text-align: center;
    border-right: 1px solid var(--bs-border-color);
    transition: background .3s;
    position: relative;
}
.hg-day-head:last-child { border-right: none; }
.hg-day-head.hg-today { box-shadow: inset 0 -3px 0 var(--bs-primary); }
.hg-day-head.hg-today .hg-daynum { color: var(--bs-primary); }
.hg-weekday { font-size: .63rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--bs-secondary-color); }
.hg-daynum { font-size: 1.15rem; font-weight: 800; line-height: 1.15; }
.hg-month { font-size: .6rem; color: var(--bs-secondary-color); }
.hg-libre-badge {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: .56rem; font-weight: 700; letter-spacing: .04em;
    color: #059669; background: rgba(16,185,129,.15);
    border-radius: 999px; padding: 1px 6px; margin-top: 2px;
}
.hg-add-btn {
    position: absolute; top: 4px; right: 4px;
    border: none; background: transparent; color: var(--bs-secondary-color);
    opacity: .5; cursor: pointer; font-size: .8rem; padding: 2px 4px; border-radius: 4px;
}
.hg-add-btn:hover { opacity: 1; background: var(--bs-tertiary-bg); color: var(--bs-primary); }

/* Fila "todo el día" */
.hg-allday {
    display: grid;
    grid-template-columns: 52px repeat(7, minmax(110px, 1fr));
    border-bottom: 2px solid var(--bs-border-color);
    background: var(--bs-body-bg);
}
.hg-allday-label {
    font-size: .6rem; color: var(--bs-secondary-color); font-weight: 700;
    display: flex; align-items: center; justify-content: center; text-align: center;
    border-right: 1px solid var(--bs-border-color); padding: 4px 2px;
}
.hg-allday-cell {
    border-right: 1px solid var(--bs-border-color);
    padding: 4px; min-height: 30px; display: flex; flex-direction: column; gap: 3px;
}
.hg-allday-cell:last-child { border-right: none; }
.hg-allday-item {
    border-radius: 6px; padding: 3px 6px; font-size: .68rem; font-weight: 700;
    cursor: pointer; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* Cuerpo con scroll y eje de horas */
.hg-scroll { max-height: 560px; overflow-y: auto; position: relative; }
.hg-body {
    display: grid;
    grid-template-columns: 52px repeat(7, minmax(110px, 1fr));
    position: relative;
}
.hg-gutter { position: relative; border-right: 1px solid var(--bs-border-color); }
.hg-hour-label {
    position: absolute; left: 0; right: 4px; text-align: right;
    font-size: .62rem; color: var(--bs-secondary-color); transform: translateY(-50%);
}
.hg-day-col {
    position: relative;
    border-right: 1px solid var(--bs-border-color);
    background-image: repeating-linear-gradient(
        to bottom,
        var(--bs-border-color) 0, var(--bs-border-color) 1px,
        transparent 1px, transparent var(--hg-row-h)
    );
}
.hg-day-col:last-child { border-right: none; }
.hg-day-col.hg-today-col { background-color: rgba(13,110,253,.035); }
/* Capas del día: mapa de calor al fondo (z=1) < línea "ahora" (z=5) < franjas de horario (z=10) */
.hg-heat { position: absolute; left: 0; right: 0; z-index: 1; pointer-events: none; }
.hg-now-line {
    position: absolute; left: 0; right: 0; height: 0; border-top: 2px solid #ef4444; z-index: 5; pointer-events: none;
}
.hg-now-line::before {
    content: ''; position: absolute; left: -4px; top: -4px; width: 8px; height: 8px;
    border-radius: 50%; background: #ef4444;
}
.hg-block {
    position: absolute; z-index: 10; border-radius: 6px; font-size: .68rem; line-height: 1.15;
    padding: 3px 5px; overflow: hidden; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,.25);
    border-left: 3px solid transparent; transition: filter .15s;
    color: var(--bs-body-color);
}
.hg-block:hover { filter: brightness(0.96); }
.hg-block-name { font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.hg-block-time { opacity: .78; font-size: .62rem; }
/* Punto de estado (Programado/Confirmado/Ausente/Extra), independiente del color por empleado */
.hg-block-dot, .emp-pill-dot {
    display: inline-block; width: 7px; height: 7px; border-radius: 50%;
    margin-right: 4px; flex-shrink: 0; vertical-align: middle;
}

/* Leyenda mapa de calor / filtros */
.hm-legend { display: flex; align-items: center; gap: 4px; font-size: .7rem; color: var(--bs-secondary-color); }
.hm-swatch { width: 14px; height: 14px; border-radius: 3px; }
/* Employee filter */
.emp-pill { transition: all .15s; }
.emp-pill.active { box-shadow: 0 0 0 2px var(--bs-primary); }
/* Modal agendar / detalle */
.modal-header-hor { background: linear-gradient(135deg, #4c1d95 0%, #6366f1 100%); color: #fff; }
.hd-row { display: flex; align-items: flex-start; gap: 10px; padding: 8px 0; border-bottom: 1px dashed var(--bs-border-color); }
.hd-row:last-child { border-bottom: none; }
.hd-row i { width: 20px; text-align: center; color: var(--bs-secondary-color); margin-top: 2px; }
.hd-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .05em; color: var(--bs-secondary-color); font-weight: 700; }
.hd-value { font-size: .88rem; font-weight: 600; }
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

    {{-- ── Tabs ────────────────────────────────────────────── --}}
    <div class="cal-tab-nav d-flex gap-1">
        <button class="cal-tab-btn active" data-target="tab-cal">
            <i class="bi bi-calendar3 me-1"></i>Calendario
        </button>
        <button class="cal-tab-btn" data-target="tab-hor">
            <i class="bi bi-calendar2-week me-1"></i>Horarios
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- TAB 1 — Calendario mensual                           --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div id="tab-cal">

        {{-- Cabecera: navegación + filtros --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
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
            <div class="d-flex flex-wrap gap-2">
                <button class="cal-filter-btn f-clientes active" data-filter="clientes">
                    <span class="dot"></span>Clientes
                </button>
                <button class="cal-filter-btn f-cuentas active" data-filter="cuentas">
                    <span class="dot"></span>Cuentas
                </button>
                @if(!$isLocked)
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
                @endif
                <button class="cal-filter-btn f-tareas active" data-filter="tareas">
                    <span class="dot"></span>Tareas
                </button>
            </div>
        </div>

        {{-- Grid mensual --}}
        <div class="cal-grid" id="calGrid">
            <div class="cal-weekday">Lun</div>
            <div class="cal-weekday">Mar</div>
            <div class="cal-weekday">Mié</div>
            <div class="cal-weekday">Jue</div>
            <div class="cal-weekday">Vie</div>
            <div class="cal-weekday">Sáb</div>
            <div class="cal-weekday">Dom</div>
        </div>

        {{-- Leyenda --}}
        <div class="cal-legend d-flex flex-wrap gap-3 mt-3 px-1">
            <span><span class="dot" style="background:var(--cal-clientes);"></span>Venc. clientes</span>
            <span><span class="dot" style="background:var(--cal-cuentas);"></span>Venc. cuentas</span>
            @if(!$isLocked)
            <span><span class="dot" style="background:var(--cal-ventas);"></span>Ventas</span>
            <span><span class="dot" style="background:var(--cal-gastos);"></span>Gastos</span>
            <span><span class="dot" style="background:var(--cal-costos);"></span>Costos</span>
            <span><span class="dot" style="background:var(--cal-stats);"></span>Estadísticas</span>
            @endif
            <span><span class="dot" style="background:var(--cal-tareas);"></span>Tareas</span>
            <span><span class="dot" style="background:#dc2626;"></span>Vencido</span>
        </div>
    </div>{{-- /tab-cal --}}

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- TAB 2 — Horarios semanal                             --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div id="tab-hor" style="display:none;">

        {{-- Navegación semanal --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary rounded-circle" id="prevWeek">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <h5 class="mb-0 fw-bold" id="weekTitle" style="min-width:260px;text-align:center;"></h5>
                <button class="btn btn-sm btn-outline-secondary rounded-circle" id="nextWeek">
                    <i class="bi bi-chevron-right"></i>
                </button>
                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 ms-1" id="thisWeekBtn">Esta semana</button>
            </div>
            {{-- Leyenda estados (el punto de color dentro de cada bloque) --}}
            <div class="d-flex flex-wrap gap-3" style="font-size:.72rem;">
                <span class="d-flex align-items-center gap-1">
                    <span style="width:10px;height:10px;border-radius:50%;background:var(--h-prog);display:inline-block;"></span>Programado
                </span>
                <span class="d-flex align-items-center gap-1">
                    <span style="width:10px;height:10px;border-radius:50%;background:var(--h-conf);display:inline-block;"></span>Confirmado
                </span>
                <span class="d-flex align-items-center gap-1">
                    <span style="width:10px;height:10px;border-radius:50%;background:var(--h-ause);display:inline-block;"></span>Ausente
                </span>
                <span class="d-flex align-items-center gap-1">
                    <span style="width:10px;height:10px;border-radius:50%;background:var(--h-extr);display:inline-block;"></span>Extra
                </span>
            </div>
        </div>

        {{-- Filtro por empleado (solo admin/gerente) — el color de cada pill es el mismo
             color de fondo con el que se pintan sus franjas en la línea de tiempo --}}
        @if($isAdmin)
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3 p-3 rounded-3" style="background:var(--bs-tertiary-bg);">
            <span class="small fw-semibold" style="color:var(--bs-secondary-color);">
                <i class="bi bi-people me-1"></i>Ver empleado:
            </span>
            <button class="btn btn-sm btn-outline-secondary rounded-pill emp-pill active" data-wid="all">Todos</button>
            @foreach($empleadosActivos as $emp)
            @php $empSlot = ($loop->index % 8) + 1; @endphp
            <button class="btn btn-sm btn-outline-secondary rounded-pill emp-pill" data-wid="{{ $emp['id'] }}">
                <span class="emp-pill-dot" style="background:var(--emp-{{ $empSlot }}-border);"></span>{{ $emp['nombre'] }}
            </button>
            @endforeach
        </div>
        <div class="small text-muted mb-2 px-1">
            <i class="bi bi-info-circle me-1"></i>El color de fondo de cada franja identifica al empleado (mismo color que su filtro); el punto pequeño indica el estado.
        </div>
        @endif

        {{-- Grid semanal — línea de tiempo por horas --}}
        <div class="hg-outer">
            <div class="hg-hscroll">
                <div class="hg-header" id="hgHeader"></div>
                <div class="hg-allday" id="hgAllday"></div>
                <div class="hg-scroll" id="hgScroll">
                    <div class="hg-body" id="hgBody"></div>
                </div>
            </div>
        </div>

        {{-- Leyenda mapa de calor --}}
        @if($isAdmin)
        <div class="d-flex align-items-center gap-3 mt-3 px-1 flex-wrap">
            <span class="small text-muted fw-semibold"><i class="bi bi-thermometer-half me-1"></i>Mapa de calor (empleados simultáneos por hora):</span>
            <div class="hm-legend">
                <span class="hm-swatch" style="background:rgba(34,197,94,.10);border:1px solid #ddd;"></span> 0
            </div>
            <div class="hm-legend">
                <span class="hm-swatch" style="background:rgba(34,197,94,.30);"></span> 1
            </div>
            <div class="hm-legend">
                <span class="hm-swatch" style="background:rgba(34,197,94,.55);"></span> 2–3
            </div>
            <div class="hm-legend">
                <span class="hm-swatch" style="background:rgba(34,197,94,.85);"></span> 4+
            </div>
            <span class="small text-muted ms-1">Las zonas sin color = ningún empleado trabajando (hueco).</span>
        </div>
        @endif
    </div>{{-- /tab-hor --}}

</div>{{-- /card --}}

{{-- ── Modal: detalle de día (tab 1) ──────────────────────── --}}
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

{{-- ── Modal: agendar horario (tab 2) ─────────────────────── --}}
<x-modal name="modalAgendar" maxWidth="sm">
    <div class="modal-header modal-header-hor border-0 py-3 px-4">
        <div>
            <h5 class="modal-title fw-bold mb-0"><i class="bi bi-calendar-plus me-2"></i>Agendar horario</h5>
            <small id="modalAgendarFecha" style="opacity:.7;"></small>
        </div>
        <button type="button" class="btn-close btn-close-white" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>
    <div class="modal-body p-4" id="modalAgendarBody"></div>
</x-modal>

{{-- ── Modal: detalle de un horario (tab 2) ───────────────── --}}
<x-modal name="modalHorDetalle" maxWidth="sm">
    <div class="modal-header modal-header-hor border-0 py-3 px-4">
        <div>
            <h5 class="modal-title fw-bold mb-0" id="modalHorDetTitle"><i class="bi bi-person-badge me-2"></i>Detalle del horario</h5>
            <small id="modalHorDetFecha" style="opacity:.7;"></small>
        </div>
        <button type="button" class="btn-close btn-close-white" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>
    <div class="modal-body p-4" id="modalHorDetBody"></div>
</x-modal>

@endsection

@section('scripts')
<script>
(function () {
    /* ══════════════════════════════════════════════════════════
       Datos desde PHP
    ══════════════════════════════════════════════════════════ */
    const DATA = {
        clientes:  @json($usuariosVenc),
        cuentas:   @json($cuentasVenc),
        ventas:    @json($ventas),
        gastos:    @json($gastos),
        costos:    @json($costos),
        stats:     @json($statsJson),
        tareas:    @json($tareasJson),
        nuevos:    @json($clientesNuevos),
        horarios:  @json($horariosData),
        extras:    @json($extrasData),
        empleados: @json($empleadosActivos),
        isAdmin:   @json($isAdmin),
    };

    /* ══════════════════════════════════════════════════════════
       Helpers comunes
    ══════════════════════════════════════════════════════════ */
    const fmt  = d => d ? d.substring(0, 10) : null;
    const pad  = n => String(n).padStart(2, '0');
    const ymd  = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
    const MESES       = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    const MESES_SHORT = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const DIAS_SHORT  = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];

    function esc(s) {
        if (s == null) return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function money(n) {
        return '$' + parseFloat(n || 0).toLocaleString('es-EC', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    /* ══════════════════════════════════════════════════════════
       TABS
    ══════════════════════════════════════════════════════════ */
    document.querySelectorAll('.cal-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.cal-tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const target = btn.dataset.target;
            document.getElementById('tab-cal').style.display = target === 'tab-cal' ? '' : 'none';
            document.getElementById('tab-hor').style.display = target === 'tab-hor' ? '' : 'none';
            if (target === 'tab-hor') renderWeek();
        });
    });

    /* ══════════════════════════════════════════════════════════
       TAB 1 — CALENDARIO MENSUAL
    ══════════════════════════════════════════════════════════ */
    const today   = new Date();
    let   current = new Date(today.getFullYear(), today.getMonth(), 1);
    const active  = { clientes: true, cuentas: true, ventas: true, gastos: true, costos: true, stats: true, tareas: true };

    function buildIndex() {
        const empty = () => ({ clientes:[], cuentas:[], ventas:[], gastos:[], costos:[], stats:null, tareas:[], nuevos:[] });
        const idx = {};
        const add = (date, key, item) => {
            if (!date) return;
            const d = fmt(date);
            if (!d) return;
            if (!idx[d]) idx[d] = empty();
            idx[d][key].push(item);
        };

        DATA.clientes.forEach(u => add(u.fecha, 'clientes', u));
        DATA.cuentas .forEach(c => add(c.fecha, 'cuentas',  c));
        DATA.ventas  .forEach(v => add(v.fecha, 'ventas',   v));
        DATA.gastos  .forEach(g => add(g.fecha, 'gastos',   g));
        DATA.costos  .forEach(c => add(c.fecha, 'costos',   c));
        DATA.tareas  .forEach(t => add(t.fecha, 'tareas',   t));
        DATA.nuevos  .forEach(n => add(n.fecha, 'nuevos',   n));

        Object.entries(DATA.stats).forEach(([d, s]) => {
            if (!idx[d]) idx[d] = empty();
            idx[d].stats = s;
        });

        return idx;
    }

    function render() {
        const idx    = buildIndex();
        const year   = current.getFullYear();
        const month  = current.getMonth();
        const first  = new Date(year, month, 1);
        const last   = new Date(year, month + 1, 0);
        const todayS = ymd(today);

        document.getElementById('monthTitle').textContent = `${MESES[month]} ${year}`;

        const grid = document.getElementById('calGrid');
        [...grid.querySelectorAll('.cal-day')].forEach(el => el.remove());

        let startDow = (first.getDay() + 6) % 7;
        for (let i = 0; i < startDow; i++) {
            const prev = new Date(year, month, -startDow + i + 1);
            grid.appendChild(makeDay(ymd(prev), prev.getDate(), idx, true));
        }
        for (let d = 1; d <= last.getDate(); d++) {
            const date = new Date(year, month, d);
            const ds   = ymd(date);
            grid.appendChild(makeDay(ds, d, idx, false, ds === todayS));
        }
        const total  = startDow + last.getDate();
        const remain = (7 - (total % 7)) % 7;
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

    /* ── Modal día (tab 1) ─────────────────────────────────── */
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

    function buildModalBody(ev) {
        let html = '';

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

        if (active.clientes && ev.nuevos?.length) {
            html += sectionHtml('bi-person-plus','#ccfbf1','#065f46','Clientes nuevos registrados',
                ev.nuevos.map(n => `
                    <div class="detail-item" style="background:#f0fdf4;">
                        <div class="detail-badge" style="background:#dcfce7;color:#15803d;"><i class="bi bi-person-check-fill"></i></div>
                        <div><div class="fw-semibold">${esc(n.nombre)}</div><small class="text-muted">ID: ${n.id}</small></div>
                    </div>`).join(''));
        }

        if (active.clientes && ev.clientes?.length) {
            html += sectionHtml('bi-people','#ccfbf1','#0f766e','Vencimientos de usuarios',
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
                    </div>`).join(''));
        }

        if (active.cuentas && ev.cuentas?.length) {
            html += sectionHtml('bi-sim','#ffedd5','#c2410c','Vencimientos de cuentas',
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
                    </div>`).join(''));
        }

        if (active.ventas && ev.ventas?.length) {
            const total = ev.ventas.reduce((s, v) => s + (v.monto||0), 0);
            html += sectionHtml('bi-bag-check','#dbeafe','#1e40af',`Ventas — Total: ${money(total)}`,
                ev.ventas.map(v => `
                    <div class="detail-item" style="background:#eff6ff;">
                        <div class="detail-badge" style="background:#dbeafe;color:#1d4ed8;"><i class="bi bi-bag-fill"></i></div>
                        <div>
                            <div class="fw-semibold">${money(v.monto)}</div>
                            <small class="text-muted">Cliente: ${esc(v.cliente)} · #${esc(v.id)}</small>
                        </div>
                    </div>`).join(''));
        }

        if (active.gastos && ev.gastos?.length) {
            const total = ev.gastos.reduce((s, g) => s + (g.monto||0), 0);
            html += sectionHtml('bi-receipt','#fee2e2','#991b1b',`Gastos — Total: ${money(total)}`,
                ev.gastos.map(g => `
                    <div class="detail-item" style="background:#fef2f2;">
                        <div class="detail-badge" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-receipt-cutoff"></i></div>
                        <div>
                            <div class="fw-semibold">${money(g.monto)} <span class="text-muted fw-normal">· ${esc(g.tipo)}</span></div>
                            <small class="text-muted">${esc(g.descripcion||'Sin descripción')}</small>
                        </div>
                    </div>`).join(''));
        }

        if (active.costos && ev.costos?.length) {
            const total = ev.costos.reduce((s, c) => s + (c.monto||0), 0);
            html += sectionHtml('bi-currency-dollar','#ede9fe','#4c1d95',`Costos — Total: ${money(total)}`,
                ev.costos.map(c => `
                    <div class="detail-item" style="background:#f5f3ff;">
                        <div class="detail-badge" style="background:#ede9fe;color:#7c3aed;"><i class="bi bi-arrow-down-right-circle-fill"></i></div>
                        <div>
                            <div class="fw-semibold">${money(c.monto)}</div>
                            <small class="text-muted">Cuenta: ${c.cuenta} · ${esc(c.usuario||'—')} · ${esc(c.servicio||'—')}</small>
                        </div>
                    </div>`).join(''));
        }

        if (active.tareas && ev.tareas?.length) {
            html += sectionHtml('bi-check2-square','#fce7f3','#831843','Tareas pendientes',
                ev.tareas.map(t => `
                    <div class="detail-item" style="background:#fdf4ff;">
                        <div class="detail-badge" style="background:#fce7f3;color:#be185d;"><i class="bi bi-list-task"></i></div>
                        <div><div class="fw-semibold">${esc(t.nombre)}</div></div>
                        <a href="/admin/tareas/${t.id}" class="btn btn-sm btn-outline-secondary ms-auto">Ver</a>
                    </div>`).join(''));
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

    /* ── Controles tab 1 ───────────────────────────────────── */
    document.getElementById('prevMonth').addEventListener('click', () => { current.setMonth(current.getMonth()-1); render(); });
    document.getElementById('nextMonth').addEventListener('click', () => { current.setMonth(current.getMonth()+1); render(); });
    document.getElementById('todayBtn').addEventListener('click', () => { current = new Date(today.getFullYear(), today.getMonth(), 1); render(); });
    document.querySelectorAll('.cal-filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const f = btn.dataset.filter;
            active[f] = !active[f];
            btn.classList.toggle('active', active[f]);
            render();
        });
    });

    render();

    /* ══════════════════════════════════════════════════════════
       TAB 2 — HORARIOS SEMANAL (línea de tiempo por horas)
    ══════════════════════════════════════════════════════════ */
    const ESTADO_CFG = {
        programado: { label:'Programado', bg:'#fef3c7', color:'#92400e', badge:'#d97706' },
        confirmado: { label:'Confirmado',  bg:'#dcfce7', color:'#15803d', badge:'#16a34a' },
        ausente:    { label:'Ausente',     bg:'#fee2e2', color:'#dc2626', badge:'#dc2626' },
        extra:      { label:'Extra',       bg:'#dbeafe', color:'#1d4ed8', badge:'#2563eb' },
    };

    const ROW_H = 36; // px por hora — debe coincidir con --hg-row-h en CSS
    const EMP_PALETTE_SLOTS = 8; // debe coincidir con --emp-1..8 en CSS

    // Color por empleado (identidad) — independiente del estado. Mismo orden que
    // $empleadosActivos en el backend, así el índice coincide con el de los pills de filtro.
    const empColorIndex = {};
    (DATA.empleados || []).forEach((e, i) => { empColorIndex[e.id] = i % EMP_PALETTE_SLOTS; });

    function empColorVars(empId) {
        const idx = (empColorIndex[empId] ?? 0) + 1;
        return { bg: `var(--emp-${idx}-bg)`, border: `var(--emp-${idx}-border)` };
    }

    let weekStart    = getMondayOf(today);
    let selectedEmps = []; // empty = todos
    let itemRegistry = []; // items renderizados en la semana actual, para abrir el detalle al hacer click

    function getMondayOf(d) {
        const date = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        const dow  = (date.getDay() + 6) % 7; // 0=mon
        date.setDate(date.getDate() - dow);
        return date;
    }

    function getWeekDays(start) {
        return Array.from({ length: 7 }, (_, i) => {
            const d = new Date(start);
            d.setDate(d.getDate() + i);
            return d;
        });
    }

    function horariosForDay(dateStr) {
        const empF = selectedEmps.length > 0;
        const filter = h => h.fecha === dateStr && (!empF || selectedEmps.includes(h.empleado_id));
        return [
            ...DATA.horarios.filter(filter),
            ...DATA.extras.filter(filter),
        ];
    }

    function timeToMin(t) {
        if (!t) return null;
        const parts = t.split(':');
        const h = parseInt(parts[0], 10), m = parseInt(parts[1] || '0', 10);
        if (isNaN(h)) return null;
        return h * 60 + (isNaN(m) ? 0 : m);
    }

    // Asigna "carriles" (lanes) a los items de un mismo día para que los horarios
    // que se cruzan entre empleados se vean lado a lado en vez de superpuestos.
    function assignLanes(items) {
        const sorted = [...items].sort((a, b) => a.startMin - b.startMin);
        const laneEnds = [];
        sorted.forEach(it => {
            let lane = laneEnds.findIndex(end => end <= it.startMin);
            if (lane === -1) { lane = laneEnds.length; laneEnds.push(it.endMin); }
            else { laneEnds[lane] = it.endMin; }
            it.lane = lane;
        });
        const totalLanes = laneEnds.length || 1;
        sorted.forEach(it => it.totalLanes = totalLanes);
        return sorted;
    }

    // Cuenta empleados distintos activos por hora (0-23), para el mapa de calor
    function heatCounts(timed, allday) {
        const sets = Array.from({ length: 24 }, () => new Set());
        timed.forEach(it => {
            const startH = Math.max(0, Math.floor(it.startMin / 60));
            const endH   = Math.min(24, Math.ceil(it.endMin / 60));
            for (let h = startH; h < endH; h++) sets[h].add(it.empleado_id);
        });
        allday.forEach(it => { for (let h = 0; h < 24; h++) sets[h].add(it.empleado_id); });
        return sets.map(s => s.size);
    }

    function heatColor(count) {
        if (!count)      return 'transparent';
        if (count === 1) return 'rgba(34,197,94,.30)';
        if (count <= 3)  return 'rgba(34,197,94,.55)';
        return 'rgba(34,197,94,.85)';
    }

    function renderWeek() {
        const days   = getWeekDays(weekStart);
        const today0 = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        const nowMin = today.getHours() * 60 + today.getMinutes();

        const d0 = days[0], d6 = days[6];
        document.getElementById('weekTitle').textContent =
            `${d0.getDate()} ${MESES_SHORT[d0.getMonth()]} — ${d6.getDate()} ${MESES_SHORT[d6.getMonth()]} ${d6.getFullYear()}`;

        itemRegistry = [];

        // Clasifica los horarios/extras de cada día en "con hora definida" vs "todo el día"
        const perDay = days.map(date => {
            const dateStr = ymd(date);
            const raw     = horariosForDay(dateStr);
            const timed   = [];
            const allday  = [];
            raw.forEach(h => {
                const sMin = timeToMin(h.hora_inicio);
                const eMin = timeToMin(h.hora_fin);
                if (sMin !== null && eMin !== null && eMin > sMin) {
                    timed.push({ ...h, startMin: sMin, endMin: eMin });
                } else {
                    allday.push(h);
                }
            });
            return { date, dateStr, timed: assignLanes(timed), allday };
        });

        renderHgHeader(perDay, today0);
        renderHgAllday(perDay);
        renderHgBody(perDay, today0, nowMin);
    }

    function renderHgHeader(perDay, today0) {
        let html = '<div class="hg-corner"></div>';
        perDay.forEach(({ date, dateStr, timed, allday }) => {
            const isToday = date.getTime() === today0.getTime();
            const isPast  = date < today0;
            const isLibre = timed.length === 0 && allday.length === 0;
            const libreBadge = isLibre && !isPast
                ? `<div class="hg-libre-badge"><i class="bi bi-circle-fill" style="font-size:.4rem;"></i>Libre</div>` : '';
            const addBtn = !isPast
                ? `<button class="hg-add-btn" data-fecha="${dateStr}" title="Agendar horario"><i class="bi bi-plus-lg"></i></button>` : '';
            html += `
            <div class="hg-day-head${isToday ? ' hg-today' : ''}">
                ${addBtn}
                <div class="hg-weekday">${DIAS_SHORT[date.getDay()]}</div>
                <div class="hg-daynum">${date.getDate()}</div>
                <div class="hg-month">${MESES_SHORT[date.getMonth()]}</div>
                ${libreBadge}
            </div>`;
        });
        document.getElementById('hgHeader').innerHTML = html;
    }

    function renderHgAllday(perDay) {
        let html = '<div class="hg-allday-label">Todo<br>el día</div>';
        perDay.forEach(({ allday }) => {
            if (!allday.length) { html += '<div class="hg-allday-cell"></div>'; return; }
            let cell = '<div class="hg-allday-cell">';
            allday.forEach(h => {
                const cfg    = ESTADO_CFG[h.estado] || ESTADO_CFG.programado;
                const colors = DATA.isAdmin ? empColorVars(h.empleado_id) : { bg: cfg.bg, border: cfg.badge };
                const idx    = itemRegistry.push(h) - 1;
                cell += `<div class="hg-allday-item" data-ridx="${idx}" title="${esc(h.nombre)}" style="background:${colors.bg};border-left:3px solid ${colors.border};">
                    <span class="hg-block-dot" style="background:${cfg.badge};"></span>${esc(h.nombre)}
                </div>`;
            });
            cell += '</div>';
            html += cell;
        });
        document.getElementById('hgAllday').innerHTML = html;
    }

    function renderHgBody(perDay, today0, nowMin) {
        const totalH = ROW_H * 24;

        let gutter = `<div class="hg-gutter" style="height:${totalH}px;">`;
        for (let h = 0; h < 24; h++) {
            gutter += `<div class="hg-hour-label" style="top:${h * ROW_H}px;">${pad(h)}:00</div>`;
        }
        gutter += '</div>';

        let cols = '';
        perDay.forEach(({ date, timed, allday }) => {
            const isToday = date.getTime() === today0.getTime();
            let col = `<div class="hg-day-col${isToday ? ' hg-today-col' : ''}" style="height:${totalH}px;">`;

            if (DATA.isAdmin) {
                const counts = heatCounts(timed, allday);
                counts.forEach((c, h) => {
                    const color = heatColor(c);
                    if (color !== 'transparent') {
                        col += `<div class="hg-heat" style="top:${h * ROW_H}px;height:${ROW_H}px;background:${color};" title="${c} empleado(s) trabajando — ${pad(h)}:00"></div>`;
                    }
                });
            }

            if (isToday && nowMin >= 0 && nowMin <= 1440) {
                col += `<div class="hg-now-line" style="top:${(nowMin / 60 * ROW_H).toFixed(1)}px;"></div>`;
            }

            timed.forEach(h => {
                const cfg    = ESTADO_CFG[h.estado] || ESTADO_CFG.programado;
                const colors = DATA.isAdmin ? empColorVars(h.empleado_id) : { bg: cfg.bg, border: cfg.badge };
                const top    = (h.startMin / 60) * ROW_H;
                const height = Math.max(((h.endMin - h.startMin) / 60) * ROW_H, 20);
                const width  = 100 / h.totalLanes;
                const left   = h.lane * width;
                const idx    = itemRegistry.push(h) - 1;
                col += `
                <div class="hg-block" data-ridx="${idx}"
                     style="top:${top}px;height:${height}px;left:calc(${left}% + 2px);width:calc(${width}% - 4px);
                            background:${colors.bg};border-left-color:${colors.border};">
                    <div class="hg-block-name"><span class="hg-block-dot" style="background:${cfg.badge};"></span>${esc(h.nombre)}</div>
                    <div class="hg-block-time">${h.hora_inicio}–${h.hora_fin}</div>
                </div>`;
            });

            col += '</div>';
            cols += col;
        });

        document.getElementById('hgBody').innerHTML = gutter + cols;

        // Auto-scroll a horario laboral (07:00) solo la primera vez que se pinta
        const scrollBox = document.getElementById('hgScroll');
        if (scrollBox && !scrollBox.dataset.scrolled) {
            scrollBox.scrollTop = 7 * ROW_H;
            scrollBox.dataset.scrolled = '1';
        }
    }

    /* ── Detalle de un horario al hacer click (bloque o "todo el día") ── */
    function openHorDetalle(item) {
        const cfg   = ESTADO_CFG[item.estado] || ESTADO_CFG.programado;
        const parts = item.fecha.split('-');
        const label = `${parseInt(parts[2])} de ${MESES[parseInt(parts[1]) - 1]} de ${parts[0]}`;

        document.getElementById('modalHorDetTitle').innerHTML = `<i class="bi bi-person-badge me-2"></i>${esc(item.nombre)}`;
        document.getElementById('modalHorDetFecha').textContent = label;

        const esExtra  = item.estado === 'extra';
        const agendado = (!esExtra && item.hora_inicio && item.hora_fin)
            ? `${item.hora_inicio} – ${item.hora_fin}`
            : (esExtra ? 'Sin horario agendado (trabajó sin turno asignado)' : 'Todo el día / sin hora definida');

        // Sesiones reales de conexión (separadas por huecos, no un solo tramo continuo)
        const sesiones = Array.isArray(item.sesiones) ? item.sesiones : [];
        const realHtml = sesiones.length
            ? sesiones.map(s => `<div>${esc(s.inicio)} – ${esc(s.fin)}</div>`).join('')
            : '<div class="text-muted">Sin registro de conexión</div>';
        const realLabel = sesiones.length > 1
            ? `Conectado (real) — ${sesiones.length} sesiones`
            : 'Conectado (real)';
        const canDel = item.id && (item.es_mio || DATA.isAdmin);
        const esSerie = canDel && !!item.recurrencia_id;

        document.getElementById('modalHorDetBody').innerHTML = `
            <div class="hd-row">
                <i class="bi bi-calendar-event"></i>
                <div><div class="hd-label">Agendado</div><div class="hd-value">
                    ${esc(agendado)}
                    ${esSerie ? '<span class="badge bg-secondary-subtle text-secondary ms-2"><i class="bi bi-arrow-repeat me-1"></i>Serie recurrente</span>' : ''}
                </div></div>
            </div>
            <div class="hd-row">
                <i class="bi bi-wifi"></i>
                <div><div class="hd-label">${esc(realLabel)}</div><div class="hd-value">${realHtml}</div></div>
            </div>
            <div class="hd-row">
                <i class="bi bi-flag"></i>
                <div><div class="hd-label">Estado</div><div class="hd-value">
                    <span style="background:${cfg.badge};color:#fff;padding:2px 10px;border-radius:999px;font-size:.75rem;font-weight:700;">${cfg.label}</span>
                </div></div>
            </div>
            ${item.notas ? `
            <div class="hd-row">
                <i class="bi bi-sticky"></i>
                <div><div class="hd-label">Notas</div><div class="hd-value fw-normal">${esc(item.notas)}</div></div>
            </div>` : ''}
            ${canDel && !esSerie ? `<button class="btn btn-outline-danger btn-sm w-100 mt-3" id="hd-del-btn" data-id="${item.id}"><i class="bi bi-x-circle me-1"></i>Cancelar horario</button>` : ''}
            ${esSerie ? `
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-outline-danger btn-sm w-50" id="hd-del-uno" data-id="${item.id}"><i class="bi bi-x-circle me-1"></i>Solo este día</button>
                <button class="btn btn-danger btn-sm w-50" id="hd-del-serie" data-id="${item.id}"><i class="bi bi-x-circle-fill me-1"></i>Este y los futuros</button>
            </div>` : ''}
        `;

        const delBtn = document.getElementById('hd-del-btn');
        if (delBtn) delBtn.addEventListener('click', function () {
            if (!confirm('¿Cancelar este horario agendado?')) return;
            deleteHorario(this.dataset.id, 'uno');
        });
        const delUnoBtn = document.getElementById('hd-del-uno');
        if (delUnoBtn) delUnoBtn.addEventListener('click', function () {
            if (!confirm('¿Cancelar solo esta ocurrencia de la serie?')) return;
            deleteHorario(this.dataset.id, 'uno');
        });
        const delSerieBtn = document.getElementById('hd-del-serie');
        if (delSerieBtn) delSerieBtn.addEventListener('click', function () {
            if (!confirm('¿Cancelar esta y todas las ocurrencias futuras de esta serie recurrente?')) return;
            deleteHorario(this.dataset.id, 'serie');
        });

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'modalHorDetalle' }));
    }

    // Delegación de clicks: bloques con hora, items "todo el día" y botón "+" del header
    document.getElementById('hgBody').addEventListener('click', e => {
        const blk = e.target.closest('.hg-block');
        if (!blk) return;
        const item = itemRegistry[+blk.dataset.ridx];
        if (item) openHorDetalle(item);
    });
    document.getElementById('hgAllday').addEventListener('click', e => {
        const el = e.target.closest('.hg-allday-item');
        if (!el) return;
        const item = itemRegistry[+el.dataset.ridx];
        if (item) openHorDetalle(item);
    });
    document.getElementById('hgHeader').addEventListener('click', e => {
        const btn = e.target.closest('.hg-add-btn');
        if (!btn) return;
        openAgendarModal(btn.dataset.fecha, new Date(btn.dataset.fecha));
    });

    /* ── Modal: agendar ────────────────────────────────────── */
    function openAgendarModal(dateStr, date) {
        const parts = dateStr.split('-');
        const label = `${parseInt(parts[2])} de ${MESES[parseInt(parts[1])-1]} de ${parts[0]}`;
        document.getElementById('modalAgendarFecha').textContent = label;

        const empOptions = DATA.isAdmin
            ? DATA.empleados.map(e => `<option value="${e.id}">${esc(e.nombre)}</option>`).join('')
            : '';
        const empSel = DATA.isAdmin
            ? `<div class="mb-3">
                <label class="form-label fw-semibold small">Empleado</label>
                <select class="form-select form-select-sm" id="ha-emp">${empOptions}</select>
               </div>`
            : '';

        const DIA_LETRAS = ['L','M','X','J','V','S','D'];
        const diaAnchor  = date.getDay() === 0 ? 7 : date.getDay(); // isoWeekday: 1=lun..7=dom
        const diasBtns = DIA_LETRAS.map((l, i) => {
            const dia = i + 1;
            return `<button type="button" class="btn btn-sm btn-outline-secondary ha-dia-btn${dia === diaAnchor ? ' active' : ''}" data-dia="${dia}">${l}</button>`;
        }).join('');

        const hastaDefault = new Date(date);
        hastaDefault.setDate(hastaDefault.getDate() + 7 * 8); // sugerencia: 8 semanas
        const hastaMax = new Date(date);
        hastaMax.setDate(hastaMax.getDate() + 365);

        document.getElementById('modalAgendarBody').innerHTML = `
        ${empSel}
        <div class="row g-3 mb-3">
            <div class="col-6">
                <label class="form-label fw-semibold small">Hora entrada</label>
                <input type="time" class="form-control form-control-sm" id="ha-inicio">
            </div>
            <div class="col-6">
                <label class="form-label fw-semibold small">Hora salida</label>
                <input type="time" class="form-control form-control-sm" id="ha-fin">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold small">Repetir</label>
            <select class="form-select form-select-sm" id="ha-repetir">
                <option value="no">No se repite</option>
                <option value="semanal">Cada semana (mismo día)</option>
                <option value="dias_semana">Días específicos de la semana</option>
            </select>
        </div>
        <div class="mb-3" id="ha-dias-wrap" style="display:none;">
            <label class="form-label fw-semibold small d-block">Días de la semana</label>
            <div class="d-flex gap-1 flex-wrap" id="ha-dias-picker">${diasBtns}</div>
        </div>
        <div class="mb-3" id="ha-hasta-wrap" style="display:none;">
            <label class="form-label fw-semibold small">Repetir hasta</label>
            <input type="date" class="form-control form-control-sm" id="ha-hasta"
                   min="${dateStr}" max="${ymd(hastaMax)}" value="${ymd(hastaDefault)}">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold small">Notas (opcional)</label>
            <input type="text" class="form-control form-control-sm" id="ha-notas" maxlength="300" placeholder="Descripción...">
        </div>
        <button class="btn btn-primary w-100" id="ha-save" data-fecha="${dateStr}">
            <i class="bi bi-calendar-check me-2"></i>Guardar horario
        </button>
        <div id="ha-msg" class="mt-2 small text-center"></div>`;

        document.getElementById('ha-repetir').addEventListener('change', function () {
            const diasWrap  = document.getElementById('ha-dias-wrap');
            const hastaWrap = document.getElementById('ha-hasta-wrap');
            diasWrap.style.display  = this.value === 'dias_semana' ? '' : 'none';
            hastaWrap.style.display = this.value === 'no' ? 'none' : '';
        });

        document.querySelectorAll('.ha-dia-btn').forEach(btn => {
            btn.addEventListener('click', () => btn.classList.toggle('active'));
        });

        document.getElementById('ha-save').addEventListener('click', function() {
            saveHorario(this.dataset.fecha, this);
        });

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'modalAgendar' }));
    }

    function saveHorario(fecha, btn) {
        const empId   = document.getElementById('ha-emp')?.value || null;
        const inicio  = document.getElementById('ha-inicio')?.value || null;
        const fin     = document.getElementById('ha-fin')?.value || null;
        const notas   = document.getElementById('ha-notas')?.value || null;
        const repetir = document.getElementById('ha-repetir')?.value || 'no';
        const hasta   = document.getElementById('ha-hasta')?.value || null;
        const dias    = Array.from(document.querySelectorAll('.ha-dia-btn.active')).map(b => parseInt(b.dataset.dia, 10));
        const msg     = document.getElementById('ha-msg');

        if (repetir === 'dias_semana' && dias.length === 0) {
            msg.innerHTML = '<span class="text-danger">Elige al menos un día de la semana.</span>';
            return;
        }
        if (repetir !== 'no' && !hasta) {
            msg.innerHTML = '<span class="text-danger">Indica hasta cuándo se repite.</span>';
            return;
        }

        const body = { fecha };
        if (empId)  body.empleado_id = empId;
        if (inicio) body.hora_inicio = inicio;
        if (fin)    body.hora_fin    = fin;
        if (notas)  body.notas       = notas;
        if (repetir !== 'no') {
            body.repetir = repetir;
            body.repetir_hasta = hasta;
            if (repetir === 'dias_semana') body.dias_semana = dias;
        }

        btn.disabled = true;
        fetch('/admin/horarios', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify(body),
        }).then(r => r.json()).then(d => {
            btn.disabled = false;
            if (d.success) {
                const count = d.count || 1;
                msg.innerHTML = count > 1
                    ? `<span class="text-success"><i class="bi bi-check2-circle me-1"></i>${count} horarios agendados (serie).</span>`
                    : '<span class="text-success"><i class="bi bi-check2-circle me-1"></i>Horario agendado.</span>';

                if (Array.isArray(d.horarios) && d.horarios.length) {
                    d.horarios.forEach(h => DATA.horarios.push(h));
                } else {
                    DATA.horarios.push({
                        id: d.id, empleado_id: d.empleado_id, nombre: d.nombre,
                        fecha, hora_inicio: d.hora_inicio, hora_fin: d.hora_fin,
                        sesiones: [], estado: 'programado', es_mio: d.es_mio, notas: d.notas,
                        recurrencia_id: d.recurrencia_id || null,
                    });
                }

                setTimeout(() => {
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'modalAgendar' }));
                    renderWeek();
                }, 800);
            } else {
                msg.innerHTML = '<span class="text-danger">Error al guardar. Verifica los datos.</span>';
            }
        }).catch(() => {
            btn.disabled = false;
            msg.innerHTML = '<span class="text-danger">Error de conexión.</span>';
        });
    }

    function deleteHorario(id, alcance) {
        const qs = alcance === 'serie' ? '?alcance=serie' : '';
        fetch(`/admin/horarios/${id}${qs}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
        }).then(r => r.json()).then(d => {
            if (d.success) {
                const idsBorrados = Array.isArray(d.ids) ? d.ids.map(String) : [String(id)];
                DATA.horarios = DATA.horarios.filter(h => !idsBorrados.includes(String(h.id)));
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'modalHorDetalle' }));
                renderWeek();
            }
        });
    }

    /* ── Controles tab 2 ───────────────────────────────────── */
    document.getElementById('prevWeek').addEventListener('click', () => {
        weekStart.setDate(weekStart.getDate() - 7);
        if (document.getElementById('tab-hor').style.display !== 'none') renderWeek();
    });
    document.getElementById('nextWeek').addEventListener('click', () => {
        weekStart.setDate(weekStart.getDate() + 7);
        if (document.getElementById('tab-hor').style.display !== 'none') renderWeek();
    });
    document.getElementById('thisWeekBtn').addEventListener('click', () => {
        weekStart = getMondayOf(today);
        if (document.getElementById('tab-hor').style.display !== 'none') renderWeek();
    });

    document.querySelectorAll('.emp-pill').forEach(btn => {
        btn.addEventListener('click', () => {
            const wid = btn.dataset.wid;
            if (wid === 'all') {
                selectedEmps = [];
                document.querySelectorAll('.emp-pill').forEach(b => b.classList.toggle('active', b.dataset.wid === 'all'));
            } else {
                const id  = parseInt(wid);
                const idx = selectedEmps.indexOf(id);
                if (idx === -1) selectedEmps.push(id);
                else selectedEmps.splice(idx, 1);
                document.querySelector('.emp-pill[data-wid="all"]')?.classList.toggle('active', selectedEmps.length === 0);
                btn.classList.toggle('active', selectedEmps.includes(id));
            }
            renderWeek();
        });
    });

})();
</script>
@endsection
