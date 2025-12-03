document.addEventListener("DOMContentLoaded", function () {
    // Sidebar toggle
    const sidebar = document.querySelector("#layoutSidenav_nav");
    const toggleBtn = document.querySelector("#sidebarToggle");

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener("click", function () {
            sidebar.classList.toggle("show");
        });
    }

    // Dark mode toggle desde navbar (Desktop)
    const darkModeToggle = document.getElementById('toggleDarkMode');
    const darkModeIcon = document.getElementById('darkModeIcon');

    if (darkModeToggle && darkModeIcon) {
        // Actualizar icono inicial
        updateDarkModeIcon();

        // Event listener para el botón
        darkModeToggle.addEventListener('click', function() {
            if (typeof ThemeManager !== 'undefined') {
                ThemeManager.toggleDarkMode();
                updateDarkModeIcon();
                updateMobileDarkModeIcon();
            }
        });
    }

    // Dark mode toggle desde navbar (Mobile)
    const darkModeToggleMobile = document.getElementById('toggleDarkModeMobile');
    const darkModeIconMobile = document.getElementById('darkModeIconMobile');
    const darkModeTextMobile = document.getElementById('darkModeTextMobile');

    if (darkModeToggleMobile && darkModeIconMobile) {
        // Actualizar icono inicial móvil
        updateMobileDarkModeIcon();

        // Event listener para el botón móvil
        darkModeToggleMobile.addEventListener('click', function() {
            if (typeof ThemeManager !== 'undefined') {
                ThemeManager.toggleDarkMode();
                updateDarkModeIcon();
                updateMobileDarkModeIcon();
            }
        });
    }

    // Función para actualizar el icono según el estado (Desktop)
    function updateDarkModeIcon() {
        if (!darkModeIcon) return;

        if (typeof ThemeManager !== 'undefined' && ThemeManager.isDarkMode()) {
            darkModeIcon.className = 'fas fa-sun fa-lg'; // Sol cuando está en modo oscuro
        } else {
            darkModeIcon.className = 'fas fa-moon fa-lg'; // Luna cuando está en modo claro
        }
    }

    // Función para actualizar el icono según el estado (Mobile)
    function updateMobileDarkModeIcon() {
        if (!darkModeIconMobile || !darkModeTextMobile) return;

        if (typeof ThemeManager !== 'undefined' && ThemeManager.isDarkMode()) {
            darkModeIconMobile.className = 'fas fa-sun me-2'; // Sol cuando está en modo oscuro
            darkModeTextMobile.textContent = 'Modo Claro';
        } else {
            darkModeIconMobile.className = 'fas fa-moon me-2'; // Luna cuando está en modo claro
            darkModeTextMobile.textContent = 'Modo Oscuro';
        }
    }
});

