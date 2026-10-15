<link rel="stylesheet" href="/Zyro/admin/css/header.css">
<link rel="stylesheet" href="/Zyro/font/bootstrap-icons.min.css">

<header>
    <div class="menu-main">
        <ul class="menu-left">
            <li><button type="button" id="btn-sidebar"><i class="bi bi-list"></i></button></li>
            <li><a href="/Zyro/admin/index.php">Dashboard</a></li>
            <li><a href="/Zyro/index.php" target="_blank">Xem Website ZYRO ↗</a></li>
        </ul>
        <ul class="menu-right">
            <li><span style="font-weight: 600; font-size: 13px; margin-right: 12px;">👤 <?php echo isset($adminUser['name']) ? htmlspecialchars($adminUser['name'], ENT_QUOTES, 'UTF-8') : 'Admin'; ?></span></li>
            <li><a href="/Zyro/pages/DangXuat.php" title="Đăng xuất" style="color: #dc3545; font-weight: bold; text-decoration: none;">Đăng xuất</a></li>
        </ul>
    </div>
</header>