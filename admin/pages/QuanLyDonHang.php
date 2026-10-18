<?php
require_once('../includes/auth_check.php');
require_once('../../config/payos.php');
require_once('../../class/classProduct.php');

$productModel = new san_pham();

$successMsg = '';
$errorMsg = '';

// 1. Xử lý POST các hành động trong Quy trình Xử lý Đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = trim($_POST['action']);
    $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;

    if ($orderId <= 0) {
        $errorMsg = 'Mã đơn hàng không hợp lệ!';
    } elseif ($action === 'admin_ship_order') {
        // Quy tắc 2: Admin xác nhận giao đơn (Chờ xử lý -> Đang giao)
        $res = $productModel->xac_nhan_giao_don($orderId);
        if ($res['success']) {
            $successMsg = $res['message'];
        } else {
            $errorMsg = $res['message'];
        }
    } elseif ($action === 'admin_confirm_delivered') {
        // Quy tắc 3: Admin xác nhận đã giao (Đang giao -> Đã giao)
        $collectedAmount = isset($_POST['collected_amount']) && $_POST['collected_amount'] !== '' ? (float)$_POST['collected_amount'] : null;
        $res = $productModel->xac_nhan_da_giao($orderId, $collectedAmount);
        if ($res['success']) {
            $successMsg = $res['message'];
        } else {
            $errorMsg = $res['message'];
        }
    } elseif ($action === 'admin_request_refund') {
        // Quy tắc 3: Admin yêu cầu hoàn (Đang giao -> Hoàn trả)
        $reason = isset($_POST['refund_reason']) ? trim($_POST['refund_reason']) : '';
        $res = $productModel->yeu_cau_hoan_tra($orderId, $reason);
        if ($res['success']) {
            $successMsg = $res['message'];
        } else {
            $errorMsg = $res['message'];
        }
    } elseif ($action === 'admin_process_refund') {
        // Quy tắc 4: Admin xác nhận đã hoàn trả (Hoàn trả -> Đã hoàn trả)
        $refundAmount = isset($_POST['refund_amount']) && $_POST['refund_amount'] !== '' ? (float)$_POST['refund_amount'] : null;
        $refundNote = isset($_POST['refund_note']) ? trim($_POST['refund_note']) : '';
        $proofImageName = null;

        // Xử lý upload ảnh minh chứng đối với Banking
        if (isset($_FILES['refund_proof_image']) && $_FILES['refund_proof_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['refund_proof_image']['tmp_name'];
            $fileName = $_FILES['refund_proof_image']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = array('jpg', 'jpeg', 'png', 'webp', 'gif');

            if (in_array($fileExt, $allowedExts)) {
                $proofImageName = 'refund_' . $orderId . '_' . time() . '.' . $fileExt;
                $targetDir = dirname(__FILE__) . '/../../uploads/refunds/';
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0777, true);
                }
                $targetPath = $targetDir . $proofImageName;
                if (!move_uploaded_file($fileTmp, $targetPath)) {
                    $proofImageName = null;
                }
            }
        }

        $res = $productModel->xac_nhan_da_hoan_tra($orderId, $refundAmount, $proofImageName, $refundNote);
        if ($res['success']) {
            $successMsg = $res['message'];
        } else {
            $errorMsg = $res['message'];
        }
    } elseif ($action === 'admin_cancel_order') {
        // Quy tắc 5: Admin hủy đơn ở Chờ xử lý (COS -> Đã hủy, Banking -> Hoàn trả)
        $reason = isset($_POST['cancel_reason']) ? trim($_POST['cancel_reason']) : '';
        $res = $productModel->huy_don_hang_admin($orderId, $reason);
        if ($res['success']) {
            $successMsg = $res['message'];
        } else {
            $errorMsg = $res['message'];
        }
    }
}

// 2. Lấy tham số lọc & tìm kiếm
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// 3. Thống kê số lượng đơn hàng theo trạng thái
$stats = $productModel->dem_don_hang_theo_trang_thai();

// 4. Phân trang đơn hàng động
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['limit']) && in_array((int)$_GET['limit'], array(5, 10, 20, 50, 100)) ? (int)$_GET['limit'] : 10;

$totalItems = $productModel->dem_tong_so_don_hang($statusFilter, $searchKeyword);
$totalPages = max(1, (int)ceil($totalItems / $perPage));
if ($currentPage > $totalPages) $currentPage = $totalPages;
$offset = ($currentPage - 1) * $perPage;

$orders = $productModel->lay_danh_sach_tat_ca_don_hang($statusFilter, $searchKeyword, $perPage, $offset);

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
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
            background: #ffffff;
            padding: 20px 24px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
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
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
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
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            box-sizing: border-box !important;
        }

        .orders-table {
            width: 100%;
            min-width: 850px;
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

        .badge-hoantra {
            background: #ffedd5;
            color: #c2410c;
            border: 1px solid #fdba74;
        }

        .badge-dahoantra {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        /* Workflow Action Buttons */
        .btn-flow-ship {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            border: none;
            padding: 7px 13px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .btn-flow-ship:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(2, 132, 199, 0.35);
        }

        .btn-flow-delivery {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            border: none;
            padding: 7px 13px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .btn-flow-delivery:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.35);
        }

        .btn-flow-refund {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            border: none;
            padding: 7px 13px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.25);
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .btn-flow-refund:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.35);
        }

        .btn-flow-cancel {
            background: #ffffff;
            color: #dc2626;
            border: 1px solid #fca5a5;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .btn-flow-cancel:hover {
            background: #fee2e2;
            border-color: #ef4444;
        }

        .btn-flow-proof {
            background: #ede9fe;
            color: #6d28d9;
            border: 1px solid #ddd6fe;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-flow-proof:hover {
            background: #ddd6fe;
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

            <div class="admin-content-box">
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

                <!-- Banner Hướng Dẫn Cấu Hình Webhook PayOS Khi Triển Khai Hosting -->
                <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; box-shadow: 0 4px 14px rgba(0,0,0,0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 44px; height: 44px; background: rgba(2, 132, 199, 0.2); border: 1px solid #0284c7; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                            ⚡
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                <span>WEBHOOK PAYOS (DÀNH CHO HOSTING):</span>
                                <span style="background: #10b981; color: #ffffff; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 20px;">SẴN SÀNG</span>
                            </div>
                            <div style="font-size: 12px; color: #94a3b8; margin-top: 3px;">
                                Khi đưa lên Host, copy URL này dán vào <strong>Kênh thanh toán → Thiết lập Webhook</strong> tại <a href="https://my.payos.vn" target="_blank" rel="noopener noreferrer" style="color: #38bdf8; text-decoration: underline;">my.payos.vn</a>:
                            </div>
                            <div style="margin-top: 6px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <code style="background: #020617; color: #38bdf8; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-family: monospace; border: 1px solid #334155;" id="adminWebhookUrl"><?php echo htmlspecialchars(PayOS_Config::getWebhookUrl(), ENT_QUOTES, 'UTF-8'); ?></code>
                                <button type="button" onclick="navigator.clipboard.writeText('<?php echo addslashes(PayOS_Config::getWebhookUrl()); ?>'); alert('Đã sao chép Webhook URL thành công: ' + '<?php echo addslashes(PayOS_Config::getWebhookUrl()); ?>');" style="background: #0284c7; color: #ffffff; border: none; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; transition: background 0.2s;">
                                    📋 Sao chép URL
                                </button>
                            </div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <a href="/Zyro/logs/payos_webhook.log" target="_blank" style="background: rgba(255,255,255,0.1); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.2); padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                            📄 Xem Log Webhook
                        </a>
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

                    <a href="QuanLyDonHang.php?status=hoantra" class="kpi-card" style="border-left-color: #f97316;">
                        <div class="kpi-title">
                            <span style="color: #ea580c;">Chờ Hoàn Trả</span>
                            <span>🔄</span>
                        </div>
                        <div class="kpi-value" style="color: #ea580c;"><?php echo (int)$stats['hoantra']; ?></div>
                        <div class="kpi-desc">Cần xử lý hoàn tiền / hàng</div>
                    </a>

                    <a href="QuanLyDonHang.php?status=dahoantra" class="kpi-card" style="border-left-color: #64748b;">
                        <div class="kpi-title">
                            <span style="color: #475569;">Đã Hoàn Trả</span>
                            <span>💸</span>
                        </div>
                        <div class="kpi-value" style="color: #475569;"><?php echo (int)$stats['dahoantra']; ?></div>
                        <div class="kpi-desc">Đã tất toán hoàn tiền xong</div>
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
                        <a href="QuanLyDonHang.php?status=hoantra<?php echo !empty($searchKeyword) ? '&search=' . urlencode($searchKeyword) : ''; ?>" 
                           class="tab-btn <?php echo ($statusFilter === 'hoantra') ? 'active' : ''; ?>">
                            <span>🔄 Hoàn Trả</span>
                            <span class="tab-badge"><?php echo (int)$stats['hoantra']; ?></span>
                        </a>
                        <a href="QuanLyDonHang.php?status=dahoantra<?php echo !empty($searchKeyword) ? '&search=' . urlencode($searchKeyword) : ''; ?>" 
                           class="tab-btn <?php echo ($statusFilter === 'dahoantra') ? 'active' : ''; ?>">
                            <span>💸 Đã Hoàn Trả</span>
                            <span class="tab-badge"><?php echo (int)$stats['dahoantra']; ?></span>
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
                                    
                                    $isBanking = san_pham::la_thanh_toan_banking($ord['payment_method']) || !empty($ord['payos_transaction_id']);
                                    $isCash = !$isBanking;

                                    // Xác định class badge
                                    $badgeClass = 'badge-choxuly';
                                    if ($statusRaw === 'Đã hoàn trả' || stripos($statusRaw, 'đã hoàn trả') !== false) {
                                        $badgeClass = 'badge-dahoantra';
                                    } elseif ($statusRaw === 'Hoàn trả' || stripos($statusRaw, 'hoàn') !== false) {
                                        $badgeClass = 'badge-hoantra';
                                    } elseif ($statusRaw === 'Đã thanh toán' || stripos($statusRaw, 'đã thanh toán') !== false || stripos($statusRaw, 'da thanh toan') !== false) {
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
                                                <?php if ($isBanking): ?>
                                                    <span style="color: #0284c7; font-weight: 700;">💳 PayOS (VietQR)</span>
                                                    <?php if (!empty($ord['ident_code'])): ?>
                                                        <div style="font-size: 11px; color: #0284c7; font-family: monospace; font-weight: 700;" title="Mã nhận diện 6 chữ số">Mã ĐD: <?php echo htmlspecialchars($ord['ident_code'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <?php elseif (!empty($ord['payment_code'])): ?>
                                                        <div style="font-size: 11px; color: #64748b; font-family: monospace;">Mã: <?php echo htmlspecialchars($ord['payment_code'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($ord['payos_transaction_id'])): ?>
                                                        <div style="font-size: 10px; color: #16a34a; font-weight: 600;" title="Mã GD PayOS">GD: <?php echo htmlspecialchars($ord['payos_transaction_id'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span style="color: #15803d; font-weight: 700;">💵 Tiền mặt (COS)</span>
                                                    <?php if (!empty($ord['collected_amount'])): ?>
                                                        <div style="font-size: 11px; color: #16a34a; font-weight: 600;">Đã thu: <?php echo number_format($ord['collected_amount']); ?>đ</div>
                                                    <?php endif; ?>
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
                                            <!-- Khung xử lý trạng thái theo quy trình 5 bước -->
                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <?php if ($statusRaw === 'Chờ xử lý' || (stripos($statusRaw, 'chờ') !== false && stripos($statusRaw, 'thanh toán') === false)): ?>
                                                    <!-- Bước 2 & 5: Đơn ở Chờ xử lý -->
                                                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                                        <form method="POST" action="QuanLyDonHang.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8') : ''; ?>" style="margin: 0;">
                                                            <input type="hidden" name="action" value="admin_ship_order">
                                                            <input type="hidden" name="order_id" value="<?php echo $oId; ?>">
                                                            <button type="submit" class="btn-flow-ship" onclick="return confirm('Xác nhận giao đơn hàng <?php echo $orderCode; ?>? Trạng thái sẽ chuyển sang Đang giao.');">
                                                                🚚 Xác nhận giao đơn
                                                            </button>
                                                        </form>

                                                        <button type="button" class="btn-flow-cancel" onclick="confirmCancelPending(<?php echo $oId; ?>, '<?php echo $orderCode; ?>', <?php echo $isBanking ? 'true' : 'false'; ?>)">
                                                            ✕ Hủy đơn
                                                        </button>
                                                    </div>

                                                <?php elseif ($statusRaw === 'Đang giao' || stripos($statusRaw, 'đang') !== false): ?>
                                                    <!-- Bước 3: Đơn ở Đang giao -> Popup xác nhận giao hàng (Đã giao + Yêu cầu hoàn) -->
                                                    <div>
                                                        <button type="button" class="btn-flow-delivery" onclick="openDeliveryModal(<?php echo $oId; ?>, '<?php echo $orderCode; ?>', <?php echo (float)$ord['total_price']; ?>, <?php echo $isCash ? 'true' : 'false'; ?>)">
                                                            📦 Xác nhận giao hàng
                                                        </button>
                                                    </div>

                                                <?php elseif ($statusRaw === 'Hoàn trả' || (stripos($statusRaw, 'hoàn') !== false && stripos($statusRaw, 'đã') === false)): ?>
                                                    <!-- Bước 4: Đơn ở Hoàn trả -> Nút Xác nhận đã hoàn trả -->
                                                    <div>
                                                        <button type="button" class="btn-flow-refund" onclick="openRefundModal(<?php echo $oId; ?>, '<?php echo $orderCode; ?>', <?php echo (float)$ord['total_price']; ?>, <?php echo $isBanking ? 'true' : 'false'; ?>)">
                                                            💸 Xác nhận đã hoàn trả
                                                        </button>
                                                    </div>

                                                <?php elseif ($statusRaw === 'Chờ thanh toán'): ?>
                                                    <div style="font-size: 11px; color: #b45309; background: #fffbeb; padding: 4px 8px; border-radius: 6px; border: 1px solid #fef3c7;">
                                                        ⏱️ Chờ khách quét QR (10p)
                                                    </div>

                                                <?php elseif ($statusRaw === 'Đã giao'): ?>
                                                    <div style="font-size: 12px; color: #15803d; font-weight: 700;">
                                                        ✓ Đã giao thành công
                                                        <?php if ($isCash && !empty($ord['collected_amount'])): ?>
                                                            <div style="font-size: 11px; color: #166534; font-weight: normal;">Đã thu: <?php echo number_format($ord['collected_amount']); ?>đ</div>
                                                        <?php endif; ?>
                                                    </div>

                                                <?php elseif ($statusRaw === 'Đã hoàn trả'): ?>
                                                    <div style="font-size: 12px; color: #475569; font-weight: 700;">
                                                        ✓ Đã hoàn trả xong
                                                        <?php if ($isBanking && !empty($ord['refund_amount'])): ?>
                                                            <div style="font-size: 11px; color: #0284c7; font-weight: normal;">Đã hoàn: <?php echo number_format($ord['refund_amount']); ?>đ</div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($ord['refund_proof_image'])): ?>
                                                            <div style="margin-top: 3px;">
                                                                <button type="button" class="btn-flow-proof" onclick="viewProofModal('/Zyro/uploads/refunds/<?php echo htmlspecialchars($ord['refund_proof_image'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo $orderCode; ?>')">
                                                                    📷 Minh chứng hoàn tiền
                                                                </button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                <?php elseif ($statusRaw === 'Đã hủy'): ?>
                                                    <div style="font-size: 12px; color: #dc2626; font-weight: 600;">
                                                        ✕ Đã hủy đơn
                                                        <?php if (!empty($ord['refund_note'])): ?>
                                                            <div style="font-size: 10px; color: #991b1b; max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($ord['refund_note'], ENT_QUOTES, 'UTF-8'); ?>">
                                                                (<?php echo htmlspecialchars($ord['refund_note'], ENT_QUOTES, 'UTF-8'); ?>)
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
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
                    <?php include('../includes/pagination.php'); ?>
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

    <!-- Modal Quy tắc 3: Xác nhận giao hàng (Đang giao -> Đã giao hoặc Hoàn trả) -->
    <div id="deliveryModal" class="modal-overlay">
        <div class="modal-box" style="max-width: 500px;">
            <button type="button" class="modal-close-btn" onclick="closeDeliveryModal()">✕</button>
            <h3 style="margin-top: 0; font-size: 18px; font-weight: 800; color: #0f172a;">📦 XÁC NHẬN GIAO HÀNG THÀNH CÔNG</h3>
            <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">
                Đơn hàng: <strong id="deliveryOrderCode" style="color: #0284c7;">--</strong>
            </p>

            <form id="deliveryForm" method="POST" action="QuanLyDonHang.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                <input type="hidden" name="action" id="deliveryAction" value="admin_confirm_delivered">
                <input type="hidden" name="order_id" id="deliveryOrderId" value="">
                <input type="hidden" name="refund_reason" id="deliveryRefundReason" value="">

                <!-- Ô nhập tiền thu đối với đơn tiền mặt (COS) -->
                <div id="cosCashBox" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 20px;">
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                        💵 Số tiền đã thu từ khách (VNĐ): <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="number" name="collected_amount" id="deliveryCollectedAmount" class="search-input" style="width: 100%; box-sizing: border-box; font-size: 15px; font-weight: 700;" min="0" step="1000">
                    <small style="color: #64748b; font-size: 11px; display: block; margin-top: 4px;">Mặc định bằng tổng tiền đơn hàng. Có thể điều chỉnh nếu có giảm trừ/phụ thu thực tế.</small>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn-flow-cancel" style="padding: 9px 16px; font-size: 13px;" onclick="handleDeliveryRefund()">
                        🔄 Yêu Cầu Hoàn
                    </button>
                    <button type="button" class="btn-flow-delivery" style="padding: 9px 18px; font-size: 13px;" onclick="submitDeliveryDelivered()">
                        ✅ Đã Giao
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Quy tắc 4: Xác nhận hoàn trả Banking (Bắt buộc số tiền và ảnh minh chứng) -->
    <div id="refundModal" class="modal-overlay">
        <div class="modal-box" style="max-width: 520px;">
            <button type="button" class="modal-close-btn" onclick="closeRefundModal()">✕</button>
            <h3 style="margin-top: 0; font-size: 18px; font-weight: 800; color: #0f172a;">💸 XÁC NHẬN ĐÃ HOÀN TRẢ ĐƠN HÀNG</h3>
            <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
                Đơn hàng: <strong id="refundOrderCode" style="color: #0284c7;">--</strong>
            </p>

            <form id="refundForm" method="POST" action="QuanLyDonHang.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8') : ''; ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="admin_process_refund">
                <input type="hidden" name="order_id" id="refundOrderId" value="">

                <div id="bankingRefundFields">
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                            Số tiền đã chuyển khoản hoàn trả (VNĐ): <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="number" name="refund_amount" id="refundAmountInput" class="search-input" style="width: 100%; box-sizing: border-box; font-size: 15px; font-weight: 700;" min="1000" step="1000">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                            Minh chứng hình ảnh chuyển khoản hoàn tiền: <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="file" name="refund_proof_image" id="refundProofInput" accept="image/*" class="search-input" style="width: 100%; box-sizing: border-box;" onchange="previewRefundImg(this)">
                        <div id="refundImgPreviewBox" style="display: none; margin-top: 8px; text-align: center;">
                            <img id="refundImgPreview" src="" alt="Preview" style="max-height: 180px; max-width: 100%; border-radius: 8px; border: 1px solid #cbd5e1;">
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                        Ghi chú hoàn tiền (Tùy chọn):
                    </label>
                    <textarea name="refund_note" class="search-input" style="width: 100%; box-sizing: border-box; height: 60px; font-size: 13px;" placeholder="Nhập lý do hoặc mã giao dịch ngân hàng..."></textarea>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="reset-btn" onclick="closeRefundModal()">Hủy Bỏ</button>
                    <button type="submit" class="btn-flow-refund" style="padding: 9px 18px; font-size: 13px;">
                        💸 Xác Nhận Đã Hoàn Trả Xong
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Xem Ảnh Minh Chứng Hoàn Tiền -->
    <div id="proofPreviewModal" class="modal-overlay">
        <div class="modal-box" style="max-width: 600px; text-align: center;">
            <button type="button" class="modal-close-btn" onclick="closeProofModal()">✕</button>
            <h3 style="margin-top: 0; font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">📷 MINH CHỨNG CHUYỂN KHOẢN HOÀN TIỀN</h3>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 16px;" id="proofOrderCodeText">--</p>
            <img id="proofPreviewImgTag" src="" alt="Minh chứng" style="max-width: 100%; max-height: 70vh; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        </div>
    </div>

    <!-- Hidden form xử lý hủy đơn Chờ xử lý -->
    <form id="adminCancelForm" method="POST" action="QuanLyDonHang.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8') : ''; ?>" style="display: none;">
        <input type="hidden" name="action" value="admin_cancel_order">
        <input type="hidden" name="order_id" id="adminCancelOrderId" value="">
        <input type="hidden" name="cancel_reason" id="adminCancelReason" value="">
    </form>

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

        // Quy tắc 5: Hủy đơn ở trạng thái Chờ xử lý
        function confirmCancelPending(orderId, orderCode, isBanking) {
            var msg = isBanking 
                ? 'Đơn hàng ' + orderCode + ' đã thanh toán trực tuyến (Banking). Khi hủy, đơn sẽ chuyển sang trạng thái "Hoàn trả" để tiến hành hoàn tiền cho khách. Xác nhận hủy?'
                : 'Xác nhận hủy đơn hàng tiền mặt ' + orderCode + '? Trạng thái sẽ chuyển sang "Đã hủy".';
            
            if (confirm(msg)) {
                var reason = prompt('Nhập lý do hủy đơn (Tùy chọn):', '');
                document.getElementById('adminCancelOrderId').value = orderId;
                document.getElementById('adminCancelReason').value = reason ? reason : '';
                document.getElementById('adminCancelForm').submit();
            }
        }

        // Quy tắc 3: Mở modal xác nhận giao hàng
        function openDeliveryModal(orderId, orderCode, totalPrice, isCash) {
            document.getElementById('deliveryOrderId').value = orderId;
            document.getElementById('deliveryOrderCode').innerText = orderCode;
            document.getElementById('deliveryAction').value = 'admin_confirm_delivered';
            document.getElementById('deliveryRefundReason').value = '';

            var cosBox = document.getElementById('cosCashBox');
            var collectedInput = document.getElementById('deliveryCollectedAmount');
            if (isCash) {
                cosBox.style.display = 'block';
                collectedInput.value = totalPrice;
                collectedInput.required = true;
            } else {
                cosBox.style.display = 'none';
                collectedInput.value = '';
                collectedInput.required = false;
            }

            document.getElementById('deliveryModal').style.display = 'flex';
        }

        function closeDeliveryModal() {
            document.getElementById('deliveryModal').style.display = 'none';
        }

        function submitDeliveryDelivered() {
            var form = document.getElementById('deliveryForm');
            var collectedInput = document.getElementById('deliveryCollectedAmount');
            if (document.getElementById('cosCashBox').style.display !== 'none') {
                if (!collectedInput.value || parseFloat(collectedInput.value) < 0) {
                    alert('Vui lòng nhập số tiền đã thu từ khách!');
                    collectedInput.focus();
                    return;
                }
            }
            document.getElementById('deliveryAction').value = 'admin_confirm_delivered';
            if (confirm('Xác nhận giao thành công cho đơn hàng này?')) {
                form.submit();
            }
        }

        function handleDeliveryRefund() {
            var reason = prompt('Nhập lý do yêu cầu hoàn trả đơn hàng:', 'Giao không thành công / Khách từ chối nhận');
            if (reason === null) return;
            document.getElementById('deliveryAction').value = 'admin_request_refund';
            document.getElementById('deliveryRefundReason').value = reason;
            document.getElementById('deliveryForm').submit();
        }

        // Quy tắc 4: Mở modal xác nhận đã hoàn trả
        function openRefundModal(orderId, orderCode, totalPrice, isBanking) {
            if (!isBanking) {
                // Đơn tiền mặt: Admin xác nhận chuyển luôn sang Đã hoàn trả
                if (confirm('Xác nhận hoàn tất hoàn trả cho đơn hàng tiền mặt ' + orderCode + '?')) {
                    var f = document.createElement('form');
                    f.method = 'POST';
                    f.action = 'QuanLyDonHang.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8') : ''; ?>';
                    f.innerHTML = '<input type="hidden" name="action" value="admin_process_refund">' +
                                  '<input type="hidden" name="order_id" value="' + orderId + '">';
                    document.body.appendChild(f);
                    f.submit();
                }
                return;
            }

            // Đơn Banking: Mở modal bắt buộc nhập số tiền và upload ảnh
            document.getElementById('refundOrderId').value = orderId;
            document.getElementById('refundOrderCode').innerText = orderCode;
            document.getElementById('refundAmountInput').value = totalPrice;
            document.getElementById('refundProofInput').value = '';
            document.getElementById('refundImgPreviewBox').style.display = 'none';
            document.getElementById('refundAmountInput').required = true;
            document.getElementById('refundProofInput').required = true;

            document.getElementById('refundModal').style.display = 'flex';
        }

        function closeRefundModal() {
            document.getElementById('refundModal').style.display = 'none';
        }

        function previewRefundImg(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('refundImgPreview').src = e.target.result;
                    document.getElementById('refundImgPreviewBox').style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Xem ảnh minh chứng hoàn trả
        function viewProofModal(imgUrl, orderCode) {
            document.getElementById('proofOrderCodeText').innerText = 'Đơn hàng ' + orderCode;
            document.getElementById('proofPreviewImgTag').src = imgUrl;
            document.getElementById('proofPreviewModal').style.display = 'flex';
        }

        function closeProofModal() {
            document.getElementById('proofPreviewModal').style.display = 'none';
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
            var pModal = document.getElementById('printInvoiceModal');
            if (event.target === pModal) pModal.style.display = 'none';
            var dModal = document.getElementById('deliveryModal');
            if (event.target === dModal) dModal.style.display = 'none';
            var rModal = document.getElementById('refundModal');
            if (event.target === rModal) rModal.style.display = 'none';
            var pfModal = document.getElementById('proofPreviewModal');
            if (event.target === pfModal) pfModal.style.display = 'none';
        };
    </script>
</body>

</html>
