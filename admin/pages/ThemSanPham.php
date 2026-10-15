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
    } else {
        $imageUrl = 'trending-NIKE+WAFFLE+NAV.png';

        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
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
                    $imageUrl = $uniqueName;
                } else {
                    $errorProduct = "Không thể lưu file ảnh vào máy chủ. Vui lòng kiểm tra quyền thư mục!";
                }
            }
        } elseif (isset($_POST['preset_image']) && !empty($_POST['preset_image'])) {
            $imageUrl = trim($_POST['preset_image']);
        }

        if (empty($errorProduct)) {
            $newProductId = $productModel->them_san_pham($name, $brandId, $categoryId, $price, $description, $imageUrl);
            if ($newProductId) {
                $successProduct = "Thêm sản phẩm mới thành công! Mã sản phẩm: #$newProductId";
            } else {
                $errorProduct = "Đã xảy ra lỗi khi thêm sản phẩm vào CSDL MySQL!";
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
        $errorArticle = "Vui lòng điền đầy đủ tiêu đề, tóm tắt và nội dung bài viết!";
    } else {
        $imageUrl = 'blog_banner.png';

        if (isset($_FILES['article_image_file']) && $_FILES['article_image_file']['error'] === UPLOAD_ERR_OK) {
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
                    $imageUrl = $uniqueName;
                } else {
                    $errorArticle = "Không thể lưu file ảnh bìa vào máy chủ!";
                }
            }
        } elseif (isset($_POST['preset_article_image']) && !empty($_POST['preset_article_image'])) {
            $imageUrl = trim($_POST['preset_article_image']);
        }

        if (empty($errorArticle)) {
            $newPostId = $productModel->them_bai_viet($title, $summary, $content, $imageUrl, $authorId);
            if ($newPostId) {
                $successArticle = "Đăng bài viết mới thành công! Mã bài viết: #$newPostId";
            } else {
                $errorArticle = "Đã xảy ra lỗi khi lưu bài viết vào CSDL MySQL!";
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
    <title>Post Sản Phẩm & Bài Viết — ZYRO Admin</title>
    <link rel="stylesheet" href="/Zyro/admin/css/layout.css">
    <link rel="stylesheet" href="/Zyro/admin/css/index.css">
    <link rel="stylesheet" href="/Zyro/admin/css/admin-style.css">
    <style>
        .publish-tab-nav {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
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
                        <a href="/Zyro/admin/pages/QuanLySanPham.php" class="btn-view-sm" style="padding: 10px 18px; font-size: 13px;">
                            ← Quay Lại Danh Sách
                        </a>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <div class="publish-tab-nav">
                    <a href="?tab=product" class="publish-tab-item <?php echo ($activeTab === 'product') ? 'active' : ''; ?>">
                        👟 1. Đăng Sản Phẩm Mới (Post Giày)
                    </a>
                    <a href="?tab=article" class="publish-tab-item <?php echo ($activeTab === 'article') ? 'active' : ''; ?>">
                        📰 2. Đăng Bài Viết & Cẩm Nang (Post Tin Tức)
                    </a>
                </div>

                <!-- ================= TAB 1: ĐĂNG SẢN PHẨM MỚI ================= -->
                <?php if ($activeTab === 'product'): ?>

                    <?php if (!empty($successProduct)): ?>
                        <div style="background: #EBFBEE; border: 1px solid #D3F9D8; color: #2B8A3E; padding: 16px 20px; border-radius: 8px; margin-bottom: 24px;">
                            <h4 style="margin: 0 0 6px 0; font-size: 16px; font-weight: 800;">✓ <?php echo htmlspecialchars($successProduct, ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p style="margin: 0; font-size: 13px;">
                                <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo $newProductId; ?>" target="_blank" style="color: #2B8A3E; font-weight: bold; text-decoration: underline;">
                                    Xem ngay sản phẩm trên website ↗
                                </a> hoặc 
                                <a href="?tab=article" style="color: #2B8A3E; font-weight: bold; text-decoration: underline; margin-left: 10px;">
                                    Viết bài giới thiệu cho sản phẩm này →
                                </a>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errorProduct)): ?>
                        <div style="background: #FFF5F5; border: 1px solid #FFE3E3; color: #E03131; padding: 16px 20px; border-radius: 8px; margin-bottom: 24px;">
                            <h4 style="margin: 0 0 6px 0; font-size: 15px; font-weight: 800;">⚠ <?php echo htmlspecialchars($errorProduct, ENT_QUOTES, 'UTF-8'); ?></h4>
                        </div>
                    <?php endif; ?>

                    <form action="ThemSanPham.php?tab=product" method="POST" enctype="multipart/form-data" class="admin-form-card">
                        <input type="hidden" name="form_type" value="product">

                        <div class="form-grid-2">
                            <div class="form-group-item">
                                <label for="prod_name" class="form-label-bold">Tên Giày Thể Thao (*)</label>
                                <input type="text" id="prod_name" name="name" class="form-input-text" 
                                       placeholder="Ví dụ: Nike Air Zoom Pegasus 41" required
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>

                            <div class="form-group-item">
                                <label for="prod_price" class="form-label-bold">Giá Bán Niêm Yết (VNĐ) (*)</label>
                                <input type="number" id="prod_price" name="price" class="form-input-text" 
                                       placeholder="Ví dụ: 2850000" min="10000" step="1000" required
                                       value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group-item">
                                <label for="prod_brand" class="form-label-bold">Thương Hiệu Giày (*)</label>
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
                                <label for="prod_category" class="form-label-bold">Phân Loại Dòng Giày (*)</label>
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
                            <label for="prod_desc" class="form-label-bold">Mô Tả Sản Phẩm & Công Nghệ</label>
                            <textarea id="prod_desc" name="description" class="form-textarea-custom" rows="4" 
                                      placeholder="Mô tả chất liệu thân giày, đệm khí Air Zoom, đế cao su chống trượt..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                        </div>

                        <div class="image-upload-section">
                            <label class="form-label-bold">Hình Ảnh Sản Phẩm (Tỉ Lệ 1:1 Showroom)</label>
                            
                            <div class="image-upload-wrapper">
                                <div class="image-preview-container">
                                    <img id="preview-image-product" src="/Zyro/assets/img/trending-NIKE+WAFFLE+NAV.png" alt="Xem trước ảnh">
                                </div>
                                <div class="image-upload-options">
                                    <div class="upload-file-option">
                                        <label class="small-label">Tải tệp ảnh từ máy tính (Khuyến nghị PNG/WEBP trong suốt):</label>
                                        <input type="file" id="image_file" name="image_file" accept=".png,.jpg,.jpeg,.webp" class="form-file-input">
                                    </div>
                                    <div class="preset-image-option">
                                        <label class="small-label">Hoặc chọn nhanh ảnh mẫu có sẵn trong kho assets:</label>
                                        <select id="preset_image" name="preset_image" class="form-select-custom">
                                            <option value="">-- Chọn ảnh mẫu có sẵn --</option>
                                            <option value="trending-NIKE+WAFFLE+NAV.png">Nike Waffle Nav (Navy/White)</option>
                                            <option value="trending-NIKE+AIR+MAX+PULSE.png">Nike Air Max Pulse (Black/Grey)</option>
                                            <option value="trending-AIR+JORDAN+1+LOW.png">Air Jordan 1 Low (Classic Red/White)</option>
                                            <option value="trending-Converse+Run+Star+Motion.png">Converse Run Star Motion (Futuristic)</option>
                                            <option value="trending-Converse+Chuck+Taylor+All+Star+Lugged+2.0.png">Converse Lugged 2.0 (High Platform)</option>
                                            <option value="trending-JORDAN+STADIUM+90.png">Jordan Stadium 90 (Vintage Beige)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-action-row">
                            <button type="submit" class="btn-submit-action">
                                🚀 Xuất Bản Sản Phẩm Mới
                            </button>
                            <button type="reset" class="btn-cancel-action">
                                Làm Mới Form
                            </button>
                        </div>
                    </form>

                <!-- ================= TAB 2: ĐĂNG BÀI VIẾT & CẨM NANG ================= -->
                <?php else: ?>

                    <?php if (!empty($successArticle)): ?>
                        <div style="background: #EBFBEE; border: 1px solid #D3F9D8; color: #2B8A3E; padding: 16px 20px; border-radius: 8px; margin-bottom: 24px;">
                            <h4 style="margin: 0 0 6px 0; font-size: 16px; font-weight: 800;">✓ <?php echo htmlspecialchars($successArticle, ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p style="margin: 0; font-size: 13px;">
                                <a href="/Zyro/pages/GioiThieu.php?post_id=<?php echo $newPostId; ?>" target="_blank" style="color: #2B8A3E; font-weight: bold; text-decoration: underline;">
                                    Xem bài viết trên website công khai ↗
                                </a>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errorArticle)): ?>
                        <div style="background: #FFF5F5; border: 1px solid #FFE3E3; color: #E03131; padding: 16px 20px; border-radius: 8px; margin-bottom: 24px;">
                            <h4 style="margin: 0 0 6px 0; font-size: 15px; font-weight: 800;">⚠ <?php echo htmlspecialchars($errorArticle, ENT_QUOTES, 'UTF-8'); ?></h4>
                        </div>
                    <?php endif; ?>

                    <form action="ThemSanPham.php?tab=article" method="POST" enctype="multipart/form-data" class="admin-form-card">
                        <input type="hidden" name="form_type" value="article">

                        <div class="form-group-item">
                            <label for="post_title" class="form-label-bold">Tiêu Đề Bài Viết / Cẩm Nang (*)</label>
                            <input type="text" id="post_title" name="title" class="form-input-text" 
                                   placeholder="Ví dụ: Top 5 Mẫu Giày Sneaker Retro Đáng Mua Nhất 2026" required
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                        </div>

                        <div class="form-group-item">
                            <label for="post_summary" class="form-label-bold">Đoạn Trích Tóm Tắt (Short Summary) (*)</label>
                            <textarea id="post_summary" name="summary" class="form-textarea-custom" rows="3" required
                                      placeholder="Tóm tắt nội dung cốt lõi của bài viết trong 2-3 câu ngắn..."><?php echo isset($_POST['summary']) ? htmlspecialchars($_POST['summary'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                            <small style="color: #64748b; font-size: 12px;">Đoạn tóm tắt này sẽ hiển thị tại danh thiếp bài viết ở trang chủ và trang giới thiệu.</small>
                        </div>

                        <div class="form-group-item">
                            <label for="post_content" class="form-label-bold">Nội Dung Chi Tiết Bài Viết (*)</label>
                            <textarea id="post_content" name="content" class="form-textarea-custom" rows="10" required
                                      placeholder="Nhập toàn văn bài viết, hướng dẫn phối đồ, kinh nghiệm chọn size giày..."><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                        </div>

                        <div class="image-upload-section">
                            <label class="form-label-bold">Ảnh Bìa Bài Viết (Cover Banner)</label>
                            
                            <div class="image-upload-wrapper">
                                <div class="image-preview-container">
                                    <img id="preview-image-article" src="/Zyro/assets/img/blog_banner.png" alt="Xem trước ảnh bìa">
                                </div>
                                <div class="image-upload-options">
                                    <div class="upload-file-option">
                                        <label class="small-label">Tải tệp ảnh bìa từ máy tính (JPG, PNG, WEBP):</label>
                                        <input type="file" id="article_image_file" name="article_image_file" accept=".png,.jpg,.jpeg,.webp" class="form-file-input">
                                    </div>
                                    <div class="preset-image-option">
                                        <label class="small-label">Hoặc chọn ảnh bìa có sẵn:</label>
                                        <select id="preset_article_image" name="preset_article_image" class="form-select-custom">
                                            <option value="">-- Chọn ảnh bìa mẫu --</option>
                                            <option value="blog_banner.png">Banner Thương Hiệu ZYRO (blog_banner.png)</option>
                                            <option value="blog_run.png">Vận Động Viên Chạy Bộ (blog_run.png)</option>
                                            <option value="trending-NIKE+WAFFLE+NAV.png">Sneaker Thể Thao Phối Đồ</option>
                                            <option value="trending-NIKE+AIR+MAX+PULSE.png">Nike Air Max Pulse</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-action-row">
                            <button type="submit" class="btn-submit-action" style="background: #2563eb;">
                                📢 Xuất Bản Bài Viết Ngay
                            </button>
                            <button type="reset" class="btn-cancel-action">
                                Làm Mới Form
                            </button>
                        </div>
                    </form>

                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Preview Tab 1: Product Image
        const fileInputProd = document.getElementById("image_file");
        const presetSelectProd = document.getElementById("preset_image");
        const previewImgProd = document.getElementById("preview-image-product");

        if (fileInputProd && previewImgProd) {
            fileInputProd.addEventListener("change", function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (evt) {
                        previewImgProd.src = evt.target.result;
                    };
                    reader.readAsDataURL(file);
                    if (presetSelectProd) presetSelectProd.value = "";
                }
            });
        }

        if (presetSelectProd && previewImgProd) {
            presetSelectProd.addEventListener("change", function () {
                if (this.value) {
                    previewImgProd.src = "/Zyro/assets/img/" + this.value;
                    if (fileInputProd) fileInputProd.value = "";
                }
            });
        }

        // Preview Tab 2: Article Image
        const fileInputArt = document.getElementById("article_image_file");
        const presetSelectArt = document.getElementById("preset_article_image");
        const previewImgArt = document.getElementById("preview-image-article");

        if (fileInputArt && previewImgArt) {
            fileInputArt.addEventListener("change", function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (evt) {
                        previewImgArt.src = evt.target.result;
                    };
                    reader.readAsDataURL(file);
                    if (presetSelectArt) presetSelectArt.value = "";
                }
            });
        }

        if (presetSelectArt && previewImgArt) {
            presetSelectArt.addEventListener("change", function () {
                if (this.value) {
                    previewImgArt.src = "/Zyro/assets/img/" + this.value;
                    if (fileInputArt) fileInputArt.value = "";
                }
            });
        }
    });
    </script>
</body>
</html>
