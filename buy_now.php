<?php
session_start();
include_once "class/customerlogin.php";

$loggedIn = isset($_SESSION['customer_id']);
$customerInfo = null;
$missingInfo = false;

if ($loggedIn) {
    $customer = new customerlogin();
    $customerId = $_SESSION['customer_id'];
    $customerInfo = $customer->getinforcustomerbyid($customerId)->fetch_assoc();
    $missingInfo = empty($customerInfo['tenKhachHang']) || empty($customerInfo['soDT']) || empty($customerInfo['diaChi']);
}

if ($loggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullName'])) {
    $_SESSION['shipping_info'] = [
        'fullName' => $_POST['fullName'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'city' => $_POST['city']
    ];

    $addressDisplay = $_POST['address'] . ', ' . $_POST['city'];
    echo "<script>
        document.querySelector('.address-content').textContent = '$addressDisplay';
        document.getElementById('addressModal').style.display = 'none';
        document.body.classList.remove('no-scroll');
        alert('Thông tin giao hàng đã được lưu!');
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/buynow.css">
    <title>Invoice</title>
</head>
<body>
    <div class="cart-fragment">
        <h2 class="title-name">Hóa đơn mua hàng</h2>
        <!-- Địa chỉ giao hàng -->
        <div class="info__cart location">
            <div class="title-content">
                <?php if (!$loggedIn): ?>
                    <div class="NoLogin" style="color: red;">Vui lòng đăng nhập để mua hàng</div>
                <?php else: ?>
                    <div class="infocustomer">
                        <div class="info">
                            <div class="note">
                                <?php 
                                if ($missingInfo && !isset($_SESSION['shipping_info'])) {
                                    echo "Vui lòng cung cấp thông tin giao hàng";
                                } else {
                                    $name = isset($_SESSION['shipping_info']) ? $_SESSION['shipping_info']['fullName'] : $customerInfo['tenKhachHang'];
                                    $phone = isset($_SESSION['shipping_info']) ? $_SESSION['shipping_info']['phone'] : $customerInfo['soDT'];
                                    echo htmlspecialchars("$name | $phone");
                                }
                                ?>
                            </div>
                        </div>
                        <div class="address">
                            <i class="fa-solid fa-location-dot"></i>
                            <span class="address-content">
                                <?php 
                                if ($missingInfo && !isset($_SESSION['shipping_info'])) {
                                    echo "Vui lòng cung cấp thông tin giao hàng";
                                } else {
                                    $address = isset($_SESSION['shipping_info']) ? $_SESSION['shipping_info']['address'] . ', ' . $_SESSION['shipping_info']['city'] : $customerInfo['diaChi'];
                                    echo htmlspecialchars($address);
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($loggedIn): ?>
                <div class="delivery_arrow" onclick="openAddressModal()">
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Modal nhập thông tin giao hàng -->
        <div id="addressModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeAddressModal()">×</span>
                <h3>Nhập thông tin giao hàng</h3>
                <form id="addressForm" method="POST" action="">
                    <label for="fullName">Họ và tên:</label>
                    <input type="text" id="fullName" name="fullName" value="<?php echo isset($_SESSION['shipping_info']) ? $_SESSION['shipping_info']['fullName'] : ($loggedIn ? $customerInfo['tenKhachHang'] : ''); ?>" required>

                    <label for="phone">Số điện thoại:</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo isset($_SESSION['shipping_info']) ? $_SESSION['shipping_info']['phone'] : ($loggedIn ? $customerInfo['soDT'] : ''); ?>" required>

                    <label for="address">Địa chỉ:</label>
                    <input type="text" id="address" name="address" value="<?php echo isset($_SESSION['shipping_info']) ? $_SESSION['shipping_info']['address'] : ($loggedIn ? $customerInfo['diaChi'] : ''); ?>" required>

                    <label for="city">Tỉnh/Thành phố:</label>
                    <input type="text" id="city" name="city" value="<?php echo isset($_SESSION['shipping_info']) ? $_SESSION['shipping_info']['city'] : ''; ?>" required>

                    <button type="submit" class="btn-save">Lưu thông tin</button>
                </form>
            </div>
        </div>

        <!-- Danh sách sản phẩm (dữ liệu tĩnh) -->
        <div class="product-list">
            <div class="product-item">
                <div class="product-item-info">
                    <div class="product-item__left">
                        <img class="proImg" src="images/cart.png" alt="Sản phẩm">
                    </div>
                    <div class="product-item__right">
                        <div class="product-item__details">
                            <div class="product-item__name">Tên sản phẩm</div>
                            <div class="product-item__limit">Còn lại: 10</div>
                            <div class="product-item__quanty">SL: 1</div>
                        </div>
                        <div class="product-item__price">100.000đ</div>
                    </div>
                </div>
                <div class="product-item-info-quanty">
                    <div class="info-quanty">
                        <span>Số lượng:</span>
                        <button class="btn-minus">-</button>
                        <input type="number" class="product-quantity" value="1" min="1">
                        <button class="btn-plus">+</button>
                    </div>
                </div>
                <hr class="boderHr">
                <div class="total-provisional">
                    <span class="total-product-quantity">
                        <span class="total-label">Tạm tính </span>(1 sản phẩm):
                    </span>
                    <span class="temp-total-money">
                        <span class="temp-total-money-data">495.000đ</span>
                    </span>
                </div>
                <div class="discount">
                    <span class="total-product-quantity">
                        <span class="total-label">Miễn giảm:</span>
                    </span>
                    <span class="temp-total-money">
                        <span class="temp-total-money-data">0</span>
                    </span>
                </div>
                <div class="totalmoney">
                    <span class="total-product-quantity">
                        <span class="total-label">Cần thanh toán:</span>
                    </span>
                    <span class="temp-total-money">
                        <span class="temp-total-money-data">495.000đ</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Hình thức thanh toán -->
        <div class="payment-method">
            <h3>Hình thức thanh toán</h3>
            <label>
                <input type="radio" name="payment" value="cod" checked> Thanh toán khi nhận hàng
            </label><br>
            <label>
                <input type="radio" name="payment" value="bank"> Chuyển khoản ngân hàng
            </label><br>
            <label>
                <input type="radio" name="payment" value="wallet"> Ví điện tử (Momo, ZaloPay)
            </label>
        </div>

        <!-- Nút Thanh toán / Hủy -->
        <div class="payment-actions">
            <button class="btn-cancel" onclick="window.location.href='index.php'">Hủy đơn</button>
            <button class="btn-pay" onclick="checkout()">Thanh toán</button>
        </div>
    </div>

    <script>
        // Kiểm tra trạng thái đăng nhập
        const isLoggedIn = <?php echo $loggedIn ? 'true' : 'false'; ?>;

        // Mở modal và khóa cuộn
        function openAddressModal() {
            if (!isLoggedIn) {
                alert('Vui lòng đăng nhập để mua hàng!');
                window.location.href = 'login.php'; // Chuyển hướng đến trang đăng nhập
                return;
            }
            document.getElementById("addressModal").style.display = "block";
            document.body.classList.add('no-scroll');
        }

        // Đóng modal và mở lại cuộn
        function closeAddressModal() {
            document.getElementById("addressModal").style.display = "none";
            document.body.classList.remove('no-scroll');
        }

        // Đóng modal khi nhấn ra ngoài và mở lại cuộn
        window.onclick = function(event) {
            const modal = document.getElementById("addressModal");
            if (event.target == modal) {
                modal.style.display = "none";
                document.body.classList.remove('no-scroll');
            }
        }

        // Xử lý thanh toán
        function checkout() {
            if (!isLoggedIn) {
                alert('Vui lòng đăng nhập để mua hàng!');
                window.location.href = 'login.php'; // Chuyển hướng đến trang đăng nhập
                return;
            }

            const paymentMethod = document.querySelector('input[name="payment"]:checked').value;
            if (!<?php echo isset($_SESSION['shipping_info']) ? 'true' : 'false'; ?>) {
                alert('Vui lòng nhập thông tin giao hàng trước khi thanh toán!');
                openAddressModal();
                return;
            }

            alert('Đặt hàng thành công với phương thức: ' + paymentMethod);
            window.location.href = 'index.php'; // Chuyển hướng về trang chủ
        }
    </script>
</body>
</html>