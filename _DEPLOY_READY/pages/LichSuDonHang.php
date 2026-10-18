<?php
if (session_id() == '') {
    session_start();
}
require_once('../class/classProduct.php');

$productModel = new san_pham();

// Tự động kiểm tra hủy các đơn PayOS quá 10 phút chưa thanh toán
$productModel->tu_dong_huy_don_qua_han_payos();

$currentUserId = null;
if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0) {
    $currentUserId = (int)$_SESSION['user_id'];
} elseif (isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['user_id'])) {
    $currentUserId = (int)$_SESSION['user']['user_id'];
}
$isLoggedIn = ($currentUserId !== null && $currentUserId > 0);

$successMessage = '';
$errorMessage = '';

// 1. Xử lý yêu cầu Hủy đơn hàng trực tuyến
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_order') {
    $cancelOrderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $verifyPhone = isset($_POST['verify_phone']) ? trim($_POST['verify_phone']) : null;

    if ($cancelOrderId > 0) {
        $cancelResult = $productModel->huy_don_hang_khach($cancelOrderId, $currentUserId, $verifyPhone);
        if ($cancelResult['success']) {
            $successMessage = $cancelResult['message'];
        } else {
            $errorMessage = $cancelResult['message'];
        }
    }
}

// 2. Tra cứu đơn hàng cho khách vãng lai
$searchedOrder = null;
$searchedOrderItems = array();
$searchOrderId = '';
$searchPhone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'search_order') {
    $rawOrderId = isset($_POST['search_order_id']) ? trim($_POST['search_order_id']) : '';
    $searchPhone = isset($_POST['search_phone']) ? trim($_POST['search_phone']) : '';

    // Loại bỏ tiền tố ZYRO-DH nếu người dùng nhập kèm
    $cleanOrderId = preg_replace('/[^0-9]/', '', $rawOrderId);
    $searchOrderId = (int)$cleanOrderId;

    if ($searchOrderId <= 0 || empty($searchPhone)) {
        $errorMessage = "Vui lòng nhập đầy đủ Mã đơn hàng và Số điện thoại đã đặt!";
    } else {
        $searchedOrder = $productModel->tra_cuu_don_hang($searchOrderId, $searchPhone);
        if ($searchedOrder) {
            $searchedOrderItems = $productModel->lay_chi_tiet_san_pham_don_hang($searchOrderId);
        } else {
            $errorMessage = "Không tìm thấy đơn hàng nào khớp với mã #$rawOrderId và số điện thoại $searchPhone!";
        }
    }
}

// 3. Nếu đã đăng nhập, lấy danh sách tất cả đơn hàng của tài khoản
$myOrders = array();
if ($isLoggedIn) {
    $myOrders = $productModel->lay_danh_sach_don_hang_user($currentUserId);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử & Theo Dõi Đơn Hàng — ZYRO Store</title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="/Zyro/assets/css/bootstrap.css">
    <!-- Style chính có cache-busting -->
    <link rel="stylesheet" href="/Zyro/assets/css/layout.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/Zyro/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="/Zyro/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/Zyro/assets/js/action.js"></script>
    <style>
        .order-history-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            transition: all 0.25s ease;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .order-history-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            border-color: #cbd5e1;
        }
        .order-history-header {
            background: #f8fafc;
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .badge-status-choxuly { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-status-chothanhtoan { background: #fffbeb; color: #b45309; border: 1px solid #fcd34d; }
        .badge-status-danggiao { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .badge-status-dagiao { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-status-dahuy { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-status-hoantra { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
        .badge-status-dahoantra { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }

        .search-order-box {
            background: #ffffff;
            border: 2px dashed #cbd5e1;
            border-radius: 18px;
            padding: 30px;
            margin-bottom: 30px;
        }

        /* Dynamic Order Stepper */
        .order-timeline {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 25px 0 15px 0;
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
            width: 40px;
            height: 40px;
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
    </style>
</head>
<body>
    <!-- Header dùng chung -->
    <?php include ('../includes/header.php'); ?>

    <main class="page-content-wrapper">
    <div class="container my-4">
        <!-- Breadcrumb -->
        <nav class="zyro-breadcrumb" aria-label="breadcrumb">
            <a href="/Zyro/index.php">Trang chủ</a>
            <span>/</span>
            <span class="text-dark fw-bold">Theo dõi & Lịch sử đơn hàng</span>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h1 class="h3 fw-bold mb-1 text-dark">
                    <i class="fas fa-truck-fast text-primary me-2"></i>Theo Dõi & Lịch Sử Đơn Hàng
                </h1>
                <p class="text-muted small mb-0">Tra cứu tình trạng vận chuyển và kiểm tra các đơn hàng đã đặt online tại ZYRO Store</p>
            </div>
            <div>
                <a href="/Zyro/pages/SanPham.php" class="btn btn-outline-dark rounded-pill btn-sm px-3">
                    <i class="fas fa-shopping-bag me-1"></i>Tiếp Tục Mua Sắm
                </a>
            </div>
        </div>

        <!-- Thông báo alert -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- 1. HỘP TRA CỨU ĐƠN HÀNG CHO KHÁCH HÀNG (Dành cho cả khách vãng lai hoặc kiểm tra nhanh) -->
        <div class="search-order-box shadow-sm">
            <h4 class="fw-bold text-dark mb-2">
                <i class="fas fa-search text-primary me-2"></i>Tra Cứu Đơn Hàng Nhanh
            </h4>
            <p class="text-muted small mb-3">Nhập Mã đơn hàng (ví dụ: <code>ZYRO-DH00001</code> hoặc số <code>1</code>) và Số điện thoại nhận hàng:</p>

            <form action="LichSuDonHang.php" method="POST" class="row g-3">
                <input type="hidden" name="action" value="search_order">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Mã đơn hàng (*):</label>
                    <input type="text" name="search_order_id" class="form-control rounded-pill" 
                           placeholder="Ví dụ: ZYRO-DH00001 hoặc 1" required
                           value="<?php echo isset($_POST['search_order_id']) ? htmlspecialchars($_POST['search_order_id']) : ''; ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Số điện thoại người nhận (*):</label>
                    <input type="tel" name="search_phone" class="form-control rounded-pill" 
                           placeholder="Số điện thoại đã dùng khi đặt hàng" required
                           value="<?php echo isset($_POST['search_phone']) ? htmlspecialchars($_POST['search_phone']) : ''; ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark rounded-pill fw-bold w-100 py-2">
                        <i class="fas fa-search me-1"></i>Tra Cứu
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. KẾT QUẢ TRA CỨU ĐƠN HÀNG (NẾU CÓ) -->
        <?php if ($searchedOrder): 
            $sId = (int)$searchedOrder['orders_id'];
            $sCode = 'ZYRO-DH' . str_pad($sId, 5, '0', STR_PAD_LEFT);
            $sDate = date("d/m/Y H:i", strtotime($searchedOrder['order_date']));
            $sStatus = $searchedOrder['status'] ? $searchedOrder['status'] : 'Chờ xử lý';
            $sIsPayOS = san_pham::la_thanh_toan_banking($searchedOrder['payment_method']) || !empty($searchedOrder['payos_transaction_id']);
            $sIsRefundFlow = ($sStatus === 'Hoàn trả' || $sStatus === 'Đã hoàn trả' || stripos($sStatus, 'hoàn') !== false);
            $sIsCancelled = ($sStatus === 'Đã hủy' || stripos($sStatus, 'hủy') !== false);
            $sCanCancel = ($sStatus === 'Chờ xử lý' || $sStatus === 'Cho xu ly' || stripos($sStatus, 'chờ') !== false || stripos($sStatus, 'cho') !== false);
            
            // Badge status class
            $sBadgeClass = 'badge-status-choxuly';
            if ($sStatus === 'Chờ thanh toán') $sBadgeClass = 'badge-status-chothanhtoan';
            elseif ($sStatus === 'Đang giao') $sBadgeClass = 'badge-status-danggiao';
            elseif ($sStatus === 'Đã giao') $sBadgeClass = 'badge-status-dagiao';
            elseif ($sStatus === 'Hoàn trả') $sBadgeClass = 'badge-status-hoantra';
            elseif ($sStatus === 'Đã hoàn trả') $sBadgeClass = 'badge-status-dahoantra';
            elseif ($sIsCancelled) $sBadgeClass = 'badge-status-dahuy';
        ?>
            <div class="card border-primary border-2 shadow-sm rounded-4 mb-5 overflow-hidden">
                <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="fw-bold"><i class="fas fa-check-circle text-primary me-2"></i>Kết Quả Tra Cứu: Đơn Hàng #<?php echo $sCode; ?></span>
                    <span class="badge <?php echo $sBadgeClass; ?> fw-bold px-3 py-2 rounded-pill"><?php echo htmlspecialchars($sStatus, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="card-body p-4">
                    
                    <!-- TIẾN TRÌNH THAY ĐỔI ĐỘNG (DYNAMIC STEPPER) -->
                    <div class="mb-4">
                        <?php if ($sIsCancelled): ?>
                            <div class="alert alert-danger rounded-4 py-2 px-3 small d-flex align-items-center mb-0">
                                <i class="fas fa-exclamation-triangle fa-lg me-2"></i>
                                <div>
                                    <strong>Đơn hàng đã bị hủy.</strong>
                                    <?php if (!empty($searchedOrder['refund_note'])): ?>
                                        <span class="ms-1 text-muted">(Lý do: <?php echo htmlspecialchars($searchedOrder['refund_note'], ENT_QUOTES, 'UTF-8'); ?>)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php elseif ($sIsRefundFlow): ?>
                            <!-- Luồng Hoàn Trả Động: Bỏ hẳn luồng Đã giao -->
                            <div class="order-timeline">
                                <div class="timeline-step completed">
                                    <div class="step-circle"><i class="fas fa-check"></i></div>
                                    <div class="step-title">Đặt Hàng</div>
                                </div>
                                <div class="timeline-step completed">
                                    <div class="step-circle"><i class="fas fa-check"></i></div>
                                    <div class="step-title">Chờ Xử Lý</div>
                                </div>
                                <div class="timeline-step <?php echo ($sStatus === 'Hoàn trả') ? 'active' : 'completed'; ?>">
                                    <div class="step-circle" style="<?php echo ($sStatus === 'Hoàn trả') ? 'background: #f97316; border-color: #ea580c; color: white;' : 'background: #ea580c; border-color: #ea580c; color: white;'; ?>">
                                        <i class="fas <?php echo ($sStatus === 'Hoàn trả') ? 'fa-sync-alt fa-spin' : 'fa-check'; ?>"></i>
                                    </div>
                                    <div class="step-title" style="color: #ea580c; font-weight: 700;">Yêu Cầu Hoàn Trả</div>
                                </div>
                                <div class="timeline-step <?php echo ($sStatus === 'Đã hoàn trả') ? 'completed' : ''; ?>">
                                    <div class="step-circle" style="<?php echo ($sStatus === 'Đã hoàn trả') ? 'background: #059669; border-color: #059669; color: white;' : ''; ?>">
                                        <i class="fas fa-hand-holding-usd"></i>
                                    </div>
                                    <div class="step-title" style="<?php echo ($sStatus === 'Đã hoàn trả') ? 'color: #059669; font-weight: 700;' : ''; ?>">Đã Hoàn Trả</div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Luồng Giao Hàng Chuẩn Động -->
                            <?php
                            $sPendingDone = ($sStatus === 'Đang giao' || $sStatus === 'Đã giao');
                            $sPendingActive = ($sStatus === 'Chờ xử lý' || $sStatus === 'Chờ thanh toán');
                            $sShippingDone = ($sStatus === 'Đã giao');
                            $sShippingActive = ($sStatus === 'Đang giao');
                            $sDeliveredDone = ($sStatus === 'Đã giao');
                            ?>
                            <div class="order-timeline">
                                <div class="timeline-step completed">
                                    <div class="step-circle"><i class="fas fa-check"></i></div>
                                    <div class="step-title">Đặt Hàng</div>
                                </div>
                                <div class="timeline-step <?php echo $sPendingDone ? 'completed' : ($sPendingActive ? 'active' : ''); ?>">
                                    <div class="step-circle"><i class="fas <?php echo $sPendingDone ? 'fa-check' : 'fa-box'; ?>"></i></div>
                                    <div class="step-title">Chờ Xử Lý</div>
                                </div>
                                <div class="timeline-step <?php echo $sShippingDone ? 'completed' : ($sShippingActive ? 'active' : ''); ?>">
                                    <div class="step-circle"><i class="fas <?php echo $sShippingDone ? 'fa-check' : 'fa-shipping-fast'; ?>"></i></div>
                                    <div class="step-title">Đang Giao Hàng</div>
                                </div>
                                <div class="timeline-step <?php echo $sDeliveredDone ? 'completed' : ''; ?>">
                                    <div class="step-circle"><i class="fas fa-home"></i></div>
                                    <div class="step-title">Giao Thành Công</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold border-bottom pb-2 text-dark"><i class="fas fa-user text-primary me-2"></i>Thông Tin Người Nhận</h6>
                            <p class="mb-1"><strong>Họ tên:</strong> <?php echo htmlspecialchars($searchedOrder['receiver_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="mb-1"><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($searchedOrder['receiver_phone'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="mb-1"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($searchedOrder['shipping_address'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="mb-0">
                                <strong>Phương thức:</strong> 
                                <?php if ($sIsPayOS): ?>
                                    <span class="badge bg-primary px-3 py-1 rounded-pill">💳 Chuyển khoản VietQR (PayOS)</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary px-3 py-1 rounded-pill">💵 Tiền mặt (COS)</span>
                                    <?php if (!empty($searchedOrder['collected_amount']) && (float)$searchedOrder['collected_amount'] > 0): ?>
                                        <span class="text-success small fw-bold ms-1">(Đã thu: <?php echo number_format($searchedOrder['collected_amount'], 0, ',', '.'); ?>₫)</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold border-bottom pb-2 text-dark"><i class="fas fa-clock text-primary me-2"></i>Thời Gian & Tiến Trình</h6>
                            <p class="mb-1"><strong>Ngày đặt:</strong> <?php echo $sDate; ?></p>
                            <p class="mb-1"><strong>Trạng thái:</strong> <span class="badge <?php echo $sBadgeClass; ?>"><?php echo htmlspecialchars($sStatus, ENT_QUOTES, 'UTF-8'); ?></span></p>
                            <p class="mb-1"><strong>Tổng thanh toán:</strong> <span class="text-danger fw-bold fs-5"><?php echo number_format($searchedOrder['total_price'], 0, ',', '.'); ?>₫</span></p>

                            <!-- Thông tin Hoàn Trả nếu có -->
                            <?php if ($sIsRefundFlow): ?>
                                <div class="p-2 mt-2 rounded bg-light border">
                                    <div class="text-primary fw-bold small mb-1"><i class="fas fa-undo-alt me-1"></i>Chi Tiết Hoàn Trả:</div>
                                    <?php if (!empty($searchedOrder['refund_amount']) && (float)$searchedOrder['refund_amount'] > 0): ?>
                                        <div class="small">Số tiền hoàn: <strong class="text-danger"><?php echo number_format($searchedOrder['refund_amount'], 0, ',', '.'); ?>₫</strong></div>
                                    <?php endif; ?>
                                    <?php if (!empty($searchedOrder['refund_note'])): ?>
                                        <div class="small text-muted">Lý do: <em><?php echo htmlspecialchars($searchedOrder['refund_note'], ENT_QUOTES, 'UTF-8'); ?></em></div>
                                    <?php endif; ?>
                                    <?php if (!empty($searchedOrder['refund_proof_image'])): ?>
                                        <div class="small mt-1">
                                            <a href="/Zyro/uploads/refunds/<?php echo htmlspecialchars($searchedOrder['refund_proof_image'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="fw-bold text-success text-decoration-none">
                                                <i class="fas fa-image me-1"></i>Xem Minh Chứng Chuyển Khoản Hoàn Tiền
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Bảng sản phẩm -->
                    <h6 class="fw-bold mb-3 text-dark"><i class="fas fa-shoe-prints text-primary me-2"></i>Chi Tiết Sản Phẩm:</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Kích cỡ</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end">Đơn giá</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($searchedOrderItems as $sItem): 
                                    $pName = htmlspecialchars($sItem['product_name'], ENT_QUOTES, 'UTF-8');
                                    $pImg = htmlspecialchars($sItem['image_url'] ? $sItem['image_url'] : 'trending-NIKE+WAFFLE+NAV.png', ENT_QUOTES, 'UTF-8');
                                ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="/Zyro/assets/img/<?php echo $pImg; ?>" alt="<?php echo $pName; ?>" style="width: 50px; height: 50px; object-fit: contain; margin-right: 12px; border: 1px solid #eee; border-radius: 6px;">
                                                <strong><?php echo $pName; ?></strong>
                                            </div>
                                        </td>
                                        <td class="text-center"><span class="badge bg-dark">Size <?php echo htmlspecialchars($sItem['size']); ?></span></td>
                                        <td class="text-center"><?php echo (int)$sItem['quantity']; ?></td>
                                        <td class="text-end"><?php echo number_format($sItem['price'], 0, ',', '.'); ?>₫</td>
                                        <td class="text-end fw-bold text-dark"><?php echo number_format($sItem['price'] * $sItem['quantity'], 0, ',', '.'); ?>₫</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Nút Hủy đơn nếu còn Chờ xử lý -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <a href="DatHangThanhCong.php?order_id=<?php echo $sId; ?>" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="fas fa-receipt me-1"></i>Xem Hóa Đơn Chi Tiết
                        </a>
                        <?php if ($sCanCancel): ?>
                            <?php 
                                $confirmMsg = $sIsPayOS 
                                    ? 'Đơn hàng này đã thanh toán qua PayOS. Khi bạn gửi yêu cầu hủy, đơn hàng sẽ chuyển sang trạng thái HOÀN TRẢ để Zyro đối soát và hoàn tiền vào tài khoản của bạn. Bạn có chắc chắn muốn hủy đơn không?' 
                                    : 'Bạn có chắc chắn muốn hủy đơn hàng này không? Thao tác này không thể hoàn tác!';
                            ?>
                            <form action="LichSuDonHang.php" method="POST" onsubmit="return confirm('<?php echo addslashes($confirmMsg); ?>');">
                                <input type="hidden" name="action" value="cancel_order">
                                <input type="hidden" name="order_id" value="<?php echo $sId; ?>">
                                <input type="hidden" name="verify_phone" value="<?php echo htmlspecialchars($searchedOrder['receiver_phone'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-outline-danger rounded-pill px-4">
                                    <i class="fas fa-times me-1"></i>Yêu Cầu Hủy Đơn Hàng
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- 3. DANH SÁCH TOÀN BỘ ĐƠN HÀNG CỦA THÀNH VIÊN ĐÃ ĐĂNG NHẬP -->
        <div class="mt-4">
            <h4 class="fw-bold text-dark mb-3">
                <i class="fas fa-history text-primary me-2"></i>Đơn Hàng Của Bạn
            </h4>

            <?php if (!$isLoggedIn): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-light">
                    <p class="text-muted mb-3">Bạn chưa đăng nhập. Vui lòng đăng nhập để lưu trữ và theo dõi toàn bộ lịch sử đơn hàng của tài khoản!</p>
                    <div>
                        <a href="/Zyro/pages/DangNhap.php" class="btn btn-dark rounded-pill px-4">
                            <i class="fas fa-sign-in-alt me-1"></i>Đăng Nhập Ngay
                        </a>
                    </div>
                </div>
            <?php elseif (empty($myOrders)): ?>
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                    <div class="mb-3">
                        <i class="fas fa-box-open fa-3x text-muted"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Bạn chưa có đơn hàng nào tại ZYRO Store</h5>
                    <p class="text-muted mb-4">Hãy chọn cho mình đôi giày thể thao yêu thích và trải nghiệm mua sắm trực tuyến ngay hôm nay!</p>
                    <div>
                        <a href="/Zyro/pages/SanPham.php" class="btn btn-dark rounded-pill px-4 py-2">
                            Khám Phá Sản Phẩm Ngay
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="order-list">
                    <?php foreach ($myOrders as $ord): 
                        $ordId = (int)$ord['orders_id'];
                        $ordCode = 'ZYRO-DH' . str_pad($ordId, 5, '0', STR_PAD_LEFT);
                        $ordDate = date("d/m/Y H:i", strtotime($ord['order_date']));
                        $ordStatus = $ord['status'] ? $ord['status'] : 'Chờ xử lý';
                        $ordTotal = $ord['total_price'];
                        $ordIsBanking = san_pham::la_thanh_toan_banking($ord['payment_method']) || !empty($ord['payos_transaction_id']);
                        $ordIsRefundFlow = ($ordStatus === 'Hoàn trả' || $ordStatus === 'Đã hoàn trả' || stripos($ordStatus, 'hoàn') !== false);
                        $ordIsCancelled = ($ordStatus === 'Đã hủy' || stripos($ordStatus, 'hủy') !== false);
                        $canCancel = ($ordStatus === 'Chờ xử lý' || $ordStatus === 'Cho xu ly' || stripos($ordStatus, 'chờ') !== false || stripos($ordStatus, 'cho') !== false);

                        $statusClass = 'badge-status-choxuly';
                        if ($ordStatus === 'Chờ thanh toán') $statusClass = 'badge-status-chothanhtoan';
                        elseif ($ordStatus === 'Đang giao') $statusClass = 'badge-status-danggiao';
                        elseif ($ordStatus === 'Đã giao') $statusClass = 'badge-status-dagiao';
                        elseif ($ordStatus === 'Hoàn trả') $statusClass = 'badge-status-hoantra';
                        elseif ($ordStatus === 'Đã hoàn trả') $statusClass = 'badge-status-dahoantra';
                        elseif ($ordIsCancelled) $statusClass = 'badge-status-dahuy';
                    ?>
                        <div class="order-history-card">
                            <div class="order-history-header">
                                <div>
                                    <span class="fw-bold text-dark me-3">Mã đơn: #<?php echo $ordCode; ?></span>
                                    <span class="text-muted small"><i class="far fa-calendar-alt me-1"></i><?php echo $ordDate; ?></span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge <?php echo $statusClass; ?> px-3 py-2 rounded-pill fw-semibold">
                                        <?php echo htmlspecialchars($ordStatus, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- TIẾN TRÌNH THAY ĐỔI ĐỘNG (DYNAMIC STEPPER CHO TỪNG ĐƠN) -->
                            <div class="px-4 pt-3 pb-1 border-bottom bg-light">
                                <?php if ($ordIsCancelled): ?>
                                    <div class="alert alert-danger py-1 px-3 small rounded-3 mb-2 d-flex align-items-center justify-content-between">
                                        <span><i class="fas fa-times-circle me-1"></i>Đơn hàng đã bị hủy.</span>
                                        <?php if (!empty($ord['refund_note'])): ?>
                                            <span class="text-muted fst-italic"><?php echo htmlspecialchars($ord['refund_note'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($ordIsRefundFlow): ?>
                                    <!-- Luồng Hoàn Trả: Bỏ hẳn luồng Đã giao -->
                                    <div class="order-timeline" style="margin: 15px 0 10px 0;">
                                        <div class="timeline-step completed">
                                            <div class="step-circle" style="width: 32px; height: 32px; font-size: 11px;"><i class="fas fa-check"></i></div>
                                            <div class="step-title" style="font-size: 11px;">Đặt Hàng</div>
                                        </div>
                                        <div class="timeline-step completed">
                                            <div class="step-circle" style="width: 32px; height: 32px; font-size: 11px;"><i class="fas fa-check"></i></div>
                                            <div class="step-title" style="font-size: 11px;">Chờ Xử Lý</div>
                                        </div>
                                        <div class="timeline-step <?php echo ($ordStatus === 'Hoàn trả') ? 'active' : 'completed'; ?>">
                                            <div class="step-circle" style="width: 32px; height: 32px; font-size: 11px; <?php echo ($ordStatus === 'Hoàn trả') ? 'background: #f97316; border-color: #ea580c; color: white;' : 'background: #ea580c; border-color: #ea580c; color: white;'; ?>">
                                                <i class="fas <?php echo ($ordStatus === 'Hoàn trả') ? 'fa-sync-alt fa-spin' : 'fa-check'; ?>"></i>
                                            </div>
                                            <div class="step-title" style="font-size: 11px; color: #ea580c; font-weight: 700;">Yêu Cầu Hoàn Trả</div>
                                        </div>
                                        <div class="timeline-step <?php echo ($ordStatus === 'Đã hoàn trả') ? 'completed' : ''; ?>">
                                            <div class="step-circle" style="width: 32px; height: 32px; font-size: 11px; <?php echo ($ordStatus === 'Đã hoàn trả') ? 'background: #059669; border-color: #059669; color: white;' : ''; ?>">
                                                <i class="fas fa-hand-holding-usd"></i>
                                            </div>
                                            <div class="step-title" style="font-size: 11px; <?php echo ($ordStatus === 'Đã hoàn trả') ? 'color: #059669; font-weight: 700;' : ''; ?>">Đã Hoàn Trả</div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Luồng Giao Hàng Chuẩn Động -->
                                    <?php
                                    $oPendingDone = ($ordStatus === 'Đang giao' || $ordStatus === 'Đã giao');
                                    $oPendingActive = ($ordStatus === 'Chờ xử lý' || $ordStatus === 'Chờ thanh toán');
                                    $oShippingDone = ($ordStatus === 'Đã giao');
                                    $oShippingActive = ($ordStatus === 'Đang giao');
                                    $oDeliveredDone = ($ordStatus === 'Đã giao');
                                    ?>
                                    <div class="order-timeline" style="margin: 15px 0 10px 0;">
                                        <div class="timeline-step completed">
                                            <div class="step-circle" style="width: 32px; height: 32px; font-size: 11px;"><i class="fas fa-check"></i></div>
                                            <div class="step-title" style="font-size: 11px;">Đặt Hàng</div>
                                        </div>
                                        <div class="timeline-step <?php echo $oPendingDone ? 'completed' : ($oPendingActive ? 'active' : ''); ?>">
                                            <div class="step-circle" style="width: 32px; height: 32px; font-size: 11px;"><i class="fas <?php echo $oPendingDone ? 'fa-check' : 'fa-box'; ?>"></i></div>
                                            <div class="step-title" style="font-size: 11px;">Chờ Xử Lý</div>
                                        </div>
                                        <div class="timeline-step <?php echo $oShippingDone ? 'completed' : ($oShippingActive ? 'active' : ''); ?>">
                                            <div class="step-circle" style="width: 32px; height: 32px; font-size: 11px;"><i class="fas <?php echo $oShippingDone ? 'fa-check' : 'fa-shipping-fast'; ?>"></i></div>
                                            <div class="step-title" style="font-size: 11px;">Đang Giao</div>
                                        </div>
                                        <div class="timeline-step <?php echo $oDeliveredDone ? 'completed' : ''; ?>">
                                            <div class="step-circle" style="width: 32px; height: 32px; font-size: 11px;"><i class="fas fa-home"></i></div>
                                            <div class="step-title" style="font-size: 11px;">Đã Giao</div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div>
                                    <div class="text-muted small">Người nhận: <strong><?php echo htmlspecialchars($ord['receiver_name'], ENT_QUOTES, 'UTF-8'); ?></strong> (<?php echo htmlspecialchars($ord['receiver_phone'], ENT_QUOTES, 'UTF-8'); ?>)</div>
                                    <div class="text-muted small">Địa chỉ: <?php echo htmlspecialchars($ord['shipping_address'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-muted small">
                                        Phương thức: 
                                        <?php if ($ordIsBanking): ?>
                                            <span class="badge bg-primary px-2 py-1 rounded-pill">💳 Chuyển khoản VietQR (PayOS)</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary px-2 py-1 rounded-pill">💵 Tiền mặt (COS)</span>
                                            <?php if (!empty($ord['collected_amount']) && (float)$ord['collected_amount'] > 0): ?>
                                                <span class="text-success fw-bold ms-1">(Đã thu: <?php echo number_format($ord['collected_amount'], 0, ',', '.'); ?>₫)</span>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if (!empty($ord['refund_amount']) && (float)$ord['refund_amount'] > 0): ?>
                                            <span class="text-danger fw-bold ms-2"><i class="fas fa-undo-alt me-1"></i>Đã hoàn: <?php echo number_format($ord['refund_amount'], 0, ',', '.'); ?>₫</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="small text-muted">Tổng thanh toán:</div>
                                    <div class="fs-5 fw-bold text-danger"><?php echo number_format($ordTotal, 0, ',', '.'); ?>₫</div>
                                    <div class="mt-2 d-flex gap-2 justify-content-end">
                                        <a href="DatHangThanhCong.php?order_id=<?php echo $ordId; ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                            <i class="fas fa-eye me-1"></i>Xem Chi Tiết
                                        </a>
                                        <?php if ($canCancel): ?>
                                            <?php 
                                                $ordConfirmMsg = $ordIsBanking 
                                                    ? 'Đơn hàng này đã thanh toán qua PayOS. Khi bạn gửi yêu cầu hủy, đơn hàng sẽ chuyển sang trạng thái HOÀN TRẢ để Zyro đối soát và hoàn tiền vào tài khoản của bạn. Bạn có chắc chắn muốn hủy đơn không?' 
                                                    : 'Bạn có chắc chắn muốn hủy đơn hàng #' . $ordCode . '?';
                                            ?>
                                            <form action="LichSuDonHang.php" method="POST" onsubmit="return confirm('<?php echo addslashes($ordConfirmMsg); ?>');">
                                                <input type="hidden" name="action" value="cancel_order">
                                                <input type="hidden" name="order_id" value="<?php echo $ordId; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                    Hủy Đơn
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    </main>

    <!-- Footer dùng chung -->
    <?php include ('../includes/footer.php'); ?>
</body>
</html>
