<?php
    include '../class/category.php';
	$category = new category();

	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])){
        $name = $_POST['type_name'];
        $id = $_POST['id'] ?? null;

        
        if ($id) {
            $update_category = $category->update($id, $name);
        } else {
            $insert_catagory = $category->insertcategory($name);
        }
	}

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['trangThai'])) {
        $id = intval($_POST['id']);
        $status = intval($_POST['trangThai']);
        $update_status = $category->updateStatus($id, $status);
    }

    $search = isset($_POST['searchdata']) ? trim($_POST['searchdata']) : "";
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
    <link rel="stylesheet" href="css/search.css">
    <script src="js/type.js"></script>
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
        <h2>Quản lý loại sản phẩm</h2>
        <div class="search-container">
            <!-- Nút Thêm -->
            <button class="btn-add" id="openModal">
                <i class="fa-solid fa-plus"></i> Thêm
            </button>

            <!-- Form tìm kiếm -->
            <form method="POST" class="search-input">
                <input type="text" name="searchdata" id="searchdata" placeholder="Tìm kiếm theo tên..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" name="Search"><i class="fa fa-search"></i> Tìm kiếm</button>
            </form>
        </div>

        <div id="modal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Thêm loại sản phẩm</h2>
                <form method="post" enctype="multipart/form-data" class="form" id="formSlider">
                    <table>
                        <tr>
                            <td><label for="brand_name">Loại sản phẩm:</label></td>
                            <td><input type="text" id="brand_name" name="type_name" placeholder="Nhập tên loại..."/></td>
                            
                        </tr>
                        <tr>
                            <td colspan="2">
                                <span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="errorten">
                                    Vui lòng nhập tên loại sản phẩm
                                </span>
                            </td>
                        </tr>

                        
                        <tr>
                            <td><button type="button" class="cancel">Hủy</button></td>
                            <td><input type="submit" name="submit" value="Lưu lại" /></td>
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
                        <th style="width: 20%;">STT</th>
                        <th style="width: 50%;">Loại sản phẩm</th>
                        <th style="width: 15%;">Trạng thái</th>
                        <th style="width: 15%;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Nếu có dữ liệu tìm kiếm
                        if (!empty($search)) {
                            $categorylist = $category->search($search); // Gọi phương thức search
                        } else {
                            $categorylist = $category->show(); // Gọi phương thức show
                        }

                        $i = 0;
                        if ($categorylist !== false && $categorylist->num_rows > 0) { // Kiểm tra $sdlist không phải false
                            while ($result = $categorylist->fetch_assoc()) {
                                $i++;
                    ?>
                    <tr>
                        <td><?php echo $i?></td>
                        <td><?php echo $result['tenLoai']?></td>
                        <td><?php echo (intval($result['trangthai']) === 1 ? "Còn kinh doanh" : "Ngừng kinh doanh")?></td>
                        <td class="btn-container">
                            <form action="" method="POST">
                                <input type="hidden" name="id" value="<?php echo $result['id_loai']; ?>">
                                <input type="hidden" name="trangThai" value="<?php echo ($result['trangthai'] == 1) ? 1 : 0; ?>">
                                <button style="margin-left: 30px;" type="submit" class="btn-action btn-edit" title="Đổi trạng thái">
                                    <i class="fa-solid fa-rotate"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php
                            }
                        } else {
                            // Hiển thị thông báo khi không có kết quả hoặc truy vấn lỗi
                            echo '<tr><td colspan="4" style="text-align: center; padding: 20px;">';
                            if ($categorylist === false) {
                                echo "Không tìm thấy loại sản phẩm nào phù hợp với từ khóa '" . htmlspecialchars($search) . "'";
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