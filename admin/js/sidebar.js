document.addEventListener("DOMContentLoaded", function () {
    const btnToggle = document.getElementById("btn-sidebar");
    const sidebar = document.getElementById("sidebar");

    if (btnToggle && sidebar) {
        btnToggle.addEventListener("click", function () {
            sidebar.classList.toggle("collapsed");
        });
    }
});
