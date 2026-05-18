<?php
include("zyro-class.php");

class khach_hang extends Zyro
{
    public function xuat_san_pham()
    {   
        $pdo = $this->ketnoi();
        
        $loai = $_GET['loai'];
        // echo $loai;

        if ($loai == 'all')
        {
            $sql = "SELECT hinh, tensp, mota, gia FROM sanpham  ORDER BY tensp ASC";
        }
        else 
        {
            $sql = "SELECT hinh, tensp, mota, gia FROM sanpham WHERE mota = '$loai' ORDER BY tensp ASC";
        }

        $result = $pdo->query($sql); // Dùng query() thay vì prepare() vì không có tham số

        if ($result) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) 
            {
                $hinh = $row['hinh'];
                $tensp = $row['tensp'];
                $mota = $row['mota'];
                $gia = number_format($row['gia'], 0, '.'); // Định dạng giá tiền

                echo    '<div class="sanpham-box">
                            <a href="#">
                                <img src="../img/'. htmlspecialchars($hinh) .'" alt="'. htmlspecialchars($tensp) .'">
                                <div class="sanpham-thongtin">
                                    <p class="sp-name">'. htmlspecialchars($tensp) .'</p>
                                    <div class="sp-mo-ta-ngan">'. htmlspecialchars($mota) .'</div>
                                    <div class="sp-gia"><span>'. $gia .'</span>₫</div>
                                </div>
                            </a>
                            <div class="like-sp">
                                <div id="like-sp-icon" class="like-sp-icon"></div>
                            </div>
                        </div>';
            }
        } 
        else 
        {
            echo "Không có dữ liệu!";
        }
    }

}
?>
