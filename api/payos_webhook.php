<?php
/**
 * Webhook tiếp nhận kết quả thanh toán tự động từ Cổng thanh toán PayOS
 * URL Webhook để cấu hình trên PayOS Dashboard: https://your-domain.com/Zyro/api/payos_webhook.php
 */
header('Content-Type: application/json; charset=UTF-8');

require_once('../class/classDatabase.php');
require_once('../class/classProduct.php');
require_once('../class/classPayOS.php');

// 1. Chỉ chấp nhận phương thức POST từ PayOS
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array(
        'success' => false,
        'message' => 'Method Not Allowed. Only POST is accepted.'
    ));
    exit();
}

// 2. Đọc dữ liệu Raw JSON Body
$rawInput = file_get_contents('php://input');
if (empty($rawInput)) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'message' => 'Empty request body.'
    ));
    exit();
}

$payload = json_decode($rawInput, true);
if (!$payload || !is_array($payload)) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'message' => 'Invalid JSON format.'
    ));
    exit();
}

// 3. Xác thực chữ ký số HMAC-SHA256 với CHECKSUM_KEY của PayOS
$isValidSignature = PayOS_Service::xac_thuc_webhook($payload);
if (!$isValidSignature) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'message' => 'Invalid webhook signature.'
    ));
    exit();
}

// 4. Trích xuất thông tin giao dịch
$data = $payload['data'];
$orderCode = isset($data['orderCode']) ? (int)$data['orderCode'] : 0;
$amount = isset($data['amount']) ? (float)$data['amount'] : 0;
$reference = isset($data['reference']) ? trim($data['reference']) : ('PAYOS_' . time());
$description = isset($data['description']) ? trim($data['description']) : '';

if ($orderCode <= 0 || $amount <= 0) {
    // Chữ ký hợp lệ, đây là test ping kiểm tra URL từ PayOS Dashboard
    http_response_code(200);
    echo json_encode(array(
        'success' => true,
        'message' => 'PayOS webhook ping test received successfully.'
    ));
    exit();
}

// 5. Cập nhật trạng thái đơn hàng trong CSDL MySQL zyro_db
$productModel = new san_pham();
$result = $productModel->xac_nhan_thanh_toan_payos($orderCode, $reference, $amount, $description);

if ($result['success']) {
    http_response_code(200);
    echo json_encode(array(
        'success' => true,
        'message' => $result['message'],
        'order_id' => $result['order_id'],
        'total_price' => $result['total_price']
    ));
} else {
    // Khi chữ ký số hợp lệ nhưng không tìm thấy đơn hàng trong CSDL (ví dụ đơn hàng thử nghiệm từ PayOS),
    // bắt buộc trả về HTTP 200 OK theo chuẩn của PayOS để xác thực URL webhook thành công trên Dashboard.
    http_response_code(200);
    echo json_encode(array(
        'success' => true,
        'message' => 'Webhook received. ' . $result['message']
    ));
}
exit();
