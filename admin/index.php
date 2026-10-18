<?php
require_once('includes/auth_check.php');
require_once('../class/classProduct.php');

$productModel = new san_pham();
$orderStats = $productModel->dem_don_hang_theo_trang_thai();
$recentOrders = $productModel->lay_danh_sach_tat_ca_don_hang('all', '', 5, 0);
$allProducts = $productModel->lay_san_pham_trending(null, 1000);
$totalProductCount = count($allProducts);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Quản Trị — ZYRO Admin</title>
    <link rel="stylesheet" href="/Zyro/admin/css/layout.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/Zyro/admin/css/index.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/Zyro/admin/css/admin-style.css?v=<?php echo time(); ?>">
    <style>
        html, body {
            overflow-x: hidden !important;
            max-width: 100vw !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .app-wrapper {
            display: flex !important;
            width: 100% !important;
            max-width: 100vw !important;
            overflow-x: hidden !important;
        }
        #sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            bottom: 0 !important;
            width: 250px !important;
            min-width: 250px !important;
            max-width: 250px !important;
            height: 100vh !important;
            z-index: 1050 !important;
        }
        #container {
            margin-left: 250px !important;
            width: calc(100% - 250px) !important;
            max-width: calc(100% - 250px) !important;
            flex: 1 1 0% !important;
            min-width: 0 !important;
            overflow-x: hidden !important;
        }
        .dashboard-welcome {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .welcome-title {
            margin: 0 0 6px 0;
            font-size: 24px;
            font-weight: 800;
        }

        .welcome-subtitle {
            margin: 0;
            color: #94a3b8;
            font-size: 14px;
        }

        .quick-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-action-hero {
            background: #ffffff;
            color: #0f172a;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-action-hero:hover {
            background: #f1f5f9;
            transform: translateY(-1px);
        }

        .btn-action-hero-outline {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-action-hero-outline:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* KPI Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border-top: 4px solid #3b82f6;
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }

        .stat-card.warning { border-top-color: #f59e0b; }
        .stat-card.info { border-top-color: #06b6d4; }
        .stat-card.success { border-top-color: #10b981; }
        .stat-card.purple { border-top-color: #8b5cf6; }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            margin: 8px 0 4px 0;
        }

        .stat-subtext {
            font-size: 12px;
            color: #94a3b8;
        }

        /* Recent Orders Section */
        .section-box {
            background: #ffffff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 12px;
        }

        .section-title {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-mini {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .table-mini th {
            text-align: left;
            padding: 10px 12px;
            background: #f8fafc;
            color: #64748b;
            font-weight: 700;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-mini td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .badge-status-sm {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-shipping { background: #e0f2fe; color: #0369a1; }
        .badge-delivered { background: #dcfce7; color: #15803d; }
        .badge-cancelled { background: #fee2e2; color: #b91c1c; }
    </style>
</head>

<body>
    <div class="app-wrapper">
        <?php include('includes/sidebar.php'); ?>
        <div id="container">
            <?php include('includes/header.php'); ?>

            <div class="admin-content-box">
                <!-- Banner chào mừng -->
                <div class="dashboard-welcome">
                    <div>
                        <h2 class="welcome-title">Chào mừng trở lại, <?php echo htmlspecialchars($adminUser['name'], ENT_QUOTES, 'UTF-8'); ?>! 👑</h2>
                        <p class="welcome-subtitle">Bảng điều khiển quản trị hệ thống ZYRO Store — Quyền Hạn: <strong>Quản Trị Viên (Admin)</strong></p>
                    </div>
                    <div class="quick-actions">
                        <a href="/Zyro/admin/pages/ThongKeDonHang.php" class="btn-action-hero" style="background: #0284c7; color: #ffffff;">
                            📈 Báo Cáo Doanh Thu
                        </a>
                        <a href="/Zyro/admin/pages/QuanLyDonHang.php?status=choxuly" class="btn-action-hero">
                            📦 Xử Lý Đơn Chờ (<?php echo (int)$orderStats['choxuly']; ?>)
                        </a>
                        <a href="/Zyro/admin/pages/ThemSanPham.php?tab=product" class="btn-action-hero-outline">
                            ➕ Thêm Giày Mới
                        </a>
                    </div>
                </div>

                <!-- Thống kê KPI Nhanh -->
                <div class="stats-grid">
                    <a href="/Zyro/admin/pages/QuanLyDonHang.php?status=all" class="stat-card">
                        <div class="stat-header">
                            <span>Tổng Đơn Hàng</span>
                            <span>📦</span>
                        </div>
                        <div class="stat-value"><?php echo (int)$orderStats['all']; ?></div>
                        <div class="stat-subtext">Tất cả đơn đặt từ khách</div>
                    </a>

                    <a href="/Zyro/admin/pages/QuanLyDonHang.php?status=choxuly" class="stat-card warning">
                        <div class="stat-header">
                            <span style="color: #b45309;">Chờ Xử Lý</span>
                            <span>⏳</span>
                        </div>
                        <div class="stat-value" style="color: #b45309;"><?php echo (int)$orderStats['choxuly']; ?></div>
                        <div class="stat-subtext">Cần đóng gói & xác nhận</div>
                    </a>

                    <a href="/Zyro/admin/pages/QuanLyDonHang.php?status=danggiao" class="stat-card info">
                        <div class="stat-header">
                            <span style="color: #0369a1;">Đang Vận Chuyển</span>
                            <span>🚚</span>
                        </div>
                        <div class="stat-value" style="color: #0369a1;"><?php echo (int)$orderStats['danggiao']; ?></div>
                        <div class="stat-subtext">Đơn đang trên đường giao</div>
                    </a>

                    <a href="/Zyro/admin/pages/ThongKeDonHang.php" class="stat-card success">
                        <div class="stat-header">
                            <span style="color: #15803d;">Doanh Thu Thực Thu</span>
                            <span>💰</span>
                        </div>
                        <div class="stat-value" style="color: #15803d; font-size: 24px;">
                            <?php echo number_format($orderStats['delivered_revenue']); ?>đ
                        </div>
                        <div class="stat-subtext">Tiền mặt đã thu & Banking đã TT (Xem chi tiết →)</div>
                    </a>

                    <a href="/Zyro/admin/pages/QuanLySanPham.php" class="stat-card purple">
                        <div class="stat-header">
                            <span style="color: #7c3aed;">Giày Trên Kệ</span>
                            <span>👟</span>
                        </div>
                        <div class="stat-value" style="color: #7c3aed;"><?php echo (int)$totalProductCount; ?></div>
                        <div class="stat-subtext">Mẫu sản phẩm đang bán</div>
                    </a>
                </div>

                <!-- Đơn Hàng Gần Đây Cần Xử Lý -->
                <div class="section-box">
                    <div class="section-header">
                        <h3 class="section-title">
                            <span>📦 Đơn Hàng Mới Gần Đây</span>
                        </h3>
                        <a href="/Zyro/admin/pages/QuanLyDonHang.php" style="color: #0284c7; text-decoration: none; font-size: 13px; font-weight: 700;">
                            Xem tất cả đơn hàng →
                        </a>
                    </div>

                    <?php if (empty($recentOrders)): ?>
                        <div style="text-align: center; padding: 24px; color: #94a3b8;">
                            Chưa có đơn hàng nào trong hệ thống.
                        </div>
                    <?php else: ?>
                        <div class="admin-table-responsive">
                            <table class="table-mini">
                                <thead>
                                    <tr>
                                        <th>MÃ ĐƠN</th>
                                        <th>NGƯỜI NHẬN</th>
                                        <th>SỐ ĐIỆN THOẠI</th>
                                        <th>TỔNG TIỀN</th>
                                        <th>NGÀY ĐẶT</th>
                                        <th>TRẠNG THÁI</th>
                                        <th style="text-align: right;">THAO TÁC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $ro): 
                                        $roId = (int)$ro['orders_id'];
                                        $st = trim($ro['status']);
                                        $badge = 'badge-pending';
                                        if ($st === 'Đang giao' || stripos($st, 'đang') !== false) {
                                            $badge = 'badge-shipping';
                                        } elseif ($st === 'Đã giao' || stripos($st, 'đã') !== false) {
                                            $badge = 'badge-delivered';
                                        } elseif ($st === 'Đã hủy' || stripos($st, 'hủy') !== false) {
                                            $badge = 'badge-cancelled';
                                        }
                                    ?>
                                        <tr>
                                            <td><strong>#ZYRO-DH<?php echo str_pad($roId, 2, '0', STR_PAD_LEFT); ?></strong></td>
                                            <td><strong><?php echo htmlspecialchars($ro['receiver_name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                            <td><?php echo htmlspecialchars($ro['receiver_phone'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td style="color: #dc2626; font-weight: 700;"><?php echo number_format($ro['total_price']); ?>đ</td>
                                            <td style="color: #64748b;"><?php echo date('d/m/Y H:i', strtotime($ro['order_date'])); ?></td>
                                            <td><span class="badge-status-sm <?php echo $badge; ?>"><?php echo htmlspecialchars($st, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td style="text-align: right;">
                                                <a href="/Zyro/admin/pages/QuanLyDonHang.php?search=<?php echo $roId; ?>" 
                                                   style="background: #0f172a; color: #ffffff; padding: 5px 10px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;">
                                                    Xử lý →
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>

    </div>
    <script src="/Zyro/admin/js/sidebar.js"></script>
</body>

</html>