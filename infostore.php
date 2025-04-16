<?php
	include_once "class/brand.php";
	$brand = new brand();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Store</title>
    <link rel="stylesheet" href="css/infostore.css">
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <?php
        include "layout/header.php"
    ?>
    <!-- Customer Photos -->
    <section class="customer-photos">
        <div class="container">
            <div class="photo-gallery">
                <img src="https://www.watchstore.vn/images/albums/resized/11-1871112100-737619434_1711878831.webp" alt="">
                <img src="https://www.watchstore.vn/images/albums/resized/10-1531806116-342350087_1711878663.webp" alt="">
                <img src="https://www.watchstore.vn/images/albums/resized/8-1684183425-2141744429-1_1711878532.webp" alt="">
                <img src="https://www.watchstore.vn/images/albums/resized/7-723871612-1337313133_1711878505.webp" alt="">
            </div>
        </div>
    </section>

    <section class="text1">
        <div class="set">THÀNH LẬP NĂM 2025</div>
        <div class="camket">CAM KẾT 100% HÀNG CHÍNH HÃNG</div>
        <div class="text">Trải qua nhiều năm thành lập, với những nỗ lực không ngừng, WatchStore đã gặt hái nhiều thành công trong hoạt động kinh doanh và chiếm lĩnh trên thị trường.</div>
        <div class="text">WatchStore đã đăng ký hoạt động thương mại điện tử với Bộ Công Thương (Xem giấy phép đăng ký TMĐT với Bộ Công Thương). Chúng tôi cam kết 100% sản phẩm chính hãng.</div>
        <div class="remem">Các cột mốc đáng ghi nhớ:</div>
        <ul class="milestone-list">
            <li><strong>Năm 2025:</strong> Trở thành đại lý bán lẻ chính thức của thương hiệu đồng hồ Casio do Công ty Cổ Phần Anh Khuê Watch chính nhánh.</li>
            <li><strong>Năm 2025:</strong> Được cấp giấy chứng nhận đại lý ủy quyền bán lẻ chính thức của các thương hiệu đồng hồ cao cấp Orient, Seiko.</li>
            <li><strong>Năm 2025:</strong> Chứng nhận đại lý chính hãng của các thương hiệu đồng hồ Frederique Constant, Swatch, Freelook, Daniel Klein.</li>
            <li><strong>Năm 2025:</strong> Đại lý ủy quyền chính thức của thương hiệu đồng hồ Citizen, Olym Pianus, Olympia Star, Ogival, Bentley, I&W Carnival.</li>
        </ul>
    </section>

    <section class="commit">
        <div class="card">
            <div class="number-circle">1</div>
            <h2 class="title">Chất lượng luôn hàng đầu</h2>
            <p class="description">Luôn mang đến cho khách hàng sản phẩm chất lượng tốt nhất</p>
        </div>
        <div class="card">
            <div class="number-circle">2</div>
            <h2 class="title">Cam kết luôn tận tâm</h2>
            <p class="description">Luôn đặt khách hàng là trung tâm trong mọi việc, có trách nhiệm với những sản phẩm bán ra</p>
        </div>
        <div class="card">
            <div class="number-circle">3</div>
            <h2 class="title">Chính sách thu hút nhân tài</h2>
            <p class="description">Luôn tạo môi trường làm việc tốt nhất, tạo nhiều cơ hội thể hiện năng lực, tạo dựng sự nghiệp</p>
        </div>
        <div class="card">
            <div class="number-circle">4</div>
            <h2 class="title">Luôn hoàn thiện và đổi mới</h2>
            <p class="description">Luôn nổ lực đổi mới, hoàn thiện dịch vụ khách hàng</p>
        </div>
        <div class="card">
            <div class="number-circle">5</div>
            <h2 class="title">Phát triển bền vững</h2>
            <p class="description">Luôn hợp tác với những đại lý, nhà phân phối, xây dựng thị trường đồng hồ chính hãng trong sạch</p>
        </div>
    </section>
    
    <section class="customer-photos-x">
        <div class="set">WATCHSTORE</div>
        <div class="camket">GIÁ TRỊ CỐT LÕI DOANH NGHIỆP</div>
        <div class="containe-xr">
                <div class="img-1">
                    <img src="https://www.watchstore.vn/images/albums/resized/11-1871112100-737619434_1711878831.webp" alt="" class="img1">
                    <img src="https://www.watchstore.vn/images/albums/resized/10-1531806116-342350087_1711878663.webp" alt="" class="img2">
                </div>
                <div class="img-2">
                    <img src="https://www.watchstore.vn/images/albums/resized/8-1684183425-2141744429-1_1711878532.webp" alt="" class="img3">
                    <img src="https://www.watchstore.vn/images/albums/resized/7-723871612-1337313133_1711878505.webp" alt="" class="img4">
                </div>
        </div>
    </section>
    <?php
        include "layout/footer.php"
    ?>
</body>
</html>