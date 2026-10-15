<?php
require_once('../includes/auth_check.php');
require_once('../../class/classProduct.php');

$productModel = new san_pham();
$successMessage = '';
$errorMessage = '';

// Xử lý Cập nhật giá sản phẩm (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_price') {
    $editId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $rawPrice = isset($_POST['new_price']) ? trim($_POST['new_price']) : '';
    // Lọc bỏ dấu chấm, phẩy, chữ đ, khoảng trắng
    $cleanPrice = preg_replace('/[^0-9]/', '', $rawPrice);
    $newPrice = (float)$cleanPrice;

    if ($editId > 0 && $newPrice >= 0) {
        if ($productModel->cap_nhat_gia_san_pham($editId, $newPrice)) {
            $formattedPrice = number_format($newPrice, 0, ',', '.');
            $successMessage = "Đã cập nhật đơn giá sản phẩm #$editId thành công! Giá mới: {$formattedPrice}₫";
        } else {
            $errorMessage = "Không thể cập nhật giá sản phẩm #$editId. Vui lòng thử lại!";
        }
    } else {
        $errorMessage = "Đơn giá không hợp lệ. Vui lòng nhập số tiền lớn hơn 0!";
    }
}

// Xử lý Xóa sản phẩm (GET)
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
    <style>
        .btn-edit-sm {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            background: #FFF4E6;
            color: #D9480F;
            border: 1px solid #FFE8CC;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            margin: 0 2px;
        }
        .btn-edit-sm:hover {
            background: #FD7E14;
            color: #FFFFFF;
            border-color: #FD7E14;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(253, 126, 20, 0.25);
        }

        .btn-detail-sm {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            background: #E7F5FF;
            color: #1971C2;
            border: 1px solid #D0EBFF;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            margin: 0 2px;
        }
        .btn-detail-sm:hover {
            background: #228BE6;
            color: #FFFFFF;
            border-color: #228BE6;
            transform: translateY(-1px);
        }

        /* Modal Sửa Giá Nhanh */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            padding: 20px;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal-box {
            background: #FFFFFF;
            width: 100%;
            max-width: 480px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            animation: modalFadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.92) translateY(10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        .modal-header {
            padding: 18px 24px;
            border-bottom: 1px solid #F1F3F5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #FAFAFA;
        }
        .modal-title {
            font-size: 17px;
            font-weight: 700;
            color: #111111;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .modal-close-btn {
            background: transparent;
            border: none;
            font-size: 20px;
            line-height: 1;
            cursor: pointer;
            color: #868E96;
            padding: 4px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .modal-close-btn:hover {
            color: #111;
            background: #E9ECEF;
        }
        .modal-body {
            padding: 24px;
        }
        .product-preview-card {
            display: flex;
            gap: 14px;
            background: #F8F9FA;
            border: 1px solid #E9ECEF;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 20px;
            align-items: center;
        }
        .product-preview-img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            background: #FFFFFF;
            border: 1px solid #DEE2E6;
        }
        .product-preview-info h4 {
            margin: 0 0 4px 0;
            font-size: 14px;
            font-weight: 700;
            color: #212529;
        }
        .product-preview-info p {
            margin: 0;
            font-size: 13px;
            color: #6C757D;
        }
        .form-group-modal {
            margin-bottom: 18px;
        }
        .form-label-modal {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #343A40;
            margin-bottom: 8px;
        }
        .input-price-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-price-modal {
            width: 100%;
            padding: 12px 48px 12px 16px;
            font-size: 18px;
            font-weight: 700;
            color: #D9480F;
            border: 2px solid #CED4DA;
            border-radius: 10px;
            transition: all 0.2s;
            outline: none;
            box-sizing: border-box;
        }
        .input-price-modal:focus {
            border-color: #FD7E14;
            box-shadow: 0 0 0 4px rgba(253, 126, 20, 0.15);
        }
        .input-price-unit {
            position: absolute;
            right: 16px;
            font-weight: 700;
            color: #868E96;
            font-size: 16px;
            pointer-events: none;
        }
        .price-quick-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 10px;
        }
        .price-quick-btn {
            background: #F1F3F5;
            border: 1px solid #E9ECEF;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            color: #495057;
            cursor: pointer;
            transition: all 0.2s;
        }
        .price-quick-btn:hover {
            background: #FFE8CC;
            color: #D9480F;
            border-color: #FFD8A8;
        }
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #F1F3F5;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            background: #FAFAFA;
        }
        .btn-modal-cancel {
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            background: #E9ECEF;
            color: #495057;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-modal-cancel:hover {
            background: #DEE2E6;
        }
        .btn-modal-save {
            padding: 10px 22px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            background: #FD7E14;
            color: #FFFFFF;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-modal-save:hover {
            background: #E8590C;
            box-shadow: 0 4px 12px rgba(232, 89, 12, 0.3);
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
                                    <th style="width: 55px;">ID</th>
                                    <th style="width: 75px;">Hình Ảnh</th>
                                    <th>Tên Giày Thể Thao</th>
                                    <th>Thương Hiệu</th>
                                    <th>Phân Loại</th>
                                    <th>Đơn Giá</th>
                                    <th>Ngày Tạo</th>
                                    <th style="text-align: right; width: 230px;">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($products)): ?>
                                    <?php foreach ($products as $row): 
                                        $id = (int)$row['product_id'];
                                        $name = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                                        $brand = htmlspecialchars($row['brand_name'] ? $row['brand_name'] : 'Khác', ENT_QUOTES, 'UTF-8');
                                        $category = htmlspecialchars($row['category_name'] ? $row['category_name'] : 'Khác', ENT_QUOTES, 'UTF-8');
                                        $rawPriceNum = (float)$row['price'];
                                        $price = number_format($rawPriceNum, 0, ',', '.');
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
                                            <td><strong style="color: #D9480F; font-size: 15px;"><?php echo $price; ?>₫</strong></td>
                                            <td style="color: #6C757D; font-size: 13px;"><?php echo $date; ?></td>
                                            <td style="text-align: right; white-space: nowrap;">
                                                <button type="button" class="btn-edit-sm" title="Sửa giá sản phẩm ngay lập tức"
                                                        onclick="openEditPriceModal(<?php echo $id; ?>, '<?php echo addslashes($row['name']); ?>', <?php echo $rawPriceNum; ?>, '<?php echo addslashes($img); ?>')">
                                                    ✏️ Sửa giá
                                                </button>
                                                <a href="/Zyro/admin/pages/SuaSanPham.php?id=<?php echo $id; ?>" class="btn-detail-sm" title="Chỉnh sửa chi tiết thông tin">
                                                    ⚙️ Sửa
                                                </a>
                                                <a href="/Zyro/pages/ChiTietSanPham.php?id=<?php echo $id; ?>" target="_blank" class="btn-view-sm" title="Xem trên website bán hàng">
                                                    👁️ Xem
                                                </a>
                                                <a href="/Zyro/admin/pages/QuanLySanPham.php?action=delete&id=<?php echo $id; ?>" 
                                                   class="btn-del-sm" 
                                                   onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm: <?php echo addslashes($name); ?> (#<?php echo $id; ?>)?');"
                                                   title="Xóa sản phẩm">
                                                    🗑️
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

    <!-- MODAL SỬA GIÁ NHANH -->
    <div id="modalEditPrice" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">✏️ Chỉnh Sửa Đơn Giá Sản Phẩm</h3>
                <button type="button" class="modal-close-btn" onclick="closeEditPriceModal()">&times;</button>
            </div>
            <form method="POST" action="/Zyro/admin/pages/QuanLySanPham.php">
                <input type="hidden" name="action" value="update_price">
                <input type="hidden" id="modalProductId" name="product_id" value="">
                
                <div class="modal-body">
                    <div class="product-preview-card">
                        <img id="modalProductImg" class="product-preview-img" src="" alt="Thumbnail">
                        <div class="product-preview-info">
                            <h4 id="modalProductName">Tên sản phẩm</h4>
                            <p>Mã sản phẩm: <strong id="modalProductCode">#--</strong> | Giá hiện tại: <span id="modalCurrentPrice" style="color: #D9480F; font-weight: 700;">--</span></p>
                        </div>
                    </div>

                    <div class="form-group-modal">
                        <label for="modalInputPrice" class="form-label-modal">Nhập Đơn Giá Mới (VNĐ):</label>
                        <div class="input-price-wrapper">
                            <input type="number" id="modalInputPrice" name="new_price" class="input-price-modal" min="0" step="1000" required>
                            <span class="input-price-unit">VNĐ</span>
                        </div>
                        <div class="price-quick-tags">
                            <span style="font-size: 11px; color: #868E96; align-self: center; margin-right: 4px;">Gợi ý nhanh:</span>
                            <button type="button" class="price-quick-btn" onclick="adjustModalPrice(100000)">+100.000đ</button>
                            <button type="button" class="price-quick-btn" onclick="adjustModalPrice(200000)">+200.000đ</button>
                            <button type="button" class="price-quick-btn" onclick="adjustModalPrice(500000)">+500.000đ</button>
                            <button type="button" class="price-quick-btn" onclick="adjustModalPrice(-100000)">-100.000đ</button>
                            <button type="button" class="price-quick-btn" onclick="roundModalPrice()">Làm tròn số</button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="closeEditPriceModal()">Hủy</button>
                    <button type="submit" class="btn-modal-save">
                        💾 Lưu Giá Mới
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="/Zyro/admin/js/sidebar.js"></script>
    <script>
        function openEditPriceModal(id, name, price, img) {
            document.getElementById('modalProductId').value = id;
            document.getElementById('modalProductName').textContent = name;
            document.getElementById('modalProductCode').textContent = '#' + id;
            document.getElementById('modalProductImg').src = '/Zyro/assets/img/' + img;
            document.getElementById('modalCurrentPrice').textContent = Number(price).toLocaleString('vi-VN') + '₫';
            document.getElementById('modalInputPrice').value = price;

            var modal = document.getElementById('modalEditPrice');
            modal.classList.add('active');
            setTimeout(function() {
                var inp = document.getElementById('modalInputPrice');
                inp.focus();
                inp.select();
            }, 100);
        }

        function closeEditPriceModal() {
            var modal = document.getElementById('modalEditPrice');
            modal.classList.remove('active');
        }

        function adjustModalPrice(amount) {
            var inp = document.getElementById('modalInputPrice');
            var cur = parseFloat(inp.value) || 0;
            var next = Math.max(0, cur + amount);
            inp.value = next;
        }

        function roundModalPrice() {
            var inp = document.getElementById('modalInputPrice');
            var cur = parseFloat(inp.value) || 0;
            inp.value = Math.round(cur / 10000) * 10000;
        }

        // Đóng modal khi bấm ra ngoài vùng xám
        document.getElementById('modalEditPrice').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditPriceModal();
            }
        });

        // Đóng modal khi bấm Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditPriceModal();
            }
        });
    </script>
</body>
</html>
