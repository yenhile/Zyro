<?php
if (session_id() == '') {
    session_start();
}
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += isset($item['quantity']) ? (int)$item['quantity'] : 1;
    }
}
$currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
?>
<!-- Menu top -->
<div id="menu-top">
    <div id="left-menu-top">
        <a href="/Zyro/index.php"><img src="/Zyro/assets/img/Zyro-logo2.png" alt="Logo"></a>
    </div>
    <div id="right-menu-top">
        <ul>
            <?php if ($currentUser): ?>
                <li><a href="/Zyro/pages/DangXuat.php">Đăng xuất</a></li>
                <li>|</li>
                <li>
                    <div class="dropdown">
                        <button type="button" id="drop-tk">
                            <span>Chào, <?php echo htmlspecialchars($currentUser['name'], ENT_QUOTES, 'UTF-8'); ?> ▾</span>
                        </button>
                        <ul class="dropdown-menu" id="drop-tai-khoang" aria-labelledby="drop-tk">
                            <li>
                                <?php if (isset($currentUser['phanquyen']) && $currentUser['phanquyen'] == 0): ?>
                                    <div class="dp-tk">
                                        <a href="/Zyro/admin/index.php" style="color: #dc3545; font-weight: bold;">Trang Quản Trị</a><br>
                                    </div>
                                <?php endif; ?>
                                <div class="dp-tk">
                                    <a href="/Zyro/pages/GioHang.php">Giỏ hàng của tôi</a><br>
                                </div>
                                <div class="dp-tk">
                                    <a href="/Zyro/pages/LichSuDonHang.php">Đơn hàng của tôi</a><br>
                                </div>
                                <div class="dp-tk">
                                    <a href="/Zyro/pages/DangXuat.php">Đăng xuất</a><br>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>
            <?php else: ?>
                <li><a href="/Zyro/pages/DangKy.php">Đăng ký</a></li>
                <li>|</li>
                <li><a href="/Zyro/pages/DangNhap.php">Đăng nhập</a></li>
                <li>|</li>
                <li><a href="/Zyro/pages/LichSuDonHang.php">Tra cứu đơn hàng</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<nav class="navbar navbar-expand-lg navbar-light" id="menu-main">
    <div class="container-fluid">
        <div id="menu-main-left">
            <a class="navbar-brand" id="Logo-Zyro" href="/Zyro/index.php">ZYRO</a>
        </div>

        <!-- Menu chính tinh gọn (BA Senior & FullStack Optimized) -->
        <div id="menu-main-center">
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link nav-link-zyro" href="/Zyro/index.php">Trang Chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-zyro" href="/Zyro/pages/SanPham.php?loai=all">Tất Cả Sản Phẩm</a>
                    </li>
                    <li class="nav-item dropdown zyro-dropdown-item">
                        <a class="nav-link nav-link-zyro dropdown-toggle" href="javascript:void(0)" id="dropThuongHieu" role="button" aria-expanded="false">
                            Thương Hiệu
                        </a>
                        <ul class="dropdown-menu zyro-drop-menu shadow-sm" aria-labelledby="dropThuongHieu">
                            <li><a class="dropdown-item py-2" href="/Zyro/pages/SanPham.php?loai=Nike">Nike</a></li>
                            <li><a class="dropdown-item py-2" href="/Zyro/pages/SanPham.php?loai=Converse">Converse</a></li>
                            <li><a class="dropdown-item py-2" href="/Zyro/pages/SanPham.php?loai=Jordan">Jordan</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown zyro-dropdown-item">
                        <a class="nav-link nav-link-zyro dropdown-toggle" href="javascript:void(0)" id="dropLoaiGiay" role="button" aria-expanded="false">
                            Loại Giày
                        </a>
                        <ul class="dropdown-menu zyro-drop-menu shadow-sm" aria-labelledby="dropLoaiGiay">
                            <li><a class="dropdown-item py-2" href="/Zyro/pages/SanPham.php?loai=Giày chạy bộ">Giày Chạy Bộ</a></li>
                            <li><a class="dropdown-item py-2" href="/Zyro/pages/SanPham.php?loai=Giày đá banh">Giày Đá Banh</a></li>
                            <li><a class="dropdown-item py-2" href="/Zyro/pages/SanPham.php?loai=Giày bóng rổ">Giày Bóng Rổ</a></li>
                            <li><a class="dropdown-item py-2" href="/Zyro/pages/SanPham.php?loai=Giày Tennis">Giày Tennis</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-zyro" href="/Zyro/pages/GioiThieu.php">Giới Thiệu</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Giỏ hàng -->
        <div id="menu-main-right">
            <div class="box-icon-menu-main" style="position: relative;">
                <a href="/Zyro/pages/GioHang.php" title="Giỏ hàng của bạn">
                    <img id="icon-bag" src="/Zyro/assets/icon/bag-shopping.png" alt="Giỏ hàng">
                    <?php if ($cartCount > 0): ?>
                        <span class="badge-cart-count"><?php echo $cartCount > 99 ? '99+' : $cartCount; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Nút toggle menu cho mobile -->
        <button class="navbar-toggler" id="Nut-mobile" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<script>
// Logic mở Dropdown Select tương tác cao (Hỗ trợ Click lẫn Hover 100% tin cậy)
document.addEventListener("DOMContentLoaded", function () {
    const dropdownToggles = document.querySelectorAll('.zyro-dropdown-item > .dropdown-toggle');
    dropdownToggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const parent = this.parentElement;
            const menu = parent.querySelector('.dropdown-menu');
            const isCurrentlyShown = menu.classList.contains('show');

            // Đóng tất cả dropdown khác
            document.querySelectorAll('.zyro-dropdown-item .dropdown-menu').forEach(function (m) {
                m.classList.remove('show');
            });
            document.querySelectorAll('.zyro-dropdown-item > .dropdown-toggle').forEach(function (t) {
                t.setAttribute('aria-expanded', 'false');
            });

            // Nếu đang chưa mở thì mở ra
            if (!isCurrentlyShown) {
                menu.classList.add('show');
                this.setAttribute('aria-expanded', 'true');
            }
        });
    });

    // Đóng dropdown khi click ra ngoài màn hình
    document.addEventListener('click', function () {
        document.querySelectorAll('.zyro-dropdown-item .dropdown-menu').forEach(function (m) {
            m.classList.remove('show');
        });
        document.querySelectorAll('.zyro-dropdown-item > .dropdown-toggle').forEach(function (t) {
            t.setAttribute('aria-expanded', 'false');
        });
    });
});
</script>