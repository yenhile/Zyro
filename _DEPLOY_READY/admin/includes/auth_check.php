<?php
if (session_id() == '') {
    session_start();
}

// Kiểm tra đăng nhập và phân quyền Quản trị viên (phanquyen == 0)
$isLoggedIn = isset($_SESSION['user']);
$isAdmin = $isLoggedIn && isset($_SESSION['user']['phanquyen']) && (int)$_SESSION['user']['phanquyen'] === 0;

if (!$isLoggedIn || !$isAdmin) {
    // Điều hướng về trang Đăng nhập kèm thông báo lỗi và lưu URL đích
    $currentUri = $_SERVER['REQUEST_URI'];
    header("Location: /Zyro/pages/DangNhap.php?error=admin_required&redirect=" . urlencode($currentUri));
    exit();
}

// Lưu thông tin Admin hiện tại để dùng trong view
$adminUser = $_SESSION['user'];
?>
