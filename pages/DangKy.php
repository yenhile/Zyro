<?php
if (session_id() == '') {
    session_start();
}
require_once("../class/classProduct.php");

$productModel = new san_pham();

$redirect = isset($_REQUEST['redirect']) ? trim($_REQUEST['redirect']) : '';
if (empty($redirect) || strpos($redirect, 'http') === 0 || strpos($redirect, '//') === 0) {
    if (!empty($redirect) && strpos($redirect, '/Zyro') !== 0) {
        $redirect = '';
    }
}

// Nếu đã đăng nhập thì về trang chủ
if (isset($_SESSION['user'])) {
    header("Location: /Zyro/index.php");
    exit();
}

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho = isset($_POST['txtHo']) ? trim($_POST['txtHo']) : '';
    $ten = isset($_POST['txtTen']) ? trim($_POST['txtTen']) : '';
    $name = trim($ho . ' ' . $ten);
    $email = isset($_POST['txtEmail']) ? trim($_POST['txtEmail']) : '';
    $pass = isset($_POST['txtPass']) ? trim($_POST['txtPass']) : '';
    $pass2 = isset($_POST['txtPass2']) ? trim($_POST['txtPass2']) : '';
    $phone = isset($_POST['txtSDT']) ? trim($_POST['txtSDT']) : '';
    $address = isset($_POST['txtDiaChi']) ? trim($_POST['txtDiaChi']) : '';
    $agree = isset($_POST['txtDieuKhoang']);

    if (empty($name) || empty($email) || empty($pass) || empty($phone)) {
        $errorMessage = "Vui lòng điền đầy đủ các thông tin bắt buộc (*)!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Địa chỉ Email không đúng định dạng!";
    } elseif (strlen($pass) < 6) {
        $errorMessage = "Mật khẩu phải chứa ít nhất 6 ký tự!";
    } elseif ($pass !== $pass2) {
        $errorMessage = "Mật khẩu xác nhận không trùng khớp!";
    } elseif (!$agree) {
        $errorMessage = "Bạn cần đồng ý với Điều khoản và Chính sách bảo mật của ZYRO!";
    } else {
        $result = $productModel->dang_ky($name, $email, $pass, $phone, $address, 1);

        if ($result === 'email_exists') {
            $errorMessage = "Email này đã được sử dụng. Vui lòng chọn email khác hoặc Đăng nhập!";
        } elseif ($result) {
            // Tự động đăng nhập người dùng mới
            $newUser = $productModel->dang_nhap($email, $pass);
            if ($newUser) {
                $_SESSION['user'] = $newUser;
                $_SESSION['phanquyen'] = 1;
            }

            if (!empty($redirect)) {
                header("Location: " . $redirect);
            } else {
                header("Location: /Zyro/index.php?welcome=1");
            }
            exit();
        } else {
            $errorMessage = "Đã xảy ra lỗi khi tạo tài khoản. Vui lòng thử lại!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Tài Khoản — ZYRO Store</title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="/Zyro/assets/css/bootstrap.css">
    <script src="/Zyro/assets/js/bootstrap.bundle.min.js"></script>
    <!-- Link style -->
    <link rel="stylesheet" href="/Zyro/assets/css/layout.css">
    <link rel="stylesheet" href="/Zyro/assets/css/style.css">
    <link rel="stylesheet" href="/Zyro/assets/css/tai-khoang.css">
    <script src="/Zyro/assets/js/action.js"></script>
</head>
<body>
    <div class="container" id="sigup">
        <div id="prev-home">
            <p>
                <a id="Logo-Zyro" href="/Zyro/index.php">ZYRO</a>
                <a href="/Zyro/index.php"><img src="/Zyro/assets/img/Zyro-logo2.png" alt="Logo" id="logo2-zyro"></a>
            </p>
        </div>

        <form action="/Zyro/pages/DangKy.php" method="POST" id="frm-sig">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8'); ?>">
            
            <p id="head-form">ĐĂNG KÝ THÀNH VIÊN</p>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3" role="alert">
                    <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <!-- Thông tin tài khoản -->
            <p class="sub-form">1. Thông tin đăng nhập</p>
            <div class="box-input">
                <input type="email" name="txtEmail" id="txtEmail" class="text-box-tk" placeholder="Email (*)" required
                       value="<?php echo isset($_POST['txtEmail']) ? htmlspecialchars($_POST['txtEmail'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                <span class="lb-input">Email (*)</span>
            </div>

            <div class="box-input">
                <input type="password" name="txtPass" id="txtPass" class="text-box-tk" placeholder="Mật khẩu (tối thiểu 6 ký tự)" required>
                <span class="lb-input">Mật khẩu (*)</span>
            </div>

            <div class="box-input">
                <input type="password" name="txtPass2" id="txtPass2" class="text-box-tk" placeholder="Nhập lại mật khẩu" required>
                <span class="lb-input">Nhập lại mật khẩu (*)</span>
            </div>

            <!-- Thông tin cá nhân -->
            <p class="sub-form mt-3">2. Thông tin cá nhân</p>
            <nav class="d-flex gap-2">
                <div class="box-input-cn w-50">
                    <input type="text" name="txtHo" id="txtHo" class="text-box-cn" placeholder="Họ đệm" 
                           value="<?php echo isset($_POST['txtHo']) ? htmlspecialchars($_POST['txtHo'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    <span class="lb-input">Họ</span>
                </div>
                <div class="box-input-cn w-50">
                    <input type="text" name="txtTen" id="txtTen" class="text-box-cn" placeholder="Tên (*)" required
                           value="<?php echo isset($_POST['txtTen']) ? htmlspecialchars($_POST['txtTen'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    <span class="lb-input">Tên (*)</span>
                </div>
            </nav>

            <div class="box-input mt-2">
                <input type="tel" name="txtSDT" id="txtSDT" class="text-box-tk" placeholder="Số điện thoại (*)" required
                       value="<?php echo isset($_POST['txtSDT']) ? htmlspecialchars($_POST['txtSDT'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                <span class="lb-input">Số điện thoại (*)</span>
            </div>

            <div class="box-input">
                <input type="text" name="txtDiaChi" id="txtDiaChi" class="text-box-tk" placeholder="Địa chỉ giao hàng (Tỉnh/Thành, Quận/Huyện...)"
                       value="<?php echo isset($_POST['txtDiaChi']) ? htmlspecialchars($_POST['txtDiaChi'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                <span class="lb-input">Địa chỉ</span>
            </div>

            <!-- Check điều khoản -->
            <div class="box-input sub-form d-flex align-items-center gap-2 mb-2" style="height: auto;">
                <input type="checkbox" name="txtDieuKhoang" id="txtDieuKhoang" checked required> 
                <span class="text-checkbox small">Tôi đồng ý với Chính sách bảo mật và Điều khoản mua hàng tại ZYRO</span>
            </div>

            <!-- Nút đăng ký -->
            <div class="mb-4">
                <button id="Nut-xem-chi-tiet" type="submit" name="sbDangKy" class="w-100" style="padding: 14px; font-size: 16px;">
                    Đăng Ký Tài Khoản
                </button>
            </div>

            <!-- Link đăng nhập -->
            <div class="text-center small text-muted">
                Đã có tài khoản ZYRO? 
                <a href="/Zyro/pages/DangNhap.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>" class="text-dark fw-bold text-decoration-underline">
                    Đăng nhập ngay
                </a>
            </div>
        </form>
    </div>
</body>
</html>