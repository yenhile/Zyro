// 1. Reveiw Mua sắm theo biểu tượng (Nike & Jordan)
document.addEventListener("DOMContentLoaded", function () {
    const slider = document.querySelector('#box-shop-icons') || document.querySelector('.box-reveiw');
    const prevButton = document.querySelector('#icon-shoe-prev') || document.querySelector('.prev-reveiw');
    const nextButton = document.querySelector('#icon-shoe-next') || document.querySelector('.next-reveiw');
    const scrollbarThumb = document.querySelector('.scrollbar-thumb');

    if (!slider || !prevButton || !nextButton) return;

    const slideWidth = 420; // Khoảng cách cuộn ~2 biểu tượng

    function updateScrollbar() {
        if (!scrollbarThumb) return;
        const maxScrollLeft = slider.scrollWidth - slider.clientWidth;
        if (maxScrollLeft <= 0) return;
        const scrollPercentage = slider.scrollLeft / maxScrollLeft;
        scrollbarThumb.style.left = `${scrollPercentage * 80}%`;
    }

    prevButton.addEventListener('click', () => {
        slider.scrollLeft -= slideWidth;
        updateScrollbar();
    });

    nextButton.addEventListener('click', () => {
        slider.scrollLeft += slideWidth;
        updateScrollbar();
    });

    slider.addEventListener('scroll', updateScrollbar);
});

// 2. Reveiw sản phẩm Trending Nike
document.addEventListener("DOMContentLoaded", function () {
    const slider = document.querySelector('#box-trend-nike');
    const prevButton_trend = document.querySelector('#trend-prev-nike');
    const nextButton_trend = document.querySelector('#trend-next-nike');
    const scrollbarThumb = document.querySelector('.scrollbar-thumb');

    if (!slider || !prevButton_trend || !nextButton_trend) return;

    const slideWidth = slider.clientWidth / 3;

    function updateScrollbar() {
        if (!scrollbarThumb) return;
        const maxScrollLeft = slider.scrollWidth - slider.clientWidth;
        if (maxScrollLeft <= 0) return;
        const scrollPercentage = slider.scrollLeft / maxScrollLeft;
        scrollbarThumb.style.left = `${scrollPercentage * 80}%`;
    }

    prevButton_trend.addEventListener('click', () => {
        slider.scrollLeft -= slideWidth;
        updateScrollbar();
    });

    nextButton_trend.addEventListener('click', () => {
        slider.scrollLeft += slideWidth;
        updateScrollbar();
    });

    slider.addEventListener('scroll', updateScrollbar);
});

// 3. Mua sắm theo môn thể thao / Loại thể thao
document.addEventListener("DOMContentLoaded", function () {
    const slider = document.querySelector('#box-loai-the-thao');
    const prevButton = document.querySelector('#loai-the-thao-prev');
    const nextButton = document.querySelector('#loai-the-thao-next');
    const scrollbarThumb = document.querySelector('.scrollbar-thumb');

    if (!slider || !prevButton || !nextButton) return;

    const slideWidth = slider.clientWidth / 3;

    function updateScrollbar() {
        if (!scrollbarThumb) return;
        const maxScrollLeft = slider.scrollWidth - slider.clientWidth;
        if (maxScrollLeft <= 0) return;
        const scrollPercentage = slider.scrollLeft / maxScrollLeft;
        scrollbarThumb.style.left = `${scrollPercentage * 80}%`;
    }

    prevButton.addEventListener('click', () => {
        slider.scrollLeft -= slideWidth;
        updateScrollbar();
    });

    nextButton.addEventListener('click', () => {
        slider.scrollLeft += slideWidth;
        updateScrollbar();
    });

    slider.addEventListener('scroll', updateScrollbar);
});

// 4. Reveiw sản phẩm Trending Converse
document.addEventListener("DOMContentLoaded", function () {
    const slider = document.querySelector('#box-trend-converse');
    const prevButton_trend = document.querySelector('#trend-prev-converse');
    const nextButton_trend = document.querySelector('#trend-next-converse');
    const scrollbarThumb = document.querySelector('.scrollbar-thumb');

    if (!slider || !prevButton_trend || !nextButton_trend) return;

    const slideWidth = slider.clientWidth / 3;

    function updateScrollbar() {
        if (!scrollbarThumb) return;
        const maxScrollLeft = slider.scrollWidth - slider.clientWidth;
        if (maxScrollLeft <= 0) return;
        const scrollPercentage = slider.scrollLeft / maxScrollLeft;
        scrollbarThumb.style.left = `${scrollPercentage * 80}%`;
    }

    prevButton_trend.addEventListener('click', () => {
        slider.scrollLeft -= slideWidth;
        updateScrollbar();
    });

    nextButton_trend.addEventListener('click', () => {
        slider.scrollLeft += slideWidth;
        updateScrollbar();
    });

    slider.addEventListener('scroll', updateScrollbar);
});

// Toggle filter trên trang sản phẩm
function toggleFilters(button) {
    const dropFilters = button.nextElementSibling;
    if (!dropFilters) return;

    const allDropFilters = document.querySelectorAll('.drop-filters');
    allDropFilters.forEach(menu => {
        if (menu !== dropFilters) {
            menu.classList.remove('show');
        }
    });

    dropFilters.classList.toggle('show');
}
