document.addEventListener("DOMContentLoaded", function () {
    // Sidebar toggle
    const sidebar = document.querySelector("#layoutSidenav_nav");
    const toggleBtn = document.querySelector("#sidebarToggle");

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener("click", function () {
            sidebar.classList.toggle("show");
        });
    }

    /* ---------------------------------------------------------------------
       Modo claro / oscuro — PREFERENCIA PERSONAL
       ---------------------------------------------------------------------
       Cualquier empleado puede cambiarlo: solo le afecta a él, y se guarda en
       el servidor para que le siga entre dispositivos. (El TEMA, en cambio, es
       global y solo lo cambia un administrador desde /admin/sistema.)

       El botón cicla tres estados, como en cualquier aplicación:
         Automático (sigue al sistema operativo) → Claro → Oscuro → Automático
       --------------------------------------------------------------------- */
    const btnEscritorio = document.getElementById('toggleDarkMode');
    const iconoEscritorio = document.getElementById('darkModeIcon');
    const btnMovil = document.getElementById('toggleDarkModeMobile');
    const iconoMovil = document.getElementById('darkModeIconMobile');
    const textoMovil = document.getElementById('darkModeTextMobile');

    const ETIQUETAS = {
        system: { icono: 'fa-circle-half-stroke', texto: 'Tema: automático', titulo: 'Sigue al sistema — clic para forzar claro' },
        light:  { icono: 'fa-sun',                texto: 'Tema: claro',      titulo: 'Modo claro — clic para forzar oscuro' },
        dark:   { icono: 'fa-moon',               texto: 'Tema: oscuro',     titulo: 'Modo oscuro — clic para volver a automático' },
    };

    function esquemaActual() {
        return document.documentElement.getAttribute('data-color-scheme') || 'system';
    }

    function pintarIconos() {
        const info = ETIQUETAS[esquemaActual()] || ETIQUETAS.system;

        if (iconoEscritorio) iconoEscritorio.className = 'fas ' + info.icono + ' fa-lg';
        if (btnEscritorio) btnEscritorio.setAttribute('title', info.titulo);
        if (iconoMovil) iconoMovil.className = 'fas ' + info.icono + ' me-2';
        if (textoMovil) textoMovil.textContent = info.texto;
    }

    function ciclar() {
        if (typeof ThemeManager !== 'undefined') ThemeManager.cicloEsquema();
    }

    if (btnEscritorio) btnEscritorio.addEventListener('click', ciclar);
    if (btnMovil) btnMovil.addEventListener('click', ciclar);

    // Se repinta cuando cambia la preferencia y también cuando cambia el modo
    // del sistema operativo con la pestaña abierta.
    window.addEventListener('esquemaCambiado', pintarIconos);

    pintarIconos();
});
