<?php
if (session_id() == '') {
    session_start();
}

// Hủy thông tin tài khoản
unset($_SESSION['user']);
unset($_SESSION['phanquyen']);

// Chuyển hướng về Trang chủ
header("Location: /Zyro/index.php?logout=1");
exit();
?>
