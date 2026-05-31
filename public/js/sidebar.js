document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.querySelector("#layoutSidenav_nav");
    const toggleBtn = document.querySelector("#toggleSidebar");

    if (toggleBtn) {
        toggleBtn.addEventListener("click", function () {
            sidebar.classList.toggle("show");
        });
    }
});