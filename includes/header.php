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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
<!-- Boostrap -->
    <link rel="stylesheet" href="/Zyro/assets/css/bootstrap.css">
    <script src="/Zyro/assets/js/bootstrap.bundle.min.js"></script>
<!-- Link style -->
    <link rel="stylesheet" href="/Zyro/assets/css/layout.css">
    <link rel="stylesheet" href="/Zyro/assets/css/style.css">
</head>
<body>
<!-- Menu top -->
    <div id="menu-top">
        <div id="left-menu-top">
            <a href="/Zyro/index.php"><img src="/Zyro/assets/img/Zyro-logo2.png" alt="Logo"></a>
        </div>
        <div id="right-menu-top">
            <ul>
                <li>
                    <?php if ($currentUser): ?>
                        <div class="dropdown">
                            <button type="button" id="drop-tk">
                                <span>Chào, <?php echo htmlspecialchars($currentUser['name'], ENT_QUOTES, 'UTF-8'); ?></span>
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
                    <?php else: ?>
                        <div class="dropdown">
                            <button type="button" id="drop-tk">
                                <span>Tài khoản</span>
                            </button>
                            <ul class="dropdown-menu" id="drop-tai-khoang" aria-labelledby="drop-tk">
                                <li>
                                    <div class="dp-tk">
                                        <a href="/Zyro/pages/DangNhap.php">Đăng nhập</a><br>
                                    </div>
                                    <div class="dp-tk">
                                        <a href="/Zyro/pages/DangKy.php">Đăng ký</a><br>
                                    </div>
                                    <div class="dp-tk">
                                        <a href="/Zyro/pages/LichSuDonHang.php">Tra cứu đơn hàng</a><br>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </li>
                <li>|</li>
                <li><a href="/Zyro/pages/LichSuDonHang.php">Tra cứu đơn hàng</a></li>
                <li>|</li>
                <li><a href="/Zyro/pages/GioiThieu.php">Giới thiệu & Tin tức</a></li>
                <li>|</li>
                <li><a href="/Zyro/pages/SanPham.php">Cửa hàng</a></li>
            </ul>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg navbar-light" id="menu-main">
        <div class="container-fluid">
            <div id="menu-main-left">
                <a class="navbar-brand" id="Logo-Zyro" href="/Zyro/index.php">ZYRO</a>
            </div>

<!-- Menu chính -->
            <div id="menu-main-center">
                <header class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <div class="dropdown">
                                <button class="btn-menu" type="button" id="drop-menu-1">
                                    <span>Mới & Nổi bật</span>
                                </button>
                                <ul class="dropdown-menu menu-drop" id="menu-drop-1" aria-labelledby="drop-menu-1">
                                    <li id="drop-menu1">
                                        <div class="drop" id="dp-1">
                                            <p><span class="h-drop">Nổi bật</span></p>
                                            <a href="#">Sản sắp ra mắt</a><br>
                                            <a href="#">Sản phẩm mới</a><br>
                                            <a href="#">Sản phẩm bán chạy nhất</a><br>
                                            <a href="#">Độc quyền cho thành viên</a><br>
                                            <a href="#">Tùy chỉnh với Zyro By You</a><br>
                                            <a href="#">Xu hướng hiện nay</a><br>
                                        </div>
                                        
                                        <div class="drop" id="dp-1">
                                            <p><span class="h-drop">Xu hướng</span></p>
                                            <a href="#">Xu hướng</a><br>
                                            <a href="#">Zyro 24.7</a><br>
                                            <a href="#">Xu hướng hiện tại</a><br>
                                            <a href="#">Chạy bộ cổ điển</a><br>
                                            <a href="#">Công cụ tìm giày chạy bộ</a><br>
                                        </div>

                                        <div class="drop" id="dp-1">
                                            <p><span class="h-drop">Biểu tượng cửa hàng</span></p>
                                            <a href="#">Phong cách sống</a><br>
                                            <a href="#">Air Force 1</a><br>
                                            <a href="#">Air Jordan 1</a><br>
                                            <a href="#">Air Max</a><br>
                                            <a href="#">Dunk</a><br>
                                            <a href="#">Cortez</a><br>
                                            <a href="#">Blazer</a><br>
                                            <a href="#">Pegasus</a><br>
                                            <a href="#">Vomero</a><br>
                                        </div>

                                        <div class="drop" id="dp-1">
                                            <p><span class="h-drop">Mua theo môn thể thao</span></p>
                                            <a href="#">Chạy bộ</a><br>
                                            <a href="#">Bóng rổ</a><br>
                                            <a href="#">Bóng đá</a><br>
                                            <a href="#">Golf</a><br>
                                            <a href="#">Quần vợt</a><br>
                                            <a href="#">Phòng tập thể dục và luyện tập</a><br>
                                            <a href="#">Yoga</a><br>
                                            <a href="#">Trượt ván</a><br>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <div class="dropdown">
                                <button class="btn-menu" type="button" id="drop-menu-2">
                                    <span>Nam</span>
                                </button>
                                <ul class="dropdown-menu menu-drop" id="menu-drop-2" aria-labelledby="drop-menu-2">
                                    <li id="drop-menu2">
                                        <div class="drop" id="dp-2">
                                            <p><span class="h-drop">Nổi bật</span></p>
                                            <a href="#">Hàng mới về</a><br>
                                            <a href="#">Bán chạy nhất</a><br>
                                            <a href="#">Mua tất cả Giảm giá</a><br>
                                        </div>

                                        <div class="drop" id="dp-2">
                                            <p><span class="h-drop">Giày</span></p>
                                            <a href="../php/SanPham-Giay-Nam.php?loai=all">Tất cả giày</a><br>
                                            <a href="#">Phong cách sống</a><br>
                                            <a href="#">Jordan</a><br>
                                            <a href="../php/SanPham-Giay-Nam.php?loai=Giày chạy bộ nam">Chạy bộ</a><br>
                                            <a href="#">Bóng đá</a><br>
                                            <a href="#">Bóng rổ</a><br>
                                            <a href="#">Phòng tập thể dục và luyện tập</a><br>
                                            <a href="#">Trượt ván</a><br>
                                            <a href="#">Zyro By You</a><br>
                                        </div>

                                        <div class="drop" id="dp-2">
                                            <p><span class="h-drop">Mua theo môn thể thao</span></p>
                                            <a href="#">Chạy bộ</a><br>
                                            <a href="#">Bóng rổ</a><br>
                                            <a href="#">Bóng đá</a><br>
                                            <a href="#">Golf</a><br>
                                            <a href="#">Quần vợt</a><br>
                                            <a href="#">Phòng tập thể dục và luyện tập</a><br>
                                            <a href="#">Yoga</a><br>
                                            <a href="#">Trượt ván</a><br>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <div class="dropdown">
                                <button class="btn-menu" type="button" id="drop-menu-3">
                                    <span>Nữ</span>
                                </button>
                                <ul class="dropdown-menu menu-drop" id="menu-drop-3" aria-labelledby="drop-menu-3">
                                    <li id="drop-menu3">
                                        <div class="drop" id="dp-3">
                                            <p><span class="h-drop">Nổi bật</span></p>
                                            <a href="#">Hàng mới về</a><br>
                                            <a href="#">Bán chạy nhất</a><br>
                                            <a href="#">Mua tất cả Giảm giá</a><br>
                                        </div>

                                        <div class="drop" id="dp-3">
                                            <p><span class="h-drop">Giày</span></p>
                                            <a href="#">Tất cả giày</a><br>
                                            <a href="#">Phong cách sống</a><br>
                                            <a href="#">Jordan</a><br>
                                            <a href="#">Chạy bộ</a><br>
                                            <a href="#">Bóng đá</a><br>
                                            <a href="#">Bóng rổ</a><br>
                                            <a href="#">Phòng tập thể dục và luyện tập</a><br>
                                            <a href="#">Trượt ván</a><br>
                                            <a href="#">Zyro By You</a><br>
                                        </div>

                                        <div class="drop" id="dp-3">
                                            <p><span class="h-drop">Mua theo môn thể thao</span></p>
                                            <a href="#">Yoga</a><br>
                                            <a href="#">Chạy bộ</a><br>
                                            <a href="#">Phòng tập thể dục và luyện tập</a><br>
                                            <a href="#">Bóng rổ</a><br>
                                            <a href="#">Quần vợt</a><br>
                                            <a href="#">Golf</a><br>
                                            <a href="#">Bóng đá</a><br>
                                            <a href="#">Trượt ván</a><br>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <div class="dropdown">
                                <button class="btn-menu" type="button" id="drop-menu-4">
                                    <span>Trẻ em</span>
                                </button>
                                <ul class="dropdown-menu menu-drop" id="menu-drop-4" aria-labelledby="drop-menu-4">
                                    <li id="drop-menu4">
                                        <div class="drop" id="dp-4">
                                            <p><span class="h-drop">Nổi bật</span></p>
                                            <a href="#">Hàng mới về</a><br>
                                            <a href="#">Bán chạy nhất</a><br>
                                            <a href="#">Quay lại trường học</a><br>
                                            <a href="#">Đồ thể thao</a><br>
                                            <a href="#">Phong cách sống</a><br>
                                        </div>

                                        <div class="drop" id="dp-4">
                                            <p><span class="h-drop">Giày</span></p>
                                            <a href="#">Tất cả giày</a><br>
                                            <a href="#">Phong cách sống</a><br>
                                            <a href="#">Jordan</a><br>
                                            <a href="#">Bóng đá</a><br>
                                            <a href="#">Chạy bộ</a><br>
                                            <a href="#">Bóng rổ</a><br>
                                        </div>

                                        <div class="drop" id="dp-4">
                                            <p><span class="h-drop">Trẻ em theo độ tuổi</span></p>
                                            <a href="#">Trẻ lớn hơn (7 - 14 tuổi)</a><br>
                                            <a href="#">Trẻ nhỏ hơn (4 - 7 tuổi)</a><br>
                                            <a href="#">Trẻ sơ sinh & trẻ mới biết đi (0 - 4 tuổi)</a><br><br>
                                            
                                            <p><span class="h-drop">Mua sắm theo môn thể thao</span></p>
                                            <a href="#">Bóng đá</a><br>
                                            <a href="#">Chạy bộ</a><br>
                                            <a href="#">Bóng rổ</a><br>
                                            <a href="#">Phòng tập thể dục và đào tạo</a><br>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                        <div class="dropdown">
                                <button class="btn-menu" type="button" id="drop-menu-5">
                                    <span>Ưu đãi</span>
                                </button>
                                <ul class="dropdown-menu menu-drop" id="menu-drop-5" aria-labelledby="drop-menu-5">
                                    <li id="drop-menu5">
                                        <div class="drop" id="dp-5">
                                            <p><span class="h-drop">Khuyến mại & Ưu đãi</span></p>
                                            <a href="#">Mua sắm tất cả Khuyến mại</a><br>
                                            <a href="#">Bán chạy nhất</a><br>
                                            <a href="#">Cơ hội cuối cùng</a><br>
                                        </div>

                                        <div class="drop" id="dp-5">
                                            <p><span class="h-drop">Khuyến mại dành cho nam</span></p>
                                            <a href="#">Giày</a><br>
                                            <a href="#">Quần áo</a><br>
                                            <a href="#">Phụ kiện và thiết bị</a><br>
                                        </div>

                                        <div class="drop" id="dp-5">
                                            <p><span class="h-drop">Khuyến mại dành cho nữ</span></p>
                                            <a href="#">Giày</a><br>
                                            <a href="#">Quần áo</a><br>
                                            <a href="#">Phụ kiện và thiết bị</a><br>
                                        </div>

                                        <div class="drop" id="dp-5">
                                            <p><span class="h-drop">Khuyến mại dành cho trẻ em</span></p>
                                            <a href="#">Giày</a><br>
                                            <a href="#">Quần áo</a><br>
                                            <a href="#">Phụ kiện và thiết bị</a><br>
                                        </div>

                                        <div class="drop" id="dp-5">
                                            <p><span class="h-drop">Mua sắm theo môn thể thao</span></p>
                                            <a href="#">Bóng đá</a><br>
                                            <a href="#">Chạy bộ</a><br>
                                            <a href="#">Bóng rổ</a><br>
                                            <a href="#">Phòng tập thể dục và đào tạo</a><br>
                                            <a href="#">Quần vợt</a><br>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </header>
            </div>

<!-- Tìm kiếm -->
            <div id="menu-main-right">
                <div id="box-search-menu-main">
                    <form id="tim-kiem">
                        <button type="submit" id="btn-search"><img id="icon-search" src="/Zyro/assets/icon/search.png" alt="search"></button>
                        <input type="search" id="txt-search" placeholder="Search" aria-label="Search">
                    </form>
                </div>

                <div class="box-icon-menu-main">
                    <a href="#"><img id="icon-like" src="/Zyro/assets/icon/like.png" alt="like"></a>
                </div>

                <div class="box-icon-menu-main" style="position: relative;">
                    <a href="/Zyro/pages/GioHang.php">
                        <img id="icon-bag" src="/Zyro/assets/icon/bag-shopping.png" alt="bag shopping">
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
</body>
</html>