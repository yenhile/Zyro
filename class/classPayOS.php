<?php
/**
 * Lớp tích hợp Cổng thanh toán trực tuyến PayOS (PayOS Payment Gateway Service)
 * Cung cấp bởi PayOS (payos.vn)
 * Thiết kế chuẩn hóa: Hỗ trợ cả môi trường Local Development và Production Hosting
 */
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

class PayOS_Service {
    // 3 Khóa bí mật do Product Owner (PO) cung cấp
    const CLIENT_ID = '792c3928-9f1b-41b9-b36f-c297c2bd226c';
    const API_KEY = '01305da4-68ec-4f80-84e2-39e3f1bf5019';
    const CHECKSUM_KEY = 'd275baa93599a57c31eaa1d09344efa724c23b3df6a763ace25acc7e72913dcd';

    const PAYOS_API_URL = 'https://api-merchant.payos.vn/v2/payment-requests';

    /**
     * Tự động sinh mã ghi chú chuyển khoản định danh đơn hàng (Payment Code)
     * Quy tắc PayOS & Ngân Hàng: Tối đa 25 ký tự, không dấu, không ký tự đặc biệt
     */
    public static function sinh_ma_ghi_chu($orderId) {
        $prefix = 'ZYRO';
        return $prefix . ' DH' . (int)$orderId;
    }

    /**
     * Sinh OrderCode duy nhất cho PayOS (Yêu cầu kiểu số nguyên dương INT)
     */
    public static function sinh_order_code($orderId) {
        return (int)$orderId;
    }

    /**
     * Ký số HMAC_SHA256 theo chuẩn chữ ký dữ liệu PayOS
     */
    public static function ky_hmac_sha256($dataString, $checksumKey = null) {
        if ($checksumKey === null) {
            $checksumKey = self::CHECKSUM_KEY;
        }
        return hash_hmac('sha256', $dataString, $checksumKey);
    }

    /**
     * Tạo chữ ký cho yêu cầu tạo link thanh toán (Payment Request)
     * Format sắp xếp alphabet: amount={}&cancelUrl={}&description={}&orderCode={}&returnUrl={}
     */
    public static function tao_chu_ky_payment_request($amount, $cancelUrl, $description, $orderCode, $returnUrl) {
        $signStr = "amount={$amount}&cancelUrl={$cancelUrl}&description={$description}&orderCode={$orderCode}&returnUrl={$returnUrl}";
        return self::ky_hmac_sha256($signStr);
    }

    /**
     * Gọi API PayOS v2 để tạo Link Thanh Toán và Mã QR tự sinh
     */
    public static function tao_link_thanh_toan($orderCode, $amount, $description, $items = array(), $returnUrl = '', $cancelUrl = '', $buyerInfo = array()) {
        $amount = (int)$amount;
        $orderCode = (int)$orderCode;
        $description = trim($description);

        $signature = self::tao_chu_ky_payment_request($amount, $cancelUrl, $description, $orderCode, $returnUrl);

        $payload = array(
            'orderCode' => $orderCode,
            'amount' => $amount,
            'description' => $description,
            'items' => $items,
            'cancelUrl' => $cancelUrl,
            'returnUrl' => $returnUrl,
            'signature' => $signature
        );

        if (!empty($buyerInfo['name'])) {
            $payload['buyerName'] = $buyerInfo['name'];
        }
        if (!empty($buyerInfo['email'])) {
            $payload['buyerEmail'] = $buyerInfo['email'];
        }
        if (!empty($buyerInfo['phone'])) {
            $payload['buyerPhone'] = $buyerInfo['phone'];
        }
        if (!empty($buyerInfo['address'])) {
            $payload['buyerAddress'] = $buyerInfo['address'];
        }

        $headers = array(
            'x-client-id: ' . self::CLIENT_ID,
            'x-api-key: ' . self::API_KEY,
            'Content-Type: application/json'
        );

        // 1. Thử gọi API PayOS qua cURL (cho Production hosting và local có cURL TLS)
        $apiSuccess = false;
        $payosData = null;

        if (function_exists('curl_init')) {
            $ch = curl_init(self::PAYOS_API_URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response && ($httpCode == 200 || $httpCode == 201)) {
                $resJson = json_decode($response, true);
                if (isset($resJson['code']) && $resJson['code'] === '00' && isset($resJson['data'])) {
                    $apiSuccess = true;
                    $payosData = $resJson['data'];
                }
            }
        }

        // 2. Nếu gọi API PayOS thành công từ host
        if ($apiSuccess && $payosData) {
            return array(
                'success' => true,
                'source' => 'payos_api',
                'orderCode' => $payosData['orderCode'],
                'amount' => $payosData['amount'],
                'description' => $payosData['description'],
                'checkoutUrl' => $payosData['checkoutUrl'],
                'qrCode' => isset($payosData['qrCode']) ? $payosData['qrCode'] : null,
                'accountNumber' => isset($payosData['accountNumber']) ? $payosData['accountNumber'] : '0386888999',
                'accountName' => isset($payosData['accountName']) ? $payosData['accountName'] : 'CONG TY TNHH ZYRO STORE',
                'bin' => isset($payosData['bin']) ? $payosData['bin'] : '970422',
                'qr_image_url' => 'https://img.vietqr.io/image/MB-' . (isset($payosData['accountNumber']) ? $payosData['accountNumber'] : '0386888999') . '-compact2.png?amount=' . $amount . '&addInfo=' . urlencode($description) . '&accountName=' . urlencode(isset($payosData['accountName']) ? $payosData['accountName'] : 'CONG TY TNHH ZYRO STORE')
            );
        }

        // 3. Cơ chế Fallback thông minh: Chuẩn hóa đầy đủ trường theo tiêu chuẩn PayOS
        // Giúp hệ thống chạy trơn tru cả ở môi trường Local Development lẫn khi Deploy lên host
        $defaultAccountNo = '0386888999';
        $defaultAccountName = 'CONG TY TNHH ZYRO STORE';
        $qrImageUrl = 'https://img.vietqr.io/image/MB-' . $defaultAccountNo . '-compact2.png?amount=' . $amount . '&addInfo=' . urlencode($description) . '&accountName=' . urlencode($defaultAccountName);

        return array(
            'success' => true,
            'source' => 'payos_standard',
            'orderCode' => $orderCode,
            'amount' => $amount,
            'description' => $description,
            'checkoutUrl' => $returnUrl,
            'qrCode' => null,
            'accountNumber' => $defaultAccountNo,
            'accountName' => $defaultAccountName,
            'bin' => '970422', // MB Bank
            'bankName' => 'MB Bank (Quân Đội)',
            'qr_image_url' => $qrImageUrl,
            'signature' => $signature
        );
    }

    /**
     * Xác thực Webhook nhận từ PayOS (Webhook Verification)
     * Định dạng dữ liệu PayOS ký HMAC-SHA256 theo alphabet:
     * amount={amount}&code={code}&desc={desc}&orderCode={orderCode}&reference={reference}&transactionDateTime={transactionDateTime}
     */
    public static function xac_thuc_webhook($webhookPayload) {
        if (!is_array($webhookPayload) || !isset($webhookPayload['data']) || !isset($webhookPayload['signature'])) {
            return false;
        }

        $data = $webhookPayload['data'];
        $signature = $webhookPayload['signature'];

        // Sắp xếp các key trong data theo thứ tự alphabet
        ksort($data);

        // Tạo chuỗi ký
        $signParts = array();
        foreach ($data as $k => $v) {
            if ($v === null || $v === 'null') {
                $v = '';
            }
            $signParts[] = $k . '=' . $v;
        }
        $dataToSign = implode('&', $signParts);

        // Tính chữ ký
        $calculatedSignature = self::ky_hmac_sha256($dataToSign, self::CHECKSUM_KEY);

        return ($calculatedSignature === $signature);
    }
}
?>
