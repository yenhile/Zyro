<?php
if (session_id() == '') {
    session_start();
}
require_once('../class/classProduct.php');
require_once('../class/classPayOS.php');

$productModel = new san_pham();

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : (isset($_SESSION['last_order_id']) ? (int)$_SESSION['last_order_id'] : 0);

if ($orderId <= 0) {
    header("Location: /Zyro/pages/GioHang.php");
    exit();
}

$order = $productModel->lay_thong_tin_don_hang($orderId);
if (!$order) {
    header("Location: /Zyro/pages/GioHang.php");
    exit();
}

// Nếu đơn hàng đã được thanh toán rồi, chuyển hướng thẳng sang trang Thành Công
if (trim($order['status']) === 'Đã thanh toán' || trim($order['status']) === 'Đã giao' || trim($order['status']) === 'Đang giao') {
    header("Location: /Zyro/pages/DatHangThanhCong.php?order_id=" . $orderId);
    exit();
}

$orderItems = $productModel->lay_chi_tiet_san_pham_don_hang($orderId);
$orderCode = (int)$order['orders_id'];
$amount = (int)$order['total_price'];

// Sinh mã ghi chú tự sinh định danh nếu chưa có trong CSDL
$paymentCode = !empty($order['payment_code']) ? $order['payment_code'] : PayOS_Service::sinh_ma_ghi_chu($orderId);

// Đảm bảo CSDL lưu mã ghi chú tự sinh và payos_order_code
if (empty($order['payment_code']) || empty($order['payos_order_code'])) {
    $productModel->cap_nhat_thong_tin_payos($orderId, $paymentCode, $orderCode);
}

// Chuẩn bị URL callback
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$returnUrl = $protocol . $host . "/Zyro/pages/DatHangThanhCong.php?order_id=" . $orderId;
$cancelUrl = $protocol . $host . "/Zyro/pages/GioHang.php?order_cancelled=" . $orderId;

$buyerInfo = array(
    'name' => $order['receiver_name'],
    'phone' => $order['receiver_phone'],
    'address' => $order['shipping_address']
);

// Tạo link và mã QR PayOS
$payosResult = PayOS_Service::tao_link_thanh_toan($orderCode, $amount, $paymentCode, array(), $returnUrl, $cancelUrl, $buyerInfo);
$qrImageUrl = $payosResult['qr_image_url'];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán Đơn Hàng #<?php echo $orderCode; ?> — ZYRO Store</title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="/Zyro/assets/css/bootstrap.css">
    <link rel="stylesheet" href="/Zyro/assets/css/layout.css">
    <link rel="stylesheet" href="/Zyro/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="/Zyro/assets/js/bootstrap.bundle.min.js"></script>
    <style>
        .payment-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .payment-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }

        .qr-wrapper {
            background: #f8fafc;
            border: 2px dashed #0284c7;
            border-radius: 16px;
            padding: 20px;
            display: inline-block;
            margin: 20px auto;
            position: relative;
        }

        .qr-image {
            max-width: 260px;
            width: 100%;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            background: #ffffff;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .copy-btn {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #0284c7;
            cursor: pointer;
            transition: all 0.2s;
        }

        .copy-btn:hover {
            background: #0284c7;
            color: #ffffff;
        }

        .timer-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fef3c7;
            color: #92400e;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
        }

        .pulse-dot {
            width: 10px;
            height: 10px;
            background-color: #0284c7;
            border-radius: 50%;
            display: inline-block;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(2, 132, 199, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 8px rgba(2, 132, 199, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(2, 132, 199, 0); }
        }

        /* Modal Overlay khi thanh toán thành công */
        .success-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.85);
            z-index: 99999;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
        }

        .success-modal {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: popIn 0.3s ease-out;
        }

        @keyframes popIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>
</head>

<body style="background-color: #f8fafc;">
    <!-- Header dùng chung -->
    <?php include('../includes/header.php'); ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                <div class="payment-card">
                    <!-- Header Thanh Toán -->
                    <div class="payment-header">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary px-3 py-1 rounded-pill">Cổng Thanh Toán PayOS</span>
                            <span class="timer-badge">
                                <i class="fas fa-clock"></i> <span id="countdown">15:00</span>
                            </span>
                        </div>
                        <h2 class="h4 fw-bold mb-1">QUÉT MÃ QR ĐỂ THANH TOÁN</h2>
                        <p class="small text-light text-opacity-75 mb-0">Hệ thống tự động kích hoạt đơn ngay khi tài khoản nhận được tiền</p>
                    </div>

                    <div class="p-4 text-center">
                        <!-- Khung Mã QR Thanh Toán Auto-Fill -->
                        <div class="qr-wrapper">
                            <img src="<?php echo htmlspecialchars($qrImageUrl, ENT_QUOTES, 'UTF-8'); ?>" 
                                 alt="VietQR PayOS" 
                                 class="qr-image" 
                                 id="payosQrImg">
                            <div class="mt-2 small text-muted">
                                <i class="fas fa-camera text-primary me-1"></i> Mở App Ngân hàng bất kỳ hoặc Ví điện tử để quét mã
                            </div>
                        </div>

                        <!-- Trạng thái chờ thanh toán -->
                        <div class="alert alert-info py-2 px-3 small rounded-3 d-inline-flex align-items-center gap-2 mb-4">
                            <span class="pulse-dot"></span>
                            <span>Đang chờ chuyển khoản... (Hệ thống tự động đối soát qua Webhook PayOS)</span>
                        </div>

                        <!-- Chi tiết thông tin chuyển khoản (Auto-Fill) -->
                        <div class="card border-0 bg-light rounded-4 text-start mb-4">
                            <div class="info-row">
                                <span class="text-muted">Ngân hàng thụ hưởng:</span>
                                <strong class="text-dark">MB Bank (Ngân hàng TMCP Quân Đội)</strong>
                            </div>
                            <div class="info-row">
                                <span class="text-muted">Số tài khoản:</span>
                                <div>
                                    <strong class="text-primary fs-6 me-2" id="valAccountNo">0386888999</strong>
                                    <button type="button" class="copy-btn" onclick="copyToClipboard('0386888999')">
                                        <i class="fas fa-copy"></i> Sao chép
                                    </button>
                                </div>
                            </div>
                            <div class="info-row">
                                <span class="text-muted">Chủ tài khoản:</span>
                                <strong class="text-dark">CONG TY TNHH ZYRO STORE</strong>
                            </div>
                            <div class="info-row">
                                <span class="text-muted">Số tiền thanh toán:</span>
                                <div>
                                    <strong class="text-danger fs-5 me-2" id="valAmount"><?php echo number_format($amount); ?>₫</strong>
                                    <button type="button" class="copy-btn" onclick="copyToClipboard('<?php echo $amount; ?>')">
                                        <i class="fas fa-copy"></i> Sao chép
                                    </button>
                                </div>
                            </div>
                            <div class="info-row">
                                <span class="text-muted">Nội dung ghi chú (*Bắt buộc):</span>
                                <div>
                                    <code class="fs-6 text-dark bg-white px-2 py-1 rounded border me-2" id="valMemo"><?php echo htmlspecialchars($paymentCode, ENT_QUOTES, 'UTF-8'); ?></code>
                                    <button type="button" class="copy-btn" onclick="copyToClipboard('<?php echo addslashes($paymentCode); ?>')">
                                        <i class="fas fa-copy"></i> Sao chép
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Lưu ý chuyển tiền -->
                        <div class="text-start small text-muted mb-4 p-3 bg-white border rounded-3">
                            <strong class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Lưu ý quan trọng:</strong>
                            <ul class="mb-0 mt-1 ps-3">
                                <li>Vui lòng giữ nguyên <strong>Mã ghi chú</strong> để giao dịch được Webhook tự động xác nhận tức thì.</li>
                                <li>Không chỉnh sửa số tiền thanh toán hiển thị trên App ngân hàng.</li>
                            </ul>
                        </div>

                        <!-- Các hành động thay thế -->
                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <a href="/Zyro/pages/GioHang.php" class="btn btn-link text-muted text-decoration-none small">
                                ← Quay lại Giỏ Hàng
                            </a>
                            <a href="/Zyro/pages/DatHangThanhCong.php?order_id=<?php echo $orderCode; ?>" class="btn btn-outline-dark rounded-pill px-4 btn-sm">
                                Xem Thông Tin Đơn Hàng →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Thông Báo Thanh Toán Thành Công -->
    <div id="successOverlay" class="success-overlay">
        <div class="success-modal">
            <div style="width: 72px; height: 72px; background: #22c55e; color: #ffffff; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 36px; margin-bottom: 20px;">
                ✓
            </div>
            <h3 class="fw-bold text-dark mb-2">THANH TOÁN THÀNH CÔNG!</h3>
            <p class="text-muted small mb-4">PayOS đã xác nhận giao dịch chuyển khoản cho đơn hàng #<?php echo $orderCode; ?>. Đang chuyển hướng...</p>
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Footer dùng chung -->
    <?php include('../includes/footer.php'); ?>

    <script>
        // 1. Sao chép nội dung vào Clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Đã sao chép: ' + text);
            }).catch(function(err) {
                // Fallback cho trình duyệt cũ
                var dummy = document.createElement("input");
                document.body.appendChild(dummy);
                dummy.setAttribute("value", text);
                dummy.select();
                document.execCommand("copy");
                document.body.removeChild(dummy);
                alert('Đã sao chép: ' + text);
            });
        }

        // 2. Đồng hồ đếm ngược 15 phút
        var timeLeft = 15 * 60;
        var countdownEl = document.getElementById('countdown');
        var timerInterval = setInterval(function() {
            timeLeft--;
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                countdownEl.innerText = "Hết hạn";
            } else {
                var m = Math.floor(timeLeft / 60);
                var s = timeLeft % 60;
                countdownEl.innerText = (m < 10 ? "0" + m : m) + ":" + (s < 10 ? "0" + s : s);
            }
        }, 1000);

        // 3. Cơ chế Polling tự động kiểm tra trạng thái thanh toán qua Webhook mỗi 3 giây
        var orderId = <?php echo $orderCode; ?>;
        var pollInterval = setInterval(function() {
            fetch('/Zyro/api/check_order_status.php?order_id=' + orderId)
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data && data.is_paid) {
                        clearInterval(pollInterval);
                        clearInterval(timerInterval);
                        // Hiển thị Popup thành công
                        document.getElementById('successOverlay').style.display = 'flex';
                        setTimeout(function() {
                            window.location.href = '/Zyro/pages/DatHangThanhCong.php?order_id=' + orderId + '&paid=1';
                        }, 2000);
                    }
                })
                .catch(function(err) {
                    console.log('Polling check error:', err);
                });
        }, 3000);
    </script>
</body>

</html>
