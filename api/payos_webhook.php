<?php
/**
 * Webhook chính thức từ Cổng Thanh Toán PayOS (payos.vn)
 * Thiết kế chuẩn hóa: Sẵn sàng 100% cho Production Hosting
 * 
 * Chức năng:
 * 1. Tiếp nhận và xác thực chữ ký HMAC-SHA256 Checksum bằng CHECKSUM_KEY.
 * 2. Tự động phản hồi HTTP 200 OK khi PayOS gửi request xác nhận Webhook URL (confirm-webhook, orderCode: 123).
 * 3. Trích xuất mã nhận diện 6 ký tự hoặc mã đơn hàng để đối soát giao dịch thực tế.
 * 4. Cập nhật đơn hàng sang trạng thái 'Chờ xử lý', lưu thời gian và mã giao dịch ngân hàng.
 * 5. Hỗ trợ Idempotent (chống trùng lặp khi PayOS retry) và ghi log chi tiết vào logs/payos_webhook.log.
 */
header('Content-Type: application/json; charset=UTF-8');

require_once(__DIR__ . '/../config/payos.php');
require_once(__DIR__ . '/../class/classDatabase.php');
require_once(__DIR__ . '/../class/classProduct.php');
require_once(__DIR__ . '/../class/classPayOS.php');

if (!function_exists('http_response_code')) {
    function http_response_code($code = NULL) {
        static $prev_code = 200;
        if ($code !== NULL) {
            $prev_code = $code;
            switch ($code) {
                case 200: $text = 'OK'; break;
                case 400: $text = 'Bad Request'; break;
                case 403: $text = 'Forbidden'; break;
                case 404: $text = 'Not Found'; break;
                case 405: $text = 'Method Not Allowed'; break;
                case 422: $text = 'Unprocessable Entity'; break;
                case 500: $text = 'Internal Server Error'; break;
                default: $text = 'Unknown'; break;
            }
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $code . ' ' . $text);
        }
        return $prev_code;
    }
}

/**
 * Hàm ghi nhật ký Webhook an toàn
 */
function log_payos_webhook($message, $payload = null) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/payos_webhook.log';
    $time = date('Y-m-d H:i:s');
    $clientIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'UNKNOWN_IP';
    $logLine = "[{$time}] [IP: {$clientIp}] {$message}";
    if ($payload !== null) {
        $logLine .= " | Payload: " . (is_string($payload) ? $payload : json_encode($payload, JSON_UNESCAPED_UNICODE));
    }
    $logLine .= PHP_EOL;
    @file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
}

// 1. Chỉ chấp nhận phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    log_payos_webhook("REJECTED: Method not allowed ({$_SERVER['REQUEST_METHOD']})");
    echo json_encode(array('code' => '01', 'desc' => 'Method Not Allowed'));
    exit();
}

// 2. Đọc nội dung JSON Body
$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput, true);

if (!$payload || !is_array($payload)) {
    http_response_code(400);
    log_payos_webhook("REJECTED: Invalid JSON payload", $rawInput);
    echo json_encode(array('code' => '02', 'desc' => 'Invalid JSON payload'));
    exit();
}

// 3. Xác thực chữ ký dữ liệu HMAC_SHA256 của PayOS
$isValidSignature = PayOS_Service::xac_thuc_webhook($payload);
if (!$isValidSignature) {
    http_response_code(403);
    log_payos_webhook("SECURITY ALERT: Invalid Checksum Signature", $payload);
    echo json_encode(array('code' => '03', 'desc' => 'Invalid Checksum Signature'));
    exit();
}

// 4. Bóc tách dữ liệu giao dịch từ PayOS
$data = isset($payload['data']) ? $payload['data'] : array();
$orderCode = isset($data['orderCode']) ? (int)$data['orderCode'] : 0;
$amount = isset($data['amount']) ? (float)$data['amount'] : 0;
$description = isset($data['description']) ? trim($data['description']) : '';
$reference = !empty($data['reference']) ? $data['reference'] : (!empty($data['paymentLinkId']) ? $data['paymentLinkId'] : 'PAYOS_' . time());

// 5. Xử lý request test xác nhận Webhook URL của PayOS (confirm-webhook)
// Khi admin cấu hình webhook tại my.payos.vn, PayOS sẽ gửi test payload với orderCode 123
if ($orderCode === 123 || stripos($description, 'VQRIO123') !== false) {
    http_response_code(200);
    log_payos_webhook("SUCCESS: PayOS confirm-webhook test request verified successfully (OrderCode: 123)");
    echo json_encode(array(
        'code' => '00',
        'desc' => 'Webhook test verified successfully',
        'data' => array(
            'orderCode' => 123,
            'is_test' => true
        )
    ));
    exit();
}

// 6. Xử lý đơn hàng thực tế: Đối soát mã nhận diện 6 ký tự và cập nhật đơn sang 'Chờ xử lý'
$productModel = new san_pham();
$result = $productModel->xac_nhan_thanh_toan_payos($orderCode, $reference, $amount, $description);

if ($result['success']) {
    http_response_code(200);
    $alreadyMsg = !empty($result['already_processed']) ? " (Idempotent: Order already processed)" : "";
    log_payos_webhook("SUCCESS: Order #{$result['order_id']} confirmed paid{$alreadyMsg}", $data);
    echo json_encode(array(
        'code' => '00',
        'desc' => 'Success',
        'order_id' => $result['order_id'],
        'ident_code' => isset($result['ident_code']) ? $result['ident_code'] : null,
        'already_processed' => !empty($result['already_processed'])
    ));
} else {
    // Chữ ký hợp lệ nhưng đơn hàng chưa khớp hoặc không tìm thấy:
    // Vẫn phản hồi HTTP 200 để PayOS ghi nhận server đã nhận tin, tránh retry liên tục
    http_response_code(200);
    log_payos_webhook("NOTICE: Signature valid but order unhandled: {$result['message']}", $data);
    echo json_encode(array(
        'code' => '00',
        'desc' => 'Webhook acknowledged: ' . $result['message'],
        'orderCode' => $orderCode
    ));
}
exit();
?>
