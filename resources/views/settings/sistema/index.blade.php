@extends('layouts.navigation')

@section('title', 'Sistema - Streamify')

@section('main')
<div class="container-fluid px-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mt-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sistema</li>
        </ol>
    </nav>

    <!-- Título principal -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-cogs"></i> Configuración del Sistema
        </h1>
    </div>

    <!-- Tarjeta de Temas -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-palette"></i> Gestión de Temas
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Selecciona el tema visual para toda la plataforma. Los cambios se aplican en tiempo real.
                    </p>

                    <!-- Toggle Dark Mode -->
                    <div class="alert alert-info d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <i class="fas fa-moon me-2"></i>
                            <strong>Modo Oscuro</strong>
                            <p class="mb-0 small">Activa el modo oscuro sobre cualquier tema</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="darkModeToggle" style="width: 3rem; height: 1.5rem;">
                        </div>
                    </div>

                    <!-- Grid de temas -->
                    <div class="row g-3" id="themesGrid">
                        <!-- Tema Default -->
                        <div class="col-md-6 col-lg-4">
                            <div class="theme-card" data-theme="default">
                                <div class="theme-preview" style="background: linear-gradient(135deg, #ffe226 0%, #e6cb22 100%);">
                                    <span class="theme-icon">🎨</span>
                                </div>
                                <div class="theme-info">
                                    <h6 class="theme-name">Streamify Original</h6>
                                    <p class="theme-description">Amarillo y Marrón</p>
                                    <button class="btn btn-sm btn-outline-primary select-theme-btn">Seleccionar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Tema Christmas -->
                        <div class="col-md-6 col-lg-4">
                            <div class="theme-card" data-theme="christmas">
                                <div class="theme-preview" style="background: linear-gradient(135deg, #c92a2a 0%, #2f9e44 100%);">
                                    <span class="theme-icon">🎄</span>
                                </div>
                                <div class="theme-info">
                                    <h6 class="theme-name">Navidad</h6>
                                    <p class="theme-description">Rojo y Verde festivo</p>
                                    <span class="badge bg-info mb-2">2-26 Diciembre</span>
                                    <button class="btn btn-sm btn-outline-primary select-theme-btn">Seleccionar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Tema New Year -->
                        <div class="col-md-6 col-lg-4">
                            <div class="theme-card" data-theme="newyear">
                                <div class="theme-preview" style="background: linear-gradient(135deg, #ffd43b 0%, #1a1a1a 100%);">
                                    <span class="theme-icon">🎆</span>
                                </div>
                                <div class="theme-info">
                                    <h6 class="theme-name">Año Nuevo</h6>
                                    <p class="theme-description">Dorado y Negro</p>
                                    <span class="badge bg-info mb-2">1-7 Enero</span>
                                    <button class="btn btn-sm btn-outline-primary select-theme-btn">Seleccionar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Tema Valentine -->
                        <div class="col-md-6 col-lg-4">
                            <div class="theme-card" data-theme="valentine">
                                <div class="theme-preview" style="background: linear-gradient(135deg, #e64980 0%, #f06595 100%);">
                                    <span class="theme-icon">💝</span>
                                </div>
                                <div class="theme-info">
                                    <h6 class="theme-name">San Valentín</h6>
                                    <p class="theme-description">Rosa romántico</p>
                                    <span class="badge bg-info mb-2">10-15 Febrero</span>
                                    <button class="btn btn-sm btn-outline-primary select-theme-btn">Seleccionar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Tema Spring -->
                        <div class="col-md-6 col-lg-4">
                            <div class="theme-card" data-theme="spring">
                                <div class="theme-preview" style="background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%);">
                                    <span class="theme-icon">🌸</span>
                                </div>
                                <div class="theme-info">
                                    <h6 class="theme-name">Primavera</h6>
                                    <p class="theme-description">Verde fresco</p>
                                    <span class="badge bg-info mb-2">20-21 Marzo</span>
                                    <button class="btn btn-sm btn-outline-primary select-theme-btn">Seleccionar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Tema Summer -->
                        <div class="col-md-6 col-lg-4">
                            <div class="theme-card" data-theme="summer">
                                <div class="theme-preview" style="background: linear-gradient(135deg, #22b8cf 0%, #1098ad 100%);">
                                    <span class="theme-icon">☀️</span>
                                </div>
                                <div class="theme-info">
                                    <h6 class="theme-name">Verano</h6>
                                    <p class="theme-description">Azul cielo</p>
                                    <span class="badge bg-info mb-2">21-23 Junio</span>
                                    <button class="btn btn-sm btn-outline-primary select-theme-btn">Seleccionar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Tema Autumn -->
                        <div class="col-md-6 col-lg-4">
                            <div class="theme-card" data-theme="autumn">
                                <div class="theme-preview" style="background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%);">
                                    <span class="theme-icon">🍂</span>
                                </div>
                                <div class="theme-info">
                                    <h6 class="theme-name">Otoño</h6>
                                    <p class="theme-description">Naranja cálido</p>
                                    <span class="badge bg-info mb-2">22-24 Septiembre</span>
                                    <button class="btn btn-sm btn-outline-primary select-theme-btn">Seleccionar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Información adicional -->
                    <div class="alert alert-info mb-0">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle"></i> Información
                        </h6>
                        <ul class="mb-0">
                            <li>Los temas con fechas se activan automáticamente en esos períodos</li>
                            <li>Puedes seleccionar manualmente cualquier tema en cualquier momento</li>
                            <li>Algunos temas incluyen decoraciones especiales (nieve, confetti, corazones)</li>
                            <li>Los cambios se guardan automáticamente y se aplican en todas las páginas</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de tema actual -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-check-circle"></i> Tema Actual
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <span id="currentThemeIcon" style="font-size: 2rem;" class="me-3">🎨</span>
                        <div>
                            <h5 id="currentThemeName" class="mb-1">Streamify Original</h5>
                            <p id="currentThemeDescription" class="text-muted mb-0">Amarillo y Marrón</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.theme-card {
    border: 2px solid var(--border-color, #e0e0e0);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    cursor: pointer;
    background: var(--bg-card, #fff);
}

.theme-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg, 0 8px 20px rgba(0, 0, 0, 0.12));
    border-color: var(--primary-color, #007bff);
}

.theme-card.active {
    border-color: var(--primary-color, #007bff);
    border-width: 3px;
}

.theme-preview {
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.theme-icon {
    font-size: 3rem;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.theme-info {
    padding: 15px;
    text-align: center;
}

.theme-name {
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--text-primary, #333);
}

.theme-description {
    font-size: 0.85rem;
    color: var(--text-secondary, #666);
    margin-bottom: 10px;
}

.select-theme-btn {
    width: 100%;
}

.theme-card.active .select-theme-btn {
    background: var(--primary-color, #007bff);
    color: var(--text-on-primary, #fff);
    border-color: var(--primary-color, #007bff);
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const themesGrid = document.getElementById('themesGrid');
    const darkModeToggle = document.getElementById('darkModeToggle');
    const currentThemeIcon = document.getElementById('currentThemeIcon');
    const currentThemeName = document.getElementById('currentThemeName');
    const currentThemeDescription = document.getElementById('currentThemeDescription');

    // Información de los temas
    const themesInfo = {
        default: { icon: '🎨', name: 'Streamify Original', description: 'Amarillo y Marrón' },
        christmas: { icon: '🎄', name: 'Navidad', description: 'Rojo y Verde festivo' },
        newyear: { icon: '🎆', name: 'Año Nuevo', description: 'Dorado y Negro' },
        valentine: { icon: '💝', name: 'San Valentín', description: 'Rosa romántico' },
        spring: { icon: '🌸', name: 'Primavera', description: 'Verde fresco' },
        summer: { icon: '☀️', name: 'Verano', description: 'Azul cielo' },
        autumn: { icon: '🍂', name: 'Otoño', description: 'Naranja cálido' }
    };

    // Actualizar toggle de dark mode
    function updateDarkModeToggle() {
        if (typeof ThemeManager !== 'undefined' && darkModeToggle) {
            darkModeToggle.checked = ThemeManager.isDarkMode();
        }
    }

    // Actualizar UI de tema actual
    function updateCurrentThemeDisplay() {
        if (typeof ThemeManager !== 'undefined') {
            const currentTheme = ThemeManager.getCurrentTheme();
            const info = themesInfo[currentTheme];

            if (info) {
                currentThemeIcon.textContent = info.icon;
                currentThemeName.textContent = info.name;
                currentThemeDescription.textContent = info.description;
            }

            // Marcar tarjeta activa
            document.querySelectorAll('.theme-card').forEach(card => {
                card.classList.remove('active');
                const btn = card.querySelector('.select-theme-btn');
                if (card.dataset.theme === currentTheme) {
                    card.classList.add('active');
                    btn.textContent = 'Activo';
                } else {
                    btn.textContent = 'Seleccionar';
                }
            });
        }
    }

    // Manejar clicks en botones de temas
    themesGrid.addEventListener('click', function(e) {
        const btn = e.target.closest('.select-theme-btn');
        if (btn) {
            const card = btn.closest('.theme-card');
            const themeId = card.dataset.theme;

            if (typeof ThemeManager !== 'undefined') {
                ThemeManager.setTheme(themeId);
                updateCurrentThemeDisplay();
            }
        }
    });

    // Manejar toggle de dark mode
    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', function() {
            if (typeof ThemeManager !== 'undefined') {
                ThemeManager.setDarkMode(this.checked);
            }
        });
    }

    // Escuchar cambios de tema
    window.addEventListener('themeChanged', function() {
        updateCurrentThemeDisplay();
    });

    // Escuchar cambios de dark mode
    window.addEventListener('darkModeChanged', function() {
        updateDarkModeToggle();
    });

    // Inicializar
    updateCurrentThemeDisplay();
    updateDarkModeToggle();
});
</script>
@endsection
