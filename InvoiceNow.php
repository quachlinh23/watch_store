<?php
session_start();
include_once "class/customerlogin.php";
include_once "class/customer_info.php";
include_once "class/cart.php";

$loggedIn = isset($_SESSION['customer_id']);
$customerInfo = null;
$missingInfo = false;
$customer_info = new customer();

if ($loggedIn) {
    $customer = new customerlogin();
    $customerId = $_SESSION['customer_id'];
    $result = $customer->getinforcustomerbyid($customerId);
    if ($result && $customerInfo = $result->fetch_assoc()) {
        $missingInfo = empty($customerInfo['tenKhachHang']) || empty($customerInfo['soDT']) || empty($customerInfo['diaChi']);
    } else {
        $missingInfo = true;
        error_log("Failed to fetch customer info for ID: $customerId");
    }
}

if ($loggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullName'])) {
    $customerId = $_SESSION['customer_id'];
    
    $fullName = trim($_POST['fullName'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $ward = trim($_POST['ward'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $city = trim($_POST['city'] ?? '');

    $errors = [];
    if (empty($fullName)) $errors[] = "Họ và tên không được để trống";
    if (empty($phone)) $errors[] = "Số điện thoại không được để trống";
    if (empty($address)) $errors[] = "Địa chỉ chi tiết không được để trống";
    if (empty($ward)) $errors[] = "Xã/Phường không được để trống";
    if (empty($district)) $errors[] = "Quận/Huyện không được để trống";
    if (empty($city)) $errors[] = "Tỉnh/Thành phố không được để trống";

    if (!empty($errors)) {
        $errorMessage = htmlspecialchars(implode(". ", $errors));
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: '$errorMessage',
                confirmButtonText: 'OK'
            });
        </script>";
    } else {
        $fullAddress = "$address, $ward, $district, $city";

        $updateResult = $customer_info->updateCustomerInfo($customerId, $fullName, $fullAddress, $phone, $email);

        if ($updateResult === true) {
            $_SESSION['shipping_info'] = [
                'fullName' => $fullName,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
                'ward' => $ward,
                'district' => $district,
                'city' => $city
            ];

            $addressDisplay = htmlspecialchars($fullAddress);
            $nameDisplay = htmlspecialchars($fullName);
            $phoneDisplay = htmlspecialchars($phone);
            echo "<script>
                document.querySelector('.address-content').textContent = '$addressDisplay';
                document.querySelector('.text-gray-700.text-lg.mt-1').textContent = '$nameDisplay | $phoneDisplay';
                document.getElementById('addressModal').style.display = 'none';
                document.body.classList.remove('no-scroll');
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: 'Thông tin giao hàng đã được lưu!',
                    confirmButtonText: 'OK'
                });
            </script>";
        } else {
            $errorMessage = htmlspecialchars($updateResult);
            echo "<script>
                document.getElementById('addressModal').style.display = 'none';
                document.body.classList.remove('no-scroll');
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: '$errorMessage',
                    confirmButtonText: 'OK'
                });
            </script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Watch Store - Thanh toán ngay</title>
    <style>
        .no-scroll { overflow: hidden; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; margin: 5% auto; padding: 20px; width: 90%; max-width: 600px; border-radius: 8px; }
        .close { float: right; font-size: 24px; cursor: pointer; }
        select, input { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; }
        .quantity-container { display: flex; align-items: center; }
        .quantity-container button { width: 30px; height: 30px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9; cursor: pointer; }
        .quantity-container input { width: 50px; text-align: center; margin: 0 5px; }
        .remove-btn { background: none; border: none; cursor: pointer; color: #e53e3e; }
        input.quantity::-webkit-outer-spin-button,
        input.quantity::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input.quantity { -moz-appearance: textfield; }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto p-4 max-w-7xl">
        <h2 class="text-2xl font-bold text-center mb-6">Thanh toán ngay</h2>
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Left Panel: Buyer Information -->
            <div class="lg:w-1/2 bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Thông tin giao hàng</h3>
                <?php if (!$loggedIn): ?>
                    <div class="text-red-500 mb-4">Vui lòng đăng nhập để mua hàng</div>
                    <a href="login.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Đăng nhập</a>
                <?php else: ?>
                    <div class="mb-4">
                        <p class="text-gray-700 text-lg mt-1">
                            <?php 
                            if ($missingInfo && !isset($_SESSION['shipping_info'])) {
                                echo "Vui lòng cung cấp thông tin giao hàng";
                            } else {
                                $name = isset($_SESSION['shipping_info']) ? $_SESSION['shipping_info']['fullName'] : ($customerInfo['tenKhachHang'] ?? 'Chưa cung cấp');
                                $phone = isset($_SESSION['shipping_info']) ? $_SESSION['shipping_info']['phone'] : ($customerInfo['soDT'] ?? 'Chưa cung cấp');
                                echo htmlspecialchars("$name | $phone");
                            }
                            ?>
                        </p>
                        <p class="text-gray-700 text-lg flex items-center mt-1">
                            <i class="fa-solid fa-location-dot mr-2"></i>
                            <span class="address-content">
                                <?php
                                if ($missingInfo && !isset($_SESSION['shipping_info'])) {
                                    echo "Vui lòng cung cấp thông tin giao hàng";
                                } else {
                                    $address = isset($_SESSION['shipping_info']) 
                                        ? $_SESSION['shipping_info']['address'] . ', ' . $_SESSION['shipping_info']['ward'] . ', ' . $_SESSION['shipping_info']['district'] . ', ' . $_SESSION['shipping_info']['city']
                                        : ($customerInfo['diaChi'] ?? 'Chưa cung cấp');
                                    echo htmlspecialchars($address);
                                }
                                ?>
                            </span>
                        </p>
                    </div>
                    <button onclick="openAddressModal()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Cập nhật thông tin</button>
                <?php endif; ?>
            </div>

            <!-- Right Panel: Order Details -->
            <div class="lg:w-1/2 bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Chi tiết đơn hàng</h3>
                <div class="product-list" id="product-list"></div>
                <div class="mt-4 flex justify-end">
                    <button class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 mr-2" onclick="history.back()">Hủy đơn</button>
                    <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600" onclick="checkout()">Thanh Toán</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Address Input -->
    <div id="addressModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddressModal()">×</span>
            <h3 class="text-lg font-semibold mb-4">Cập nhật thông tin giao hàng</h3>
            <form id="addressForm" method="POST" action="">
                <div class="mb-4">
                    <label for="fullName" class="block text-sm font-medium">Họ và tên:</label>
                    <input type="text" id="fullName" name="fullName" value="<?php echo isset($_SESSION['shipping_info']) ? htmlspecialchars($_SESSION['shipping_info']['fullName']) : ($loggedIn && $customerInfo ? htmlspecialchars($customerInfo['tenKhachHang']) : ''); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium">Số điện thoại:</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo isset($_SESSION['shipping_info']) ? htmlspecialchars($_SESSION['shipping_info']['phone']) : ($loggedIn && $customerInfo ? htmlspecialchars($customerInfo['soDT']) : ''); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_SESSION['shipping_info']) ? htmlspecialchars($_SESSION['shipping_info']['email']) : ($loggedIn && $customerInfo ? htmlspecialchars($customerInfo['email'] ?? '') : ''); ?>">
                </div>
                <div class="mb-4">
                    <label for="city" class="block text-sm font-medium">Tỉnh/Thành phố:</label>
                    <select id="city" name="city" required>
                        <option value="">Chọn Tỉnh/Thành phố</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="district" class="block text-sm font-medium">Quận/Huyện:</label>
                    <select id="district" name="district" required>
                        <option value="">Chọn Quận/Huyện</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="ward" class="block text-sm font-medium">Xã/Phường:</label>
                    <select id="ward" name="ward" required>
                        <option value="">Chọn Xã/Phường</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="address" class="block text-sm font-medium">Địa chỉ chi tiết:</label>
                    <input type="text" id="address" name="address" value="<?php echo isset($_SESSION['shipping_info']) ? htmlspecialchars($_SESSION['shipping_info']['address']) : ($loggedIn && $customerInfo ? htmlspecialchars($customerInfo['diaChi']) : ''); ?>" required>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">Lưu thông tin</button>
            </form>
        </div>
    </div>

    <script>
        const isLoggedIn = <?php echo $loggedIn ? 'true' : 'false'; ?>;
        const storedAddress = "<?php echo isset($_SESSION['shipping_info']) 
            ? htmlspecialchars($_SESSION['shipping_info']['address'] . ', ' . $_SESSION['shipping_info']['ward'] . ', ' . $_SESSION['shipping_info']['district'] . ', ' . $_SESSION['shipping_info']['city'])
            : ($loggedIn && $customerInfo && $customerInfo['diaChi'] ? htmlspecialchars($customerInfo['diaChi']) : ''); ?>";

        function openAddressModal() {
            if (!isLoggedIn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Chưa đăng nhập!',
                    text: 'Vui lòng đăng nhập để tiếp tục.',
                    confirmButtonText: 'Đăng nhập',
                    showCancelButton: true,
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'login.php';
                    }
                });
                return;
            }
            document.getElementById("addressModal").style.display = "block";
            document.body.classList.add('no-scroll');
            parseAndPreselectAddress(storedAddress);
        }

        function closeAddressModal() {
            document.getElementById("addressModal").style.display = "none";
            document.body.classList.remove('no-scroll');
        }

        window.onclick = function(event) {
            const modal = document.getElementById("addressModal");
            if (event.target == modal) {
                closeAddressModal();
            }
        }

        async function parseAndPreselectAddress(address) {
            if (!address) {
                await loadCities();
                return;
            }

            const addressParts = address.split(',').map(part => part.trim());
            let detailedAddress, ward, district, city;

            if (addressParts.length >= 4) {
                detailedAddress = addressParts[0];
                ward = addressParts[1];
                district = addressParts[2];
                city = addressParts[3];
            } else if (addressParts.length === 3) {
                detailedAddress = addressParts[0];
                ward = '';
                district = addressParts[1];
                city = addressParts[2];
            } else {
                detailedAddress = addressParts[0] || '';
                ward = district = city = '';
            }

            document.getElementById('address').value = detailedAddress;
            await loadCities(city, district, ward);
        }

        async function loadCities(preselectCity = '', preselectDistrict = '', preselectWard = '') {
            const citySelect = document.getElementById('city');
            try {
                const response = await fetch('https://provinces.open-api.vn/api/p/');
                const cities = await response.json();
                citySelect.innerHTML = '<option value="">Chọn Tỉnh/Thành phố</option>';
                let cityCode = null;
                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.name;
                    option.text = city.name;
                    option.dataset.code = city.code;
                    citySelect.appendChild(option);
                    if (city.name === preselectCity) {
                        option.selected = true;
                        cityCode = city.code;
                    }
                });

                if (cityCode) {
                    await loadDistricts(cityCode, preselectDistrict, preselectWard);
                } else {
                    document.getElementById('district').innerHTML = '<option value="">Chọn Quận/Huyện</option>';
                    document.getElementById('ward').innerHTML = '<option value="">Chọn Xã/Phường</option>';
                }
            } catch (error) {
                console.error('Error loading cities:', error);
            }
        }

        async function loadDistricts(cityCode, preselectDistrict = '', preselectWard = '') {
            const districtSelect = document.getElementById('district');
            districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
            try {
                const response = await fetch(`https://provinces.open-api.vn/api/p/${cityCode}?depth=2`);
                const data = await response.json();
                let districtCode = null;
                data.districts.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.name;
                    option.text = district.name;
                    option.dataset.code = district.code;
                    districtSelect.appendChild(option);
                    if (district.name === preselectDistrict) {
                        option.selected = true;
                        districtCode = district.code;
                    }
                });

                if (districtCode) {
                    await loadWards(districtCode, preselectWard);
                } else {
                    document.getElementById('ward').innerHTML = '<option value="">Chọn Xã/Phường</option>';
                }
            } catch (error) {
                console.error('Error loading districts:', error);
            }
        }

        async function loadWards(districtCode, preselectWard = '') {
            const wardSelect = document.getElementById('ward');
            wardSelect.innerHTML = '<option value="">Chọn Xã/Phường</option>';
            try {
                const response = await fetch(`https://provinces.open-api.vn/api/d/${districtCode}?depth=2`);
                const data = await response.json();
                data.wards.forEach(ward => {
                    const option = document.createElement('option');
                    option.value = ward.name;
                    option.text = ward.name;
                    wardSelect.appendChild(option);
                    if (ward.name === preselectWard) {
                        option.selected = true;
                    }
                });
            } catch (error) {
                console.error('Error loading wards:', error);
            }
        }

        document.getElementById('city').addEventListener('change', async function() {
            const cityCode = this.options[this.selectedIndex].dataset.code;
            document.getElementById('district').innerHTML = '<option value="">Chọn Quận/Huyện</option>';
            document.getElementById('ward').innerHTML = '<option value="">Chọn Xã/Phường</option>';
            if (cityCode) {
                await loadDistricts(cityCode);
            }
        });

        document.getElementById('district').addEventListener('change', async function() {
            const districtCode = this.options[this.selectedIndex].dataset.code;
            document.getElementById('ward').innerHTML = '<option value="">Chọn Xã/Phường</option>';
            if (districtCode) {
                await loadWards(districtCode);
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
            const productList = document.getElementById("product-list");
            let checkoutItemsNow = JSON.parse(sessionStorage.getItem("checkoutItemsNow")) || [];

            // Hàm cập nhật tổng tiền
            function updateTotal() {
                let total = 0;
                checkoutItemsNow.forEach(item => {
                    total += item.price * item.quantity;
                });
                const totalDiv = document.querySelector(".total-div");
                if (totalDiv) {
                    totalDiv.innerHTML = `
                        <span>Tổng cộng:</span>
                        <span>${total.toLocaleString("vi-VN", { style: "currency", currency: "VND" }).replace("₫", "đ")}</span>
                    `;
                }
                sessionStorage.setItem("checkoutItemsNow", JSON.stringify(checkoutItemsNow));
                return total;
            }

            // Hàm hiển thị danh sách sản phẩm
            function renderProducts() {
                productList.innerHTML = '';
                if (checkoutItemsNow.length === 0) {
                    productList.innerHTML = '<p class="text-center text-gray-500">Không có sản phẩm nào được chọn.</p>';
                    document.querySelector(".bg-green-500").disabled = true;
                    return;
                }

                document.querySelector(".bg-green-500").disabled = false;
                checkoutItemsNow.forEach((item, index) => {
                    if (!item.idProduct || !item.price || !item.quantity || !item.name || !item.image || !item.stock) {
                        productList.innerHTML = '<p class="text-center text-red-500">Dữ liệu sản phẩm không hợp lệ.</p>';
                        document.querySelector(".bg-green-500").disabled = true;
                        return;
                    }
                    const subtotal = item.price * item.quantity;
                    const productItem = document.createElement("div");
                    productItem.className = "flex items-center mb-4 pb-4 border-b";
                    productItem.dataset.index = index;
                    productItem.innerHTML = `
                        <img class="w-16 h-16 object-cover rounded mr-4" src="${item.image}" alt="${item.name}">
                        <div class="flex-1">
                            <p class="font-medium">${item.name}</p>
                            <p class="text-sm text-gray-500">Còn lại: ${item.stock}</p>
                            <div class="quantity-container mt-2">
                                <button class="decrease-btn" data-index="${index}">-</button>
                                <input type="number" class="quantity" value="${item.quantity}" min="1" max="${item.stock}" data-index="${index}">
                                <button class="increase-btn" data-index="${index}">+</button>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <p class="font-medium subtotal mr-4">${subtotal.toLocaleString("vi-VN", { style: "currency", currency: "VND" }).replace("₫", "đ")}</p>
                            <button class="remove-btn" data-index="${index}" title="Xóa"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    `;
                    productList.appendChild(productItem);
                });

                const totalDiv = document.createElement("div");
                totalDiv.className = "flex justify-between font-semibold mt-4 total-div";
                totalDiv.innerHTML = `
                    <span>Tổng cộng:</span>
                    <span>${updateTotal()}</span>
                `;
                productList.appendChild(totalDiv);
            }

            // Xử lý tăng số lượng
            productList.addEventListener("click", function(e) {
                if (e.target.classList.contains("increase-btn")) {
                    const index = parseInt(e.target.dataset.index);
                    const item = checkoutItemsNow[index];
                    if (item.quantity < item.stock) {
                        item.quantity += 1;
                        sessionStorage.setItem("checkoutItemsNow", JSON.stringify(checkoutItemsNow));
                        renderProducts();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Số lượng tối đa',
                            text: `Số lượng tối đa cho "${item.name}" là ${item.stock}.`,
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });

            // Xử lý giảm số lượng
            productList.addEventListener("click", function(e) {
                if (e.target.classList.contains("decrease-btn")) {
                    const index = parseInt(e.target.dataset.index);
                    const item = checkoutItemsNow[index];
                    if (item.quantity > 1) {
                        item.quantity -= 1;
                        sessionStorage.setItem("checkoutItemsNow", JSON.stringify(checkoutItemsNow));
                        renderProducts();
                    }
                }
            });

            // Xử lý nhập số lượng trực tiếp
            productList.addEventListener("input", function(e) {
                if (e.target.classList.contains("quantity")) {
                    const index = parseInt(e.target.dataset.index);
                    const item = checkoutItemsNow[index];
                    let value = parseInt(e.target.value);

                    if (isNaN(value) || value < 1) {
                        value = 1;
                        e.target.value = value;
                    } else if (value > item.stock) {
                        value = item.stock;
                        e.target.value = value;
                        Swal.fire({
                            icon: 'warning',
                            title: 'Số lượng tối đa',
                            text: `Số lượng tối đa cho "${item.name}" là ${item.stock}.`,
                            confirmButtonText: 'OK'
                        });
                    }

                    item.quantity = value;
                    sessionStorage.setItem("checkoutItemsNow", JSON.stringify(checkoutItemsNow));
                    renderProducts();
                }
            });

            // Xử lý xóa sản phẩm
            productList.addEventListener("click", function(e) {
                if (e.target.closest(".remove-btn")) {
                    const index = parseInt(e.target.closest(".remove-btn").dataset.index);
                    const item = checkoutItemsNow[index];
                    Swal.fire({
                        icon: 'warning',
                        title: 'Xóa sản phẩm',
                        text: `Bạn có chắc muốn xóa "${item.name}" khỏi đơn hàng?`,
                        showCancelButton: true,
                        confirmButtonText: 'Xóa',
                        cancelButtonText: 'Hủy'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            checkoutItemsNow.splice(index, 1);
                            sessionStorage.setItem("checkoutItemsNow", JSON.stringify(checkoutItemsNow));
                            renderProducts();
                            Swal.fire({
                                icon: 'success',
                                title: 'Đã xóa',
                                text: `"${item.name}" đã được xóa khỏi đơn hàng.`,
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });

            // Khởi tạo danh sách sản phẩm
            renderProducts();

            <?php if ($loggedIn && $missingInfo && !isset($_SESSION['shipping_info'])): ?>
                openAddressModal();
            <?php endif; ?>
        });

        function checkout() {
            if (!isLoggedIn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Chưa đăng nhập!',
                    text: 'Vui lòng đăng nhập để thanh toán.',
                    confirmButtonText: 'Đăng nhập',
                    showCancelButton: true,
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'login.php';
                    }
                });
                return;
            }

            if (!<?php echo isset($_SESSION['shipping_info']) || !$missingInfo ? 'true' : 'false'; ?>) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Thiếu thông tin giao hàng!',
                    text: 'Vui lòng cập nhật thông tin giao hàng trước khi thanh toán.',
                    confirmButtonText: 'Cập nhật'
                }).then(() => {
                    openAddressModal();
                });
                return;
            }

            const checkoutItemsNow = JSON.parse(sessionStorage.getItem("checkoutItemsNow")) || [];
            if (checkoutItemsNow.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Không có sản phẩm!',
                    text: 'Vui lòng chọn sản phẩm để thanh toán.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            const form = document.createElement("form");
            form.method = "POST";
            form.action = "";

            const itemsInput = document.createElement("input");
            itemsInput.type = "hidden";
            itemsInput.name = "items";
            itemsInput.value = JSON.stringify(checkoutItemsNow);
            form.appendChild(itemsInput);

            const actionInput = document.createElement("input");
            actionInput.type = "hidden";
            actionInput.name = "action";
            actionInput.value = "process_checkout_buynow";
            form.appendChild(actionInput);

            document.body.appendChild(form);
            form.submit();
        }
    </script>

    <?php
    if ($loggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_checkout_buynow') {
        $cart = new cart();
        $maTaiKhoan = $_SESSION['customer_id'];
        $items = json_decode($_POST['items'] ?? '[]', true);
        $shippingInfo = $_SESSION['shipping_info'] ?? null;

        if (empty($items)) {
            echo "<script>Swal.fire({icon: 'error', title: 'Lỗi', text: 'Không có sản phẩm để thanh toán', confirmButtonText: 'OK'}).then(() => { window.history.back(); });</script>";
            exit;
        }

        if (empty($shippingInfo) && $missingInfo) {
            echo "<script>Swal.fire({icon: 'error', title: 'Lỗi', text: 'Thông tin giao hàng không đầy đủ', confirmButtonText: 'OK'}).then(() => { window.history.back(); });</script>";
            exit;
        }

        if (empty($shippingInfo) && !$missingInfo) {
            $shippingInfo = [
                'fullName' => $customerInfo['tenKhachHang'],
                'phone' => $customerInfo['soDT'],
                'email' => $customerInfo['email'] ?? '',
                'address' => '',
                'ward' => '',
                'district' => '',
                'city' => $customerInfo['diaChi']
            ];
        }

        $result = $cart->process_checkout_buynow($maTaiKhoan, $items, $shippingInfo);

        if ($result['success']) {
            unset($_SESSION['shipping_info']);
            echo "<script>
                sessionStorage.removeItem('checkoutItemsNow');
                Swal.fire({
                    icon: 'success',
                    title: 'Đặt hàng thành công',
                    text: 'Cảm ơn bạn đã mua hàng!',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'index.php';
                });
            </script>";
        } else {
            $errorMessage = htmlspecialchars($result['message']);
            echo "<script>Swal.fire({icon: 'error', title: 'Lỗi', text: '$errorMessage', confirmButtonText: 'OK'}).then(() => { window.history.back(); });</script>";
        }
        exit;
    }
    ?>
</body>
</html>