<?php
include '../class/nhaphang.php';
include '../lib/session.php';

Session::checkSession();
$nhaphang = new nhaphang();
$tennguoinhap = Session::get('fullname');
$idnguoinhap = Session::get('idAcount');
$mahoadontieptheo = $nhaphang->getNextID();
$ncc = $nhaphang->getSupplier();
$sanpham = $nhaphang->getProduct();
$imports = $nhaphang->getAllImports();

// Biến để lưu chi tiết hóa đơn và thông tin hóa đơn khi xem
$importDetails = null;
$selectedImportId = null;
$selectedImport = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Search'])) {
    $startDate = isset($_POST['start_date']) ? $_POST['start_date'] : null;
    $endDate = isset($_POST['end_date']) ? $_POST['end_date'] : null;

    if ($startDate && $endDate) {
        $imports = $nhaphang->searchInvoice($startDate, $endDate);
    } else {
        $imports = $nhaphang->getAllImports();
    }
} else {
    $imports = $nhaphang->getAllImports();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['viewDetails'])) {
        $selectedImportId = $_POST['importId'];
        $importDetails = $nhaphang->getImportDetails($selectedImportId);

        if ($imports) {
            $imports->data_seek(0);
            while ($import = $imports->fetch_assoc()) {
                if ($import['maPhieuNhap'] === $selectedImportId) {
                    $selectedImport = $import;
                    break;
                }
            }
        }
    }
}

// Xử lý yêu cầu AJAX cho thêm hóa đơn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['invoice']) || !isset($data['products'])) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ!']);
        exit;
    }

    $invoice = $data['invoice'];
    $products = $data['products'];
    $chiTietSanPham = [];
    foreach ($products as $product) {
        $chiTietSanPham[] = [
            'masanpham' => $product['productCode'],
            'gianhap' => $product['importPrice'],
            'soluong' => $product['quantity'],
            'giaban' => $product['sellPrice']
        ];
    }
    try {
        $result = $nhaphang->addInvoice(
            $invoice['invoiceCode'],
            $invoice['importer'],
            $invoice['totalAmount'],
            $invoice['supplier'],
            $invoice['importDate'],
            $chiTietSanPham
        );
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi thêm hóa đơn: ' . $e->getMessage()]);
        exit;
    }

    if (strpos($result, "Thành công") !== false) {
        echo json_encode(['success' => true, 'message' => $result]);
    } else {
        echo json_encode(['success' => false, 'message' => $result]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/model.css">
    <link rel="stylesheet" href="css/manage.css">
    <link rel="stylesheet" href="css/nhaphang.css">
    <link rel="stylesheet" href="css/search.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let today = new Date().toISOString().split("T")[0];
            let endDateInput = document.getElementById("end_date");
            let startDateInput = document.getElementById("start_date");

            endDateInput.value = today;
            endDateInput.setAttribute("max", today);

            startDateInput.addEventListener("change", function () {
                let startDate = new Date(this.value);
                let endDate = new Date(endDateInput.value);
                if (this.value && startDate > endDate) {
                    alert("Ngày bắt đầu không được lớn hơn ngày kết thúc!");
                    this.value = "";
                }
            });

            endDateInput.addEventListener("change", function () {
                let startDate = new Date(startDateInput.value);
                let endDate = new Date(this.value);
                if (this.value && startDateInput.value && startDate > endDate) {
                    alert("Ngày kết thúc không được nhỏ hơn ngày bắt đầu!");
                    this.value = "";
                }
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h2>Quản lý nhập hàng</h2>
        <div class="search-container"> 
            <form method="POST" class="search-input">
                <input type="date" name="start_date" id="start_date" placeholder="Từ ngày" title="Từ ngày">
                <input type="date" name="end_date" id="end_date" placeholder="Đến ngày" title="Đến ngày">
                <button type="submit" name="Search" title="Tìm kiếm"><span style="padding-right: 5px;"><i class="fa-solid fa-magnifying-glass"></i></span> Tìm kiếm</button>
            </form>
            <button class="btn btn-add" id="openModal" title="Thêm">
                <span><i class="fa-solid fa-plus"></i></span> Thêm
            </button>
        </div>
        <div class="table-container">
            <table class="table_slider">
                <thead>
                    <tr>
                        <th style="width: 10%;">STT</th>
                        <th style="width: 20%;">Nhà cung cấp</th>
                        <th style="width: 15%;">Ngày nhập</th>
                        <th style="width: 20%;">Người nhập</th>
                        <th style="width: 20%;">Tổng tiền</th>
                        <th style="width: 15%;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($imports) {
                        $stt = 1;
                        $imports->data_seek(0);
                        while ($import = $imports->fetch_assoc()) {
                            ?>
                            <tr data-import-id="<?php echo $import['maPhieuNhap']; ?>">
                                <td><?php echo $stt++; ?></td>
                                <td><?php echo $import['tenNCC']; ?></td>
                                <td><?php echo $import['ngayLap']; ?></td>
                                <td><?php echo $import['tenNhanVien']; ?></td>
                                <td><?php echo number_format($import['tongTien'], 0, ',', '.') . ' VNĐ'; ?></td>
                                <td class="btn-container">
                                    <form method="POST">
                                        <input type="hidden" name="importId" value="<?php echo $import['maPhieuNhap']; ?>">
                                        <button style="background-color: #008000; color: white; font-weight: bold;" title="Chi tiết" type="submit" name="viewDetails" class="btn btn-action btn-detail">
                                            <span><i class="fa-solid fa-eye" style="padding-right: 10px;"></i></span>Chi tiết
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="6">Không có dữ liệu</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Form nhập hàng -->
    <div id="importForm" class="modal-overlay" style="display: none;">
        <div class="modal-window">
            <span class="modal-close" onclick="closeImportForm()">×</span>
            <div class="form-container">
                <h2>Hóa đơn nhập hàng</h2>
                <div class="form-section">
                    <h3 class="section-title">Thông tin hóa đơn</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="invoiceCode">Mã hóa đơn</label>
                            <input type="text" id="invoiceCode" readonly value="<?php echo $mahoadontieptheo; ?>">
                        </div>
                        <div class="form-group">
                            <label for="importer">Người nhập</label>
                            <input type="hidden" id="idimporter" value="<?php echo $idnguoinhap; ?>" readonly>
                            <input type="text" id="importer" value="<?php echo $tennguoinhap; ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="importDate">Ngày nhập</label>
                            <input type="date" id="importDate" value="<?php echo date('Y-m-d'); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="supplierSelect">Nhà cung cấp</label>
                            <select id="supplierSelect" name="mancc" required>
                                <option value="">-- Chọn nhà cung cấp --</option>
                                <?php
                                if ($ncc) {
                                    $ncc->data_seek(0);
                                    while ($ncclist = $ncc->fetch_assoc()) {
                                        echo '<option value="' . $ncclist['id_nhacungcap'] . '">' . $ncclist['tenNCC'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="section-title">Thêm sản phẩm</h3>
                        <button id="addProduct" class="btn btn-add">
                            <span>+</span> Thêm
                        </button>
                    </div>
                    <div class="form-grid product-input-grid">
                        <div class="form-group select-product">
                            <label for="productSelect">Sản phẩm</label>
                            <select id="productSelect" required>
                                <option value="">-- Chọn sản phẩm --</option>
                                <?php
                                if ($sanpham) {
                                    $sanpham->data_seek(0);
                                    while ($sanphamlist = $sanpham->fetch_assoc()) {
                                        echo '<option value="' . $sanphamlist['maSanPham'] . '|' . $sanphamlist['tenSanPham'] . '">' . $sanphamlist['maSanPham'] . ' - ' . $sanphamlist['tenSanPham'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <span class="error-message"></span>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Số lượng</label>
                            <input type="number" id="quantity" min="1" placeholder="Số lượng" required>
                            <span class="error-message"></span>
                        </div>
                        <div class="form-group">
                            <label for="importPrice">Giá nhập (VNĐ)</label>
                            <input type="number" id="importPrice" min="0" placeholder="Giá nhập" required>
                            <span class="error-message"></span>
                        </div>
                        <div class="form-group">
                            <label for="profitPercent">Phần trăm lợi nhuận (%)</label>
                            <input type="number" id="profitPercent" min="0" step="1" placeholder="Phần trăm" required>
                            <span class="error-message"></span>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Danh sách sản phẩm</h3>
                    <div class="table-wrapper">
                        <table id="productTable">
                            <thead>
                                <tr>
                                    <th>Mã sản phẩm</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Giá nhập (VNĐ)</th>
                                    <th>Giá bán (VNĐ) & Phần trăm lời</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="productList"></tbody>
                        </table>
                    </div>
                    <div class="total-amount" style="margin-top: 10px; text-align: right; font-weight: bold;">
                        Tổng tiền: <span id="totalAmount">0</span> VNĐ
                    </div>
                </div>

                <div class="button-group">
                    <button id="confirmImport" class="btn btn-action">
                        <span>✔️</span> Nhập hàng
                    </button>
                    <button class="btn btn-action btn-cancel" onclick="closeImportForm()">
                        <span>❌</span> Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Form xem chi tiết -->
    <div id="DetailsForm" class="modal-overlay" style="display: <?php echo ($importDetails !== null) ? 'flex' : 'none'; ?>;">
        <div class="modal-window">
            <span class="modal-close" onclick="closeDetailsForm()">×</span>
            <div class="form-container">
                <h2>Chi tiết hóa đơn nhập hàng</h2>
                <div class="form-section">
                    <h3 class="section-title">Thông tin hóa đơn</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="detailInvoiceCode">Mã hóa đơn</label>
                            <input type="text" id="detailInvoiceCode" readonly value="<?php echo $selectedImportId ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="detailImporter">Người nhập</label>
                            <input type="text" id="detailImporter" readonly value="<?php echo $selectedImport['tenNhanVien'] ?? $tennguoinhap; ?>">
                        </div>
                        <div class="form-group">
                            <label for="detailImportDate">Ngày nhập</label>
                            <input type="date" id="detailImportDate" readonly value="<?php echo $selectedImport['ngayLap'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="detailSupplier">Nhà cung cấp</label>
                            <input type="text" id="detailSupplier" readonly value="<?php echo $selectedImport['tenNCC'] ?? ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Danh sách sản phẩm</h3>
                    <div class="table-wrapper">
                        <table id="detailProductTable">
                            <thead>
                                <tr>
                                    <th>Mã sản phẩm</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Giá nhập (VNĐ)</th>
                                    <th>Giá bán (VNĐ) & Phần trăm lời</th>
                                </tr>
                            </thead>
                            <tbody id="detailProductList">
                                <?php
                                if ($importDetails) {
                                    foreach ($importDetails as $product) {
                                        $profitPercent = (($product['sellPrice'] - $product['importPrice']) / $product['importPrice']) * 100;
                                        ?>
                                        <tr>
                                            <td><?php echo $product['productCode']; ?></td>
                                            <td><?php echo $product['productName']; ?></td>
                                            <td><?php echo $product['quantity']; ?></td>
                                            <td><?php echo number_format($product['importPrice'], 0, ',', '.') . ' VNĐ'; ?></td>
                                            <td><?php echo number_format($product['sellPrice'], 0, ',', '.') . ' VNĐ (' . round($profitPercent, 1) . '%)'; ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="total-amount" style="margin-top: 10px; text-align: right; font-weight: bold;">
                        Tổng tiền: <span id="detailTotalAmount"><?php echo $selectedImport ? number_format($selectedImport['tongTien'], 0, ',', '.') : '0'; ?> VNĐ</span>
                    </div>
                </div>

                <div class="button-group">
                    <button class="btn btn-action btn-cancel" onclick="closeDetailsForm()">
                        <span>❌</span> Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const importForm = document.getElementById("importForm");
            const detailsForm = document.getElementById("DetailsForm");
            const openModalBtn = document.getElementById("openModal");
            const productList = document.getElementById("productList");
            const totalAmountSpan = document.getElementById("totalAmount");

            // Mở form thêm hóa đơn
            openModalBtn.addEventListener("click", function () {
                importForm.style.display = "flex";
            });

            // Đóng form
            window.closeImportForm = function() {
                importForm.style.display = "none";
                resetForm();
            };

            window.closeDetailsForm = function() {
                detailsForm.style.display = "none";
            };

            // Thêm sản phẩm
            document.getElementById("addProduct").addEventListener("click", function (event) {
                event.preventDefault();

                const productSelect = document.getElementById("productSelect");
                const quantity = document.getElementById("quantity");
                const importPrice = document.getElementById("importPrice");
                const profitPercent = document.getElementById("profitPercent");

                if (!validateInputs(productSelect, quantity, importPrice, profitPercent)) return;

                const [productCode, productName] = productSelect.value.split("|");
                const quantityValue = parseInt(quantity.value);
                const importPriceValue = parseInt(importPrice.value);
                const profitPercentValue = parseFloat(profitPercent.value);
                const sellPriceValue = importPriceValue * (1 + profitPercentValue / 100);

                let existingRow = Array.from(productList.children).find(row => 
                    row.dataset.productCode === productCode
                );

                if (existingRow) {
                    const quantityCell = existingRow.querySelector(".quantity");
                    quantityCell.textContent = parseInt(quantityCell.textContent) + quantityValue;
                } else {
                    const newRow = document.createElement("tr");
                    newRow.dataset.productCode = productCode;
                    newRow.innerHTML = `
                        <td>${productCode}</td>
                        <td>${productName}</td>
                        <td class="quantity">${quantityValue}</td>
                        <td>${importPriceValue.toLocaleString('vi-VN')} VNĐ</td>
                        <td>${sellPriceValue.toLocaleString('vi-VN')} VNĐ (${profitPercentValue}%)</td>
                        <td><button class="btn-delete" onclick="this.parentElement.parentElement.remove(); updateTotalAmount()">❌</button></td>
                    `;
                    productList.appendChild(newRow);
                }

                resetProductInputs();
                updateTotalAmount();
            });

            // Xác nhận nhập hàng
            document.getElementById("confirmImport").addEventListener("click", function () {
                if (productList.children.length === 0) {
                    alert("Vui lòng thêm ít nhất một sản phẩm!");
                    return;
                }
                const supplier = document.getElementById("supplierSelect").value;
                if (!supplier) {
                    alert("Vui lòng chọn nhà cung cấp!");
                    return;
                }

                const invoiceData = {
                    invoiceCode: document.getElementById("invoiceCode").value,
                    importer: document.getElementById("idimporter").value,
                    importDate: document.getElementById("importDate").value,
                    supplier: supplier
                };

                let totalAmount = 0;
                const products = Array.from(productList.children).map(row => {
                    const quantity = parseInt(row.cells[2].textContent);
                    const importPrice = parseInt(row.cells[3].textContent.replace(/[^\d]/g, ''));
                    const sellPriceText = row.cells[4].textContent.split(' ')[0];
                    const sellPrice = parseInt(sellPriceText.replace(/[^\d]/g, ''));
                    totalAmount += quantity * importPrice;

                    return {
                        productCode: row.cells[0].textContent,
                        productName: row.cells[1].textContent,
                        quantity: quantity,
                        importPrice: importPrice,
                        sellPrice: sellPrice
                    };
                });

                invoiceData.totalAmount = totalAmount;
                const importData = {
                    invoice: invoiceData,
                    products: products
                };

                fetch(window.location.href, {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(importData)
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    closeImportForm();
                    location.reload();
                })
                .catch(error => {
                    console.error("Lỗi:", error);
                    alert("Đã xảy ra lỗi khi gửi dữ liệu!");
                });
            });

            // Cập nhật tổng tiền
            function updateTotalAmount() {
                let total = 0;
                Array.from(productList.children).forEach(row => {
                    const quantity = parseInt(row.cells[2].textContent);
                    const importPrice = parseInt(row.cells[3].textContent.replace(/[^\d]/g, ''));
                    total += quantity * importPrice;
                });
                totalAmountSpan.textContent = total.toLocaleString('vi-VN');
            }

            // Validation
            function validateInputs(productSelect, quantity, importPrice, profitPercent) {
                const inputs = [
                    { element: productSelect, message: "Vui lòng chọn sản phẩm" },
                    { element: quantity, message: "Số lượng phải lớn hơn 0" },
                    { element: importPrice, message: "Giá nhập phải lớn hơn hoặc bằng 0" },
                    { element: profitPercent, message: "Phần trăm lợi nhuận phải lớn hơn hoặc bằng 0" }
                ];

                let isValid = true;
                inputs.forEach(input => {
                    const value = input.element.value;
                    const errorSpan = input.element.nextElementSibling;
                    if (!value || (input.element !== productSelect && parseFloat(value) < 0)) {
                        errorSpan.textContent = input.message;
                        errorSpan.style.display = "block";
                        isValid = false;
                    } else {
                        errorSpan.style.display = "none";
                    }
                });

                return isValid;
            }

            // Reset inputs
            function resetProductInputs() {
                document.getElementById("productSelect").value = "";
                document.getElementById("quantity").value = "";
                document.getElementById("importPrice").value = "";
                document.getElementById("profitPercent").value = "";
            }

            function resetForm() {
                resetProductInputs();
                document.getElementById("supplierSelect").value = "";
                productList.innerHTML = "";
                updateTotalAmount();
            }
        });
    </script>
</body>
</html>