<?php
    include '../class/supplier.php';
    $supplier = new supplier();

    $message = "";
    $message_color = "red";

    // Xử lý submit từ cả hai form
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['submit'])) { // Thêm nhà cung cấp
            $tenNCC = trim($_POST['supplier_name']);
            $diaChi = trim($_POST['supplier_address']);
            $soDT = trim($_POST['supplier_phone']);
            
            $result = $supplier->add($tenNCC, $diaChi, $soDT);
            if ($result === true) {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $message = $result; // Lấy thông báo lỗi từ phương thức add
                $message_color = "red";
            }
        } elseif (isset($_POST['update'])) { // Sửa nhà cung cấp
            $supplier_id = $_POST['supplier_id'];
            $tenNCC = trim($_POST['supplier_name_update']);
            $diaChi = trim($_POST['supplier_address_update']);
            $soDT = trim($_POST['supplier_phone_update']);
            
            $result = $supplier->update($supplier_id, $tenNCC, $diaChi, $soDT);
            if ($result === true) {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $message = $result; // Lấy thông báo lỗi từ phương thức update
                $message_color = "red";
            }
        }
    }
    $search = isset($_POST['searchdata']) ? trim($_POST['searchdata']) : ""; // Giữ giá trị tìm kiếm
    $resultsearch = null; // Kết quả tìm kiếm

    // Xử lý khi form tìm kiếm được submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Search'])) {
        $data = trim($_POST['searchdata']);
        if (!empty($data)) { // Chỉ tìm kiếm nếu dữ liệu không rỗng
            $resultsearch = $supplier->searchInfoSupplier($data);
        }
    }

    // Xác định danh sách nhà cung cấp để hiển thị
    $search = isset($_POST['searchdata']) ? trim($_POST['searchdata']) : "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['trangThai'])) {
        $id = intval($_POST['id']);
        $status = intval($_POST['trangThai']);
        $update_status = $supplier->updateStatus($id, $status);
    }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/manage.css">
    <link rel="stylesheet" href="css/model.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/search.css">

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var modal = document.getElementById("modal");
            var updateModal = document.getElementById("modalupdate");
            var closeBtn = document.querySelectorAll(".close");
            var cancelBtn = document.querySelectorAll(".cancel");
            var formAdd = document.getElementById("formSupplier");
            var formUpdate = document.getElementById("formUpdateSupplier");

            // Hàm reset form thêm
            function resetAddForm() {
                document.getElementById("supplier_name").value = "";
                document.getElementById("supplier_address").value = "";
                document.getElementById("supplier_phone").value = "";
                resetErrors("errorname", "erroraddress", "errorphone", "error_duplicate");
            }

            // Hàm reset lỗi chung
            function resetErrors(...errorIds) {
                errorIds.forEach(id => {
                    document.getElementById(id).style.display = "none";
                });
            }

            // Mở modal thêm
            document.getElementById("openModal").addEventListener("click", function () {
                modal.style.display = "flex";
                resetAddForm();
            });

            // Mở modal sửa
            document.querySelectorAll(".btn-edit").forEach(function (editBtn) {
                editBtn.addEventListener("click", function (event) {
                    event.preventDefault();
                    var row = editBtn.closest("tr");
                    var supplierName = row.cells[1].textContent.trim();
                    var supplierAddress = row.cells[2].textContent.trim();
                    var supplierPhone = row.cells[3].textContent.trim();
                    var supplierId = editBtn.getAttribute("data-id");

                    updateModal.style.display = "flex";
                    document.getElementById("supplier_name_update").value = supplierName;
                    document.getElementById("supplier_address_update").value = supplierAddress;
                    document.getElementById("supplier_phone_update").value = supplierPhone;
                    document.getElementById("supplier_id").value = supplierId;
                    resetErrors("errorname_update", "erroraddress_update", "errorphone_update", "error_duplicate_update");
                });
            });

            // Đóng các modal
            closeBtn.forEach(btn => btn.addEventListener("click", function () {
                modal.style.display = "none";
                updateModal.style.display = "none";
            }));

            cancelBtn.forEach(btn => btn.addEventListener("click", function () {
                modal.style.display = "none";
                updateModal.style.display = "none";
            }));

            window.addEventListener("click", function (event) {
                if (event.target === modal) modal.style.display = "none";
                if (event.target === updateModal) updateModal.style.display = "none";
            });

            // Hàm validate chung cho cả hai form
            function validateForm(formId, nameId, addressId, phoneId, errorNameId, errorAddressId, errorPhoneId) {
                let isValid = true;
                let supplierName = document.getElementById(nameId).value.trim();
                let supplierAddress = document.getElementById(addressId).value.trim();
                let supplierPhone = document.getElementById(phoneId).value.trim();

                if (!supplierName) {
                    document.getElementById(errorNameId).style.display = "block";
                    isValid = false;
                } else {
                    document.getElementById(errorNameId).style.display = "none";
                }

                if (!supplierAddress) {
                    document.getElementById(errorAddressId).style.display = "block";
                    isValid = false;
                } else {
                    document.getElementById(errorAddressId).style.display = "none";
                }

                if (!supplierPhone) {
                    document.getElementById(errorPhoneId).style.display = "block";
                    isValid = false;
                } else if (!/^[0-9]{10,11}$/.test(supplierPhone)) {
                    document.getElementById(errorPhoneId).textContent = "Số điện thoại phải có 10-11 số!";
                    document.getElementById(errorPhoneId).style.display = "block";
                    isValid = false;
                } else {
                    document.getElementById(errorPhoneId).style.display = "none";
                }

                return isValid;
            }

            // Validate form thêm
            formAdd.addEventListener("submit", function (event) {
                if (!validateForm("formSupplier", "supplier_name", "supplier_address", "supplier_phone", "errorname", "erroraddress", "errorphone")) {
                    event.preventDefault();
                }
            });

            // Validate form sửa
            formUpdate.addEventListener("submit", function (event) {
                if (!validateForm("formUpdateSupplier", "supplier_name_update", "supplier_address_update", "supplier_phone_update", "errorname_update", "erroraddress_update", "errorphone_update")) {
                    event.preventDefault();
                }
            });

            // Hiển thị modal và lỗi server-side nếu có
            <?php if ($message && $message_color === "red") { ?>
                <?php if (isset($_POST['submit'])) { ?>
                    modal.style.display = "flex";
                    document.getElementById("error_duplicate").style.color = "<?php echo $message_color; ?>";
                    document.getElementById("error_duplicate").textContent = "<?php echo addslashes($message); ?>";
                    document.getElementById("error_duplicate").style.display = "block";
                <?php } elseif (isset($_POST['update'])) { ?>
                    updateModal.style.display = "flex";
                    document.getElementById("supplier_id").value = "<?php echo htmlspecialchars($_POST['supplier_id']); ?>";
                    document.getElementById("supplier_name_update").value = "<?php echo htmlspecialchars($_POST['supplier_name_update']); ?>";
                    document.getElementById("supplier_address_update").value = "<?php echo htmlspecialchars($_POST['supplier_address_update']); ?>";
                    document.getElementById("supplier_phone_update").value = "<?php echo htmlspecialchars($_POST['supplier_phone_update']); ?>";
                    document.getElementById("error_duplicate_update").style.color = "<?php echo $message_color; ?>";
                    document.getElementById("error_duplicate_update").textContent = "<?php echo addslashes($message); ?>";
                    document.getElementById("error_duplicate_update").style.display = "block";
                <?php } ?>
            <?php } ?>
        });
    </script>

    <script>
        window.onload = function () {
            document.getElementById("searchdata").addEventListener("input", function () {
                if (this.value.trim() === "") {
                    window.location.href = window.location.pathname; // Reload trang khi ô tìm kiếm trống
                }
            });
        };
    </script>
</head>
<body>
    <div class="container">
        <h2>Quản lý nhà cung cấp</h2>
        
        <!-- Ô tìm kiếm -->
        <div class="search-container">
            <!-- Nút Thêm -->
            <button class="btn-add" id="openModal">
                <i class="fa-solid fa-plus"></i> Thêm
            </button>

            <!-- Form tìm kiếm -->
            <form method="POST" class="search-input">
                <input type="text" name="searchdata" id="searchdata" placeholder="Tìm kiếm theo tên hoặc số điện thoại..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" name="Search"><i class="fa fa-search"></i> Tìm kiếm</button>
            </form>
        </div>


        <!-- Modal thêm nhà cung cấp -->
        <div id="modal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close">×</span>
                <h2>Thêm nhà cung cấp</h2>
                <form method="post" class="form" id="formSupplier">
                    <table>
                        <tr>
                            <td><label for="supplier_name">Tên nhà cung cấp:</label></td>
                            <td><input type="text" id="supplier_name" name="supplier_name" value="<?php echo isset($_POST['supplier_name']) ? htmlspecialchars($_POST['supplier_name']) : ''; ?>" placeholder="Nhập tên nhà cung cấp..."/></td>
                        </tr>
                        <tr>
                            <td colspan="2"><span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="errorname">Vui lòng nhập tên nhà cung cấp</span></td>
                        </tr>
                        <tr>
                            <td><label for="supplier_address">Địa chỉ:</label></td>
                            <td><input type="text" id="supplier_address" name="supplier_address" value="<?php echo isset($_POST['supplier_address']) ? htmlspecialchars($_POST['supplier_address']) : ''; ?>" placeholder="Nhập địa chỉ nhà cung cấp..."/></td>
                        </tr>
                        <tr>
                            <td colspan="2"><span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="erroraddress">Vui lòng nhập địa chỉ</span></td>
                        </tr>
                        <tr>
                            <td><label for="supplier_phone">Số điện thoại:</label></td>
                            <td><input type="text" id="supplier_phone" name="supplier_phone" value="<?php echo isset($_POST['supplier_phone']) ? htmlspecialchars($_POST['supplier_phone']) : ''; ?>" placeholder="Nhập số điện thoại nhà cung cấp..."/></td>
                        </tr>
                        <tr>
                            <td colspan="2"><span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="errorphone">Vui lòng nhập số điện thoại</span></td>
                        </tr>
                        <tr>
                            <td colspan="2"><span style="display: none; color: <?php echo $message_color; ?>; font-weight: bold;" id="error_duplicate"><?php echo $message; ?></span></td>
                        </tr>
                        <tr>
                            <td><button type="button" class="cancel">Hủy</button></td>
                            <td><input type="submit" name="submit" value="Lưu lại" /></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>

        <!-- Modal sửa nhà cung cấp -->
        <div id="modalupdate" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close">×</span>
                <h2>Sửa nhà cung cấp</h2>
                <form method="post" class="form" id="formUpdateSupplier">
                    <input type="hidden" id="supplier_id" name="supplier_id" value="<?php echo isset($_POST['supplier_id']) ? htmlspecialchars($_POST['supplier_id']) : ''; ?>">
                    <table>
                        <tr>
                            <td><label for="supplier_name_update">Tên nhà cung cấp:</label></td>
                            <td><input type="text" id="supplier_name_update" name="supplier_name_update" value="<?php echo isset($_POST['supplier_name_update']) ? htmlspecialchars($_POST['supplier_name_update']) : ''; ?>" placeholder="Nhập tên nhà cung cấp..."/></td>
                        </tr>
                        <tr>
                            <td colspan="2"><span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="errorname_update">Vui lòng nhập tên nhà cung cấp</span></td>
                        </tr>
                        <tr>
                            <td><label for="supplier_address_update">Địa chỉ:</label></td>
                            <td><input type="text" id="supplier_address_update" name="supplier_address_update" value="<?php echo isset($_POST['supplier_address_update']) ? htmlspecialchars($_POST['supplier_address_update']) : ''; ?>" placeholder="Nhập địa chỉ nhà cung cấp..."/></td>
                        </tr>
                        <tr>
                            <td colspan="2"><span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="erroraddress_update">Vui lòng nhập địa chỉ</span></td>
                        </tr>
                        <tr>
                            <td><label for="supplier_phone_update">Số điện thoại:</label></td>
                            <td><input type="text" id="supplier_phone_update" name="supplier_phone_update" value="<?php echo isset($_POST['supplier_phone_update']) ? htmlspecialchars($_POST['supplier_phone_update']) : ''; ?>" placeholder="Nhập số điện thoại nhà cung cấp..."/></td>
                        </tr>
                        <tr>
                            <td colspan="2"><span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="errorphone_update">Vui lòng nhập số điện thoại</span></td>
                        </tr>
                        <tr>
                            <td colspan="2"><span style="display: none; color: <?php echo $message_color; ?>; font-weight: bold;" id="error_duplicate_update"><?php echo $message; ?></span></td>
                        </tr>
                        <tr>
                            <td><button type="button" class="cancel">Hủy</button></td>
                            <td><input type="submit" name="update" value="Cập nhật" /></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>

        <!-- Bảng danh sách -->
        <div class="table-container">
            <table class="table_slider">
                <thead>
                    <tr>
                        <th style="width: 5%;">STT</th>
                        <th style="width: 25%;">Tên nhà cung cấp</th>
                        <th style="width: 25%;">Địa chỉ</th>
                        <th style="width: 15%;">Số điện thoại</th>
                        <th style="width: 15%;">Trạng thái</th>
                        <th style="width: 15%;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    
                    <?php
                    if (!empty($search)) {
                        $supplierlist = $supplier->searchInfoSupplier($search); // Gọi phương thức search
                    } else {
                        $supplierlist = $supplier->show(); // Gọi phương thức show
                    }

                    $i = 0;
                    if ($supplierlist !== false && $supplierlist->num_rows > 0) { // Kiểm tra $sdlist không phải false
                        while ($result = $supplierlist->fetch_assoc()) {
                            $i++;
                    ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td><?php echo htmlspecialchars($result['tenNCC']); ?></td>
                        <td><?php echo htmlspecialchars($result['diaChi']); ?></td>
                        <td><?php echo htmlspecialchars($result['soDT']); ?></td>
                        <td><?php echo $result['trangThai'] == 1 ? "Còn hợp tác" : "Ngừng hợp tác"; ?></td>
                        <td class="btn-container">
                            <a style="background-color: green;" href="#" class="btn-action btn-edit" data-id="<?php echo $result['id_nhacungcap']; ?>">
                                <i class="fa-solid fa-edit"></i>
                            </a>
                            <form action="" method="POST">
                                <input type="hidden" name="id" value="<?php echo $result['id_nhacungcap']; ?>">
                                <input type="hidden" name="trangThai" value="<?php echo ($result['trangThai'] == 1) ? 1 : 0; ?>">
                                <button type="submit" class="btn-action" style="background-color: red;" title="Đổi trạng thái">
                                    <i class="fa-solid fa-rotate"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php
                            }
                        } else {
                            // Hiển thị thông báo khi không có kết quả hoặc truy vấn lỗi
                            echo '<tr><td colspan="6" style="text-align: center; padding: 20px;">';
                            if ($supplierlist === false) {
                                echo "Không tìm thấy nhà cung cấp nào phù hợp với từ khóa '" . htmlspecialchars($search) . "'";
                            }
                            echo '</td></tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>