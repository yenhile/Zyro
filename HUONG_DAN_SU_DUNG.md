# 📖 TÀI LIỆU HƯỚNG DẪN THAO TÁC & KIỂM TRA NGHIỆP VỤ HỆ THỐNG ZYRO
> **Hệ Thống Thương Mại Điện Tử Bán Giày Thể Thao — ZYRO FOOTWEAR**  
> *Đồ án / Bài tập lớn môn: Phát triển Web — Trường Đại học Công nghiệp TP.HCM (IUH)*  
> *Công nghệ nền tảng: PHP Native (PDO Prepared Statements), CSDL MySQL (zyro_db), Bootstrap 5, Cổng thanh toán VietQR / PayOS*

---

## 📌 PHẦN 1: THÔNG TIN MÔI TRƯỜNG & TÀI KHOẢN TRUY CẬP

### 1.1. Đường dẫn truy cập hệ thống
* **Truy cập nội bộ (Localhost WAMP):**  
  👉 `http://localhost/Zyro/`  
  👉 Trang quản trị: `http://localhost/Zyro/admin/`
* **Truy cập trực tuyến công khai (Cloudflare Tunnel Live):**  
  👉 `https://geographical-dancing-harry-steady.trycloudflare.com/Zyro/`  
  👉 Trang quản trị: `https://geographical-dancing-harry-steady.trycloudflare.com/Zyro/admin/`

---

### 1.2. Danh sách tài khoản thử nghiệm có sẵn trong CSDL
| Loại tài khoản | Email đăng nhập | Mật khẩu | Quyền hạn (`phanquyen`) | Ghi chú |
| :--- | :--- | :--- | :---: | :--- |
| **Quản trị viên (Admin)** | `admin@zyro.vn` | `123456` | `0` | Toàn quyền quản trị kho hàng, đơn hàng, thống kê, bài viết |
| **Khách hàng 1** | `nam@gmail.com` | `123456` | `1` | Khách hàng quen, có sẵn lịch sử đơn hàng |
| **Khách hàng 2** | `testonline2026@zyro.vn` | `123456` | `1` | Tài khoản chuyên test quy trình thanh toán PayOS |
| **Khách hàng mới** | *Tự đăng ký* | *Tự đặt* | `1` | Kiểm tra luồng đăng ký tài khoản mới trực tiếp |

---

## 🛍️ PHẦN 2: HƯỚNG DẪN THAO TÁC & CHECK NGHIỆP VỤ KHÁCH HÀNG (CLIENT FLOW)

### BƯỚC 1: Khám phá Trang chủ, Tìm kiếm & Lọc Sản phẩm
1. **Truy cập trang chủ:** Mở trình duyệt vào `http://localhost/Zyro/index.php`.
2. **Kiểm tra Banner Hero & Danh mục nổi bật:**
   * Lướt xem các slider/banner xu hướng thể thao (Nike, Converse, Jordan).
   * Bấm vào các nút danh mục nhanh: *Giày chạy bộ, Giày đá banh, Giày bóng rổ, Lifestyle, Tennis*.
3. **Thao tác Tìm kiếm & Lọc sản phẩm:**
   * Tại thanh tìm kiếm trên Header: Gõ từ khóa ví dụ `Nike` hoặc `Air Max` hoặc `Converse` $\rightarrow$ Nhấn **Tìm kiếm**.
   * Kiểm tra kết quả: Hệ thống chỉ hiển thị đúng các mẫu giày khớp với từ khóa tìm kiếm.
   * Lọc theo Thương hiệu hoặc Danh mục: Nhấp vào menu **Thương hiệu** (Nike, Converse, Jordan) $\rightarrow$ Kiểm tra danh sách hiển thị đúng thương hiệu được chọn.

---

### BƯỚC 2: Xem Chi tiết Sản phẩm & Đánh giá
1. Từ danh sách sản phẩm, nhấp vào một sản phẩm bất kỳ (ví dụ: *Nike Air Zoom Pegasus 40* hoặc *Converse Chuck 70 Vintage*).
2. **Kiểm tra thông tin chi tiết:**
   * Hình ảnh sản phẩm chất lượng cao (không bị vỡ ảnh, không bị lỗi 404).
   * Giá tiền hiển thị định dạng VNĐ chuẩn (ví dụ: `3,590,000 đ`).
   * Đoạn mô tả công nghệ đệm giày, chất liệu, tính năng vận động.
3. **Thao tác chọn biến thể:**
   * Chọn kích cỡ giày (Size): `38`, `39`, `40`, `41`, `42`, `43`.
   * Chọn số lượng cần mua (mặc định là `1`, có thể bấm `+` hoặc `-`).
4. **Kiểm tra khu vực Đánh giá khách hàng:**
   * Kéo xuống tab "Đánh giá & Bình luận" $\rightarrow$ Xem các nhận xét sao thực tế từ CSDL.

---

### BƯỚC 3: Thao tác Giỏ hàng (Shopping Cart)
1. **Thêm vào giỏ:**
   * Tại trang chi tiết, nhấn nút **"Thêm vào giỏ hàng"** $\rightarrow$ Hệ thống hiển thị thông báo popup hoặc toast thành công.
   * Biểu tượng Giỏ hàng trên Header tự động nhảy số lượng sản phẩm.
2. **Vào trang Giỏ hàng:** Nhấp vào icon Giỏ hàng trên Header hoặc truy cập `pages/GioHang.php`.
3. **Kiểm tra các thao tác chỉnh sửa giỏ hàng:**
   * Thay đổi số lượng sản phẩm $\rightarrow$ Kiểm tra tổng tiền từng món và **Tổng thành tiền** tự động cập nhật chính xác.
   * Bấm nút **Xóa** một món hàng $\rightarrow$ Mặt hàng biến mất khỏi giỏ hàng ngay lập tức.
   * Nhấn nút **"Tiến hành Thanh toán"** để chuyển sang trang đặt hàng.

---

### BƯỚC 4: Đăng ký & Đăng nhập Tài khoản
1. **Đăng xuất (nếu đang đăng nhập):** Bấm vào tên tài khoản góc phải $\rightarrow$ Chọn **Đăng xuất**.
2. **Kiểm tra Đăng ký (`pages/DangKy.php`):**
   * Nhấp nút **Đăng ký** trên menu.
   * Nhập Họ tên, Email mới (ví dụ: `khachhangmoi@gmail.com`), Số điện thoại, Mật khẩu $\rightarrow$ Bấm **Tạo tài khoản**.
   * Hệ thống tự động mã hóa mật khẩu và tạo bản ghi trong bảng `taikhoan`.
3. **Kiểm tra Đăng nhập (`pages/DangNhap.php`):**
   * Nhập email và mật khẩu vừa tạo (hoặc dùng `nam@gmail.com` / `123456`).
   * Đăng nhập thành công: Menu Header hiển thị lời chào `Xin chào, [Tên khách hàng]` cùng dropdown quản lý tài khoản.

---

### BƯỚC 5: Đặt hàng & Nghiệp vụ Thanh toán (2 Phương thức)
Truy cập trang thanh toán: `pages/ThanhToan.php`.
1. **Điền thông tin giao hàng:**
   * Họ tên người nhận: `Nguyễn Văn A`
   * Số điện thoại: `0987654321`
   * Địa chỉ nhận hàng: `12 Nguyễn Văn Bảo, Phường 4, Gò Vấp, TP.HCM`
   * Ghi chú giao hàng: `Giao giờ hành chính`
2. **Thử nghiệm Kịch bản A: Thanh toán COD (Tiền mặt khi nhận hàng)**
   * Chọn phương thức: **"Thanh toán khi nhận hàng (COD)"**.
   * Nhấn nút **"Xác nhận đặt hàng"**.
   * **Kết quả cần kiểm tra:**
     * Chuyển hướng sang trang **Đặt hàng thành công** (`pages/DatHangThanhCong.php`).
     * Hiển thị đầy đủ: Mã đơn hàng, Tên người nhận, Tổng tiền, Trạng thái: **"Chờ xử lý"**.
     * Giỏ hàng được làm rỗng.
     * CSDL MySQL bảng `donhang` ghi nhận 1 bản ghi mới với `payment_method = 'COD'` và `status = 'Chờ xử lý'`.
3. **Thử nghiệm Kịch bản B: Thanh toán trực tuyến VietQR / PayOS**
   * Cho một sản phẩm vào giỏ hàng và quay lại trang Thanh toán.
   * Chọn phương thức: **"Chuyển khoản / Quét mã QR (PayOS)"**.
   * Nhấn nút **"Xác nhận thanh toán"**.
   * **Kết quả cần kiểm tra:**
     * Chuyển hướng sang trang hiển thị mã QR: `pages/ThanhToanPayOS.php`.
     * Hiển thị mã QR VietQR chuẩn ngân hàng có logo NAPAS/Ngân hàng kèm:
       * Tên chủ tài khoản & Số tài khoản.
       * Số tiền chính xác của đơn hàng.
       * **Mã ghi chú chuyển khoản (Payment Code):** Ví dụ `ZYRO DH15`.
     * Trang web có vòng tròn chờ giao dịch với cơ chế kiểm tra đối soát tự động mỗi 3 giây.
     * Khi chuyển khoản thực tế qua App ngân hàng hoặc khi Webhook PayOS kích hoạt:
       * Trang tự động phát hiện giao dịch thành công và chuyển ngay sang trang Hoàn tất đơn hàng với trạng thái: **"Đã thanh toán qua PayOS"**.

---

### BƯỚC 6: Theo dõi Đơn hàng của tôi (`pages/DonHangCuaToi.php`)
1. Bấm vào menu cá nhân góc phải $\rightarrow$ Chọn **"Đơn hàng của tôi"**.
2. **Kiểm tra thông tin danh sách:**
   * Hiển thị toàn bộ các đơn hàng do tài khoản hiện tại đã đặt.
   * Có huy hiệu (badge) màu sắc phân biệt trạng thái rõ ràng:
     * 🟡 *Chờ xử lý* (Đơn COD mới đặt)
     * 🟢 *Đã thanh toán* (Đơn chuyển khoản PayOS thành công)
     * 🔵 *Đang giao hàng* / *Đã giao hàng*
   * Nhấn nút **"Xem chi tiết"** để xem lại danh sách giày, size giày, số lượng và tổng tiền.

---

### BƯỚC 7: Đọc Cẩm nang & Tin tức (`pages/TinTuc.php`)
1. Nhấp vào mục **Tin tức / Cẩm nang** trên Header.
2. Kiểm tra hiển thị danh sách 5 bài viết blog chất lượng:
   * *Câu chuyện khởi nguồn của ZYRO.*
   * *Hướng dẫn chọn size giày thể thao chuẩn xác.*
   * *Top 5 mẫu Sneaker Retro kinh điển.*
3. Nhấp vào từng bài để đọc chi tiết nội dung, hình ảnh minh họa và tác giả.

---

## 🛠️ PHẦN 3: HƯỚNG DẪN THAO TÁC & CHECK NGHIỆP VỤ QUẢN TRỊ VIÊN (ADMIN FLOW)

### BƯỚC 1: Đăng nhập Trang Quản trị (Admin Guard)
1. Truy cập đường dẫn: `http://localhost/Zyro/admin/` (hoặc link online Cloudflare `/Zyro/admin/`).
2. **Kiểm tra cơ chế bảo mật phân quyền:**
   * Nếu chưa đăng nhập hoặc đăng nhập bằng tài khoản khách hàng thường $\rightarrow$ Hệ thống tự động chặn và chuyển hướng về trang đăng nhập kèm cảnh báo.
3. Đăng nhập bằng tài khoản Quản trị viên:
   * **Email:** `admin@zyro.vn`
   * **Mật khẩu:** `123456`
4. Đăng nhập thành công: Chuyển hướng vào **Admin Dashboard**.

---

### BƯỚC 2: Kiểm tra Bảng điều khiển Tổng quan (`admin/index.php`)
1. **Kiểm tra 4 thẻ chỉ số thống kê (Cards):**
   * 👟 **Tổng sản phẩm:** Hiển thị số lượng sản phẩm hiện có trong kho (15 sản phẩm).
   * 📦 **Tổng đơn hàng:** Hiển thị tổng số đơn đã tiếp nhận từ khách hàng.
   * 💰 **Tổng doanh thu:** Tổng tiền tích lũy từ các đơn hàng đã đặt.
   * 👥 **Thành viên:** Số lượng tài khoản người dùng trong hệ thống.
2. **Danh sách đơn hàng mới nhất:** Bảng hiển thị nhanh các đơn hàng vừa phát sinh để Admin kịp thời xử lý.

---

### BƯỚC 3: Quản lý Kho Sản phẩm (`admin/sanpham.php`)
1. **Xem danh sách sản phẩm:**
   * Bảng hiển thị hình ảnh thu nhỏ, tên giày, thương hiệu, danh mục, giá bán và ngày tạo.
2. **Thêm mới sản phẩm (`admin/them_sanpham.php`):**
   * Nhấn nút **"Thêm sản phẩm mới"**.
   * Nhập Tên sản phẩm: `Nike Invincible 3 Test`
   * Chọn Thương hiệu: `Nike` | Loại giày: `Giày chạy bộ`
   * Nhập Giá: `4200000`
   * Nhập Mô tả: `Dòng giày chạy êm ái nhất phân khúc.`
   * Chọn ảnh đại diện từ máy tính $\rightarrow$ Nhấn **Lưu sản phẩm**.
   * **Kiểm tra:** Sản phẩm mới xuất hiện ngay trên danh sách Admin và ra trang chủ khách hàng kiểm tra thấy xuất hiện ngay lập tức!
3. **Sửa thông tin sản phẩm:**
   * Nhấn nút **Sửa** (icon cây bút) $\rightarrow$ Đổi giá bán hoặc đổi mô tả $\rightarrow$ Nhấn **Cập nhật** $\rightarrow$ Kiểm tra thông tin đã đổi trong CSDL.
4. **Xóa sản phẩm:**
   * Nhấn nút **Xóa** sản phẩm thử nghiệm vừa tạo $\rightarrow$ Xác nhận $\rightarrow$ Sản phẩm bị xóa an toàn khỏi CSDL.

---

### BƯỚC 4: Quản lý & Xử lý Đơn hàng (`admin/donhang.php`)
1. **Xem danh sách đơn hàng:**
   * Danh sách đầy đủ: Mã đơn, Khách hàng, Số điện thoại, Tổng tiền, Phương thức thanh toán (COD / PAYOS), Trạng thái đơn, Ngày đặt.
   * Lọc đơn hàng theo trạng thái (*Chờ xử lý, Đang giao hàng, Đã thanh toán, Đã giao hàng, Đã hủy*).
2. **Xem chi tiết & Cập nhật trạng thái (`admin/chitiet_donhang.php`):**
   * Nhấn nút **"Xem chi tiết"** tại một đơn hàng bất kỳ.
   * Kiểm tra thông tin người nhận hàng, địa chỉ, ghi chú.
   * Kiểm tra bảng chi tiết mặt hàng: tên giày, hình ảnh, size giày, số lượng, đơn giá, thành tiền.
   * **Cập nhật trạng thái đơn:**
     * Tại mục "Cập nhật trạng thái", chọn **"Đang giao hàng"** hoặc **"Đã giao hàng"** $\rightarrow$ Bấm **Lưu thay đổi**.
     * **Kiểm tra:** Đăng nhập tài khoản khách hàng tương ứng vào mục "Đơn hàng của tôi", trạng thái đơn hàng của khách hàng cũng được cập nhật đồng bộ 100%!

---

### BƯỚC 5: Báo cáo Thống kê Doanh thu Đơn hàng (`admin/thongke.php`)
1. Truy cập menu **"Thống kê"** trên thanh điều hướng Admin.
2. **Kiểm tra các biểu đồ & bảng biểu phân tích:**
   * **Lọc theo khoảng thời gian:** Chọn *Từ ngày* và *Đến ngày* $\rightarrow$ Bấm **"Lọc thống kê"** để xem doanh số trong kỳ.
   * **Thống kê theo phương thức thanh toán:** Phân tích tỷ trọng doanh thu giữa phương thức **COD** và **PayOS**.
   * **Top sản phẩm bán chạy nhất:** Xếp hạng các mẫu giày có số lượng bán và doanh thu cao nhất.
   * **Tỷ lệ trạng thái đơn hàng:** Biểu đồ trực quan tỷ lệ đơn thành công, đang giao và chờ xử lý.

---

### BƯỚC 6: Quản lý Danh mục & Bài viết Blog
1. **Quản lý Danh mục (`admin/danhmuc.php`):** Thêm, sửa tên loại giày hoặc thương hiệu phân phối.
2. **Quản lý Bài viết (`admin/baiviet.php`):** Đăng tải bài viết hướng dẫn mới, biên tập nội dung cẩm nang chăm sóc giày thể thao.

---

## 🎯 PHẦN 4: BẢNG KIỂM TRA ĐỐI CHIẾU 10 TIÊU CHÍ BÀI TẬP LỚN (EVALUATION CHECKLIST)

| STT | Tiêu chí yêu cầu môn học | Tình trạng | Vị trí kiểm tra / File mã nguồn |
| :---: | :--- | :---: | :--- |
| **1** | Giao diện Responsive chuẩn Bootstrap 5, thẩm mỹ cao | ✅ ĐẠT | Toàn bộ giao diện client & admin |
| **2** | Trang chủ & Tìm kiếm / Lọc sản phẩm đa tiêu chí | ✅ ĐẠT | `index.php`, `class/classProduct.php` |
| **3** | Trang chi tiết sản phẩm, chọn size, số lượng, đánh giá | ✅ ĐẠT | `pages/ChiTietSanPham.php` |
| **4** | Quản lý Giỏ hàng qua Session hoàn chỉnh (+/-/xóa) | ✅ ĐẠT | `pages/GioHang.php` |
| **5** | Đăng ký, Đăng nhập, Phân quyền bảo mật (Bcrypt/SHA) | ✅ ĐẠT | `pages/DangNhap.php`, `pages/DangKy.php` |
| **6** | Đặt hàng & Lưu trữ CSDL toàn vẹn (Khóa ngoại CASCADE) | ✅ ĐẠT | `pages/ThanhToan.php`, `bảng donhang_chitiet` |
| **7** | Tích hợp Thanh toán trực tuyến VietQR / PayOS tự động | ✅ ĐẠT | `pages/ThanhToanPayOS.php`, `api/payos_webhook.php` |
| **8** | Quản lý Lịch sử đơn hàng của người dùng | ✅ ĐẠT | `pages/DonHangCuaToi.php` |
| **9** | Quản trị Admin: CRUD Sản phẩm, Quản lý Đơn hàng | ✅ ĐẠT | `admin/sanpham.php`, `admin/donhang.php` |
| **10** | Trang Báo cáo Thống kê Doanh thu chuyên sâu cho Admin | ✅ ĐẠT | `admin/thongke.php` |

---

## 💡 PHẦN 5: XỬ LÝ SỰ CỐ NHANH (TROUBLESHOOTING & FAQS)

* **Hỏi: Khi truy cập báo lỗi "Lỗi kết nối CSDL"?**  
  👉 **Đáp:** Hãy chắc chắn phần mềm WAMP / XAMPP đang chạy và biểu tượng MySQL có màu xanh (cổng 3306). Kiểm tra tệp `class/classDatabase.php` đang để `$host = 'localhost'`, `$database = 'zyro_db'`.

* **Hỏi: Khi chạy Cloudflare Tunnel bị lỗi `Error 1033`?**  
  👉 **Đáp:** Do cửa sổ chạy tunnel bị đóng hoặc kết nối mạng ngắt quãng. Chỉ cần mở lại terminal và chạy `.\cloudflared.exe tunnel --url http://localhost` là có link mới ngay lập tức.

* **Hỏi: Làm sao để import lại dữ liệu gốc ban đầu nếu lỡ xóa nhầm sản phẩm?**  
  👉 **Đáp:** Mở **phpMyAdmin** $\rightarrow$ Chọn database `zyro_db` $\rightarrow$ Nhấn **Import** $\rightarrow$ Chọn tệp `CSDL/zyro_db.sql` có sẵn trong thư mục dự án để phục hồi 100% dữ liệu gốc bất kỳ lúc nào.

---
*Tài liệu được khởi tạo và đồng bộ tự động theo chuẩn quy tắc kiến trúc dự án ZYRO.*
