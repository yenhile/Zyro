<?php
require_once('../includes/auth_check.php');
require_once('../../class/classProduct.php');

$productModel = new san_pham();

$successMsg = '';
$errorMsg = '';

// 1. Xử lý POST Cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order_status') {
    $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $newStatus = isset($_POST['new_status']) ? trim($_POST['new_status']) : '';

    if ($orderId > 0 && !empty($newStatus)) {
        $updateResult = $productModel->cap_nhat_trang_thai_don_hang($orderId, $newStatus);
        if ($updateResult['success']) {
            $successMsg = $updateResult['message'];
        } else {
            $errorMsg = $updateResult['message'];
        }
    } else {
        $errorMsg = 'Dữ liệu cập nhật đơn hàng không hợp lệ!';
    }
}

// 2. Lấy tham số lọc & tìm kiếm
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// 3. Thống kê số lượng đơn hàng theo trạng thái
$stats = $productModel->dem_don_hang_theo_trang_thai();

// 4. Lấy danh sách đơn hàng theo điều kiện lọc
$orders = $productModel->lay_danh_sach_tat_ca_don_hang($statusFilter, $searchKeyword, 100, 0);

// Nạp chi tiết cho từng đơn hàng để hiển thị nhanh
$orderDetailsMap = array();
if (!empty($orders)) {
    foreach ($orders as $ord) {
        $orderDetailsMap[$ord['orders_id']] = $productModel->lay_chi_tiet_don_hang($ord['orders_id']);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý & Xử Lý Đơn Hàng — ZYRO Admin</title>
    <link rel="stylesheet" href="/Zyro/admin/css/layout.css">
    <link rel="stylesheet" href="/Zyro/admin/css/index.css">
    <link rel="stylesheet" href="/Zyro/admin/css/admin-style.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            background: #ffffff;
            padding: 20px 24px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .page-title {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-subtitle {
            margin: 4px 0 0 0;
            color: #6b7280;
            font-size: 14px;
        }

        /* KPI Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .kpi-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border-left: 5px solid #cbd5e1;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }

        .kpi-card.all { border-left-color: #3b82f6; }
        .kpi-card.pending { border-left-color: #f59e0b; }
        .kpi-card.shipping { border-left-color: #06b6d4; }
        .kpi-card.delivered { border-left-color: #10b981; }
        .kpi-card.cancelled { border-left-color: #ef4444; }

        .kpi-title {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
        }

        .kpi-value {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
        }

        .kpi-desc {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
        }

        /* Alert notifications */
        .alert-custom {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Tabs & Search Filter */
        .filter-container {
            background: #ffffff;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .filter-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            color: #4b5563;
            background: #f3f4f6;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .tab-btn:hover {
            background: #e5e7eb;
            color: #111827;
        }

        .tab-btn.active {
            background: #111827;
            color: #ffffff;
        }

        .tab-badge {
            background: rgba(0, 0, 0, 0.1);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
        }

        .tab-btn.active .tab-badge {
            background: rgba(255, 255, 255, 0.25);
        }

        .search-box {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .search-input {
            padding: 8px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 13px;
            width: 260px;
            outline: none;
            transition: border-color 0.2s;
        }

        .search-input:focus {
            border-color: #111827;
        }

        .search-btn {
            background: #111827;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .search-btn:hover {
            background: #374151;
        }

        .reset-btn {
            background: #f3f4f6;
            color: #4b5563;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }

        /* Order Table */
        .orders-table-wrapper {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            text-align: left;
        }

        .orders-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .orders-table td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .orders-table tr:hover td {
            background-color: #fafbfc;
        }

        /* Status Badges */
        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-choxuly {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .badge-chothanhtoan {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fcd34d;
        }

        .badge-dathanhtoan {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #6ee7b7;
        }

        .badge-danggiao {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .badge-dagiao {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .badge-dahuy {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* Quick Status Update Form */
        .status-form {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-select {
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: #ffffff;
            outline: none;
            cursor: pointer;
        }

        .btn-update {
            background: #0284c7;
            color: #ffffff;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .btn-update:hover {
            background: #0369a1;
        }

        .action-group {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-action-view {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }

        .btn-action-view:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-action-print {
            background: #f3f4f6;
            color: #111827;
            border: 1px solid #d1d5db;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }

        .btn-action-print:hover {
            background: #111827;
            color: #ffffff;
        }

        /* Details Row Collapsible */
        .details-row {
            display: none;
            background-color: #f8fafc;
        }

        .details-content {
            padding: 16px 20px;
            border-top: 1px dashed #cbd5e1;
            border-bottom: 2px solid #e2e8f0;
        }

        .detail-item-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        .shoe-detail-card {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
        }

        .shoe-thumb {
            width: 55px;
            height: 55px;
            border-radius: 6px;
            object-fit: cover;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
        }

        /* Printable Invoice Styles */
        @media print {
            body * {
                visibility: hidden;
            }
            #printInvoiceModal, #printInvoiceModal * {
                visibility: visible;
            }
            #printInvoiceModal {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 20px;
                background: #ffffff !important;
                box-shadow: none !important;
            }
            .no-print {
                display: none !important;
            }
        }

        /* Modal Invoice */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-box {
            background: #ffffff;
            width: 100%;
            max-width: 680px;
            border-radius: 12px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 28px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-close-btn {
            position: absolute;
            top: 16px;
            right: 16px;
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>
    <div class="app-wrapper">
        <?php include('../includes/sidebar.php'); ?>
        <div id="container">
            <?php include('../includes/header.php'); ?>

            <div style="padding: 24px;">
                <!-- Header Tiêu Đề -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">📦 Quản Lý & Xử Lý Đơn Hàng</h1>
                        <p class="page-subtitle">Theo dõi, lọc và cập nhật tiến trình đơn hàng trực tuyến của hệ thống ZYRO Store</p>
                    </div>
                    <div>
                        <a href="/Zyro/admin/index.php" class="reset-btn">← Quay lại Dashboard</a>
                    </div>
                </div>

                <!-- Thông Báo Kết Quả -->
                <?php if (!empty($successMsg)): ?>
                    <div class="alert-custom alert-success">
                        <span>✅</span> <?php echo htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMsg)): ?>
                    <div class="alert-custom alert-danger">
                        <span>⚠️</span> <?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <!-- KPI Thống Kê Nhanh -->
                <div class="kpi-grid">
                    <a href="QuanLyDonHang.php?status=all" class="kpi-card all">
                        <div class="kpi-title">
                            <span>Tổng Đơn Hàng</span>
                            <span>📦</span>
                        </div>
                        <div class="kpi-value"><?php echo (int)$stats['all']; ?></div>
                        <div class="kpi-desc">Toàn bộ đơn hàng ghi nhận</div>
                    </a>

                    <a href="QuanLyDonHang.php?status=dathanhtoan" class="kpi-card" style="border-left-color: #10b981;">
                        <div class="kpi-title">
                            <span style="color: #059669;">Đã Thanh Toán PayOS</span>
                            <span>💳</span>
                        </div>
                        <div class="kpi-value" style="color: #059669;"><?php echo (int)$stats['dathanhtoan']; ?></div>
                        <div class="kpi-desc">Doanh thu PayOS: <?php echo number_format($stats['paid_revenue']); ?>đ</div>
                    </a>

                    <a href="QuanLyDonHang.php?status=choxuly" class="kpi-card pending">
                        <div class="kpi-title">
                            <span style="color: #b45309;">Chờ Xử Lý</span>
                            <span>⏳</span>
                        </div>
                        <div class="kpi-value" style="color: #b45309;"><?php echo (int)$stats['choxuly']; ?></div>
                        <div class="kpi-desc">Cần xác nhận đóng gói</div>
                    </a>

                    <a href="QuanLyDonHang.php?status=danggiao" class="kpi-card shipping">
                        <div class="kpi-title">
                            <span style="color: #0369a1;">Đang Giao Hàng</span>
                            <span>🚚</span>
                        </div>
                        <div class="kpi-value" style="color: #0369a1;"><?php echo (int)$stats['danggiao']; ?></div>
                        <div class="kpi-desc">Đơn đang trên đường vận chuyển</div>
                    </a>

                    <a href="QuanLyDonHang.php?status=dagiao" class="kpi-card delivered">
                        <div class="kpi-title">
                            <span style="color: #15803d;">Đã Giao Thành Công</span>
                            <span>✅</span>
                        </div>
                        <div class="kpi-value" style="color: #15803d;"><?php echo (int)$stats['dagiao']; ?></div>
                        <div class="kpi-desc">Doanh thu giao: <?php echo number_format($stats['delivered_revenue']); ?>đ</div>
                    </a>

                    <a href="QuanLyDonHang.php?status=dahuy" class="kpi-card cancelled">
                        <div class="kpi-title">
                            <span style="color: #b91c1c;">Đã Hủy</span>
                            <span>❌</span>
                        </div>
                        <div class="kpi-value" style="color: #b91c1c;"><?php echo (int)$stats['dahuy']; ?></div>
                        <div class="kpi-desc">Đơn khách hoặc shop hủy</div>
                    </a>
                </div>

                <!-- Bộ Lọc Trạng Thái & Tìm Kiếm -->
                <div class="filter-container">
                    <div class="filter-tabs">
                        <a href="QuanLyDonHang.php?status=all<?php echo !empty($searchKeyword) ? '&search=' . urlencode($searchKeyword) : ''; ?>" 
                           class="tab-btn <?php echo ($statusFilter === 'all' || empty($statusFilter)) ? 'active' : ''; ?>">
                            <span>Tất Cả</span>
                            <span class="tab-badge"><?php echo (int)$stats['all']; ?></span>
                        </a>
                        <a href="QuanLyDonHang.php?status=dathanhtoan<?php echo !empty($searchKeyword) ? '&search=' . urlencode($searchKeyword) : ''; ?>" 
                           class="tab-btn <?php echo ($statusFilter === 'dathanhtoan') ? 'active' : ''; ?>">
                            <span>💳 Đã Thanh Toán</span>
                            <span class="tab-badge"><?php echo (int)$stats['dathanhtoan']; ?></span>
                        </a>
                        <a href="QuanLyDonHang.php?status=chothanhtoan<?php echo !empty($searchKeyword) ? '&search=' . urlencode($searchKeyword) : ''; ?>" 
                           class="tab-btn <?php echo ($statusFilter === 'chothanhtoan') ? 'active' : ''; ?>">
                            <span>⏱️ Chờ Thanh Toán</span>
                            <span class="tab-badge"><?php echo (int)$stats['chothanhtoan']; ?></span>
                        </a>
                        <a href="QuanLyDonHang.php?status=choxuly<?php echo !empty($searchKeyword) ? '&search=' . urlencode($searchKeyword) : ''; ?>" 
                           class="tab-btn <?php echo ($statusFilter === 'choxuly') ? 'active' : ''; ?>">
                            <span>⏳ Chờ Xử Lý</span>
                            <span class="tab-badge"><?php echo (int)$stats['choxuly']; ?></span>
                        </a>
                        <a href="QuanLyDonHang.php?status=danggiao<?php echo !empty($searchKeyword) ? '&search=' . urlencode($searchKeyword) : ''; ?>" 
                           class="tab-btn <?php echo ($statusFilter === 'danggiao') ? 'active' : ''; ?>">
                            <span>🚚 Đang Giao</span>
                            <span class="tab-badge"><?php echo (int)$stats['danggiao']; ?></span>
                        </a>
                        <a href="QuanLyDonHang.php?status=dagiao<?php echo !empty($searchKeyword) ? '&search=' . urlencode($searchKeyword) : ''; ?>" 
                           class="tab-btn <?php echo ($statusFilter === 'dagiao') ? 'active' : ''; ?>">
                            <span>✅ Đã Giao</span>
                            <span class="tab-badge"><?php echo (int)$stats['dagiao']; ?></span>
                        </a>
                        <a href="QuanLyDonHang.php?status=dahuy<?php echo !empty($searchKeyword) ? '&search=' . urlencode($searchKeyword) : ''; ?>" 
                           class="tab-btn <?php echo ($statusFilter === 'dahuy') ? 'active' : ''; ?>">
                            <span>❌ Đã Hủy</span>
                            <span class="tab-badge"><?php echo (int)$stats['dahuy']; ?></span>
                        </a>
                    </div>

                    <form method="GET" action="QuanLyDonHang.php" class="search-box">
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="text" name="search" class="search-input" 
                               placeholder="Tìm mã đơn, tên khách, SĐT..." 
                               value="<?php echo htmlspecialchars($searchKeyword, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="search-btn">🔍 Tìm</button>
                        <?php if (!empty($searchKeyword) || $statusFilter !== 'all'): ?>
                            <a href="QuanLyDonHang.php" class="reset-btn" title="Xóa bộ lọc">✕</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Bảng Danh Sách Đơn Hàng -->
                <div class="orders-table-wrapper">
                    <?php if (empty($orders)): ?>
                        <div style="padding: 48px; text-align: center; color: #64748b;">
                            <div style="font-size: 40px; margin-bottom: 12px;">📭</div>
                            <div style="font-weight: 700; font-size: 16px; color: #1e293b;">Không tìm thấy đơn hàng nào!</div>
                            <p style="font-size: 14px; margin-top: 6px;">Hãy thử thay đổi trạng thái lọc hoặc từ khóa tìm kiếm.</p>
                            <a href="QuanLyDonHang.php" class="tab-btn active" style="margin-top: 12px; display: inline-block;">Xem Tất Cả Đơn Hàng</a>
                        </div>
                    <?php else: ?>
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>MÃ ĐƠN HÀNG</th>
                                    <th>KHÁCH HÀNG / NGƯỜI NHẬN</th>
                                    <th>SẢN PHẨM & SL</th>
                                    <th>TỔNG TIỀN & PTTT</th>
                                    <th>NGÀY ĐẶT</th>
                                    <th>TRẠNG THÁI</th>
                                    <th>XỬ LÝ TRẠNG THÁI</th>
                                    <th style="text-align: right;">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $ord): 
                                    $oId = (int)$ord['orders_id'];
                                    $orderCode = '#ZYRO-DH' . str_pad($oId, 2, '0', STR_PAD_LEFT);
                                    $statusRaw = trim($ord['status']);
                                    
                                    // Xác định class badge
                                    $badgeClass = 'badge-choxuly';
                                    if ($statusRaw === 'Đã thanh toán' || stripos($statusRaw, 'đã thanh toán') !== false || stripos($statusRaw, 'da thanh toan') !== false) {
                                        $badgeClass = 'badge-dathanhtoan';
                                    } elseif ($statusRaw === 'Chờ thanh toán' || stripos($statusRaw, 'chờ thanh toán') !== false || stripos($statusRaw, 'cho thanh toan') !== false) {
                                        $badgeClass = 'badge-chothanhtoan';
                                    } elseif ($statusRaw === 'Đang giao' || stripos($statusRaw, 'đang') !== false || stripos($statusRaw, 'dang') !== false) {
                                        $badgeClass = 'badge-danggiao';
                                    } elseif ($statusRaw === 'Đã giao' || stripos($statusRaw, 'đã giao') !== false || stripos($statusRaw, 'da giao') !== false) {
                                        $badgeClass = 'badge-dagiao';
                                    } elseif ($statusRaw === 'Đã hủy' || stripos($statusRaw, 'hủy') !== false || stripos($statusRaw, 'huy') !== false) {
                                        $badgeClass = 'badge-dahuy';
                                    }

                                    $items = isset($orderDetailsMap[$oId]) ? $orderDetailsMap[$oId] : array();
                                    $itemsJson = htmlspecialchars(json_encode($items), ENT_QUOTES, 'UTF-8');
                                    $ordJson = htmlspecialchars(json_encode($ord), ENT_QUOTES, 'UTF-8');
                                ?>
                                    <tr id="order-row-<?php echo $oId; ?>">
                                        <td>
                                            <strong style="color: #0284c7; font-size: 14px;"><?php echo $orderCode; ?></strong>
                                            <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">ID: #<?php echo $oId; ?></div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($ord['receiver_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div style="font-size: 13px; color: #475569; margin-top: 2px;">
                                                📞 <a href="tel:<?php echo htmlspecialchars($ord['receiver_phone'], ENT_QUOTES, 'UTF-8'); ?>" style="color: inherit; text-decoration: none;"><?php echo htmlspecialchars($ord['receiver_phone'], ENT_QUOTES, 'UTF-8'); ?></a>
                                            </div>
                                            <div style="font-size: 12px; color: #64748b; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;" title="<?php echo htmlspecialchars($ord['shipping_address'], ENT_QUOTES, 'UTF-8'); ?>">
                                                📍 <?php echo htmlspecialchars($ord['shipping_address'], ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <?php if (!empty($ord['user_fullname'])): ?>
                                                <div style="font-size: 11px; color: #8b5cf6; margin-top: 2px;">
                                                    👤 TK: <?php echo htmlspecialchars($ord['user_fullname'], ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="font-weight: 700; color: #0f172a;"><?php echo (int)$ord['total_quantity']; ?> đôi</span>
                                            <span style="color: #64748b; font-size: 12px;">(<?php echo (int)$ord['total_items']; ?> mẫu)</span>
                                            <div style="margin-top: 4px;">
                                                <button type="button" class="btn-action-view" onclick="toggleDetails(<?php echo $oId; ?>)">
                                                    <span>Xem mẫu</span> ▾
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 800; color: #dc2626; font-size: 15px;">
                                                <?php echo number_format($ord['total_price']); ?>đ
                                            </div>
                                            <div style="font-size: 12px; color: #475569; margin-top: 2px;">
                                                <?php if (!empty($ord['payment_code']) || stripos($ord['payment_method'], 'payos') !== false || stripos($ord['payment_method'], 'banking') !== false): ?>
                                                    <span style="color: #0284c7; font-weight: 700;">💳 PayOS (VietQR)</span>
                                                    <?php if (!empty($ord['payment_code'])): ?>
                                                        <div style="font-size: 11px; color: #64748b; font-family: monospace;">Mã: <?php echo htmlspecialchars($ord['payment_code'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($ord['payos_transaction_id'])): ?>
                                                        <div style="font-size: 10px; color: #16a34a; font-weight: 600;" title="Mã GD PayOS">GD: <?php echo htmlspecialchars($ord['payos_transaction_id'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span style="color: #15803d; font-weight: 600;">💵 Tiền mặt (COD)</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td style="color: #64748b; font-size: 13px;">
                                            <?php echo date('d/m/Y', strtotime($ord['order_date'])); ?><br>
                                            <span style="font-size: 11px; color: #94a3b8;"><?php echo date('H:i', strtotime($ord['order_date'])); ?></span>
                                            <?php if (!empty($ord['paid_at'])): ?>
                                                <div style="font-size: 10px; color: #16a34a; margin-top: 2px;">⚡ TT: <?php echo date('H:i d/m', strtotime($ord['paid_at'])); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge-status <?php echo $badgeClass; ?>">
                                                <?php echo htmlspecialchars($statusRaw, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- Form xử lý trạng thái nhanh -->
                                            <form method="POST" action="QuanLyDonHang.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8') : ''; ?>" class="status-form">
                                                <input type="hidden" name="action" value="update_order_status">
                                                <input type="hidden" name="order_id" value="<?php echo $oId; ?>">
                                                
                                                <select name="new_status" class="status-select">
                                                    <option value="Đã thanh toán" <?php echo ($statusRaw === 'Đã thanh toán') ? 'selected' : ''; ?>>💳 Đã thanh toán</option>
                                                    <option value="Chờ thanh toán" <?php echo ($statusRaw === 'Chờ thanh toán') ? 'selected' : ''; ?>>⏱️ Chờ thanh toán</option>
                                                    <option value="Chờ xử lý" <?php echo ($statusRaw === 'Chờ xử lý') ? 'selected' : ''; ?>>⏳ Chờ xử lý</option>
                                                    <option value="Đang giao" <?php echo ($statusRaw === 'Đang giao') ? 'selected' : ''; ?>>🚚 Đang giao</option>
                                                    <option value="Đã giao" <?php echo ($statusRaw === 'Đã giao') ? 'selected' : ''; ?>>✅ Đã giao</option>
                                                    <option value="Đã hủy" <?php echo ($statusRaw === 'Đã hủy') ? 'selected' : ''; ?>>❌ Đã hủy</option>
                                                </select>
                                                
                                                <button type="submit" class="btn-update" onclick="return confirm('Xác nhận cập nhật trạng thái đơn hàng <?php echo $orderCode; ?>?');">Lưu</button>
                                            </form>
                                        </td>
                                        <td style="text-align: right;">
                                            <div class="action-group" style="justify-content: flex-end;">
                                                <button type="button" class="btn-action-print" 
                                                        onclick="openPrintInvoice(<?php echo $ordJson; ?>, <?php echo $itemsJson; ?>)">
                                                    🖨️ In Phiếu
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Hàng Chi Tiết Mở Rộng -->
                                    <tr id="details-row-<?php echo $oId; ?>" class="details-row">
                                        <td colspan="8">
                                            <div class="details-content">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                                    <strong style="color: #1e293b; font-size: 13px;">DANH SÁCH SẢN PHẨM TRONG ĐƠN HÀNG (<?php echo count($items); ?> MÓN):</strong>
                                                    <?php if (!empty($ord['note'])): ?>
                                                        <div style="font-size: 12px; color: #b45309; background: #fef3c7; padding: 4px 10px; border-radius: 4px;">
                                                            📝 <strong>Ghi chú:</strong> <?php echo htmlspecialchars($ord['note'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="detail-item-grid">
                                                    <?php if (empty($items)): ?>
                                                        <div style="color: #94a3b8; font-size: 13px;">Không có dữ liệu chi tiết sản phẩm.</div>
                                                    <?php else: ?>
                                                        <?php foreach ($items as $it): 
                                                            $imgName = !empty($it['image_url']) ? $it['image_url'] : 'trending-NIKE+WAFFLE+NAV.png';
                                                            $imgSrc = (file_exists(dirname(__FILE__) . '/../../uploads/' . $imgName)) 
                                                                    ? '/Zyro/uploads/' . $imgName 
                                                                    : '/Zyro/assets/img/' . $imgName;
                                                            $prodTitle = !empty($it['product_name']) ? $it['product_name'] : (!empty($it['name']) ? $it['name'] : 'Giày Thể Thao ZYRO');
                                                        ?>
                                                            <div class="shoe-detail-card">
                                                                <img src="<?php echo htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8'); ?>" 
                                                                     alt="<?php echo htmlspecialchars($prodTitle, ENT_QUOTES, 'UTF-8'); ?>" 
                                                                     class="shoe-thumb"
                                                                     onerror="this.src='/Zyro/assets/img/trending-NIKE+WAFFLE+NAV.png';">
                                                                <div style="flex: 1; min-width: 0;">
                                                                    <div style="font-weight: 700; font-size: 13px; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                                        <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo (int)$it['product_id']; ?>" target="_blank" style="color: inherit; text-decoration: none;">
                                                                            <?php echo htmlspecialchars($prodTitle, ENT_QUOTES, 'UTF-8'); ?>
                                                                        </a>
                                                                    </div>
                                                                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                                                        <span>Hãng: <strong><?php echo htmlspecialchars(!empty($it['brand_name']) ? $it['brand_name'] : 'ZYRO', ENT_QUOTES, 'UTF-8'); ?></strong></span> | 
                                                                        <span>Size: <strong style="color: #0284c7;"><?php echo htmlspecialchars($it['size'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                                                                    </div>
                                                                    <div style="font-size: 12px; font-weight: 700; color: #dc2626; margin-top: 2px;">
                                                                        <?php echo number_format($it['price']); ?>đ × <?php echo (int)$it['quantity']; ?> = <?php echo number_format($it['price'] * $it['quantity']); ?>đ
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <?php include('../includes/footer.php'); ?>
        </div>
    </div>

    <!-- Modal In Hóa Đơn / Phiếu Xuất Kho -->
    <div id="printInvoiceModal" class="modal-overlay">
        <div class="modal-box">
            <button type="button" class="modal-close-btn no-print" onclick="closePrintInvoice()">✕</button>
            
            <div id="invoicePrintArea">
                <!-- Tiêu đề Cửa hàng -->
                <div style="text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 16px;">
                    <h2 style="margin: 0; font-size: 22px; font-weight: 900; letter-spacing: 1px;">ZYRO FOOTWEAR STORE</h2>
                    <p style="margin: 4px 0 0 0; font-size: 12px; color: #475569;">Hệ Thống Giày Thể Thao Chính Hãng — Đại Học Công Nghiệp TP.HCM (IUH)</p>
                    <p style="margin: 2px 0 0 0; font-size: 12px; color: #475569;">Hotline: 1900 6868 | Website: zyro.local | Email: support@zyro.vn</p>
                </div>

                <div style="text-align: center; margin-bottom: 20px;">
                    <h3 style="margin: 0; font-size: 18px; font-weight: 800; text-transform: uppercase;">PHIẾU XUẤT KHO KIÊM HÓA ĐƠN BÁN HÀNG</h3>
                    <div id="invOrderCode" style="font-size: 14px; font-weight: bold; color: #0284c7; margin-top: 4px;">#ZYRO-DH01</div>
                    <div id="invOrderDate" style="font-size: 12px; color: #64748b; margin-top: 2px;">Ngày đặt: 15/10/2026</div>
                </div>

                <!-- Thông tin khách hàng & Giao nhận -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 13px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <div><strong>Khách nhận:</strong> <span id="invReceiverName">--</span></div>
                        <div><strong>Điện thoại:</strong> <span id="invReceiverPhone">--</span></div>
                        <div style="grid-column: span 2;"><strong>Địa chỉ nhận:</strong> <span id="invAddress">--</span></div>
                        <div><strong>Phương thức:</strong> <span id="invPayment">--</span></div>
                        <div><strong>Trạng thái:</strong> <span id="invStatus">--</span></div>
                        <div style="grid-column: span 2;" id="invNoteBox"><strong>Ghi chú:</strong> <span id="invNote">Không có</span></div>
                    </div>
                </div>

                <!-- Bảng sản phẩm -->
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 16px;" border="1" bordercolor="#cbd5e1">
                    <thead>
                        <tr style="background: #f1f5f9;">
                            <th style="padding: 8px;">STT</th>
                            <th style="padding: 8px; text-align: left;">Sản Phẩm</th>
                            <th style="padding: 8px;">Size</th>
                            <th style="padding: 8px;">SL</th>
                            <th style="padding: 8px; text-align: right;">Đơn Giá</th>
                            <th style="padding: 8px; text-align: right;">Thành Tiền</th>
                        </tr>
                    </thead>
                    <tbody id="invItemsBody">
                        <!-- Dữ liệu JS điền vào -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" style="padding: 8px; text-align: right; font-weight: bold;">TỔNG CỘNG THANH TOÁN:</td>
                            <td id="invTotalPrice" style="padding: 8px; text-align: right; font-weight: 900; color: #dc2626; font-size: 15px;">0đ</td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Chữ ký xác nhận -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; text-align: center; margin-top: 30px; font-size: 13px;">
                    <div>
                        <strong>Người Lập Phiếu</strong><br>
                        <span style="font-size: 11px; color: #64748b;">(Ký & ghi rõ họ tên)</span>
                        <div style="height: 50px;"></div>
                        <strong><?php echo htmlspecialchars($adminUser['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>
                    <div>
                        <strong>Nhân Viên Giao Hàng</strong><br>
                        <span style="font-size: 11px; color: #64748b;">(Ký & ghi rõ họ tên)</span>
                        <div style="height: 50px;"></div>
                    </div>
                    <div>
                        <strong>Khách Hàng Nhận</strong><br>
                        <span style="font-size: 11px; color: #64748b;">(Ký & ghi rõ họ tên)</span>
                        <div style="height: 50px;"></div>
                    </div>
                </div>
            </div>

            <!-- Nút Thao Tác Modal -->
            <div class="no-print" style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                <button type="button" class="reset-btn" onclick="closePrintInvoice()">Đóng</button>
                <button type="button" class="search-btn" onclick="window.print()">🖨️ In Phiếu Ngay</button>
            </div>
        </div>
    </div>

    <script src="/Zyro/admin/js/sidebar.js"></script>
    <script>
        // Mở/đóng dòng xem chi tiết sản phẩm trong đơn hàng
        function toggleDetails(orderId) {
            var row = document.getElementById('details-row-' + orderId);
            if (!row) return;
            if (row.style.display === 'table-row') {
                row.style.display = 'none';
            } else {
                row.style.display = 'table-row';
            }
        }

        // Mở modal in hóa đơn xuất kho
        function openPrintInvoice(order, items) {
            var modal = document.getElementById('printInvoiceModal');
            if (!modal) return;

            var oId = parseInt(order.orders_id);
            var padId = oId < 10 ? '0' + oId : oId;
            document.getElementById('invOrderCode').innerText = '#ZYRO-DH' + padId;
            document.getElementById('invOrderDate').innerText = 'Ngày đặt: ' + order.order_date;
            document.getElementById('invReceiverName').innerText = order.receiver_name;
            document.getElementById('invReceiverPhone').innerText = order.receiver_phone;
            document.getElementById('invAddress').innerText = order.shipping_address;
            document.getElementById('invPayment').innerText = order.payment_method;
            document.getElementById('invStatus').innerText = order.status;

            if (order.note && order.note.trim() !== '') {
                document.getElementById('invNote').innerText = order.note;
                document.getElementById('invNoteBox').style.display = 'block';
            } else {
                document.getElementById('invNoteBox').style.display = 'none';
            }

            var tbody = document.getElementById('invItemsBody');
            tbody.innerHTML = '';

            var total = 0;
            if (items && items.length > 0) {
                for (var i = 0; i < items.length; i++) {
                    var it = items[i];
                    var qty = parseInt(it.quantity);
                    var price = parseFloat(it.price);
                    var subtotal = qty * price;
                    total += subtotal;

                    var tr = document.createElement('tr');
                    tr.innerHTML = '<td style="padding: 6px; text-align: center;">' + (i + 1) + '</td>' +
                                   '<td style="padding: 6px;"><strong>' + it.name + '</strong> (' + (it.brand_name || 'ZYRO') + ')</td>' +
                                   '<td style="padding: 6px; text-align: center;">' + it.size + '</td>' +
                                   '<td style="padding: 6px; text-align: center;">' + qty + '</td>' +
                                   '<td style="padding: 6px; text-align: right;">' + price.toLocaleString('vi-VN') + 'đ</td>' +
                                   '<td style="padding: 6px; text-align: right; font-weight: bold;">' + subtotal.toLocaleString('vi-VN') + 'đ</td>';
                    tbody.appendChild(tr);
                }
            } else {
                tbody.innerHTML = '<tr><td colspan="6" style="padding: 10px; text-align: center; color: #64748b;">Không có chi tiết sản phẩm</td></tr>';
            }

            var finalTotal = parseFloat(order.total_price) || total;
            document.getElementById('invTotalPrice').innerText = finalTotal.toLocaleString('vi-VN') + 'đ';

            modal.style.display = 'flex';
        }

        // Đóng modal in hóa đơn
        function closePrintInvoice() {
            var modal = document.getElementById('printInvoiceModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Đóng modal khi bấm ra ngoài box
        window.onclick = function(event) {
            var modal = document.getElementById('printInvoiceModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };
    </script>
</body>

</html>
