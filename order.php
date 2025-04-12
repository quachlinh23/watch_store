<?php
session_start();
include_once "class/brand.php";
include_once "class/cart.php"; // Đảm bảo đường dẫn đúng tới class cart

$cart = new cart();
$brand = new brand();

// Lấy danh sách phiếu xuất của người dùng
$userId = $_SESSION['customer_id'] ?? null; // Giả sử customer_id là maTaiKhoan
$invoices = $userId ? $cart->loadInvoiceOfUser($userId) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đơn đặt hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/order.css">
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
    <style>
        .btn-cancel:disabled {
            background-color: #cccccc;
            color: #666666;
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>
</head>
<body>
<?php include 'layout/header.php'; ?>
    <div class="cart-container">
        <h2>Lịch Sử Đơn Đặt Hàng</h2>
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
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Bạn chưa có đơn đặt hàng nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $index => $invoice): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($invoice['ngayLap'])); ?></td>
                                <td><?php echo number_format($invoice['tongTien'], 0, ',', '.') . ' VNĐ'; ?></td>
                                <td><?php echo $invoice['trangThai'] == 0 ? 'Đang xử lý' : 'Đã Duyệt'; ?></td>
                                <td class="action-buttons">
                                    <button class="btn-view" onclick='showInvoiceDetails(<?php echo json_encode($invoice); ?>)'>Xem chi tiết</button>
                                    <button class="btn-cancel" <?php echo $invoice['trangThai'] != 0 ? 'disabled' : ''; ?>>Hủy đơn</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
                                    <th>Tên sản phẩm</th>
                                    <th>Hình ảnh</th>
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
        // Hàm hiển thị modal chi tiết hóa đơn
        function showInvoiceDetails(invoice) {
            document.getElementById("invoiceCode").textContent = invoice.maphieuxuat;
            document.getElementById("customerName").textContent = invoice.customer.tenKhachHang;
            document.getElementById("customerPhone").textContent = invoice.customer.soDT;
            document.getElementById("customerAddress").textContent = invoice.customer.diaChi;
            document.getElementById("orderStatus").textContent = invoice.trangThai === 0 ? "Đang xử lý" : "Hoàn thành";
            document.getElementById("totalAmount").textContent = invoice.tongTien.toLocaleString('vi-VN') + " VNĐ";

            const productListBody = document.getElementById("productListBody");
            productListBody.innerHTML = ""; // Xóa dữ liệu cũ
            invoice.items.forEach((product, index) => {
                const total = product.soLuongXuat * product.giaban;
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${product.tenSanPham}</td>
                    <td><img src="admin/${product.hinhAnh}" alt="${product.tenSanPham}" style="width: 50px; height: 50px;"></td>
                    <td>${product.soLuongXuat}</td>
                    <td>${product.giaban.toLocaleString('vi-VN')} VNĐ</td>
                    <td>${total.toLocaleString('vi-VN')} VNĐ</td>
                `;
                productListBody.appendChild(row);
            });

            document.getElementById("invoiceDetailModal").style.display = "flex";
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