/**
 * MODO OSCURO - INTEGRADO CON THEME MANAGER
 * Versión: 2.0
 * Fecha: 1 de diciembre de 2025
 */

document.addEventListener("DOMContentLoaded", function () {
    const toggleDarkModeButton = document.getElementById("toggleDarkMode");
    const darkModeIcon = document.getElementById("darkModeIcon");

    if (!toggleDarkModeButton || !darkModeIcon) {
        console.warn('[DarkMode] Botón de modo oscuro no encontrado');
        return;
    }

    // Función para actualizar el icono
    function updateIcon(isDark) {
        if (isDark) {
            darkModeIcon.classList.remove("fa-moon");
            darkModeIcon.classList.add("fa-sun");
        } else {
            darkModeIcon.classList.remove("fa-sun");
            darkModeIcon.classList.add("fa-moon");
        }
    }

    // Sincronizar con ThemeManager si está disponible
    function syncWithThemeManager() {
        if (typeof ThemeManager !== 'undefined') {
            const currentTheme = ThemeManager.getCurrentTheme();
            const isDark = currentTheme === 'dark';
            updateIcon(isDark);
            console.log('[DarkMode] Sincronizado con ThemeManager:', currentTheme);
        }
    }

    // Cambiar entre modo claro y oscuro
    toggleDarkModeButton.addEventListener("click", function () {
        if (typeof ThemeManager !== 'undefined') {
            const currentTheme = ThemeManager.getCurrentTheme();

            if (currentTheme === 'dark') {
                // Cambiar a tema default (claro)
                ThemeManager.setTheme('default');
                updateIcon(false);
                console.log('[DarkMode] Cambiado a modo claro');
            } else {
                // Cambiar a tema dark
                ThemeManager.setTheme('dark');
                updateIcon(true);
                console.log('[DarkMode] Cambiado a modo oscuro');
            }
        } else {
            console.error('[DarkMode] ThemeManager no está disponible');
        }
    });

    // Escuchar cambios de tema
    window.addEventListener('themeChanged', function(event) {
        const isDark = event.detail.theme === 'dark';
        updateIcon(isDark);
        console.log('[DarkMode] Tema cambiado a:', event.detail.theme);
    });

    // Inicializar al cargar
    setTimeout(syncWithThemeManager, 100);

    console.log('[DarkMode] Sistema de modo oscuro inicializado ✓');
});
