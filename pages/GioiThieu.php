<?php
if (session_id() == '') {
    session_start();
}
require_once("../class/classProduct.php");

$productModel = new san_pham();

$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$articleDetail = null;

if ($postId > 0) {
    $articleDetail = $productModel->lay_chi_tiet_bai_viet($postId);
}

$pageTitle = $articleDetail ? htmlspecialchars($articleDetail['title'], ENT_QUOTES, 'UTF-8') . " — ZYRO Blog" : "Giới Thiệu Về ZYRO Store — Tinh Thần Thể Thao";
$latestArticles = $productModel->lay_danh_sach_bai_viet(6);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="/Zyro/assets/css/bootstrap.css">
    <!-- Style chính có cache-busting -->
    <link rel="stylesheet" href="/Zyro/assets/css/layout.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/Zyro/assets/css/style.css?v=<?php echo time(); ?>">
    <script src="/Zyro/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/Zyro/assets/js/action.js"></script>
</head>
<body>
    <!-- Header dùng chung -->
    <?php include ('../includes/header.php'); ?>

    <main class="page-content-wrapper">
    <div class="container my-4">
        <?php if ($articleDetail): 
            $title = htmlspecialchars($articleDetail['title'], ENT_QUOTES, 'UTF-8');
            $summary = htmlspecialchars($articleDetail['summary'], ENT_QUOTES, 'UTF-8');
            $content = $articleDetail['content'];
            $img = htmlspecialchars($articleDetail['image_url'], ENT_QUOTES, 'UTF-8');
            $author = htmlspecialchars($articleDetail['author_name'] ? $articleDetail['author_name'] : 'Ban Biên Tập ZYRO', ENT_QUOTES, 'UTF-8');
            $date = date("d/m/Y", strtotime($articleDetail['created_at']));
        ?>
            <!-- CHI TIẾT BÀI VIẾT -->
            <nav class="zyro-breadcrumb" aria-label="breadcrumb">
                <a href="/Zyro/index.php">Trang chủ</a>
                <span>/</span>
                <a href="/Zyro/pages/GioiThieu.php">Giới thiệu & Bài viết</a>
                <span>/</span>
                <span class="text-dark fw-bold"><?php echo $title; ?></span>
            </nav>

            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="article-detail-header">
                        <span class="badge bg-dark mb-2">Cẩm Nang Sneaker</span>
                        <h1 class="article-detail-title"><?php echo $title; ?></h1>
                        <div class="text-muted small">
                            Đăng bởi <strong><?php echo $author; ?></strong> • Ngày <?php echo $date; ?>
                        </div>
                    </div>

                    <div class="article-detail-img-box">
                        <img src="/Zyro/assets/img/<?php echo $img; ?>" alt="<?php echo $title; ?>">
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-4 fst-italic border-start border-4 border-dark">
                        <?php echo $summary; ?>
                    </div>

                    <div class="article-detail-content mb-5">
                        <p><?php echo nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8')); ?></p>
                    </div>

                    <div class="border-top pt-4 mb-5 d-flex justify-content-between align-items-center">
                        <a href="/Zyro/pages/GioiThieu.php" class="btn btn-outline-dark rounded-pill px-4">
                            ← Quay lại danh sách bài viết
                        </a>
                        <a href="/Zyro/pages/SanPham.php" class="btn btn-dark rounded-pill px-4">
                            Xem Bộ Sưu Tập Giày ZYRO →
                        </a>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- TRANG GIỚI THIỆU & CẨM NANG TỔNG QUAN -->
            <nav class="zyro-breadcrumb" aria-label="breadcrumb">
                <a href="/Zyro/index.php">Trang chủ</a>
                <span>/</span>
                <span class="text-dark fw-bold">Giới thiệu & Bài viết</span>
            </nav>

            <!-- 1. Hero Banner Giới thiệu -->
            <div class="about-hero-box">
                <span class="about-hero-badge">Câu Chuyện ZYRO Store</span>
                <h1 class="about-hero-title">Đam Mê Bất Tận Trên Từng Bước Chạy</h1>
                <p class="about-hero-subtitle">
                    ZYRO được xây dựng bởi những người yêu thể thao và văn hóa sneaker tại TP.HCM. Chúng tôi cam kết mang tới cho cộng đồng những sản phẩm giày thể thao 100% chính hãng từ Nike, Converse, Jordan với trải nghiệm mua sắm hiện đại và chuyên nghiệp nhất.
                </p>
            </div>

            <!-- 2. Giá trị cốt lõi (4 Trụ Cột) -->
            <div class="row g-4 mb-5">
                <div class="col-6 col-md-3">
                    <div class="value-card">
                        <div class="value-icon">🛡️</div>
                        <h4 class="value-title">100% Chính Hãng</h4>
                        <p class="value-desc">Cam kết hàng chính hãng có tem mác, hộp đầy đủ từ Nike, Converse, Jordan.</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="value-card">
                        <div class="value-icon">🚚</div>
                        <h4 class="value-title">Giao Nhanh Toàn Quốc</h4>
                        <p class="value-desc">Miễn phí vận chuyển cho đơn hàng từ 1.000.000₫, giao hàng an toàn tận tay.</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="value-card">
                        <div class="value-icon">🔄</div>
                        <h4 class="value-title">Đổi Trả 30 Ngày</h4>
                        <p class="value-desc">Chính sách đổi size linh hoạt trong 30 ngày nếu chưa vừa vặn bàn chân bạn.</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="value-card">
                        <div class="value-icon">💬</div>
                        <h4 class="value-title">Tư Vấn Tận Tâm</h4>
                        <p class="value-desc">Đội ngũ chuyên gia sneaker luôn sẵn sàng hỗ trợ bạn chọn đôi giày ưng ý nhất.</p>
                    </div>
                </div>
            </div>

            <!-- 3. Danh sách bài viết & Cẩm nang Sneaker -->
            <div class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1" style="font-size: 26px;">Cẩm Nang & Bài Viết Mới Nhất</h2>
                        <p class="text-muted small mb-0">Khám phá văn hóa sneaker, xu hướng thời trang và kinh nghiệm chọn giày thể thao</p>
                    </div>
                </div>

                <div class="row g-4">
                    <?php if (!empty($latestArticles)): ?>
                        <?php foreach ($latestArticles as $art): 
                            $artId = (int)$art['post_id'];
                            $artTitle = htmlspecialchars($art['title'], ENT_QUOTES, 'UTF-8');
                            $artSummary = htmlspecialchars($art['summary'], ENT_QUOTES, 'UTF-8');
                            $artImg = htmlspecialchars($art['image_url'], ENT_QUOTES, 'UTF-8');
                            $artDate = date("d/m/Y", strtotime($art['created_at']));
                            $artAuthor = htmlspecialchars($art['author_name'] ? $art['author_name'] : 'ZYRO Store', ENT_QUOTES, 'UTF-8');
                        ?>
                            <div class="col-md-4 mb-3">
                                <div class="article-card">
                                    <div class="article-card-img-wrap">
                                        <a href="/Zyro/pages/GioiThieu.php?post_id=<?php echo $artId; ?>">
                                            <img src="/Zyro/assets/img/<?php echo $artImg; ?>" alt="<?php echo $artTitle; ?>">
                                        </a>
                                    </div>
                                    <div class="article-card-body">
                                        <div class="article-meta">
                                            <span><?php echo $artDate; ?></span> • <span><?php echo $artAuthor; ?></span>
                                        </div>
                                        <h3 class="article-title">
                                            <a href="/Zyro/pages/GioiThieu.php?post_id=<?php echo $artId; ?>" class="text-dark text-decoration-none">
                                                <?php echo $artTitle; ?>
                                            </a>
                                        </h3>
                                        <p class="article-summary"><?php echo $artSummary; ?></p>
                                        <a href="/Zyro/pages/GioiThieu.php?post_id=<?php echo $artId; ?>" class="btn-read-article">
                                            Đọc tiếp <span>→</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-4 text-muted">
                            Chưa có bài viết nào được đăng tải.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 4. Banner Call To Action Mua Hàng -->
            <div class="p-5 rounded-4 bg-light text-center border mb-4">
                <h3 class="fw-bold mb-2">Sẵn Sàng Nâng Tầm Phong Cách Cùng ZYRO?</h3>
                <p class="text-muted mb-4 max-w-500 mx-auto">Hàng trăm mẫu giày chạy bộ, bóng rổ, lifestyle thời thượng đang chờ đón bạn.</p>
                <a href="/Zyro/pages/SanPham.php" class="btn btn-dark px-5 py-3 rounded-pill fw-bold text-uppercase">
                    Khám Phá Toàn Bộ Giày Thể Thao Ngay
                </a>
            </div>

        <?php endif; ?>
    </div>
    </main>

    <!-- Footer dùng chung -->
    <?php include ('../includes/footer.php'); ?>
</body>
</html>
