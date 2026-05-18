<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
<!-- Boostrap -->
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
<!-- Link style -->
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/tai-khoang.css">
    <script src="../assets/js/action.js"></script>
</head>
<body>
<!-- Content form dang nhap -->
    <div class="container" id="login">
        <div id="prev-home">
            <p>
                <a id="Logo-Zyro" href="../php/TrangChu.php">ZYRO</a>
                <a href="../php/TrangChu.php"><img src="../assets/img/Zyro-logo2.png" alt="Logo" id="logo2-zyro"></a>
            </p>
        </div>
        <form action="" id="frm-sig">
            <p id="head-form">ĐĂNG NHẬP</p>
            <div class="box-input">
                <input type="text" name="txtEmail" id="txtEmail" class="text-box-tk" placeholder="Email">
                <span class="lb-input">Email</span>
            </div>
            <div class="box-input">
                <input type="password" name="txtPass" id="txtPass" class="text-box-tk" placeholder="Mật khẩu">
                <span class="lb-input">Mật khẩu</span>
            </div>
<!-- Nhớ mật khẩu -->
            <div class="box-input sub-form">
                <input type="checkbox" name="txtNhoMatKhau" id="txtNhoMatKhau"> <span class="text-checkbox">Nhớ mật khẩu</span>
            </div>
<!-- Nút đăng nhập -->
            <div class="box-input" id="box-sb-dang-ky">
                <button id="Nut-xem-chi-tiet" type="submit" name="sbDangKy" id="sbDangKy">
                    Đăng nhập
                </button>
            </div>
        </form>
    </div>
    



</body>
</html>