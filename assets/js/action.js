// Reveiw sản phẩm
document.addEventListener("DOMContentLoaded", function () {
    const slider = document.querySelector('.box-reveiw');
    const prevButton = document.querySelector('.prev-reveiw');
    const nextButton = document.querySelector('.next-reveiw');
    const scrollbarThumb = document.querySelector('.scrollbar-thumb');

    const slideWidth = slider.clientWidth / 3; // Lấy chiều rộng của 1 sản phẩm (hiển thị 3 sp)
    const maxScrollLeft = slider.scrollWidth - slider.clientWidth; // Giới hạn cuộn tối đa



    // Cập nhật vị trí thanh trượt
    function updateScrollbar() {
        const scrollPercentage = slider.scrollLeft / maxScrollLeft;
        scrollbarThumb.style.left = `${scrollPercentage * 80}%`; // Thanh trượt di chuyển
    }

    prevButton.addEventListener('click', () => {
        slider.scrollLeft -= slideWidth;
        updateScrollbar();
    });

    nextButton.addEventListener('click', () => {
        slider.scrollLeft += slideWidth;
        updateScrollbar();
    });

    // Cập nhật thanh cuộn khi kéo chuột
    slider.addEventListener('scroll', updateScrollbar);
});


// Reveiw sản phẩm Nike
document.addEventListener("DOMContentLoaded", function () {
    const slider = document.querySelector('#box-trend-nike');
    // Sản phẩm trending
    const prevButton_trend = document.querySelector('#trend-prev-nike');
    const nextButton_trend = document.querySelector('#trend-next-nike');
    const scrollbarThumb = document.querySelector('.scrollbar-thumb');

    const slideWidth = slider.clientWidth / 3; // Lấy chiều rộng của 1 sản phẩm (hiển thị 3 sp)
    const maxScrollLeft = slider.scrollWidth - slider.clientWidth; // Giới hạn cuộn tối đa



    // Cập nhật vị trí thanh trượt
    function updateScrollbar() {
        const scrollPercentage = slider.scrollLeft / maxScrollLeft;
        scrollbarThumb.style.left = `${scrollPercentage * 80}%`; // Thanh trượt di chuyển
    }

    prevButton_trend.addEventListener('click', () => {
        slider.scrollLeft -= slideWidth;
        updateScrollbar();
    });

    nextButton_trend.addEventListener('click', () => {
        slider.scrollLeft += slideWidth;
        updateScrollbar();
    });

    // Cập nhật thanh cuộn khi kéo chuột
    slider.addEventListener('scroll', updateScrollbar);
});

// Loại thể thao
document.addEventListener("DOMContentLoaded", function () {
    const slider = document.querySelector('#box-loai-the-thao');
    // Sản phẩm trending
    const prevButton = document.querySelector('#loai-the-thao-prev');
    const nextButton = document.querySelector('#loai-the-thao-next');
    const scrollbarThumb = document.querySelector('.scrollbar-thumb');

    const slideWidth = slider.clientWidth / 3; // Lấy chiều rộng của 1 sản phẩm (hiển thị 3 sp)
    const maxScrollLeft = slider.scrollWidth - slider.clientWidth; // Giới hạn cuộn tối đa



    // Cập nhật vị trí thanh trượt
    function updateScrollbar() {
        const scrollPercentage = slider.scrollLeft / maxScrollLeft;
        scrollbarThumb.style.left = `${scrollPercentage * 80}%`; // Thanh trượt di chuyển
    }

    prevButton.addEventListener('click', () => {
        slider.scrollLeft -= slideWidth;
        updateScrollbar();
    });

    nextButton.addEventListener('click', () => {
        slider.scrollLeft += slideWidth;
        updateScrollbar();
    });

    // Cập nhật thanh cuộn khi kéo chuột
    slider.addEventListener('scroll', updateScrollbar);
});


// Reveiw sản phẩm Converse
document.addEventListener("DOMContentLoaded", function () {
    const slider = document.querySelector('#box-trend-converse');
    // Sản phẩm trending
    const prevButton_trend = document.querySelector('#trend-prev-converse');
    const nextButton_trend = document.querySelector('#trend-next-converse');
    const scrollbarThumb = document.querySelector('.scrollbar-thumb');

    const slideWidth = slider.clientWidth / 3; // Lấy chiều rộng của 1 sản phẩm (hiển thị 3 sp)
    const maxScrollLeft = slider.scrollWidth - slider.clientWidth; // Giới hạn cuộn tối đa



    // Cập nhật vị trí thanh trượt
    function updateScrollbar() {
        const scrollPercentage = slider.scrollLeft / maxScrollLeft;
        scrollbarThumb.style.left = `${scrollPercentage * 80}%`; // Thanh trượt di chuyển
    }

    prevButton_trend.addEventListener('click', () => {
        slider.scrollLeft -= slideWidth;
        updateScrollbar();
    });

    nextButton_trend.addEventListener('click', () => {
        slider.scrollLeft += slideWidth;
        updateScrollbar();
    });

    // Cập nhật thanh cuộn khi kéo chuột
    slider.addEventListener('scroll', updateScrollbar);
});


document.addEventListener("DOMContentLoaded", function () {
    const icons = document.querySelectorAll('.like-sp-icon');
    icons.forEach(icon => {
        icon.addEventListener('click', () => {
            icon.classList.toggle('liked');
        });
    });
});


function toggleFilters(button) {
    // Tìm phần tử thả xuống (là anh chị em của nút)
    const dropFilters = button.nextElementSibling;

    // Đóng tất cả các menu khác trước
    const allDropFilters = document.querySelectorAll('.drop-filters');
    allDropFilters.forEach(menu => {
        if (menu !== dropFilters) {
            menu.classList.remove('show');
        }
    });

    // Chuyển đổi trạng thái hiển thị của menu hiện tại
    dropFilters.classList.toggle('show');
}

