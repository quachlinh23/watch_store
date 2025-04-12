<?php
session_start();
include_once "class/customerlogin.php";
include_once "class/cart.php";

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

        <!-- Danh sách sản phẩm -->
        <div class="product-list" id="product-list">
            <!-- Sản phẩm sẽ được thêm động bằng JavaScript -->
        </div>

        <!-- Nút Thanh toán / Hủy -->
        <div class="payment-actions">
            <button class="btn-cancel" onclick="window.location.href='index.php'">Hủy đơn</button>
            <button class="btn-pay" onclick="checkout()">Thanh Toán</button>
        </div>
    </div>

    <script>
        const isLoggedIn = <?php echo $loggedIn ? 'true' : 'false'; ?>;

        function openAddressModal() {
            if (!isLoggedIn) {
                alert('Vui lòng đăng nhập để mua hàng!');
                window.location.href = 'login.php';
                return;
            }
            document.getElementById("addressModal").style.display = "block";
            document.body.classList.add('no-scroll');
        }

        function closeAddressModal() {
            document.getElementById("addressModal").style.display = "none";
            document.body.classList.remove('no-scroll');
        }

        window.onclick = function(event) {
            const modal = document.getElementById("addressModal");
            if (event.target == modal) {
                modal.style.display = "none";
                document.body.classList.remove('no-scroll');
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            const productList = document.getElementById("product-list");
            const checkoutItems = JSON.parse(sessionStorage.getItem("checkoutItems")) || [];

            if (checkoutItems.length === 0) {
                productList.innerHTML = '<p style="text-align: center;">Không có sản phẩm nào được chọn.</p>';
                document.querySelector(".btn-pay").disabled = true;
                return;
            }

            let total = 0;
            checkoutItems.forEach(item => {
                const subtotal = item.price * item.quantity;
                total += subtotal;
                const productItem = document.createElement("div");
                productItem.className = "product-item";
                productItem.innerHTML = `
                    <div class="product-item-info">
                        <div class="product-item__left">
                            <img class="proImg" src="${item.image}" alt="${item.name}">
                        </div>
                        <div class="product-item__right">
                            <div class="product-item__details">
                                <div class="product-item__name">${item.name}</div>
                                <div class="product-item__limit">Còn lại: ${item.stock}</div>
                                <div class="product-item__quanty">SL: ${item.quantity}</div>
                            </div>
                            <div class="product-item__price">${item.price.toLocaleString("vi-VN")}đ</div>
                        </div>
                    </div>
                    <hr class="boderHr">
                `;
                productList.appendChild(productItem);
            });

            const totalDiv = document.createElement("div");
            totalDiv.className = "totalmoney";
            totalDiv.innerHTML = `
                <span class="total-product-quantity">
                    <span class="total-label">Cần thanh toán:</span>
                </span>
                <span class="temp-total-money">
                    <span class="temp-total-money-data">${total.toLocaleString("vi-VN")}đ</span>
                </span>
            `;
            productList.appendChild(totalDiv);
        });

        function checkout() {
            if (!isLoggedIn) {
                alert('Vui lòng đăng nhập để mua hàng!');
                window.location.href = 'login.php';
                return;
            }

            if (!<?php echo isset($_SESSION['shipping_info']) ? 'true' : 'false'; ?>) {
                alert('Vui lòng nhập thông tin giao hàng trước khi thanh toán!');
                openAddressModal();
                return;
            }

            const checkoutItems = JSON.parse(sessionStorage.getItem("checkoutItems")) || [];
            if (checkoutItems.length === 0) {
                alert('Không có sản phẩm nào để thanh toán!');
                return;
            }

            const form = document.createElement("form");
            form.method = "POST";
            form.action = "";

            const itemsInput = document.createElement("input");
            itemsInput.type = "hidden";
            itemsInput.name = "items";
            itemsInput.value = JSON.stringify(checkoutItems);
            form.appendChild(itemsInput);

            const actionInput = document.createElement("input");
            actionInput.type = "hidden";
            actionInput.name = "action";
            actionInput.value = "process_checkout";
            form.appendChild(actionInput);

            document.body.appendChild(form);
            form.submit();
        }
    </script>

    <?php
    if ($loggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_checkout') {
        $cart = new cart();
        $maTaiKhoan = $_SESSION['customer_id']; // Lấy maTaiKhoan từ session
        $items = json_decode($_POST['items'] ?? '[]', true);
        $shippingInfo = $_SESSION['shipping_info'] ?? null;

        if (empty($items) || empty($shippingInfo)) {
            echo "<script>alert('Thông tin không đầy đủ'); window.history.back();</script>";
            exit;
        }

        $result = $cart->process_checkout($maTaiKhoan, $items, $shippingInfo);

        if ($result['success']) {
            unset($_SESSION['shipping_info']);
            echo "<script>
                sessionStorage.removeItem('checkoutItems');
                alert('Đặt hàng thành công');
                window.location.href = 'index.php';
            </script>";
        } else {
            echo "<script>alert('Có lỗi xảy ra: " . htmlspecialchars($result['message']) . "'); window.history.back();</script>";
        }
        exit;
    }
    ?>
</body>
</html>