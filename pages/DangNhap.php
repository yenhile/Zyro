<?php
if (session_id() == '') {
    session_start();
}
require_once("../class/classProduct.php");

$productModel = new san_pham();

$redirect = isset($_REQUEST['redirect']) ? trim($_REQUEST['redirect']) : '';
if (empty($redirect) || strpos($redirect, 'http') === 0 || strpos($redirect, '//') === 0) {
    // Chỉ cho phép relative path nội bộ để chống Open Redirect
    if (!empty($redirect) && strpos($redirect, '/Zyro') !== 0) {
        $redirect = '';
    }
}

// Nếu đã đăng nhập thì chuyển hướng luôn
if (isset($_SESSION['user'])) {
    if (!empty($redirect)) {
        header("Location: " . $redirect);
    } elseif (isset($_SESSION['user']['phanquyen']) && $_SESSION['user']['phanquyen'] == 0) {
        header("Location: /Zyro/admin/index.php");
    } else {
        header("Location: /Zyro/index.php");
    }
    exit();
}

$errorMessage = '';
$successMessage = '';

if (isset($_GET['registered'])) {
    $successMessage = "Đăng ký tài khoản thành công! Vui lòng đăng nhập.";
}
if (isset($_GET['error']) && $_GET['error'] === 'admin_required') {
    $errorMessage = "Trang Quản Trị yêu cầu quyền Admin. Vui lòng đăng nhập bằng tài khoản Quản trị viên!";
}
if (isset($_GET['error']) && $_GET['error'] === 'login_required') {
    $errorMessage = "Vui lòng đăng nhập để tiếp tục sử dụng tính năng này!";
}

// Xử lý gửi form đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['txtEmail']) ? trim($_POST['txtEmail']) : '';
    $pass = isset($_POST['txtPass']) ? trim($_POST['txtPass']) : '';

    if (empty($email) || empty($pass)) {
        $errorMessage = "Vui lòng nhập đầy đủ Email và Mật khẩu!";
    } else {
        $user = $productModel->dang_nhap($email, $pass);
        if ($user) {
            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = (int)$user['user_id'];
            $_SESSION['phanquyen'] = (int)$user['phanquyen'];

            // Điều hướng
            if (!empty($redirect)) {
                header("Location: " . $redirect);
            } elseif ($user['phanquyen'] == 0) {
                header("Location: /Zyro/admin/index.php");
            } else {
                header("Location: /Zyro/index.php");
            }
            exit();
        } else {
            $errorMessage = "Email hoặc Mật khẩu không chính xác. Vui lòng thử lại!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập — ZYRO Store</title>
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
    <div class="container" id="login">
        <div id="prev-home">
            <p>
                <a id="Logo-Zyro" href="/Zyro/index.php">ZYRO</a>
                <a href="/Zyro/index.php"><img src="/Zyro/assets/img/Zyro-logo2.png" alt="Logo" id="logo2-zyro"></a>
            </p>
        </div>

        <form action="/Zyro/pages/DangNhap.php" method="POST" id="frm-sig">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8'); ?>">
            
            <p id="head-form">ĐĂNG NHẬP</p>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3" role="alert">
                    <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success py-2 px-3 small rounded-3 mb-3" role="alert">
                    <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <div class="box-input">
                <input type="email" name="txtEmail" id="txtEmail" class="text-box-tk" placeholder="Email" required 
                       value="<?php echo isset($_POST['txtEmail']) ? htmlspecialchars($_POST['txtEmail'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                <span class="lb-input">Email</span>
            </div>

            <div class="box-input">
                <input type="password" name="txtPass" id="txtPass" class="text-box-tk" placeholder="Mật khẩu" required>
                <span class="lb-input">Mật khẩu</span>
            </div>

            <!-- Nhớ mật khẩu & Quên mật khẩu -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <label class="d-flex align-items-center gap-2 small text-muted" style="cursor: pointer;">
                    <input type="checkbox" name="txtNhoMatKhau" id="txtNhoMatKhau"> 
                    <span>Nhớ mật khẩu</span>
                </label>
                <a href="#" class="small text-muted text-decoration-none">Quên mật khẩu?</a>
            </div>

            <!-- Nút đăng nhập -->
            <div class="mb-4">
                <button id="Nut-xem-chi-tiet" type="submit" name="btnDangNhap" class="w-100" style="padding: 14px; font-size: 16px;">
                    Đăng nhập
                </button>
            </div>

            <!-- Link đăng ký -->
            <div class="text-center small text-muted">
                Chưa có tài khoản ZYRO? 
                <a href="/Zyro/pages/DangKy.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>" class="text-dark fw-bold text-decoration-underline">
                    Đăng ký ngay
                </a>
            </div>
        </form>
    </div>
</body>
</html>