<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Trang Chủ</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="../assets/js/action.js"></script>
</head>
<body>
<!-- Header -->
    <?php include ('includes/header.php'); ?>

<!-- Banner
    <div id="banner-khung">
        <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="banner">
                        <div class="box-banner1 text-banner">
                            KIỂU DÁNG MỚI ĐANG ĐƯỢC BÁN: GIẢM GIÁ TỚI 40%
                        </div>    
                        <div class="box-banner2">
                            <a href="#"><u>Hãy nhanh tay nào!</u></a>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="banner">
                        <div class="box-banner1 text-banner">
                            MIỄN PHÍ & TRẢ HÀNG MIỄN PHÍ TRONG VÒNG 30 NGÀY
                        </div>
                        <div class="box-banner2">
                            <a href="#"><u>Xem chi tiết</u></a>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="banner">
                        <div class="box-banner1 text-banner">
                            DI CHUYỂN, MUA SẮM, TÙY CHỈNH VÀ ĂN MỪNG CÙNG RYZO
                        </div>
                        <div class="box-banner2">
                            <a href="#"><u>Tham gia ngay</u></a>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev btn-prev-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
                <img src="../icon/icon-prev.png" class="icon-next-prev" alt="">
            </button>
            <button class="carousel-control-next btn-prev-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
                <img src="../icon/icon-next.png" class="icon-next-prev" alt="">
            </button>
        </div>
    </div> -->
<!-- Slide giới thiệu -->
<div id="carouselExampleDark" class="carousel carousel-dark slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active" data-bs-interval="10000">
                <a href="#">
                    <img src="assets/img/banner-nike-air-max-dn8.png" class="d-block w-100" id="banner-nike" alt="...">
                </a>
            </div>
            <div class="carousel-item" data-bs-interval="2000">
                <a href="#"><img src="assets/img/banner-converse-seasonal_color_eng_main_desktop.webp" class="d-block w-100" alt="..."></a>
            </div>
            <div class="carousel-item">
                <a href="#"><img src="assets/img/banner-converse-love_chuck_eng_main_desktop.webp" class="d-block w-100" alt="..."></a>
            </div>
        </div>
        <button class="carousel-control-prev" id="banner-prev" type="button" data-bs-target="#carouselExampleDark" data-bs-slide="prev">
            <img src="assets/icon/icon-prev.png" class="icon-next-prev" alt="">
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleDark" data-bs-slide="next">
            <img src="assets/icon/icon-next.png" class="icon-next-prev" alt="">
        </button>
    </div>

<!-- content -->
    <div class="container">
    <!-- 
        <section id="Content-1">
            <img src="../img/Nike1.jpg" id="img-nike1-home" alt="">
            <span>Mới nhất</span>
            <p><img src="../img/AIR-MAX-DN8.png" id="img-air-max-dn8" alt=""></p>
            <p>Giới thiệu chương tiếp theo của Dynamic Air. Cảm nhận.</p><br>
            <p><a href="#" id="Nut-xem-chi-tiet">Xem ngay</a></p>
        </section> -->

<!-- Reveiw sản phẩm -->
        <div class="container-reveiw-san-pham">
            <h3>Mua sắm theo biểu tượng: Nike</h3>
            <button class="prev-reveiw"><img class="icon-next-prev" src="../icon/icon-prev.png" alt=""></button>
            <button class="next-reveiw"><img class="icon-next-prev" src="../icon/icon-next.png" alt=""></button>
            <div class="box-reveiw">
                <div class="sp-reveiw"><a href="#"><img src="../img/review-sp-nike-VOMERO.png" alt="Vomero 5"></a></div>
                <div class="sp-reveiw"><a href="#"><img src="../img/review-sp-nike-PEGASUS41.png" alt="PEGASUS 41"></a></div>
                <div class="sp-reveiw"><a href="#"><img src="../img/review-sp-nike-BLAZER.png" alt="BLAZER"></a></div>
                <div class="sp-reveiw"><a href="#"><img src="../img/review-sp-nike-DUNKS.png" alt="DUNKS"></a></div>
                <div class="sp-reveiw"><a href="#"><img src="../img/review-sp-nike-METCON.png" alt="METCON"></a></div>
                <div class="sp-reveiw"><a href="#"><img src="../img/review-sp-nike-V2K.png" alt="V2K"></a></div>
                <div class="sp-reveiw"><a href="#"><img src="../img/review-sp-nike-AIR-JORDAN1.png" alt="AIR JORDAN 1"></a></div>
            </div>
        </div>

<!-- Sản phẩm trending Nike -->
        <div class="container-reveiw-san-pham">
            <h3>Xu hướng hiện nay: Nike</h3>
            <button class="prev-reveiw" id="trend-prev-nike"><img class="icon-next-prev" src="../icon/icon-prev.png" alt=""></button>
            <button class="next-reveiw" id="trend-next-nike"><img class="icon-next-prev" src="../icon/icon-next.png" alt=""></button>
            <div class="box-reveiw" id="box-trend-nike">
                <div class="sp-reveiw">
                    <a href="#">
                        <img src="../img/trending-NIKE+WAFFLE+NAV.png">
                        <div class="trend-sp-thong-tin">
                            <div class="trend-sp-name">Nike Waffle Nav</div>
                            <div class="trend-sp-mo-ta-ngan">Giày nam</div>
                            <div class="trend-sp-gia"><span>2,349,000</span>₫</div>
                        </div>
                    </a>
                    <div class="like-sp">
                        <div id="like-sp-icon" class="like-sp-icon"></div>
                    </div>
                </div>
                <div class="sp-reveiw">
                    <a href="#">
                        <img src="../img/trending-BLAZER+MID+'77+VNTG.png">
                        <div class="trend-sp-thong-tin">
                            <div class="trend-sp-name">Nike Blazer Mid '77 Vintage</div>
                            <div class="trend-sp-mo-ta-ngan">Giày nam</div>
                            <div class="trend-sp-gia"><span>2,929,000</span>₫</div>
                        </div>
                    </a>
                </div>
                <div class="sp-reveiw">
                    <a href="#">
                        <img src="../img/trending-W+NIKE+CORTEZ.png">
                        <div class="trend-sp-thong-tin">
                            <div class="trend-sp-name">Nike Cortez</div>
                            <div class="trend-sp-mo-ta-ngan">Giày nữ</div>
                            <div class="trend-sp-gia"><span>2,779,000</span>₫</div>
                        </div>
                    </a>
                </div>
                <div class="sp-reveiw">
                    <a href="#">
                        <img src="../img/trending-WMNS+AIR+JORDAN+1+MID.png">
                        <div class="trend-sp-thong-tin">
                            <div class="trend-sp-name">Air Jordan 1 Mid</div>
                            <div class="trend-sp-mo-ta-ngan">Giày nữ</div>
                            <div class="trend-sp-gia"><span>3,669,000</span>₫</div>
                        </div>
                    </a>
                </div>
                <div class="sp-reveiw">
                    <a href="#">
                        <img src="../img/trending-NIKE+PEGASUS+PREMIUM.png">
                        <div class="trend-sp-thong-tin">
                            <div class="trend-sp-name">Nike Pegasus Premium</div>
                            <div class="trend-sp-mo-ta-ngan">Giày chạy bộ nam</div>
                            <div class="trend-sp-gia"><span>6,179,000</span>₫</div>
                        </div>
                    </a>
                </div>
                <div class="sp-reveiw">
                    <a href="#">
                        <img src="../img/trending-NIKE+AIR+MORE+UPTEMPO+(GS).png">
                        <div class="trend-sp-thong-tin">
                            <div class="trend-sp-name">Nike Air More Uptempo</div>
                            <div class="trend-sp-mo-ta-ngan">Giày trẻ em lớn tuổi</div>
                            <div class="trend-sp-gia"><span>3,569,000</span>₫</div>
                        </div>
                    </a>
                </div>
                <div class="sp-reveiw">
                    <a href="#">
                        <img src="../img/trending-kid-NIKE+DYNAMO+2+EASYON+LIL+(TD).png">
                        <div class="trend-sp-thong-tin">
                            <div class="trend-sp-name">Nike Dynamo 2 EasyOn</div>
                            <div class="trend-sp-mo-ta-ngan">Giày bé/trẻ mới biết đi</div>
                            <div class="trend-sp-gia"><span>1,529,000</span>₫</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

<!-- Loại sản phẩm -->
        <div class="container-reveiw-san-pham">
            <h3>Mua sắm theo môn thể thao</h3>
            <button class="prev-reveiw"><img id="loai-the-thao-prev" class="icon-next-prev" src="../icon/icon-prev.png" alt=""></button>
            <button class="next-reveiw"><img id="loai-the-thao-next" class="icon-next-prev" src="../icon/icon-next.png" alt=""></button>
            <div class="box-reveiw" id="box-loai-the-thao">
                <div class="sp-reveiw"><a href="#"><img src="../img/nike-loaiTT-chay-bo.jpg"> <div class="cta-loai-the-thao">Chạy bộ</div></a></div>
                <div class="sp-reveiw"><a href="#"><img src="../img/nike-loaiTT-da-bong.jpg"> <div class="cta-loai-the-thao">Đá bóng</div></a></div>
                <div class="sp-reveiw"><a href="#"><img src="../img/nike-loaiTT-bong-ro.jpg"> <div class="cta-loai-the-thao">Bóng rổ</div></a></div>
                <div class="sp-reveiw"><a href="#"><img src="../img/nike-loaiTT-yoga.jpg"> <div class="cta-loai-the-thao">Yoga</div></a></div>
                <div class="sp-reveiw"><a href="#"><img src="../img/nike-loaiTT-gym.jpg"> <div class="cta-loai-the-thao">Gym</div></a></div>
                <div class="sp-reveiw"><a href="#"><img src="../img/nike-loaiTT-truot-van.jpg"> <div class="cta-loai-the-thao">Trượt ván</div></a></div>
                <div class="sp-reveiw"><a href="#"><img src="../img/nike-loaiTT-dance.jpg"> <div class="cta-loai-the-thao">Dance</div></a></div>
            </div>
        </div>

<!-- Sản phẩm trending converse -->
        <div class="container-reveiw-san-pham">
            <h3>Xu hướng hiện nay: Converse</h3>
            <button class="prev-reveiw" id="trend-prev-converse"><img class="icon-next-prev" src="../icon/icon-prev.png" alt=""></button>
            <button class="next-reveiw" id="trend-next-converse"><img class="icon-next-prev" src="../icon/icon-next.png" alt=""></button>
            <div class="box-reveiw" id="box-trend-converse">
                <div class="sp-reveiw">
                    <a href="#">
                        <img src="../img/trend-converse-chuk.webp">
                        <div class="trend-sp-thong-tin">
                            <div class="trend-sp-name">Chuck 70</div>
                            <div class="trend-sp-mo-ta-ngan">Giày nam</div>
                            <div class="trend-sp-gia"><span>2,000,000</span>₫</div>
                        </div>
                    </a>
                </div>
                <div class="sp-reveiw">
                    <a href="#">
                        <img src="../img/trend-converse-chuck-taylor-all-star.webp">
                        <div class="trend-sp-thong-tin">
                            <div class="trend-sp-name">Nike Blazer Mid '77 Vintage</div>
                            <div class="trend-sp-mo-ta-ngan">Giày nữ</div>
                            <div class="trend-sp-gia"><span>1,240,000</span>₫ - <span>1,550,000</span>₫</div>
                        </div>
                    </a>
                </div>
                <div class="sp-reveiw">
                    <a href="#">
                        <img src="../img/trend-converse-cruice.webp">
                        <div class="trend-sp-thong-tin">
                            <div class="trend-sp-name">Converse Cruise</div>
                            <div class="trend-sp-mo-ta-ngan">Giày nữ</div>
                            <div class="trend-sp-gia"><span>1,880,000</span>₫</div>
                        </div>
                    </a>
                </div>
                <div class="sp-reveiw">
                    <a href="#">
                        <img src="../img/trend-converse-chuck-70-2.webp">
                        <div class="trend-sp-thong-tin">
                            <div class="trend-sp-name">Chuck 70</div>
                            <div class="trend-sp-mo-ta-ngan">Giày nam</div>
                            <div class="trend-sp-gia"><span>1,600,000</span>₫</div>
                        </div>
                    </a>
                </div>
                <div class="sp-reveiw">
                    <a href="#">
                        <img src="../img/trend-converse-star-player-76-premium.webp">
                        <div class="trend-sp-thong-tin">
                            <div class="trend-sp-name">Star Player 76 Premium Canvas</div>
                            <div class="trend-sp-mo-ta-ngan">Giày chạy bộ nam</div>
                            <div class="trend-sp-gia"><span>6,179,000</span>₫</div>
                        </div>
                    </a>
                </div>
                <div class="sp-reveiw">
                    <a href="#">
                        <img src="../img/trend-converse-commer-des.webp">
                        <div class="trend-sp-thong-tin">
                            <div class="trend-sp-name">Converse x Comme des Garçons Kids Chuck 70</div>
                            <div class="trend-sp-mo-ta-ngan">Giày trẻ em lớn tuổi</div>
                            <div class="trend-sp-gia"><span>2,200,000</span>₫</div>
                        </div>
                    </a>
                </div>
                <div class="sp-reveiw">
                    <a href="#">
                        <img src="../img/trend-converse-commer-des2.webp">
                        <div class="trend-sp-thong-tin">
                            <div class="trend-sp-name">Converse x Comme des Garçons Kids Chuck 70</div>
                            <div class="trend-sp-mo-ta-ngan">Giày trẻ em lớn tuổi</div>
                            <div class="trend-sp-gia"><span>2,200,000</span>₫</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

<!-- Footer -->
    <?php include ('includes/footer.php'); ?>
</body>
</html>