<?php
/**
 * ZYRO ADMIN - COMPONENT PHÂN TRANG CHUẨN
 * Yêu cầu:
 * 1. "1->10 item / 175" ở dưới cùng bên trái trang
 * 2. Bộ chọn số lượng item 5, 10, 20, 50, 100/page
 * 3. "< 1 2 3 ... >" ở dưới cùng bên phải, thay đổi động
 */

if (!isset($totalItems)) $totalItems = 0;
$perPage = isset($perPage) && in_array((int)$perPage, array(5, 10, 20, 50, 100)) ? (int)$perPage : 10;
$totalPages = max(1, (int)ceil($totalItems / $perPage));
$currentPage = isset($currentPage) ? max(1, min($totalPages, (int)$currentPage)) : 1;
$offset = ($currentPage - 1) * $perPage;

$fromItem = $totalItems > 0 ? ($offset + 1) : 0;
$toItem = min($offset + $perPage, $totalItems);

if (!isset($queryParams) || !is_array($queryParams)) {
    $queryParams = $_GET;
}

if (!function_exists('build_zyro_page_url')) {
    function build_zyro_page_url($page, $limit, $params) {
        $p = $params;
        $p['page'] = $page;
        $p['limit'] = $limit;
        return '?' . http_build_query($p);
    }
}
?>

<style>
.zyro-pagination-bar {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    flex-wrap: wrap !important;
    gap: 16px !important;
    padding: 16px 20px !important;
    background: #ffffff !important;
    border-top: 1px solid #e9ecef !important;
    border-radius: 0 0 14px 14px !important;
    box-sizing: border-box !important;
    width: 100% !important;
}

.pagination-info-left {
    display: flex !important;
    align-items: center !important;
}

.pagination-count-badge {
    display: inline-block !important;
    padding: 6px 14px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    color: #1e293b !important;
    background: #f1f5f9 !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 20px !important;
    letter-spacing: 0.3px !important;
}

.pagination-selector-mid {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    font-size: 13px !important;
    color: #475569 !important;
    font-weight: 600 !important;
}

.per-page-dropdown {
    padding: 6px 12px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 6px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    color: #0f172a !important;
    background-color: #ffffff !important;
    cursor: pointer !important;
    outline: none !important;
    transition: all 0.2s ease !important;
}

.per-page-dropdown:focus,
.per-page-dropdown:hover {
    border-color: #0f172a !important;
    box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.1) !important;
}

.pagination-nav-right {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
}

.page-nav-btn,
.page-num-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 34px !important;
    height: 34px !important;
    padding: 0 10px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    color: #334155 !important;
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 6px !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
    box-sizing: border-box !important;
}

.page-nav-btn:hover:not(.disabled),
.page-num-btn:hover:not(.active) {
    background: #f8fafc !important;
    border-color: #0f172a !important;
    color: #0f172a !important;
}

.page-num-btn.active {
    background: #0f172a !important;
    border-color: #0f172a !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    cursor: default !important;
}

.page-nav-btn.disabled {
    color: #94a3b8 !important;
    background: #f8fafc !important;
    border-color: #e2e8f0 !important;
    cursor: not-allowed !important;
    opacity: 0.6 !important;
}

.page-ellipsis {
    padding: 0 4px !important;
    font-size: 13px !important;
    font-weight: bold !important;
    color: #94a3b8 !important;
    letter-spacing: 1px !important;
}
</style>

<div class="zyro-pagination-bar">
    <!-- 1. Dưới cùng bên trái: "1->10 item / 175" -->
    <div class="pagination-info-left">
        <span class="pagination-count-badge">
            <?php echo $fromItem; ?>-><?php echo $toItem; ?> item / <?php echo $totalItems; ?>
        </span>
    </div>

    <!-- 2. Ở giữa: Bộ chọn số lượng item 5, 10, 20, 50, 100/page -->
    <div class="pagination-selector-mid">
        <label for="zyro-per-page-select-<?php echo isset($paginationId) ? $paginationId : 'main'; ?>" class="per-page-label">Hiển thị:</label>
        <select id="zyro-per-page-select-<?php echo isset($paginationId) ? $paginationId : 'main'; ?>" class="per-page-dropdown" onchange="window.location.href = this.value;">
            <?php foreach (array(5, 10, 20, 50, 100) as $optLimit): ?>
                <option value="<?php echo htmlspecialchars(build_zyro_page_url(1, $optLimit, $queryParams), ENT_QUOTES, 'UTF-8'); ?>" <?php echo $perPage === $optLimit ? 'selected' : ''; ?>>
                    <?php echo $optLimit; ?>/page
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- 3. Dưới cùng bên phải: "< 1 2 3 ... >" thay đổi động -->
    <div class="pagination-nav-right">
        <!-- Nút Previous '<' -->
        <?php if ($currentPage > 1): ?>
            <a href="<?php echo htmlspecialchars(build_zyro_page_url($currentPage - 1, $perPage, $queryParams), ENT_QUOTES, 'UTF-8'); ?>" class="page-nav-btn prev-btn" title="Trang trước">&lt;</a>
        <?php else: ?>
            <span class="page-nav-btn prev-btn disabled">&lt;</span>
        <?php endif; ?>

        <!-- Danh sách trang động 1 2 3 ... -->
        <?php
        $range = 2;
        $showEllipsisStart = false;
        $showEllipsisEnd = false;

        for ($p = 1; $p <= $totalPages; $p++):
            if ($p == 1 || $p == $totalPages || ($p >= $currentPage - $range && $p <= $currentPage + $range)):
                $isActive = ($p == $currentPage);
        ?>
                <a href="<?php echo htmlspecialchars(build_zyro_page_url($p, $perPage, $queryParams), ENT_QUOTES, 'UTF-8'); ?>" class="page-num-btn <?php echo $isActive ? 'active' : ''; ?>">
                    <?php echo $p; ?>
                </a>
        <?php
            elseif ($p < $currentPage - $range && !$showEllipsisStart):
                $showEllipsisStart = true;
                echo '<span class="page-ellipsis">...</span>';
            elseif ($p > $currentPage + $range && !$showEllipsisEnd):
                $showEllipsisEnd = true;
                echo '<span class="page-ellipsis">...</span>';
            endif;
        endfor;
        ?>

        <!-- Nút Next '>' -->
        <?php if ($currentPage < $totalPages): ?>
            <a href="<?php echo htmlspecialchars(build_zyro_page_url($currentPage + 1, $perPage, $queryParams), ENT_QUOTES, 'UTF-8'); ?>" class="page-nav-btn next-btn" title="Trang sau">&gt;</a>
        <?php else: ?>
            <span class="page-nav-btn next-btn disabled">&gt;</span>
        <?php endif; ?>
    </div>
</div>
