<?php
/**
 * Tệp cấu hình kết nối Cơ sở dữ liệu MySQL (Database Configuration)
 * Dành cho hệ thống ZYRO Store
 * Tự động nhận diện thông minh giữa môi trường Localhost WAMP và Hosting InfinityFree (zyro.xo.je)
 */

$hostHeader = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$isHosting = (!empty($hostHeader) && $hostHeader !== 'localhost' && $hostHeader !== '127.0.0.1' && stripos($hostHeader, 'localhost:') === false);

if ($isHosting) {
    // -------------------------------------------------------------
    // THÔNG TIN CSDL TRÊN HOSTING INFINITYFREE (zyro.xo.je):
    // -------------------------------------------------------------
    if (!defined('DB_HOST')) define('DB_HOST', 'sql312.infinityfree.com');
    if (!defined('DB_PORT')) define('DB_PORT', 3306);
    if (!defined('DB_USER')) define('DB_USER', 'if0_43205028');
    if (!defined('DB_PASS')) define('DB_PASS', '5xhZwb6ZkMS');
    if (!defined('DB_NAME')) define('DB_NAME', 'if0_43205028_zyro');
} else {
    // -------------------------------------------------------------
    // MÔI TRƯỜNG MÁY TÍNH CÁ NHÂN (WAMP SERVER LOCAL):
    // -------------------------------------------------------------
    if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
    if (!defined('DB_PORT')) define('DB_PORT', 3306);
    if (!defined('DB_USER')) define('DB_USER', 'root');
    if (!defined('DB_PASS')) define('DB_PASS', '');
    if (!defined('DB_NAME')) define('DB_NAME', 'zyro_db');
}
