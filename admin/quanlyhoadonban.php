<?php 
include '../class/banhang.php';
include '../lib/session.php';
Session::checkSession();

$idnguoiduyet = Session::get('idAcount');
$banhang = new banhang();

if (isset($_POST['approve_invoice']) && isset($_POST['invoice_id'])) {
    $invoice_id = intval($_POST['invoice_id']);
    $result = $banhang->approveInvoice($invoice_id,$idnguoiduyet);
    echo json_encode(['success' => $result]);
    exit;
}


// Get filter parameter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Load all invoices
$invoices = $banhang->loadAllInvoices();

// Filter invoices based on the selected filter
$filtered_invoices = [];
foreach ($invoices as $invoice) {
    if ($filter === 'all') {
        $filtered_invoices[] = $invoice;
    } elseif ($filter === 'supported' && $invoice['trangThai'] == 1) {
        $filtered_invoices[] = $invoice;
    } elseif ($filter === 'pending' && $invoice['trangThai'] == 0) {
        $filtered_invoices[] = $invoice;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/model.css">
    <link rel="stylesheet" href="css/manage.css">
    <link rel="stylesheet" href="css/donhang.css">
    <link rel="stylesheet" href="css/search.css">
    <link rel="stylesheet" href="css/contact.css">
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
                <?php if (empty($filtered_invoices)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Không có đơn hàng nào.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($filtered_invoices as $index => $invoice): ?>
                        <tr>
                            <td><?= $index + 1; ?></td>
                            <td><?= htmlspecialchars($invoice['customer']['tenKhachHang']); ?></td>
                            <td><?= htmlspecialchars($invoice['customer']['soDT']); ?></td>
                            <td><?= $invoice['trangThai'] == 1 ? 'Đã duyệt' : 'Chưa duyệt'; ?></td>
                            <td><?= number_format($invoice['tongTien'], 0, ',', '.') . ' VNĐ'; ?></td>
                            <td class="btn-container">
                                <button style="background-color: #4caf50; color: white; font-weight: bold;" title="Xem chi tiết" class="btn-action btn-detail" onclick='showDetail(<?= json_encode($invoice); ?>)'>
                                    <i style="padding-right: 5px;" class="fa-solid fa-eye"></i> Chi tiết
                                </button>
                                <?php if ($invoice['trangThai'] == 0): ?>
                                    <button style="background-color: #ff0000; color: white; font-weight: bold;" class="btn-action btn-approve" data-invoice-id="<?= $invoice['maphieuxuat']; ?>">
                                        <i style="padding-right: 5px;" class="fa-solid fa-check-to-slot"></i> Duyệt đơn
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
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
                                <th>Hình ảnh</th>
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
    function showDetail(invoice) {
        // Điền thông tin cơ bản
        document.getElementById('invoiceCode').textContent = invoice.maphieuxuat;
        document.getElementById('customerName').textContent = invoice.customer.tenKhachHang;
        document.getElementById('customerPhone').textContent = invoice.customer.soDT;
        document.getElementById('customerAddress').textContent = invoice.customer.diaChi;
        document.getElementById('orderStatus').textContent = invoice.trangThai == 1 ? 'Đã duyệt' : 'Chưa duyệt';
        document.getElementById('totalAmount').textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(invoice.tongTien);

        // Điền danh sách sản phẩm
        const productTableBody = document.getElementById('productListBody');
        productTableBody.innerHTML = ''; // Xóa dữ liệu cũ

        invoice.items.forEach((product, index) => {
            const total = product.soLuongXuat * product.giaban;
            const row = `<tr>
                            <td>${index + 1}</td>
                            <td><img src="${product.hinhAnh}" alt="Product Image" width="60" height="60"></td>
                            <td>${product.tenSanPham}</td>
                            <td>${product.soLuongXuat}</td>
                            <td>${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(product.giaban)}</td>
                            <td>${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(total)}</td>
                        </tr>`;
            productTableBody.innerHTML += row;
        });

        // Hiển thị modal
        document.getElementById('invoiceDetailModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('invoiceDetailModal').style.display = 'none';
    }

    // Handle "Duyệt đơn" (Approve Order) button click
    document.querySelectorAll('.btn-approve').forEach(button => {
        button.addEventListener('click', function() {
            const invoiceId = this.getAttribute('data-invoice-id');
            if (confirm('Bạn có chắc chắn muốn duyệt đơn hàng này?')) {
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `approve_invoice=true&invoice_id=${invoiceId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Đơn hàng đã được duyệt thành công!');
                        location.reload(); // Reload the page to reflect the updated status
                    } else {
                        alert('Có lỗi xảy ra khi duyệt đơn hàng.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi duyệt đơn hàng.');
                });
            }
        });
    });
</script>

</body>
</html>