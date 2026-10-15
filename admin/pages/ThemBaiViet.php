<?php
require_once('../includes/auth_check.php');
require_once('../../class/classProduct.php');

$productModel = new san_pham();

$successMessage = '';
$errorMessage = '';
$newPostId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $summary = isset($_POST['summary']) ? trim($_POST['summary']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $authorId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

    if (empty($title) || empty($summary) || empty($content)) {
        $errorMessage = "Vui lòng điền đầy đủ tiêu đề, tóm tắt và nội dung bài viết!";
    } else {
        $imageUrl = 'blog_banner.png'; // Ảnh mặc định

        // Xử lý upload ảnh
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['image_file']['tmp_name'];
            $fileName = $_FILES['image_file']['name'];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = array('jpg', 'jpeg', 'png', 'webp');

            if (!in_array($ext, $allowedExts)) {
                $errorMessage = "Định dạng ảnh không hợp lệ! Chỉ chấp nhận file JPG, PNG hoặc WEBP.";
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
                    $errorMessage = "Không thể lưu file ảnh vào máy chủ. Vui lòng kiểm tra quyền thư mục!";
                }
            }
        } elseif (isset($_POST['preset_image']) && !empty($_POST['preset_image'])) {
            $imageUrl = trim($_POST['preset_image']);
        }

        if (empty($errorMessage)) {
            $newPostId = $productModel->them_bai_viet($title, $summary, $content, $imageUrl, $authorId);
            if ($newPostId) {
                $successMessage = "Đăng bài viết mới thành công! Mã bài viết: #$newPostId";
            } else {
                $errorMessage = "Đã xảy ra lỗi khi lưu bài viết vào CSDL MySQL!";
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
    <title>Đăng Bài Viết Mới - ZYRO Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin-style.css">
    <style>
        .image-preview-box {
            width: 100%;
            height: 220px;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #f8fafc;
            position: relative;
        }
        .image-preview-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar Navigation -->
        <?php include('../includes/sidebar.php'); ?>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Top Header -->
            <header class="admin-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h4 mb-0 fw-bold text-dark">
                        <i class="fas fa-newspaper text-primary me-2"></i>Đăng Bài Viết & Cẩm Nang Mới
                    </h1>
                    <small class="text-muted">Xuất bản bài viết hướng dẫn, đánh giá giày và tin tức thời trang ZYRO</small>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <a href="../../pages/GioiThieu.php" target="_blank" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                        <i class="fas fa-external-link-alt me-1"></i>Xem Trang Giới Thiệu
                    </a>
                    <div class="vr"></div>
                    <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-semibold">
                        <i class="fas fa-user-shield me-1"></i>Admin: <?php echo htmlspecialchars($_SESSION['user']); ?>
                    </span>
                </div>
            </header>

            <div class="admin-content">
                <!-- Notifications -->
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-3" role="alert">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                        <div class="flex-grow-1">
                            <strong>Thành công!</strong> <?php echo htmlspecialchars($successMessage); ?>
                            <?php if ($newPostId): ?>
                                <div class="mt-2">
                                    <a href="../../pages/GioiThieu.php?post_id=<?php echo $newPostId; ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-3">
                                        <i class="fas fa-eye me-1"></i>Xem bài viết vừa đăng trên website
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-3" role="alert">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                        <div>
                            <strong>Không thể hoàn tất:</strong> <?php echo htmlspecialchars($errorMessage); ?>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Form Card -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3 border-light d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark"><i class="fas fa-pen-fancy text-primary me-2"></i>Nội dung bài viết mới</span>
                        <span class="text-danger small">* Các trường bắt buộc nhập</span>
                    </div>
                    <div class="card-body p-4">
                        <form action="ThemBaiViet.php" method="POST" enctype="multipart/form-data" id="postArticleForm">
                            <div class="row g-4">
                                <!-- Cột Trái: Tiêu đề, Tóm tắt, Nội dung -->
                                <div class="col-lg-8">
                                    <div class="mb-3">
                                        <label for="postTitle" class="form-label fw-semibold">
                                            Tiêu đề bài viết <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-lg rounded-3" id="postTitle" name="title" 
                                               placeholder="Ví dụ: Top 5 đôi giày chạy bộ đáng mua nhất 2026" required
                                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="postSummary" class="form-label fw-semibold">
                                            Đoạn trích tóm tắt <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control rounded-3" id="postSummary" name="summary" rows="3" 
                                                  placeholder="Tóm tắt ngắn gọn nội dung cốt lõi của bài viết (khoảng 2-3 câu)..." required><?php echo isset($_POST['summary']) ? htmlspecialchars($_POST['summary']) : ''; ?></textarea>
                                        <small class="text-muted">Đoạn tóm tắt này sẽ hiển thị ở danh thiếp bài viết ngoài trang chủ và trang giới thiệu.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="postContent" class="form-label fw-semibold">
                                            Nội dung chi tiết bài viết <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control rounded-3" id="postContent" name="content" rows="12" 
                                                  placeholder="Nhập toàn văn bài viết, cẩm nang thể thao hoặc thông tin khuyến mãi..." required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                                    </div>
                                </div>

                                <!-- Cột Phải: Hình ảnh đại diện & Banner -->
                                <div class="col-lg-4">
                                    <div class="card bg-light border-0 rounded-4 p-3 mb-4">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-image text-primary me-1"></i>Ảnh bìa bài viết
                                        </label>

                                        <!-- Preview Box -->
                                        <div class="image-preview-box mb-3" id="previewContainer">
                                            <div id="previewPlaceholder" class="text-center p-3">
                                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                                <p class="small text-muted mb-0">Tải ảnh bìa bài viết (JPG, PNG, WEBP)</p>
                                            </div>
                                            <img id="imagePreview" alt="Xem trước ảnh bìa">
                                        </div>

                                        <!-- File Upload Input -->
                                        <div class="mb-3">
                                            <label for="image_file" class="form-label small fw-semibold">Chọn file ảnh từ máy tính:</label>
                                            <input class="form-control form-control-sm" type="file" id="image_file" name="image_file" accept="image/png, image/jpeg, image/webp">
                                        </div>

                                        <div class="text-center my-2">
                                            <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill">HOẶC DÙNG ẢNH MẪU CÓ SẴN</span>
                                        </div>

                                        <!-- Preset Select -->
                                        <div class="mt-2">
                                            <select class="form-select form-select-sm" name="preset_image" id="presetSelect">
                                                <option value="">-- Chọn ảnh mẫu có sẵn --</option>
                                                <option value="blog_banner.png">Ảnh bìa ZYRO Brand (blog_banner.png)</option>
                                                <option value="blog_run.png">Vận động viên chạy bộ (blog_run.png)</option>
                                                <option value="trending-NIKE+WAFFLE+NAV.png">Sneaker thể thao cá tính</option>
                                                <option value="trending-NIKE+AIR+MAX+PULSE.png">Nike Air Max Pulse High-tech</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Submit Box -->
                                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold w-100 mb-2">
                                            <i class="fas fa-paper-plane me-2"></i>Xuất Bản Bài Viết
                                        </button>
                                        <button type="reset" class="btn btn-outline-secondary rounded-pill w-100">
                                            <i class="fas fa-redo me-1"></i>Làm lại form
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const imageFileInput = document.getElementById('image_file');
        const presetSelect = document.getElementById('presetSelect');
        const imagePreview = document.getElementById('imagePreview');
        const previewPlaceholder = document.getElementById('previewPlaceholder');

        // Xem trước khi chọn file upload
        imageFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    imagePreview.src = event.target.result;
                    imagePreview.style.display = 'block';
                    previewPlaceholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
                presetSelect.value = '';
            }
        });

        // Xem trước khi chọn preset
        presetSelect.addEventListener('change', function(e) {
            const val = e.target.value;
            if (val) {
                imagePreview.src = '../../assets/img/' + val;
                imagePreview.style.display = 'block';
                previewPlaceholder.style.display = 'none';
                imageFileInput.value = '';
            } else if (!imageFileInput.files.length) {
                imagePreview.style.display = 'none';
                previewPlaceholder.style.display = 'block';
            }
        });
    </script>
</body>
</html>
