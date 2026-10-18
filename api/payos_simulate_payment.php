<?php
header('Content-Type: application/json; charset=UTF-8');

require_once(__DIR__ . '/../class/classDatabase.php');
require_once(__DIR__ . '/../class/classProduct.php');

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

// Nhận order_id hoặc ident_code từ GET hoặc POST
$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : (isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0);
$identCode = isset($_POST['ident_code']) ? trim($_POST['ident_code']) : (isset($_GET['ident_code']) ? trim($_GET['ident_code']) : '');

$productModel = new san_pham();
$order = null;

if ($orderId > 0) {
    $order = $productModel->lay_thong_tin_don_hang($orderId);
} elseif (!empty($identCode)) {
    // Tìm đơn hàng theo ident_code
    $db = new Database();
    $conn = $db->getConnection();
    if ($conn) {
        $stmt = $conn->prepare("SELECT * FROM donhang WHERE ident_code = ? LIMIT 1");
        $stmt->execute(array($identCode));
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

if (!$order) {
    http_response_code(404);
    echo json_encode(array(
        'success' => false,
        'message' => 'Không tìm thấy đơn hàng tương ứng để giả lập thanh toán.'
    ));
    exit();
}

$orderCode = (int)$order['orders_id'];
$amount = (int)$order['total_price'];
$currentIdentCode = !empty($order['ident_code']) ? $order['ident_code'] : $identCode;

if (empty($currentIdentCode)) {
    $currentIdentCode = $productModel->sinh_ma_nhan_dien_don_hang(6);
    $productModel->cap_nhat_thong_tin_payos($orderCode, 'ZYRO ' . $currentIdentCode, $orderCode, $currentIdentCode);
}

// Giả lập bot PayOS sinh chuỗi ghi chú chuẩn: "[Mã PayOS] [Mã nhận diện 6 chữ số]"
$payosCode = 'POS' . rand(10000, 99999);
$simulatedDescription = $payosCode . ' ' . $currentIdentCode;
$simulatedTxnId = 'SIM_TXN_' . date('YmdHis') . '_' . rand(1000, 9999);

// Gọi logic đối soát và xử lý đơn hàng
$result = $productModel->xac_nhan_thanh_toan_payos($orderCode, $simulatedTxnId, $amount, $simulatedDescription);

if ($result) {
    echo json_encode(array(
        'success' => true,
        'message' => 'Giả lập PayOS xác nhận chuyển khoản thành công! Đơn hàng đã chuyển sang trạng thái "Chờ xử lý".',
        'data' => array(
            'order_id' => $orderCode,
            'ident_code' => $currentIdentCode,
            'payos_code' => $payosCode,
            'note_description' => $simulatedDescription,
            'transaction_id' => $simulatedTxnId,
            'amount' => $amount,
            'new_status' => 'Chờ xử lý'
        )
    ));
} else {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'message' => 'Lỗi khi cập nhật trạng thái đơn hàng trong hệ thống.'
    ));
}
exit();
