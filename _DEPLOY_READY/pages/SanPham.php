<?php
require_once("../class/classProduct.php");
$p = new san_pham();
$loai = isset($_GET['loai']) ? trim($_GET['loai']) : (isset($_GET['brand']) ? trim($_GET['brand']) : (isset($_GET['category']) ? trim($_GET['category']) : 'all'));
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
    <!-- Link style với cache-busting -->
    <link rel="stylesheet" href="/Zyro/assets/css/layout.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/Zyro/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/Zyro/assets/css/san-pham.css?v=<?php echo time(); ?>">
    <script src="/Zyro/assets/js/action.js"></script>
</head>

<body>
<!-- Header -->
    <?php include ('../includes/header.php'); ?>
    <main class="page-content-wrapper">
<!-- Content -->
    <div class="container my-4">
        <div class="filters">
            <p class="titile-filters">Danh Mục Giày <span>(ZYRO)</span></p>
            <div class="loai-san-pham">
                <p><a href="/Zyro/pages/SanPham.php?loai=all" class="<?php echo ($loai === 'all' || empty($loai)) ? 'fw-bold text-dark' : ''; ?>">Tất cả sản phẩm</a></p>
                <p><a href="/Zyro/pages/SanPham.php?loai=Giày chạy bộ" class="<?php echo ($loai === 'Giày chạy bộ') ? 'fw-bold text-dark' : ''; ?>">Chạy bộ</a></p>
                <p><a href="/Zyro/pages/SanPham.php?loai=Giày đá banh" class="<?php echo ($loai === 'Giày đá banh') ? 'fw-bold text-dark' : ''; ?>">Đá banh</a></p>
                <p><a href="/Zyro/pages/SanPham.php?loai=Giày bóng rổ" class="<?php echo ($loai === 'Giày bóng rổ') ? 'fw-bold text-dark' : ''; ?>">Bóng rổ</a></p>
                <p><a href="/Zyro/pages/SanPham.php?loai=Giày Tennis" class="<?php echo ($loai === 'Giày Tennis') ? 'fw-bold text-dark' : ''; ?>">Tennis</a></p>
            </div>

            <div class="box-filters">
                <button class="btn-drop-filters" onclick="toggleFilters(this)">Nhãn hiệu</button>
                <div class="drop-filters">
                    <p><a href="/Zyro/pages/SanPham.php?loai=Nike" class="<?php echo ($loai === 'Nike' || $loai === '1') ? 'fw-bold text-dark' : ''; ?>">Nike</a></p>
                    <p><a href="/Zyro/pages/SanPham.php?loai=Converse" class="<?php echo ($loai === 'Converse' || $loai === '2') ? 'fw-bold text-dark' : ''; ?>">Converse</a></p>
                    <p><a href="/Zyro/pages/SanPham.php?loai=Jordan" class="<?php echo ($loai === 'Jordan' || $loai === '3') ? 'fw-bold text-dark' : ''; ?>">Jordan</a></p>
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
    </main>
<!-- Footer -->
    <?php include ('../includes/footer.php'); ?>
</body>

</html>