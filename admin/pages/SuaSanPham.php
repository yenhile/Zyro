<?php
require_once('../includes/auth_check.php');
require_once('../../class/classProduct.php');

$productModel = new san_pham();
$brands = $productModel->lay_danh_sach_thuong_hieu();
$categories = $productModel->lay_danh_sach_loai_giay();

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($productId <= 0) {
    header("Location: /Zyro/admin/pages/QuanLySanPham.php");
    exit();
}

$product = $productModel->lay_chi_tiet_san_pham($productId);
if (!$product) {
    header("Location: /Zyro/admin/pages/QuanLySanPham.php");
    exit();
}

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $brandId = isset($_POST['brand_id']) ? (int)$_POST['brand_id'] : 0;
    $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    
    $rawPrice = isset($_POST['price']) ? trim($_POST['price']) : '';
    $cleanPrice = preg_replace('/[^0-9]/', '', $rawPrice);
    $price = (float)$cleanPrice;
    
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    
    if (empty($name) || $brandId <= 0 || $categoryId <= 0 || $price <= 0) {
        $errorMessage = "Vui lòng nhập đầy đủ các thông tin bắt buộc (*) và đơn giá phải lớn hơn 0!";
    } else {
        $newImageUrl = null;

        // Nếu người dùng upload ảnh mới
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['image_file']['tmp_name'];
            $fileName = $_FILES['image_file']['name'];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = array('jpg', 'jpeg', 'png', 'webp');

            if (!in_array($ext, $allowedExts)) {
                $errorMessage = "Định dạng ảnh không hợp lệ! Chỉ chấp nhận file JPG, PNG hoặc WEBP.";
            } else {
                $uniqueName = 'shoe_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $targetAssets = dirname(__FILE__) . '/../../assets/img/' . $uniqueName;
                $targetUploads = dirname(__FILE__) . '/../../uploads/' . $uniqueName;

                if (!is_dir(dirname(__FILE__) . '/../../uploads')) {
                    @mkdir(dirname(__FILE__) . '/../../uploads', 0777, true);
                }

                if (move_uploaded_file($tmpPath, $targetAssets)) {
                    @copy($targetAssets, $targetUploads);
                    $newImageUrl = $uniqueName;
                } else {
                    $errorMessage = "Không thể tải file ảnh lên máy chủ. Vui lòng thử lại!";
                }
            }
        }

        if (empty($errorMessage)) {
            if ($productModel->cap_nhat_san_pham($productId, $name, $brandId, $categoryId, $price, $description, $newImageUrl)) {
                $successMessage = "Đã cập nhật thông tin sản phẩm #$productId thành công!";
                // Cập nhật lại dữ liệu đang hiển thị trên form
                $product = $productModel->lay_chi_tiet_san_pham($productId);
            } else {
                $errorMessage = "Có lỗi xảy ra trong quá trình cập nhật CSDL. Vui lòng thử lại!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Sản Phẩm #<?php echo $productId; ?> — ZYRO Admin</title>
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
        .edit-form-card {
            background: #FFFFFF;
            border-radius: 16px;
            border: 1px solid #E9ECEF;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            padding: 28px;
            margin-bottom: 30px;
        }
        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        .form-group-admin {
            margin-bottom: 20px;
        }
        .form-label-admin {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 8px;
        }
        .form-label-admin span.req {
            color: #E03131;
        }
        .form-control-admin {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #CED4DA;
            border-radius: 8px;
            font-size: 14px;
            color: #212529;
            transition: all 0.2s;
            outline: none;
            box-sizing: border-box;
            background: #FAFAFA;
        }
        .form-control-admin:focus {
            background: #FFFFFF;
            border-color: #FD7E14;
            box-shadow: 0 0 0 3px rgba(253, 126, 20, 0.15);
        }
        .price-highlight-input {
            font-size: 18px;
            font-weight: 700;
            color: #D9480F;
        }
        .image-preview-wrapper {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #F8F9FA;
            border: 1px dashed #CED4DA;
            padding: 16px;
            border-radius: 10px;
        }
        .current-img-box {
            width: 90px;
            height: 90px;
            border-radius: 8px;
            border: 1px solid #DEE2E6;
            background: #FFFFFF;
            object-fit: cover;
        }
        .btn-action-group {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-top: 10px;
        }
        .btn-save-submit {
            padding: 12px 28px;
            background: #FD7E14;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-save-submit:hover {
            background: #E8590C;
            box-shadow: 0 6px 16px rgba(232, 89, 12, 0.3);
            transform: translateY(-1px);
        }
        .btn-back-link {
            padding: 12px 20px;
            background: #F1F3F5;
            color: #495057;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-back-link:hover {
            background: #E9ECEF;
            color: #212529;
        }
    </style>
</head>

<body>
    <div class="app-wrapper">
        <?php include('../includes/sidebar.php'); ?>
        <div id="container">
            <?php include('../includes/header.php'); ?>

            <div class="admin-content-box">
                <div class="admin-header-row">
                    <div>
                        <h1 class="admin-page-heading">⚙️ Chỉnh Sửa Sản Phẩm #<?php echo $productId; ?></h1>
                        <p class="admin-page-desc">Cập nhật đơn giá, tên sản phẩm, thông số và hình ảnh hiển thị trên cửa hàng</p>
                    </div>
                    <div>
                        <a href="/Zyro/admin/pages/QuanLySanPham.php" class="btn-back-link">
                            ← Quay lại Danh Sách
                        </a>
                        <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo $productId; ?>" target="_blank" class="btn-back-link" style="background: #E7F5FF; color: #1971C2;">
                            👁️ Xem trên Website
                        </a>
                    </div>
                </div>

                <?php if (!empty($successMessage)): ?>
                    <div style="background: #EBFBEE; border: 1px solid #D3F9D8; color: #2B8A3E; padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600;">
                        ✓ <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div style="background: #FFF5F5; border: 1px solid #FFC9C9; color: #E03131; padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600;">
                        ⚠️ <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <div class="edit-form-card">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Tên giày thể thao <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control-admin" required value="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-row-3">
                            <div class="form-group-admin">
                                <label class="form-label-admin">Thương hiệu <span class="req">*</span></label>
                                <select name="brand_id" class="form-control-admin" required>
                                    <?php foreach ($brands as $b): ?>
                                        <option value="<?php echo $b['brand_id']; ?>" <?php echo ($b['brand_id'] == $product['brand_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group-admin">
                                <label class="form-label-admin">Phân loại giày <span class="req">*</span></label>
                                <select name="category_id" class="form-control-admin" required>
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?php echo $c['category_id']; ?>" <?php echo ($c['category_id'] == $product['category_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group-admin">
                                <label class="form-label-admin">Đơn giá niêm yết (VNĐ) <span class="req">*</span></label>
                                <input type="number" name="price" class="form-control-admin price-highlight-input" min="0" step="1000" required 
                                       value="<?php echo (float)$product['price']; ?>">
                            </div>
                        </div>

                        <div class="form-group-admin">
                            <label class="form-label-admin">Mô tả đặc tính & công nghệ giày</label>
                            <textarea name="description" class="form-control-admin" rows="5"><?php echo htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="form-group-admin">
                            <label class="form-label-admin">Hình ảnh sản phẩm</label>
                            <div class="image-preview-wrapper">
                                <img src="/Zyro/assets/img/<?php echo htmlspecialchars($product['image_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="Ảnh hiện tại" class="current-img-box">
                                <div>
                                    <p style="margin: 0 0 6px 0; font-size: 13px; font-weight: 600; color: #495057;">
                                        Ảnh hiện tại: <code><?php echo htmlspecialchars($product['image_url'], ENT_QUOTES, 'UTF-8'); ?></code>
                                    </p>
                                    <p style="margin: 0 0 10px 0; font-size: 12px; color: #868E96;">
                                        Nếu muốn thay đổi ảnh, chọn tệp mới bên dưới (JPG, PNG, WEBP):
                                    </p>
                                    <input type="file" name="image_file" accept="image/*" class="form-control-admin" style="padding: 6px;">
                                </div>
                            </div>
                        </div>

                        <div class="btn-action-group">
                            <button type="submit" class="btn-save-submit">
                                💾 Lưu Thay Đổi Sản Phẩm
                            </button>
                            <a href="/Zyro/admin/pages/QuanLySanPham.php" class="btn-back-link">
                                Hủy bỏ
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <?php include('../includes/footer.php'); ?>
        </div>
    </div>
    <script src="/Zyro/admin/js/sidebar.js"></script>
</body>
</html>
