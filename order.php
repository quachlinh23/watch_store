<?php
	include_once "class/brand.php";
	$brand = new brand();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn Đặt Hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/order.css">
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
</head>
<body>
<?php include 'layout/header.php'; ?>
    <div class="cart-container">
        <h2>Đơn Đặt Hàng</h2>
        <div class="cart-content">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>28/03/2025</td>
                        <td>1,500,000 VNĐ</td>
                        <td>Đang xử lý</td>
                        <td class="action-buttons">
                            <button class="btn-view" onclick="showInvoiceDetails(1)">Xem chi tiết</button>
                            <button class="btn-cancel">Hủy đơn</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>27/03/2025</td>
                        <td>2,700,000 VNĐ</td>
                        <td>Hoàn thành</td>
                        <td class="action-buttons">
                            <button class="btn-view" onclick="showInvoiceDetails(2)">Xem chi tiết</button>
                            <button class="btn-cancel">Hủy đơn</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>27/03/2025</td>
                        <td>2,700,000 VNĐ</td>
                        <td>Hoàn thành</td>
                        <td class="action-buttons">
                            <button class="btn-view" onclick="showInvoiceDetails(2)">Xem chi tiết</button>
                            <button class="btn-cancel">Hủy đơn</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>27/03/2025</td>
                        <td>2,700,000 VNĐ</td>
                        <td>Hoàn thành</td>
                        <td class="action-buttons">
                            <button class="btn-view" onclick="showInvoiceDetails(2)">Xem chi tiết</button>
                            <button class="btn-cancel">Hủy đơn</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>27/03/2025</td>
                        <td>2,700,000 VNĐ</td>
                        <td>Hoàn thành</td>
                        <td class="action-buttons">
                            <button class="btn-view" onclick="showInvoiceDetails(2)">Xem chi tiết</button>
                            <button class="btn-cancel">Hủy đơn</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>27/03/2025</td>
                        <td>2,700,000 VNĐ</td>
                        <td>Hoàn thành</td>
                        <td class="action-buttons">
                            <button class="btn-view" onclick="showInvoiceDetails(2)">Xem chi tiết</button>
                            <button class="btn-cancel">Hủy đơn</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>27/03/2025</td>
                        <td>2,700,000 VNĐ</td>
                        <td>Hoàn thành</td>
                        <td class="action-buttons">
                            <button class="btn-view" onclick="showInvoiceDetails(2)">Xem chi tiết</button>
                            <button class="btn-cancel">Hủy đơn</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>27/03/2025</td>
                        <td>2,700,000 VNĐ</td>
                        <td>Hoàn thành</td>
                        <td class="action-buttons">
                            <button class="btn-view" onclick="showInvoiceDetails(2)">Xem chi tiết</button>
                            <button class="btn-cancel">Hủy đơn</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>27/03/2025</td>
                        <td>2,700,000 VNĐ</td>
                        <td>Hoàn thành</td>
                        <td class="action-buttons">
                            <button class="btn-view" onclick="showInvoiceDetails(2)">Xem chi tiết</button>
                            <button class="btn-cancel">Hủy đơn</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>27/03/2025</td>
                        <td>2,700,000 VNĐ</td>
                        <td>Hoàn thành</td>
                        <td class="action-buttons">
                            <button class="btn-view" onclick="showInvoiceDetails(2)">Xem chi tiết</button>
                            <button class="btn-cancel">Hủy đơn</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>27/03/2025</td>
                        <td>2,700,000 VNĐ</td>
                        <td>Hoàn thành</td>
                        <td class="action-buttons">
                            <button class="btn-view" onclick="showInvoiceDetails(2)">Xem chi tiết</button>
                            <button class="btn-cancel">Hủy đơn</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php include 'layout/footer.php'; ?>

    <!-- Modal Chi tiết hóa đơn -->
    <div id="invoiceDetailModal" class="modal-overlay" style="display: none;">
        <div class="modal-window">
            <span class="modal-close" onclick="closeModal()">×</span>
            <h2>Chi tiết hóa đơn</h2>
            <hr>
            <div id="invoiceDetails">
                <div class="info-table">
                    <table>
                        <tr>
                            <td><div class="info-item"><label>Mã hóa đơn: <span id="invoiceCode"></span></label></div></td>
                            <td><div class="info-item"><label>Khách hàng: <span id="customerName"></span></label></div></td>
                            <td><div class="info-item"><label>Số điện thoại: <span id="customerPhone"></span></label></div></td>
                        </tr>
                        <tr>
                            <td><div class="info-item"><label>Địa chỉ: <span id="customerAddress"></span></label></div></td>
                            <td><div class="info-item"><label>Trạng thái: <span id="orderStatus"></span></label></div></td>
                        </tr>
                    </table>
                </div>
                <div class="product-section">
                    <label class="section-label">Sản phẩm:</label>
                    <div class="table-wrapper">
                        <table id="productList">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Mã sản phẩm</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Đơn giá</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody id="productListBody">
                                <!-- Dữ liệu sản phẩm sẽ được điền bằng JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="total-section">
                    <label>Tổng tiền: <span id="totalAmount"></span></label>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dữ liệu mẫu (có thể thay bằng dữ liệu từ database)
        const invoiceData = {
            1: {
                invoiceCode: "HD001",
                customerName: "Nguyễn Văn A",
                customerPhone: "0901234567",
                customerAddress: "123 Đường Láng, Hà Nội",
                orderStatus: "Đang xử lý",
                products: [
                    { id: "SP001", name: "Áo thun", quantity: 2, price: "200,000 VNĐ", total: "400,000 VNĐ" },
                    { id: "SP002", name: "Quần jeans", quantity: 1, price: "500,000 VNĐ", total: "500,000 VNĐ" },
                    { id: "SP003", name: "Giày thể thao", quantity: 1, price: "300,000 VNĐ", total: "300,000 VNĐ" },
                    { id: "SP004", name: "Mũ lưỡi trai", quantity: 1, price: "100,000 VNĐ", total: "100,000 VNĐ" },
                    { id: "SP005", name: "Túi xách", quantity: 1, price: "200,000 VNĐ", total: "200,000 VNĐ" }
                ],
                totalAmount: "1,500,000 VNĐ"
            },
            2: {
                invoiceCode: "HD002",
                customerName: "Trần Thị B",
                customerPhone: "0912345678",
                customerAddress: "456 Nguyễn Trãi, TP.HCM",
                orderStatus: "Hoàn thành",
                products: [
                    { id: "SP003", name: "Giày thể thao", quantity: 1, price: "1,000,000 VNĐ", total: "1,000,000 VNĐ" },
                    { id: "SP004", name: "Túi xách", quantity: 1, price: "800,000 VNĐ", total: "800,000 VNĐ" },
                    { id: "SP005", name: "Áo khoác", quantity: 1, price: "400,000 VNĐ", total: "400,000 VNĐ" },
                    { id: "SP006", name: "Kính râm", quantity: 2, price: "150,000 VNĐ", total: "300,000 VNĐ" },
                    { id: "SP007", name: "Dép sandal", quantity: 1, price: "200,000 VNĐ", total: "200,000 VNĐ" }
                ],
                totalAmount: "2,700,000 VNĐ"
            }
        };

        // Hàm hiển thị modal chi tiết hóa đơn
        function showInvoiceDetails(invoiceId) {
            const data = invoiceData[invoiceId];
            if (data) {
                document.getElementById("invoiceCode").textContent = data.invoiceCode;
                document.getElementById("customerName").textContent = data.customerName;
                document.getElementById("customerPhone").textContent = data.customerPhone;
                document.getElementById("customerAddress").textContent = data.customerAddress;
                document.getElementById("orderStatus").textContent = data.orderStatus;
                document.getElementById("totalAmount").textContent = data.totalAmount;

                const productListBody = document.getElementById("productListBody");
                productListBody.innerHTML = ""; // Xóa dữ liệu cũ
                data.products.forEach((product, index) => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${product.id}</td>
                        <td>${product.name}</td>
                        <td>${product.quantity}</td>
                        <td>${product.price}</td>
                        <td>${product.total}</td>
                    `;
                    productListBody.appendChild(row);
                });

                document.getElementById("invoiceDetailModal").style.display = "flex";
            }
        }

        // Hàm đóng modal
        function closeModal() {
            document.getElementById("invoiceDetailModal").style.display = "none";
        }

        // Đóng modal khi nhấp ra ngoài
        document.getElementById("invoiceDetailModal").addEventListener("click", function(event) {
            if (event.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>