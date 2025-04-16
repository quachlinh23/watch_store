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
    $customerInfo = $customer->getinforcustomerbyid($customerId)->fetch_assoc();
    $missingInfo = empty($customerInfo['tenKhachHang']) || empty($customerInfo['soDT']) || empty($customerInfo['diaChi']);
}

if ($loggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullName'])) {
    $customer = new customerlogin();
    $customerId = $_SESSION['customer_id'];
    
    // Get form data
    $fullName = $_POST['fullName'];
    $phone = $_POST['phone'];
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'];
    $ward = $_POST['ward'];
    $district = $_POST['district'];
    $city = $_POST['city'];

    // Construct the full address
    $fullAddress = "$address, $ward, $district, $city";

    // Call updateCustomerInfo to save to the database
    $updateResult = $customer_info->updateCustomerInfo($customerId, $fullName, $fullAddress, $phone, $email);

    if ($updateResult === true) {
        // Update session data
        $_SESSION['shipping_info'] = [
            'fullName' => $fullName,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'ward' => $ward,
            'district' => $district,
            'city' => $city
        ];

        $addressDisplay = $fullAddress;
        echo "<script>
            document.querySelector('.address-content').textContent = '$addressDisplay';
            document.getElementById('addressModal').style.display = 'none';
            document.body.classList.remove('no-scroll');
            alert('Thông tin giao hàng đã được lưu!');
        </script>";
    } else {
        // Display error message from updateCustomerInfo
        echo "<script>
            document.getElementById('addressModal').style.display = 'none';
            document.body.classList.remove('no-scroll');
            alert('Lỗi: $updateResult');
        </script>";
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
    <title>Watch Store</title>
    <style>
        .no-scroll { overflow: hidden; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; margin: 5% auto; padding: 20px; width: 90%; max-width: 600px; border-radius: 8px; }
        .close { float: right; font-size: 24px; cursor: pointer; }
        select, input { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto p-4 max-w-7xl">
        <h2 class="text-2xl font-bold text-center mb-6">Thanh toán</h2>
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
                                $name = isset($_SESSION['shipping_info']) ? $_SESSION['shipping_info']['fullName'] : $customerInfo['tenKhachHang'];
                                $phone = isset($_SESSION['shipping_info']) ? $_SESSION['shipping_info']['phone'] : $customerInfo['soDT'];
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
                                        : $customerInfo['diaChi'];
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
                <div class="product-list" id="product-list">
                    <!-- Products will be added dynamically -->
                </div>
                <div class="mt-4 flex justify-between">
                    <button class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400" onclick="window.location.href='index.php'">Hủy đơn</button>
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
                    <input type="text" id="fullName" name="fullName" value="<?php echo isset($_SESSION['shipping_info']) ? $_SESSION['shipping_info']['fullName'] : ($loggedIn ? $customerInfo['tenKhachHang'] : ''); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="phone" class class="block text-sm font-medium">Số điện thoại:</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo isset($_SESSION['shipping_info']) ? $_SESSION['shipping_info']['phone'] : ($loggedIn ? $customerInfo['soDT'] : ''); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_SESSION['shipping_info']) ? $_SESSION['shipping_info']['email'] : ''; ?>">
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
                    <input type="text" id="address" name="address" value="<?php echo isset($_SESSION['shipping_info']) ? $_SESSION['shipping_info']['address'] : ($loggedIn ? $customerInfo['diaChi'] : ''); ?>" required>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">Lưu thông tin</button>
            </form>
        </div>
    </div>

    <script>
        const isLoggedIn = <?php echo $loggedIn ? 'true' : 'false'; ?>;
        const storedAddress = "<?php echo isset($_SESSION['shipping_info']) 
            ? $_SESSION['shipping_info']['address'] . ', ' . $_SESSION['shipping_info']['ward'] . ', ' . $_SESSION['shipping_info']['district'] . ', ' . $_SESSION['shipping_info']['city']
            : ($loggedIn ? $customerInfo['diaChi'] : ''); ?>";

        function openAddressModal() {
            if (!isLoggedIn) {
                alert('Vui lòng đăng nhập để mua hàng!');
                window.location.href = 'login.php';
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

        // Parse the stored address and preselect dropdowns
        async function parseAndPreselectAddress(address) {
            if (!address) {
                console.log("No address provided");
                await loadCities(); // Load cities without pre-selection
                return;
            }

            console.log("Stored address:", address);

            // Split the address into components
            const addressParts = address.split(',').map(part => part.trim());
            console.log("Address parts:", addressParts);

            // Handle both formats: "detailedAddress, ward, district, city" (4 parts) or "detailedAddress, district, city" (3 parts)
            let detailedAddress, ward, district, city;

            if (addressParts.length === 4) {
                // Format: "detailedAddress, ward, district, city"
                detailedAddress = addressParts[0];
                ward = addressParts[1];
                district = addressParts[2];
                city = addressParts[3];
            } else if (addressParts.length === 3) {
                // Format: "detailedAddress, district, city"
                detailedAddress = addressParts[0];
                ward = ''; // Ward is not provided
                district = addressParts[1];
                city = addressParts[2];
            } else {
                console.log("Invalid address format, loading cities without pre-selection");
                await loadCities();
                return;
            }

            console.log("Parsed - Detailed:", detailedAddress, "Ward:", ward, "District:", district, "City:", city);

            // Populate the detailed address input
            document.getElementById('address').value = detailedAddress;

            // Load cities and preselect
            await loadCities(city, district, ward);
        }

        // Load address data from API
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

                console.log("Preselected city:", preselectCity, "City code:", cityCode);

                // If a city was preselected, load districts
                if (cityCode) {
                    await loadDistricts(cityCode, preselectDistrict, preselectWard);
                } else {
                    console.log("City not found in API data, loading districts without pre-selection");
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

                console.log("Preselected district:", preselectDistrict, "District code:", districtCode);

                // If a district was preselected, load wards
                if (districtCode) {
                    await loadWards(districtCode, preselectWard);
                } else {
                    console.log("District not found in API data, loading wards without pre-selection");
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

                console.log("Preselected ward:", preselectWard);
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
            const checkoutItems = JSON.parse(sessionStorage.getItem("checkoutItems")) || [];

            if (checkoutItems.length === 0) {
                productList.innerHTML = '<p class="text-center text-gray-500">Không có sản phẩm nào được chọn.</p>';
                document.querySelector(".bg-green-500").disabled = true;
                return;
            }

            let total = 0;
            checkoutItems.forEach(item => {
                const subtotal = item.price * item.quantity;
                total += subtotal;
                const productItem = document.createElement("div");
                productItem.className = "flex items-center mb-4 pb-4 border-b";
                productItem.innerHTML = `
                    <img class="w-16 h-16 object-cover rounded mr-4" src="${item.image}" alt="${item.name}">
                    <div class="flex-1">
                        <p class="font-medium">${item.name}</p>
                        <p class="text-sm text-gray-500">Còn lại: ${item.stock}</p>
                        <p class="text-sm text-gray-500">Số lượng: ${item.quantity}</p>
                    </div>
                    <p class="font-medium">${(item.price * item.quantity).toLocaleString("vi-VN")}đ</p>
                `;
                productList.appendChild(productItem);
            });

            const totalDiv = document.createElement("div");
            totalDiv.className = "flex justify-between font-semibold mt-4";
            totalDiv.innerHTML = `
                <span>Tổng cộng:</span>
                <span>${total.toLocaleString("vi-VN")}đ</span>
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
        $maTaiKhoan = $_SESSION['customer_id'];
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