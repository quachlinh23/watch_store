<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COWatch Store</title>
    <link rel="stylesheet" href="css/model.css">
    <link rel="stylesheet" href="css/manage.css">
    <link rel="stylesheet" href="css/donhang.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="container">
    <h2>Quản lý đơn hàng</h2>
    <div class="search-container" style="margin-top: 30px; margin-bottom: 25px;">
        <form method="get">
            <select name="filter">
                <option value="all" <?= ($filter == 'all') ? 'selected' : ''; ?>>Tất cả</option>
                <option value="supported" <?= ($filter == 'supported') ? 'selected' : ''; ?>>Đã duyệt</option>
                <option value="pending" <?= ($filter == 'pending') ? 'selected' : ''; ?>>Chưa duyệt</option>
            </select>
            <button type="submit" name="Search"><i class="fa fa-filter"></i> Lọc</button>
        </form>
    </div>
    <div class="table-container">
        <table class="table_slider">
            <thead>
                <tr>
                    <th style="width: 5%;">STT</th>
                    <th style="width: 20%;">Tên khách hàng</th>
                    <th style="width: 15%;">Số điện thoại</th>
                    <th style="width: 15%;">Trạng thái</th>
                    <th style="width: 20%;">Tổng tiền</th>
                    <th style="width: 25%;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Nguyễn Văn A</td>
                    <td>0123 456 789</td>
                    <td>Đã giao</td>
                    <td>1.500.000 VNĐ</td>
                    <td class="btn-container">
                        <button style="background-color: #4caf50; color: white; font-weight: bold;" title="Xem chi tiết" class="btn-action btn-detail" onclick="showDetail('01', 'Nguyễn Văn A', '0123456789', 'Đã giao', '1.500.000 VNĐ')">
                            <i style="padding-right: 5px;" class="fa-solid fa-eye"></i> Chi tiết
                        </button>
                        <button style="background-color: #ff0000; color: white; font-weight: bold;" class="btn-action">
                            <i style="padding-right: 5px;" class="fa-solid fa-check-to-slot" style="color: black;"></i> Duyệt đơn
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Trần Thị B</td>
                    <td>0987 654 321</td>
                    <td>Chưa giao</td>
                    <td>2.200.000 VNĐ</td>
                    <td class="btn-container">
                        <button style="background-color: #4caf50; color: white; font-weight: bold;" title="Xem chi tiết" class="btn-action btn-detail" onclick="showDetail('01', 'Nguyễn Văn A', '0123456789', 'Đã giao', '1.500.000 VNĐ')">
                            <i style="padding-right: 5px;" class="fa-solid fa-eye"></i> Chi tiết
                        </button>
                        <button style="background-color: #ff0000; color: white; font-weight: bold;" class="btn-action">
                            <i style="padding-right: 5px;" class="fa-solid fa-check-to-slot" style="color: black;"></i> Duyệt đơn
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Chi tiết hóa đơn -->
    <div id="invoiceDetailModal" class="modal-overlay" style="display: none;">
        <div class="modal-window">
            <span class="modal-close" onclick="closeModal()">×</span>
            <h2>Chi tiết hóa đơn</h2>
            <div id="invoiceDetails">
                <div class="info-table">
                    <table>
                        <tr>
                            <td>
                                <div class="info-item">
                                    <label>Mã hóa đơn: <span id="invoiceCode"></span></label>
                                </div>
                            </td>
                            <td>
                                <div class="info-item">
                                    <label>Khách hàng: <span id="customerName"></span></label>
                                </div>
                            </td>
                            <td>
                                <div class="info-item">
                                    <label>Số điện thoại: <span id="customerPhone"></span></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="info-item">
                                    <label>Địa chỉ: <span id="customerAddress"></span></label>
                                </div>
                            </td>
                            <td>
                                <div class="info-item">
                                    <label>Trạng thái: <span id="orderStatus"></span></label>
                                </div>
                            </td>
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
    // Dữ liệu mẫu cho sản phẩm (có thể thay đổi theo từng đơn hàng)
    const orderProducts = {
        '01': [
            { stt: 1, code: 'SP001', name: 'Đồng hồ Rolex', quantity: 1, price: '1.000.000 VNĐ', total: '1.000.000 VNĐ' },
            { stt: 2, code: 'SP002', name: 'Đồng hồ Casio', quantity: 2, price: '250.000 VNĐ', total: '500.000 VNĐ' }
        ],
        '02': [
            { stt: 1, code: 'SP003', name: 'Áo thun nam', quantity: 2, price: '150.000 VNĐ', total: '300.000 VNĐ' },
            { stt: 2, code: 'SP004', name: 'Quần jeans', quantity: 1, price: '500.000 VNĐ', total: '500.000 VNĐ' },
            { stt: 3, code: 'SP005', name: 'Giày thể thao', quantity: 1, price: '800.000 VNĐ', total: '800.000 VNĐ' },
            { stt: 4, code: 'SP006', name: 'Mũ lưỡi trai', quantity: 3, price: '100.000 VNĐ', total: '300.000 VNĐ' },
            { stt: 5, code: 'SP007', name: 'Túi xách', quantity: 1, price: '300.000 VNĐ', total: '300.000 VNĐ' }
        ]
    };

    function showDetail(invoiceCode, customerName, customerPhone, orderStatus, totalAmount) {
        // Điền thông tin cơ bản
        document.getElementById('invoiceCode').textContent = invoiceCode;
        document.getElementById('customerName').textContent = customerName;
        document.getElementById('customerPhone').textContent = customerPhone;
        document.getElementById('orderStatus').textContent = orderStatus;
        document.getElementById('totalAmount').textContent = totalAmount;

        // Điền danh sách sản phẩm dựa trên mã hóa đơn
        const products = orderProducts[invoiceCode] || [];
        const productTableBody = document.getElementById('productListBody');
        productTableBody.innerHTML = ''; // Xóa dữ liệu cũ

        products.forEach(product => {
            const row = `<tr>
                            <td>${product.stt}</td>
                            <td>${product.code}</td>
                            <td>${product.name}</td>
                            <td>${product.quantity}</td>
                            <td>${product.price}</td>
                            <td>${product.total}</td>
                        </tr>`;
            productTableBody.innerHTML += row;
        });

        // Hiển thị modal
        document.getElementById('invoiceDetailModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('invoiceDetailModal').style.display = 'none';
    }
</script>

</body>
</html>