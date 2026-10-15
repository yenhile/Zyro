<?php
require_once('../includes/auth_check.php');
require_once('../../class/classProduct.php');

$productModel = new san_pham();

// 1. Tiếp nhận tham số bộ lọc thời gian
$period = isset($_GET['period']) ? trim($_GET['period']) : 'all';
$fromDate = isset($_GET['from_date']) ? trim($_GET['from_date']) : '';
$toDate = isset($_GET['to_date']) ? trim($_GET['to_date']) : '';

// Xử lý logic gán ngày mặc định theo period nếu người dùng chưa chọn ngày tùy chỉnh
if (empty($fromDate) && empty($toDate)) {
    if ($period === 'today') {
        $fromDate = date('Y-m-d');
        $toDate = date('Y-m-d');
    } elseif ($period === '7days') {
        $fromDate = date('Y-m-d', strtotime('-7 days'));
        $toDate = date('Y-m-d');
    } elseif ($period === '30days') {
        $fromDate = date('Y-m-d', strtotime('-30 days'));
        $toDate = date('Y-m-d');
    } elseif ($period === 'this_month') {
        $fromDate = date('Y-m-01');
        $toDate = date('Y-m-t');
    }
}

// 2. Nạp dữ liệu thống kê từ Backend
$overview = $productModel->lay_thong_ke_tong_quan_tai_chinh($fromDate, $toDate);
$trendData = $productModel->lay_doanh_thu_theo_thoi_gian($period, $fromDate, $toDate);
$topShoes = $productModel->lay_top_san_pham_ban_chay(5, $fromDate, $toDate);
$paymentBreakdown = $productModel->lay_co_cau_phuong_thuc_thanh_toan($fromDate, $toDate);
$statusStats = $productModel->dem_don_hang_theo_trang_thai();

// Chuẩn bị dữ liệu JSON cho biểu đồ Canvas/Chart
$chartLabels = array();
$chartRevenues = array();
$chartOrders = array();

if (!empty($trendData)) {
    foreach ($trendData as $td) {
        $chartLabels[] = date('d/m', strtotime($td['stat_date']));
        $chartRevenues[] = (float)$td['total_revenue'];
        $chartOrders[] = (int)$td['order_count'];
    }
} else {
    $chartLabels[] = date('d/m');
    $chartRevenues[] = 0;
    $chartOrders[] = 0;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo & Thống Kê Đơn Hàng — ZYRO Admin</title>
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
            flex-wrap: wrap;
            gap: 16px;
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

        /* Filter Toolbar */
        .filter-toolbar {
            background: #ffffff;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .filter-preset-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-filter-preset {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            color: #4b5563;
            background: #f3f4f6;
            transition: all 0.2s;
        }

        .btn-filter-preset:hover {
            background: #e5e7eb;
            color: #111827;
        }

        .btn-filter-preset.active {
            background: #0f172a;
            color: #ffffff;
        }

        .custom-date-form {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .date-input {
            padding: 7px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
        }

        .btn-apply-filter {
            background: #0284c7;
            color: #ffffff;
            border: none;
            padding: 7px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-apply-filter:hover {
            background: #0369a1;
        }

        /* 5 Financial KPI Cards */
        .kpi-grid-5 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .kpi-box {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            border-top: 4px solid #cbd5e1;
            position: relative;
            overflow: hidden;
        }

        .kpi-box.green { border-top-color: #10b981; }
        .kpi-box.blue { border-top-color: #3b82f6; }
        .kpi-box.purple { border-top-color: #8b5cf6; }
        .kpi-box.amber { border-top-color: #f59e0b; }
        .kpi-box.teal { border-top-color: #14b8a6; }

        .kpi-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
            display: flex;
            justify-content: space-between;
        }

        .kpi-number {
            font-size: 26px;
            font-weight: 900;
            color: #0f172a;
            margin: 8px 0 4px 0;
        }

        .kpi-subtext {
            font-size: 12px;
            color: #94a3b8;
        }

        /* Analytics Section Layout */
        .analytics-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        @media (max-width: 1024px) {
            .analytics-row {
                grid-template-columns: 1fr;
            }
        }

        .panel-box {
            background: #ffffff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 12px;
        }

        .panel-title {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Chart Canvas Styling */
        .chart-container {
            position: relative;
            width: 100%;
            height: 280px;
        }

        /* Top Shoes Table */
        .top-shoes-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .top-shoes-table th {
            text-align: left;
            padding: 10px 12px;
            background: #f8fafc;
            color: #64748b;
            font-weight: 700;
            border-bottom: 1px solid #e2e8f0;
        }

        .top-shoes-table td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .shoe-item-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .shoe-thumb-sm {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .rank-badge {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 12px;
        }

        .rank-1 { background: #fef08a; color: #854d0e; }
        .rank-2 { background: #e2e8f0; color: #334155; }
        .rank-3 { background: #fed7aa; color: #9a3412; }

        /* Breakdown Bars */
        .breakdown-item {
            margin-bottom: 16px;
        }

        .breakdown-label {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .progress-bar-bg {
            height: 10px;
            background: #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 6px;
            transition: width 0.5s ease-in-out;
        }

        /* Printable Styles */
        @media print {
            body * {
                visibility: hidden;
            }
            #reportPrintArea, #reportPrintArea * {
                visibility: visible;
            }
            #reportPrintArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background: #ffffff !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="app-wrapper">
        <?php include('../includes/sidebar.php'); ?>
        <div id="container">
            <?php include('../includes/header.php'); ?>

            <div style="padding: 24px;" id="reportPrintArea">
                <!-- Tiêu đề trang & Nút In Báo Cáo -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">📈 Báo Cáo & Thống Kê Doanh Thu</h1>
                        <p class="page-subtitle">Phân tích hiệu quả kinh doanh, dòng tiền và sản phẩm bán chạy của hệ thống ZYRO Store</p>
                    </div>
                    <div class="no-print" style="display: flex; gap: 10px;">
                        <button type="button" class="btn-apply-filter" onclick="window.print()" style="background: #0f172a; display: inline-flex; align-items: center; gap: 6px;">
                            🖨️ In Báo Cáo
                        </button>
                        <a href="/Zyro/admin/pages/QuanLyDonHang.php" class="btn-filter-preset" style="background: #f1f5f9;">
                            📦 Xử Lý Đơn Hàng →
                        </a>
                    </div>
                </div>

                <!-- Thanh Công Cụ Bộ Lọc Thời Gian -->
                <div class="filter-toolbar no-print">
                    <div class="filter-preset-group">
                        <a href="ThongKeDonHang.php?period=all" class="btn-filter-preset <?php echo ($period === 'all' && empty($fromDate)) ? 'active' : ''; ?>">Toàn Bộ</a>
                        <a href="ThongKeDonHang.php?period=today" class="btn-filter-preset <?php echo ($period === 'today') ? 'active' : ''; ?>">Hôm Nay</a>
                        <a href="ThongKeDonHang.php?period=7days" class="btn-filter-preset <?php echo ($period === '7days') ? 'active' : ''; ?>">7 Ngày Qua</a>
                        <a href="ThongKeDonHang.php?period=30days" class="btn-filter-preset <?php echo ($period === '30days') ? 'active' : ''; ?>">30 Ngày Qua</a>
                        <a href="ThongKeDonHang.php?period=this_month" class="btn-filter-preset <?php echo ($period === 'this_month') ? 'active' : ''; ?>">Tháng Này</a>
                    </div>

                    <form method="GET" action="ThongKeDonHang.php" class="custom-date-form">
                        <span style="font-size: 13px; font-weight: 600; color: #64748b;">Khoảng ngày:</span>
                        <input type="date" name="from_date" class="date-input" value="<?php echo htmlspecialchars($fromDate, ENT_QUOTES, 'UTF-8'); ?>" required>
                        <span style="color: #94a3b8;">→</span>
                        <input type="date" name="to_date" class="date-input" value="<?php echo htmlspecialchars($toDate, ENT_QUOTES, 'UTF-8'); ?>" required>
                        <button type="submit" class="btn-apply-filter">Lọc</button>
                        <?php if (!empty($fromDate) || $period !== 'all'): ?>
                            <a href="ThongKeDonHang.php?period=all" class="btn-filter-preset" title="Đặt lại bộ lọc">✕</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- 5 Thẻ Chỉ Số Tài Chính Cốt Lõi (Financial KPI Cards) -->
                <div class="kpi-grid-5">
                    <div class="kpi-box green">
                        <div class="kpi-label">
                            <span>Doanh Thu Thực Thu</span>
                            <span>💰</span>
                        </div>
                        <div class="kpi-number" style="color: #10b981;">
                            <?php echo number_format($overview['delivered_revenue']); ?>đ
                        </div>
                        <div class="kpi-subtext">
                            Tiềm năng: <strong><?php echo number_format($overview['potential_revenue']); ?>đ</strong>
                        </div>
                    </div>

                    <div class="kpi-box blue">
                        <div class="kpi-label">
                            <span>Tổng Số Đơn Hàng</span>
                            <span>📦</span>
                        </div>
                        <div class="kpi-number" style="color: #0284c7;">
                            <?php echo (int)$overview['total_orders']; ?>
                        </div>
                        <div class="kpi-subtext">
                            Đã giao: <strong><?php echo (int)$overview['delivered_orders']; ?></strong> | Đang giao: <strong><?php echo (int)$overview['shipping_orders']; ?></strong>
                        </div>
                    </div>

                    <div class="kpi-box purple">
                        <div class="kpi-label">
                            <span>Giày Xuất Bán</span>
                            <span>👟</span>
                        </div>
                        <div class="kpi-number" style="color: #7c3aed;">
                            <?php echo (int)$overview['total_shoes_sold']; ?> <span style="font-size: 15px; font-weight: 600;">đôi</span>
                        </div>
                        <div class="kpi-subtext">
                            Tổng sản phẩm trong các đơn hợp lệ
                        </div>
                    </div>

                    <div class="kpi-box teal">
                        <div class="kpi-label">
                            <span>Tỷ Lệ Giao Thành Công</span>
                            <span>🎯</span>
                        </div>
                        <div class="kpi-number" style="color: #0d9488;">
                            <?php echo $overview['success_rate']; ?>%
                        </div>
                        <div class="kpi-subtext">
                            Đơn hủy: <strong><?php echo (int)$overview['cancelled_orders']; ?></strong> đơn
                        </div>
                    </div>

                    <div class="kpi-box amber">
                        <div class="kpi-label">
                            <span>Giá Trị Đơn Trung Bình</span>
                            <span>💵</span>
                        </div>
                        <div class="kpi-number" style="color: #d97706; font-size: 22px;">
                            <?php echo number_format($overview['avg_order_value']); ?>đ
                        </div>
                        <div class="kpi-subtext">
                            AOV (Average Order Value)
                        </div>
                    </div>
                </div>

                <!-- Hàng Phân Tích: Biểu Đồ Xu Hướng + Top Sản Phẩm Bán Chạy -->
                <div class="analytics-row">
                    <!-- Biểu đồ xu hướng doanh thu -->
                    <div class="panel-box">
                        <div class="panel-header">
                            <h3 class="panel-title">
                                <span>📊 Biểu Đồ Biến Động Doanh Thu & Đơn Hàng</span>
                            </h3>
                            <span style="font-size: 12px; color: #64748b; font-weight: 600;">
                                <?php echo !empty($fromDate) ? htmlspecialchars($fromDate . ' → ' . $toDate, ENT_QUOTES, 'UTF-8') : 'Toàn bộ thời gian'; ?>
                            </span>
                        </div>

                        <div class="chart-container">
                            <canvas id="revenueTrendCanvas"></canvas>
                        </div>

                        <div style="display: flex; justify-content: center; gap: 24px; margin-top: 16px; font-size: 13px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="display: inline-block; width: 14px; height: 14px; background: #0284c7; border-radius: 4px;"></span>
                                <span>Doanh Thu (VNĐ)</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="display: inline-block; width: 14px; height: 14px; background: #f59e0b; border-radius: 50%;"></span>
                                <span>Số Lượng Đơn Hàng</span>
                            </div>
                        </div>
                    </div>

                    <!-- Phân bổ phương thức & trạng thái -->
                    <div class="panel-box">
                        <div class="panel-header">
                            <h3 class="panel-title">
                                <span>💳 Cơ Cấu Thanh Toán</span>
                            </h3>
                        </div>

                        <?php 
                            $totalPmCount = max(1, $paymentBreakdown['total_count']);
                            $codPct = round(($paymentBreakdown['cod_count'] / $totalPmCount) * 100);
                            $bankPct = round(($paymentBreakdown['banking_count'] / $totalPmCount) * 100);
                            $otherPct = 100 - $codPct - $bankPct;
                        ?>

                        <div class="breakdown-item">
                            <div class="breakdown-label">
                                <span>💵 Tiền mặt khi nhận (COD)</span>
                                <strong><?php echo (int)$paymentBreakdown['cod_count']; ?> đơn (<?php echo $codPct; ?>%)</strong>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: <?php echo $codPct; ?>%; background: #10b981;"></div>
                            </div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                Doanh thu: <?php echo number_format($paymentBreakdown['cod_amount']); ?>đ
                            </div>
                        </div>

                        <div class="breakdown-item">
                            <div class="breakdown-label">
                                <span>💳 VietQR MB Bank (Chuyển khoản)</span>
                                <strong><?php echo (int)$paymentBreakdown['banking_count']; ?> đơn (<?php echo $bankPct; ?>%)</strong>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: <?php echo $bankPct; ?>%; background: #0284c7;"></div>
                            </div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                Doanh thu: <?php echo number_format($paymentBreakdown['banking_amount']); ?>đ
                            </div>
                        </div>

                        <div style="border-top: 1px solid #f1f5f9; padding-top: 16px; margin-top: 20px;">
                            <div class="panel-title" style="font-size: 15px; margin-bottom: 12px;">
                                <span>📦 Trạng Thái Đơn Hàng</span>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12px;">
                                <div style="background: #fef3c7; color: #92400e; padding: 8px 12px; border-radius: 8px; font-weight: 700;">
                                    ⏳ Chờ xử lý: <?php echo (int)$statusStats['choxuly']; ?>
                                </div>
                                <div style="background: #e0f2fe; color: #0369a1; padding: 8px 12px; border-radius: 8px; font-weight: 700;">
                                    🚚 Đang giao: <?php echo (int)$statusStats['danggiao']; ?>
                                </div>
                                <div style="background: #dcfce7; color: #15803d; padding: 8px 12px; border-radius: 8px; font-weight: 700;">
                                    ✅ Đã giao: <?php echo (int)$statusStats['dagiao']; ?>
                                </div>
                                <div style="background: #fee2e2; color: #b91c1c; padding: 8px 12px; border-radius: 8px; font-weight: 700;">
                                    ❌ Đã hủy: <?php echo (int)$statusStats['dahuy']; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bảng Xếp Hạng Top Giày Thể Thao Bán Chạy -->
                <div class="panel-box" style="margin-bottom: 24px;">
                    <div class="panel-header">
                        <h3 class="panel-title">
                            <span>🏆 Top Mẫu Giày Thể Thao Bán Chạy Nhất (Best Sellers)</span>
                        </h3>
                        <a href="/Zyro/admin/pages/ThemSanPham.php?tab=product" class="no-print" style="color: #0284c7; font-size: 13px; font-weight: 700; text-decoration: none;">
                            + Thêm mẫu mới
                        </a>
                    </div>

                    <?php if (empty($topShoes)): ?>
                        <div style="text-align: center; padding: 32px; color: #94a3b8;">
                            Chưa có dữ liệu bán hàng trong khoảng thời gian này.
                        </div>
                    <?php else: ?>
                        <table class="top-shoes-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px; text-align: center;">HẠNG</th>
                                    <th>MẪU GIÀY THỂ THAO</th>
                                    <th>THƯƠNG HIỆU</th>
                                    <th>ĐƠN GIÁ</th>
                                    <th style="text-align: center;">ĐÃ BÁN</th>
                                    <th style="text-align: right;">DOANH THU ĐEM LẠI</th>
                                    <th style="width: 140px;" class="no-print">TỶ TRỌNG</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $rank = 1;
                                    $maxSold = !empty($topShoes[0]['total_sold']) ? (int)$topShoes[0]['total_sold'] : 1;
                                    foreach ($topShoes as $ts): 
                                        $imgName = !empty($ts['image_url']) ? $ts['image_url'] : 'trending-NIKE+WAFFLE+NAV.png';
                                        $imgSrc = (file_exists(dirname(__FILE__) . '/../../uploads/' . $imgName)) 
                                                ? '/Zyro/uploads/' . $imgName 
                                                : '/Zyro/assets/img/' . $imgName;
                                        $soldCount = (int)$ts['total_sold'];
                                        $pctSold = round(($soldCount / $maxSold) * 100);
                                ?>
                                    <tr>
                                        <td style="text-align: center;">
                                            <span class="rank-badge rank-<?php echo $rank; ?>">
                                                <?php echo $rank; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="shoe-item-cell">
                                                <img src="<?php echo htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8'); ?>" 
                                                     alt="<?php echo htmlspecialchars($ts['product_name'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                     class="shoe-thumb-sm"
                                                     onerror="this.src='/Zyro/assets/img/trending-NIKE+WAFFLE+NAV.png';">
                                                <div>
                                                    <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo (int)$ts['product_id']; ?>" target="_blank" style="font-weight: 700; color: #0f172a; text-decoration: none;">
                                                        <?php echo htmlspecialchars($ts['product_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                    <div style="font-size: 11px; color: #94a3b8;">Mã SP: #<?php echo (int)$ts['product_id']; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-weight: 600; color: #475569;"><?php echo htmlspecialchars($ts['brand_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </td>
                                        <td style="font-weight: 600; color: #1e293b;">
                                            <?php echo number_format($ts['unit_price']); ?>đ
                                        </td>
                                        <td style="text-align: center;">
                                            <strong style="color: #0284c7; font-size: 14px;"><?php echo $soldCount; ?></strong> <span style="font-size: 12px; color: #64748b;">đôi</span>
                                        </td>
                                        <td style="text-align: right; font-weight: 800; color: #dc2626; font-size: 14px;">
                                            <?php echo number_format($ts['total_revenue']); ?>đ
                                        </td>
                                        <td class="no-print">
                                            <div class="progress-bar-bg" title="<?php echo $pctSold; ?>%">
                                                <div class="progress-bar-fill" style="width: <?php echo $pctSold; ?>%; background: #0284c7;"></div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                    $rank++;
                                    endforeach; 
                                ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Footer Ký Tên Dành Cho Bản In -->
                <div style="display: none; margin-top: 40px; text-align: center;" id="printSignatures">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                        <div>
                            <strong>Người Lập Báo Cáo</strong><br>
                            <span style="font-size: 12px; color: #64748b;">(Ký & ghi rõ họ tên)</span>
                            <div style="height: 60px;"></div>
                            <strong><?php echo htmlspecialchars($adminUser['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                        <div>
                            <strong>Giám Đốc Cửa Hàng ZYRO</strong><br>
                            <span style="font-size: 12px; color: #64748b;">(Ký, đóng dấu & ghi rõ họ tên)</span>
                            <div style="height: 60px;"></div>
                            <strong>Ban Quản Trị ZYRO Store</strong>
                        </div>
                    </div>
                </div>
            </div>

            <?php include('../includes/footer.php'); ?>
        </div>
    </div>

    <!-- Chart.js Thư viện vẽ đồ thị tương tác -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/Zyro/admin/js/sidebar.js"></script>
    <script>
        // Khởi tạo Biểu đồ Xu hướng Doanh Thu & Đơn Hàng
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('revenueTrendCanvas');
            if (!ctx) return;

            var labels = <?php echo json_encode($chartLabels); ?>;
            var revenues = <?php echo json_encode($chartRevenues); ?>;
            var orders = <?php echo json_encode($chartOrders); ?>;

            if (typeof Chart !== 'undefined') {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Doanh Thu (VNĐ)',
                                data: revenues,
                                backgroundColor: 'rgba(2, 132, 199, 0.85)',
                                borderColor: '#0284c7',
                                borderWidth: 1,
                                borderRadius: 6,
                                yAxisID: 'y'
                            },
                            {
                                label: 'Số Đơn Hàng',
                                data: orders,
                                type: 'line',
                                borderColor: '#f59e0b',
                                backgroundColor: '#f59e0b',
                                borderWidth: 3,
                                pointRadius: 5,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#f59e0b',
                                pointBorderWidth: 2,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Doanh thu (VNĐ)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString('vi-VN') + 'đ';
                                    }
                                },
                                grid: {
                                    color: '#f1f5f9'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Số lượng đơn'
                                },
                                grid: {
                                    drawOnChartArea: false
                                },
                                ticks: {
                                    stepSize: 1
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        if (context.dataset.yAxisID === 'y') {
                                            return 'Doanh thu: ' + context.parsed.y.toLocaleString('vi-VN') + 'đ';
                                        } else {
                                            return 'Số đơn: ' + context.parsed.y + ' đơn';
                                        }
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                // Fallback nếu không có internet hoặc CDN bị chặn
                var parent = ctx.parentElement;
                parent.innerHTML = '<div style="padding: 20px; text-align: center; color: #64748b;">(Biểu đồ Canvas tự động hiển thị khi có kết nối Chart.js)</div>';
            }
        });
    </script>
</body>

</html>
