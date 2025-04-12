<?php
include '../class/banhang.php';
include '../lib/session.php';
Session::checkSession();

$idnguoiduyet = Session::get('idAcount');
$banhang = new banhang();

if (isset($_POST['action']) && in_array($_POST['action'], ['approve_invoice', 'approve_return'])) {
    header('Content-Type: application/json');
    $invoice_id = isset($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : 0;

    if ($invoice_id <= 0 || empty($idnguoiduyet)) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    try {
        if ($_POST['action'] === 'approve_invoice') {
            $result = $banhang->approveInvoice($invoice_id, $idnguoiduyet);
            echo json_encode(['success' => $result, 'message' => $result ? 'Duyệt đơn thành công' : 'Lỗi khi duyệt đơn']);
        } elseif ($_POST['action'] === 'approve_return') {
            $result = $banhang->approveReturn($invoice_id, $idnguoiduyet);
            echo json_encode(['success' => $result, 'message' => $result ? 'Đồng ý trả hàng thành công' : $result['message'] ?? 'Lỗi khi đồng ý trả hàng']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
    }
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
                            <td>
                                <?php
                                switch ($invoice['trangThai']) {
                                    case 0: echo 'Đang xử lý'; break;
                                    case 1: echo 'Đã duyệt'; break;
                                    case 2: echo 'Đã hủy'; break;
                                    case 3: echo 'Đã nhận được hàng'; break;
                                    case 4: echo 'Yêu cầu trả hàng'; break;
                                    case 5: echo 'Đã đồng ý trả hàng'; break;
                                    default: echo 'Không xác định';
                                }
                                ?>
                            </td>
                            <td><?= number_format($invoice['tongTien'], 0, ',', '.') . ' VNĐ'; ?></td>
                            <td class="btn-container">
                                <button style="background-color: #4caf50; color: white; font-weight: bold;" title="Xem chi tiết" class="btn-action btn-detail" onclick='showDetail(<?= json_encode($invoice); ?>)'>
                                    <i style="padding-right: 5px;" class="fa-solid fa-eye"></i> Chi tiết
                                </button>
                                <?php if ($invoice['trangThai'] == 0): ?>
                                    <button style="background-color: #ff0000; color: white; font-weight: bold;" class="btn-action btn-approve" data-invoice-id="<?= $invoice['maphieuxuat']; ?>">
                                        <i style="padding-right: 5px;" class="fa-solid fa-check-to-slot"></i> Duyệt đơn
                                    </button>
                                <?php elseif ($invoice['trangThai'] == 4): ?>
                                    <button style="background-color: #ff0000; color: white; font-weight: bold;" class="btn-action btn-return" data-invoice-id="<?= $invoice['maphieuxuat']; ?>">
                                        <i style="padding-right: 5px;" class="fa-solid fa-check-to-slot"></i> Đồng ý trả hàng
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
        document.getElementById('invoiceCode').textContent = invoice.maphieuxuat;
        document.getElementById('customerName').textContent = invoice.customer.tenKhachHang;
        document.getElementById('customerPhone').textContent = invoice.customer.soDT;
        document.getElementById('customerAddress').textContent = invoice.customer.diaChi;
        document.getElementById('orderStatus').textContent =
            invoice.trangThai === 0 ? 'Đang xử lý' :
            invoice.trangThai === 1 ? 'Đã duyệt' :
            invoice.trangThai === 2 ? 'Đã hủy' :
            invoice.trangThai === 3 ? 'Đã nhận được hàng' :
            invoice.trangThai === 4 ? 'Yêu cầu trả hàng' :
            invoice.trangThai === 5 ? 'Đã đồng ý trả hàng' : 'Không xác định';
        document.getElementById('totalAmount').textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(invoice.tongTien);

        const productTableBody = document.getElementById('productListBody');
        productTableBody.innerHTML = '';

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

        document.getElementById('invoiceDetailModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('invoiceDetailModal').style.display = 'none';
    }

    // Handle "Duyệt đơn"
    document.querySelectorAll('.btn-approve').forEach(button => {
        button.addEventListener('click', function() {
            const invoiceId = this.getAttribute('data-invoice-id');
            if (!invoiceId || isNaN(invoiceId)) {
                alert('Mã hóa đơn không hợp lệ');
                return;
            }
            if (confirm('Bạn có chắc chắn muốn duyệt đơn hàng này?')) {
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=approve_invoice&invoice_id=${encodeURIComponent(invoiceId)}`
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error approving invoice:', error);
                    alert('Có lỗi xảy ra khi duyệt đơn hàng: ' + error.message);
                });
            }
        });
    });

    // Handle "Đồng ý trả hàng"
    document.querySelectorAll('.btn-return').forEach(button => {
        button.addEventListener('click', function() {
            const invoiceId = this.getAttribute('data-invoice-id');
            if (!invoiceId || isNaN(invoiceId)) {
                alert('Mã hóa đơn không hợp lệ');
                return;
            }
            if (confirm('Bạn có chắc chắn muốn đồng ý trả hàng cho đơn hàng này?')) {
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=approve_return&invoice_id=${encodeURIComponent(invoiceId)}`
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error approving return:', error);
                    alert('Có lỗi xảy ra khi đồng ý trả hàng: ' + error.message);
                });
            }
        });
    });
</script>

</body>
</html>