
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng</title>
<!-- Boostrap -->
    <link rel="stylesheet" href="../css/bootstrap.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
<!-- Link style -->
    <link rel="stylesheet" href="../css/layout.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/san-pham.css">
    <script src="../js/action.js"></script>
</head>

<body>
<!-- Header -->
    <?php include ('../includes/header.php'); ?>
<!-- Content -->
    <div class="container">
        <div class="filters">
            <p class="titile-filters">Giày nam <span>(100)</span></p>
            <div class="loai-san-pham">
                <p><a href="#">Chạy bộ</a></p>
                <p><a href="#">Tennis</a></p>
            </div>

            <div class="box-filters">
            <button class="btn-drop-filters" onclick="toggleFilters(this)">Nhãn hiệu</button>
                <div class="drop-filters">
                    <p><a href="#">NIKE Sportswear</a></p>
                    <p><a href="#">Jordan</a></p>
                    <p><a href="#">NIKE By You</a></p>
                    <p><a href="#">NIKE Lap</a></p>
                </div>
            </div>

            <div class="box-filters">
                <button class="btn-drop-filters" onclick="toggleFilters(this)">Chiều cao</button>
                <div class="drop-filters">
                    <p><input type="checkbox"> <span>Cổ Thấp</span></p>
                    <p><input type="checkbox"> <span>Cổ Cao</span></p>
                </div>
            </div>

        </div>
        <div class="box-danh-sach-sp">
            <div class="box-danh-sach-3-sp">
                <?php
                $p->xuat_san_pham();
                ?>
            </div>

        </div>
    </div>
<!-- Footer -->
    <?php include ('../includes/footer.php'); ?>
</body>

</html>