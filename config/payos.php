<?php
/**
 * Tệp cấu hình Cổng Thanh Toán PayOS (PayOS Configuration)
 * Dành cho hệ thống ZYRO Store (Footwear E-Commerce)
 * 
 * HƯỚNG DẪN KHI ĐƯA LÊN HOSTING:
 * 1. Bạn chỉ cần vào trang https://my.payos.vn -> Kênh thanh toán -> Thiết lập Webhook.
 * 2. Copy Webhook URL do hệ thống sinh tự động (gọi qua PayOS_Config::getWebhookUrl()) và dán vào PayOS.
 * 3. Nếu muốn đổi tài khoản PayOS mới, chỉ cần cập nhật 3 khóa CLIENT_ID, API_KEY, CHECKSUM_KEY dưới đây
 *    hoặc thiết lập biến môi trường trên hosting (cPanel Environment Variables).
 */

class PayOS_Config {
    // 3 Khóa bí mật kết nối PayOS (payos.vn)
    const DEFAULT_CLIENT_ID = '792c3928-9f1b-41b9-b36f-c297c2bd226c';
    const DEFAULT_API_KEY = '01305da4-68ec-4f80-84e2-39e3f1bf5019';
    const DEFAULT_CHECKSUM_KEY = 'd275baa93599a57c31eaa1d09344efa724c23b3df6a763ace25acc7e72913dcd';

    const API_ENDPOINT = 'https://api-merchant.payos.vn/v2/payment-requests';

    /**
     * Lấy Client ID (Ưu tiên biến môi trường hosting nếu có)
     */
    public static function getClientId() {
        $env = getenv('PAYOS_CLIENT_ID');
        if (!empty($env)) {
            return trim($env);
        }
        if (isset($_ENV['PAYOS_CLIENT_ID']) && !empty($_ENV['PAYOS_CLIENT_ID'])) {
            return trim($_ENV['PAYOS_CLIENT_ID']);
        }
        return self::DEFAULT_CLIENT_ID;
    }

    /**
     * Lấy API Key
     */
    public static function getApiKey() {
        $env = getenv('PAYOS_API_KEY');
        if (!empty($env)) {
            return trim($env);
        }
        if (isset($_ENV['PAYOS_API_KEY']) && !empty($_ENV['PAYOS_API_KEY'])) {
            return trim($_ENV['PAYOS_API_KEY']);
        }
        return self::DEFAULT_API_KEY;
    }

    /**
     * Lấy Checksum Key
     */
    public static function getChecksumKey() {
        $env = getenv('PAYOS_CHECKSUM_KEY');
        if (!empty($env)) {
            return trim($env);
        }
        if (isset($_ENV['PAYOS_CHECKSUM_KEY']) && !empty($_ENV['PAYOS_CHECKSUM_KEY'])) {
            return trim($_ENV['PAYOS_CHECKSUM_KEY']);
        }
        return self::DEFAULT_CHECKSUM_KEY;
    }

    /**
     * Tự động nhận diện Base URL của website (Hỗ trợ cả Localhost /Zyro/ lẫn Hosting Domain gốc /)
     */
    public static function getBaseUrl() {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                   || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
                   || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $protocol = $isHttps ? 'https://' : 'http://';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        
        // Nhận diện đường dẫn thư mục cấp con (ví dụ: /Zyro) nếu dự án chạy trong subfolder
        $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
        $subfolder = '';
        if (stripos($scriptName, '/Zyro/') !== false) {
            $subfolder = '/Zyro';
        }

        return $protocol . $host . $subfolder;
    }

    /**
     * Tự động sinh Webhook URL chuẩn hóa cho PayOS
     * Ví dụ trên Host: https://zyrostore.com/api/payos_webhook.php
     * Hoặc Localhost: http://localhost/Zyro/api/payos_webhook.php
     */
    public static function getWebhookUrl() {
        return self::getBaseUrl() . '/api/payos_webhook.php';
    }

    /**
     * Sinh Return URL sau khi thanh toán thành công
     */
    public static function getReturnUrl($orderId) {
        return self::getBaseUrl() . '/pages/DatHangThanhCong.php?order_id=' . (int)$orderId . '&paid=1';
    }

    /**
     * Sinh Cancel URL khi khách bấm hủy
     */
    public static function getCancelUrl($orderId) {
        return self::getBaseUrl() . '/pages/GioHang.php?order_cancelled=' . (int)$orderId;
    }
}
