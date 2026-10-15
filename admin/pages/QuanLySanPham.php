<?php
require_once('../includes/auth_check.php');
require_once('../../class/classProduct.php');

$productModel = new san_pham();
$successMessage = '';
$errorMessage = '';

// Xử lý Xóa sản phẩm
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    if ($delId > 0) {
        if ($productModel->xoa_san_pham($delId)) {
            $successMessage = "Đã xóa sản phẩm #$delId thành công!";
        } else {
            $errorMessage = "Không thể xóa sản phẩm. Có thể sản phẩm này đang nằm trong đơn hàng!";
        }
    }
}

$products = $productModel->lay_danh_sach_tat_ca_san_pham();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm — ZYRO Admin</title>
    <link rel="stylesheet" href="/Zyro/admin/css/layout.css">
    <link rel="stylesheet" href="/Zyro/admin/css/index.css">
    <link rel="stylesheet" href="/Zyro/admin/css/admin-style.css">
</head>

<body>
    <div class="app-wrapper">
        <?php include('../includes/sidebar.php'); ?>
        <div id="container">
            <?php include('../includes/header.php'); ?>

            <div class="admin-content-box">
                <div class="admin-header-row">
                    <div>
                        <h1 class="admin-page-heading">👟 Quản Lý Sản Phẩm</h1>
                        <p class="admin-page-desc">Danh sách toàn bộ giày thể thao trong kho hàng ZYRO Store (Tổng: <?php echo count($products); ?> sản phẩm)</p>
                    </div>
                    <div>
                        <a href="/Zyro/admin/pages/ThemSanPham.php" class="btn-admin-primary">
                            ➕ Thêm Sản Phẩm Mới
                        </a>
                    </div>
                </div>

                <?php if (!empty($successMessage)): ?>
                    <div style="background: #EBFBEE; border: 1px solid #D3F9D8; color: #2B8A3E; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600;">
                        ✓ <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div style="background: #FFF5F5; border: 1px solid #FFC9C9; color: #E03131; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600;">
                        ⚠️ <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <div class="admin-card">
                    <div class="admin-card-body" style="padding: 0;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th style="width: 80px;">Hình Ảnh</th>
                                    <th>Tên Giày Thể Thao</th>
                                    <th>Thương Hiệu</th>
                                    <th>Phân Loại</th>
                                    <th>Đơn Giá</th>
                                    <th>Ngày Tạo</th>
                                    <th style="text-align: right; width: 170px;">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($products)): ?>
                                    <?php foreach ($products as $row): 
                                        $id = (int)$row['product_id'];
                                        $name = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                                        $brand = htmlspecialchars($row['brand_name'] ? $row['brand_name'] : 'Khác', ENT_QUOTES, 'UTF-8');
                                        $category = htmlspecialchars($row['category_name'] ? $row['category_name'] : 'Khác', ENT_QUOTES, 'UTF-8');
                                        $price = number_format($row['price'], 0, ',', '.');
                                        $img = htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8');
                                        $date = date("d/m/Y", strtotime($row['created_at']));
                                    ?>
                                        <tr>
                                            <td><strong>#<?php echo $id; ?></strong></td>
                                            <td>
                                                <div class="admin-thumb-box">
                                                    <img src="/Zyro/assets/img/<?php echo $img; ?>" alt="<?php echo $name; ?>">
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?php echo $name; ?></strong>
                                            </td>
                                            <td><span class="badge-brand-admin"><?php echo $brand; ?></span></td>
                                            <td><span class="badge-category-admin"><?php echo $category; ?></span></td>
                                            <td><strong style="color: #111111;"><?php echo $price; ?>₫</strong></td>
                                            <td style="color: #6C757D; font-size: 13px;"><?php echo $date; ?></td>
                                            <td style="text-align: right;">
                                                <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo $id; ?>" target="_blank" class="btn-view-sm" title="Xem trên website">
                                                    👁️ Xem
                                                </a>
                                                <a href="/Zyro/admin/pages/QuanLySanPham.php?action=delete&id=<?php echo $id; ?>" 
                                                   class="btn-del-sm" 
                                                   onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm: <?php echo addslashes($name); ?> (#<?php echo $id; ?>)?');">
                                                    🗑️ Xóa
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 40px; color: #868E96;">
                                            Chưa có sản phẩm nào trong kho dữ liệu.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php include('../includes/footer.php'); ?>
        </div>
    </div>
    <script src="/Zyro/admin/js/sidebar.js"></script>
</body>
</html>
