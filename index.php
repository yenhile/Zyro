<?php
require_once("class/classProduct.php");

$productModel = new san_pham();

// Lấy danh sách sản phẩm theo thương hiệu
$nikeProducts = $productModel->lay_san_pham_trending(1);      // Nike (brand_id = 1)
$converseProducts = $productModel->lay_san_pham_trending(2);  // Converse (brand_id = 2)
$categories = $productModel->lay_danh_sach_loai_giay();       // Danh mục môn thể thao / loại giày
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZYRO — Giày Thể Thao Chính Hãng (Nike, Converse, Jordan)</title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="/Zyro/assets/css/bootstrap.css">
    <!-- Style tùy chỉnh -->
    <link rel="stylesheet" href="/Zyro/assets/css/layout.css">
    <link rel="stylesheet" href="/Zyro/assets/css/style.css">
    <script src="/Zyro/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/Zyro/assets/js/action.js"></script>
</head>
<body>
<!-- Header dùng chung -->
    <?php include ('includes/header.php'); ?>

<!-- Slide Banner Giới Thiệu -->
    <div id="carouselExampleDark" class="carousel carousel-dark slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active" data-bs-interval="6000">
                <a href="/Zyro/pages/SanPham.php?brand=1">
                    <img src="/Zyro/assets/img/banner-nike-air-max-dn8.png" class="d-block w-100" id="banner-nike" alt="Nike Air Max Dn8">
                </a>
            </div>
            <div class="carousel-item" data-bs-interval="4000">
                <a href="/Zyro/pages/SanPham.php?brand=2">
                    <img src="/Zyro/assets/img/banner-converse-seasonal_color_eng_main_desktop.webp" class="d-block w-100" alt="Converse Seasonal">
                </a>
            </div>
            <div class="carousel-item" data-bs-interval="4000">
                <a href="/Zyro/pages/SanPham.php?brand=2">
                    <img src="/Zyro/assets/img/banner-converse-love_chuck_eng_main_desktop.webp" class="d-block w-100" alt="Converse Love Chuck">
                </a>
            </div>
        </div>
        <button class="carousel-control-prev" id="banner-prev" type="button" data-bs-target="#carouselExampleDark" data-bs-slide="prev">
            <img src="/Zyro/assets/icon/icon-prev.png" class="icon-next-prev" alt="Prev">
        </button>
        <button class="carousel-control-next" id="banner-next" type="button" data-bs-target="#carouselExampleDark" data-bs-slide="next">
            <img src="/Zyro/assets/icon/icon-next.png" class="icon-next-prev" alt="Next">
        </button>
    </div>

<!-- Content Chính -->
    <div class="container">

<!-- 1. Mua sắm theo biểu tượng: Nike -->
        <div class="container-reveiw-san-pham">
            <h3>Mua sắm theo biểu tượng: Nike</h3>
            <button class="prev-reveiw" type="button"><img class="icon-next-prev" src="/Zyro/assets/icon/icon-prev.png" alt="Prev"></button>
            <button class="next-reveiw" type="button"><img class="icon-next-prev" src="/Zyro/assets/icon/icon-next.png" alt="Next"></button>
            <div class="box-reveiw">
                <div class="sp-reveiw"><a href="/Zyro/pages/SanPham.php?brand=1"><img src="/Zyro/assets/img/review-sp-nike-VOMERO.png" alt="Vomero 5"></a></div>
                <div class="sp-reveiw"><a href="/Zyro/pages/SanPham.php?brand=1"><img src="/Zyro/assets/img/review-sp-nike-PEGASUS41.png" alt="Pegasus 41"></a></div>
                <div class="sp-reveiw"><a href="/Zyro/pages/SanPham.php?brand=1"><img src="/Zyro/assets/img/review-sp-nike-BLAZER.png" alt="Blazer"></a></div>
                <div class="sp-reveiw"><a href="/Zyro/pages/SanPham.php?brand=1"><img src="/Zyro/assets/img/review-sp-nike-DUNKS.png" alt="Dunks"></a></div>
                <div class="sp-reveiw"><a href="/Zyro/pages/SanPham.php?brand=1"><img src="/Zyro/assets/img/review-sp-nike-METCON.png" alt="Metcon"></a></div>
                <div class="sp-reveiw"><a href="/Zyro/pages/SanPham.php?brand=1"><img src="/Zyro/assets/img/review-sp-nike-V2K.png" alt="V2K"></a></div>
                <div class="sp-reveiw"><a href="/Zyro/pages/SanPham.php?brand=3"><img src="/Zyro/assets/img/review-sp-nike-AIR-JORDAN1.png" alt="Air Jordan 1"></a></div>
            </div>
        </div>

<!-- 2. Sản phẩm trending Nike (Dynamic từ CSDL zyro_db) -->
        <div class="container-reveiw-san-pham">
            <h3>Xu hướng hiện nay: Nike</h3>
            <button class="prev-reveiw" id="trend-prev-nike" type="button"><img class="icon-next-prev" src="/Zyro/assets/icon/icon-prev.png" alt="Prev"></button>
            <button class="next-reveiw" id="trend-next-nike" type="button"><img class="icon-next-prev" src="/Zyro/assets/icon/icon-next.png" alt="Next"></button>
            <div class="box-reveiw" id="box-trend-nike">
                <?php if (!empty($nikeProducts)): ?>
                    <?php foreach ($nikeProducts as $row): 
                        $pid = (int)$row['product_id'];
                        $pname = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                        $pdesc = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
                        $pcat = htmlspecialchars($row['category_name'] ? $row['category_name'] : 'Giày thể thao nam/nữ', ENT_QUOTES, 'UTF-8');
                        $pprice = number_format($row['price'], 0, ',', '.');
                        $pimg = htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8');
                    ?>
                        <div class="sp-reveiw">
                            <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo $pid; ?>">
                                <div class="product-img-wrap">
                                    <img src="/Zyro/assets/img/<?php echo $pimg; ?>" alt="<?php echo $pname; ?>">
                                </div>
                                <div class="trend-sp-thong-tin">
                                    <div class="trend-sp-name"><?php echo $pname; ?></div>
                                    <div class="trend-sp-mo-ta-ngan"><?php echo $pcat; ?></div>
                                    <div class="trend-sp-gia"><span><?php echo $pprice; ?></span>₫</div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted p-3">Đang cập nhật sản phẩm xu hướng Nike...</p>
                <?php endif; ?>
            </div>
        </div>

<!-- 3. Mua sắm theo môn thể thao / Loại giày -->
        <div class="container-reveiw-san-pham">
            <h3>Mua sắm theo môn thể thao</h3>
            <button class="prev-reveiw" id="loai-the-thao-prev" type="button"><img class="icon-next-prev" src="/Zyro/assets/icon/icon-prev.png" alt="Prev"></button>
            <button class="next-reveiw" id="loai-the-thao-next" type="button"><img class="icon-next-prev" src="/Zyro/assets/icon/icon-next.png" alt="Next"></button>
            <div class="box-reveiw" id="box-loai-the-thao">
                <div class="sp-reveiw"><a href="/Zyro/pages/SanPham.php?category=1"><img src="/Zyro/assets/img/nike-loaiTT-chay-bo.jpg" alt="Chạy bộ"> <div class="cta-loai-the-thao">Chạy bộ</div></a></div>
                <div class="sp-reveiw"><a href="/Zyro/pages/SanPham.php?category=2"><img src="/Zyro/assets/img/nike-loaiTT-da-bong.jpg" alt="Đá banh"> <div class="cta-loai-the-thao">Đá banh</div></a></div>
                <div class="sp-reveiw"><a href="/Zyro/pages/SanPham.php?category=3"><img src="/Zyro/assets/img/nike-loaiTT-bong-ro.jpg" alt="Bóng rổ"> <div class="cta-loai-the-thao">Bóng rổ</div></a></div>
                <div class="sp-reveiw"><a href="/Zyro/pages/SanPham.php?category=5"><img src="/Zyro/assets/img/nike-just-tenis.jpg" alt="Tennis"> <div class="cta-loai-the-thao">Tennis</div></a></div>
                <div class="sp-reveiw"><a href="/Zyro/pages/SanPham.php?category=4"><img src="/Zyro/assets/img/nike-loaiTT-gym.jpg" alt="Gym & Thể hình"> <div class="cta-loai-the-thao">Gym</div></a></div>
                <div class="sp-reveiw"><a href="/Zyro/pages/SanPham.php?category=4"><img src="/Zyro/assets/img/nike-loaiTT-truot-van.jpg" alt="Trượt ván"> <div class="cta-loai-the-thao">Trượt ván</div></a></div>
                <div class="sp-reveiw"><a href="/Zyro/pages/SanPham.php?category=4"><img src="/Zyro/assets/img/nike-loaiTT-yoga.jpg" alt="Yoga"> <div class="cta-loai-the-thao">Yoga</div></a></div>
                <div class="sp-reveiw"><a href="/Zyro/pages/SanPham.php?category=4"><img src="/Zyro/assets/img/nike-loaiTT-dance.jpg" alt="Dance"> <div class="cta-loai-the-thao">Dance</div></a></div>
            </div>
        </div>

<!-- 4. Sản phẩm trending Converse (Dynamic từ CSDL zyro_db) -->
        <div class="container-reveiw-san-pham">
            <h3>Xu hướng hiện nay: Converse</h3>
            <button class="prev-reveiw" id="trend-prev-converse" type="button"><img class="icon-next-prev" src="/Zyro/assets/icon/icon-prev.png" alt="Prev"></button>
            <button class="next-reveiw" id="trend-next-converse" type="button"><img class="icon-next-prev" src="/Zyro/assets/icon/icon-next.png" alt="Next"></button>
            <div class="box-reveiw" id="box-trend-converse">
                <?php if (!empty($converseProducts)): ?>
                    <?php foreach ($converseProducts as $row): 
                        $pid = (int)$row['product_id'];
                        $pname = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                        $pdesc = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
                        $pcat = htmlspecialchars($row['category_name'] ? $row['category_name'] : 'Giày Converse cổ điển', ENT_QUOTES, 'UTF-8');
                        $pprice = number_format($row['price'], 0, ',', '.');
                        $pimg = htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8');
                    ?>
                        <div class="sp-reveiw">
                            <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo $pid; ?>">
                                <div class="product-img-wrap">
                                    <img src="/Zyro/assets/img/<?php echo $pimg; ?>" alt="<?php echo $pname; ?>">
                                </div>
                                <div class="trend-sp-thong-tin">
                                    <div class="trend-sp-name"><?php echo $pname; ?></div>
                                    <div class="trend-sp-mo-ta-ngan"><?php echo $pcat; ?></div>
                                    <div class="trend-sp-gia"><span><?php echo $pprice; ?></span>₫</div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted p-3">Đang cập nhật sản phẩm xu hướng Converse...</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

<!-- Footer dùng chung -->
    <?php include ('includes/footer.php'); ?>
</body>
</html>