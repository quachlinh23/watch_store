<?php
session_start();
include_once "class/brand.php";
include_once "class/cart.php";

$cart = new cart();
$brand = new brand();

// Lấy danh sách phiếu xuất của người dùng nếu đã đăng nhập
$userId = $_SESSION['customer_id'] ?? null;
$invoices = $userId ? $cart->loadInvoiceOfUser($userId) : [];

// Xử lý yêu cầu AJAX
if (isset($_POST['action']) && $userId) {
    header('Content-Type: application/json');
    $maPX = isset($_POST['maPX']) ? (int)$_POST['maPX'] : 0;

    if ($maPX <= 0) {
        echo json_encode(['success' => false, 'message' => 'Mã hóa đơn không hợp lệ']);
        exit;
    }

    if ($_POST['action'] === 'cancel') {
        $result = $cart->cancelOrder($maPX);
        echo json_encode($result);
        exit;
    } elseif ($_POST['action'] === 'received') {
        $result = $cart->markAsReceived($maPX);
        echo json_encode($result);
        exit;
    } elseif ($_POST['action'] === 'request_return') {
        $result = $cart->requestReturn($maPX);
        echo json_encode($result);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đơn đặt hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/order.css">
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
    <style>
        .tab {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: center;
        }

        .tab-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            background: #e5e7eb;
            color: #1e3a8a;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s, transform 0.2s;
        }

        .tab-btn.active {
            background: #1e3a8a;
            color: #fff;
        }

        .tab-btn:hover:not(.active) {
            background: #d4af37;
            color: #fff;
            transform: scale(1.05);
        }

        .btn-cancel:hover:not(:disabled), .btn-received:hover:not(:disabled), .btn-return:hover:not(:disabled) {
            background: #991b1b;
            transform: scale(1.05);
        }

        .btn-cancel:disabled, .btn-received:disabled, .btn-return:disabled {
            background: #cccccc;
            color: #666666;
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>
</head>
<body>
<?php include 'layout/header.php'; ?>
    <div class="cart-container">
        <div class="tab">
            <button class="tab-btn active" data-tab="processing" onclick="filterInvoices('processing')">Đang xử lý</button>
            <button class="tab-btn" data-tab="approved" onclick="filterInvoices('approved')">Đơn đang được vận chuyển</button>
            <button class="tab-btn" data-tab="received" onclick="filterInvoices('received')">Hoàn thành</button>
            <button class="tab-btn" data-tab="cancelled" onclick="filterInvoices('cancelled')">Đơn hủy</button>
        </div>
        <h2>Lịch Sử Đơn Đặt Hàng</h2>
        <div class="cart-content">
            <?php if (!$userId): ?>
                <p style="text-align: center;">Vui lòng <a href="login.php">đăng nhập</a> để xem lịch sử đơn hàng.</p>
            <?php elseif (empty($invoices)): ?>
                <p style="text-align: center;">Bạn chưa có đơn đặt hàng nào.</p>
            <?php else: ?>
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
                    <tbody id="invoiceTableBody">
                        <?php foreach ($invoices as $index => $invoice): ?>
                            <tr data-mapx="<?php echo $invoice['maphieuxuat']; ?>" data-status="<?php echo $invoice['trangThai']; ?>">
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($invoice['ngayLap'])); ?></td>
                                <td><?php echo number_format($invoice['tongTien'], 0, ',', '.') . ' VNĐ'; ?></td>
                                <td>
                                    <?php
                                    if ($invoice['trangThai'] == 0) {
                                        echo 'Đang xử lý';
                                    } elseif ($invoice['trangThai'] == 1) {
                                        echo 'Đã duyệt';
                                    } elseif ($invoice['trangThai'] == 2) {
                                        echo 'Đã hủy';
                                    } elseif ($invoice['trangThai'] == 3) {
                                        echo 'Đã nhận hàng';
                                    } else {
                                        echo 'Yêu cầu trả hàng';
                                    }
                                    ?>
                                </td>
                                <td class="action-buttons">
                                    <button class="btn-view" onclick='showInvoiceDetails(<?php echo json_encode($invoice); ?>)'>Xem chi tiết</button>
                                    <?php if ($invoice['trangThai'] == 0): ?>
                                        <button class="btn-cancel"
                                            onclick="cancelInvoice(<?php echo $invoice['maphieuxuat']; ?>)">
                                            Hủy đơn
                                        </button>
                                    <?php elseif ($invoice['trangThai'] == 1): ?>
                                        <button class="btn-received"
                                            onclick="markAsReceived(<?php echo $invoice['maphieuxuat']; ?>)">
                                            Đã nhận hàng
                                        </button>
                                    <?php elseif ($invoice['trangThai'] == 3): ?>
                                        <button class="btn-return"
                                            onclick="requestReturn(<?php echo $invoice['maphieuxuat']; ?>)">
                                            Yêu cầu trả hàng
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-cancel" disabled>
                                            <?php echo $invoice['trangThai'] == 2 ? 'Đã hủy' : 'Đã yêu cầu trả hàng'; ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
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
        // Lưu dữ liệu hóa đơn từ PHP
        const invoices = <?php echo json_encode($invoices); ?>;

        // Hàm hiển thị modal chi tiết hóa đơn
        function showInvoiceDetails(invoice) {
            document.getElementById("invoiceCode").textContent = invoice.maphieuxuat;
            document.getElementById("customerName").textContent = invoice.customer.tenKhachHang;
            document.getElementById("customerPhone").textContent = invoice.customer.soDT;
            document.getElementById("customerAddress").textContent = invoice.customer.diaChi;
            document.getElementById("orderStatus").textContent = 
                invoice.trangThai === 0 ? "Đang xử lý" : 
                invoice.trangThai === 1 ? "Đã duyệt" : 
                invoice.trangThai === 2 ? "Đã hủy" : 
                invoice.trangThai === 3 ? "Đã nhận hàng" : "Yêu cầu trả hàng";
            document.getElementById("totalAmount").textContent = invoice.tongTien.toLocaleString('vi-VN') + " VNĐ";

            const productListBody = document.getElementById("productListBody");
            productListBody.innerHTML = "";
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

        // Hàm hủy hóa đơn
        function cancelInvoice(maPX) {
            if (!confirm('Bạn có chắc muốn hủy hóa đơn này?')) {
                return;
            }

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=cancel&maPX=' + encodeURIComponent(maPX)
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    const row = document.querySelector(`tr[data-mapx="${maPX}"]`);
                    if (row) {
                        row.querySelector('td:nth-child(4)').textContent = 'Đã hủy';
                        row.setAttribute('data-status', '2');
                        row.querySelector('.action-buttons').innerHTML = `
                            <button class="btn-view" onclick="showInvoiceDetails(${JSON.stringify(invoices.find(inv => inv.maphieuxuat === maPX))})">Xem chi tiết</button>
                            <button class="btn-cancel" disabled>Đã hủy</button>
                        `;
                        filterInvoices(document.querySelector('.tab-btn.active').getAttribute('data-tab') || 'processing');
                    }
                    window.location.reload();
                }
            })
            
            .catch(error => {
                alert('Đã xảy ra lỗi: ' + error.message);
            });
        }

        // Hàm đánh dấu đã nhận hàng
        function markAsReceived(maPX) {
            if (!confirm('Bạn có xác nhận đã nhận hàng?')) {
                return;
            }

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=received&maPX=' + encodeURIComponent(maPX)
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    const row = document.querySelector(`tr[data-mapx="${maPX}"]`);
                    if (row) {
                        row.querySelector('td:nth-child(4)').textContent = 'Đã nhận hàng';
                        row.setAttribute('data-status', '3');
                        row.querySelector('.action-buttons').innerHTML = `
                            <button class="btn-view" onclick="showInvoiceDetails(${JSON.stringify(invoices.find(inv => inv.maphieuxuat === maPX))})">Xem chi tiết</button>
                            <button class="btn-return" onclick="requestReturn(${maPX})">Yêu cầu trả hàng</button>
                        `;
                        filterInvoices(document.querySelector('.tab-btn.active').getAttribute('data-tab') || 'processing');
                    }
                    window.location.reload();
                }
            })
            .catch(error => {
                alert('Đã xảy ra lỗi: ' + error.message);
            });
        }

        // Hàm yêu cầu trả hàng
        function requestReturn(maPX) {
            if (!confirm('Bạn có chắc muốn yêu cầu trả hàng cho hóa đơn này?')) {
                return;
            }

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=request_return&maPX=' + encodeURIComponent(maPX)
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    const row = document.querySelector(`tr[data-mapx="${maPX}"]`);
                    if (row) {
                        row.querySelector('td:nth-child(4)').textContent = 'Yêu cầu trả hàng';
                        row.setAttribute('data-status', '4');
                        row.querySelector('.action-buttons').innerHTML = `
                            <button class="btn-view" onclick="showInvoiceDetails(${JSON.stringify(invoices.find(inv => inv.maphieuxuat === maPX))})">Xem chi tiết</button>
                            <button class="btn-cancel" disabled>Đã yêu cầu trả hàng</button>
                        `;
                        filterInvoices(document.querySelector('.tab-btn.active').getAttribute('data-tab') || 'processing');
                    }
                }
                window.location.reload();
            })
            .catch(error => {
                alert('Đã xảy ra lỗi: ' + error.message);
            });
        }

        // Hàm lọc hóa đơn theo tab
        function filterInvoices(tab) {
            const tableBody = document.getElementById('invoiceTableBody');
            tableBody.innerHTML = '';

            let filteredInvoices = [];
            if (tab === 'processing') {
                filteredInvoices = invoices.filter(invoice => invoice.trangThai === 0);
            } else if (tab === 'approved') {
                filteredInvoices = invoices.filter(invoice => invoice.trangThai === 1);
            } else if (tab === 'cancelled') {
                filteredInvoices = invoices.filter(invoice => invoice.trangThai === 2);
            } else if (tab === 'received') {
                filteredInvoices = invoices.filter(invoice => invoice.trangThai === 3 || invoice.trangThai === 4);
            }

            filteredInvoices.forEach((invoice, index) => {
                const row = document.createElement('tr');
                row.setAttribute('data-mapx', invoice.maphieuxuat);
                row.setAttribute('data-status', invoice.trangThai);
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${new Date(invoice.ngayLap).toLocaleDateString('vi-VN')}</td>
                    <td>${invoice.tongTien.toLocaleString('vi-VN')} VNĐ</td>
                    <td>${invoice.trangThai === 0 ? 'Đang xử lý' :
                        invoice.trangThai === 1 ? 'Đã duyệt' :
                        invoice.trangThai === 2 ? 'Đã hủy' :
                        invoice.trangThai === 3 ? 'Đã nhận hàng' : 'Yêu cầu trả hàng'}</td>
                    <td class="action-buttons">
                        <button class="btn-view" onclick='showInvoiceDetails(${JSON.stringify(invoice)})'>Xem chi tiết</button>
                        ${invoice.trangThai === 0 ? `<button class="btn-cancel" onclick="cancelInvoice(${invoice.maphieuxuat})">Hủy đơn</button>` :
                        invoice.trangThai === 1 ? `<button class="btn-received" onclick="markAsReceived(${invoice.maphieuxuat})">Đã nhận hàng</button>` :
                        invoice.trangThai === 3 ? `<button class="btn-return" onclick="requestReturn(${invoice.maphieuxuat})">Yêu cầu trả hàng</button>` :
                        `<button class="btn-cancel" disabled>${invoice.trangThai === 2 ? 'Đã hủy' : 'Đã yêu cầu trả hàng'}</button>`}
                    </td>
                `;
                tableBody.appendChild(row);
            });

            // Cập nhật trạng thái active cho tab
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector(`.tab-btn[data-tab="${tab}"]`).classList.add('active');

            // Hiển thị thông báo nếu không có hóa đơn
            if (filteredInvoices.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Không có hóa đơn nào.</td></tr>';
            }
        }

        // Khởi tạo tab mặc định
        window.onload = function() {
            filterInvoices('processing');
        };
    </script>
</body>
</html>