document.addEventListener("DOMContentLoaded", function () {
    const btnToggle = document.getElementById("btn-sidebar");
    const sidebar = document.getElementById("sidebar");

    if (btnToggle && sidebar && !btnToggle.dataset.bound) {
        btnToggle.dataset.bound = "true";
        btnToggle.addEventListener("click", function (e) {
            e.preventDefault();
            sidebar.classList.toggle("collapsed");
        });
    }
});
