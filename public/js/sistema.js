document.addEventListener("DOMContentLoaded", function () {
    const toggleDarkModeButton = document.getElementById("toggleDarkMode");
    const darkModeIcon = document.getElementById("darkModeIcon");
    const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)");
    const currentTheme = localStorage.getItem("theme");

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

    // Aplicar el tema guardado o el predeterminado del sistema
    if (currentTheme === "dark") {
        document.body.classList.add("dark-mode");
        updateIcon(true);
    } else if (currentTheme === "light") {
        document.body.classList.remove("dark-mode");
        updateIcon(false);
    } else if (prefersDarkScheme.matches) {
        document.body.classList.add("dark-mode");
        updateIcon(true);
    }

    // Cambiar el tema al hacer clic en el botón
    toggleDarkModeButton.addEventListener("click", function () {
        if (document.body.classList.contains("dark-mode")) {
            document.body.classList.remove("dark-mode");
            localStorage.setItem("theme", "light");
            updateIcon(false);
        } else {
            document.body.classList.add("dark-mode");
            localStorage.setItem("theme", "dark");
            updateIcon(true);
        }
    });
});
