/**
 * STREAMIFY — GESTOR DE APARIENCIA GLOBAL
 * Versión: 2.0
 *
 * QUÉ CAMBIÓ RESPECTO A LA v1
 * ---------------------------
 * Antes el tema y el modo oscuro vivían en localStorage. Eso significaba que
 * cada navegador y cada sesión de empleado tenía su propia copia: si el
 * administrador cambiaba el tema, nadie más lo veía. Ese era el bug.
 *
 * Ahora la fuente de verdad es el SERVIDOR (tabla ajustes_apariencia, vía
 * AparienciaService). Este script ya no decide nada por su cuenta:
 *
 *   1. El layout pinta data-theme y data-dark-mode en el <html> desde PHP,
 *      así que la apariencia correcta se ve desde el primer frame (sin
 *      parpadeo) y sin depender de JavaScript.
 *   2. Este módulo solo refleja ese estado y, cuando un administrador cambia
 *      algo, lo MANDA al servidor.
 *   3. Un sondeo ligero detecta cambios hechos desde otro dispositivo y los
 *      aplica en caliente, sin recargar.
 *
 * El catálogo de temas ya no está duplicado aquí: llega desde PHP en
 * window.StreamifyApariencia.catalogo.
 */

const ThemeManager = {
    config: null,
    currentTheme: 'default',
    darkMode: false,
    _sondeo: null,

    /** Cada cuánto se comprueba si otro dispositivo cambió la apariencia. */
    INTERVALO_SONDEO: 60000,

    init() {
        this.config = window.StreamifyApariencia || null;

        if (!this.config) {
            // Sin configuración del servidor no hay nada que gestionar. Puede
            // pasar en una vista que no incluya partials/apariencia-config.
            console.warn('[Apariencia] window.StreamifyApariencia no está definido; el gestor queda inactivo.');
            return;
        }

        // El servidor ya aplicó los atributos en el <html>; solo los leemos.
        this.currentTheme = this.config.tema || 'default';
        this.darkMode = !!this.config.modoOscuro;

        this.actualizarUI();
        this.aplicarDecoracion();
        this.initEventListeners();
        this.iniciarSondeo();
    },

    get temas() {
        return (this.config && this.config.catalogo) || {};
    },

    /* ---------------------------------------------------------------------
       Aplicación local (sin persistir)
       --------------------------------------------------------------------- */

    /**
     * Pinta un estado en el documento. No habla con el servidor.
     * Se usa al recibir la respuesta del guardado y al detectar un cambio
     * remoto durante el sondeo.
     */
    aplicar(tema, modoOscuro) {
        const html = document.documentElement;

        if (tema && this.temas[tema]) {
            html.setAttribute('data-theme', tema);
            this.currentTheme = tema;
        }

        this.darkMode = !!modoOscuro;
        if (this.darkMode) {
            html.setAttribute('data-dark-mode', 'true');
            html.setAttribute('data-bs-theme', 'dark');
        } else {
            html.removeAttribute('data-dark-mode');
            html.setAttribute('data-bs-theme', 'light');
        }

        this.actualizarUI();
        this.aplicarDecoracion();

        window.dispatchEvent(new CustomEvent('aparienciaCambiada', {
            detail: { tema: this.currentTheme, modoOscuro: this.darkMode }
        }));
    },

    aplicarDecoracion() {
        if (typeof Decorations === 'undefined') return;

        const decoracion = (this.temas[this.currentTheme] || {}).decoracion;
        if (decoracion) {
            Decorations.activate(decoracion);
        } else {
            Decorations.deactivateAll();
        }
    },

    /* ---------------------------------------------------------------------
       Persistencia en el servidor
       --------------------------------------------------------------------- */

    /**
     * Guarda en el servidor. Solo funciona para administradores; el resto
     * recibe 403 y se les avisa en vez de dejar la interfaz mintiendo.
     */
    async guardar(cambios) {
        if (!this.config.puedeEditar || !this.config.rutas.guardar) {
            this.avisar('Solo un administrador puede cambiar la apariencia de la plataforma.', 'error');
            return false;
        }

        // Optimista: se aplica ya para que la interfaz responda al instante.
        const previo = { tema: this.currentTheme, modoOscuro: this.darkMode };
        this.aplicar(
            cambios.tema !== undefined ? cambios.tema : previo.tema,
            cambios.modo_oscuro !== undefined ? cambios.modo_oscuro : previo.modoOscuro
        );

        try {
            const respuesta = await fetch(this.config.rutas.guardar, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(cambios),
            });

            const datos = await respuesta.json().catch(() => ({}));

            if (!respuesta.ok || !datos.success) {
                throw new Error(datos.message || `El servidor respondió ${respuesta.status}`);
            }

            // El servidor manda: puede haber resuelto un tema de temporada
            // distinto al que se pidió.
            this.config.tema = datos.apariencia.tema;
            this.config.temaBase = datos.apariencia.temaBase;
            this.config.modoOscuro = datos.apariencia.modoOscuro;
            this.config.autoTemporada = datos.apariencia.autoTemporada;

            this.aplicar(datos.apariencia.tema, datos.apariencia.modoOscuro);
            this.avisar('Apariencia actualizada para toda la plataforma.', 'ok');
            return true;

        } catch (error) {
            // Revertir: no dejar la pantalla mostrando algo que no se guardó.
            console.error('[Apariencia] No se pudo guardar:', error);
            this.aplicar(previo.tema, previo.modoOscuro);
            this.avisar('No se pudo guardar el cambio. Revisa tu conexión e inténtalo de nuevo.', 'error');
            return false;
        }
    },

    setTheme(temaId) {
        if (!this.temas[temaId]) {
            console.error(`[Apariencia] Tema desconocido: ${temaId}`);
            return;
        }
        return this.guardar({ tema: temaId });
    },

    setDarkMode(activo) {
        return this.guardar({ modo_oscuro: !!activo });
    },

    toggleDarkMode() {
        return this.setDarkMode(!this.darkMode);
    },

    setAutoTemporada(activo) {
        return this.guardar({ auto_temporada: !!activo });
    },

    resetToDefault() {
        return this.guardar({ tema: 'default' });
    },

    /* ---------------------------------------------------------------------
       Sondeo: propagar cambios entre dispositivos
       --------------------------------------------------------------------- */

    iniciarSondeo() {
        if (this._sondeo) clearInterval(this._sondeo);

        this._sondeo = setInterval(() => this.comprobarCambios(), this.INTERVALO_SONDEO);

        // Al volver a la pestaña, comprobar de inmediato: es cuando más
        // probable es que algo haya cambiado mientras no mirabas.
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') this.comprobarCambios();
        });
    },

    async comprobarCambios() {
        try {
            const respuesta = await fetch(this.config.rutas.leer, {
                headers: { 'Accept': 'application/json' },
            });
            if (!respuesta.ok) return;

            const datos = await respuesta.json();
            const remoto = datos.apariencia;
            if (!remoto) return;

            if (remoto.tema !== this.currentTheme || !!remoto.modoOscuro !== this.darkMode) {
                console.log('[Apariencia] Cambio detectado desde otro dispositivo; aplicando.');
                this.config.tema = remoto.tema;
                this.config.modoOscuro = remoto.modoOscuro;
                this.aplicar(remoto.tema, remoto.modoOscuro);
            }
        } catch (error) {
            // Un fallo de red en el sondeo no debe molestar al usuario.
        }
    },

    /* ---------------------------------------------------------------------
       Interfaz
       --------------------------------------------------------------------- */

    actualizarUI() {
        const tema = this.temas[this.currentTheme];
        if (!tema) return;

        const icono = document.getElementById('currentThemeIcon');
        if (icono) icono.textContent = tema.icono;

        document.querySelectorAll('.theme-option').forEach((opcion) => {
            const id = opcion.getAttribute('data-theme');
            opcion.classList.toggle('active', id === this.currentTheme);
        });

        document.querySelectorAll('.theme-card').forEach((tarjeta) => {
            tarjeta.classList.toggle('active', tarjeta.getAttribute('data-theme') === this.currentTheme);
        });

        const toggle = document.getElementById('darkModeToggle');
        if (toggle) toggle.checked = this.darkMode;
    },

    /** Aviso discreto; usa el toast del panel si existe. */
    avisar(mensaje, tipo = 'ok') {
        if (typeof window.mostrarToast === 'function') {
            window.mostrarToast(mensaje, tipo);
            return;
        }

        let aviso = document.getElementById('apariencia-aviso');
        if (!aviso) {
            aviso = document.createElement('div');
            aviso.id = 'apariencia-aviso';
            aviso.style.cssText =
                'position:fixed;bottom:22px;left:50%;transform:translateX(-50%);z-index:9999;' +
                'padding:10px 18px;border-radius:999px;font-size:.85rem;font-weight:600;' +
                'box-shadow:0 6px 20px rgba(0,0,0,.25);transition:opacity .25s;pointer-events:none;';
            document.body.appendChild(aviso);
        }

        aviso.textContent = mensaje;
        aviso.style.background = tipo === 'error' ? '#c62828' : '#0f8a4d';
        aviso.style.color = '#fff';
        aviso.style.opacity = '1';

        clearTimeout(this._avisoTimer);
        this._avisoTimer = setTimeout(() => { aviso.style.opacity = '0'; }, 3200);
    },

    initEventListeners() {
        document.addEventListener('click', (e) => {
            const opcion = e.target.closest('[data-theme]');
            if (!opcion) return;
            if (!opcion.matches('.theme-option, .theme-card, .theme-apply-btn')) return;

            e.preventDefault();
            this.setTheme(opcion.getAttribute('data-theme'));
        });

        const toggle = document.getElementById('darkModeToggle');
        if (toggle) {
            toggle.addEventListener('change', (e) => this.setDarkMode(e.target.checked));
        }
    },

    /* --- API pública (compatibilidad con la v1) -------------------------- */
    getAvailableThemes() { return Object.keys(this.temas); },
    getCurrentTheme() { return this.currentTheme; },
    isDarkMode() { return this.darkMode; },
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => ThemeManager.init());
} else {
    ThemeManager.init();
}

window.ThemeManager = ThemeManager;
