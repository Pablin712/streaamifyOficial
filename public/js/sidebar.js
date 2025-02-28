document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.querySelector("#layoutSidenav_nav");
    const toggleBtn = document.querySelector("#toggleSidebar");

    toggleBtn.addEventListener("click", function () {
        sidebar.classList.toggle("show");
    });

    if (window.innerWidth > 768) {
        sidebar.classList.add("collapsed");
    }
});