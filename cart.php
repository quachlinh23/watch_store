<?php
session_start();
include_once "class/brand.php";
include_once "class/cart.php";

// Tắt hiển thị lỗi PHP để tránh nhiễu JSON
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);

$cart = new cart();
$brand = new brand();
$userId = $_SESSION['customer_id'] ?? null;

$idcart = $cart->getcartidbycustomer($userId);

// Xử lý AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $idProduct = (int)$_POST['idProduct'] ?? 0;
    $soluong = isset($_POST['soluong']) ? (int)$_POST['soluong'] : null;

    if ($idcart === 0 || $idProduct === 0) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin cần thiết']);
        exit;
    }

    switch ($action) {
        case 'increment':
            $response = $cart->increment_quantity($idcart, $idProduct);
            break;
        case 'decrement':
            $response = $cart->decrement_quantity($idcart, $idProduct);
            break;
        case 'update':
            $response = $cart->update_quantity($idcart, $idProduct, $soluong);
            break;
        case 'remove':
            $response = $cart->remove_from_cart($idcart, $idProduct);
            break;
        default:
            $response = ['success' => false, 'message' => 'Hành động không hợp lệ'];
    }
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Store - Giỏ Hàng</title>
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include 'layout/header.php'; ?>
    <div class="cart-container">
        <h2>Giỏ Hàng Của Bạn</h2>
        <table class="cart-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all" checked></th>
                    <th>Sản phẩm</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="cart-items">
                <?php
                $products = $cart->showCart($userId);
                if ($products && !empty($products)) {
                    foreach ($products as $item):
                ?>
                    <tr data-product-id="<?= htmlspecialchars($item['maSanPham']) ?>"
                        data-stock="<?= htmlspecialchars($item['soluongTon']) ?>"
                        data-price="<?= htmlspecialchars($item['giaban']) ?>">
                        <td><input type="checkbox" class="select-item" checked></td>
                        <td class="product-info">
                            <img src="admin/<?= htmlspecialchars($item['hinhAnh']) ?>" alt="Sản phẩm" class="product-img">
                            <span class="product-name"><?= htmlspecialchars($item['tenSanPham']) ?></span>
                        </td>
                        <td class="price"><?= number_format($item['giaban'], 0, ',', '.') ?> đ</td>
                        <td>
                            <div class="quantity-container">
                                <button class="decrease-btn">-</button>
                                <input type="number" class="quantity" value="<?= htmlspecialchars($item['soLuong']) ?>" min="1">
                                <button class="increase-btn">+</button>
                            </div>
                            <div class="conlai">
                                <span><span class="available"><?= htmlspecialchars($item['soluongTon']) ?></span> sản phẩm có sẵn</span>
                            </div>
                        </td>
                        <td class="total-price"><?= number_format($item['thanhTien'], 0, ',', '.') ?> đ</td>
                        <td><button title="Xóa" class="remove-btn"><i class="fa-solid fa-trash"></i></button></td>
                    </tr>
                <?php
                    endforeach;
                } else {
                ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px;">Giỏ hàng của bạn đang trống.</td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
        <div class="cart-summary">
            <h3>Tổng cộng: <span id="total"><?= number_format($products[0]['tongTien'] ?? 0, 0, ',', '.') ?> đ</span></h3>
            <button class="checkout-btn">Thanh Toán</button>
        </div>
    </div>
    <?php include 'layout/footer.php'; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const selectAllCheckbox = document.getElementById("select-all");
            const cartItemsContainer = document.getElementById("cart-items");
            let itemCheckboxes = document.querySelectorAll(".select-item");
            const totalDisplay = document.getElementById("total");
            const cartId = <?= json_encode($idcart) ?>;

            // Hàm vô hiệu hóa dòng hết hàng
            function disableOutOfStockRow(row) {
                const stock = parseInt(row.getAttribute("data-stock"));
                const checkbox = row.querySelector(".select-item");
                const input = row.querySelector(".quantity");
                const increaseBtn = row.querySelector(".increase-btn");
                const decreaseBtn = row.querySelector(".decrease-btn");
                const availableText = row.querySelector(".conlai .available");

                availableText.textContent = stock;
                availableText.style.color = stock === 0 ? "red" : "inherit";

                if (stock === 0) {
                    checkbox.checked = false;
                    checkbox.disabled = true;
                    input.value = 0;
                    input.disabled = true;
                    increaseBtn.disabled = true;
                    decreaseBtn.disabled = true;
                    row.style.pointerEvents = "none";
                    row.style.opacity = "0.6";
                    const removeBtn = row.querySelector(".remove-btn");
                    removeBtn.style.pointerEvents = "auto";
                } else {
                    checkbox.disabled = false;
                    input.disabled = false;
                    increaseBtn.disabled = false;
                    decreaseBtn.disabled = false;
                    row.style.pointerEvents = "auto";
                    row.style.opacity = "1";
                }
            }

            // Áp dụng vô hiệu hóa
            document.querySelectorAll("#cart-items tr").forEach(row => {
                disableOutOfStockRow(row);
            });

            // Hàm cập nhật tổng tiền của một hàng
            function updateRowTotal(row, soLuong, thanhTien, soluongTon = null) {
                const totalPriceCell = row.querySelector(".total-price");
                const quantityInput = row.querySelector(".quantity");
                const availableText = row.querySelector(".conlai .available");

                // Cập nhật số lượng và tổng tiền
                quantityInput.value = soLuong;
                totalPriceCell.textContent = thanhTien.toLocaleString("vi-VN") + " đ";

                // Cập nhật số lượng tồn kho nếu có
                if (soluongTon !== null) {
                    row.setAttribute("data-stock", soluongTon);
                    availableText.textContent = soluongTon;
                    disableOutOfStockRow(row);
                }
            }

            // Hàm cập nhật tổng tiền giỏ hàng
            function updateTotal(tongTien) {
                totalDisplay.textContent = tongTien.toLocaleString("vi-VN") + " đ";
                const rows = cartItemsContainer.querySelectorAll("tr");
                if (rows.length === 0 || rows[0].querySelector("td[colspan]")) {
                    cartItemsContainer.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">Giỏ hàng của bạn đang trống.</td></tr>';
                    totalDisplay.textContent = "0 đ";
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.disabled = true;
                }
            }

            // Xử lý chọn tất cả
            selectAllCheckbox.addEventListener("change", function () {
                itemCheckboxes.forEach(checkbox => {
                    if (!checkbox.disabled) {
                        checkbox.checked = this.checked;
                    }
                });
                let total = 0;
                itemCheckboxes.forEach(checkbox => {
                    if (checkbox.checked && !checkbox.disabled) {
                        const row = checkbox.closest("tr");
                        total += parseFloat(row.querySelector(".total-price").textContent.replace(" đ", "").replace(/\./g, ""));
                    }
                });
                updateTotal(total);
            });

            // Xử lý checkbox từng sản phẩm
            function handleItemCheckboxChange() {
                const enabledCheckboxes = Array.from(itemCheckboxes).filter(checkbox => !checkbox.disabled);
                selectAllCheckbox.checked = enabledCheckboxes.every(checkbox => checkbox.checked);
                let total = 0;
                itemCheckboxes.forEach(checkbox => {
                    if (checkbox.checked && !checkbox.disabled) {
                        const row = checkbox.closest("tr");
                        total += parseFloat(row.querySelector(".total-price").textContent.replace(" đ", "").replace(/\./g, ""));
                    }
                });
                updateTotal(total);
            }

            // Gắn sự kiện cho checkbox
            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener("change", handleItemCheckboxChange);
            });

            // Xử lý tăng số lượng
            cartItemsContainer.addEventListener("click", function (e) {
                if (e.target.classList.contains("increase-btn")) {
                    e.preventDefault();
                    const row = e.target.closest("tr");
                    const maxStock = parseInt(row.getAttribute("data-stock"));
                    let value = parseInt(row.querySelector(".quantity").value);

                    if (value < maxStock) {
                        $.ajax({
                            url: 'cart.php',
                            method: 'POST',
                            data: {
                                action: 'increment',
                                idcart: cartId,
                                idProduct: row.getAttribute("data-product-id")
                            },
                            success: function (response) {
                                try {
                                    const result = typeof response === 'string' ? JSON.parse(response.trim()) : response;
                                    if (result.success) {
                                        updateRowTotal(row, result.soLuong, result.thanhTien, result.soluongTon);
                                        updateTotal(result.tongTien);
                                        handleItemCheckboxChange();
                                        console.log("Tăng số lượng thành công", result.message);
                                    } else {
                                        alert(result.message);
                                    }
                                } catch (e) {
                                    console.error("Invalid JSON response:", response);
                                    alert("Có lỗi xảy ra khi xử lý phản hồi từ server.");
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error("AJAX error:", status, error);
                                alert("Có lỗi xảy ra khi tăng số lượng.");
                            }
                        });
                    } else {
                        alert(`Số lượng tối đa có thể chọn là ${maxStock}.`);
                    }
                }
            });

            // Xử lý giảm số lượng
            cartItemsContainer.addEventListener("click", function (e) {
                if (e.target.classList.contains("decrease-btn")) {
                    e.preventDefault();
                    const row = e.target.closest("tr");
                    let value = parseInt(row.querySelector(".quantity").value);

                    if (value > 1) {
                        $.ajax({
                            url: 'cart.php',
                            method: 'POST',
                            data: {
                                action: 'decrement',
                                idcart: cartId,
                                idProduct: row.getAttribute("data-product-id")
                            },
                            success: function (response) {
                                try {
                                    const result = typeof response === 'string' ? JSON.parse(response.trim()) : response;
                                    if (result.success) {
                                        updateRowTotal(row, result.soLuong, result.thanhTien, result.soluongTon);
                                        updateTotal(result.tongTien);
                                        handleItemCheckboxChange();
                                        console.log("Giảm số lượng thành công", result.message);
                                    } else {
                                        alert(result.message);
                                    }
                                } catch (e) {
                                    console.error("Invalid JSON response:", response);
                                    alert("Có lỗi xảy ra khi xử lý phản hồi từ server.");
                                }
                            },
                            error: function () {
                                alert("Có lỗi xảy ra khi giảm số lượng.");
                            }
                        });
                    }
                }
            });

            // Xử lý nhập số lượng trực tiếp
            cartItemsContainer.addEventListener("input", function (e) {
                if (e.target.classList.contains("quantity")) {
                    const row = e.target.closest("tr");
                    const maxStock = parseInt(row.getAttribute("data-stock"));
                    let value = parseInt(e.target.value);

                    if (isNaN(value) || value < 1) {
                        e.target.value = 1;
                        value = 1;
                    } else if (value > maxStock) {
                        e.target.value = maxStock;
                        value = maxStock;
                        alert(`Số lượng tối đa có thể chọn là ${maxStock}.`);
                    }

                    $.ajax({
                        url: 'cart.php',
                        method: 'POST',
                        data: {
                            action: 'update',
                            idcart: cartId,
                            idProduct: row.getAttribute("data-product-id"),
                            soluong: value
                        },
                        success: function (response) {
                            try {
                                const result = typeof response === 'string' ? JSON.parse(response.trim()) : response;
                                if (result.success) {
                                    updateRowTotal(row, result.soLuong, result.thanhTien, result.soluongTon);
                                    updateTotal(result.tongTien);
                                    handleItemCheckboxChange();
                                    console.log("Cập nhật thành công", result.message);
                                } else {
                                    alert(result.message);
                                }
                            } catch (e) {
                                console.error("Invalid JSON response:", response);
                                alert("Có lỗi xảy ra khi xử lý phản hồi từ server.");
                            }
                        },
                        error: function () {
                            alert("Có lỗi xảy ra khi cập nhật giỏ hàng.");
                        }
                    });
                }
            });

            // Xử lý xóa sản phẩm
            cartItemsContainer.addEventListener("click", function (e) {
                if (e.target.closest(".remove-btn")) {
                    const row = e.target.closest("tr");
                    const productId = row.getAttribute("data-product-id");

                    if (confirm("Bạn có chắc muốn xóa sản phẩm này?")) {
                        $.ajax({
                            url: 'cart.php',
                            method: 'POST',
                            data: {
                                action: 'remove',
                                idcart: cartId,
                                idProduct: productId
                            },
                            success: function (response) {
                                try {
                                    const result = typeof response === 'string' ? JSON.parse(response.trim()) : response;
                                    if (result.success) {
                                        row.remove();
                                        updateTotal(result.tongTien);
                                        itemCheckboxes = cartItemsContainer.querySelectorAll(".select-item");
                                        itemCheckboxes.forEach(checkbox => {
                                            checkbox.removeEventListener("change", handleItemCheckboxChange);
                                            checkbox.addEventListener("change", handleItemCheckboxChange);
                                        });
                                        console.log("Xóa thành công", result.message);
                                    } else {
                                        alert(result.message);
                                    }
                                } catch (e) {
                                    console.error("Invalid JSON response:", response);
                                    alert("Có lỗi xảy ra khi xử lý phản hồi từ server.");
                                }
                            },
                            error: function () {
                                alert("Có lỗi xảy ra khi xóa sản phẩm.");
                            }
                        });
                    }
                }
            });

            // Khởi tạo tổng tiền ban đầu
            handleItemCheckboxChange();
        });
    </script>

    <script>
        document.querySelector(".checkout-btn").addEventListener("click", function () {
            const selectedItems = [];
            document.querySelectorAll(".select-item:checked").forEach(checkbox => {
                const row = checkbox.closest("tr");
                selectedItems.push({
                    idProduct: row.getAttribute("data-product-id"),
                    quantity: parseInt(row.querySelector(".quantity").value),
                    price: parseFloat(row.getAttribute("data-price")),
                    name: row.querySelector(".product-name").textContent,
                    image: row.querySelector(".product-img").src,
                    stock: parseInt(row.getAttribute("data-stock"))
                });
            });

            if (selectedItems.length === 0) {
                alert("Vui lòng chọn ít nhất một sản phẩm để thanh toán.");
                return;
            }

            // Lưu danh sách sản phẩm vào sessionStorage
            sessionStorage.setItem("checkoutItems", JSON.stringify(selectedItems));

            // Chuyển hướng sang buynow.php
            window.location.href = "buy_now.php";
        });
    </script>
</body>
</html>