@extends('layouts.navigation')
@section('title', 'Sistema — Streamify')

@section('main')
<div class="container-fluid px-4 pb-5">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mt-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
            <li class="breadcrumb-item active">Sistema</li>
        </ol>
    </nav>

    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="sistema-icon-wrap">
            <i class="fas fa-palette"></i>
        </div>
        <div>
            <h1 class="h3 mb-0 fw-bold">Configuración del Sistema</h1>
            <p class="text-muted mb-0 small">Personaliza la apariencia de toda la plataforma en tiempo real</p>
        </div>
    </div>

    <div class="row g-4">

        {{-- ── COLUMNA PRINCIPAL ─────────────────────────────── --}}
        <div class="col-xl-8">

            {{-- Dark Mode Toggle --}}
            <div class="sistema-card mb-4">
                <div class="sistema-card-header">
                    <span><i class="fas fa-adjust me-2"></i>Modo de visualización</span>
                </div>
                <div class="sistema-card-body">
                    <div class="dark-mode-toggle-row">
                        <div class="d-flex align-items-center gap-3">
                            <div class="toggle-icon-wrap" id="darkModeIconWrap">
                                <i class="fas fa-sun" id="darkModeIcon"></i>
                            </div>
                            <div>
                                <div class="fw-semibold" id="darkModeLabel">Modo Claro</div>
                                <div class="small text-muted">Se aplica sobre cualquier tema activo</div>
                            </div>
                        </div>
                        <label class="sistema-switch">
                            <input type="checkbox" id="darkModeToggle">
                            <span class="sistema-switch-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Temas Grid --}}
            <div class="sistema-card">
                <div class="sistema-card-header">
                    <span><i class="fas fa-swatchbook me-2"></i>Temas disponibles</span>
                    <span class="badge-active-theme" id="activeThemeBadge">Cargando...</span>
                </div>
                <div class="sistema-card-body">
                    <div class="themes-grid" id="themesGrid">

                        {{-- DEFAULT --}}
                        <div class="theme-card" data-theme="default">
                            <div class="theme-preview-wrap">
                                <div class="theme-preview" style="background:linear-gradient(135deg,#ffe226,#341806);">
                                    <span class="theme-emoji">🎨</span>
                                    <div class="theme-preview-dots">
                                        <span style="background:#ffe226"></span>
                                        <span style="background:#341806"></span>
                                        <span style="background:#fffb50"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="theme-body">
                                <div class="theme-name">Streamify Original</div>
                                <div class="theme-desc">Amarillo & Marrón</div>
                                <div class="theme-badges">
                                    <span class="tbadge tbadge-permanent">Permanente</span>
                                </div>
                            </div>
                            <button class="theme-select-btn">Seleccionar</button>
                        </div>

                        {{-- MUNDIAL 2026 --}}
                        <div class="theme-card theme-featured" data-theme="mundial2026">
                            <div class="theme-new-ribbon">¡NUEVO!</div>
                            <div class="theme-preview-wrap">
                                <div class="theme-preview mundial-preview">
                                    <span class="theme-emoji">🏆</span>
                                    <div class="theme-preview-dots">
                                        <span style="background:#FFD100"></span>
                                        <span style="background:#003893"></span>
                                        <span style="background:#D30000"></span>
                                    </div>
                                    <div class="mundial-flag-stripe"></div>
                                </div>
                            </div>
                            <div class="theme-body">
                                <div class="theme-name">⚽ Mundial 2026</div>
                                <div class="theme-desc">Ecuador 🇪🇨 · USA · México · Canadá</div>
                                <div class="theme-badges">
                                    <span class="tbadge tbadge-event">11 Jun – 19 Jul</span>
                                    <span class="tbadge tbadge-auto">Auto</span>
                                </div>
                            </div>
                            <button class="theme-select-btn">Seleccionar</button>
                        </div>

                        {{-- NAVIDAD --}}
                        <div class="theme-card" data-theme="christmas">
                            <div class="theme-preview-wrap">
                                <div class="theme-preview" style="background:linear-gradient(135deg,#c92a2a,#2f9e44);">
                                    <span class="theme-emoji">🎄</span>
                                    <div class="theme-preview-dots">
                                        <span style="background:#c92a2a"></span>
                                        <span style="background:#2f9e44"></span>
                                        <span style="background:#ffd43b"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="theme-body">
                                <div class="theme-name">Navidad</div>
                                <div class="theme-desc">Rojo & Verde festivo</div>
                                <div class="theme-badges">
                                    <span class="tbadge tbadge-event">2–26 Dic</span>
                                    <span class="tbadge tbadge-auto">Auto</span>
                                    <span class="tbadge tbadge-fx">❄️ Nieve</span>
                                </div>
                            </div>
                            <button class="theme-select-btn">Seleccionar</button>
                        </div>

                        {{-- AÑO NUEVO --}}
                        <div class="theme-card" data-theme="newyear">
                            <div class="theme-preview-wrap">
                                <div class="theme-preview" style="background:linear-gradient(135deg,#1a1a1a,#ffd43b);">
                                    <span class="theme-emoji">🎆</span>
                                    <div class="theme-preview-dots">
                                        <span style="background:#ffd43b"></span>
                                        <span style="background:#1a1a1a"></span>
                                        <span style="background:#ffe066"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="theme-body">
                                <div class="theme-name">Año Nuevo</div>
                                <div class="theme-desc">Dorado & Negro</div>
                                <div class="theme-badges">
                                    <span class="tbadge tbadge-event">1–7 Ene</span>
                                    <span class="tbadge tbadge-auto">Auto</span>
                                    <span class="tbadge tbadge-fx">🎇 Fuegos</span>
                                </div>
                            </div>
                            <button class="theme-select-btn">Seleccionar</button>
                        </div>

                        {{-- SAN VALENTÍN --}}
                        <div class="theme-card" data-theme="valentine">
                            <div class="theme-preview-wrap">
                                <div class="theme-preview" style="background:linear-gradient(135deg,#e64980,#f06595);">
                                    <span class="theme-emoji">💝</span>
                                    <div class="theme-preview-dots">
                                        <span style="background:#e64980"></span>
                                        <span style="background:#f06595"></span>
                                        <span style="background:#ffc9da"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="theme-body">
                                <div class="theme-name">San Valentín</div>
                                <div class="theme-desc">Rosa romántico</div>
                                <div class="theme-badges">
                                    <span class="tbadge tbadge-event">10–15 Feb</span>
                                    <span class="tbadge tbadge-auto">Auto</span>
                                    <span class="tbadge tbadge-fx">💕 Corazones</span>
                                </div>
                            </div>
                            <button class="theme-select-btn">Seleccionar</button>
                        </div>

                        {{-- PRIMAVERA --}}
                        <div class="theme-card" data-theme="spring">
                            <div class="theme-preview-wrap">
                                <div class="theme-preview" style="background:linear-gradient(135deg,#51cf66,#37b24d);">
                                    <span class="theme-emoji">🌸</span>
                                    <div class="theme-preview-dots">
                                        <span style="background:#51cf66"></span>
                                        <span style="background:#fab005"></span>
                                        <span style="background:#d8f5a2"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="theme-body">
                                <div class="theme-name">Primavera</div>
                                <div class="theme-desc">Verde fresco</div>
                                <div class="theme-badges">
                                    <span class="tbadge tbadge-event">20–21 Mar</span>
                                    <span class="tbadge tbadge-auto">Auto</span>
                                </div>
                            </div>
                            <button class="theme-select-btn">Seleccionar</button>
                        </div>

                        {{-- VERANO --}}
                        <div class="theme-card" data-theme="summer">
                            <div class="theme-preview-wrap">
                                <div class="theme-preview" style="background:linear-gradient(135deg,#22b8cf,#1098ad);">
                                    <span class="theme-emoji">☀️</span>
                                    <div class="theme-preview-dots">
                                        <span style="background:#22b8cf"></span>
                                        <span style="background:#ff922b"></span>
                                        <span style="background:#a5d8ff"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="theme-body">
                                <div class="theme-name">Verano</div>
                                <div class="theme-desc">Azul cielo & sol</div>
                                <div class="theme-badges">
                                    <span class="tbadge tbadge-event">21–23 Jun</span>
                                    <span class="tbadge tbadge-auto">Auto</span>
                                </div>
                            </div>
                            <button class="theme-select-btn">Seleccionar</button>
                        </div>

                        {{-- OTOÑO --}}
                        <div class="theme-card" data-theme="autumn">
                            <div class="theme-preview-wrap">
                                <div class="theme-preview" style="background:linear-gradient(135deg,#fd7e14,#e8590c);">
                                    <span class="theme-emoji">🍂</span>
                                    <div class="theme-preview-dots">
                                        <span style="background:#fd7e14"></span>
                                        <span style="background:#862e9c"></span>
                                        <span style="background:#ffd8a8"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="theme-body">
                                <div class="theme-name">Otoño</div>
                                <div class="theme-desc">Naranja cálido</div>
                                <div class="theme-badges">
                                    <span class="tbadge tbadge-event">22–24 Sep</span>
                                    <span class="tbadge tbadge-auto">Auto</span>
                                </div>
                            </div>
                            <button class="theme-select-btn">Seleccionar</button>
                        </div>

                    </div>{{-- /themes-grid --}}
                </div>
            </div>

        </div>{{-- /col-xl-8 --}}

        {{-- ── COLUMNA LATERAL ───────────────────────────────── --}}
        <div class="col-xl-4">

            {{-- Tema activo --}}
            <div class="sistema-card mb-4" id="currentThemeCard">
                <div class="sistema-card-header">
                    <span><i class="fas fa-check-circle me-2 text-success"></i>Tema activo</span>
                </div>
                <div class="sistema-card-body">
                    <div class="current-theme-display">
                        <div class="current-theme-emoji" id="currentThemeIcon">🎨</div>
                        <div>
                            <div class="fw-bold fs-5" id="currentThemeName">Streamify Original</div>
                            <div class="text-muted small" id="currentThemeDescription">Amarillo y Marrón</div>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary w-100 mt-3"
                            onclick="if(typeof ThemeManager!=='undefined')ThemeManager.resetToDefault()">
                        <i class="fas fa-undo me-1"></i> Restaurar predeterminado
                    </button>
                </div>
            </div>

            {{-- Leyenda --}}
            <div class="sistema-card mb-4">
                <div class="sistema-card-header">
                    <span><i class="fas fa-info-circle me-2"></i>Información</span>
                </div>
                <div class="sistema-card-body">
                    <ul class="sistema-info-list">
                        <li><span class="tbadge tbadge-auto">Auto</span> Se activa automáticamente en la fecha indicada</li>
                        <li><span class="tbadge tbadge-fx">✨ Efectos</span> Incluye animaciones y decoraciones especiales</li>
                        <li><span class="tbadge tbadge-permanent">Permanente</span> Disponible todo el año</li>
                        <li><i class="fas fa-moon text-primary me-1"></i> Modo oscuro funciona sobre cualquier tema</li>
                        <li><i class="fas fa-save text-primary me-1"></i> Preferencias guardadas por navegador</li>
                    </ul>
                </div>
            </div>

            {{-- Mundial 2026 Highlight --}}
            <div class="mundial-highlight-card">
                <div class="mundial-hl-header">
                    <span class="fs-4">⚽</span>
                    <div>
                        <div class="fw-bold">FIFA World Cup 2026</div>
                        <div class="small opacity-75">México · Canadá · USA</div>
                    </div>
                    <span style="font-size:1.5rem">🏆</span>
                </div>
                <div class="mundial-flag-row">
                    <span title="Ecuador">🇪🇨</span>
                    <span title="México">🇲🇽</span>
                    <span title="Canadá">🇨🇦</span>
                    <span title="USA">🇺🇸</span>
                </div>
                <div class="mundial-hl-body">
                    <div class="mundial-date-pill">
                        <i class="fas fa-calendar-alt me-1"></i>
                        11 Junio — 19 Julio 2026
                    </div>
                    <p class="small mt-2 mb-0 opacity-85">
                        Activa el tema y disfruta de pelotas de fútbol, confeti en los colores de <strong>Ecuador 🇪🇨</strong> y el ticker del mundial en toda la plataforma.
                    </p>
                </div>
                <button class="mundial-activate-btn" onclick="if(typeof ThemeManager!=='undefined')ThemeManager.setTheme('mundial2026')">
                    <i class="fas fa-futbol me-2"></i>¡Activar Mundial 2026!
                </button>
            </div>

        </div>{{-- /col-xl-4 --}}
    </div>{{-- /row --}}
</div>

<style>
/* ── Layout base ─────────────────────────────────────────── */
.sistema-icon-wrap {
    width: 52px; height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--primary-color, #ffe226), var(--primary-dark, #e6cb22));
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: var(--text-on-primary, #341806);
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
    flex-shrink: 0;
}

/* ── Sistema cards ───────────────────────────────────────── */
.sistema-card {
    background: var(--bg-card, #fff);
    border: 1px solid var(--border-color, #dee2e6);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: var(--shadow-sm, 0 2px 4px rgba(0,0,0,.06));
    transition: all .25s;
}
.sistema-card-header {
    padding: 14px 20px;
    background: var(--bg-light, #f8f9fa);
    border-bottom: 1px solid var(--border-color, #dee2e6);
    font-weight: 600; font-size: .88rem;
    display: flex; align-items: center; justify-content: space-between;
    color: var(--text-primary, #333);
}
.sistema-card-body { padding: 20px; }

/* ── Dark mode toggle ────────────────────────────────────── */
.dark-mode-toggle-row {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; flex-wrap: wrap;
}
.toggle-icon-wrap {
    width: 44px; height: 44px; border-radius: 12px;
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; color: #fff;
    transition: all .3s;
}
.toggle-icon-wrap.dark {
    background: linear-gradient(135deg, #4338ca, #1e1b4b);
}

.sistema-switch { position: relative; display: inline-block; width: 56px; height: 28px; cursor: pointer; }
.sistema-switch input { opacity: 0; width: 0; height: 0; }
.sistema-switch-slider {
    position: absolute; inset: 0;
    background: #ccc; border-radius: 28px;
    transition: .3s;
}
.sistema-switch-slider::before {
    content: '';
    position: absolute;
    width: 22px; height: 22px;
    left: 3px; bottom: 3px;
    background: #fff; border-radius: 50%;
    transition: .3s; box-shadow: 0 2px 4px rgba(0,0,0,.2);
}
.sistema-switch input:checked + .sistema-switch-slider { background: #4338ca; }
.sistema-switch input:checked + .sistema-switch-slider::before { transform: translateX(28px); }

/* ── Badge tema activo ───────────────────────────────────── */
.badge-active-theme {
    background: var(--primary-color, #ffe226);
    color: var(--text-on-primary, #341806);
    font-size: .7rem; font-weight: 700;
    padding: 3px 10px; border-radius: 999px;
}

/* ── Themes grid ─────────────────────────────────────────── */
.themes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 14px;
}

.theme-card {
    border: 2px solid var(--border-color, #dee2e6);
    border-radius: 14px;
    overflow: hidden;
    cursor: pointer;
    background: var(--bg-card, #fff);
    transition: all .22s;
    position: relative;
    display: flex; flex-direction: column;
}
.theme-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 28px rgba(0,0,0,.13);
    border-color: var(--primary-color, #ffe226);
}
.theme-card.active {
    border-color: var(--primary-color, #ffe226);
    border-width: 3px;
    box-shadow: 0 0 0 4px rgba(var(--primary-rgb, 255,226,38), .15);
}
.theme-featured {
    border-color: #FFD100;
    box-shadow: 0 4px 20px rgba(255, 209, 0, .25);
}

/* Ribbon "NUEVO" */
.theme-new-ribbon {
    position: absolute; top: 10px; right: -1px;
    background: linear-gradient(135deg, #D30000, #ff4444);
    color: #fff; font-size: .6rem; font-weight: 800;
    padding: 3px 10px 3px 8px;
    border-radius: 4px 0 0 4px;
    letter-spacing: .06em; z-index: 2;
    box-shadow: -2px 2px 6px rgba(0,0,0,.2);
}

.theme-preview-wrap { flex-shrink: 0; }
.theme-preview {
    height: 100px;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    position: relative; overflow: hidden;
}
.theme-emoji {
    font-size: 2.6rem;
    filter: drop-shadow(0 2px 6px rgba(0,0,0,.25));
    z-index: 1;
}
.theme-preview-dots {
    display: flex; gap: 5px; margin-top: 6px; z-index: 1;
}
.theme-preview-dots span {
    width: 10px; height: 10px; border-radius: 50%;
    border: 2px solid rgba(255,255,255,.5);
}

/* Mundial preview especial */
.mundial-preview {
    background: linear-gradient(180deg, #0a2e14 0%, #003893 55%, #D30000 100%) !important;
}
.mundial-flag-stripe {
    position: absolute; bottom: 0; left: 0; right: 0; height: 6px;
    background: linear-gradient(90deg, #FFD100 33%, #003893 33% 66%, #D30000 66%);
}

.theme-body {
    padding: 10px 12px 8px;
    flex: 1;
}
.theme-name { font-weight: 700; font-size: .85rem; color: var(--text-primary, #333); margin-bottom: 2px; }
.theme-desc { font-size: .75rem; color: var(--text-secondary, #666); margin-bottom: 6px; }
.theme-badges { display: flex; flex-wrap: wrap; gap: 4px; }

.tbadge {
    font-size: .62rem; font-weight: 700; padding: 2px 7px;
    border-radius: 999px; letter-spacing: .03em; white-space: nowrap;
}
.tbadge-event    { background: #e0f2fe; color: #0369a1; }
.tbadge-auto     { background: #dcfce7; color: #15803d; }
.tbadge-fx       { background: #fef9c3; color: #854d0e; }
.tbadge-permanent{ background: #f3f4f6; color: #374151; }

.theme-select-btn {
    width: 100%; padding: 8px 12px;
    background: var(--bg-light, #f8f9fa);
    border: none; border-top: 1px solid var(--border-color, #dee2e6);
    font-size: .78rem; font-weight: 600;
    color: var(--text-primary, #333);
    cursor: pointer; transition: all .18s;
}
.theme-select-btn:hover {
    background: var(--primary-color, #ffe226);
    color: var(--text-on-primary, #341806);
}
.theme-card.active .theme-select-btn {
    background: var(--primary-color, #ffe226);
    color: var(--text-on-primary, #341806);
}

/* ── Tema activo sidebar ─────────────────────────────────── */
.current-theme-display {
    display: flex; align-items: center; gap: 14px;
}
.current-theme-emoji { font-size: 2.5rem; }

/* ── Info list ───────────────────────────────────────────── */
.sistema-info-list {
    list-style: none; padding: 0; margin: 0;
    display: flex; flex-direction: column; gap: 10px;
}
.sistema-info-list li {
    font-size: .82rem; color: var(--text-primary, #333);
    display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
}

/* ── Mundial highlight card ──────────────────────────────── */
.mundial-highlight-card {
    background: linear-gradient(160deg, #003893 0%, #001f5c 60%, #0a2e14 100%);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 28px rgba(0,56,147,.35);
    border: 2px solid #FFD100;
}
.mundial-hl-header {
    display: flex; align-items: center; gap: 12px;
    padding: 16px 18px 10px;
    color: #FFD100;
}
.mundial-flag-row {
    display: flex; gap: 10px;
    padding: 4px 18px 10px;
    font-size: 1.6rem;
}
.mundial-hl-body {
    padding: 0 18px 14px;
    color: #e0f0ff;
}
.mundial-date-pill {
    display: inline-flex; align-items: center;
    background: rgba(255,209,0,.15);
    border: 1px solid rgba(255,209,0,.4);
    color: #FFD100; font-weight: 700; font-size: .78rem;
    padding: 4px 12px; border-radius: 999px;
}
.mundial-activate-btn {
    width: 100%; padding: 12px;
    background: linear-gradient(135deg, #FFD100, #e6bc00);
    border: none; color: #001a0a;
    font-weight: 800; font-size: .9rem;
    cursor: pointer; transition: all .2s;
    letter-spacing: .02em;
}
.mundial-activate-btn:hover {
    background: linear-gradient(135deg, #ffe640, #FFD100);
    box-shadow: 0 4px 16px rgba(255,209,0,.5);
}

/* ── Dark mode adaptación ────────────────────────────────── */
[data-dark-mode="true"] .theme-card {
    background: var(--bg-card) !important;
    border-color: var(--border-color) !important;
}
[data-dark-mode="true"] .theme-select-btn {
    background: var(--bg-light) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}
[data-dark-mode="true"] .theme-card.active .theme-select-btn,
[data-dark-mode="true"] .theme-card:hover .theme-select-btn {
    background: var(--primary-color) !important;
    color: var(--text-on-primary) !important;
}
[data-dark-mode="true"] .sistema-card {
    background: var(--bg-card) !important;
    border-color: var(--border-color) !important;
}
[data-dark-mode="true"] .sistema-card-header {
    background: var(--bg-light) !important;
    border-color: var(--border-color) !important;
    color: var(--text-primary) !important;
}
[data-dark-mode="true"] .theme-name { color: var(--text-primary) !important; }
[data-dark-mode="true"] .theme-desc { color: var(--text-secondary) !important; }
[data-dark-mode="true"] .tbadge-event    { background:#0c2a3d; color:#7dd3fc; }
[data-dark-mode="true"] .tbadge-auto     { background:#052e16; color:#86efac; }
[data-dark-mode="true"] .tbadge-fx       { background:#2d1f0a; color:#fde68a; }
[data-dark-mode="true"] .tbadge-permanent{ background:#1f2937; color:#9ca3af; }
[data-dark-mode="true"] .badge-active-theme {
    background: var(--primary-color) !important;
    color: var(--text-on-primary) !important;
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const darkModeToggle   = document.getElementById('darkModeToggle');
    const darkModeIcon     = document.getElementById('darkModeIcon');
    const darkModeIconWrap = document.getElementById('darkModeIconWrap');
    const darkModeLabel    = document.getElementById('darkModeLabel');

    const themesInfo = {
        default:      { icon:'🎨', name:'Streamify Original',  description:'Amarillo & Marrón' },
        mundial2026:  { icon:'🏆', name:'Mundial 2026 ⚽',      description:'Ecuador 🇪🇨 · USA · México · Canadá' },
        christmas:    { icon:'🎄', name:'Navidad',              description:'Rojo & Verde festivo' },
        newyear:      { icon:'🎆', name:'Año Nuevo',            description:'Dorado & Negro' },
        valentine:    { icon:'💝', name:'San Valentín',         description:'Rosa romántico' },
        spring:       { icon:'🌸', name:'Primavera',            description:'Verde fresco' },
        summer:       { icon:'☀️', name:'Verano',               description:'Azul cielo & sol' },
        autumn:       { icon:'🍂', name:'Otoño',                description:'Naranja cálido' },
    };

    function updateDarkModeUI(isDark) {
        if (!darkModeToggle) return;
        darkModeToggle.checked = isDark;
        if (isDark) {
            darkModeIcon.className   = 'fas fa-moon';
            darkModeIconWrap.classList.add('dark');
            darkModeLabel.textContent = 'Modo Oscuro';
        } else {
            darkModeIcon.className   = 'fas fa-sun';
            darkModeIconWrap.classList.remove('dark');
            darkModeLabel.textContent = 'Modo Claro';
        }
    }

    function updateCurrentThemeDisplay(themeId) {
        const info = themesInfo[themeId];
        if (!info) return;
        document.getElementById('currentThemeIcon').textContent        = info.icon;
        document.getElementById('currentThemeName').textContent        = info.name;
        document.getElementById('currentThemeDescription').textContent = info.description;
        document.getElementById('activeThemeBadge').textContent        = info.name;
    }

    function markActiveCard(themeId) {
        document.querySelectorAll('.theme-card').forEach(card => {
            const isActive = card.dataset.theme === themeId;
            card.classList.toggle('active', isActive);
            const btn = card.querySelector('.theme-select-btn');
            if (btn) btn.textContent = isActive ? '✓ Activo' : 'Seleccionar';
        });
    }

    // Clicks en tarjetas
    document.getElementById('themesGrid').addEventListener('click', function (e) {
        const btn  = e.target.closest('.theme-select-btn');
        const card = e.target.closest('.theme-card');
        if ((btn || card) && typeof ThemeManager !== 'undefined') {
            const themeId = (btn || card).closest('.theme-card').dataset.theme;
            ThemeManager.setTheme(themeId);
        }
    });

    // Toggle dark mode
    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', function () {
            if (typeof ThemeManager !== 'undefined') ThemeManager.setDarkMode(this.checked);
        });
    }

    // Eventos del ThemeManager
    window.addEventListener('themeChanged',   e => { updateCurrentThemeDisplay(e.detail.theme); markActiveCard(e.detail.theme); });
    window.addEventListener('darkModeChanged', e => updateDarkModeUI(e.detail.darkMode));

    // Inicializar
    if (typeof ThemeManager !== 'undefined') {
        updateCurrentThemeDisplay(ThemeManager.getCurrentTheme() || 'default');
        markActiveCard(ThemeManager.getCurrentTheme() || 'default');
        updateDarkModeUI(ThemeManager.isDarkMode());
    }
});
</script>
@endsection
