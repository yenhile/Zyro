<?php
require_once('../includes/auth_check.php');
require_once('../../class/classProduct.php');

$productModel = new san_pham();
$brands = $productModel->lay_danh_sach_thuong_hieu();
$categories = $productModel->lay_danh_sach_loai_giay();

$activeTab = (isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'article') ? 'article' : 'product';

$successProduct = '';
$errorProduct = '';
$newProductId = null;

$successArticle = '';
$errorArticle = '';
$newPostId = null;

// 1. Xử lý POST Thêm Sản Phẩm Mới (Tab 1)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'product') {
    $activeTab = 'product';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $brandId = isset($_POST['brand_id']) ? (int)$_POST['brand_id'] : 0;
    $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    
    if (empty($name) || $brandId <= 0 || $categoryId <= 0 || $price <= 0) {
        $errorProduct = "Vui lòng nhập đầy đủ các trường thông tin bắt buộc (*)!";
    } elseif (!isset($_FILES['image_file']) || $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
        $errorProduct = "Vui lòng chọn tệp hình ảnh sản phẩm từ máy tính (*)!";
    } else {
        $tmpPath = $_FILES['image_file']['tmp_name'];
        $fileName = $_FILES['image_file']['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = array('jpg', 'jpeg', 'png', 'webp');

        if (!in_array($ext, $allowedExts)) {
            $errorProduct = "Định dạng ảnh không hợp lệ! Chỉ chấp nhận file JPG, PNG hoặc WEBP.";
        } else {
            $uniqueName = 'shoe_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $targetAssets = dirname(__FILE__) . '/../../assets/img/' . $uniqueName;
            $targetUploads = dirname(__FILE__) . '/../../uploads/' . $uniqueName;

            if (!is_dir(dirname(__FILE__) . '/../../uploads')) {
                @mkdir(dirname(__FILE__) . '/../../uploads', 0777, true);
            }

            if (move_uploaded_file($tmpPath, $targetAssets)) {
                @copy($targetAssets, $targetUploads);
                $newProductId = $productModel->them_san_pham($name, $brandId, $categoryId, $price, $description, $uniqueName);
                if ($newProductId) {
                    $successProduct = "Thêm sản phẩm mới thành công! Mã sản phẩm: #$newProductId";
                    // Tự động làm mới form sau khi đăng thành công
                    $_POST = array();
                } else {
                    $errorProduct = "Đã xảy ra lỗi khi thêm sản phẩm vào CSDL MySQL!";
                }
            } else {
                $errorProduct = "Không thể lưu file ảnh vào máy chủ. Vui lòng kiểm tra quyền thư mục!";
            }
        }
    }
}

// 2. Xử lý POST Thêm Bài Viết Mới (Tab 2)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'article') {
    $activeTab = 'article';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $summary = isset($_POST['summary']) ? trim($_POST['summary']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $authorId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

    if (empty($title) || empty($summary) || empty($content)) {
        $errorArticle = "Vui lòng điền đầy đủ tiêu đề, tóm tắt và nội dung bài viết (*)!";
    } elseif (!isset($_FILES['article_image_file']) || $_FILES['article_image_file']['error'] !== UPLOAD_ERR_OK) {
        $errorArticle = "Vui lòng chọn tệp ảnh bìa cho bài viết (*)!";
    } else {
        $tmpPath = $_FILES['article_image_file']['tmp_name'];
        $fileName = $_FILES['article_image_file']['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = array('jpg', 'jpeg', 'png', 'webp');

        if (!in_array($ext, $allowedExts)) {
            $errorArticle = "Định dạng ảnh không hợp lệ! Chỉ chấp nhận file JPG, PNG hoặc WEBP.";
        } else {
            $uniqueName = 'blog_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $targetAssets = dirname(__FILE__) . '/../../assets/img/' . $uniqueName;
            $targetUploads = dirname(__FILE__) . '/../../uploads/' . $uniqueName;

            if (!is_dir(dirname(__FILE__) . '/../../uploads')) {
                @mkdir(dirname(__FILE__) . '/../../uploads', 0777, true);
            }

            if (move_uploaded_file($tmpPath, $targetAssets)) {
                @copy($targetAssets, $targetUploads);
                $newPostId = $productModel->them_bai_viet($title, $summary, $content, $uniqueName, $authorId);
                if ($newPostId) {
                    $successArticle = "Đăng bài viết mới thành công! Mã bài viết: #$newPostId";
                    // Tự động làm mới form sau khi đăng thành công
                    $_POST = array();
                } else {
                    $errorArticle = "Đã xảy ra lỗi khi lưu bài viết vào CSDL MySQL!";
                }
            } else {
                $errorArticle = "Không thể lưu file ảnh bìa vào máy chủ!";
            }
        }
    }
}

// 3. Xử lý Xóa Bài Viết (GET)
if (isset($_GET['action']) && $_GET['action'] === 'delete_article' && isset($_GET['id'])) {
    $activeTab = 'article';
    $delId = (int)$_GET['id'];
    if ($delId > 0) {
        if ($productModel->xoa_bai_viet($delId)) {
            $successArticle = "Đã xóa bài viết #$delId thành công!";
        } else {
            $errorArticle = "Không thể xóa bài viết #$delId. Vui lòng thử lại!";
        }
    }
}

// 4. Lấy danh sách bài viết phân trang cho Bảng Quản Lý Bài Viết
$articlePage = (isset($_GET['page']) && $activeTab === 'article') ? max(1, (int)$_GET['page']) : 1;
$articleLimit = (isset($_GET['limit']) && $activeTab === 'article' && in_array((int)$_GET['limit'], array(5, 10, 20, 50, 100))) ? (int)$_GET['limit'] : 5;
$articleSearch = isset($_GET['search']) ? trim($_GET['search']) : '';

$totalItems = $productModel->dem_tong_so_bai_viet($articleSearch);
$totalPages = max(1, (int)ceil($totalItems / $articleLimit));
if ($articlePage > $totalPages) $articlePage = $totalPages;
$offset = ($articlePage - 1) * $articleLimit;

$articlesList = $productModel->lay_danh_sach_bai_viet_phan_trang($articleLimit, $offset, $articleSearch);

// Gán biến cho pagination.php
$currentPage = $articlePage;
$perPage = $articleLimit;
$queryParams = $_GET;
$queryParams['tab'] = 'article';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Sản Phẩm & Bài Viết — ZYRO Admin</title>
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
        .publish-tab-nav {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 14px;
        }
        .publish-tab-item {
            text-decoration: none;
            padding: 10px 22px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 14px;
            color: #64748b;
            background: #f1f5f9;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .publish-tab-item:hover {
            color: #0f172a;
            background: #e2e8f0;
        }
        .publish-tab-item.active {
            color: #ffffff;
            background: #0f172a;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
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
                        <h1 class="admin-page-heading">✍️ Đăng Sản Phẩm & Bài Viết</h1>
                        <p class="admin-page-desc">Hệ thống biên tập và xuất bản giày thể thao & tin tức thời trang ZYRO Store</p>
                    </div>
                    <div>
                        <a href="/Zyro/admin/pages/QuanLySanPham.php" class="btn-cancel-action" style="padding: 10px 18px; font-size: 13px; text-decoration: none;">
                            ← Quay Lại Kho Sản Phẩm
                        </a>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <div class="publish-tab-nav">
                    <a href="?tab=product" class="publish-tab-item <?php echo ($activeTab === 'product') ? 'active' : ''; ?>">
                        👟 1. Đăng Sản Phẩm Mới (Post Giày)
                    </a>
                    <a href="?tab=article" class="publish-tab-item <?php echo ($activeTab === 'article') ? 'active' : ''; ?>">
                        📰 2. Đăng Bài Viết & Quản Lý Tin Tức
                    </a>
                </div>

                <!-- ================= TAB 1: ĐĂNG SẢN PHẨM MỚI ================= -->
                <?php if ($activeTab === 'product'): ?>

                    <?php if (!empty($successProduct)): ?>
                        <div style="background: #EBFBEE; border: 1px solid #D3F9D8; color: #2B8A3E; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px;">
                            <h4 style="margin: 0 0 6px 0; font-size: 16px; font-weight: 800;">✓ <?php echo htmlspecialchars($successProduct, ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p style="margin: 0; font-size: 13px;">
                                <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo (int)$newProductId; ?>" target="_blank" style="color: #2B8A3E; font-weight: bold; text-decoration: underline;">
                                    Xem ngay sản phẩm trên website ↗
                                </a> hoặc 
                                <a href="/Zyro/admin/pages/QuanLySanPham.php" style="color: #2B8A3E; font-weight: bold; text-decoration: underline; margin-left: 10px;">
                                    Về trang Quản Lý Sản Phẩm →
                                </a>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errorProduct)): ?>
                        <div style="background: #FFF5F5; border: 1px solid #FFE3E3; color: #E03131; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px;">
                            <h4 style="margin: 0 0 6px 0; font-size: 15px; font-weight: 800;">⚠ <?php echo htmlspecialchars($errorProduct, ENT_QUOTES, 'UTF-8'); ?></h4>
                        </div>
                    <?php endif; ?>

                    <form action="ThemSanPham.php?tab=product" method="POST" enctype="multipart/form-data" class="admin-form-card" id="formPostProduct">
                        <input type="hidden" name="form_type" value="product">

                        <div class="form-grid-2">
                            <div class="form-group-item">
                                <label for="prod_name" class="form-label-bold">
                                    <span>Tên Giày Thể Thao</span>
                                    <span class="required-badge">(*)</span>
                                </label>
                                <input type="text" id="prod_name" name="name" class="form-input-text" 
                                       placeholder="Ví dụ: Nike Air Zoom Pegasus 41" required
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>

                            <div class="form-group-item">
                                <label for="prod_price" class="form-label-bold">
                                    <span>Giá Bán Niêm Yết (VNĐ)</span>
                                    <span class="required-badge">(*)</span>
                                </label>
                                <input type="number" id="prod_price" name="price" class="form-input-text" 
                                       placeholder="Ví dụ: 2850000" min="10000" step="1000" required
                                       value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group-item">
                                <label for="prod_brand" class="form-label-bold">
                                    <span>Thương Hiệu Giày</span>
                                    <span class="required-badge">(*)</span>
                                </label>
                                <select id="prod_brand" name="brand_id" class="form-select-custom" required>
                                    <option value="">-- Chọn Thương Hiệu --</option>
                                    <?php foreach ($brands as $b): ?>
                                        <option value="<?php echo (int)$b['brand_id']; ?>" 
                                            <?php echo (isset($_POST['brand_id']) && (int)$_POST['brand_id'] === (int)$b['brand_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group-item">
                                <label for="prod_category" class="form-label-bold">
                                    <span>Phân Loại Dòng Giày</span>
                                    <span class="required-badge">(*)</span>
                                </label>
                                <select id="prod_category" name="category_id" class="form-select-custom" required>
                                    <option value="">-- Chọn Phân Loại --</option>
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?php echo (int)$c['category_id']; ?>"
                                            <?php echo (isset($_POST['category_id']) && (int)$_POST['category_id'] === (int)$c['category_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group-item">
                            <label for="prod_desc" class="form-label-bold">
                                <span>Mô Tả Sản Phẩm & Công Nghệ Đệm Khí</span>
                            </label>
                            <textarea id="prod_desc" name="description" class="form-textarea-custom" rows="4" 
                                      placeholder="Mô tả chất liệu thân giày, đệm khí Air Zoom, đế cao su chống trượt..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                        </div>

                        <!-- Khung Cố Định Hình Ảnh Sản Phẩm (Không Gán Sẵn, Không Tràn Màn Hình) -->
                        <div class="image-upload-section">
                            <label class="form-label-bold">
                                <span>Hình Ảnh Sản Phẩm (Khung Cố Định 1:1)</span>
                                <span class="required-badge">(*) Bắt buộc</span>
                            </label>
                            
                            <div class="image-upload-wrapper">
                                <!-- Khung xem trước cố định đúng 200x200px -->
                                <div class="image-preview-box-product" id="box-preview-product">
                                    <div class="image-preview-placeholder" id="placeholder-prod">
                                        <span class="placeholder-icon">📷</span>
                                        <span class="placeholder-text">Chưa chọn ảnh<br><small style="font-size: 11px; font-weight: normal; color: #94a3b8;">Khung cố định 200×200px</small></span>
                                    </div>
                                    <img id="preview-image-product" src="" alt="Xem trước ảnh sản phẩm">
                                </div>

                                <div class="image-upload-controls">
                                    <label for="image_file" class="custom-file-upload-btn">
                                        📁 Chọn Tệp Ảnh Từ Máy Tính...
                                    </label>
                                    <input type="file" id="image_file" name="image_file" accept=".png,.jpg,.jpeg,.webp" style="display: none;" required>
                                    
                                    <div id="file-chosen-prod" style="font-size: 13px; font-weight: 600; color: #0284c7; margin-top: 8px;"></div>
                                    
                                    <div class="upload-file-hint">
                                        💡 Chấp nhận file định dạng: <strong>PNG, JPG, JPEG, WEBP</strong>.<br>
                                        📐 Ảnh tự động được co vừa vặn hoàn hảo trong khung 200×200px, tuyệt đối không bị tràn che màn hình.
                                    </div>

                                    <button type="button" id="btn-clear-prod-img" class="btn-cancel-action" style="padding: 6px 14px; font-size: 12px; margin-top: 10px; display: none;">
                                        ✕ Hủy chọn ảnh
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-action-row">
                            <button type="submit" class="btn-submit-action">
                                🚀 Xuất Bản Sản Phẩm Mới
                            </button>
                            <button type="button" class="btn-cancel-action" onclick="resetProductForm()">
                                🔄 Làm Mới Form
                            </button>
                        </div>
                    </form>

                <!-- ================= TAB 2: ĐĂNG BÀI VIẾT & QUẢN LÝ BÀI VIẾT ================= -->
                <?php else: ?>

                    <?php if (!empty($successArticle)): ?>
                        <div style="background: #EBFBEE; border: 1px solid #D3F9D8; color: #2B8A3E; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px;">
                            <h4 style="margin: 0 0 6px 0; font-size: 16px; font-weight: 800;">✓ <?php echo htmlspecialchars($successArticle, ENT_QUOTES, 'UTF-8'); ?></h4>
                            <?php if ($newPostId): ?>
                                <p style="margin: 0; font-size: 13px;">
                                    <a href="/Zyro/pages/GioiThieu.php?post_id=<?php echo (int)$newPostId; ?>" target="_blank" style="color: #2B8A3E; font-weight: bold; text-decoration: underline;">
                                        Xem bài viết vừa đăng trên website công khai ↗
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errorArticle)): ?>
                        <div style="background: #FFF5F5; border: 1px solid #FFE3E3; color: #E03131; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px;">
                            <h4 style="margin: 0 0 6px 0; font-size: 15px; font-weight: 800;">⚠ <?php echo htmlspecialchars($errorArticle, ENT_QUOTES, 'UTF-8'); ?></h4>
                        </div>
                    <?php endif; ?>

                    <!-- Form Đăng Bài Viết Mới -->
                    <form action="ThemSanPham.php?tab=article" method="POST" enctype="multipart/form-data" class="admin-form-card" id="formPostArticle">
                        <input type="hidden" name="form_type" value="article">

                        <div class="form-group-item">
                            <label for="post_title" class="form-label-bold">
                                <span>Tiêu Đề Bài Viết / Cẩm Nang Tin Tức</span>
                                <span class="required-badge">(*)</span>
                            </label>
                            <input type="text" id="post_title" name="title" class="form-input-text" 
                                   placeholder="Ví dụ: Top 5 Mẫu Giày Sneaker Retro Đáng Mua Nhất 2026" required
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                        </div>

                        <div class="form-group-item">
                            <label for="post_summary" class="form-label-bold">
                                <span>Đoạn Trích Tóm Tắt (Short Summary)</span>
                                <span class="required-badge">(*)</span>
                            </label>
                            <textarea id="post_summary" name="summary" class="form-textarea-custom" rows="3" required
                                      placeholder="Tóm tắt nội dung cốt lõi của bài viết trong 2-3 câu ngắn..."><?php echo isset($_POST['summary']) ? htmlspecialchars($_POST['summary'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                            <small style="color: #64748b; font-size: 12px;">Đoạn tóm tắt này sẽ hiển thị tại danh thiếp bài viết ở trang chủ và trang giới thiệu.</small>
                        </div>

                        <div class="form-group-item">
                            <label for="post_content" class="form-label-bold">
                                <span>Nội Dung Chi Tiết Toàn Văn</span>
                                <span class="required-badge">(*)</span>
                            </label>
                            <textarea id="post_content" name="content" class="form-textarea-custom" rows="8" required
                                      placeholder="Nhập toàn văn bài viết, hướng dẫn phối đồ, kinh nghiệm chọn size giày..."><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                        </div>

                        <!-- Khung Cố Định Ảnh Bìa Bài Viết (Không Gán Sẵn, Không Tràn Màn Hình) -->
                        <div class="image-upload-section">
                            <label class="form-label-bold">
                                <span>Ảnh Bìa Bài Viết (Khung Cố Định Banner)</span>
                                <span class="required-badge">(*) Bắt buộc</span>
                            </label>
                            
                            <div class="image-upload-wrapper">
                                <!-- Khung xem trước cố định tối đa 420x200px -->
                                <div class="image-preview-box-article" id="box-preview-article">
                                    <div class="image-preview-placeholder" id="placeholder-art">
                                        <span class="placeholder-icon">🖼️</span>
                                        <span class="placeholder-text">Chưa chọn ảnh bìa<br><small style="font-size: 11px; font-weight: normal; color: #94a3b8;">Khung cố định 420×200px</small></span>
                                    </div>
                                    <img id="preview-image-article" src="" alt="Xem trước ảnh bìa bài viết">
                                </div>

                                <div class="image-upload-controls">
                                    <label for="article_image_file" class="custom-file-upload-btn">
                                        📁 Chọn Tệp Ảnh Bìa Từ Máy Tính...
                                    </label>
                                    <input type="file" id="article_image_file" name="article_image_file" accept=".png,.jpg,.jpeg,.webp" style="display: none;" required>
                                    
                                    <div id="file-chosen-art" style="font-size: 13px; font-weight: 600; color: #0284c7; margin-top: 8px;"></div>

                                    <div class="upload-file-hint">
                                        💡 Khuyến nghị tỉ lệ ảnh ngang hoặc chữ nhật (JPG, PNG, WEBP).<br>
                                        📐 Ảnh tự động được co vừa vặn trong khung 420×200px, không bị tràn màn hình.
                                    </div>

                                    <button type="button" id="btn-clear-art-img" class="btn-cancel-action" style="padding: 6px 14px; font-size: 12px; margin-top: 10px; display: none;">
                                        ✕ Hủy chọn ảnh
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-action-row">
                            <button type="submit" class="btn-submit-action" style="background: #2563eb;">
                                📢 Xuất Bản Bài Viết Ngay
                            </button>
                            <button type="button" class="btn-cancel-action" onclick="resetArticleForm()">
                                🔄 Làm Mới Form
                            </button>
                        </div>
                    </form>

                    <!-- ================= BẢNG QUẢN LÝ BÀI VIẾT ĐÃ POST ================= -->
                    <div class="articles-card">
                        <div class="articles-card-header">
                            <h3 class="articles-card-title">
                                <span>📰 Danh Sách Bài Viết Đã Đăng</span>
                                <span style="font-size: 12px; font-weight: 700; background: #e0f2fe; color: #0284c7; padding: 4px 10px; border-radius: 20px;">
                                    <?php echo (int)$totalItems; ?> bài viết
                                </span>
                            </h3>
                        </div>

                        <div class="admin-table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th style="width: 60px; text-align: center;">#ID</th>
                                        <th style="width: 100px;">ẢNH BÌA</th>
                                        <th>TIÊU ĐỀ BÀI VIẾT</th>
                                        <th>ĐOẠN TRÍCH TÓM TẮT</th>
                                        <th style="width: 130px;">TÁC GIẢ</th>
                                        <th style="width: 120px;">NGÀY ĐĂNG</th>
                                        <th style="width: 140px; text-align: right;">THAO TÁC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($articlesList)): ?>
                                        <?php foreach ($articlesList as $art): 
                                            $artId = (int)$art['post_id'];
                                            $artTitle = htmlspecialchars($art['title'], ENT_QUOTES, 'UTF-8');
                                            $artSummary = htmlspecialchars($art['summary'], ENT_QUOTES, 'UTF-8');
                                            $artAuthor = !empty($art['author_name']) ? htmlspecialchars($art['author_name'], ENT_QUOTES, 'UTF-8') : 'Admin Zyro';
                                            $artDate = date('d/m/Y', strtotime($art['created_at']));
                                            $artTime = date('H:i', strtotime($art['created_at']));

                                            $imgName = !empty($art['image_url']) ? $art['image_url'] : 'banner1.png';
                                            $imgSrc = (file_exists(dirname(__FILE__) . '/../../uploads/' . $imgName)) 
                                                    ? '/Zyro/uploads/' . $imgName 
                                                    : '/Zyro/assets/img/' . $imgName;
                                        ?>
                                            <tr>
                                                <td style="text-align: center; font-weight: 700; color: #64748b;">
                                                    #<?php echo $artId; ?>
                                                </td>
                                                <td>
                                                    <img src="<?php echo htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8'); ?>" 
                                                         alt="<?php echo $artTitle; ?>" 
                                                         class="article-thumb-img"
                                                         onerror="this.src='/Zyro/assets/img/banner1.png';">
                                                </td>
                                                <td>
                                                    <a href="/Zyro/pages/GioiThieu.php?post_id=<?php echo $artId; ?>" target="_blank" style="font-weight: 700; color: #0f172a; text-decoration: none; display: block; line-height: 1.4;">
                                                        <?php echo $artTitle; ?>
                                                    </a>
                                                </td>
                                                <td style="color: #64748b; font-size: 13px; max-width: 320px; line-height: 1.4;">
                                                    <?php echo (mb_strlen($artSummary) > 100) ? mb_substr($artSummary, 0, 100) . '...' : $artSummary; ?>
                                                </td>
                                                <td>
                                                    <span style="font-size: 12px; font-weight: 600; color: #0369a1; background: #f0f9ff; padding: 3px 8px; border-radius: 6px;">
                                                        👤 <?php echo $artAuthor; ?>
                                                    </span>
                                                </td>
                                                <td style="font-size: 13px; color: #64748b;">
                                                    <?php echo $artDate; ?><br>
                                                    <span style="font-size: 11px; color: #94a3b8;"><?php echo $artTime; ?></span>
                                                </td>
                                                <td style="text-align: right;">
                                                    <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                                        <a href="/Zyro/pages/GioiThieu.php?post_id=<?php echo $artId; ?>" target="_blank" class="btn-detail-sm" title="Xem bài viết công khai">
                                                            👁️ Xem
                                                        </a>
                                                        <a href="ThemSanPham.php?tab=article&action=delete_article&id=<?php echo $artId; ?>&page=<?php echo $currentPage; ?>&limit=<?php echo $perPage; ?>" 
                                                           class="btn-delete-sm" 
                                                           onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết #<?php echo $artId; ?>: &quot;<?php echo addslashes($art['title']); ?>&quot;?');"
                                                           title="Xóa bài viết">
                                                            🗑️ Xóa
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                                                Chưa có bài viết nào trong hệ thống. Hãy đăng bài viết đầu tiên ở form phía trên!
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Phân trang danh sách bài viết -->
                        <?php include('../includes/pagination.php'); ?>
                    </div>

                <?php endif; ?>
            </div>

            <?php include('../includes/footer.php'); ?>
        </div>
    </div>

    <!-- JavaScript Xử Lý Preview Ảnh Cố Định Không Bị Tràn Màn Hình -->
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // 1. Preview Ảnh Sản Phẩm (Khung 200x200px)
        const fileInputProd = document.getElementById("image_file");
        const previewImgProd = document.getElementById("preview-image-product");
        const placeholderProd = document.getElementById("placeholder-prod");
        const fileChosenProd = document.getElementById("file-chosen-prod");
        const btnClearProd = document.getElementById("btn-clear-prod-img");

        if (fileInputProd && previewImgProd) {
            fileInputProd.addEventListener("change", function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (evt) {
                        previewImgProd.src = evt.target.result;
                        previewImgProd.style.display = "block";
                        if (placeholderProd) placeholderProd.style.display = "none";
                        if (fileChosenProd) fileChosenProd.textContent = "✓ Đã chọn: " + file.name;
                        if (btnClearProd) btnClearProd.style.display = "inline-flex";
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        if (btnClearProd && fileInputProd) {
            btnClearProd.addEventListener("click", function () {
                fileInputProd.value = "";
                previewImgProd.src = "";
                previewImgProd.style.display = "none";
                if (placeholderProd) placeholderProd.style.display = "block";
                if (fileChosenProd) fileChosenProd.textContent = "";
                btnClearProd.style.display = "none";
            });
        }

        // 2. Preview Ảnh Bìa Bài Viết (Khung 420x200px)
        const fileInputArt = document.getElementById("article_image_file");
        const previewImgArt = document.getElementById("preview-image-article");
        const placeholderArt = document.getElementById("placeholder-art");
        const fileChosenArt = document.getElementById("file-chosen-art");
        const btnClearArt = document.getElementById("btn-clear-art-img");

        if (fileInputArt && previewImgArt) {
            fileInputArt.addEventListener("change", function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (evt) {
                        previewImgArt.src = evt.target.result;
                        previewImgArt.style.display = "block";
                        if (placeholderArt) placeholderArt.style.display = "none";
                        if (fileChosenArt) fileChosenArt.textContent = "✓ Đã chọn: " + file.name;
                        if (btnClearArt) btnClearArt.style.display = "inline-flex";
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        if (btnClearArt && fileInputArt) {
            btnClearArt.addEventListener("click", function () {
                fileInputArt.value = "";
                previewImgArt.src = "";
                previewImgArt.style.display = "none";
                if (placeholderArt) placeholderArt.style.display = "block";
                if (fileChosenArt) fileChosenArt.textContent = "";
                btnClearArt.style.display = "none";
            });
        }

        // Tự động làm mới giao diện form sau khi post thành công
        <?php if (!empty($successProduct)): ?>
            resetProductForm();
        <?php endif; ?>
        <?php if (!empty($successArticle)): ?>
            resetArticleForm();
        <?php endif; ?>
    });

    // Hàm làm mới toàn bộ các trường và ảnh của Form Sản Phẩm
    function resetProductForm() {
        const form = document.getElementById("formPostProduct");
        if (form) {
            form.reset();
            const inputs = form.querySelectorAll("input[type='text'], input[type='number'], textarea");
            inputs.forEach(function (inp) {
                inp.value = "";
            });
            const selects = form.querySelectorAll("select");
            selects.forEach(function (sel) {
                sel.selectedIndex = 0;
            });
            const fileInput = document.getElementById("image_file");
            if (fileInput) fileInput.value = "";
        }
        const previewImgProd = document.getElementById("preview-image-product");
        const placeholderProd = document.getElementById("placeholder-prod");
        const fileChosenProd = document.getElementById("file-chosen-prod");
        const btnClearProd = document.getElementById("btn-clear-prod-img");
        if (previewImgProd) {
            previewImgProd.src = "";
            previewImgProd.style.display = "none";
        }
        if (placeholderProd) placeholderProd.style.display = "block";
        if (fileChosenProd) fileChosenProd.textContent = "";
        if (btnClearProd) btnClearProd.style.display = "none";
    }

    // Hàm làm mới toàn bộ các trường và ảnh của Form Bài Viết
    function resetArticleForm() {
        const form = document.getElementById("formPostArticle");
        if (form) {
            form.reset();
            const inputs = form.querySelectorAll("input[type='text'], textarea");
            inputs.forEach(function (inp) {
                inp.value = "";
            });
            const fileInput = document.getElementById("article_image_file");
            if (fileInput) fileInput.value = "";
        }
        const previewImgArt = document.getElementById("preview-image-article");
        const placeholderArt = document.getElementById("placeholder-art");
        const fileChosenArt = document.getElementById("file-chosen-art");
        const btnClearArt = document.getElementById("btn-clear-art-img");
        if (previewImgArt) {
            previewImgArt.src = "";
            previewImgArt.style.display = "none";
        }
        if (placeholderArt) placeholderArt.style.display = "block";
        if (fileChosenArt) fileChosenArt.textContent = "";
        if (btnClearArt) btnClearArt.style.display = "none";
    }
    </script>
</body>
</html>
