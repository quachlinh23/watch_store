<?php
session_start();
include_once "class/brand.php";
include_once "class/product.php";
include_once "class/rating.php";
include_once "class/cart.php";

$cart = new Cart();
$brand = new brand();
$product = new product();
$rating = new Rating();
$id = $_GET['id'];
$productInfo = $product->getProductById($id);
$formatted_price = number_format($productInfo['giaban'], 0, ',', '.');
$userId = $_SESSION['customer_id'] ?? null;
$ratings = $rating->getRatingsWithCustomerInfo($id);
$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
$specs = $product->getSpecs($id);
$userHasRated = false;
$userRating = null;
if ($userId) {
    foreach ($ratings as $r) {
        if ($r['maTaiKhoan'] == $userId) {
            $userHasRated = true;
            $userRating = $r;
            break;
        }
    }
}

if (isset($_POST['submit_review'])) {
    $ratingValue = $_POST['rating'] ?? null;
    $comment = trim($_POST['comment']);
    $productId = $_POST['product_id'] ?? null;
    $userId = $_SESSION['customer_id'] ?? null;
    $action = $_POST['action'] ?? 'insert'; // Xác định hành động: insert hoặc update

    if (!is_numeric($userId) || !is_numeric($productId)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: 'Vui lòng đăng nhập và chọn sản phẩm hợp lệ!',
                confirmButtonText: 'OK'
            });
        </script>";
    } elseif ($ratingValue === null || empty($comment)) {
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Thiếu thông tin!',
                text: 'Vui lòng chọn số sao và nhập cảm nhận!',
                confirmButtonText: 'OK'
            });
        </script>";
    } else {
        if ($action === 'update') {
            // Cập nhật đánh giá
            $updateResult = $rating->update($userId, $productId, ['rating' => $ratingValue, 'comment' => $comment]);
            if ($updateResult) {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Cập nhật đánh giá thành công!',
                        text: 'Cảm ơn bạn đã chỉnh sửa đánh giá.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: 'Có lỗi xảy ra khi cập nhật đánh giá!',
                        confirmButtonText: 'OK'
                    });
                </script>";
            }
        } else {
            // Thêm mới đánh giá
            $insertResult = $rating->insert($userId, $productId, ['rating' => $ratingValue, 'comment' => $comment]);
            if ($insertResult) {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Gửi đánh giá thành công!',
                        text: 'Cảm ơn bạn đã đánh giá sản phẩm.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: 'Có lỗi xảy ra khi gửi đánh giá!',
                        confirmButtonText: 'OK'
                    });
                </script>";
            }
        }
    }
}

if (isset($_POST['add_to_cart'])) {
    header('Content-Type: application/json');
    $productId = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    if (!$userId) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập để thêm vào giỏ hàng!']);
        exit;
    }

    $cartId = $cart->getcartidbycustomer($userId);
    if ($cartId == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy giỏ hàng của bạn!']);
        exit;
    }

    $result = $cart->addtocart($cartId, $productId, $quantity);
    echo json_encode($result);
    exit;
}

?>
<!DOCTYPE HTML>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Store</title>
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/details.css">
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
        .review-stars .fas.fa-star {
            color: #f5c518;
        }
        .review-stars .far.fa-star {
            color: #ccc;
        }

        .step-title {
            font-size: 22px;
            color: #444;
            margin-top: 20px;
        }

        .description {
            font-size: 16px;
            color: #666;
        }

        .size-image {
            width: 100%;
            max-width: 350px;
            display: block;
            margin: 20px auto;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .size-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .table-header {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #007bff;
            color: white;
        }

        .size-table th, .size-table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .size-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .size-table tr:hover {
            background-color: #e1e1e1;
        }

        .contact-title {
            font-size: 18px;
            color: #444;
        }

        .contact-link {
            color: #007bff;
            font-weight: bold;
            text-decoration: none;
        }

        .contact-link:hover {
            text-decoration: underline;
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
                <li class="breadcrumb__item product-name">Đồng hồ <?php echo $productInfo['tenThuongHieu']?></li>
            </ul>
        </div>
        <!-- Chi tiết sản phẩm -->
        <div class="product-detail">
            <div class="product-images">
                <div class="main_product">
                    <img id="mainImage" src="admin/<?php echo $productInfo['hinhAnh']?>" alt="Sản phẩm chính">
                    <button class="zoom-btn" title="Phóng to"><i class="fas fa-expand"></i></button>
                
                    <!-- Hộp hiển thị ảnh phóng to -->
                    <div class="sub_zoom" id="subZoom">
                        <span class="close_zoom">×</span>
                        <img id="zoomedImage" src="admin/<?php echo $productInfo['hinhAnh']?>" alt="Phóng to">
                    </div>
                </div>

                <div class="thumbnail-container">
                <?php
                    $ImgSub = $product->getSubImgProductById($id);
                    // Kiểm tra số lượng hình ảnh
                    if (isset($ImgSub) && !empty($ImgSub) && count($ImgSub) >= 4) {
                        echo '<button class="nav-btn prev-btn"><i class="fas fa-chevron-left"></i></button>';
                    }
                ?>
                
                <div class="thumbnails" id="thumbnails">
                    <?php
                        if (isset($ImgSub) && !empty($ImgSub)) {
                            foreach ($ImgSub as $img) {
                                echo '<img class="thumb" src="admin/' . htmlspecialchars($img) . '" onclick="changeImage(this)">';
                            }
                            echo '<img class="thumb" src="admin/' . htmlspecialchars($productInfo['hinhAnh']) . '" onclick="changeImage(this)">';
                        } else {
                            echo '<img class="thumb" src="admin/' . htmlspecialchars($productInfo['hinhAnh']) . '" onclick="changeImage(this)">';
                        }
                    ?>
                </div>
                
                <?php
                    // Hiển thị nút next nếu có đủ hình ảnh
                    if (isset($ImgSub) && !empty($ImgSub) && count($ImgSub) >= 4) {
                        echo '<button class="nav-btn next-btn"><i class="fas fa-chevron-right"></i></button>';
                    }
                ?>
            </div>

            </div>

            <div class="product-info">
                <div class="product-info_top">
                    <div class="banner_wrapper">
                        <img class="banner_brand" alt="Thương hiệu" src="images/bannergiamgia.png">
                    </div>
                </div>
                <div class="product-details">
                    <h2 class="name"><?php echo $productInfo['tenSanPham']?></h2>
                    <hr>
                    <p class="desc"><?php echo $productInfo['mota']?></p>
                    <div class="brand"><strong>Thương hiệu:</strong><?php echo $productInfo['tenThuongHieu']?></div>
                    <div class="price"><strong>Giá bán: </strong><span><?php echo $formatted_price?>₫</span></div>
                    <div class="conlai"><strong>Còn lại:  </strong><span><?php echo $productInfo['soluongTon']?></span></div>

                    <form class="cart-options" id="cartForm">
                        <div class="quantity">
                            <p class="soluong"><strong>Số lượng:</strong></p>
                            <button class="qty-btn" type="button" onclick="updateQuantity(-1)">-</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $productInfo['soluongTon']; ?>">
                            <button class="qty-btn" type="button" onclick="updateQuantity(1)">+</button>
                        </div>
                        <div class="buttons">
                            <input type="hidden" name="product_id" value="<?php echo $productInfo['maSanPham']; ?>">
                            <button class="add-to-cart" type="button" id="addToCartBtn">Thêm vào giỏ</button>
                            <button class="buy-now" id="buyNowBtn" style="text-decoration: none; color: white;">Mua ngay</a></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Tabs điều hướng -->
        <div class="tabs">
            <button class="tab-btn active" onclick="openTab('specs')">Thông số</button>
            <button class="tab-btn" onclick="openTab('size')">Hướng dẫn chọn size</button>
            <button class="tab-btn" onclick="openTab('reviews')">Đánh giá</button>
        </div>

        <!-- Nội dung tab -->
        <div class="tab-content active" id="specs">
            <h3>Thông số kỹ thuật</h3>
            <ul>
                <?php if (!empty($specs)): ?>
                    <?php if (!empty($specs['loaiDay'])): ?>
                        <li>Loại dây: <?= htmlspecialchars($specs['loaiDay']) ?></li>
                        <hr>
                    <?php endif; ?>
                    <?php if (!empty($specs['pin'])): ?>
                        <li>Pin: <?= htmlspecialchars($specs['pin']) ?></li>
                        <hr>
                    <?php endif; ?>
                    <?php if (!empty($specs['matKinh'])): ?>
                        <li>Mặt kính: <?= htmlspecialchars($specs['matKinh']) ?></li>
                        <hr>
                    <?php endif; ?>
                    <?php if (!empty($specs['chongNuoc'])): ?>
                        <li>Chống nước: <?= htmlspecialchars($specs['chongNuoc']) ?></li>
                        <hr>
                    <?php endif; ?>
                    <?php if (!empty($specs['chucNang'])): ?>
                        <li>Chức năng: <?= htmlspecialchars($specs['chucNang']) ?></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li>Không có thông số kỹ thuật.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="tab-content" id="size">
            <h3 class="heading">Hướng dẫn chọn size đồng hồ</h3>

            <h4 class="step-title">Bước 1: Đo size tay</h4>
            <p class="description">Để chọn được size đồng hồ phù hợp, bạn cần đo vòng tay của mình. Dưới đây là bảng tham chiếu size đồng hồ tương ứng với vòng tay của bạn.</p>
            <img src="images/huongdan.png" alt="Hình ảnh đo vòng tay" class="size-image">

            <h4 class="step-title">Bước 2: Tham chiếu size đồng hồ phù hợp</h4>
            <table class="size-table">
                <thead>
                    <tr>
                        <th class="table-header">Vòng tay (cm)</th>
                        <th class="table-header">Size đồng hồ phù hợp (cm)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>13cm</td>
                        <td>26cm - 33cm</td>
                    </tr>
                    <tr>
                        <td>14cm</td>
                        <td>28cm - 35cm</td>
                    </tr>
                    <tr>
                        <td>15cm</td>
                        <td>30cm - 38cm</td>
                    </tr>
                    <tr>
                        <td>16cm</td>
                        <td>32cm - 40cm</td>
                    </tr>
                    <tr>
                        <td>17cm</td>
                        <td>34cm - 43cm</td>
                    </tr>
                    <tr>
                        <td>18cm</td>
                        <td>36cm - 45cm</td>
                    </tr>
                    <tr>
                        <td>19cm</td>
                        <td>38cm - 48cm</td>
                    </tr>
                    <tr>
                        <td>20cm</td>
                        <td>40cm - 50cm</td>
                    </tr>
                    <tr>
                        <td>21cm</td>
                        <td>42cm - 53cm</td>
                    </tr>
                    <tr>
                        <td>22cm</td>
                        <td>44cm - 55cm</td>
                    </tr>
                </tbody>
            </table>
            <h4 class="contact-title" style="margin-top: 20px;">Hoặc liên hệ với WatchStore qua số hotline <a href="tel:0794628540" class="contact-link">0794628540</a></h4>
        </div>

        

        <div class="tab-content" id="reviews">
            <h3>Đánh giá sản phẩm</h3>
            <div class="rating-summary">
                <div class="rating-score">
                    <?php
                        $avg = $rating->getAverageRating($id);
                        $avg = $avg ? $avg : 0;
                    ?>
                    <span class="score"><i class="fas fa-star"></i><?php echo $avg?>/5</span>
                    <p class="total-reviews"><?php echo count($ratings); ?> đánh giá</p>
                    <button class="write-review" onclick="openReviewModal()">Viết đánh giá</button>
                </div>
                <div class="rating-distribution">
                    <?php
                    // Tính toán phân bố số sao
                    $starCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                    $totalRatings = count($ratings);
                    foreach ($ratings as $rating) {
                        $starCounts[$rating['soSao']]++;
                    }
                    foreach ($starCounts as $star => $count) {
                        $percentage = $totalRatings > 0 ? ($count / $totalRatings) * 100 : 0;
                        echo '<div class="rating-bar">
                            <span class="stars">'.$star.' <i class="fas fa-star"></i></span>
                            <div class="progress-bar">
                                <div class="progress" style="width: '.$percentage.'%"></div>
                            </div>
                            <span class="percentage">'.number_format($percentage, 1).'%</span>
                        </div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Modal đánh giá -->
            <div class="review-modal" id="reviewModal">
                <div class="modal-content">
                    <span class="close-modal" onclick="closeReviewModal()">×</span>
                    <h3 class="review-modal-title"><?php echo $userHasRated ? 'Chỉnh sửa đánh giá' : 'Đánh giá sản phẩm'; ?></h3>
                    <hr>
                    <form action="details.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $productInfo['maSanPham']; ?>">
                        <input type="hidden" name="action" value="<?php echo $userHasRated ? 'update' : 'insert'; ?>">
                        <div class="product-info-modal">
                            <img src="admin/<?php echo $productInfo['hinhAnh']?>" alt="Sản phẩm" class="product-img">
                            <p><?php echo $productInfo['tenSanPham']?></p>
                        </div>
                        <div class="rating-stars">
                            <input type="radio" name="rating" id="star5" value="5" <?php echo $userHasRated && $userRating['soSao'] == 5 ? 'checked' : ''; ?>>
                            <label for="star5" class="star-label"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="star4" value="4" <?php echo $userHasRated && $userRating['soSao'] == 4 ? 'checked' : ''; ?>>
                            <label for="star4" class="star-label"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="star3" value="3" <?php echo $userHasRated && $userRating['soSao'] == 3 ? 'checked' : ''; ?>>
                            <label for="star3" class="star-label"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="star2" value="2" <?php echo $userHasRated && $userRating['soSao'] == 2 ? 'checked' : ''; ?>>
                            <label for="star2" class="star-label"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="star1" value="1" <?php echo $userHasRated && $userRating['soSao'] == 1 ? 'checked' : ''; ?>>
                            <label for="star1" class="star-label"><i class="fas fa-star"></i></label>
                        </div>
                        <textarea name="comment" placeholder="Mời bạn chia sẻ cảm nhận..." rows="6"><?php echo $userHasRated ? htmlspecialchars($userRating['noiDung']) : ''; ?></textarea>
                        <button class="submit-review" type="submit" name="submit_review"><?php echo $userHasRated ? 'Cập nhật đánh giá' : 'Gửi đánh giá'; ?></button>
                    </form>
                </div>
            </div>

            <!-- Danh sách đánh giá động -->
            <div class="review-list-wrapper">
                <div class="review-list">
                    <?php
                    if (!empty($ratings)) {
                        foreach ($ratings as $rating) {
                            $stars = '';
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating['soSao']) {
                                    $stars .= '<i class="fas fa-star"></i>';
                                } else {
                                    $stars .= '<i class="far fa-star"></i>';
                                }
                            }
                            echo '<div class="review-item">
                                <p class="review-name">'.$rating['tenKhachHang'].'</p>
                                <div class="review-stars">'.$stars.'</div>
                                <p class="review-text">'.$rating['noiDung'].'</p>
                            </div>';
                        }
                    } else {
                        echo '<p>Chưa có đánh giá nào cho sản phẩm này.</p>';
                    }
                    ?>
                </div>
                <button id="toggleReviewsBtn" class="toggle-reviews-btn">Xem thêm</button>
            </div>
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
    <script>
        // Lấy số lượng tồn từ PHP
        const maxQuantity = <?php echo $productInfo['soluongTon']; ?>;
        const quantityInput = document.getElementById("quantity");

        // Hàm cập nhật số lượng
        function updateQuantity(change) {
            let currentQuantity = parseInt(quantityInput.value);
            let newQuantity = currentQuantity + change;

            // Kiểm tra giới hạn
            if (newQuantity < 1) {
                newQuantity = 1; // Không cho phép nhỏ hơn 1
            } else if (newQuantity > maxQuantity) {
                newQuantity = maxQuantity; // Không cho phép vượt quá số lượng tồn
                Swal.fire({
                    icon: 'warning',
                    title: 'Số lượng vượt quá!',
                    text: `Số lượng tối đa có thể chọn là ${maxQuantity}.`,
                    confirmButtonText: 'OK'
                });
            }

            quantityInput.value = newQuantity;
        }

        // Kiểm tra khi người dùng nhập trực tiếp vào input
        quantityInput.addEventListener('input', function() {
            let value = parseInt(this.value);

            if (isNaN(value) || value < 1) {
                this.value = 1; // Đặt lại về 1 nếu giá trị không hợp lệ
            } else if (value > maxQuantity) {
                this.value = maxQuantity; // Đặt lại về số lượng tối đa
                Swal.fire({
                    icon: 'warning',
                    title: 'Số lượng vượt quá!',
                    text: `Số lượng tối đa có thể chọn là ${maxQuantity}.`,
                    confirmButtonText: 'OK'
                });
            }
        });

        function openReviewModal() {
            <?php if (!$userId): ?>
                Swal.fire({
                    icon: 'warning',
                    title: 'Chưa đăng nhập!',
                    text: 'Vui lòng đăng nhập để viết đánh giá.',
                    confirmButtonText: 'Đăng nhập',
                    showCancelButton: true,
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'login.php';
                    }
                });
                return;
            <?php else: ?>
                document.getElementById("reviewModal").style.display = "flex";
            <?php endif; ?>
        }

        function closeReviewModal() {
            document.getElementById("reviewModal").style.display = "none";
        }

        document.getElementById("reviewModal").addEventListener("click", function(e) {
            if (e.target === this) {
                closeReviewModal();
            }
        });

        document.querySelector(".submit-review").addEventListener("click", function(e) {
            e.preventDefault();
            const rating = document.querySelector('input[name="rating"]:checked')?.value;
            const comment = document.querySelector(".modal-content textarea").value.trim();
            const productId = document.querySelector('input[name="product_id"]').value;
            const action = document.querySelector('input[name="action"]').value;

            if (!rating) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Chưa chọn đánh giá!',
                    text: 'Vui lòng chọn số sao để đánh giá sản phẩm.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            if (!comment) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Chưa nhập nhận xét!',
                    text: 'Vui lòng nhập nhận xét về sản phẩm.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            fetch('details.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `submit_review=1&rating=${rating}&comment=${encodeURIComponent(comment)}&product_id=${productId}&action=${action}`
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes('Gửi đánh giá thành công') || data.includes('Cập nhật đánh giá thành công')) {
                    Swal.fire({
                        icon: 'success',
                        title: action === 'update' ? 'Cập nhật đánh giá thành công!' : 'Gửi đánh giá thành công!',
                        text: action === 'update' ? 'Cảm ơn bạn đã chỉnh sửa đánh giá.' : 'Cảm ơn bạn đã đánh giá sản phẩm.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: 'Có lỗi xảy ra khi gửi đánh giá!',
                        confirmButtonText: 'OK'
                    });
                }
                closeReviewModal();
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Có lỗi xảy ra khi gửi đánh giá!',
                    confirmButtonText: 'OK'
                });
            });
        });

        const btn = document.getElementById("toggleReviewsBtn");
        const reviewList = document.querySelector(".review-list");
        let expanded = false;

        btn.addEventListener("click", () => {
            expanded = !expanded;
            reviewList.classList.toggle("show-all", expanded);
            btn.textContent = expanded ? "Ẩn bớt" : "Xem thêm";
        });

        const totalReviews = document.querySelectorAll(".review-list .review-item").length;
        if (totalReviews <= 3) {
            btn.style.display = "none";
        }

        document.querySelector("#addToCartBtn").addEventListener("click", function(e) {
            e.preventDefault();
            const quantity = document.querySelector("#quantity").value;
            const productId = document.querySelector('input[name="product_id"]').value;
            const maxQuantity = <?php echo $productInfo['soluongTon']; ?>;

            // Kiểm tra số lượng hợp lệ tại client
            if (!quantity || parseInt(quantity) <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Số lượng không hợp lệ!',
                    text: 'Vui lòng nhập số lượng lớn hơn 0.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            if (parseInt(quantity) > maxQuantity) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Số lượng vượt quá!',
                    text: `Số lượng tối đa có thể chọn là ${maxQuantity}.`,
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Gửi yêu cầu thêm vào giỏ hàng
            fetch('details.php?id=<?php echo $productInfo['maSanPham']; ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `add_to_cart=1&quantity=${quantity}&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: data.message,
                        showConfirmButton: true,
                        confirmButtonText: 'Xem giỏ hàng',
                        showCancelButton: true,
                        cancelButtonText: 'Tiếp tục mua sắm'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'cart.php';
                        }
                    });
                } else if (data.message.includes('Vui lòng đăng nhập')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: data.message,
                        confirmButtonText: 'Đăng nhập'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'login.php';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: data.message,
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Có lỗi xảy ra khi thêm vào giỏ hàng!',
                    confirmButtonText: 'OK'
                });
            });
        });

        document.querySelector("#buyNowBtn").addEventListener("click", function(e) {
            e.preventDefault();
            
            // Kiểm tra đăng nhập
            <?php if (!$userId): ?>
                Swal.fire({
                    icon: 'warning',
                    title: 'Chưa đăng nhập!',
                    text: 'Vui lòng đăng nhập để tiếp tục thanh toán.',
                    confirmButtonText: 'Đăng nhập',
                    showCancelButton: true,
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Lưu URL hiện tại để quay lại sau khi đăng nhập
                        sessionStorage.setItem('redirectAfterLogin', window.location.href);
                        window.location.href = 'login.php';
                    }
                });
                return;
            <?php endif; ?>

            const quantity = document.querySelector("#quantity").value;
            const maxQuantity = <?php echo $productInfo['soluongTon']; ?>;

            // Kiểm tra số lượng hợp lệ
            if (!quantity || parseInt(quantity) <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Số lượng không hợp lệ!',
                    text: 'Vui lòng nhập số lượng lớn hơn 0.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            if (parseInt(quantity) > maxQuantity) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Số lượng vượt quá!',
                    text: `Số lượng tối đa có thể chọn là ${maxQuantity}.`,
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Lưu thông tin sản phẩm vào sessionStorage
            const selectedItems = [{
                idProduct: <?php echo $productInfo['maSanPham']; ?>,
                quantity: parseInt(quantity),
                price: <?php echo $productInfo['giaban']; ?>,
                name: "<?php echo addslashes($productInfo['tenSanPham']); ?>",
                image: "admin/<?php echo $productInfo['hinhAnh']; ?>",
                stock: <?php echo $productInfo['soluongTon']; ?>
            }];

            sessionStorage.setItem("checkoutItemsNow", JSON.stringify(selectedItems));

            // Chuyển hướng đến trang thanh toán
            Swal.fire({
                icon: 'success',
                title: 'Đang chuyển đến thanh toán...',
                text: 'Vui lòng kiểm tra thông tin đơn hàng.',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = 'InvoiceNow.php';
            });
        });
    </script>
</body>
</html>