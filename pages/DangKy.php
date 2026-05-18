<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoảng</title>
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
<!-- Content form đăng ký -->
    <div class="container" id="sigup">
        <div id="prev-home">
            <p>
                <a id="Logo-Zyro" href="../php/TrangChu.php">ZYRO</a>
                <a href="../php/TrangChu.php"><img src="../assets/img/Zyro-logo2.png" alt="Logo" id="logo2-zyro"></a>
            </p>
        </div>
        <form action="" id="frm-sig">
            <p id="head-form">ĐĂNG KÝ</p>
<!-- Thông tin tài khoảng -->
            <p class="sub-form">Thông tin tài khoảng</p>
            <div class="box-input">
                <input type="text" name="txtEmail" id="txtEmail" class="text-box-tk" placeholder="Email">
                <span class="lb-input">Email</span>
            </div>
            <div class="box-input">
                <input type="password" name="txtPass" id="txtPass" class="text-box-tk" placeholder="Mật khẩu">
                <span class="lb-input">Mật khẩu</span>
            </div>
            <div class="box-input">
                <input type="password" name="txtPass2" id="txtPass2" class="text-box-tk" placeholder="Nhập lại mật khẩu">
                <span class="lb-input">Nhập lại mật khẩu</span>
            </div>
<!-- Thông tin cá nhân -->
            <p class="sub-form">Thông tin cá nhân</p>
            <nav>
                <div class="box-input-cn">
                    <input type="text" name="txtHo" id="txtHo" class="text-box-cn" placeholder="Họ">
                    <span class="lb-input">Họ</span>
                </div>
                <div class="box-input-cn">
                    <input type="text" name="txtTen" id="txtTen" class="text-box-cn" placeholder="Tên">
                    <span class="lb-input">Tên</span>
                </div>
            </nav>

            <nav>
                <div class="box-input-cn" id="box-se-gt">
                    <select name="txtGioiTinh" id="txtGioiTinh">
                        <option value="gt">Giới tính</option>
                        <option value="Nam">Nam</option>
                        <option value="Nữ">Nữ</option>
                    </select>
                    <span class="lb-input" id="lb-input-gt">Giới tính</span>
                </div>
                <div class="box-input-cn" id="box-in-sdt">
                    <input type="text" name="txtSDT" id="txtSDT" class="text-box-cn" placeholder="Số điện thoại">
                    <span class="lb-input">Số điện thoại</span>
                </div>
            </nav>
            
            <p class="sub-form">Sinh nhật</p>
            <nav>
                <div class="box-input-ngay-thang">
                    <input type="text" name="txtDate-birth" id="txtDate-birth" class="text-box-cn" placeholder="Ngày">
                    <span class="lb-input">Ngày</span>
                </div>
                <div class="box-input-ngay-thang">
                    <input type="text" name="txtMonth-birth" id="txtMonth-birth" class="text-box-cn" placeholder="Tháng">
                    <span class="lb-input">Tháng</span>
                </div>
                <div class="box-input-nam">
                    <input type="text" name="txtYear-birth" id="txtYear-birth" class="text-box-cn" placeholder="Năm">
                    <span class="lb-input">Năm</span>
                </div>
            </nav>
<!-- Check điều khoảng -->
            <div class="box-input sub-form">
                <input type="checkbox" name="txtDieuKhoang" id="txtDieuKhoang"> <span class="text-checkbox">Tôi đồng ý với <a href="#"><u>Chính sách bảo mật</u></a> và <a href="#"><u>Điều khoảng của Zyro</u></a></span>
            </div>
<!-- Nút đăng ký -->
            <div class="box-input" id="box-sb-dang-ky">
                <button id="Nut-xem-chi-tiet" type="submit" name="sbDangKy" id="sbDangKy">
                    Đăng ký
                </button>
            </div>
        </form>
    </div>
    



</body>
</html>