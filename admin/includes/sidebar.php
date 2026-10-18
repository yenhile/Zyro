<?php
$currentUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
?>
<!-- Tự động nạp CSS với cache busting -->
<link rel="stylesheet" href="/Zyro/admin/css/sidebar.css?v=<?php echo time(); ?>">

<!-- Khóa cứng CSS tại chỗ trong sidebar để chống cache và đảm bảo 100% chữ trắng, không bị co ép, không có chấm tròn -->
<style>
#sidebar {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    bottom: 0 !important;
    width: 250px !important;
    min-width: 250px !important;
    max-width: 250px !important;
    flex: 0 0 250px !important;
    flex-shrink: 0 !important;
    background-color: #0f172a !important;
    color: #ffffff !important;
    height: 100vh !important;
    min-height: 100vh !important;
    z-index: 1050 !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    box-shadow: 3px 0 16px rgba(0, 0, 0, 0.15) !important;
    transition: transform 0.3s ease !important;
    box-sizing: border-box !important;
}

#sidebar.collapsed {
    transform: translateX(-100%) !important;
    visibility: hidden !important;
    width: 0px !important;
    min-width: 0px !important;
    max-width: 0px !important;
}

#container {
    margin-left: 250px !important;
    width: calc(100% - 250px) !important;
    max-width: calc(100% - 250px) !important;
    flex: 1 1 0% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
    transition: margin-left 0.3s ease, width 0.3s ease !important;
}

#sidebar.collapsed ~ #container {
    margin-left: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
}

#sidebar .sidebar-brand {
    width: 100% !important;
    padding: 20px 14px !important;
    text-align: center !important;
    background: #020617 !important;
    border-bottom: 1px solid #1e293b !important;
    box-sizing: border-box !important;
}

#sidebar #Logo-Zyro {
    color: #ffffff !important;
    font-size: 19px !important;
    font-weight: 800 !important;
    letter-spacing: 1.5px !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
}

#sidebar #Logo-Zyro span.badge-tag {
    font-size: 10px !important;
    font-weight: 700 !important;
    background: #dc2626 !important;
    color: #ffffff !important;
    padding: 2px 6px !important;
    border-radius: 4px !important;
    letter-spacing: 0.5px !important;
}

#sidebar .sidebar-menu {
    padding: 12px 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

#sidebar .sidebar-menu ul {
    list-style: none !important;
    list-style-type: none !important;
    padding: 0 10px !important;
    margin: 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

#sidebar .sidebar-menu li {
    list-style: none !important;
    list-style-type: none !important;
    margin-bottom: 6px !important;
    padding: 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

#sidebar .sidebar-menu li::before,
#sidebar .sidebar-menu li::marker {
    display: none !important;
    content: "" !important;
}

#sidebar .sidebar-nav-link {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 12px 14px !important;
    color: #ffffff !important; /* MÀU TRẮNG SÁNG RÕ */
    font-weight: 600 !important;
    font-size: 14px !important;
    border-radius: 8px !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    transition: all 0.2s ease !important;
    background: transparent !important;
    box-sizing: border-box !important;
}

#sidebar .sidebar-nav-link span {
    color: #ffffff !important; /* TOÀN BỘ CHỮ ĐỀU MÀU TRẮNG DỄ NHÌN */
}

#sidebar .sidebar-nav-link .nav-icon {
    font-size: 16px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 22px !important;
}

#sidebar .sidebar-nav-link .nav-text {
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    color: #ffffff !important;
}

#sidebar .sidebar-nav-link:hover {
    color: #ffffff !important;
    background: #1e293b !important;
    transform: translateX(4px) !important;
}

#sidebar .sidebar-nav-link:hover span {
    color: #ffffff !important;
}

#sidebar .sidebar-nav-link.active {
    color: #ffffff !important;
    background: #0284c7 !important; /* Xanh nổi bật menu đang mở */
    font-weight: 700 !important;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4) !important;
}

#sidebar .sidebar-nav-link.active span {
    color: #ffffff !important;
}

#sidebar .sidebar-divider {
    height: 1px !important;
    background-color: #334155 !important;
    margin: 16px 4px !important;
    list-style: none !important;
}

#sidebar .sidebar-nav-link.external-link {
    color: #38bdf8 !important;
}
#sidebar .sidebar-nav-link.external-link span {
    color: #38bdf8 !important;
}
#sidebar .sidebar-nav-link.external-link:hover {
    background: rgba(56, 189, 248, 0.15) !important;
}

#sidebar .sidebar-nav-link.logout-link {
    color: #f87171 !important;
}
#sidebar .sidebar-nav-link.logout-link span {
    color: #f87171 !important;
}
#sidebar .sidebar-nav-link.logout-link:hover {
    background: rgba(248, 113, 113, 0.15) !important;
}
</style>

<div id="sidebar">
    <div class="sidebar-brand">
        <a class="navbar-brand" id="Logo-Zyro" href="/Zyro/admin/index.php">
            <span style="color: #ffffff !important;">ZYRO</span>
            <span class="badge-tag">ADMIN</span>
        </a>
    </div>
    <div class="sidebar-menu">
        <ul style="list-style: none !important; padding: 0 10px !important; margin: 0 !important;">
            <li style="list-style: none !important;">
                <a href="/Zyro/admin/index.php" class="sidebar-nav-link <?php echo (strpos($currentUri, 'admin/index.php') !== false || substr($currentUri, -6) === '/admin' || substr($currentUri, -7) === '/admin/') ? 'active' : ''; ?>" style="color: #ffffff !important;">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text" style="color: #ffffff !important;">Bảng Điều Khiển</span>
                </a>
            </li>
            <li style="list-style: none !important;">
                <a href="/Zyro/admin/pages/QuanLySanPham.php" class="sidebar-nav-link <?php echo (strpos($currentUri, 'QuanLySanPham.php') !== false || strpos($currentUri, 'SuaSanPham.php') !== false) ? 'active' : ''; ?>" style="color: #ffffff !important;">
                    <span class="nav-icon">👟</span>
                    <span class="nav-text" style="color: #ffffff !important;">Quản Lý Sản Phẩm</span>
                </a>
            </li>
            <li style="list-style: none !important;">
                <a href="/Zyro/admin/pages/QuanLyDonHang.php" class="sidebar-nav-link <?php echo (strpos($currentUri, 'QuanLyDonHang.php') !== false) ? 'active' : ''; ?>" style="color: #ffffff !important;">
                    <span class="nav-icon">📦</span>
                    <span class="nav-text" style="color: #ffffff !important;">Quản Lý Đơn Hàng</span>
                </a>
            </li>
            <li style="list-style: none !important;">
                <a href="/Zyro/admin/pages/ThongKeDonHang.php" class="sidebar-nav-link <?php echo (strpos($currentUri, 'ThongKeDonHang.php') !== false) ? 'active' : ''; ?>" style="color: #ffffff !important;">
                    <span class="nav-icon">📈</span>
                    <span class="nav-text" style="color: #ffffff !important;">Báo Cáo & Thống Kê</span>
                </a>
            </li>
            <li style="list-style: none !important;">
                <a href="/Zyro/admin/pages/ThemSanPham.php?tab=product" class="sidebar-nav-link <?php echo (strpos($currentUri, 'ThemSanPham.php') !== false && (strpos($currentUri, 'tab=product') !== false || strpos($currentUri, 'tab=') === false)) ? 'active' : ''; ?>" style="color: #ffffff !important;">
                    <span class="nav-icon">➕</span>
                    <span class="nav-text" style="color: #ffffff !important;">Post Sản Phẩm Mới</span>
                </a>
            </li>
            <li style="list-style: none !important;">
                <a href="/Zyro/admin/pages/ThemSanPham.php?tab=article" class="sidebar-nav-link <?php echo ((strpos($currentUri, 'ThemSanPham.php') !== false && strpos($currentUri, 'tab=article') !== false) || strpos($currentUri, 'ThemBaiViet.php') !== false) ? 'active' : ''; ?>" style="color: #ffffff !important;">
                    <span class="nav-icon">📝</span>
                    <span class="nav-text" style="color: #ffffff !important;">Post Bài Viết / Tin Tức</span>
                </a>
            </li>
            <li class="sidebar-divider" style="list-style: none !important;"></li>
            <li style="list-style: none !important;">
                <a href="/Zyro/index.php" target="_blank" class="sidebar-nav-link external-link">
                    <span class="nav-icon">🌐</span>
                    <span class="nav-text">Xem Website Bán Hàng ↗</span>
                </a>
            </li>
            <li style="list-style: none !important;">
                <a href="/Zyro/pages/DangXuat.php" class="sidebar-nav-link logout-link">
                    <span class="nav-icon">🚪</span>
                    <span class="nav-text">Đăng Xuất</span>
                </a>
            </li>
        </ul>
    </div>
</div>
