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
       Modo oscuro
       ---------------------------------------------------------------------
       El modo oscuro es GLOBAL: lo fija el administrador y lo ven todos los
       empleados en todos sus dispositivos (ver AparienciaService). Por eso el
       botón solo se muestra a quien puede cambiarlo; al resto se le oculta en
       vez de dejarle un botón que respondería "no tienes permiso".

       El icono NO se actualiza aquí tras el clic: el guardado es asíncrono y
       puede revertirse si el servidor falla. Se escucha el evento que emite
       ThemeManager cuando el estado queda confirmado.
       --------------------------------------------------------------------- */
    const puedeEditar = !!(window.StreamifyApariencia && window.StreamifyApariencia.puedeEditar);

    const btnEscritorio = document.getElementById('toggleDarkMode');
    const iconoEscritorio = document.getElementById('darkModeIcon');
    const btnMovil = document.getElementById('toggleDarkModeMobile');
    const iconoMovil = document.getElementById('darkModeIconMobile');
    const textoMovil = document.getElementById('darkModeTextMobile');

    if (!puedeEditar) {
        [btnEscritorio, btnMovil].forEach(function (btn) {
            if (btn) btn.closest('li, div, .nav-item')?.style.setProperty('display', 'none');
            if (btn) btn.style.display = 'none';
        });
        return;
    }

    function pintarIconos() {
        const esOscuro = typeof ThemeManager !== 'undefined' && ThemeManager.isDarkMode();

        if (iconoEscritorio) {
            // Sol cuando ya está oscuro (la acción es volver a claro), luna si no.
            iconoEscritorio.className = esOscuro ? 'fas fa-sun fa-lg' : 'fas fa-moon fa-lg';
        }
        if (iconoMovil && textoMovil) {
            iconoMovil.className = esOscuro ? 'fas fa-sun me-2' : 'fas fa-moon me-2';
            textoMovil.textContent = esOscuro ? 'Modo Claro' : 'Modo Oscuro';
        }
    }

    function alternar() {
        if (typeof ThemeManager !== 'undefined') ThemeManager.toggleDarkMode();
    }

    if (btnEscritorio) btnEscritorio.addEventListener('click', alternar);
    if (btnMovil) btnMovil.addEventListener('click', alternar);

    // ThemeManager avisa cuando el estado ya está confirmado (o revertido).
    window.addEventListener('aparienciaCambiada', pintarIconos);

    pintarIconos();
});
