<?php
    include '../class/brand.php';
    $brand = new brand();

    $error_message = ""; // Biến lưu lỗi

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        $insert_product = $brand->add($_POST, $_FILES);

        // Kiểm tra kết quả trả về từ hàm insert()
        if (strpos($insert_product, 'error') !== false) {
            $error_message = $insert_product; // Lưu thông báo lỗi
        }
    }

    $search = isset($_POST['searchdata']) ? trim($_POST['searchdata']) : "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['trangThai'])) {
        $id = intval($_POST['id']);
        $status = intval($_POST['trangThai']);
        $update_status = $brand ->updateStatus($id, $status);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        $brand_id = $_POST['brand_id'];
        $update_brand = $brand->update($brand_id, $_POST, $_FILES);
    }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/manage.css">
    <link rel="stylesheet" href="css/model.css">
    <link rel="stylesheet" href="css/search.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="js/brand.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
    var updateModal = document.getElementById("modalupdate");
    var closeBtns = document.querySelectorAll(".close");
    var cancelBtns = document.querySelectorAll(".cancel");
    var editButtons = document.querySelectorAll(".btn-edit");
    
    editButtons.forEach(function (editBtn) {
        editBtn.addEventListener("click", function (event) {
            event.preventDefault(); // Ngăn chặn reload trang

            var row = editBtn.closest("tr");
            var slideName = row.cells[1].textContent.trim();
            var imageSrc = row.cells[2].querySelector("img").src;
            var slideId = editBtn.getAttribute("data-id");
            var desc = row.cells[3].textContent.trim();

            document.getElementById("modalupdate").style.display = "flex";
            document.getElementById("brand_name_update").value = slideName;
            document.getElementById("image_preview").src = imageSrc;
            document.getElementById("brand_id").value = slideId;
            document.getElementById("brand_desc_update").value = desc;
        });
    });

    closeBtns.forEach(btn => btn.addEventListener("click", function () {
        updateModal.style.display = "none";
    }));

    cancelBtns.forEach(btn => btn.addEventListener("click", function () {
        updateModal.style.display = "none";
    }));

    window.addEventListener("click", function (event) {
        if (event.target === updateModal) {
            updateModal.style.display = "none";
        }
    });
});
    </script>
</head>
<body>
    <div class="container">
        <h2>Quản lý thương hiệu</h2>
        <div class="search-container">
            <!-- Nút Thêm -->
            <button class="btn-add" id="openModal">
                <i class="fa-solid fa-plus"></i> Thêm
            </button>

            <!-- Form tìm kiếm -->
            <form method="POST" class="search-input">
                <input type="text" name="searchdata" id="searchdata" placeholder="Tìm kiếm theo tên ..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" name="Search"><i class="fa fa-search"></i> Tìm kiếm</button>
            </form>
        </div>

        <div id="modal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Thêm thương hiệu</h2>
                <form method="post" enctype="multipart/form-data" class="form" id="formBrand">
                    <table>
                        <tr>
                            <td><label for="brand_name">Tên thương hiệu:</label></td>
                            <td><input type="text" id="brand_name" name="brand_name" placeholder="Nhập tên thương hiệu..."/></td>
                            
                        </tr>
                        <tr>
                            <td colspan="2">
                                <span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="errorten">
                                    Vui lòng nhập tên thương hiệu
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td><label for="image">Chọn ảnh:</label></td>
                            <td><input type="file" id="image" name="image" accept="image/*"/></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="erroranh">
                                    Vui lòng chọn hình ảnh
                                </span>
                            </td>
                        </tr>
                        
                        <tr>
                            <td><label for="brand_desc">Mô tả: </label></td>
                            <td>
                                <textarea id="brand_desc" name="brand_desc" placeholder="Nhập mô tả..."></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="errordesc">
                                    Vui lòng nhập mô tả
                                </span>
                            </td>
                        </tr>

                        <?php if (!empty($error_message)): ?>
                            <tr>
                                <td colspan="2">
                                    <span class="error-message" style="color: red; font-weight: bold; padding: 10px;">
                                        <?php echo $error_message; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td><button type="button" class="cancel">Hủy</button></td>
                            <td><input type="submit" name="submit" value="Lưu lại" /></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>

        <div id="modalupdate" class="modal" style="display: none;" enctype="multipart/form-data">
            <div class="modal-content">
                <span class="close">×</span>
                <h2>Sửa thương hiệu</h2>
                <form method="post" enctype="multipart/form-data" class="form" id="formUpdateBrand">
                    <input type="hidden" id="brand_id" name="brand_id">
                    <table>
                        <tr>
                            <td><label for="brand_name_update">Tên thương hiệu:</label></td>
                            <td><input type="text" id="brand_name_update" name="brand_name_update" placeholder="Nhập tên slide..."/></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="errorten_update">
                                    Vui lòng nhập tên thương hiệu
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><label>Hình ảnh hiện tại:</label></td>
                            <td><img id="image_preview" src="" alt="Ảnh hiện tại" width="150"></td>
                        </tr>
                        <tr>
                            <td><label for="image_update">Chọn ảnh mới:</label></td>
                            <td><input type="file" id="image_update" name="image" accept="image/*"/></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="erroranh_update">
                                    Vui lòng chọn hình ảnh
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="brand_desc_update">Mô tả: </label></td>
                            <td>
                                <textarea id="brand_desc_update" name="brand_desc_update" placeholder="Nhập mô tả..."></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="errordesc">
                                    Vui lòng nhập mô tả
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><button type="button" class="cancel">Hủy</button></td>
                            <td><input type="submit" name="update" value="Cập nhật" /></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>

        <!-- Bọc bảng trong div có thanh cuộn -->
        <div class="table-container">
            <table class="table_slider">
                <thead>
                    <tr>
                        <th style="width: 3%;">STT</th>
                        <th style="width: 17%;">Tên thương hiệu</th>
                        <th style="width: 20%;">Hình ảnh</th>
                        <th style="width: 30%;">Mô tả</th>
                        <th style="width: 15%;">Trạng thái</th>
                        <th style="width: 15%;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if (!empty($search)) {
                            $brlist = $brand->search($search); // Gọi phương thức search
                        } else {
                            $brlist = $brand->show(); // Gọi phương thức show
                        }

                        $i = 0;
                        if ($brlist !== false && $brlist->num_rows > 0) { // Kiểm tra $sdlist không phải false
                            while ($result = $brlist->fetch_assoc()) {
                                $i++;
                    ?>
                    <tr>
                        <td><?php echo $i?></td>
                        <td><?php echo $result['tenThuongHieu']?></td>
                        <td><img src="<?php echo $result['hinhAnh'];?>" alt=""></td>
                        <td><?php echo $result['mota']?></td>
                        <td><?php echo (intval($result['trangthai']) === 1 ? "Còn kinh doanh" : "Ngừng kinh doanh")?></td>
                        <td class="btn-container">
                            <a href="" style="background-color: green;" title="Sửa" class="btn-action btn-edit" data-id="<?php echo $result['id_thuonghieu']; ?>">
                                <i class="fa-solid fa-edit"></i>
                            </a>
                            <form action="" method="POST">
                                <input type="hidden" name="id" value="<?php echo $result['id_thuonghieu']; ?>">
                                <input type="hidden" name="trangThai" value="<?php echo ($result['trangthai'] == 1) ? 1 : 0; ?>">
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
                            echo '<tr><td colspan="5" style="text-align: center; padding: 20px;">';
                            if ($brlist === false) {
                                echo "Không tìm thấy thương hiệu nào phù hợp với từ khóa '" . htmlspecialchars($search) . "'";
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