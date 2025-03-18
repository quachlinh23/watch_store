<?php
	include_once "class/brand.php";
	$brand = new brand();
?>
<!DOCTYPE HTML>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details</title>
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/details.css">
    <link rel="stylesheet" href="css/test_1.css">
    <script src="script.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="js/details.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			font-family: Arial, sans-serif;
		}
    </style>
</head>
<body>
    <?php include 'layout/header.php';?>
    <div class="container"> 
        <div class="breadcrumbs_wrapper">
            <ul class="breadcrumb">
                <li class="breadcrumb__item"><a href="index.php"><i class="fas fa-home home-icon"></i></a></li>
                <pre class="items">   >   </pre>
                <li class="breadcrumb__item product-name">Đồng hồ casio</li>
            </ul>
        </div>
        <!-- Chi tiết sản phẩm -->
        <div class="product-detail">
            <div class="product-images">
                <div class="main_product">
                    <img id="mainImage" src="images/bannercasio.png" alt="Sản phẩm chính">
                    <button class="zoom-btn" title="Phóng to"><i class="fas fa-expand"></i></button>
                
                    <!-- Hộp hiển thị ảnh phóng to -->
                    <div class="sub_zoom" id="subZoom">
                        <span class="close_zoom">&times;</span>
                        <img id="zoomedImage" src="images/bannercasio.png" alt="Phóng to">
                    </div>
                </div>

                <div class="thumbnail-container">
                    <button class="nav-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
                    <div class="thumbnails" id="thumbnails">
                        <img class="thumb" src="images/sanphamdemo.jpg" onclick="changeImage(this)">
                        <img class="thumb" src="images/sanphamdemo_1.png" onclick="changeImage(this)">
                        <img class="thumb" src="images/sanphamdemo.jpg" onclick="changeImage(this)">
                        <img class="thumb" src="images/sanphamdemo_1.png" onclick="changeImage(this)">
                        <img class="thumb" src="images/sanphamdemo.jpg" onclick="changeImage(this)">
                    </div>
                    <button class="nav-btn next-btn"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>

            <div class="product-info">
                <div class="product-info_top">
                    <div class="banner_wrapper">
                        <img class="banner_brand" alt="Thương hiệu" src="images/bannergiamgia.png">
                    </div>
                </div>
                <div class="product-details">
                    <h2 class="name">Adidas UltraBoost 2024</h2>
                    <div class="line"></div>
                    <p class="desc">Mẫu giày thể thao cao cấp với thiết kế hiện đại, mang lại cảm giác thoải mái và thời trang.</p>
                    <div class="brand"><strong>Thương hiệu:</strong> Adidas</div>
                    <div class="price"><strong>Giá bán: </strong><span>2.500.000₫</span></div>
                    <div class="conlai"><strong>Còn lại:</strong> <span>10 sản phẩm</span></div>

                    <form class="cart-options">
                        <div class="quantity">
                            <p class="soluong"><strong>Số lượng:</strong></p>
                            <button class="qty-btn" onclick="event.preventDefault(); updateQuantity(-1)">-</button>
                            <input type="number" id="quantity" value="1" min="1">
                            <button class="qty-btn" onclick="event.preventDefault(); updateQuantity(1)">+</button>
                        </div>
                        <div class="buttons">
                            <button class="add-to-cart"><i class="fa fa-shopping-cart"></i><br>Thêm vào giỏ</button>
                            <button class="buy-now"><a href="buy_now.php" style="text-decoration: none; color: white;">⚡ Mua ngay</a></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Tabs điều hướng -->
        <div class="tabs">
            <button class="tab-btn active" onclick="openTab('specs')">📌 Thông số</button>
            <button class="tab-btn" onclick="openTab('guide')">📏 Hướng dẫn chọn size</button>
            <button class="tab-btn" onclick="openTab('reviews')">⭐ Đánh giá</button>
        </div>

        <!-- Nội dung tab -->
        <div class="tab-content active" id="specs">
            <h3>Thông số kỹ thuật</h3>
            <ul>
                <li>Chất liệu: Vải lưới thoáng khí</li>
                <li>Đế: Cao su bền</li>
                <li>Trọng lượng: 250g</li>
                <li>Màu sắc: Trắng / Đen / Xanh</li>
            </ul>
        </div>

        <div class="tab-content" id="guide">
            <h3>Hướng dẫn chọn size</h3>
            <p>Hãy đo chiều dài bàn chân và đối chiếu với bảng size bên dưới:</p>
            <table>
                <tr><th>Chiều dài (cm)</th><th>Size EU</th></tr>
                <tr><td>22.5</td><td>36</td></tr>
                <tr><td>23.5</td><td>38</td></tr>
                <tr><td>24.5</td><td>40</td></tr>
                <tr><td>25.5</td><td>42</td></tr>
            </table>
        </div>

        <div class="tab-content" id="reviews">
            <h3>Đánh giá sản phẩm</h3>
            <p>⭐⭐⭐⭐⭐ - Tuyệt vời! Giày rất nhẹ và thoáng khí.</p>
            <p>⭐⭐⭐⭐☆ - Rất đẹp, nhưng cần thêm màu sắc khác.</p>
            <p>⭐⭐⭐☆☆ - Tạm ổn, hơi chật với size 42.</p>
        </div>
    </div>

    <section class="committion">
		<div class="img_1">
			<img src="images/real_1.png" alt="">
			<p>100% Chính hãng</p>
		</div>
		<div class="img_2">
			<img src="images/deliver.png" alt="">
			<p>Miễn phí vận chuyển trên toàn quốc</p>
		</div>
		<div class="img_3">
			<img src="images/deliver.png" alt="">
			<p>Bảo hành chính hãng</p>
		</div>
		<div class="img_4">
			<img src="images/doitra.png" alt="">
			<p>Đổi trả trong vòng 7 ngày</p>
		</div>
	</section>
    <?php include 'layout/footer.php';?>
</body>
</html>