<?php
require_once("class/classProduct.php");

$productModel = new san_pham();

// Lấy danh sách sản phẩm bán chạy nhất (Top 10 CSDL donhang_chitiet)
$bestSellerProducts = $productModel->lay_danh_sach_ban_chay(10);

// Lấy danh sách sản phẩm bán chạy theo thương hiệu (4 sản phẩm mỗi hãng cho bố cục cân đối)
$nikeProducts = $productModel->lay_san_pham_trending(1, 4);      // Nike (brand_id = 1)
$converseProducts = $productModel->lay_san_pham_trending(2, 4);  // Converse (brand_id = 2)
$jordanProducts = $productModel->lay_san_pham_trending(3, 4);    // Jordan (brand_id = 3)
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZYRO — Giày Thể Thao Chính Hãng (Nike, Converse, Jordan)</title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="/Zyro/assets/css/bootstrap.css">
    <!-- Style tùy chỉnh có cache-busting -->
    <link rel="stylesheet" href="/Zyro/assets/css/layout.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/Zyro/assets/css/style.css?v=<?php echo time(); ?>">
    <script src="/Zyro/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/Zyro/assets/js/action.js"></script>
</head>
<body>
<!-- Header dùng chung -->
    <?php include ('includes/header.php'); ?>

    <main class="page-content-wrapper">
    <!-- Slide Banner Giới Thiệu -->
    <div id="carouselExampleDark" class="carousel carousel-dark slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active" data-bs-interval="6000">
                <a href="/Zyro/pages/SanPham.php?loai=Nike">
                    <img src="/Zyro/assets/img/banner-nike-air-max-dn8.png" class="d-block w-100" id="banner-nike" alt="Nike Air Max Dn8">
                </a>
            </div>
            <div class="carousel-item" data-bs-interval="4000">
                <a href="/Zyro/pages/SanPham.php?loai=Converse">
                    <img src="/Zyro/assets/img/banner-converse-seasonal_color_eng_main_desktop.webp" class="d-block w-100" alt="Converse Seasonal">
                </a>
            </div>
            <div class="carousel-item" data-bs-interval="4000">
                <a href="/Zyro/pages/SanPham.php?loai=Converse">
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

    <!-- Danh Sách Sản Phẩm Nổi Bật (100% CSDL zyro_db) -->
    <div class="container py-4">

        <!-- 1. Top 10 Sản Phẩm Bán Chạy Nhất (Best Sellers) -->
        <section class="mb-5">
            <div class="zyro-section-header">
                <div>
                    <h2 class="zyro-section-title">🔥 Top 10 Sản Phẩm Bán Chạy Nhất</h2>
                    <div class="text-muted small mt-1">Cập nhật tự động dựa trên tổng số lượng đơn hàng thực tế</div>
                </div>
                <a href="/Zyro/pages/SanPham.php?loai=all" class="zyro-section-link">Xem tất cả sản phẩm →</a>
            </div>
            <div class="row g-3 g-md-4">
                <?php if (!empty($bestSellerProducts)): ?>
                    <?php $rank = 1; foreach ($bestSellerProducts as $row): 
                        $pid = (int)$row['product_id'];
                        $pname = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                        $pbrand = htmlspecialchars($row['brand_name'] ? $row['brand_name'] : 'ZYRO', ENT_QUOTES, 'UTF-8');
                        $pcat = htmlspecialchars($row['category_name'] ? $row['category_name'] : 'Giày Thể Thao', ENT_QUOTES, 'UTF-8');
                        $pprice = number_format($row['price'], 0, ',', '.');
                        $pimg = htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8');
                        $soldCount = (int)$row['total_sold'];
                        $badgeClass = ($rank === 1) ? 'top-1' : (($rank === 2) ? 'top-2' : (($rank === 3) ? 'top-3' : ''));
                    ?>
                        <div class="col-6 col-md-4 col-lg-custom-5">
                            <div class="zyro-product-card-wrap">
                                <span class="zyro-rank-badge <?php echo $badgeClass; ?>">TOP <?php echo $rank++; ?></span>
                                <div class="zyro-product-card">
                                    <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo $pid; ?>">
                                        <div class="zyro-product-img-box">
                                            <img src="/Zyro/assets/img/<?php echo $pimg; ?>" alt="<?php echo $pname; ?>">
                                        </div>
                                        <div class="zyro-product-brand"><?php echo $pbrand; ?></div>
                                        <div class="zyro-product-name"><?php echo $pname; ?></div>
                                        <div class="zyro-sold-tag">
                                            <span>✓ Đã bán <?php echo $soldCount; ?></span>
                                        </div>
                                        <div class="zyro-product-price"><?php echo $pprice; ?>₫</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- 2. Bộ sưu tập: Nike -->
        <section class="mb-5">
            <div class="zyro-section-header">
                <h2 class="zyro-section-title">Bộ sưu tập: Nike</h2>
                <a href="/Zyro/pages/SanPham.php?loai=Nike" class="zyro-section-link">Xem tất cả Nike →</a>
            </div>
            <div class="row g-4">
                <?php if (!empty($nikeProducts)): ?>
                    <?php foreach ($nikeProducts as $row): 
                        $pid = (int)$row['product_id'];
                        $pname = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                        $pbrand = htmlspecialchars($row['brand_name'] ? $row['brand_name'] : 'Nike', ENT_QUOTES, 'UTF-8');
                        $pcat = htmlspecialchars($row['category_name'] ? $row['category_name'] : 'Giày Thể Thao', ENT_QUOTES, 'UTF-8');
                        $pprice = number_format($row['price'], 0, ',', '.');
                        $pimg = htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8');
                    ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="zyro-product-card">
                                <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo $pid; ?>">
                                    <div class="zyro-product-img-box">
                                        <img src="/Zyro/assets/img/<?php echo $pimg; ?>" alt="<?php echo $pname; ?>">
                                    </div>
                                    <div class="zyro-product-brand"><?php echo $pbrand; ?></div>
                                    <div class="zyro-product-name"><?php echo $pname; ?></div>
                                    <div class="zyro-product-cat"><?php echo $pcat; ?></div>
                                    <div class="zyro-product-price"><?php echo $pprice; ?>₫</div>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- 3. Bộ sưu tập: Converse -->
        <section class="mb-5">
            <div class="zyro-section-header">
                <h2 class="zyro-section-title">Bộ sưu tập: Converse</h2>
                <a href="/Zyro/pages/SanPham.php?loai=Converse" class="zyro-section-link">Xem tất cả Converse →</a>
            </div>
            <div class="row g-4">
                <?php if (!empty($converseProducts)): ?>
                    <?php foreach ($converseProducts as $row): 
                        $pid = (int)$row['product_id'];
                        $pname = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                        $pbrand = htmlspecialchars($row['brand_name'] ? $row['brand_name'] : 'Converse', ENT_QUOTES, 'UTF-8');
                        $pcat = htmlspecialchars($row['category_name'] ? $row['category_name'] : 'Giày Cổ Điển', ENT_QUOTES, 'UTF-8');
                        $pprice = number_format($row['price'], 0, ',', '.');
                        $pimg = htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8');
                    ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="zyro-product-card">
                                <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo $pid; ?>">
                                    <div class="zyro-product-img-box">
                                        <img src="/Zyro/assets/img/<?php echo $pimg; ?>" alt="<?php echo $pname; ?>">
                                    </div>
                                    <div class="zyro-product-brand"><?php echo $pbrand; ?></div>
                                    <div class="zyro-product-name"><?php echo $pname; ?></div>
                                    <div class="zyro-product-cat"><?php echo $pcat; ?></div>
                                    <div class="zyro-product-price"><?php echo $pprice; ?>₫</div>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- 3. Bộ sưu tập: Jordan -->
        <section class="mb-5">
            <div class="zyro-section-header">
                <h2 class="zyro-section-title">Bộ Sưu Tập: Jordan</h2>
                <a href="/Zyro/pages/SanPham.php?loai=Jordan" class="zyro-section-link">Xem tất cả Jordan →</a>
            </div>
            <div class="row g-4">
                <?php if (!empty($jordanProducts)): ?>
                    <?php foreach ($jordanProducts as $row): 
                        $pid = (int)$row['product_id'];
                        $pname = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                        $pbrand = htmlspecialchars($row['brand_name'] ? $row['brand_name'] : 'Jordan', ENT_QUOTES, 'UTF-8');
                        $pcat = htmlspecialchars($row['category_name'] ? $row['category_name'] : 'Giày Bóng Rổ', ENT_QUOTES, 'UTF-8');
                        $pprice = number_format($row['price'], 0, ',', '.');
                        $pimg = htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8');
                    ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="zyro-product-card">
                                <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo $pid; ?>">
                                    <div class="zyro-product-img-box">
                                        <img src="/Zyro/assets/img/<?php echo $pimg; ?>" alt="<?php echo $pname; ?>">
                                    </div>
                                    <div class="zyro-product-brand"><?php echo $pbrand; ?></div>
                                    <div class="zyro-product-name"><?php echo $pname; ?></div>
                                    <div class="zyro-product-cat"><?php echo $pcat; ?></div>
                                    <div class="zyro-product-price"><?php echo $pprice; ?>₫</div>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- 4. Khám phá toàn bộ sản phẩm -->
        <div class="p-4 p-md-5 my-4 text-center rounded-3 bg-light border">
            <h3 class="fw-bold mb-2">Tìm Kiếm Đôi Giày Thể Thao Phù Hợp Với Bạn</h3>
            <p class="text-muted mb-4">Khám phá toàn bộ bộ sưu tập giày chạy bộ, bóng rổ, đá banh và sneaker chính hãng tại ZYRO Store.</p>
            <a href="/Zyro/pages/SanPham.php?loai=all" class="btn btn-dark rounded-pill px-5 py-2 fw-bold">
                Khám Phá Cửa Hàng
            </a>
        </div>

    </div>
    </main>

<!-- Footer dùng chung -->
    <?php include ('includes/footer.php'); ?>
</body>
</html>