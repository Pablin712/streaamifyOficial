document.addEventListener("DOMContentLoaded", function () {
    const toggleDarkModeButton = document.getElementById("toggleDarkMode");
    const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)");
    const currentTheme = localStorage.getItem("theme");

    // Aplicar el tema guardado o el predeterminado del sistema
    if (currentTheme === "dark") {
        document.body.classList.add("dark-mode");
    } else if (currentTheme === "light") {
        document.body.classList.remove("dark-mode");
    } else if (prefersDarkScheme.matches) {
        document.body.classList.add("dark-mode");
    }

    // Cambiar el tema al hacer clic en el botón
    toggleDarkModeButton.addEventListener("click", function () {
        if (document.body.classList.contains("dark-mode")) {
            document.body.classList.remove("dark-mode");
            localStorage.setItem("theme", "light");
        } else {
            document.body.classList.add("dark-mode");
            localStorage.setItem("theme", "dark");
        }
    });
});
