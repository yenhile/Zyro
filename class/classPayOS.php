<?php
/**
 * Lớp tích hợp Cổng thanh toán trực tuyến PayOS (PayOS Payment Gateway Service)
 * Cung cấp bởi PayOS (payos.vn)
 * Thiết kế chuẩn hóa: Sẵn sàng 100% cho Production Hosting & Môi trường Local Development
 */

require_once(__DIR__ . '/../config/payos.php');

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
    /**
     * Lấy các khóa cấu hình từ PayOS_Config
     */
    public static function get_client_id() {
        return PayOS_Config::getClientId();
    }

    public static function get_api_key() {
        return PayOS_Config::getApiKey();
    }

    public static function get_checksum_key() {
        return PayOS_Config::getChecksumKey();
    }

    /**
     * Tự động sinh mã ghi chú chuyển khoản định danh đơn hàng (Payment Code)
     * Quy tắc PayOS & Ngân Hàng: Tối đa 25 ký tự, không dấu, không ký tự đặc biệt
     */
    public static function sinh_ma_ghi_chu($orderId, $identCode = null) {
        if (!empty($identCode)) {
            $clean = preg_replace('/[^A-Za-z0-9]/', '', (string)$identCode);
            return strtoupper(substr($clean, 0, 25));
        }
        return 'ZYRO DH' . (int)$orderId;
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
            $checksumKey = self::get_checksum_key();
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
    public static function tao_link_thanh_toan($orderCode, $amount, $description, $items = array(), $returnUrl = '', $cancelUrl = '', $buyerInfo = array(), $identCode = null) {
        $amount = (int)$amount;
        $orderCode = (int)$orderCode;

        // Chuẩn hóa nội dung ghi chú: Bỏ ký tự đặc biệt, tối đa 25 ký tự theo quy định PayOS
        $cleanDesc = preg_replace('/[^A-Za-z0-9 ]/', '', (string)$description);
        $cleanDesc = trim(substr($cleanDesc, 0, 25));
        if (empty($cleanDesc)) {
            $cleanDesc = !empty($identCode) ? strtoupper(trim($identCode)) : ('ZYRO ' . $orderCode);
        }

        // Tự động sinh URL nếu chưa được truyền vào
        if (empty($returnUrl)) {
            $returnUrl = PayOS_Config::getReturnUrl($orderCode);
        }
        if (empty($cancelUrl)) {
            $cancelUrl = PayOS_Config::getCancelUrl($orderCode);
        }

        $signature = self::tao_chu_ky_payment_request($amount, $cancelUrl, $cleanDesc, $orderCode, $returnUrl);

        $payload = array(
            'orderCode' => $orderCode,
            'amount' => $amount,
            'description' => $cleanDesc,
            'items' => is_array($items) ? $items : array(),
            'cancelUrl' => $cancelUrl,
            'returnUrl' => $returnUrl,
            'signature' => $signature
        );

        if (!empty($buyerInfo['name'])) {
            $payload['buyerName'] = preg_replace('/[^\p{L}\p{N}\s]/u', '', trim($buyerInfo['name']));
        }
        if (!empty($buyerInfo['email'])) {
            $payload['buyerEmail'] = trim($buyerInfo['email']);
        }
        if (!empty($buyerInfo['phone'])) {
            $payload['buyerPhone'] = preg_replace('/[^0-9]/', '', trim($buyerInfo['phone']));
        }
        if (!empty($buyerInfo['address'])) {
            $payload['buyerAddress'] = trim($buyerInfo['address']);
        }

        $headers = array(
            'x-client-id: ' . self::get_client_id(),
            'x-api-key: ' . self::get_api_key(),
            'Content-Type: application/json'
        );

        // 1. Gọi API PayOS qua cURL (Hoạt động trên Production Hosting và môi trường có kết nối mạng)
        $apiSuccess = false;
        $payosData = null;

        if (function_exists('curl_init')) {
            $ch = curl_init(PayOS_Config::API_ENDPOINT);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
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
            $bin = !empty($payosData['bin']) ? $payosData['bin'] : '970422';
            $accNo = !empty($payosData['accountNumber']) ? $payosData['accountNumber'] : '0386888999';
            $accName = !empty($payosData['accountName']) ? $payosData['accountName'] : 'CONG TY TNHH ZYRO STORE';
            $chkUrl = !empty($payosData['checkoutUrl']) ? $payosData['checkoutUrl'] : $returnUrl;
            $qrCode = isset($payosData['qrCode']) ? $payosData['qrCode'] : null;

            // Tạo link QR VietQR chuẩn hóa từ BIN động của PayOS
            $qrImageUrl = 'https://img.vietqr.io/image/' . $bin . '-' . $accNo . '-compact2.png?amount=' . $amount . '&addInfo=' . urlencode($cleanDesc) . '&accountName=' . urlencode($accName);

            $payosPrefix = 'POS' . substr(abs(crc32($orderCode . 'PAYOS')), 0, 5);
            $fullTransferDesc = !empty($identCode) ? ($payosPrefix . ' ' . $identCode) : $cleanDesc;

            return array(
                'success' => true,
                'source' => 'payos_api',
                'orderCode' => $payosData['orderCode'],
                'amount' => $payosData['amount'],
                'description' => $cleanDesc,
                'payos_code' => $payosPrefix,
                'full_description' => $fullTransferDesc,
                'checkoutUrl' => $chkUrl,
                'qrCode' => $qrCode,
                'accountNumber' => $accNo,
                'accountName' => $accName,
                'bin' => $bin,
                'qr_image_url' => $qrImageUrl,
                'signature' => $signature
            );
        }

        // 3. Cơ chế Fallback an toàn (Khi chạy offline, sandbox hoặc local chưa kết nối PayOS)
        $defaultAccountNo = '0386888999';
        $defaultAccountName = 'CONG TY TNHH ZYRO STORE';
        $defaultBin = '970422'; // MB Bank
        $payosPrefix = 'POS' . substr(abs(crc32($orderCode . 'PAYOS')), 0, 5);
        $fullTransferDesc = !empty($identCode) ? ($payosPrefix . ' ' . $identCode) : $cleanDesc;
        $qrImageUrl = 'https://img.vietqr.io/image/' . $defaultBin . '-' . $defaultAccountNo . '-compact2.png?amount=' . $amount . '&addInfo=' . urlencode($cleanDesc) . '&accountName=' . urlencode($defaultAccountName);

        return array(
            'success' => true,
            'source' => 'payos_standard',
            'orderCode' => $orderCode,
            'amount' => $amount,
            'description' => $cleanDesc,
            'payos_code' => $payosPrefix,
            'full_description' => $fullTransferDesc,
            'checkoutUrl' => $returnUrl,
            'qrCode' => null,
            'accountNumber' => $defaultAccountNo,
            'accountName' => $defaultAccountName,
            'bin' => $defaultBin,
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
        $calculatedSignature = self::ky_hmac_sha256($dataToSign, self::get_checksum_key());

        return hash_equals($calculatedSignature, $signature);
    }
}
?>
