/**
 * STREAMIFY — GESTOR DE APARIENCIA
 * Versión: 3.0
 *
 * DOS COSAS DISTINTAS, Y NO SE MEZCLAN
 * ------------------------------------
 *
 *  1. EL TEMA (Navidad, Neón, Mundial, Océano…) es GLOBAL. Lo fija el
 *     administrador en /admin/sistema y lo ve todo el mundo: cualquier
 *     empleado, en cualquier dispositivo, y los clientes en el sitio público.
 *     Vive en la base de datos. Antes vivía en localStorage y por eso lo que
 *     elegía el administrador no le llegaba a nadie.
 *
 *  2. EL MODO CLARO / OSCURO es PERSONAL, como en cualquier aplicación.
 *     Tres estados: 'system' (sigue al sistema operativo, es el de fábrica),
 *     'light' y 'dark'. Si hay sesión se guarda en el servidor para que le
 *     siga entre dispositivos; si no, en el navegador.
 *
 * El atributo data-dark-mode que consume el CSS lo resuelve el script en línea
 * de partials/apariencia-esquema.blade.php, que corre antes del primer pintado
 * para que no haya parpadeo. Aquí solo se cambia data-color-scheme y se le
 * pide que vuelva a resolver.
 */

const ThemeManager = {
    config: null,
    currentTheme: 'default',
    esquema: 'system',
    _sondeo: null,

    /** Cada cuánto se comprueba si el administrador cambió el tema global. */
    INTERVALO_SONDEO: 60000,

    ESQUEMAS: ['system', 'light', 'dark'],

    init() {
        this.config = window.StreamifyApariencia || null;

        if (!this.config) {
            console.warn('[Apariencia] window.StreamifyApariencia no está definido; el gestor queda inactivo.');
            return;
        }

        this.currentTheme = this.config.tema || 'default';
        this.esquema = document.documentElement.getAttribute('data-color-scheme') || 'system';

        this.actualizarUI();
        this.aplicarDecoracion();
        this.initEventListeners();
        this.iniciarSondeo();
    },

    get temas() {
        return (this.config && this.config.catalogo) || {};
    },

    /** ¿Se está viendo oscuro ahora mismo? (resultado, no preferencia) */
    isDarkMode() {
        return document.documentElement.hasAttribute('data-dark-mode');
    },

    /* =====================================================================
       PERSONAL — modo claro / oscuro
       ===================================================================== */

    /**
     * Cambia la preferencia personal. No afecta a nadie más.
     */
    async setEsquema(esquema) {
        if (this.ESQUEMAS.indexOf(esquema) === -1) esquema = 'system';

        const previo = this.esquema;
        this.esquema = esquema;

        // Aplicar al momento: es una preferencia personal, no hay que esperar
        // al servidor para que se vea.
        document.documentElement.setAttribute('data-color-scheme', esquema);
        if (typeof window.__sfAplicarEsquema === 'function') window.__sfAplicarEsquema();

        this.actualizarUI();
        window.dispatchEvent(new CustomEvent('esquemaCambiado', {
            detail: { esquema: esquema, oscuro: this.isDarkMode() }
        }));

        // Un visitante sin sesión lo guarda en su propio navegador.
        if (!window.__sfAutenticado) {
            try { localStorage.setItem('streamify_esquema', esquema); } catch (e) {}
            return true;
        }

        // Con sesión, al servidor, para que le siga entre dispositivos.
        try {
            const respuesta = await fetch(this.config.rutas.esquema, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ esquema: esquema }),
            });
            const datos = await respuesta.json().catch(() => ({}));
            if (!respuesta.ok || !datos.success) throw new Error(datos.message || respuesta.status);
            return true;
        } catch (error) {
            console.error('[Apariencia] No se pudo guardar la preferencia:', error);
            // Se deja aplicado en esta pestaña, pero se avisa de que no
            // persistirá: mentir sería peor.
            this.avisar('Se aplicó aquí, pero no se pudo guardar tu preferencia.', 'error');
            this.esquema = previo;
            return false;
        }
    },

    /** system → light → dark → system */
    cicloEsquema() {
        const orden = this.ESQUEMAS;
        const siguiente = orden[(orden.indexOf(this.esquema) + 1) % orden.length];
        return this.setEsquema(siguiente);
    },

    /* =====================================================================
       GLOBAL — tema de la plataforma (solo administradores)
       ===================================================================== */

    aplicarTema(tema) {
        if (!tema || !this.temas[tema]) return;
        document.documentElement.setAttribute('data-theme', tema);
        this.currentTheme = tema;
        this.actualizarUI();
        this.aplicarDecoracion();
        window.dispatchEvent(new CustomEvent('temaCambiado', { detail: { tema: tema } }));
    },

    aplicarDecoracion() {
        if (typeof Decorations === 'undefined') return;
        const decoracion = (this.temas[this.currentTheme] || {}).decoracion;
        if (decoracion) Decorations.activate(decoracion);
        else Decorations.deactivateAll();
    },

    async guardarGlobal(cambios) {
        if (!this.config.puedeEditar || !this.config.rutas.guardar) {
            this.avisar('Solo un administrador puede cambiar el tema de la plataforma.', 'error');
            return false;
        }

        const previo = this.currentTheme;
        if (cambios.tema) this.aplicarTema(cambios.tema);

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

            this.config.tema = datos.apariencia.tema;
            this.config.temaBase = datos.apariencia.temaBase;
            this.config.autoTemporada = datos.apariencia.autoTemporada;

            // El servidor manda: puede haber resuelto un tema de temporada
            // distinto al que se pidió.
            this.aplicarTema(datos.apariencia.tema);
            this.avisar('Tema actualizado para toda la plataforma.', 'ok');
            return true;

        } catch (error) {
            console.error('[Apariencia] No se pudo guardar:', error);
            this.aplicarTema(previo);
            this.avisar('No se pudo guardar el cambio. Revisa tu conexión e inténtalo de nuevo.', 'error');
            return false;
        }
    },

    setTheme(temaId) {
        if (!this.temas[temaId]) {
            console.error(`[Apariencia] Tema desconocido: ${temaId}`);
            return;
        }
        return this.guardarGlobal({ tema: temaId });
    },

    setAutoTemporada(activo) {
        return this.guardarGlobal({ auto_temporada: !!activo });
    },

    resetToDefault() {
        return this.guardarGlobal({ tema: 'default' });
    },

    /* =====================================================================
       Sondeo: propagar el tema global entre dispositivos
       ===================================================================== */

    iniciarSondeo() {
        if (this._sondeo) clearInterval(this._sondeo);
        this._sondeo = setInterval(() => this.comprobarCambios(), this.INTERVALO_SONDEO);

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') this.comprobarCambios();
        });
    },

    async comprobarCambios() {
        try {
            const respuesta = await fetch(this.config.rutas.leer, { headers: { 'Accept': 'application/json' } });
            if (!respuesta.ok) return;

            const datos = await respuesta.json();
            const remoto = datos.apariencia;
            if (!remoto) return;

            // Solo el tema: el modo claro/oscuro es personal y no se sincroniza.
            if (remoto.tema !== this.currentTheme) {
                console.log('[Apariencia] El administrador cambió el tema; aplicando.');
                this.config.tema = remoto.tema;
                this.aplicarTema(remoto.tema);
            }
        } catch (error) {
            // Un fallo de red en el sondeo no debe molestar al usuario.
        }
    },

    /* =====================================================================
       Interfaz
       ===================================================================== */

    actualizarUI() {
        const tema = this.temas[this.currentTheme];
        if (tema) {
            const icono = document.getElementById('currentThemeIcon');
            if (icono) icono.textContent = tema.icono;
        }

        document.querySelectorAll('.theme-option').forEach((o) => {
            o.classList.toggle('active', o.getAttribute('data-theme') === this.currentTheme);
        });
        document.querySelectorAll('.theme-card').forEach((c) => {
            c.classList.toggle('active', c.getAttribute('data-theme') === this.currentTheme);
        });

        // Selector de esquema (3 opciones) en la vista de sistema.
        document.querySelectorAll('[data-esquema]').forEach((el) => {
            el.classList.toggle('active', el.getAttribute('data-esquema') === this.esquema);
        });
    },

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
        // Tarjetas de tema (global, solo administradores)
        document.addEventListener('click', (e) => {
            const opcion = e.target.closest('[data-theme]');
            if (opcion && opcion.matches('.theme-option, .theme-card, .theme-apply-btn')) {
                e.preventDefault();
                this.setTheme(opcion.getAttribute('data-theme'));
                return;
            }

            // Botones de esquema personal
            const esq = e.target.closest('[data-esquema]');
            if (esq) {
                e.preventDefault();
                this.setEsquema(esq.getAttribute('data-esquema'));
            }
        });

        // Si el sistema operativo cambia y la preferencia es 'system',
        // el script en línea ya reaplica; aquí solo se refresca la interfaz.
        window.addEventListener('esquemaCambiado', () => this.actualizarUI());
    },

    /* --- API pública ----------------------------------------------------- */
    getAvailableThemes() { return Object.keys(this.temas); },
    getCurrentTheme() { return this.currentTheme; },
    getEsquema() { return this.esquema; },
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => ThemeManager.init());
} else {
    ThemeManager.init();
}

window.ThemeManager = ThemeManager;
