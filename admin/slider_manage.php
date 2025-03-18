<?php
    include '../class/slider.php';
    $sl = new slider();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        $insert_product = $sl->insert($_POST, $_FILES);
    }
    if (isset($_GET['delid'])) {
        $delid = $_GET['delid'];
        $delete_slider = $sl->delete($delid);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        $slider_id = $_POST['slider_id'];
        $update_slider = $sl->update($slider_id, $_POST, $_FILES);
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
    <script src="js/slider.js"></script>
</head>
<body>
    <div class="container">
        <h2>Quản lý slider</h2>
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

        <!-- Thêm slider -->
        <div id="modal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close">×</span>
                <h2>Thêm Slider</h2>
                <form method="post" enctype="multipart/form-data" class="form" id="formSlider">
                    <table>
                        <tr>
                            <td><label for="slide_name">Tên slide:</label></td>
                            <td><input type="text" id="slide_name" name="slide_name" placeholder="Nhập tên slide..."/></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="errorten">
                                    Vui lòng nhập tên slide
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
                            <td><button type="button" class="cancel">Hủy</button></td>
                            <td><input type="submit" name="submit" value="Lưu lại" /></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>

        <!-- Sửa Slider -->
        <div id="modalupdate" class="modal" style="display: none;" enctype="multipart/form-data">
            <div class="modal-content">
                <span class="close">×</span>
                <h2>Sửa Slider</h2>
                <form method="post" enctype="multipart/form-data" class="form" id="formUpdateSlider">
                    <input type="hidden" id="slider_id" name="slider_id">
                    <table>
                        <tr>
                            <td><label for="slide_name_update">Tên slide:</label></td>
                            <td><input type="text" id="slide_name_update" name="slide_name" placeholder="Nhập tên slide..."/></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="errorten_update">
                                    Vui lòng nhập tên slide
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
                        <th style="width: 10%;">STT</th>
                        <th style="width: 30%;">Tên slider</th>
                        <th style="width: 40%;">Hình ảnh</th>
                        <th style="width: 20%;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Nếu có dữ liệu tìm kiếm
                        if (!empty($search)) {
                            $sdlist = $sl->search($search); // Gọi phương thức search
                        } else {
                            $sdlist = $sl->show(); // Gọi phương thức show
                        }

                        $i = 0;
                        if ($sdlist !== false && $sdlist->num_rows > 0) { // Kiểm tra $sdlist không phải false
                            while ($result = $sdlist->fetch_assoc()) {
                                $i++;
                    ?>
                    <tr>
                        <td><?php echo $i?></td>
                        <td><?php echo $result['tenSlider']?></td>
                        <td><img src="<?php echo $result['hinhAnh'];?>" alt=""></td>
                        <td class="btn-container">
                            <a href="" class="btn-action btn-edit" data-id="<?php echo $result['id_slider']?>" title="Chỉnh sửa">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <a title="Xóa" href="slider_manage.php?delid=<?php echo $result['id_slider']?>" class="btn-action btn-delete" onclick="return confirm('Bạn có muốn xóa slider này không?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php
                            }
                        } else {
                            // Hiển thị thông báo khi không có kết quả hoặc truy vấn lỗi
                            echo '<tr><td colspan="4" style="text-align: center; padding: 20px;">';
                            if ($sdlist === false) {
                                echo "Không tìm thấy slider nào phù hợp với từ khóa '" . htmlspecialchars($search) . "'";
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