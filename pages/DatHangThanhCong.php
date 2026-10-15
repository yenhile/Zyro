<?php
if (session_id() == '') {
    session_start();
}
require_once('../class/classProduct.php');

$productModel = new san_pham();

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : (isset($_SESSION['last_order_id']) ? (int)$_SESSION['last_order_id'] : 0);

$order = null;
$orderItems = array();

if ($orderId > 0) {
    $order = $productModel->lay_thong_tin_don_hang($orderId);
    if ($order) {
        $orderItems = $productModel->lay_chi_tiet_san_pham_don_hang($orderId);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Hàng Thành Công — ZYRO Store</title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="/Zyro/assets/css/bootstrap.css">
    <!-- Style chính -->
    <link rel="stylesheet" href="/Zyro/assets/css/layout.css">
    <link rel="stylesheet" href="/Zyro/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="/Zyro/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/Zyro/assets/js/action.js"></script>
    <style>
        .order-success-hero {
            background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 100%);
            border: 1px solid #bbf7d0;
            border-radius: 18px;
            padding: 40px 24px;
            text-align: center;
            margin-bottom: 30px;
        }
        .order-success-icon {
            width: 72px;
            height: 72px;
            background: #22c55e;
            color: #ffffff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            box-shadow: 0 10px 25px rgba(34, 197, 94, 0.35);
            margin-bottom: 16px;
        }
        .order-code-tag {
            display: inline-block;
            background: #ffffff;
            color: #0f172a;
            border: 2px dashed #0f172a;
            padding: 8px 24px;
            border-radius: 30px;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin: 12px 0;
        }
        /* Timeline */
        .order-timeline {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 30px 0 20px 0;
        }
        .order-timeline::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 5%;
            right: 5%;
            height: 4px;
            background: #e2e8f0;
            z-index: 1;
        }
        .timeline-step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }
        .timeline-step .step-circle {
            width: 42px;
            height: 42px;
            background: #ffffff;
            border: 3px solid #cbd5e1;
            color: #64748b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px auto;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .timeline-step.active .step-circle {
            background: #0f172a;
            border-color: #0f172a;
            color: #ffffff;
            box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.15);
        }
        .timeline-step.completed .step-circle {
            background: #22c55e;
            border-color: #22c55e;
            color: #ffffff;
        }
        .timeline-step .step-title {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }
        /* Product List in Order */
        .order-product-card {
            display: flex;
            align-items: center;
            padding: 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .order-product-card:last-child {
            border-bottom: none;
        }
        .order-product-thumb {
            width: 76px;
            height: 76px;
            background: #f8fafc;
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            flex-shrink: 0;
            border: 1px solid #e2e8f0;
        }
        .order-product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 4px;
        }
        .banking-qr-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
        }
        .banking-qr-img {
            max-width: 220px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
    <!-- Header dùng chung -->
    <?php include ('../includes/header.php'); ?>

    <div class="container my-4">
        <!-- Breadcrumb -->
        <nav class="zyro-breadcrumb" aria-label="breadcrumb">
            <a href="/Zyro/index.php">Trang chủ</a>
            <span>/</span>
            <a href="/Zyro/pages/GioHang.php">Giỏ hàng</a>
            <span>/</span>
            <span class="text-dark fw-bold">Xác nhận đơn hàng</span>
        </nav>

        <?php if (!$order): ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4">
                <div class="mb-3">
                    <i class="fas fa-search fa-3x text-muted"></i>
                </div>
                <h3 class="fw-bold mb-2">Không Tìm Thấy Đơn Hàng</h3>
                <p class="text-muted mb-4">Đơn hàng không tồn tại hoặc đã bị xóa. Vui lòng kiểm tra lại liên kết hoặc tra cứu đơn hàng của bạn.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="/Zyro/index.php" class="btn btn-dark rounded-pill px-4 py-2">Về Trang Chủ</a>
                    <a href="/Zyro/pages/LichSuDonHang.php" class="btn btn-outline-dark rounded-pill px-4 py-2">Tra Cứu Đơn Hàng</a>
                </div>
            </div>
        <?php else: 
            $orderCode = 'ZYRO-DH' . str_pad($order['orders_id'], 5, '0', STR_PAD_LEFT);
            $orderDate = date("d/m/Y H:i", strtotime($order['order_date']));
            $status = $order['status'] ? $order['status'] : 'Chờ xử lý';
            $isPayOS = ($order['payment_method'] === 'PAYOS' || $order['payment_method'] === 'BANKING');
            $isPaid = ($status === 'Đã thanh toán');
            $isPendingPayment = ($status === 'Chờ thanh toán');
            
            // Tính subtotal
            $subtotal = 0;
            foreach ($orderItems as $it) {
                $subtotal += $it['price'] * $it['quantity'];
            }
            $shippingFee = max(0, $order['total_price'] - $subtotal);
        ?>
            <!-- Hero Success Box -->
            <div class="order-success-hero">
                <div class="order-success-icon" style="<?php echo $isPaid ? 'background: #10b981; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.35);' : ($isPendingPayment ? 'background: #f59e0b; box-shadow: 0 10px 25px rgba(245, 158, 11, 0.35);' : ''); ?>">
                    <i class="fas <?php echo $isPaid ? 'fa-check-double' : ($isPendingPayment ? 'fa-clock' : 'fa-check'); ?>"></i>
                </div>
                
                <?php if ($isPaid): ?>
                    <h1 class="h3 fw-bold mb-1 text-success">THANH TOÁN THÀNH CÔNG QUA PAYOS!</h1>
                    <p class="text-muted mb-2">Giao dịch trực tuyến của bạn đã được cổng thanh toán <strong>PayOS</strong> xác nhận tự động. ZYRO đang đóng gói sản phẩm để gửi sớm nhất!</p>
                <?php elseif ($isPendingPayment): ?>
                    <h1 class="h3 fw-bold mb-1 text-dark">ĐÃ TIẾP NHẬN ĐƠN HÀNG — CHỜ QUÉT MÃ THANH TOÁN</h1>
                    <p class="text-muted mb-2">Đơn hàng #<?php echo $orderCode; ?> đang chờ bạn quét mã QR PayOS để hoàn tất xác thực thanh toán.</p>
                <?php else: ?>
                    <h1 class="h3 fw-bold mb-1 text-dark">ĐẶT HÀNG TRỰC TUYẾN THÀNH CÔNG!</h1>
                    <p class="text-muted mb-2">Cảm ơn bạn đã tin chọn <strong>ZYRO Store</strong>. Đơn hàng tiền mặt (COD) của bạn đã được ghi nhận vào hệ thống.</p>
                <?php endif; ?>

                <div class="order-code-tag">
                    Mã Vận Đơn: #<?php echo $orderCode; ?>
                </div>
                <div class="text-muted small">
                    Thời gian đặt: <strong><?php echo $orderDate; ?></strong> | Trạng thái: 
                    <?php if ($isPaid): ?>
                        <span class="badge bg-success px-3 py-1 rounded-pill">ĐÃ THANH TOÁN (PAYOS VERIFIED)</span>
                    <?php elseif ($isPendingPayment): ?>
                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill">CHỜ THANH TOÁN PAYOS</span>
                    <?php else: ?>
                        <span class="badge bg-secondary px-3 py-1 rounded-pill"><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Timeline 4 Bước -->
                <div class="order-timeline mt-4">
                    <div class="timeline-step completed">
                        <div class="step-circle"><i class="fas fa-check"></i></div>
                        <div class="step-title">Đã Tiếp Nhận</div>
                    </div>
                    <div class="timeline-step active">
                        <div class="step-circle"><i class="fas fa-box"></i></div>
                        <div class="step-title">Đang Đóng Gói</div>
                    </div>
                    <div class="timeline-step">
                        <div class="step-circle"><i class="fas fa-shipping-fast"></i></div>
                        <div class="step-title">Đang Vận Chuyển</div>
                    </div>
                    <div class="timeline-step">
                        <div class="step-circle"><i class="fas fa-home"></i></div>
                        <div class="step-title">Giao Thành Công</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Cột Trái: Danh Sách Sản Phẩm Đã Đặt -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white py-3 border-light d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark"><i class="fas fa-shopping-bag text-primary me-2"></i>Sản phẩm đã đặt (<?php echo count($orderItems); ?> đôi)</span>
                            <span class="badge bg-light text-dark border">#<?php echo $orderCode; ?></span>
                        </div>
                        <div class="card-body p-0">
                            <?php foreach ($orderItems as $item): 
                                $pId = (int)$item['product_id'];
                                $pName = htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8');
                                $pBrand = htmlspecialchars($item['brand_name'] ? $item['brand_name'] : 'ZYRO', ENT_QUOTES, 'UTF-8');
                                $pSize = htmlspecialchars($item['size'], ENT_QUOTES, 'UTF-8');
                                $pQty = (int)$item['quantity'];
                                $pPrice = $item['price'];
                                $pSubtotal = $pPrice * $pQty;
                                $pImg = htmlspecialchars($item['image_url'] ? $item['image_url'] : 'trending-NIKE+WAFFLE+NAV.png', ENT_QUOTES, 'UTF-8');
                            ?>
                                <div class="order-product-card">
                                    <div class="order-product-thumb">
                                        <img src="/Zyro/assets/img/<?php echo $pImg; ?>" alt="<?php echo $pName; ?>">
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="small text-muted fw-semibold"><?php echo $pBrand; ?></div>
                                        <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo $pId; ?>" class="text-dark fw-bold text-decoration-none">
                                            <?php echo $pName; ?>
                                        </a>
                                        <div class="small text-muted mt-1">
                                            Kích cỡ: <strong class="text-dark">Size EU <?php echo $pSize; ?></strong> | Số lượng: <strong class="text-dark"><?php echo $pQty; ?></strong>
                                        </div>
                                    </div>
                                    <div class="text-end ps-3">
                                        <div class="fw-bold text-dark"><?php echo number_format($pSubtotal, 0, ',', '.'); ?>₫</div>
                                        <div class="small text-muted"><?php echo number_format($pPrice, 0, ',', '.'); ?>₫/đôi</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-footer bg-light p-3 border-0">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Tiền hàng:</span>
                                <span><?php echo number_format($subtotal, 0, ',', '.'); ?>₫</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-2">
                                <span>Phí vận chuyển toàn quốc:</span>
                                <span><?php echo ($shippingFee == 0) ? '<strong class="text-success">MIỄN PHÍ</strong>' : number_format($shippingFee, 0, ',', '.') . '₫'; ?></span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold text-dark fs-5 border-top pt-2">
                                <span>Tổng cộng thanh toán:</span>
                                <span class="text-danger"><?php echo number_format($order['total_price'], 0, ',', '.'); ?>₫</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cột Phải: Thông Tin Giao Nhận & Thanh Toán -->
                <div class="col-lg-5">
                    <!-- Khối Người Nhận -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white py-3 border-light">
                            <span class="fw-bold text-dark"><i class="fas fa-map-marker-alt text-danger me-2"></i>Thông Tin Giao Hàng</span>
                        </div>
                        <div class="card-body p-4">
                            <p class="mb-2"><strong>Họ và tên:</strong> <?php echo htmlspecialchars($order['receiver_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="mb-2"><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['receiver_phone'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="mb-2"><strong>Địa chỉ nhận hàng:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'], ENT_QUOTES, 'UTF-8')); ?></p>
                            <?php if (!empty($order['note'])): ?>
                                <p class="mb-2"><strong>Ghi chú:</strong> <span class="fst-italic text-muted"><?php echo htmlspecialchars($order['note'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                            <?php endif; ?>
                            <p class="mb-0">
                                <strong>Phương thức:</strong> 
                                <?php if ($isPayOS): ?>
                                    <span class="badge bg-primary px-3 py-1 rounded-pill">💳 Cổng Thanh Toán PayOS (VietQR)</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary px-3 py-1 rounded-pill">💵 Tiền mặt khi nhận hàng (COD)</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Khối Chi Tiết Giao Dịch PayOS -->
                    <?php if ($isPayOS): ?>
                        <?php if ($isPaid): ?>
                            <!-- Trạng thái 1: Đã thanh toán thành công qua Webhook PayOS -->
                            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #f0fdf4; border: 1px solid #bbf7d0 !important;">
                                <div class="card-body p-4 text-center">
                                    <div style="font-size: 32px; color: #16a34a; margin-bottom: 8px;">
                                        <i class="fas fa-shield-check"></i>
                                    </div>
                                    <h5 class="fw-bold text-success mb-2">Đã Xác Thực Thanh Toán Qua PayOS</h5>
                                    <p class="small text-muted mb-3">Giao dịch đã được hệ thống ngân hàng và PayOS xác nhận tự động.</p>
                                    
                                    <div class="small text-start bg-white p-3 rounded-3 border">
                                        <div class="mb-1"><strong>Mã ghi chú ZYRO:</strong> <code class="text-primary fs-6"><?php echo htmlspecialchars($order['payment_code'], ENT_QUOTES, 'UTF-8'); ?></code></div>
                                        <?php if (!empty($order['payos_transaction_id'])): ?>
                                            <div class="mb-1"><strong>Mã giao dịch PayOS:</strong> <span class="text-dark fw-bold"><?php echo htmlspecialchars($order['payos_transaction_id'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                                        <?php endif; ?>
                                        <?php if (!empty($order['paid_at'])): ?>
                                            <div class="mb-1"><strong>Thời gian thanh toán:</strong> <?php echo date('d/m/Y H:i:s', strtotime($order['paid_at'])); ?></div>
                                        <?php endif; ?>
                                        <div><strong>Số tiền ghi nhận:</strong> <span class="text-danger fw-bold fs-6"><?php echo number_format($order['total_price'], 0, ',', '.'); ?>₫</span></div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Trạng thái 2: Chờ quét mã thanh toán PayOS -->
                            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #fffbeb; border: 1px solid #fde68a !important;">
                                <div class="card-body p-4 text-center">
                                    <div style="font-size: 32px; color: #d97706; margin-bottom: 8px;">
                                        <i class="fas fa-qrcode"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-2">Đang Chờ Quét Mã Thanh Toán</h5>
                                    <p class="small text-muted mb-3">Đơn hàng #<?php echo $orderCode; ?> chưa được xác nhận thanh toán. Vui lòng quét mã QR PayOS để hoàn tất.</p>
                                    
                                    <div class="small text-start bg-white p-3 rounded-3 border mb-3">
                                        <div class="mb-1"><strong>Mã nội dung CK:</strong> <code class="text-dark fs-6"><?php echo htmlspecialchars($order['payment_code'], ENT_QUOTES, 'UTF-8'); ?></code></div>
                                        <div><strong>Số tiền cần chuyển:</strong> <span class="text-danger fw-bold fs-6"><?php echo number_format($order['total_price'], 0, ',', '.'); ?>₫</span></div>
                                    </div>

                                    <a href="/Zyro/pages/ThanhToanPayOS.php?order_id=<?php echo (int)$order['orders_id']; ?>" 
                                       class="btn btn-primary rounded-pill w-100 fw-bold py-2 shadow-sm">
                                        <i class="fas fa-camera me-1"></i> Mở Trang Quét Mã QR PayOS Ngay
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Các Nút Hành Động -->
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                        <div class="d-grid gap-2">
                            <a href="/Zyro/pages/LichSuDonHang.php" class="btn btn-dark rounded-pill fw-bold py-2">
                                <i class="fas fa-list-alt me-2"></i>Theo Dõi Lịch Sử Đơn Hàng
                            </a>
                            <a href="/Zyro/pages/SanPham.php" class="btn btn-outline-dark rounded-pill fw-semibold py-2">
                                <i class="fas fa-shopping-bag me-2"></i>Tiếp Tục Mua Sắm
                            </a>
                            <button onclick="window.print()" class="btn btn-light rounded-pill border small py-2">
                                <i class="fas fa-print me-1"></i>In Chi Tiết Đơn Hàng
                            </button>
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
