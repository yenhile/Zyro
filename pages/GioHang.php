<?php
if (session_id() == '') {
    session_start();
}
require_once("../class/classProduct.php");

$productModel = new san_pham();

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$orderSuccess = null;
$errorMessage = '';

// 1. Xử lý các Action của Giỏ Hàng
$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';

// 1.1 Thêm sản phẩm vào giỏ
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $size = isset($_POST['product_size']) ? trim($_POST['product_size']) : '40';
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    if ($qty < 1) $qty = 1;
    if ($qty > 10) $qty = 10;

    if ($productId > 0) {
        $cartKey = $productId . '_' . $size;
        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey]['quantity'] = min(10, $_SESSION['cart'][$cartKey]['quantity'] + $qty);
        } else {
            $productData = $productModel->lay_chi_tiet_san_pham($productId);
            if ($productData) {
                $_SESSION['cart'][$cartKey] = array(
                    'product_id' => $productId,
                    'name'       => $productData['name'],
                    'brand_name' => $productData['brand_name'] ? $productData['brand_name'] : 'ZYRO',
                    'price'      => (float)$productData['price'],
                    'image_url'  => $productData['image_url'],
                    'size'       => $size,
                    'quantity'   => $qty
                );
            }
        }
    }
    header("Location: /Zyro/pages/GioHang.php");
    exit();
}

// 1.2 Cập nhật số lượng (+ / -)
if ($action === 'update_qty' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartKey = isset($_POST['cart_key']) ? trim($_POST['cart_key']) : '';
    $change = isset($_POST['change']) ? (int)$_POST['change'] : 0;

    if (isset($_SESSION['cart'][$cartKey])) {
        $newQty = $_SESSION['cart'][$cartKey]['quantity'] + $change;
        if ($newQty <= 0) {
            unset($_SESSION['cart'][$cartKey]);
        } else {
            $_SESSION['cart'][$cartKey]['quantity'] = min(10, $newQty);
        }
    }
    header("Location: /Zyro/pages/GioHang.php");
    exit();
}

// 1.3 Xóa 1 món hàng
if ($action === 'remove') {
    $cartKey = isset($_REQUEST['cart_key']) ? trim($_REQUEST['cart_key']) : '';
    if (isset($_SESSION['cart'][$cartKey])) {
        unset($_SESSION['cart'][$cartKey]);
    }
    header("Location: /Zyro/pages/GioHang.php");
    exit();
}

// 1.4 Làm trống toàn bộ giỏ
if ($action === 'clear') {
    $_SESSION['cart'] = array();
    header("Location: /Zyro/pages/GioHang.php");
    exit();
}

// 1.5 Xử lý Đặt hàng (Checkout)
if ($action === 'checkout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tiêu chí 7: Phải đăng nhập mới được đặt hàng online
    if (!$currentUser) {
        header("Location: /Zyro/pages/DangNhap.php?error=login_required&redirect=" . urlencode("/Zyro/pages/GioHang.php"));
        exit();
    }

    if (empty($_SESSION['cart'])) {
        $errorMessage = "Giỏ hàng của bạn đang trống, vui lòng chọn sản phẩm trước khi đặt hàng!";
    } else {
        $receiverName = isset($_POST['receiver_name']) ? trim($_POST['receiver_name']) : '';
        $receiverPhone = isset($_POST['receiver_phone']) ? trim($_POST['receiver_phone']) : '';
        $receiverEmail = isset($_POST['receiver_email']) ? trim($_POST['receiver_email']) : '';
        $shippingAddress = isset($_POST['shipping_address']) ? trim($_POST['shipping_address']) : '';
        $note = isset($_POST['note']) ? trim($_POST['note']) : '';
        $paymentMethod = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'COD';

        if (empty($receiverName) || empty($receiverPhone) || empty($shippingAddress)) {
            $errorMessage = "Vui lòng điền đầy đủ Họ tên, Số điện thoại và Địa chỉ giao hàng!";
        } else {
            // Tính tổng tiền
            $subtotal = 0;
            foreach ($_SESSION['cart'] as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $shippingFee = ($subtotal >= 1000000) ? 0 : 30000;
            $totalPrice = $subtotal + $shippingFee;

            // Xác định user_id
            if ($currentUser && isset($currentUser['user_id'])) {
                $userId = (int)$currentUser['user_id'];
            } else {
                $userId = $productModel->tim_hoac_tao_khach_hang($receiverName, $receiverEmail, $receiverPhone, $shippingAddress);
                if ($userId) {
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['user'] = $receiverName;
                    $_SESSION['phanquyen'] = 1;
                }
            }

            // Xác định phương thức thanh toán
            $isPayOS = ($paymentMethod === 'PAYOS' || $paymentMethod === 'BANKING');
            if ($isPayOS) {
                $initialStatus = 'Chờ thanh toán';
                $payosOrderCode = (int)(time() . rand(10, 99));
                $paymentCode = 'ZYRO' . date('ymd') . rand(1000, 9999);
            } else {
                $initialStatus = 'Chờ xử lý';
                $payosOrderCode = null;
                $paymentCode = 'COD' . date('ymd') . rand(100, 999);
            }

            // Ghi đơn hàng vào CSDL MySQL zyro_db
            $orderId = $productModel->tao_don_hang(
                $userId, 
                $totalPrice, 
                $receiverName, 
                $receiverPhone, 
                $shippingAddress, 
                $note, 
                $paymentMethod, 
                $_SESSION['cart'], 
                $paymentCode, 
                $payosOrderCode, 
                $initialStatus
            );

            if ($orderId) {
                if ($isPayOS) {
                    $paymentCode = 'ZYRO DH' . $orderId;
                    $productModel->cap_nhat_thong_tin_payos($orderId, $paymentCode, $payosOrderCode);
                }

                $_SESSION['last_order_id'] = $orderId;
                // Dọn sạch giỏ hàng
                $_SESSION['cart'] = array();
                
                // Nếu là PayOS -> Điều hướng sang màn hình thanh toán QR PayOS
                if ($isPayOS) {
                    header("Location: /Zyro/pages/ThanhToanPayOS.php?order_id=" . $orderId);
                } else {
                    // Nếu là COD -> Điều hướng sang màn hình Xác Nhận Đặt Hàng Chuyên Biệt
                    header("Location: /Zyro/pages/DatHangThanhCong.php?order_id=" . $orderId);
                }
                exit();
            } else {
                $errorMessage = "Không thể lưu đơn hàng. Vui lòng thử lại sau!";
            }
        }
    }
}

// 2. Tính toán tóm tắt giỏ hàng hiện tại
$cartTotalQty = 0;
$cartSubtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotalQty += $item['quantity'];
    $cartSubtotal += $item['price'] * $item['quantity'];
}
$cartShippingFee = ($cartSubtotal >= 1000000 || $cartSubtotal == 0) ? 0 : 30000;
$cartGrandTotal = $cartSubtotal + $cartShippingFee;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng & Thanh Toán — ZYRO Store</title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="/Zyro/assets/css/bootstrap.css">
    <!-- Style chính -->
    <link rel="stylesheet" href="/Zyro/assets/css/layout.css">
    <link rel="stylesheet" href="/Zyro/assets/css/style.css">
    <script src="/Zyro/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/Zyro/assets/js/action.js"></script>
</head>
<body>
    <!-- Header dùng chung -->
    <?php include ('../includes/header.php'); ?>

    <div class="container my-4">
        <!-- Breadcrumb -->
        <nav class="zyro-breadcrumb" aria-label="breadcrumb">
            <a href="/Zyro/index.php">Trang chủ</a>
            <span>/</span>
            <a href="/Zyro/pages/SanPham.php">Sản phẩm</a>
            <span>/</span>
            <span class="text-dark fw-bold">Giỏ hàng của bạn</span>
        </nav>

        <?php if ($orderSuccess): ?>
            <!-- 1. Màn hình Đặt Hàng Thành Công -->
            <div class="order-success-state">
                <div class="success-badge-icon">✓</div>
                <h2 class="fw-bold mb-2">ĐẶT HÀNG THÀNH CÔNG!</h2>
                <p class="text-muted">Cảm ơn bạn đã tin chọn <strong>ZYRO Store</strong>. Đơn hàng của bạn đã được tiếp nhận và đang được xử lý.</p>
                
                <div class="order-code-badge">
                    Mã Đơn Hàng: #ZYRO-DH<?php echo str_pad($orderSuccess['order_id'], 5, '0', STR_PAD_LEFT); ?>
                </div>

                <div class="bg-light p-4 rounded-3 text-start mb-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Thông Tin Giao Nhận:</h5>
                    <p class="mb-1"><strong>Người nhận:</strong> <?php echo htmlspecialchars($orderSuccess['receiver_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="mb-1"><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($orderSuccess['receiver_phone'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="mb-1"><strong>Địa chỉ giao:</strong> <?php echo htmlspecialchars($orderSuccess['shipping_address'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="mb-1"><strong>Phương thức:</strong> <?php echo ($orderSuccess['payment_method'] === 'COD') ? 'Thanh toán tiền mặt khi nhận hàng (COD)' : 'Chuyển khoản trực tuyến'; ?></p>
                    <p class="mb-0"><strong>Tổng thanh toán:</strong> <span class="text-danger fw-bold fs-5"><?php echo number_format($orderSuccess['total_price'], 0, ',', '.'); ?>₫</span></p>
                </div>

                <div class="d-flex justify-content-center gap-3">
                    <a href="/Zyro/index.php" class="btn btn-dark px-4 py-2 rounded-pill">Về Trang Chủ</a>
                    <a href="/Zyro/pages/SanPham.php" class="btn btn-outline-dark px-4 py-2 rounded-pill">Tiếp Tục Mua Sắm</a>
                </div>
            </div>

        <?php elseif (empty($_SESSION['cart'])): ?>
            <!-- 2. Màn hình Giỏ Hàng Rỗng -->
            <div class="cart-empty-state">
                <div class="cart-empty-icon">🛍️</div>
                <h3 class="cart-empty-title">Túi Đồ Của Bạn Đang Trống</h3>
                <p class="cart-empty-desc">Chưa có sản phẩm nào trong giỏ hàng. Hãy khám phá bộ sưu tập giày thể thao mới nhất tại ZYRO Store ngay hôm nay!</p>
                <a href="/Zyro/pages/SanPham.php" class="btn btn-dark px-5 py-3 rounded-pill fw-bold text-uppercase">
                    Khám Phá Sản Phẩm Ngay
                </a>
            </div>

        <?php else: ?>
            <!-- 3. Giao diện Giỏ Hàng Có Sản Phẩm (2 Cột) -->
            <h1 class="cart-page-title">Túi Đồ Của Bạn (<?php echo $cartTotalQty; ?>)</h1>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <strong>Thông báo:</strong> <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Cột trái: Danh sách các món trong Giỏ Hàng -->
                <div class="col-lg-7 mb-4">
                    <div class="cart-items-container">
                        <?php foreach ($_SESSION['cart'] as $key => $item): 
                            $itemId = (int)$item['product_id'];
                            $itemName = htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');
                            $itemBrand = htmlspecialchars($item['brand_name'], ENT_QUOTES, 'UTF-8');
                            $itemSize = htmlspecialchars($item['size'], ENT_QUOTES, 'UTF-8');
                            $itemQty = (int)$item['quantity'];
                            $itemPrice = $item['price'];
                            $itemSubtotal = $itemPrice * $itemQty;
                            $itemImg = htmlspecialchars($item['image_url'], ENT_QUOTES, 'UTF-8');
                        ?>
                            <div class="cart-item-row">
                                <!-- Khung ảnh thumbnail chuẩn 1:1 showroom -->
                                <div class="cart-item-img-box">
                                    <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo $itemId; ?>">
                                        <img src="/Zyro/assets/img/<?php echo $itemImg; ?>" alt="<?php echo $itemName; ?>">
                                    </a>
                                </div>

                                <!-- Thông tin chi tiết sản phẩm -->
                                <div class="cart-item-info">
                                    <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo $itemId; ?>" class="cart-item-name">
                                        <?php echo $itemName; ?>
                                    </a>
                                    <div class="cart-item-meta">
                                        <span><?php echo $itemBrand; ?></span> • 
                                        <span>Size:<span class="badge-size"><?php echo $itemSize; ?></span></span>
                                    </div>
                                    <div class="cart-item-price-unit">
                                        <?php echo number_format($itemPrice, 0, ',', '.'); ?>₫
                                    </div>
                                </div>

                                <!-- Cụm tăng giảm số lượng -->
                                <div class="cart-qty-form">
                                    <form action="/Zyro/pages/GioHang.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="update_qty">
                                        <input type="hidden" name="cart_key" value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="change" value="-1">
                                        <button type="submit" class="cart-qty-btn" title="Giảm số lượng">-</button>
                                    </form>

                                    <input type="text" class="cart-qty-display" value="<?php echo $itemQty; ?>" readonly>

                                    <form action="/Zyro/pages/GioHang.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="update_qty">
                                        <input type="hidden" name="cart_key" value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="change" value="1">
                                        <button type="submit" class="cart-qty-btn" title="Tăng số lượng">+</button>
                                    </form>
                                </div>

                                <!-- Thành tiền -->
                                <div class="cart-item-subtotal">
                                    <?php echo number_format($itemSubtotal, 0, ',', '.'); ?>₫
                                </div>

                                <!-- Nút xóa -->
                                <form action="/Zyro/pages/GioHang.php" method="POST" onsubmit="return confirm('Bạn có chắc muốn bỏ sản phẩm này khỏi túi đồ?');">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="cart_key" value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn-remove-item" title="Xóa món đồ">🗑️</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Thanh nút hành động phụ -->
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="/Zyro/pages/SanPham.php" class="btn btn-outline-dark rounded-pill px-4">
                            ← Tiếp tục mua sắm
                        </a>
                        <form action="/Zyro/pages/GioHang.php" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn làm trống toàn bộ giỏ hàng?');">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-link text-danger text-decoration-none">
                                Làm trống giỏ hàng
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Cột phải: Tóm tắt đơn hàng & Form Đặt Hàng Online -->
                <div class="col-lg-5">
                    <div class="order-summary-box">
                        <h3 class="summary-title">Tóm Tắt Đơn Hàng</h3>

                        <div class="summary-line">
                            <span>Tạm tính (<?php echo $cartTotalQty; ?> sản phẩm):</span>
                            <span><?php echo number_format($cartSubtotal, 0, ',', '.'); ?>₫</span>
                        </div>

                        <div class="summary-line">
                            <span>Phí vận chuyển:</span>
                            <span>
                                <?php if ($cartShippingFee === 0): ?>
                                    <strong class="text-success">MIỄN PHÍ</strong>
                                <?php else: ?>
                                    <?php echo number_format($cartShippingFee, 0, ',', '.'); ?>₫
                                <?php endif; ?>
                            </span>
                        </div>

                        <?php if ($cartSubtotal < 1000000): ?>
                            <div class="alert alert-warning py-2 px-3 small rounded-3 mb-3">
                                💡 Mua thêm <strong><?php echo number_format(1000000 - $cartSubtotal, 0, ',', '.'); ?>₫</strong> để được <strong>Miễn phí vận chuyển</strong>!
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success py-2 px-3 small rounded-3 mb-3">
                                🎉 Đơn hàng đủ điều kiện nhận <strong>Miễn Phí Vận Chuyển</strong>!
                            </div>
                        <?php endif; ?>

                        <div class="summary-line total-line">
                            <span>Tổng thanh toán:</span>
                            <span><?php echo number_format($cartGrandTotal, 0, ',', '.'); ?>₫</span>
                        </div>

                        <!-- Form Đặt Hàng -->
                        <div class="checkout-form-section">
                            <h4 class="checkout-form-title">Thông Tin Nhận Hàng</h4>

                            <?php if (!$currentUser): ?>
                                <!-- Khối Khóa Bảo Mật: Bắt buộc đăng nhập để đặt hàng (Tiêu chí 7) -->
                                <div class="checkout-login-guard p-4 rounded-4 text-center my-3 shadow-sm" style="background: #ffffff; border: 2px dashed #eab308;">
                                    <div class="mb-3">
                                        <span style="font-size: 38px;">🔒</span>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-2">Đăng Nhập Để Đặt Hàng Trực Tuyến</h5>
                                    <p class="text-muted small mb-4">
                                        Nhằm bảo vệ quyền lợi bảo hành chính hãng, áp dụng chính sách vận chuyển và theo dõi mã vận đơn, ZYRO yêu cầu quý khách <strong>đăng nhập tài khoản</strong> trước khi đặt hàng.
                                    </p>
                                    <div class="d-grid gap-2">
                                        <a href="/Zyro/pages/DangNhap.php?error=login_required&redirect=<?php echo urlencode('/Zyro/pages/GioHang.php'); ?>" class="btn btn-dark rounded-pill fw-bold py-2 text-uppercase">
                                            Đăng Nhập Để Tiến Hành Đặt Hàng
                                        </a>
                                        <a href="/Zyro/pages/DangKy.php" class="btn btn-outline-secondary rounded-pill small py-2">
                                            Chưa có tài khoản? Đăng ký thành viên ZYRO
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Form Đặt Hàng khi đã đăng nhập -->
                                <form action="/Zyro/pages/GioHang.php" method="POST" id="form-checkout">
                                    <input type="hidden" name="action" value="checkout">

                                    <div class="form-field-group">
                                        <label for="receiver_name">Họ và tên người nhận (*)</label>
                                        <input type="text" id="receiver_name" name="receiver_name" required 
                                               placeholder="Nguyễn Văn A" 
                                               value="<?php echo htmlspecialchars($currentUser['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="form-field-group">
                                        <label for="receiver_phone">Số điện thoại (*)</label>
                                        <input type="tel" id="receiver_phone" name="receiver_phone" required 
                                               placeholder="0901234567" 
                                               value="<?php echo isset($currentUser['phone']) ? htmlspecialchars($currentUser['phone'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                    </div>

                                    <div class="form-field-group">
                                        <label for="receiver_email">Email nhận thông báo đơn hàng</label>
                                        <input type="email" id="receiver_email" name="receiver_email" 
                                               placeholder="email@example.com" 
                                               value="<?php echo isset($currentUser['email']) ? htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                    </div>

                                    <div class="form-field-group">
                                        <label for="shipping_address">Địa chỉ giao hàng chi tiết (*)</label>
                                        <textarea id="shipping_address" name="shipping_address" rows="2" required 
                                                  placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố"><?php echo isset($currentUser['address']) ? htmlspecialchars($currentUser['address'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                    </div>

                                    <div class="form-field-group">
                                        <label for="note">Ghi chú đơn hàng (tuỳ chọn)</label>
                                        <input type="text" id="note" name="note" placeholder="Giao giờ hành chính, gọi trước khi giao...">
                                    </div>

                                    <div class="form-field-group">
                                        <label for="payment_method">Phương thức thanh toán (*)</label>
                                        <select id="payment_method" name="payment_method" style="font-weight: 600;">
                                            <option value="PAYOS" selected>💳 Chuyển khoản trực tuyến qua Cổng PayOS (Quét mã QR tự động xác nhận)</option>
                                            <option value="COD">💵 Thanh toán tiền mặt khi nhận hàng (COD)</option>
                                        </select>
                                        <div style="font-size: 12px; color: #0284c7; margin-top: 6px;">
                                            ⚡ Khuyên dùng: Quét mã QR PayOS tự động xác thực trong 3 giây, hỗ trợ toàn bộ 50+ ngân hàng Việt Nam.
                                        </div>
                                    </div>

                                    <button type="submit" class="btn-checkout-now">
                                        Xác Nhận Đặt Hàng (<?php echo number_format($cartGrandTotal, 0, ',', '.'); ?>₫)
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer dùng chung -->
    <?php include ('../includes/footer.php'); ?>
</body>
</html>