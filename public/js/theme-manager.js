/**
 * STREAMIFY - GESTOR DE TEMAS DINÁMICOS
 * Versión: 1.0
 * Fecha: 1 de diciembre de 2025
 *
 * Controlador central para cambio de temas y persistencia
 */

const ThemeManager = {
    // Configuración
    STORAGE_KEY: 'streamify_theme',
    DEFAULT_THEME: 'default',
    currentTheme: null,

    // Temas disponibles con sus períodos automáticos
    themes: {
        default: {
            name: 'Streamify Original',
            icon: '🎨',
            decoration: null,
            autoActivate: null
        },
        dark: {
            name: 'Modo Oscuro',
            icon: '🌙',
            decoration: null,
            autoActivate: null
        },
        christmas: {
            name: 'Navidad',
            icon: '🎄',
            decoration: 'christmas',
            autoActivate: { month: 12, dayStart: 2, dayEnd: 26 }
        },
        newyear: {
            name: 'Año Nuevo',
            icon: '🎆',
            decoration: 'newyear',
            autoActivate: { month: 1, dayStart: 1, dayEnd: 7 }
        },
        valentine: {
            name: 'San Valentín',
            icon: '💝',
            decoration: 'valentine',
            autoActivate: { month: 2, dayStart: 10, dayEnd: 15 }
        },
        spring: {
            name: 'Primavera',
            icon: '🌸',
            decoration: null,
            autoActivate: { month: 3, dayStart: 20, dayEnd: 21 }
        },
        summer: {
            name: 'Verano',
            icon: '☀️',
            decoration: null,
            autoActivate: { month: 6, dayStart: 21, dayEnd: 23 }
        },
        autumn: {
            name: 'Otoño',
            icon: '🍂',
            decoration: null,
            autoActivate: { month: 9, dayStart: 22, dayEnd: 24 }
        }
    },

    /**
     * Inicializar sistema de temas
     */
    init() {
        console.log('[ThemeManager] Inicializando sistema de temas...');

        // Cargar tema desde localStorage o determinar automático
        const savedTheme = this.loadSavedTheme();
        const autoTheme = this.getAutoTheme();

        // Prioridad: tema guardado > tema automático > default
        const themeToApply = savedTheme || autoTheme || this.DEFAULT_THEME;

        console.log(`[ThemeManager] Tema guardado: ${savedTheme}, Auto: ${autoTheme}, Aplicando: ${themeToApply}`);

        this.setTheme(themeToApply, false); // false = no guardar (ya está guardado)
        // NO crear selector automático - se controla desde vista Sistema
        // this.createThemeSelector();
        this.initEventListeners();

        console.log('[ThemeManager] Sistema de temas inicializado ✓');
    },

    /**
     * Cargar tema guardado desde localStorage
     */
    loadSavedTheme() {
        try {
            const saved = localStorage.getItem(this.STORAGE_KEY);
            if (saved && this.themes[saved]) {
                return saved;
            }
        } catch (error) {
            console.warn('[ThemeManager] Error cargando tema guardado:', error);
        }
        return null;
    },

    /**
     * Guardar tema en localStorage
     */
    saveTheme(themeId) {
        try {
            localStorage.setItem(this.STORAGE_KEY, themeId);
            console.log(`[ThemeManager] Tema guardado: ${themeId}`);
        } catch (error) {
            console.error('[ThemeManager] Error guardando tema:', error);
        }
    },

    /**
     * Determinar tema automático según fecha
     */
    getAutoTheme() {
        const now = new Date();
        const currentMonth = now.getMonth() + 1; // JavaScript months are 0-based
        const currentDay = now.getDate();

        for (const [themeId, config] of Object.entries(this.themes)) {
            if (config.autoActivate) {
                const { month, dayStart, dayEnd } = config.autoActivate;

                if (currentMonth === month && currentDay >= dayStart && currentDay <= dayEnd) {
                    console.log(`[ThemeManager] Auto-activando tema: ${themeId} (${currentDay}/${currentMonth})`);
                    return themeId;
                }
            }
        }

        return null;
    },

    /**
     * Establecer tema activo
     */
    setTheme(themeId, save = true) {
        if (!this.themes[themeId]) {
            console.error(`[ThemeManager] Tema no encontrado: ${themeId}`);
            return;
        }

        console.log(`[ThemeManager] Aplicando tema: ${themeId}`);

        // Aplicar tema al documento
        document.documentElement.setAttribute('data-theme', themeId);
        this.currentTheme = themeId;

        // Guardar en localStorage si se requiere
        if (save) {
            this.saveTheme(themeId);
        }

        // Manejar decoraciones
        const themeConfig = this.themes[themeId];
        if (typeof Decorations !== 'undefined') {
            if (themeConfig.decoration) {
                Decorations.activate(themeConfig.decoration);
            } else {
                Decorations.deactivateAll();
            }
        }

        // Actualizar selector visual
        this.updateThemeSelectorUI(themeId);

        // Disparar evento personalizado
        window.dispatchEvent(new CustomEvent('themeChanged', {
            detail: { theme: themeId, config: themeConfig }
        }));

        console.log(`[ThemeManager] Tema aplicado: ${themeId} ✓`);
    },

    /**
     * Crear selector de temas en el navbar
     */
    createThemeSelector() {
        const navbar = document.querySelector('.navbar');
        if (!navbar) {
            console.warn('[ThemeManager] Navbar no encontrado');
            return;
        }

        const selectorHTML = `
            <div class="theme-selector-wrapper" style="margin-left: auto;">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                        type="button"
                        id="themeSelector"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        title="Cambiar tema">
                    <span id="currentThemeIcon">${this.themes[this.currentTheme].icon}</span>
                    <span class="d-none d-md-inline ms-2">Tema</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="themeSelector">
                    ${Object.entries(this.themes).map(([id, config]) => `
                        <li>
                            <a class="dropdown-item theme-option ${id === this.currentTheme ? 'active' : ''}"
                               href="#"
                               data-theme="${id}">
                                <span class="me-2">${config.icon}</span>
                                ${config.name}
                                ${id === this.currentTheme ? '<span class="badge bg-primary ms-2">Activo</span>' : ''}
                            </a>
                        </li>
                    `).join('')}
                </ul>
            </div>
        `;

        // Insertar antes del último elemento del navbar (usualmente el menú de usuario)
        const navbarNav = navbar.querySelector('.navbar-nav');
        if (navbarNav) {
            navbarNav.insertAdjacentHTML('beforeend', selectorHTML);
        } else {
            navbar.insertAdjacentHTML('beforeend', selectorHTML);
        }

        console.log('[ThemeManager] Selector de temas creado ✓');
    },

    /**
     * Actualizar UI del selector
     */
    updateThemeSelectorUI(themeId) {
        // Actualizar icono actual
        const iconElement = document.getElementById('currentThemeIcon');
        if (iconElement) {
            iconElement.textContent = this.themes[themeId].icon;
        }

        // Actualizar opciones del dropdown
        document.querySelectorAll('.theme-option').forEach(option => {
            const optionTheme = option.getAttribute('data-theme');

            if (optionTheme === themeId) {
                option.classList.add('active');
                option.innerHTML = `
                    <span class="me-2">${this.themes[optionTheme].icon}</span>
                    ${this.themes[optionTheme].name}
                    <span class="badge bg-primary ms-2">Activo</span>
                `;
            } else {
                option.classList.remove('active');
                option.innerHTML = `
                    <span class="me-2">${this.themes[optionTheme].icon}</span>
                    ${this.themes[optionTheme].name}
                `;
            }
        });
    },

    /**
     * Inicializar event listeners
     */
    initEventListeners() {
        // Click en opciones de tema
        document.addEventListener('click', (e) => {
            const themeOption = e.target.closest('.theme-option');
            if (themeOption) {
                e.preventDefault();
                const themeId = themeOption.getAttribute('data-theme');
                this.setTheme(themeId);
            }
        });

        // Escuchar cambios de fecha (para auto-activar temas)
        setInterval(() => {
            const autoTheme = this.getAutoTheme();
            const savedTheme = this.loadSavedTheme();

            // Solo auto-cambiar si NO hay tema guardado manualmente
            if (autoTheme && !savedTheme) {
                if (this.currentTheme !== autoTheme) {
                    console.log(`[ThemeManager] Auto-cambiando tema a: ${autoTheme}`);
                    this.setTheme(autoTheme, false);
                }
            }
        }, 60000); // Revisar cada minuto

        console.log('[ThemeManager] Event listeners inicializados ✓');
    },

    /**
     * API pública para otros scripts
     */
    getAvailableThemes() {
        return Object.keys(this.themes);
    },

    getCurrentTheme() {
        return this.currentTheme;
    },

    resetToDefault() {
        localStorage.removeItem(this.STORAGE_KEY);
        this.setTheme(this.DEFAULT_THEME, false);
    },

    enableAutoThemes() {
        localStorage.removeItem(this.STORAGE_KEY);
        const autoTheme = this.getAutoTheme();
        if (autoTheme) {
            this.setTheme(autoTheme, false);
        }
    }
};

// Auto-inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => ThemeManager.init());
} else {
    ThemeManager.init();
}

// Exponer API global
window.ThemeManager = ThemeManager;

console.log('[ThemeManager] Módulo cargado');
