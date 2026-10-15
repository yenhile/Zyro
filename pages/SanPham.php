<?php
require_once("../class/classProduct.php");
$p = new san_pham();
$loai = isset($_GET['loai']) ? trim($_GET['loai']) : 'all';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản Phẩm Giày Thể Thao — ZYRO</title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="/Zyro/assets/css/bootstrap.css">
    <script src="/Zyro/assets/js/bootstrap.bundle.min.js"></script>
    <!-- Link style -->
    <link rel="stylesheet" href="/Zyro/assets/css/layout.css">
    <link rel="stylesheet" href="/Zyro/assets/css/style.css">
    <link rel="stylesheet" href="/Zyro/assets/css/san-pham.css">
    <script src="/Zyro/assets/js/action.js"></script>
</head>

<body>
<!-- Header -->
    <?php include ('../includes/header.php'); ?>
<!-- Content -->
    <div class="container my-4">
        <div class="filters">
            <p class="titile-filters">Danh Mục Giày <span>(ZYRO)</span></p>
            <div class="loai-san-pham">
                <p><a href="/Zyro/pages/SanPham.php?loai=all">Tất cả sản phẩm</a></p>
                <p><a href="/Zyro/pages/SanPham.php?loai=Giày chạy bộ">Chạy bộ</a></p>
                <p><a href="/Zyro/pages/SanPham.php?loai=Giày đá banh">Đá banh</a></p>
                <p><a href="/Zyro/pages/SanPham.php?loai=Giày bóng rổ">Bóng rổ</a></p>
                <p><a href="/Zyro/pages/SanPham.php?loai=Giày Tennis">Tennis</a></p>
            </div>

            <div class="box-filters">
                <button class="btn-drop-filters" onclick="toggleFilters(this)">Nhãn hiệu</button>
                <div class="drop-filters">
                    <p><a href="/Zyro/pages/SanPham.php?loai=Nike">Nike</a></p>
                    <p><a href="/Zyro/pages/SanPham.php?loai=Converse">Converse</a></p>
                    <p><a href="/Zyro/pages/SanPham.php?loai=Jordan">Jordan</a></p>
                </div>
            </div>

            <div class="box-filters">
                <button class="btn-drop-filters" onclick="toggleFilters(this)">Kiểu dáng</button>
                <div class="drop-filters">
                    <p><input type="checkbox"> <span>Cổ Thấp (Low-top)</span></p>
                    <p><input type="checkbox"> <span>Cổ Cao (High-top)</span></p>
                </div>
            </div>

        </div>
        <div class="box-danh-sach-sp">
            <div class="box-danh-sach-3-sp">
                <?php
                $p->xuat_san_pham($loai);
                ?>
            </div>

        </div>
    </div>
<!-- Footer -->
    <?php include ('../includes/footer.php'); ?>
</body>

</html>