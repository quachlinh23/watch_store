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
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const decreaseBtns = document.querySelectorAll(".decrease-btn");
            const increaseBtns = document.querySelectorAll(".increase-btn");
            const quantityInputs = document.querySelectorAll(".quantity");
            const totalDisplay = document.getElementById("total");

            function updateTotal() {
                let total = 0;
                document.querySelectorAll("tbody tr").forEach(row => {
                    const price = parseFloat(row.querySelector(".price").textContent.replace("đ", "").replace(",", ""));
                    const quantity = parseInt(row.querySelector(".quantity").value);
                    row.querySelector(".total-price").textContent = (price * quantity).toLocaleString() + "đ";
                    total += price * quantity;
                });
                totalDisplay.textContent = total.toLocaleString() + "đ";
            }

            decreaseBtns.forEach(btn => {
                btn.addEventListener("click", function () {
                    let input = this.nextElementSibling;
                    if (input.value > 1) {
                        input.value--;
                        updateTotal();
                    }
                });
            });

            increaseBtns.forEach(btn => {
                btn.addEventListener("click", function () {
                    let input = this.previousElementSibling;
                    let maxStock = parseInt(this.closest("tr").querySelector(".conlai span").textContent.replace("Còn lại: ", ""));
                    if (parseInt(input.value) < maxStock) {
                        input.value++;
                        updateTotal();
                    }
                });
            });

            quantityInputs.forEach(input => {
                input.addEventListener("change", function () {
                    let maxStock = parseInt(this.closest("tr").querySelector(".conlai span").textContent.replace("Còn lại: ", ""));
                    if (this.value < 1) {
                        this.value = 1;
                    } else if (this.value > maxStock) {
                        this.value = maxStock;
                    }
                    updateTotal();
                });
            });

            updateTotal();
        });


    </script>
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
    <?php include 'layout/header.php'; ?>
    <div class="cart-container">
        <h2><i class="fa-solid fa-cart-shopping"></i> Giỏ Hàng Của Bạn</h2>
        <div class="cart-content">
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
                    <tr data-stock="10">
                        <td><input type="checkbox" class="select-item" checked></td>
                        <td class="product-info">
                            <img src="" alt="Sản phẩm" class="product-img">
                            <span class="product-name">Tên sản phẩm</span>
                        </td>
                        <td class="price">100,000đ</td>
                        <td>
                            <div class="conlai">
                                <span>Còn lại: 11</span>
                            </div>
                            <div class="quantity-container">
                                <button class="decrease-btn">-</button>
                                <input type="number" class="quantity" value="1" min="1">
                                <button class="increase-btn">+</button>
                            </div>
                        </td>
                        <td class="total-price">100,000đ</td>
                        <td><button class="remove-btn"><i class="fa-solid fa-trash"></i></button></td>
                    </tr>
                </tbody>
                <tbody id="cart-items">
                    <tr data-stock="10">
                        <td><input type="checkbox" class="select-item" checked></td>
                        <td class="product-info">
                            <img src="" alt="Sản phẩm" class="product-img">
                            <span class="product-name">Tên sản phẩm</span>
                        </td>
                        <td class="price">100,000đ</td>
                        <td>
                            <div class="conlai">
                                <span>Còn lại: 10</span>
                            </div>
                            <div class="quantity-container">
                                <button class="decrease-btn">-</button>
                                <input type="number" class="quantity" value="1" min="1">
                                <button class="increase-btn">+</button>
                            </div>
                        </td>
                        <td class="total-price">100,000đ</td>
                        <td><button class="remove-btn"><i class="fa-solid fa-trash"></i></button></td>
                    </tr>
                </tbody>
                <tbody id="cart-items">
                    <tr data-stock="10">
                        <td><input type="checkbox" class="select-item" checked></td>
                        <td class="product-info">
                            <img src="" alt="Sản phẩm" class="product-img">
                            <span class="product-name">Tên sản phẩm</span>
                        </td>
                        <td class="price">100,000đ</td>
                        <td>
                            <div class="conlai">
                                <span>Còn lại: 10</span>
                            </div>
                            <div class="quantity-container">
                                <button class="decrease-btn">-</button>
                                <input type="number" class="quantity" value="1" min="1">
                                <button class="increase-btn">+</button>
                            </div>
                        </td>
                        <td class="total-price">100,000đ</td>
                        <td><button class="remove-btn"><i class="fa-solid fa-trash"></i></button></td>
                    </tr>
                </tbody>
                <tbody id="cart-items">
                    <tr data-stock="10">
                        <td><input type="checkbox" class="select-item" checked></td>
                        <td class="product-info">
                            <img src="" alt="Sản phẩm" class="product-img">
                            <span class="product-name">Tên sản phẩm</span>
                        </td>
                        <td class="price">100,000đ</td>
                        <td>
                            <div class="conlai">
                                <span>Còn lại: 10</span>
                            </div>
                            <div class="quantity-container">
                                <button class="decrease-btn">-</button>
                                <input type="number" class="quantity" value="1" min="1">
                                <button class="increase-btn">+</button>
                            </div>
                        </td>
                        <td class="total-price">100,000đ</td>
                        <td><button class="remove-btn"><i class="fa-solid fa-trash"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="cart-summary">
            <h3>Tổng cộng: <span id="total">500,000đ</span></h3>
            <button class="checkout-btn">Thanh Toán</button>
        </div>
    </div>
    <?php include 'layout/footer.php'; ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const selectAllCheckbox = document.getElementById("select-all");
            const itemCheckboxes = document.querySelectorAll(".select-item");

            // Xử lý sự kiện khi click vào "Select All"
            selectAllCheckbox.addEventListener("change", function () {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });

            // Xử lý khi các checkbox sản phẩm được thay đổi
            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener("change", function () {
                    if (!this.checked) {
                        selectAllCheckbox.checked = false; // Nếu có 1 sản phẩm bị bỏ chọn, bỏ chọn "Select All"
                    } else if (document.querySelectorAll(".select-item:checked").length === itemCheckboxes.length) {
                        selectAllCheckbox.checked = true; // Nếu tất cả đều được chọn lại, check "Select All"
                    }
                });
            });
        });
        document.addEventListener("DOMContentLoaded", function () {
            const selectAllCheckbox = document.getElementById("select-all");
            const itemCheckboxes = document.querySelectorAll(".select-item");
            const totalDisplay = document.getElementById("total");

            function updateRowTotal(row) {
                const price = parseFloat(row.querySelector(".price").textContent.replace("đ", "").replace(",", ""));
                const quantity = parseInt(row.querySelector(".quantity").value);
                const totalPriceCell = row.querySelector(".total-price");

                const itemTotal = price * quantity;
                totalPriceCell.textContent = itemTotal.toLocaleString() + "đ"; // Cập nhật tổng tiền của từng sản phẩm
            }

            function updateTotal() {
                let total = 0;
                document.querySelectorAll("tbody tr").forEach(row => {
                    updateRowTotal(row); // Luôn cập nhật tổng tiền của từng sản phẩm
                    
                    const checkbox = row.querySelector(".select-item");
                    const totalPriceCell = row.querySelector(".total-price");
                    const itemTotal = parseFloat(totalPriceCell.textContent.replace("đ", "").replace(",", ""));

                    if (checkbox.checked) {
                        total += itemTotal; // Chỉ cộng vào tổng nếu checkbox được chọn
                    }
                });
                totalDisplay.textContent = total.toLocaleString() + "đ";
            }

            selectAllCheckbox.addEventListener("change", function () {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                updateTotal();
            });

            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener("change", updateTotal);
            });

            document.querySelectorAll(".increase-btn").forEach(btn => {
                btn.addEventListener("click", function () {
                    let input = this.previousElementSibling;
                    let maxStock = parseInt(this.closest("tr").querySelector(".conlai span").textContent.replace("Còn lại: ", ""));
                    if (parseInt(input.value) < maxStock) {
                        input.value++;
                        updateTotal(); // Luôn cập nhật tổng tiền của từng sản phẩm và tổng tiền chung (nếu checkbox được chọn)
                    }
                });
            });

            document.querySelectorAll(".decrease-btn").forEach(btn => {
                btn.addEventListener("click", function () {
                    let input = this.nextElementSibling;
                    if (parseInt(input.value) > 1) {
                        input.value--;
                        updateTotal(); // Luôn cập nhật tổng tiền của từng sản phẩm và tổng tiền chung (nếu checkbox được chọn)
                    }
                });
            });

            document.querySelectorAll(".quantity").forEach(input => {
                input.addEventListener("change", function () {
                    let maxStock = parseInt(this.closest("tr").querySelector(".conlai span").textContent.replace("Còn lại: ", ""));
                    if (this.value < 1) {
                        this.value = 1;
                    } else if (this.value > maxStock) {
                        this.value = maxStock;
                    }
                    updateTotal(); // Luôn cập nhật tổng tiền của từng sản phẩm và tổng tiền chung (nếu checkbox được chọn)
                });
            });

            updateTotal();
        });
    </script>
</body>
</html>