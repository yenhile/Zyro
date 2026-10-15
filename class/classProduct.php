<?php
require_once("classDatabase.php");

class san_pham extends Zyro
{
    /**
     * Lấy danh sách sản phẩm nổi bật / trending theo thương hiệu hoặc toàn bộ
     * Sử dụng PDO Prepared Statements chống SQL Injection
     */
    public function lay_san_pham_trending($brand_id = null, $limit = 8)
    {
        $pdo = $this->ketnoi();
        if (!$pdo) return array();

        if ($brand_id !== null) {
            $sql = "SELECT sp.*, th.name AS brand_name, lg.name AS category_name 
                    FROM sanpham sp 
                    LEFT JOIN sanpham_thuonghieu th ON sp.brand_id = th.brand_id 
                    LEFT JOIN sanpham_loaigiay lg ON sp.category_id = lg.category_id 
                    WHERE sp.brand_id = :brand_id 
                    ORDER BY sp.product_id ASC 
                    LIMIT " . (int)$limit;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':brand_id', (int)$brand_id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $sql = "SELECT sp.*, th.name AS brand_name, lg.name AS category_name 
                    FROM sanpham sp 
                    LEFT JOIN sanpham_thuonghieu th ON sp.brand_id = th.brand_id 
                    LEFT JOIN sanpham_loaigiay lg ON sp.category_id = lg.category_id 
                    ORDER BY sp.product_id ASC 
                    LIMIT " . (int)$limit;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách thương hiệu (Nike, Converse, Jordan...)
     */
    public function lay_danh_sach_thuong_hieu()
    {
        $pdo = $this->ketnoi();
        if (!$pdo) return array();

        $stmt = $pdo->prepare("SELECT * FROM sanpham_thuonghieu ORDER BY brand_id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách loại giày / môn thể thao (Chạy bộ, Bóng rổ, Tennis...)
     */
    public function lay_danh_sach_loai_giay()
    {
        $pdo = $this->ketnoi();
        if (!$pdo) return array();

        $stmt = $pdo->prepare("SELECT * FROM sanpham_loaigiay ORDER BY category_id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết 1 sản phẩm theo ID
     */
    public function lay_chi_tiet_san_pham($product_id)
    {
        $pdo = $this->ketnoi();
        if (!$pdo) return false;

        $stmt = $pdo->prepare("SELECT sp.*, th.name AS brand_name, lg.name AS category_name 
                               FROM sanpham sp 
                               LEFT JOIN sanpham_thuonghieu th ON sp.brand_id = th.brand_id 
                               LEFT JOIN sanpham_loaigiay lg ON sp.category_id = lg.category_id 
                               WHERE sp.product_id = :id");
        $stmt->bindValue(':id', (int)$product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách sản phẩm liên quan (cùng danh mục hoặc thương hiệu)
     */
    public function lay_san_pham_lien_quan($category_id, $brand_id, $exclude_id, $limit = 4)
    {
        $pdo = $this->ketnoi();
        if (!$pdo) return array();

        $sql = "SELECT sp.*, th.name AS brand_name, lg.name AS category_name 
                FROM sanpham sp 
                LEFT JOIN sanpham_thuonghieu th ON sp.brand_id = th.brand_id 
                LEFT JOIN sanpham_loaigiay lg ON sp.category_id = lg.category_id 
                WHERE (sp.category_id = :cat_id OR sp.brand_id = :brand_id) 
                  AND sp.product_id != :exclude_id 
                ORDER BY sp.product_id DESC 
                LIMIT " . (int)$limit;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':cat_id', (int)$category_id, PDO::PARAM_INT);
        $stmt->bindValue(':brand_id', (int)$brand_id, PDO::PARAM_INT);
        $stmt->bindValue(':exclude_id', (int)$exclude_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách đánh giá của sản phẩm
     */
    public function lay_danh_gia_san_pham($product_id)
    {
        $pdo = $this->ketnoi();
        if (!$pdo) return array();

        $sql = "SELECT dg.*, tk.name AS user_name 
                FROM sanpham_danhgia dg 
                LEFT JOIN taikhoan tk ON dg.user_id = tk.user_id 
                WHERE dg.product_id = :product_id 
                ORDER BY dg.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':product_id', (int)$product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm đánh giá mới cho sản phẩm (Chỉ thành viên đã đăng nhập)
     */
    public function them_danh_gia_san_pham($userId, $productId, $rating, $comment)
    {
        $pdo = $this->ketnoi();
        if (!$pdo) return false;

        $rating = max(1, min(5, (int)$rating));
        $comment = trim($comment);
        if (empty($comment)) return false;

        $sql = "INSERT INTO sanpham_danhgia (user_id, product_id, rating, comment, created_at) 
                VALUES (:user_id, :product_id, :rating, :comment, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(':product_id', (int)$productId, PDO::PARAM_INT);
        $stmt->bindValue(':rating', (int)$rating, PDO::PARAM_INT);
        $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
        return $stmt->execute();
    }

    /**
     * Hàm render danh sách sản phẩm (tương thích ngược với pages/SanPham.php)
     */
    public function xuat_san_pham($loai = 'all')
    {   
        $pdo = $this->ketnoi();
        if (!$pdo) {
            echo '<p class="text-danger">Không thể kết nối CSDL.</p>';
            return;
        }

        if ($loai == 'all' || empty($loai)) {
            $sql = "SELECT sp.*, th.name AS brand_name, lg.name AS category_name 
                    FROM sanpham sp 
                    LEFT JOIN sanpham_thuonghieu th ON sp.brand_id = th.brand_id 
                    LEFT JOIN sanpham_loaigiay lg ON sp.category_id = lg.category_id 
                    ORDER BY sp.name ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        } else {
            $sql = "SELECT sp.*, th.name AS brand_name, lg.name AS category_name 
                    FROM sanpham sp 
                    LEFT JOIN sanpham_thuonghieu th ON sp.brand_id = th.brand_id 
                    LEFT JOIN sanpham_loaigiay lg ON sp.category_id = lg.category_id 
                    WHERE lg.name = :loai OR th.name = :loai 
                    ORDER BY sp.name ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':loai', $loai, PDO::PARAM_STR);
            $stmt->execute();
        }

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($products && count($products) > 0) {
            foreach ($products as $row) {
                $id = (int)$row['product_id'];
                $hinh = htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8');
                $tensp = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                $mota = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
                $gia = number_format($row['price'], 0, ',', '.');

                echo '<div class="sanpham-box">
                        <a href="/Zyro/pages/ChiTietSanPham.php?id=' . $id . '">
                            <div class="product-img-wrap">
                                <img src="/Zyro/assets/img/' . $hinh . '" alt="' . $tensp . '">
                            </div>
                            <div class="sanpham-thongtin">
                                <p class="sp-name">' . $tensp . '</p>
                                <div class="sp-mo-ta-ngan">' . $mota . '</div>
                                <div class="sp-gia"><span>' . $gia . '</span>₫</div>
                            </div>
                        </a>
                        <div class="like-sp">
                            <div class="like-sp-icon"></div>
                        </div>
                    </div>';
            }
        } else {
            echo '<p class="text-muted">Không có dữ liệu sản phẩm phù hợp.</p>';
        }
    }

    /**
     * Tạo đơn hàng mới kèm chi tiết các món hàng (PDO Transaction)
     * Hỗ trợ lưu trữ phương thức thanh toán, mã đối soát và mã đơn PayOS
     */
    public function tao_don_hang($userId, $totalPrice, $receiverName, $phone, $address, $note, $paymentMethod, $cartItems, $paymentCode = null, $payosOrderCode = null, $initialStatus = 'Chờ xử lý') {
        $pdo = $this->ketnoi();
        try {
            $pdo->beginTransaction();

            $sqlOrder = "INSERT INTO donhang (user_id, total_price, receiver_name, receiver_phone, shipping_address, note, payment_method, payment_code, payos_order_code, order_date, status) 
                         VALUES (:user_id, :total_price, :receiver_name, :receiver_phone, :shipping_address, :note, :payment_method, :payment_code, :payos_order_code, NOW(), :status)";
            $stmtOrder = $pdo->prepare($sqlOrder);
            $stmtOrder->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
            $stmtOrder->bindValue(':total_price', $totalPrice);
            $stmtOrder->bindValue(':receiver_name', $receiverName, PDO::PARAM_STR);
            $stmtOrder->bindValue(':receiver_phone', $phone, PDO::PARAM_STR);
            $stmtOrder->bindValue(':shipping_address', $address, PDO::PARAM_STR);
            $stmtOrder->bindValue(':note', $note, PDO::PARAM_STR);
            $stmtOrder->bindValue(':payment_method', $paymentMethod, PDO::PARAM_STR);
            $stmtOrder->bindValue(':payment_code', $paymentCode, PDO::PARAM_STR);
            if ($payosOrderCode !== null) {
                $stmtOrder->bindValue(':payos_order_code', (int)$payosOrderCode, PDO::PARAM_INT);
            } else {
                $stmtOrder->bindValue(':payos_order_code', null, PDO::PARAM_NULL);
            }
            $stmtOrder->bindValue(':status', $initialStatus, PDO::PARAM_STR);
            $stmtOrder->execute();

            $orderId = (int)$pdo->lastInsertId();

            $sqlDetail = "INSERT INTO donhang_chitiet (order_id, product_id, quantity, size, price) 
                          VALUES (:order_id, :product_id, :quantity, :size, :price)";
            $stmtDetail = $pdo->prepare($sqlDetail);

            foreach ($cartItems as $item) {
                $stmtDetail->bindValue(':order_id', $orderId, PDO::PARAM_INT);
                $stmtDetail->bindValue(':product_id', (int)$item['product_id'], PDO::PARAM_INT);
                $stmtDetail->bindValue(':quantity', (int)$item['quantity'], PDO::PARAM_INT);
                $stmtDetail->bindValue(':size', isset($item['size']) ? $item['size'] : '40', PDO::PARAM_STR);
                $stmtDetail->bindValue(':price', $item['price']);
                $stmtDetail->execute();
            }

            $pdo->commit();
            return $orderId;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
    }

    /**
     * Cập nhật thông tin PayOS khi khởi tạo thanh toán
     */
    public function cap_nhat_thong_tin_payos($orderId, $paymentCode, $payosOrderCode) {
        $pdo = $this->ketnoi();
        $sql = "UPDATE donhang SET payment_code = :pcode, payos_order_code = :ocode WHERE orders_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':pcode', $paymentCode, PDO::PARAM_STR);
        $stmt->bindValue(':ocode', (int)$payosOrderCode, PDO::PARAM_INT);
        $stmt->bindValue(':id', (int)$orderId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Webhook: Xác nhận thanh toán PayOS thành công và cập nhật CSDL MySQL
     */
    public function xac_nhan_thanh_toan_payos($orderCode, $transactionId, $amount, $paymentCode = null) {
        $pdo = $this->ketnoi();

        // 1. Tìm đơn hàng theo orderCode hoặc paymentCode
        $sqlFind = "SELECT * FROM donhang WHERE (orders_id = :oid OR payos_order_code = :ocode OR payment_code = :pcode) LIMIT 1";
        $stmtFind = $pdo->prepare($sqlFind);
        $stmtFind->bindValue(':oid', (int)$orderCode, PDO::PARAM_INT);
        $stmtFind->bindValue(':ocode', (int)$orderCode, PDO::PARAM_INT);
        $stmtFind->bindValue(':pcode', $paymentCode, PDO::PARAM_STR);
        $stmtFind->execute();
        $order = $stmtFind->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return array('success' => false, 'message' => 'Không tìm thấy đơn hàng tương ứng!');
        }

        // 2. Kiểm tra số tiền
        $expectedAmount = (float)$order['total_price'];
        if ((float)$amount < $expectedAmount) {
            return array('success' => false, 'message' => 'Số tiền thanh toán nhỏ hơn tổng giá trị đơn hàng!');
        }

        // 3. Cập nhật trạng thái sang 'Đã thanh toán'
        $sqlUpdate = "UPDATE donhang 
                      SET status = 'Đã thanh toán', 
                          payos_transaction_id = :trans_id, 
                          paid_at = NOW() 
                      WHERE orders_id = :id";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->bindValue(':trans_id', $transactionId, PDO::PARAM_STR);
        $stmtUpdate->bindValue(':id', (int)$order['orders_id'], PDO::PARAM_INT);

        if ($stmtUpdate->execute()) {
            return array(
                'success' => true,
                'message' => 'Đã xác nhận thanh toán thành công cho đơn hàng #' . $order['orders_id'],
                'order_id' => (int)$order['orders_id'],
                'total_price' => $expectedAmount
            );
        }

        return array('success' => false, 'message' => 'Lỗi hệ thống khi cập nhật trạng thái đơn hàng!');
    }

    /**
     * Tìm hoặc tạo tài khoản khách hàng khi đặt hàng online
     */
    public function tim_hoac_tao_khach_hang($name, $email, $phone, $address) {
        $pdo = $this->ketnoi();
        $email = trim(strtolower($email));
        if (empty($email)) {
            $email = 'guest_' . time() . '_' . rand(100, 999) . '@zyro.local';
        }

        // Kiểm tra xem email đã có tài khoản chưa
        $stmt = $pdo->prepare("SELECT user_id FROM taikhoan WHERE email = :email LIMIT 1");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            return (int)$user['user_id'];
        }

        // Tạo tài khoản mới cho khách hàng
        $defaultPassword = hash('sha256', 'zyro@123456');
        $sqlInsert = "INSERT INTO taikhoan (name, email, password, phone, address, phanquyen, created_at) 
                      VALUES (:name, :email, :password, :phone, :address, 1, NOW())";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->bindValue(':name', $name, PDO::PARAM_STR);
        $stmtInsert->bindValue(':email', $email, PDO::PARAM_STR);
        $stmtInsert->bindValue(':password', $defaultPassword, PDO::PARAM_STR);
        $stmtInsert->bindValue(':phone', $phone, PDO::PARAM_STR);
        $stmtInsert->bindValue(':address', $address, PDO::PARAM_STR);
        $stmtInsert->execute();

        return (int)$pdo->lastInsertId();
    }

    /**
     * Băm mật khẩu an toàn có muối
     */
    public function bam_mat_khau($password) {
        if (function_exists('password_hash')) {
            return password_hash($password, PASSWORD_DEFAULT);
        }
        return hash('sha256', $password . '_zyro_salt_2026');
    }

    /**
     * Kiểm tra mật khẩu (hỗ trợ password_verify, sha256 muối, và sha256 seed data)
     */
    public function kiem_tra_mat_khau($password, $hash) {
        if (function_exists('password_verify')) {
            if (password_verify($password, $hash)) {
                return true;
            }
        }
        // Salted SHA-256
        if (hash('sha256', $password . '_zyro_salt_2026') === $hash) {
            return true;
        }
        // Raw SHA-256 (seed data 123456)
        if (hash('sha256', $password) === $hash) {
            return true;
        }
        return false;
    }

    /**
     * Kiểm tra email đã tồn tại trong bảng taikhoan chưa
     */
    public function kiem_tra_email_ton_tai($email) {
        $pdo = $this->ketnoi();
        $stmt = $pdo->prepare("SELECT user_id FROM taikhoan WHERE email = :email LIMIT 1");
        $stmt->bindValue(':email', trim(strtolower($email)), PDO::PARAM_STR);
        $stmt->execute();
        return ($stmt->fetch(PDO::FETCH_ASSOC)) ? true : false;
    }

    /**
     * Xử lý Đăng Nhập
     */
    public function dang_nhap($email, $password) {
        $pdo = $this->ketnoi();
        $email = trim(strtolower($email));
        $stmt = $pdo->prepare("SELECT * FROM taikhoan WHERE email = :email LIMIT 1");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $this->kiem_tra_mat_khau($password, $user['password'])) {
            unset($user['password']); // Bảo mật: Không lưu hash trong session
            return $user;
        }
        return false;
    }

    /**
     * Xử lý Đăng Ký Tài Khoản
     */
    public function dang_ky($name, $email, $password, $phone, $address, $phanquyen = 1) {
        $pdo = $this->ketnoi();
        $email = trim(strtolower($email));

        if ($this->kiem_tra_email_ton_tai($email)) {
            return 'email_exists';
        }

        $hash = $this->bam_mat_khau($password);
        $sql = "INSERT INTO taikhoan (name, email, password, phone, address, phanquyen, created_at) 
                VALUES (:name, :email, :password, :phone, :address, :phanquyen, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hash, PDO::PARAM_STR);
        $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindValue(':address', $address, PDO::PARAM_STR);
        $stmt->bindValue(':phanquyen', (int)$phanquyen, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return (int)$pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Lấy toàn bộ danh sách sản phẩm phục vụ Quản trị viên (Admin)
     */
    public function lay_danh_sach_tat_ca_san_pham() {
        $pdo = $this->ketnoi();
        $sql = "SELECT sp.*, th.name AS brand_name, lg.name AS category_name 
                FROM sanpham sp 
                LEFT JOIN sanpham_thuonghieu th ON sp.brand_id = th.brand_id 
                LEFT JOIN sanpham_loaigiay lg ON sp.category_id = lg.category_id 
                ORDER BY sp.product_id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm sản phẩm mới (Admin Post Sản Phẩm)
     */
    public function them_san_pham($name, $brandId, $categoryId, $price, $description, $imageUrl) {
        $pdo = $this->ketnoi();
        $sql = "INSERT INTO sanpham (name, brand_id, category_id, price, description, image_url, created_at) 
                VALUES (:name, :brand_id, :category_id, :price, :description, :image_url, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':brand_id', (int)$brandId, PDO::PARAM_INT);
        $stmt->bindValue(':category_id', (int)$categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':price', (float)$price);
        $stmt->bindValue(':description', $description, PDO::PARAM_STR);
        $stmt->bindValue(':image_url', $imageUrl, PDO::PARAM_STR);
        if ($stmt->execute()) {
            return (int)$pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Xóa sản phẩm theo ID (Admin Delete Sản Phẩm)
     */
    public function xoa_san_pham($productId) {
        $pdo = $this->ketnoi();
        $stmt = $pdo->prepare("DELETE FROM sanpham WHERE product_id = :id");
        $stmt->bindValue(':id', (int)$productId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Lấy danh sách bài viết / tin tức mới nhất
     */
    public function lay_danh_sach_bai_viet($limit = 6) {
        $pdo = $this->ketnoi();
        $sql = "SELECT bv.*, tk.name AS author_name 
                FROM baiviet bv 
                LEFT JOIN taikhoan tk ON bv.author_id = tk.user_id 
                ORDER BY bv.post_id DESC 
                LIMIT " . (int)$limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết 1 bài viết theo ID
     */
    public function lay_chi_tiet_bai_viet($postId) {
        $pdo = $this->ketnoi();
        $sql = "SELECT bv.*, tk.name AS author_name 
                FROM baiviet bv 
                LEFT JOIN taikhoan tk ON bv.author_id = tk.user_id 
                WHERE bv.post_id = :id 
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', (int)$postId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm bài viết mới (Admin Post Bài Viết)
     */
    public function them_bai_viet($title, $summary, $content, $imageUrl, $authorId) {
        $pdo = $this->ketnoi();
        $sql = "INSERT INTO baiviet (title, summary, content, image_url, author_id, created_at) 
                VALUES (:title, :summary, :content, :image_url, :author_id, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':summary', $summary, PDO::PARAM_STR);
        $stmt->bindValue(':content', $content, PDO::PARAM_STR);
        $stmt->bindValue(':image_url', $imageUrl, PDO::PARAM_STR);
        $stmt->bindValue(':author_id', (int)$authorId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return (int)$pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Lấy thông tin 1 đơn hàng theo mã orders_id
     */
    public function lay_thong_tin_don_hang($orderId) {
        $pdo = $this->ketnoi();
        $sql = "SELECT * FROM donhang WHERE orders_id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', (int)$orderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách sản phẩm trong 1 đơn hàng (kèm ảnh và thương hiệu)
     */
    public function lay_chi_tiet_san_pham_don_hang($orderId) {
        $pdo = $this->ketnoi();
        $sql = "SELECT ct.*, sp.name AS product_name, sp.image_url, th.name AS brand_name 
                FROM donhang_chitiet ct 
                LEFT JOIN sanpham sp ON ct.product_id = sp.product_id 
                LEFT JOIN sanpham_thuonghieu th ON sp.brand_id = th.brand_id 
                WHERE ct.order_id = :order_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':order_id', (int)$orderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Alias: Lấy chi tiết các món trong đơn hàng
     */
    public function lay_chi_tiet_don_hang($orderId) {
        return $this->lay_chi_tiet_san_pham_don_hang($orderId);
    }

    /**
     * Lấy toàn bộ lịch sử đơn hàng của 1 tài khoản (User ID)
     */
    public function lay_danh_sach_don_hang_user($userId) {
        $pdo = $this->ketnoi();
        $sql = "SELECT dh.*, COUNT(ct.product_id) AS total_items 
                FROM donhang dh 
                LEFT JOIN donhang_chitiet ct ON dh.orders_id = ct.order_id 
                WHERE dh.user_id = :user_id 
                GROUP BY dh.orders_id 
                ORDER BY dh.order_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tra cứu đơn hàng theo Mã đơn và Số điện thoại nhận hàng
     */
    public function tra_cuu_don_hang($orderId, $phone) {
        $pdo = $this->ketnoi();
        $phoneClean = preg_replace('/[^0-9]/', '', $phone);
        $sql = "SELECT * FROM donhang 
                WHERE orders_id = :id AND (receiver_phone = :phone OR REPLACE(receiver_phone, ' ', '') = :clean_phone) 
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', (int)$orderId, PDO::PARAM_INT);
        $stmt->bindValue(':phone', trim($phone), PDO::PARAM_STR);
        $stmt->bindValue(':clean_phone', $phoneClean, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Khách hàng hủy đơn hàng trực tuyến khi đơn hàng còn ở trạng thái 'Chờ xử lý'
     */
    public function huy_don_hang_khach($orderId, $userId = null, $phone = null) {
        $pdo = $this->ketnoi();
        
        // Kiểm tra quyền và trạng thái đơn hàng trước khi hủy
        $sqlCheck = "SELECT orders_id, status, user_id, receiver_phone FROM donhang WHERE orders_id = :id LIMIT 1";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->bindValue(':id', (int)$orderId, PDO::PARAM_INT);
        $stmtCheck->execute();
        $order = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return array('success' => false, 'message' => 'Không tìm thấy đơn hàng cần hủy!');
        }

        // Kiểm tra bảo mật quyền sở hữu đơn hàng
        if ($userId !== null && (int)$order['user_id'] !== (int)$userId) {
            return array('success' => false, 'message' => 'Bạn không có quyền thao tác trên đơn hàng này!');
        }
        if ($phone !== null) {
            $p1 = preg_replace('/[^0-9]/', '', $phone);
            $p2 = preg_replace('/[^0-9]/', '', $order['receiver_phone']);
            if ($p1 !== $p2) {
                return array('success' => false, 'message' => 'Số điện thoại xác nhận không chính xác!');
            }
        }

        // Chỉ cho phép hủy khi đang Chờ xử lý
        $statusRaw = trim($order['status']);
        $isPending = ($statusRaw === 'Chờ xử lý' || $statusRaw === 'Cho xu ly' || stripos($statusRaw, 'chờ') !== false || stripos($statusRaw, 'cho') !== false);
        if (!$isPending) {
            return array('success' => false, 'message' => 'Đơn hàng đang trong quá trình vận chuyển hoặc đã xử lý, không thể hủy trực tuyến!');
        }

        $sqlUpdate = "UPDATE donhang SET status = 'Đã hủy' WHERE orders_id = :id";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->bindValue(':id', (int)$orderId, PDO::PARAM_INT);
        if ($stmtUpdate->execute()) {
            return array('success' => true, 'message' => 'Đã hủy đơn hàng thành công!');
        }
        return array('success' => false, 'message' => 'Lỗi hệ thống khi cập nhật trạng thái đơn hàng!');
    }

    /**
     * Dành cho Admin: Lấy danh sách tất cả đơn hàng kèm bộ lọc trạng thái và tìm kiếm
     */
    public function lay_danh_sach_tat_ca_don_hang($statusFilter = 'all', $searchKeyword = '', $limit = 100, $offset = 0) {
        $pdo = $this->ketnoi();
        $params = array();
        
        $sql = "SELECT dh.*, tk.name AS user_fullname, tk.email AS user_email, 
                       COUNT(ct.product_id) AS total_items, 
                       COALESCE(SUM(ct.quantity), 0) AS total_quantity 
                FROM donhang dh 
                LEFT JOIN taikhoan tk ON dh.user_id = tk.user_id 
                LEFT JOIN donhang_chitiet ct ON dh.orders_id = ct.order_id 
                WHERE 1=1 ";
        
        // Lọc theo trạng thái
        $filterKey = strtolower(trim($statusFilter));
        if (!empty($filterKey) && $filterKey !== 'all') {
            if ($filterKey === 'dathanhtoan' || $filterKey === 'da thanh toan') {
                $sql .= " AND (dh.status = 'Đã thanh toán' OR dh.status LIKE '%Đã thanh toán%') ";
            } elseif ($filterKey === 'chothanhtoan' || $filterKey === 'cho thanh toan') {
                $sql .= " AND (dh.status = 'Chờ thanh toán' OR dh.status LIKE '%Chờ thanh toán%') ";
            } elseif ($filterKey === 'choxuly' || $filterKey === 'cho xu ly') {
                $sql .= " AND (dh.status = 'Chờ xử lý' OR (dh.status LIKE '%Chờ%' AND dh.status NOT LIKE '%thanh toán%')) ";
            } elseif ($filterKey === 'danggiao' || $filterKey === 'dang giao') {
                $sql .= " AND (dh.status = 'Đang giao' OR dh.status LIKE '%Đang%' OR dh.status LIKE '%dang%') ";
            } elseif ($filterKey === 'dagiao' || $filterKey === 'da giao') {
                $sql .= " AND (dh.status = 'Đã giao' OR dh.status LIKE '%Đã giao%' OR dh.status LIKE '%da giao%') ";
            } elseif ($filterKey === 'dahuy' || $filterKey === 'da huy') {
                $sql .= " AND (dh.status = 'Đã hủy' OR dh.status LIKE '%hủy%' OR dh.status LIKE '%huy%') ";
            } else {
                $sql .= " AND dh.status = :custom_status ";
                $params[':custom_status'] = $statusFilter;
            }
        }
        
        // Tìm kiếm theo từ khóa (Mã đơn hàng, tên khách hàng, số điện thoại)
        $search = trim($searchKeyword);
        if ($search !== '') {
            $cleanOrderId = preg_replace('/[^0-9]/', '', $search);
            if ($cleanOrderId !== '') {
                $sql .= " AND (dh.orders_id = :search_order_id OR dh.receiver_name LIKE :search_name OR dh.receiver_phone LIKE :search_phone) ";
                $params[':search_order_id'] = (int)$cleanOrderId;
                $params[':search_name'] = '%' . $search . '%';
                $params[':search_phone'] = '%' . $search . '%';
            } else {
                $sql .= " AND (dh.receiver_name LIKE :search_name OR dh.receiver_phone LIKE :search_phone) ";
                $params[':search_name'] = '%' . $search . '%';
                $params[':search_phone'] = '%' . $search . '%';
            }
        }
        
        $sql .= " GROUP BY dh.orders_id ORDER BY dh.order_date DESC, dh.orders_id DESC ";
        
        if ($limit > 0) {
            $sql .= " LIMIT " . (int)$offset . ", " . (int)$limit;
        }
        
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            if (is_int($val)) {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val, PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Dành cho Admin: Cập nhật trạng thái đơn hàng
     */
    public function cap_nhat_trang_thai_don_hang($orderId, $newStatus) {
        $pdo = $this->ketnoi();
        $orderId = (int)$orderId;
        $newStatus = trim($newStatus);
        
        // Danh sách trạng thái hợp lệ
        $validStatuses = array('Chờ xử lý', 'Chờ thanh toán', 'Đã thanh toán', 'Đang giao', 'Đã giao', 'Đã hủy');
        if (!in_array($newStatus, $validStatuses)) {
            return array('success' => false, 'message' => 'Trạng thái đơn hàng không hợp lệ!');
        }
        
        // Kiểm tra xem đơn hàng có tồn tại không
        $sqlCheck = "SELECT orders_id, status FROM donhang WHERE orders_id = :id LIMIT 1";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->bindValue(':id', $orderId, PDO::PARAM_INT);
        $stmtCheck->execute();
        $order = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            return array('success' => false, 'message' => 'Không tìm thấy đơn hàng #' . $orderId . '!');
        }
        
        $sqlUpdate = "UPDATE donhang SET status = :status WHERE orders_id = :id";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->bindValue(':status', $newStatus, PDO::PARAM_STR);
        $stmtUpdate->bindValue(':id', $orderId, PDO::PARAM_INT);
        
        if ($stmtUpdate->execute()) {
            return array(
                'success' => true, 
                'message' => 'Đã cập nhật đơn hàng #' . $orderId . ' sang trạng thái "' . $newStatus . '" thành công!',
                'old_status' => $order['status'],
                'new_status' => $newStatus
            );
        }
        
        return array('success' => false, 'message' => 'Lỗi hệ thống khi cập nhật trạng thái đơn hàng!');
    }

    /**
     * Dành cho Admin: Thống kê tổng số lượng đơn hàng theo từng trạng thái và tổng doanh thu
     */
    public function dem_don_hang_theo_trang_thai() {
        $pdo = $this->ketnoi();
        $sql = "SELECT status, COUNT(*) AS count_orders, SUM(total_price) AS sum_revenue 
                FROM donhang 
                GROUP BY status";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $counts = array(
            'all' => 0,
            'choxuly' => 0,
            'chothanhtoan' => 0,
            'dathanhtoan' => 0,
            'danggiao' => 0,
            'dagiao' => 0,
            'dahuy' => 0,
            'total_revenue' => 0,
            'delivered_revenue' => 0,
            'paid_revenue' => 0
        );
        
        if ($rows) {
            foreach ($rows as $row) {
                $status = trim($row['status']);
                $cnt = (int)$row['count_orders'];
                $rev = (float)$row['sum_revenue'];
                
                $counts['all'] += $cnt;
                $counts['total_revenue'] += $rev;
                
                if ($status === 'Đã thanh toán' || stripos($status, 'đã thanh toán') !== false || stripos($status, 'da thanh toan') !== false) {
                    $counts['dathanhtoan'] += $cnt;
                    $counts['paid_revenue'] += $rev;
                } elseif ($status === 'Chờ thanh toán' || stripos($status, 'chờ thanh toán') !== false || stripos($status, 'cho thanh toan') !== false) {
                    $counts['chothanhtoan'] += $cnt;
                } elseif ($status === 'Chờ xử lý' || stripos($status, 'chờ') !== false || stripos($status, 'cho') !== false) {
                    $counts['choxuly'] += $cnt;
                } elseif ($status === 'Đang giao' || stripos($status, 'đang') !== false || stripos($status, 'dang') !== false) {
                    $counts['danggiao'] += $cnt;
                } elseif ($status === 'Đã giao' || stripos($status, 'đã giao') !== false || stripos($status, 'da giao') !== false) {
                    $counts['dagiao'] += $cnt;
                    $counts['delivered_revenue'] += $rev;
                } elseif ($status === 'Đã hủy' || stripos($status, 'hủy') !== false || stripos($status, 'huy') !== false) {
                    $counts['dahuy'] += $cnt;
                }
            }
        }
        
        return $counts;
    }

    /**
     * Dành cho Admin: Thống kê tổng quan tài chính và hoạt động bán hàng theo khoảng thời gian
     */
    public function lay_thong_ke_tong_quan_tai_chinh($fromDate = '', $toDate = '') {
        $pdo = $this->ketnoi();
        $params = array();
        
        $whereSql = " WHERE 1=1 ";
        if (!empty($fromDate) && !empty($toDate)) {
            $whereSql .= " AND DATE(dh.order_date) BETWEEN :from_date AND :to_date ";
            $params[':from_date'] = $fromDate;
            $params[':to_date'] = $toDate;
        } elseif (!empty($fromDate)) {
            $whereSql .= " AND DATE(dh.order_date) >= :from_date ";
            $params[':from_date'] = $fromDate;
        } elseif (!empty($toDate)) {
            $whereSql .= " AND DATE(dh.order_date) <= :to_date ";
            $params[':to_date'] = $toDate;
        }

        // 1. Thống kê số lượng đơn và doanh thu từ bảng donhang
        $sqlOrders = "SELECT 
                        COUNT(dh.orders_id) AS total_orders,
                        COALESCE(SUM(dh.total_price), 0) AS total_gross_revenue,
                        COALESCE(SUM(CASE WHEN dh.status = 'Đã giao' OR dh.status LIKE '%Đã giao%' OR dh.status LIKE '%da giao%' THEN dh.total_price ELSE 0 END), 0) AS delivered_revenue,
                        COALESCE(SUM(CASE WHEN dh.status = 'Đã giao' OR dh.status LIKE '%Đã giao%' OR dh.status LIKE '%da giao%' THEN 1 ELSE 0 END), 0) AS delivered_orders,
                        COALESCE(SUM(CASE WHEN dh.status = 'Đang giao' OR dh.status LIKE '%Đang%' OR dh.status LIKE '%dang%' THEN 1 ELSE 0 END), 0) AS shipping_orders,
                        COALESCE(SUM(CASE WHEN dh.status = 'Chờ xử lý' OR dh.status LIKE '%Chờ%' OR dh.status LIKE '%cho%' THEN 1 ELSE 0 END), 0) AS pending_orders,
                        COALESCE(SUM(CASE WHEN dh.status = 'Đã hủy' OR dh.status LIKE '%hủy%' OR dh.status LIKE '%huy%' THEN 1 ELSE 0 END), 0) AS cancelled_orders,
                        COALESCE(SUM(CASE WHEN dh.status != 'Đã hủy' AND dh.status NOT LIKE '%hủy%' AND dh.status NOT LIKE '%huy%' THEN dh.total_price ELSE 0 END), 0) AS potential_revenue
                      FROM donhang dh " . $whereSql;

        $stmtOrders = $pdo->prepare($sqlOrders);
        foreach ($params as $k => $v) {
            $stmtOrders->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmtOrders->execute();
        $res = $stmtOrders->fetch(PDO::FETCH_ASSOC);

        // 2. Thống kê tổng số lượng giày bán ra từ bảng donhang_chitiet
        $sqlShoes = "SELECT COALESCE(SUM(ct.quantity), 0) AS total_shoes_sold 
                     FROM donhang_chitiet ct 
                     JOIN donhang dh ON ct.order_id = dh.orders_id " . $whereSql . "
                     AND (dh.status != 'Đã hủy' AND dh.status NOT LIKE '%hủy%' AND dh.status NOT LIKE '%huy%')";
        $stmtShoes = $pdo->prepare($sqlShoes);
        foreach ($params as $k => $v) {
            $stmtShoes->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmtShoes->execute();
        $resShoes = $stmtShoes->fetch(PDO::FETCH_ASSOC);

        $totalOrders = (int)$res['total_orders'];
        $deliveredOrders = (int)$res['delivered_orders'];
        $deliveredRevenue = (float)$res['delivered_revenue'];
        $potentialRevenue = (float)$res['potential_revenue'];
        $cancelledOrders = (int)$res['cancelled_orders'];
        $totalShoesSold = isset($resShoes['total_shoes_sold']) ? (int)$resShoes['total_shoes_sold'] : 0;

        // Tính tỷ lệ giao hàng thành công & AOV
        $successRate = ($totalOrders > 0) ? round(($deliveredOrders / $totalOrders) * 100, 1) : 0;
        $activeOrders = max(1, $totalOrders - $cancelledOrders);
        $avgOrderValue = ($deliveredOrders > 0) 
                       ? round($deliveredRevenue / $deliveredOrders) 
                       : (($potentialRevenue > 0) ? round($potentialRevenue / $activeOrders) : 0);

        return array(
            'total_orders' => $totalOrders,
            'delivered_orders' => $deliveredOrders,
            'shipping_orders' => (int)$res['shipping_orders'],
            'pending_orders' => (int)$res['pending_orders'],
            'cancelled_orders' => $cancelledOrders,
            'delivered_revenue' => $deliveredRevenue,
            'potential_revenue' => $potentialRevenue,
            'total_gross_revenue' => (float)$res['total_gross_revenue'],
            'total_shoes_sold' => $totalShoesSold,
            'success_rate' => $successRate,
            'avg_order_value' => $avgOrderValue
        );
    }

    /**
     * Dành cho Admin: Lấy xu hướng doanh thu và số lượng đơn hàng theo ngày
     */
    public function lay_doanh_thu_theo_thoi_gian($period = '7days', $fromDate = '', $toDate = '') {
        $pdo = $this->ketnoi();
        $params = array();
        
        $whereSql = " WHERE 1=1 ";
        if (!empty($fromDate) && !empty($toDate)) {
            $whereSql .= " AND DATE(dh.order_date) BETWEEN :from_date AND :to_date ";
            $params[':from_date'] = $fromDate;
            $params[':to_date'] = $toDate;
        } elseif ($period === 'today') {
            $whereSql .= " AND DATE(dh.order_date) = CURDATE() ";
        } elseif ($period === '7days') {
            $whereSql .= " AND dh.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ";
        } elseif ($period === '30days') {
            $whereSql .= " AND dh.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ";
        } elseif ($period === 'this_month') {
            $whereSql .= " AND YEAR(dh.order_date) = YEAR(CURDATE()) AND MONTH(dh.order_date) = MONTH(CURDATE()) ";
        }

        $sql = "SELECT 
                    DATE(dh.order_date) AS stat_date,
                    COUNT(dh.orders_id) AS order_count,
                    COALESCE(SUM(dh.total_price), 0) AS total_revenue,
                    COALESCE(SUM(CASE WHEN dh.status = 'Đã giao' OR dh.status LIKE '%Đã giao%' OR dh.status LIKE '%da giao%' THEN dh.total_price ELSE 0 END), 0) AS delivered_revenue,
                    COALESCE(SUM(CASE WHEN dh.status = 'Đã giao' OR dh.status LIKE '%Đã giao%' OR dh.status LIKE '%da giao%' THEN 1 ELSE 0 END), 0) AS delivered_count
                FROM donhang dh 
                " . $whereSql . "
                GROUP BY DATE(dh.order_date) 
                ORDER BY stat_date ASC";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Dành cho Admin: Lấy danh sách Top giày thể thao bán chạy nhất
     */
    public function lay_top_san_pham_ban_chay($limit = 5, $fromDate = '', $toDate = '') {
        $pdo = $this->ketnoi();
        $params = array();
        
        $whereSql = " WHERE (dh.status != 'Đã hủy' AND dh.status NOT LIKE '%hủy%' AND dh.status NOT LIKE '%huy%') ";
        if (!empty($fromDate) && !empty($toDate)) {
            $whereSql .= " AND DATE(dh.order_date) BETWEEN :from_date AND :to_date ";
            $params[':from_date'] = $fromDate;
            $params[':to_date'] = $toDate;
        }

        $sql = "SELECT 
                    ct.product_id,
                    COALESCE(sp.name, 'Giày Thể Thao ZYRO') AS product_name,
                    sp.image_url,
                    COALESCE(th.name, 'ZYRO') AS brand_name,
                    sp.price AS unit_price,
                    COALESCE(SUM(ct.quantity), 0) AS total_sold,
                    COALESCE(SUM(ct.quantity * ct.price), 0) AS total_revenue
                FROM donhang_chitiet ct 
                JOIN donhang dh ON ct.order_id = dh.orders_id 
                LEFT JOIN sanpham sp ON ct.product_id = sp.product_id 
                LEFT JOIN sanpham_thuonghieu th ON sp.brand_id = th.brand_id 
                " . $whereSql . "
                GROUP BY ct.product_id 
                ORDER BY total_sold DESC, total_revenue DESC 
                LIMIT " . (int)$limit;

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Dành cho Admin: Lấy cơ cấu phương thức thanh toán (COD vs VietQR MB Bank)
     */
    public function lay_co_cau_phuong_thuc_thanh_toan($fromDate = '', $toDate = '') {
        $pdo = $this->ketnoi();
        $params = array();
        
        $whereSql = " WHERE 1=1 ";
        if (!empty($fromDate) && !empty($toDate)) {
            $whereSql .= " AND DATE(dh.order_date) BETWEEN :from_date AND :to_date ";
            $params[':from_date'] = $fromDate;
            $params[':to_date'] = $toDate;
        }

        $sql = "SELECT 
                    payment_method,
                    COUNT(*) AS count_orders,
                    COALESCE(SUM(total_price), 0) AS sum_amount 
                FROM donhang dh 
                " . $whereSql . "
                GROUP BY payment_method";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = array(
            'cod_count' => 0,
            'cod_amount' => 0,
            'banking_count' => 0,
            'banking_amount' => 0,
            'other_count' => 0,
            'other_amount' => 0,
            'total_count' => 0
        );

        if ($rows) {
            foreach ($rows as $r) {
                $pm = strtoupper(trim($r['payment_method']));
                $cnt = (int)$r['count_orders'];
                $amt = (float)$r['sum_amount'];
                $result['total_count'] += $cnt;

                if (strpos($pm, 'COD') !== false || strpos($pm, 'TIỀN MẶT') !== false) {
                    $result['cod_count'] += $cnt;
                    $result['cod_amount'] += $amt;
                } elseif (strpos($pm, 'BANK') !== false || strpos($pm, 'VIETQR') !== false || strpos($pm, 'CHUYỂN KHOẢN') !== false) {
                    $result['banking_count'] += $cnt;
                    $result['banking_amount'] += $amt;
                } else {
                    $result['other_count'] += $cnt;
                    $result['other_amount'] += $amt;
                }
            }
        }

        return $result;
    }
}

// Giữ lại alias class khach_hang để không làm gãy code cũ nếu có
class khach_hang extends san_pham {}
?>
