<?php
header('Content-Type: application/json; charset=UTF-8');

require_once('../class/classDatabase.php');
require_once('../class/classProduct.php');

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

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(array('success' => false, 'message' => 'Missing or invalid order_id'));
    exit();
}

$productModel = new san_pham();
$order = $productModel->lay_thong_tin_don_hang($orderId);

if (!$order) {
    http_response_code(404);
    echo json_encode(array('success' => false, 'message' => 'Order not found'));
    exit();
}

$status = trim($order['status']);
$isPaid = ($status === 'Đã thanh toán' || stripos($status, 'đã thanh toán') !== false || $status === 'Đã giao' || $status === 'Đang giao');

echo json_encode(array(
    'success' => true,
    'order_id' => (int)$order['orders_id'],
    'status' => $status,
    'is_paid' => $isPaid,
    'paid_at' => $order['paid_at'],
    'transaction_id' => $order['payos_transaction_id'],
    'payment_method' => $order['payment_method']
));
exit();
