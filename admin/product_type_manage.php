<?php
    include '../class/category.php';
    $category = new category();

    $add_error = ''; // Thông báo lỗi cho form Thêm
    $edit_error = ''; // Thông báo lỗi cho form Sửa

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])){
        $name = trim($_POST['type_name']);
        $id = $_POST['id'] ?? null;

        if ($id) {
            $result = $category->update($id, $name);
            if ($result === true) {
                header("Location: " . $_SERVER['PHP_SELF']); // Reload trang nếu thành công
                exit();
            } elseif ($result === 'duplicate') {
                $edit_error = "Tên loại sản phẩm đã tồn tại";
            } else {
                $edit_error = "Cập nhật loại sản phẩm thất bại";
            }
        } else {
            $result = $category->insertcategory($name);
            if ($result === true) {
                header("Location: " . $_SERVER['PHP_SELF']); // Reload trang nếu thành công
                exit();
            } elseif ($result === 'duplicate') {
                $add_error = "Tên loại sản phẩm đã tồn tại";
            } else {
                $add_error = "Thêm loại sản phẩm thất bại";
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['trangThai'])) {
        $id = intval($_POST['id']);
        $status = intval($_POST['trangThai']);
        $category->updateStatus($id, $status);
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
    <script src="js/type.js"></script>
    <style>
        .error { color: red; font-weight: bold; margin-bottom: 10px; text-align: center; }
    </style>
    <script>
        window.onload = function () {
            document.getElementById("searchdata").addEventListener("input", function () {
                if (this.value.trim() === "") {
                    window.location.href = window.location.pathname;
                }
            });

            // Giữ modal mở nếu có lỗi
            <?php if ($add_error) { ?>
                document.getElementById('modal').style.display = 'block';
            <?php } ?>
            <?php if ($edit_error) { ?>
                document.getElementById('editModal').style.display = 'block';
                document.getElementById('edit_brand_name').value = '<?php echo htmlspecialchars($_POST['type_name']); ?>';
                document.getElementById('edit_id').value = '<?php echo htmlspecialchars($_POST['id']); ?>';
            <?php } ?>
        };
    </script>
</head>
<body>
    <div class="container">
        <h2>Quản lý loại sản phẩm</h2>
        <div class="search-container">
            <button class="btn-add" id="openModal">
                <i class="fa-solid fa-plus"></i> Thêm
            </button>
            <form method="POST" class="search-input">
                <input type="text" name="searchdata" id="searchdata" placeholder="Tìm kiếm theo tên..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" name="Search"><i class="fa fa-search"></i> Tìm kiếm</button>
            </form>
        </div>

        <!-- Modal Thêm -->
        <div id="modal" class="modal" style="display: none; ">
            <div class="modal-content">
                <span class="close">×</span>
                <h2>Thêm loại sản phẩm</h2>
                <?php if ($add_error) { echo "<div class='error'>$add_error</div>"; } ?>
                <form method="post" enctype="multipart/form-data" class="form" id="formSlider">
                    <table>
                        <tr>
                            <td><label for="brand_name">Loại sản phẩm:</label></td>
                            <td><input type="text" id="brand_name" name="type_name" placeholder="Nhập tên loại..." value="<?php echo $add_error ? htmlspecialchars($_POST['type_name']) : ''; ?>" required/></td>
                        </tr>
                        <tr>
                            <td><button type="button" class="cancel">Hủy</button></td>
                            <td><input type="submit" name="submit" value="Lưu lại" /></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>

        <!-- Modal Sửa -->
        <div id="editModal" class="modal modal-update" style="display: none;">
            <div class="modal-content">
                <span class="closeEdit close">×</span>
                <h2>Sửa loại sản phẩm</h2>
                <?php if ($edit_error) { echo "<div class='error'>$edit_error</div>"; } ?>
                <form method="post" enctype="multipart/form-data" class="form" id="formEdit">
                    <input type="hidden" name="id" id="edit_id">
                    <table>
                        <tr>
                            <td><label for="edit_brand_name">Loại sản phẩm:</label></td>
                            <td><input type="text" id="edit_brand_name" name="type_name" placeholder="Nhập tên loại..." required/></td>
                        </tr>
                        <tr>
                            <td><button type="button" class="cancelEdit cancel">Hủy</button></td>
                            <td><input type="submit" name="submit" value="Cập nhật" /></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>

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
                        if (!empty($search)) {
                            $categorylist = $category->search($search);
                        } else {
                            $categorylist = $category->show();
                        }

                        $i = 0;
                        if ($categorylist !== false && $categorylist->num_rows > 0) {
                            while ($result = $categorylist->fetch_assoc()) {
                                $i++;
                    ?>
                    <tr>
                        <td><?php echo $i?></td>
                        <td><?php echo $result['tenLoai']?></td>
                        <td><?php echo (intval($result['trangthai']) === 1 ? "Còn kinh doanh" : "Ngừng kinh doanh")?></td>
                        <td class="btn-container">
                            <button style="background-color:green;" class="btn-action btn-edit openEditModal" 
                                    data-id="<?php echo $result['id_loai']; ?>" 
                                    data-name="<?php echo htmlspecialchars($result['tenLoai']); ?>"
                                    title="Sửa">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                            <form action="" method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $result['id_loai']; ?>">
                                <input type="hidden" name="trangThai" value="<?php echo ($result['trangthai'] == 1) ? 1 : 0; ?>">
                                <button style="background-color: red;" type="submit" class="btn-action btn-edit" title="Đổi trạng thái">
                                    <i class="fa-solid fa-rotate"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php
                            }
                        } else {
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

    <script>
        const editModal = document.getElementById('editModal');
        const editButtons = document.querySelectorAll('.openEditModal');
        const closeEdit = document.querySelector('.closeEdit');
        const cancelEdit = document.querySelector('.cancelEdit');
        const addModal = document.getElementById('modal');
        const openAddModal = document.getElementById('openModal');
        const closeAdd = document.querySelector('.close');
        const cancelAdd = document.querySelector('.cancel');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_brand_name').value = name;
                editModal.style.display = 'block';
            });
        });

        closeEdit.addEventListener('click', function() {
            editModal.style.display = 'none';
        });

        cancelEdit.addEventListener('click', function() {
            editModal.style.display = 'none';
        });

        openAddModal.addEventListener('click', function() {
            addModal.style.display = 'block';
        });

        closeAdd.addEventListener('click', function() {
            addModal.style.display = 'none';
        });

        cancelAdd.addEventListener('click', function() {
            addModal.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
            if (event.target == addModal) {
                addModal.style.display = 'none';
            }
        });
    </script>
</body>
</html>