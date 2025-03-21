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

// Xử lý yêu cầu AJAX ngay trong cùng file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
    file_put_contents('debug.log', "Dữ liệu nhận được: " . file_get_contents('php://input') . "\n", FILE_APPEND);
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['invoice']) || !isset($data['products'])) {
        file_put_contents('debug.log', "Lỗi: Dữ liệu không hợp lệ\n", FILE_APPEND);
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

    file_put_contents('debug.log', "Dữ liệu trước khi gọi addInvoice: " . print_r($chiTietSanPham, true) . "\n", FILE_APPEND);

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
        file_put_contents('debug.log', "Lỗi khi gọi addInvoice: " . $e->getMessage() . "\n", FILE_APPEND);
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
    <title>COWatch Store</title>
    <link rel="stylesheet" href="css/model.css">
    <link rel="stylesheet" href="css/manage.css">
    <link rel="stylesheet" href="css/test1.css">
</head>
<body>
    <div class="container">
        <h2>Quản lý nhập hàng</h2>
        <div class="search-container"> 
            <form method="POST" class="search-input">
                <input type="text" name="searchdata" id="searchdata" placeholder="Tìm kiếm theo tên...">
                <button type="submit" name="Search"><span>🔍</span> Tìm kiếm</button>
            </form>
            <button class="btn btn-add" id="openModal">
                <span>+</span> Thêm
            </button>
        </div>
        <div class="table-container">
            <table class="table_slider">
                <thead>
                    <tr>
                        <th style="width: 5%;">STT</th>
                        <th style="width: 20%;">Nhà cung cấp</th>
                        <th style="width: 15%;">Ngày nhập</th>
                        <th style="width: 15%;">Người nhập</th>
                        <th style="width: 20%;">Tổng tiền</th>
                        <th style="width: 25%;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($imports) {
                        $stt = 1;
                        while ($import = $imports->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?php echo $stt++; ?></td>
                                <td><?php echo $import['tenNCC']; ?></td>
                                <td><?php echo $import['ngayLap']; ?></td>
                                <td><?php echo $import['tenNhanVien']; ?></td>
                                <td><?php echo number_format($import['tongTien'], 0, ',', '.') . ' VNĐ'; ?></td>
                                <td class="btn-container">
                                    <button class="btn btn-action btn-detail" onclick="showDetail('<?php echo $import['maPhieuNhap']; ?>', '<?php echo $import['tenNCC']; ?>', '<?php echo $import['ngayLap']; ?>', '<?php echo $import['maTaiKhoan']; ?>', '<?php echo number_format($import['tongTien'], 0, ',', '.') . ' VNĐ'; ?>')">
                                        <span>👁️</span> Xem chi tiết
                                    </button>
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
                <!-- Thông tin hóa đơn -->
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
                                    while ($ncclist = $ncc->fetch_assoc()) {
                                        echo '<option value="' . $ncclist['id_nhacungcap'] . '">' . $ncclist['tenNCC'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Thêm sản phẩm -->
                <div class="form-section">
                <div class="form-section-header">
                    <button id="addProduct" class="btn btn-add">
                        <span>+</span> Thêm
                    </button>
                    </div>
                    <h3 class="section-title">Thêm sản phẩm</h3>
                    <div class="form-grid product-input-grid">
                        <div class="form-group">
                            <label for="productSelect">Sản phẩm</label>
                            <select id="productSelect" required>
                                <option value="">-- Chọn sản phẩm --</option>
                                <?php
                                if ($sanpham) {
                                    while ($sanphamlist = $sanpham->fetch_assoc()) {
                                        echo '<option value="' . $sanphamlist['maSanPham'] . '|' . $sanphamlist['tenSanPham'] . '">' . $sanphamlist['tenSanPham'] . '</option>';
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
                            <label for="sellPrice">Giá bán (VNĐ)</label>
                            <input type="number" id="sellPrice" min="0" placeholder="Giá bán" required>
                            <span class="error-message"></span>
                        </div>
                    </div>
                </div>

                <!-- Danh sách sản phẩm -->
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
                                    <th>Giá bán (VNĐ)</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="productList"></tbody>
                        </table>
                    </div>
                    <!-- Thêm phần hiển thị tổng tiền -->
                    <div class="total-amount" style="margin-top: 10px; text-align: right; font-weight: bold;">
                        Tổng tiền: <span id="totalAmount">0</span> VNĐ
                    </div>
                </div>

                <!-- Nút điều khiển -->
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

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const importForm = document.getElementById("importForm");
        const openModalBtn = document.getElementById("openModal");
        const productList = document.getElementById("productList");
        const totalAmountSpan = document.getElementById("totalAmount");

        // Mở form
        openModalBtn.addEventListener("click", function () {
            importForm.style.display = "flex";
        });

        // Đóng form
        window.closeImportForm = function() {
            importForm.style.display = "none";
            resetForm();
        };

        // Thêm sản phẩm
        document.getElementById("addProduct").addEventListener("click", function (event) {
            event.preventDefault();

            const productSelect = document.getElementById("productSelect");
            const quantity = document.getElementById("quantity");
            const importPrice = document.getElementById("importPrice");
            const sellPrice = document.getElementById("sellPrice");

            if (!validateInputs(productSelect, quantity, importPrice, sellPrice)) return;

            const [productCode, productName] = productSelect.value.split("|");
            const quantityValue = parseInt(quantity.value);
            const importPriceValue = parseInt(importPrice.value);
            const sellPriceValue = parseInt(sellPrice.value);

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
                    <td>${sellPriceValue.toLocaleString('vi-VN')} VNĐ</td>
                    <td><button class="btn-delete" onclick="this.parentElement.parentElement.remove(); updateTotalAmount()">❌</button></td>
                `;
                productList.appendChild(newRow);
            }

            resetProductInputs();
            updateTotalAmount(); // Cập nhật tổng tiền sau khi thêm sản phẩm
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
                const sellPrice = parseInt(row.cells[4].textContent.replace(/[^\d]/g, ''));
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

            console.log("Dữ liệu gửi đi:", importData);

            fetch(window.location.href, {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(importData)
            })
            .then(response => {
                return response.text().then(text => {
                    console.log("Response từ server:", text);
                    return JSON.parse(text);
                });
            })
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

        // Hàm cập nhật tổng tiền
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
        function validateInputs(productSelect, quantity, importPrice, sellPrice) {
            const inputs = [
                { element: productSelect, message: "Vui lòng chọn sản phẩm" },
                { element: quantity, message: "Số lượng phải lớn hơn 0" },
                { element: importPrice, message: "Giá nhập phải lớn hơn hoặc bằng 0" },
                { element: sellPrice, message: "Giá bán phải lớn hơn 0" }
            ];

            let isValid = true;
            inputs.forEach(input => {
                const value = input.element.value;
                const errorSpan = input.element.nextElementSibling;
                if (!value || (input.element !== productSelect && parseInt(value) <= 0)) {
                    errorSpan.textContent = input.message;
                    errorSpan.style.display = "block";
                    isValid = false;
                } else {
                    errorSpan.style.display = "none";
                }
            });

            // Kiểm tra giá nhập < giá bán
            const importPriceValue = parseInt(importPrice.value);
            const sellPriceValue = parseInt(sellPrice.value);
            if (importPriceValue >= sellPriceValue) {
                importPrice.nextElementSibling.textContent = "Giá nhập phải nhỏ hơn giá bán";
                importPrice.nextElementSibling.style.display = "block";
                isValid = false;
            }

            return isValid;
        }

        // Reset inputs
        function resetProductInputs() {
            document.getElementById("productSelect").value = "";
            document.getElementById("quantity").value = "";
            document.getElementById("importPrice").value = "";
            document.getElementById("sellPrice").value = "";
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