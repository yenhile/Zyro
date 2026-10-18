<?php
if (session_id() == '') {
    session_start();
}
require_once("../class/classProduct.php");

$productModel = new san_pham();
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = $productModel->lay_chi_tiet_san_pham($productId);

$currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
if ($currentUser && is_string($currentUser) && isset($_SESSION['user_id'])) {
    $currentUser = array(
        'user_id' => (int)$_SESSION['user_id'],
        'name'    => $_SESSION['user']
    );
}

$reviewSuccessMsg = '';
$reviewErrorMsg = '';

// Tiêu chí 7: Bắt buộc đăng nhập mới được gửi đánh giá sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    if (!$currentUser) {
        header("Location: /Zyro/pages/DangNhap.php?error=login_required&redirect=" . urlencode("/Zyro/pages/ChiTietSanPham.php?id=" . $productId));
        exit();
    }
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    if (!empty($comment)) {
        $resRev = $productModel->them_danh_gia_san_pham($currentUser['user_id'], $productId, $rating, $comment);
        if ($resRev) {
            $reviewSuccessMsg = "Cảm ơn bạn đã gửi đánh giá cho sản phẩm này!";
        } else {
            $reviewErrorMsg = "Không thể lưu đánh giá. Vui lòng thử lại!";
        }
    } else {
        $reviewErrorMsg = "Vui lòng nhập nội dung đánh giá của bạn!";
    }
}

$pageTitle = $product ? htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') . " — ZYRO Store" : "Không Tìm Thấy Sản Phẩm — ZYRO";
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
        <?php if (!$product): ?>
            <!-- Trường hợp không tìm thấy sản phẩm -->
            <div class="text-center py-5 my-5">
                <h2 class="text-danger fw-bold mb-3">Sản phẩm không tồn tại hoặc đã ngừng kinh doanh</h2>
                <p class="text-muted mb-4">Rất tiếc, chúng tôi không tìm thấy sản phẩm bạn đang tìm kiếm.</p>
                <a href="/Zyro/index.php" class="btn btn-dark px-4 py-2 rounded-pill me-2">Về Trang Chủ</a>
                <a href="/Zyro/pages/SanPham.php" class="btn btn-outline-dark px-4 py-2 rounded-pill">Xem Tất Cả Sản Phẩm</a>
            </div>
        <?php else: 
            $id = (int)$product['product_id'];
            $name = htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8');
            $brand = htmlspecialchars($product['brand_name'] ? $product['brand_name'] : 'ZYRO', ENT_QUOTES, 'UTF-8');
            $category = htmlspecialchars($product['category_name'] ? $product['category_name'] : 'Giày Thể Thao', ENT_QUOTES, 'UTF-8');
            $price = number_format($product['price'], 0, ',', '.');
            $desc = htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8');
            $img = htmlspecialchars($product['image_url'], ENT_QUOTES, 'UTF-8');

            // Lấy sản phẩm liên quan & đánh giá
            $relatedProducts = $productModel->lay_san_pham_lien_quan($product['category_id'], $product['brand_id'], $id, 4);
            $reviews = $productModel->lay_danh_gia_san_pham($id);
        ?>
            <!-- 1. Breadcrumb điều hướng -->
            <nav class="zyro-breadcrumb" aria-label="breadcrumb">
                <a href="/Zyro/index.php">Trang chủ</a>
                <span>/</span>
                <a href="/Zyro/pages/SanPham.php">Sản phẩm</a>
                <span>/</span>
                <a href="/Zyro/pages/SanPham.php?loai=<?php echo urlencode($brand); ?>"><?php echo $brand; ?></a>
                <span>/</span>
                <span class="text-dark fw-bold"><?php echo $name; ?></span>
            </nav>

            <!-- 2. Chi tiết sản phẩm (2 Cột) -->
            <div class="product-detail-wrapper">
                <!-- Cột trái: Box Hình ảnh Giày -->
                <div class="detail-gallery-col">
                    <div class="product-detail-img-box">
                        <img src="/Zyro/assets/img/<?php echo $img; ?>" alt="<?php echo $name; ?>" id="main-product-img">
                    </div>
                </div>

                <!-- Cột phải: Thông tin & Form Đặt Hàng (100% CSDL zyro_db) -->
                <div class="detail-info-col">
                    <span class="detail-badge-brand"><?php echo $brand; ?></span>
                    <h1 class="detail-product-title"><?php echo $name; ?></h1>
                    <div class="detail-category-tag"><?php echo $category; ?></div>

                    <div class="detail-price-box">
                        <span><?php echo $price; ?></span>₫
                    </div>

                    <!-- Form Thêm vào Giỏ Hàng -->
                    <form action="/Zyro/pages/GioHang.php" method="POST" id="form-add-to-cart">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                        <input type="hidden" name="product_size" id="selected-size-input" value="40">

                        <!-- Chọn Size Giày (Loại bỏ nút bảng quy đổi size) -->
                        <div class="size-header-box">
                            <span class="size-title">Chọn Kích Cỡ (Size EU):</span>
                        </div>
                        <div class="size-grid">
                            <button type="button" class="size-btn-item" data-size="38">EU 38</button>
                            <button type="button" class="size-btn-item" data-size="39">EU 39</button>
                            <button type="button" class="size-btn-item active" data-size="40">EU 40</button>
                            <button type="button" class="size-btn-item" data-size="41">EU 41</button>
                            <button type="button" class="size-btn-item" data-size="42">EU 42</button>
                            <button type="button" class="size-btn-item" data-size="43">EU 43</button>
                            <button type="button" class="size-btn-item" data-size="44">EU 44</button>
                        </div>

                        <!-- Chọn Số Lượng -->
                        <div class="quantity-row">
                            <span class="quantity-label">Số lượng:</span>
                            <div class="quantity-control">
                                <button type="button" class="qty-btn" id="btn-decrease">-</button>
                                <input type="number" name="quantity" id="product-qty-input" class="qty-input" value="1" min="1" max="10" readonly>
                                <button type="button" class="qty-btn" id="btn-increase">+</button>
                            </div>
                        </div>

                        <!-- Nút CTA (Thêm vào giỏ không chuyển trang, Mua ngay chuyển giỏ hàng) -->
                        <div class="cta-btn-group">
                            <button type="button" id="btn-add-cart-action" class="btn-add-cart-large">
                                <img src="/Zyro/assets/icon/bag-shopping.png" width="18" height="18" style="filter: brightness(0) invert(1);" alt="">
                                Thêm Vào Giỏ Hàng
                            </button>
                            <button type="submit" name="buy_now" class="btn-buy-now-large">
                                Mua Ngay
                            </button>
                        </div>
                    </form>

                    <!-- Mô Tả Sản Phẩm (Lấy trực tiếp từ CSDL) -->
                    <div class="detail-desc-content mt-4">
                        <h4>Mô Tả Sản Phẩm</h4>
                        <p><?php echo nl2br($desc); ?></p>
                    </div>
                </div>
            </div>

            <!-- 3. Khối Đánh Giá Khách Hàng -->
            <div class="mt-5 pt-4 border-top">
                <h3 class="fw-bold mb-4">Đánh Giá Từ Khách Hàng (<?php echo count($reviews); ?>)</h3>
                <?php if (!empty($reviews)): ?>
                    <div class="row">
                        <?php foreach ($reviews as $rev): 
                            $reviewer = htmlspecialchars($rev['user_name'] ? $rev['user_name'] : 'Khách hàng ZYRO', ENT_QUOTES, 'UTF-8');
                            $comment = htmlspecialchars($rev['comment'], ENT_QUOTES, 'UTF-8');
                            $stars = (int)$rev['rating'];
                            $date = date("d/m/Y", strtotime($rev['created_at']));
                        ?>
                            <div class="col-md-6 mb-3">
                                <div class="review-item-card">
                                    <div class="review-header">
                                        <span class="reviewer-name"><?php echo $reviewer; ?></span>
                                        <span class="review-stars"><?php echo str_repeat('★', $stars); ?></span>
                                    </div>
                                    <p class="review-comment"><?php echo $comment; ?></p>
                                    <div class="review-date text-end"><?php echo $date; ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-4 bg-light rounded text-center text-muted">
                        Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên trải nghiệm và để lại nhận xét!
                    </div>
                <?php endif; ?>

                <!-- Form Gửi Đánh Giá Hoặc Khóa Bảo Mật Yêu Cầu Đăng Nhập (Tiêu chí 7) -->
                <?php if (!empty($reviewSuccessMsg)): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-4 shadow-sm" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($reviewSuccessMsg, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($reviewErrorMsg)): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-4 shadow-sm" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($reviewErrorMsg, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($currentUser): ?>
                    <!-- Form gửi đánh giá cho thành viên đã đăng nhập -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 mt-4 bg-light">
                        <h5 class="fw-bold text-dark mb-3">
                            <i class="fas fa-pen-nib text-primary me-2"></i>Viết Đánh Giá Của Bạn
                        </h5>
                        <form action="ChiTietSanPham.php?id=<?php echo $productId; ?>" method="POST">
                            <input type="hidden" name="action" value="submit_review">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Chấm điểm chất lượng:</label>
                                <select name="rating" class="form-select w-auto rounded-pill" required>
                                    <option value="5">★★★★★ (5/5) — Xuất sắc, rất hài lòng</option>
                                    <option value="4">★★★★☆ (4/5) — Rất tốt, giày êm chân</option>
                                    <option value="3">★★★☆☆ (3/5) — Bình thường, tạm ổn</option>
                                    <option value="2">★★☆☆☆ (2/5) — Chưa hài lòng</option>
                                    <option value="1">★☆☆☆☆ (1/5) — Kém, không vừa ý</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Nhận xét chi tiết:</label>
                                <textarea name="comment" class="form-control rounded-3" rows="3" required placeholder="Chia sẻ cảm nhận về form giày, độ êm, phong cách phối đồ..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold">
                                Gửi Đánh Giá Ngay
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Banner Yêu Cầu Đăng Nhập Mới Được Đánh Giá (Tiêu chí 7) -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 mt-4 text-center" style="background: #ffffff; border: 2px dashed #eab308 !important;">
                        <div class="mb-2">
                            <span style="font-size: 32px;">🔒</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Đăng Nhập Để Gửi Đánh Giá</h5>
                        <p class="text-muted small mb-3">
                            Để bảo đảm tính khách quan và chất lượng cộng đồng ZYRO, quý khách vui lòng <strong>đăng nhập tài khoản</strong> trước khi để lại nhận xét và chấm sao cho sản phẩm này.
                        </p>
                        <div>
                            <a href="/Zyro/pages/DangNhap.php?error=login_required&redirect=<?php echo urlencode('/Zyro/pages/ChiTietSanPham.php?id=' . $productId); ?>" class="btn btn-dark rounded-pill px-4 py-2 btn-sm fw-bold">
                                Đăng Nhập Để Đánh Giá
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 4. Khối Sản Phẩm Liên Quan / Cùng Thể Thao -->
            <?php if (!empty($relatedProducts)): ?>
                <div class="mt-5 pt-4 border-top">
                    <h3 class="fw-bold mb-4">Bạn Cũng Có Thể Thích</h3>
                    <div class="row">
                        <?php foreach ($relatedProducts as $rel): 
                            $relId = (int)$rel['product_id'];
                            $relName = htmlspecialchars($rel['name'], ENT_QUOTES, 'UTF-8');
                            $relCat = htmlspecialchars($rel['category_name'] ? $rel['category_name'] : $rel['brand_name'], ENT_QUOTES, 'UTF-8');
                            $relPrice = number_format($rel['price'], 0, ',', '.');
                            $relImg = htmlspecialchars($rel['image_url'], ENT_QUOTES, 'UTF-8');
                        ?>
                            <div class="col-6 col-md-3 mb-4">
                                <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo $relId; ?>" class="text-decoration-none">
                                    <div class="product-img-wrap" style="height: 220px;">
                                        <img src="/Zyro/assets/img/<?php echo $relImg; ?>" alt="<?php echo $relName; ?>">
                                    </div>
                                    <div class="trend-sp-thong-tin mt-2">
                                        <div class="trend-sp-name" style="font-size: 14px;"><?php echo $relName; ?></div>
                                        <div class="trend-sp-mo-ta-ngan" style="font-size: 12px;"><?php echo $relCat; ?></div>
                                        <div class="trend-sp-gia" style="font-size: 14px;"><span><?php echo $relPrice; ?></span>₫</div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <!-- Script Tương Tác Chọn Size, Số Lượng & Thêm Giỏ Hàng Tại Chỗ (Không chuyển hướng) -->
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Xử lý chọn size giày
        const sizeButtons = document.querySelectorAll(".size-btn-item");
        const sizeInput = document.getElementById("selected-size-input");

        sizeButtons.forEach(btn => {
            btn.addEventListener("click", function () {
                sizeButtons.forEach(b => b.classList.remove("active"));
                this.classList.add("active");
                if (sizeInput) {
                    sizeInput.value = this.getAttribute("data-size");
                }
            });
        });

        // Xử lý tăng giảm số lượng
        const qtyInput = document.getElementById("product-qty-input");
        const btnDecrease = document.getElementById("btn-decrease");
        const btnIncrease = document.getElementById("btn-increase");

        if (btnDecrease && qtyInput) {
            btnDecrease.addEventListener("click", function () {
                let current = parseInt(qtyInput.value) || 1;
                if (current > 1) {
                    qtyInput.value = current - 1;
                }
            });
        }

        if (btnIncrease && qtyInput) {
            btnIncrease.addEventListener("click", function () {
                let current = parseInt(qtyInput.value) || 1;
                if (current < 10) {
                    qtyInput.value = current + 1;
                }
            });
        }

        // Xử lý Thêm Vào Giỏ Hàng tại chỗ (AJAX - Không chuyển hướng trang)
        const btnAddCart = document.getElementById("btn-add-cart-action");
        const toastEl = document.getElementById("zyro-toast");
        let toastTimeout = null;

        function showToast(msg) {
            if (!toastEl) return;
            const msgEl = toastEl.querySelector(".toast-msg");
            if (msgEl) msgEl.textContent = msg;
            toastEl.classList.add("show");
            if (toastTimeout) clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => {
                toastEl.classList.remove("show");
            }, 3000);
        }

        if (btnAddCart) {
            btnAddCart.addEventListener("click", function () {
                const form = document.getElementById("form-add-to-cart");
                const formData = new FormData(form);
                formData.append("is_ajax", "1");

                const originalHtml = btnAddCart.innerHTML;
                btnAddCart.innerHTML = '<span>Đang thêm...</span>';
                btnAddCart.disabled = true;

                fetch('/Zyro/pages/GioHang.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    btnAddCart.innerHTML = originalHtml;
                    btnAddCart.disabled = false;

                    if (data && data.status === 'success') {
                        // Cập nhật số lượng giỏ hàng trên header badge
                        const cartBox = document.querySelector(".box-icon-menu-main a");
                        let badge = document.querySelector(".badge-cart-count");
                        if (cartBox) {
                            if (!badge) {
                                badge = document.createElement("span");
                                badge.className = "badge-cart-count";
                                cartBox.appendChild(badge);
                            }
                            badge.textContent = data.cart_count > 99 ? '99+' : data.cart_count;
                        }
                        showToast(data.message || "Đã thêm sản phẩm vào giỏ hàng thành công!");
                    } else {
                        showToast("Có lỗi xảy ra khi thêm vào giỏ hàng!");
                    }
                })
                .catch(err => {
                    btnAddCart.innerHTML = originalHtml;
                    btnAddCart.disabled = false;
                    showToast("Đã thêm sản phẩm vào giỏ hàng!");
                });
            });
        }
    });
    </script>

    <!-- Toast Thông Báo Khi Thêm Giỏ Hàng Tại Chỗ -->
    <div id="zyro-toast" class="zyro-toast-alert shadow">
        <span class="toast-icon">✓</span>
        <span class="toast-msg">Đã thêm sản phẩm vào giỏ hàng thành công!</span>
    </div>
    </main>

<!-- Footer dùng chung -->
    <?php include ('../includes/footer.php'); ?>
</body>
</html>
