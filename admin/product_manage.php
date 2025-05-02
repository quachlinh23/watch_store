<?php
    include '../class/product.php';
    include '../class/brand.php';
    include '../class/category.php';

    $pr = new product();
    $br = new brand();
    $category = new category();

    // Xử lý thêm sản phẩm
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && !isset($_POST['product_id'])) {
        $insert_product = $pr->insert($_POST, $_FILES);
        if (isset($insert_product)) {
            echo "<script>alert('$insert_product'); window.location.href = window.location.pathname;</script>";
        }
    }

    // Xử lý sửa sản phẩm

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        $update_data = [
            'product_name' => $_POST['product_name'],
            'product_desc' => $_POST['product_desc'],
            'product_type' => $_POST['product_type'],
            'product_brand' => $_POST['product_brand']
        ];
        $update_product = $pr->update($product_id, $update_data, $_FILES);
        if (isset($update_product)) {
            echo "<script>alert('$update_product'); window.location.href = window.location.pathname;</script>";
        }
    }

    $search = isset($_POST['searchdata']) ? htmlspecialchars(trim($_POST['searchdata']), ENT_QUOTES, 'UTF-8') : "";
    $resultsearch = null;
    $error = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Search'])) {
        if (!empty($search)) {
            $resultsearch = $pr->searchInfoProduct($search);
            if (empty($resultsearch)) {
                $error = "Không tìm thấy sản phẩm phù hợp.";
            }
        } else {
            $error = "Vui lòng nhập từ khóa tìm kiếm.";
        }
    }
    // Xử lý đổi trạng thái
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['trangThai'])) {
        $id = intval($_POST['id']);
        $status = intval($_POST['trangThai']);
        $update_status = $pr->updateStatus($id, $status);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maSanPham'], $_POST['loaiDay'], $_POST['pin'], $_POST['matKinh'], $_POST['chongNuoc'], $_POST['chucNang'])) {
        $maSanPham = htmlspecialchars(trim($_POST['maSanPham']));
        $specData = [
            'loaiDay' => !empty($_POST['loaiDay']) ? $_POST['loaiDay'] : null,
            'pin' => !empty($_POST['pin']) ? $_POST['pin'] : null,
            'matKinh' => !empty($_POST['matKinh']) ? $_POST['matKinh'] : null,
            'chongNuoc' => !empty($_POST['chongNuoc']) ? $_POST['chongNuoc'] : null,
            'chucNang' => !empty($_POST['chucNang']) ? $_POST['chucNang'] : null
        ];
        $pr->updateSpec($maSanPham, $specData);
        echo "<script>alert('Cập nhật thông số thành công'); window.location.href = window.location.pathname;</script>";
    }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="css/manage.css">
    <link rel="stylesheet" href="css/model.css">
    <link rel="stylesheet" href="css/product_custom.css">
    <link rel="stylesheet" href="css/search.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        .image-preview-container img { max-width: 100px; margin: 5px; }
        .image-placeholder { width: 100px; height: 100px; background: #f0f0f0; margin: 5px; display: inline-block; }
        .error { color: red; font-size: 12px; display: none; }
        #selectImagesBtn, #selectImagesBtnEdit { padding: 5px 10px; background: #007bff; color: white; border: none; cursor: pointer; }
        #selectImagesBtn:hover, #selectImagesBtnEdit:hover { background: #0056b3; }

        /* Overlay nền mờ */
        .modal-spec {
            display: none; /* Hiển thị bằng JS khi cần */
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        /* Hộp nội dung chính giữa màn hình */
        .modal-content-spec {
            background-color: #fff;
            margin: 80px auto;
            padding: 30px;
            border-radius: 10px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            position: relative;
            animation: fadeIn 0.3s ease-in-out;
        }

        /* Tiêu đề */
        .modal-content-spec h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 22px;
            color: #333;
        }

        /* Nút đóng */
        .modal-content-spec .close-spec {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: #888;
            cursor: pointer;
        }

        /* Nhóm input */
        .spec-input {
            margin-bottom: 15px;
        }

        .spec-input label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #444;
        }

        .spec-input input,
        .spec-input textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .spec-input textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* Nút lưu */
        .button-thongso {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .button-thongso button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .button-thongso button:hover {
            background-color: #218838;
        }

        /* Animation mở */
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

    </style>
    <script>
        window.onload = function () {
            let timeout;
            document.getElementById("searchdata").addEventListener("input", function () {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    if (this.value.trim() === "") {
                        document.querySelector(".search-input").submit();
                    }
                }, 500);
            });
        };
    </script>
</head>
<body>
    
    <!-- Modal Thêm Sản Phẩm -->
    <div id="add-modal" class="modal" style="display: none;">
        <div class="modal-content" style="margin: auto;">
            <span class="close">×</span>
            <h2>Thêm sản phẩm</h2>
            <form method="post" enctype="multipart/form-data" class="form" id="addForm">
                <div class="form-container">
                    <div class="form-column">
                        <div class="form-group">
                            <label for="product_name_add">Tên sản phẩm:</label>
                            <input type="text" id="product_name_add" name="product_name" placeholder="Nhập tên sản phẩm..." />
                            <span class="error" id="errorten_add"></span>
                        </div>
                        <div class="form-group">
                            <label for="main_image_add">Ảnh chính:</label>
                            <input type="file" id="main_image_add" name="main_image" accept="image/*" onchange="previewMainImage(this, 'main-image-preview-add')" />
                            <div id="main-image-preview-add" class="image-preview-container"></div>
                            <span class="error" id="erroranh_add"></span>
                        </div>
                        <div class="form-group">
                            <label for="product_images_add">Ảnh phụ :</label>
                            <button type="button" id="selectImagesBtn" onclick="document.getElementById('product_images_add').click();">Chọn ảnh phụ</button>
                            <input type="file" id="product_images_add" accept="image/*" style="display: none;" onchange="previewImages(this, 'image-preview-add')" />
                            <div id="image-preview-add" class="image-preview-container"></div>
                            <div id="hidden-images-add"></div> <!-- Khu vực chứa input ẩn -->
                            <span class="error" id="erroranhphu_add"></span>
                        </div>
                    </div>
                    <div class="form-column">
                        <div class="form-group">
                            <label for="desc_add">Mô tả:</label>
                            <textarea name="product_desc" id="desc_add" placeholder="Nhập mô tả sản phẩm..."></textarea>
                            <span class="error" id="errormota_add"></span>
                        </div>
                        <div class="form-group">
                            <label for="product_type_add">Loại sản phẩm:</label>
                            <select id="product_type_add" name="product_type">
                                <option value="0" selected>Chọn loại cho sản phẩm</option>
                                <?php
                                $categorys = $category->get_all_type();
                                if ($categorys) {
                                    while ($result = $categorys->fetch_assoc()) {
                                        echo "<option value='{$result['id_loai']}'>{$result['tenLoai']}</option>";
                                    }
                                }
                                ?>
                            </select>
                            <span class="error" id="errorloaisanpham_add"></span>
                        </div>
                        <div class="form-group">
                            <label for="product_brand_add">Thương hiệu:</label>
                            <select id="product_brand_add" name="product_brand">
                                <option value="0" selected>Chọn thương hiệu</option>
                                <?php
                                $brList = $br->show();
                                if ($brList && $brList->num_rows > 0) {
                                    while ($result = $brList->fetch_assoc()) {
                                        echo "<option value='{$result['id_thuonghieu']}'>{$result['tenThuongHieu']}</option>";
                                    }
                                }
                                ?>
                            </select>
                            <span class="error" id="errorthuonghieu_add"></span>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="cancel">Hủy</button>
                    <input type="submit" name="submit" value="Lưu lại" class="submit-btn" />
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Sửa Sản Phẩm -->
    <div id="edit-modal" class="modal" style="display: none;">
        <div class="modal-content" style="margin: auto;">
            <span class="close">×</span>
            <h2>Sửa sản phẩm</h2>
            <div class="form" id="editForm">
                <input type="hidden" id="product_id_edit" name="product_id">
                <div class="form-container">
                    <div class="form-column">
                        <div class="form-group">
                            <label for="product_name_edit">Tên sản phẩm:</label>
                            <input type="text" id="product_name_edit" name="product_name" placeholder="Nhập tên sản phẩm..." />
                            <span class="error" id="errorten_edit"></span>
                        </div>
                        <div class="form-group">
                            <label for="main_image_edit">Ảnh chính:</label>
                            <input type="file" id="main_image_edit" name="main_image" accept="image/*" onchange="previewMainImage(this, 'main-image-preview-edit')" />
                            <div id="main-image-preview-edit" class="image-preview-container"></div>
                            <span class="error" id="erroranh_edit"></span>
                        </div>
                        <div class="form-group">
                            <label for="product_images_edit">Ảnh phụ:</label>
                            <button type="button" id="selectImagesBtnEdit" onclick="document.getElementById('product_images_edit').click();">Chọn ảnh phụ</button>
                            <input type="file" id="product_images_edit" accept="image/*" style="display: none;" onchange="previewImageEdit(this)" />
                            <div id="image-preview-edit" class="image-preview-container"></div>
                            <div id="hidden-images-edit"></div> <!-- Khu vực chứa input ẩn -->
                            <span class="error" id="erroranhphu_edit"></span>
                        </div>
                    </div>
                    <div class="form-column">
                        <div class="form-group">
                            <label for="desc_edit">Mô tả:</label>
                            <textarea name="product_desc" id="desc_edit" placeholder="Nhập mô tả sản phẩm..."></textarea>
                            <span class="error" id="errormota_edit"></span>
                        </div>
                        <div class="form-group">
                            <label for="product_type_edit">Loại sản phẩm:</label>
                            <select id="product_type_edit" name="product_type">
                                <option value="0" selected>Chọn loại cho sản phẩm</option>
                                <?php
                                $categorys = $category->get_all_type();
                                if ($categorys) {
                                    while ($result = $categorys->fetch_assoc()) {
                                        echo "<option value='{$result['id_loai']}'>{$result['tenLoai']}</option>";
                                    }
                                }
                                ?>
                            </select>
                            <span class="error" id="errorloaisanpham_edit"></span>
                        </div>
                        <div class="form-group">
                            <label for="product_brand_edit">Thương hiệu:</label>
                            <select id="product_brand_edit" name="product_brand">
                                <option value="0" selected>Chọn thương hiệu</option>
                                <?php
                                $brList = $br->show();
                                if ($brList && $brList->num_rows > 0) {
                                    while ($result = $brList->fetch_assoc()) {
                                        echo "<option value='{$result['id_thuonghieu']}'>{$result['tenThuongHieu']}</option>";
                                    }
                                }
                                ?>
                            </select>
                            <span class="error" id="errorthuonghieu_edit"></span>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="cancel">Hủy</button>
                    <input type="submit" name="submit" value="Lưu lại" id="submit-edit-btn" onclick="submitEdit()">
                </div>
            </div>
        </div>
    </div>

    <!-- Thông số -->
    <div id="specModal" class="modal-spec">
        <div class="modal-content-spec">
            <span class="close-spec">×</span>
            <h2>Quản lý thông số sản phẩm</h2>
            <form id="specForm" method="POST" action="">
                <input type="hidden" name="maSanPham" id="maSanPham">
                <div class="spec-input">
                    <label>Loại dây</label>
                    <input type="text" name="loaiDay">
                </div>
                <div class="spec-input">
                    <label>Pin</label>
                    <input type="text" name="pin">
                </div>
                <div class="spec-input">
                    <label>Mặt kính</label>
                    <input type="text" name="matKinh">
                </div>
                <div class="spec-input">
                    <label>Chống nước</label>
                    <input type="text" name="chongNuoc">
                </div>
                <div class="spec-input">
                    <label>Chức năng</label>
                    <textarea name="chucNang"></textarea>
                </div>
                <div class="button-thongso">
                    <button type="submit">Lưu thông số</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="container">
        <h2>Quản lý sản phẩm</h2>
        <div class="container">
            <div class="search_product">
                <form method="POST" class="search-input" >
                    <input type="text" name="searchdata" id="searchdata" placeholder="Tìm kiếm theo tên sản phẩm hoặc loại, thương hiệu" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" name="Search"><i class="fa fa-search" ></i> Tìm kiếm</button>
                </form>
            </div>
            <button class="btn-add" id="openAddModal"><i class="fa-solid fa-plus"></i> Thêm</button>
        </div>
        <div class="table-container">
            <table class="table_slider">
                <thead>
                    <tr>
                        <th style="width: 5%;">M.SP</th>
                        <th style="width: 25%;">Tên sản phẩm</th>
                        <th style="width: 20%;">Loại sản phẩm</th>
                        <th style="width: 15%;">Thương hiệu</th>
                        <th style="width: 15%;">Ảnh</th>
                        <th style="width: 15%;">Trạng thái</th>
                        <th style="width: 5%;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Kiểm tra nếu có kết quả tìm kiếm
                    if ($resultsearch !== null && !empty($resultsearch)) {
                        $i = 0;
                        foreach ($resultsearch as $result) {
                            $i++;
                            // Lấy ảnh phụ
                            $subImagesResult = $pr->getSubImages($result['maSanPham']);
                            $subImages = [];
                            if ($subImagesResult) {
                                while ($subRow = $subImagesResult->fetch_assoc()) {
                                    $subImages[] = $subRow['hinhAnh'];
                                }
                            }
                            $subImagesStr = implode(',', $subImages);

                            echo "<tr class='table_row'>";
                            echo "<td>{$i}</td>";
                            echo "<td>" . htmlspecialchars($result['tenSanPham']) . "</td>";
                            echo "<td>" . htmlspecialchars($result['tenLoai']) . "</td>";
                            echo "<td>" . htmlspecialchars($result['tenThuongHieu']) . "</td>";
                            echo "<td><img src='" . htmlspecialchars($result['hinhAnh']) . "' alt='' class='product_image' style='max-width: 100px;'></td>";
                            echo "<td>" . ($result['trangthai'] == 1 ? "Còn kinh doanh" : "Ngừng kinh doanh") . "</td>";
                            echo '<td class="btn-container">
                                    <div class="btn-action btn-edit"
                                        data-id="' . htmlspecialchars($result['maSanPham']) . '">
                                        <i class="fa-solid fa-pen"></i>
                                    </div>
                                    <div class="btn-action btn-spec" data-id="' . htmlspecialchars($result['maSanPham']) . '" title="Quản lý thông số">
                                        <i class="fa-solid fa-cog"></i>
                                    </div>
                                    <form action="" method="POST" class="status-form">
                                        <input type="hidden" name="id" value="' . htmlspecialchars($result['maSanPham']) . '">
                                        <input type="hidden" name="trangThai" value="' . ($result['trangthai'] == 1 ? 1 : 0) . '">
                                        <button style="background-color:green" type="submit" class="btn-action btn-status" title="Đổi trạng thái">
                                            <i class="fa-solid fa-rotate"></i>
                                        </button>
                                    </form>
                                </td>';
                            echo "</tr>";
                        }
                    } else {
                        // Nếu không có tìm kiếm hoặc không có kết quả, hiển thị toàn bộ sản phẩm
                        $prList = $pr->show();
                        if ($prList && $prList->num_rows > 0) {
                            $i = 0;
                            while ($result = $prList->fetch_assoc()) {
                                $i++;
                                // Lấy ảnh phụ
                                $subImagesResult = $pr->getSubImages($result['maSanPham']);
                                $subImages = [];
                                if ($subImagesResult) {
                                    while ($subRow = $subImagesResult->fetch_assoc()) {
                                        $subImages[] = $subRow['hinhAnh'];
                                    }
                                }
                                $subImagesStr = implode(',', $subImages);

                                echo "<tr class='table_row'>";
                                echo "<td>{$i}</td>";
                                echo "<td>" . htmlspecialchars($result['tenSanPham']) . "</td>";
                                echo "<td>" . htmlspecialchars($result['tenLoai']) . "</td>";
                                echo "<td>" . htmlspecialchars($result['tenThuongHieu']) . "</td>";
                                echo "<td><img src='" . htmlspecialchars($result['hinhAnh']) . "' alt='' class='product_image' style='max-width: 100px;'></td>";
                                echo "<td>" . ($result['trangthai'] == 1 ? "Còn kinh doanh" : "Ngừng kinh doanh") . "</td>";
                                echo '<td class="btn-container">
                                        <div class="btn-action btn-edit"
                                            data-id="' . htmlspecialchars($result['maSanPham']) . '">
                                            <i class="fa-solid fa-pen"></i>
                                        </div>
                                        <div class="btn-action btn-spec" data-id="' . htmlspecialchars($result['maSanPham']) . '" title="Quản lý thông số">
                                            <i class="fa-solid fa-cog"></i>
                                        </div>
                                        <form action="" method="POST" class="status-form">
                                            <input type="hidden" name="id" value="' . htmlspecialchars($result['maSanPham']) . '">
                                            <input type="hidden" name="trangThai" value="' . ($result['trangthai'] == 1 ? 1 : 0) . '">
                                            <button style="background-color:green" type="submit" class="btn-action btn-status" title="Đổi trạng thái">
                                                <i class="fa-solid fa-rotate"></i>
                                            </button>
                                        </form>
                                    </td>';
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>Không có dữ liệu sản phẩm</td></tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let selectedFilesAdd = [];
        let selectedFilesEdit = [];
        let existingImagesEdit = [];

        document.querySelectorAll('.btn-action.btn-edit').forEach(element => {
            element.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                
                document.getElementById("edit-modal").style.display = "block";

                editProduct(id);
            });
        });

        function previewMainImage(input, previewId) {
            const previewContainer = document.getElementById(previewId);
            previewContainer.innerHTML = '';
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewImageEdit(input) {
            const previewContainer = document.getElementById('image-preview-edit');
            const hiddenContainer = document.getElementById('hidden-images-edit');
            const maxImages = 3;

            const currentImageCount = previewContainer.querySelectorAll('.image-preview-wrapper').length;
            const newImageCount = input.files.length;

            Array.from(input.files).forEach(file => {
                selectedFilesAdd.push(file);

                const reader = new FileReader();
                reader.onload = function (e) {
                    // Tạo wrapper
                    const wrapper = document.createElement('div');
                    wrapper.className = 'image-preview-wrapper';
                    wrapper.style.position = 'relative';
                    wrapper.style.display = 'inline-block';
                    wrapper.style.margin = '5px';

                    // Ảnh
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    img.style.width = '80px';
                    img.style.height = '80px';
                    img.style.objectFit = 'cover';
                    img.style.border = '1px solid #ccc';
                    img.style.borderRadius = '4px';

                    // Nút xoá
                    const removeBtn = document.createElement('button');
                    removeBtn.innerHTML = '×';
                    removeBtn.type = 'button';
                    removeBtn.style.position = 'absolute';
                    removeBtn.style.top = '0';
                    removeBtn.style.right = '0';
                    removeBtn.style.background = 'red';
                    removeBtn.style.color = 'white';
                    removeBtn.style.border = 'none';
                    removeBtn.style.borderRadius = '0 0 0 5px';
                    removeBtn.style.cursor = 'pointer';
                    removeBtn.style.padding = '2px 6px';
                    removeBtn.style.fontSize = '16px';

                    removeBtn.onclick = function () {
                        wrapper.remove();
                        const index = selectedFilesEdit.indexOf(file);
                        if (index > -1) {
                            selectedFilesEdit.splice(index, 1);
                        }
                        updateHiddenInputs(hiddenContainer, selectedFilesEdit);
                    };

                    // Gắn phần tử vào DOM
                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);
                    previewContainer.appendChild(wrapper);

                    updateHiddenInputs(hiddenContainer, selectedFilesEdit);
                };
                reader.readAsDataURL(file);
            });

            input.value = '';
        }

        function previewImages(input, previewId) {
            const previewContainer = document.getElementById(previewId);
            const hiddenContainer = document.getElementById(previewId.includes('add') ? 'hidden-images-add' : 'hidden-images-edit');
            const selectedFiles = previewId.includes('add') ? selectedFilesAdd : selectedFilesEdit;
            const currentImageCount = previewContainer.querySelectorAll('img').length;
            const newImageCount = input.files.length;
            const totalImages = currentImageCount + newImageCount;

            for (let i = 0; i < input.files.length; i++) {
                const file = input.files[i];
                selectedFiles.push(file);

                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgWrapper = document.createElement('div');
                    imgWrapper.style.display = 'inline-block';
                    imgWrapper.style.position = 'relative';

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    img.style.maxWidth = '100px';
                    img.style.margin = '5px';

                    const removeBtn = document.createElement('button');
                    removeBtn.innerHTML = '×';
                    removeBtn.style.position = 'absolute';
                    removeBtn.style.top = '0';
                    removeBtn.style.right = '0';
                    removeBtn.style.background = 'red';
                    removeBtn.style.color = 'white';
                    removeBtn.style.border = 'none';
                    removeBtn.style.cursor = 'pointer';
                    removeBtn.onclick = function() {
                        imgWrapper.remove();
                        const index = selectedFiles.indexOf(file);
                        if (index > -1) {
                            selectedFiles.splice(index, 1);
                        }
                        updateHiddenInputs(hiddenContainer, selectedFiles);
                    };

                    imgWrapper.appendChild(img);
                    imgWrapper.appendChild(removeBtn);
                    previewContainer.appendChild(imgWrapper);

                    updateHiddenInputs(hiddenContainer, selectedFiles);
                };
                reader.readAsDataURL(file);
            }

            input.value = '';
        }

        function updateHiddenInputs(container, files) {
            container.innerHTML = '';
            files.forEach((file, index) => {
                const input = document.createElement('input');
                input.type = 'file';
                input.name = 'product_images[]';
                input.style.display = 'none';
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;
                container.appendChild(input);
            });
        }

        function validateForm(formId) {
            const form = document.getElementById(formId);
            let isValid = true;
            const suffix = formId === 'addForm' ? '_add' : '_edit';
            const isEditForm = formId === 'editForm';

            const fields = [
                { id: `product_name${suffix}`, errorId: `errorten${suffix}`, message: 'Vui lòng nhập tên sản phẩm', required: true },
                { id: `main_image${suffix}`, errorId: `erroranh${suffix}`, message: 'Vui lòng chọn ảnh chính', checkFiles: true, required: !isEditForm },
                { id: `desc${suffix}`, errorId: `errormota${suffix}`, message: 'Vui lòng nhập mô tả', required: true },
                { id: `product_type${suffix}`, errorId: `errorloaisanpham${suffix}`, message: 'Vui lòng chọn loại sản phẩm', checkValue: '0', required: true },
                { id: `product_brand${suffix}`, errorId: `errorthuonghieu${suffix}`, message: 'Vui lòng chọn thương hiệu', checkValue: '0', required: true }
            ];

            fields.forEach(field => {
                const input = document.getElementById(field.id);
                const error = document.getElementById(field.errorId);
                const value = field.checkFiles ? input.files.length : input.value.trim();

                let hasError = false;

                if (field.required) {
                    if (field.checkFiles) {
                        const preview = document.getElementById(field.id.replace('main_image', 'main-image-preview'));
                        if (value === 0 && (!preview || !preview.innerHTML)) {
                            hasError = true;
                        }
                    } else if (value === '' || (field.checkValue && value === field.checkValue)) {
                        hasError = true;
                    }
                }

                if (hasError) {
                    error.textContent = field.message;
                    error.style.display = 'block';
                    isValid = false;
                } else {
                    error.style.display = 'none';
                }
            });

            return isValid;
        }

        function resetForm(modalId, formId) {
            const form = document.getElementById(formId);
            form.reset();
            const previewAdd = document.getElementById('image-preview-add');
            const previewEdit = document.getElementById('image-preview-edit');
            const hiddenAdd = document.getElementById('hidden-images-add');
            const hiddenEdit = document.getElementById('hidden-images-edit');
            if (modalId === 'add-modal' && previewAdd) {
                previewAdd.innerHTML = '';
                hiddenAdd.innerHTML = '';
                selectedFilesAdd = [];
            }
            if (modalId === 'edit-modal' && previewEdit) {
                previewEdit.innerHTML = '';
                hiddenEdit.innerHTML = '';
                selectedFilesEdit = [];
                existingImagesEdit = [];
            }
            document.getElementById('main-image-preview-' + (modalId === 'add-modal' ? 'add' : 'edit')).innerHTML = '';
            document.querySelectorAll(`#${modalId} .error`).forEach(error => error.style.display = 'none');
        }

        const changeImage = (image) => {
            const index = existingImagesEdit.indexOf(image);
            if (index > -1) {
                existingImagesEdit.splice(index, 1);
            }
            const img = document.querySelector(`img[src$="${image}"]`);
            if (img) {
                const wrapper = img.parentElement;
                wrapper.remove();
                selectedFilesEdit.push(image);
            }
        }

        const submitEdit = () => {
            const formData = new FormData();

            // Lấy dữ liệu text input
            const productId = document.getElementById('product_id_edit').value;
            const product_desc = document.getElementById('desc_edit').value;
            const productType = document.getElementById('product_type_edit').value;
            const productBrand = document.getElementById('product_brand_edit').value;

            const productName = document.getElementById('product_name_edit').value;
            const mainImageInput = document.getElementById('main_image_edit');

            formData.append('product_id', productId);
            formData.append('product_name', productName);
            formData.append('product_desc', product_desc);
            formData.append('product_type', productType);
            formData.append('product_brand', productBrand);

            if (mainImageInput.files.length > 0) {
                formData.append('main_image', mainImageInput.files[0]);
            }

            
            selectedFilesEdit.forEach((file, index) => {
                formData.append('sub_img_delete[]', file);
            });

            
            selectedFilesAdd.forEach((file, index) => {
                formData.append('sub_img_add[]', file);
            });
        
        
            fetch('/watch_store/admin/data/update_product.php', {
                method: 'POST',
                body: formData,
            })
            .then(res => res.json())
            .then(data => {
                alert('Cập nhật thành công!');
                document.getElementById('edit-modal').style.display = 'none';
                window.location.reload();
            })
            .catch(err => {
                console.error('Lỗi:', err.message);
            });
        }

        function editProduct(id) {
            if (id) {
                $.ajax({
                url: "../admin/data/getProductById.php", 
                method: "GET",
                data: { id: id },
                dataType: "json",
                success: function (data) {
                    if (data) {
                        $("#product_id_edit").val(data.maSanPham); 
                        $("#product_name_edit").val(data.tenSanPham); 
                        $("#desc_edit").val(data.mota);
                        $("#product_type_edit").val(data.id_loai);
                        $("#product_brand_edit").val(data.id_thuonghieu); 

                        // Hiển thị ảnh chính nếu có
                        if (data.hinhAnh) {
                            $("#main-image-preview-edit").html(
                                `<img src="${data.hinhAnh}" width="100" />`
                            );
                        }

                        if (data.anhphu) {
                            existingImagesEdit = data.anhphu;
                            let imagesHtml = "";
                            console.log(existingImagesEdit)
                            if(existingImagesEdit.length >= 1) {
                                existingImagesEdit.forEach((img) => {
                                let result = img.startsWith('../') ? img.substring(3) : img
                                return imagesHtml += `<div style="position: relative; display: inline-block; width: 80px; height: 80px; margin: 5px;">
                                                    <img
                                                        src="${result}"
                                                        style="width: 100%; height: 100%; object-fit: cover; display: block;"
                                                    />
                                                    <div  class="remove-image"
                                                        style="
                                                        position: absolute;
                                                        top: 0;
                                                        right: 0;
                                                        background: rgba(255, 0, 0, 0.8);
                                                        color: white;
                                                        padding: 2px 6px;
                                                        cursor: pointer;
                                                        font-weight: bold;
                                                        border-radius: 0 0 0 5px;
                                                        z-index: 1;
                                                        "
                                                        onclick="changeImage('${result}')"
                                                    >
                                                        ✖
                                                    </div>
                                                    </div>
                                                    `;
                                });
                            $("#image-preview-edit").html(imagesHtml);
                            }
                        }
                        $("#editForm").show();
                    }
                },
                error: function () {
                    alert("Lỗi khi lấy dữ liệu sản phẩm!");
                },
            });
            
            } else {
                alert("ID sản phẩm không hợp lệ!");
            }
        }

        document.querySelectorAll('.remove-image').forEach(element => {
            element.addEventListener('click', function() {
                console.log('remove-image clicked');
                const img = this.parentElement.querySelector('img');
                const src = img.getAttribute('src');
                const filename = src.split('/').pop();
                
                const index = existingImagesEdit.indexOf(filename);
                if (index > -1) {
                    existingImagesEdit.splice(index, 1);
                }
                this.parentElement.remove();
            });
        })

        document.addEventListener('DOMContentLoaded', () => {
            const modals = {
                'add-modal': { openBtn: 'openAddModal', formId: 'addForm' },
                'edit-modal': { formId: 'editForm' }
            };

            Object.keys(modals).forEach(modalId => {
                const modal = document.getElementById(modalId);
                const form = document.getElementById(modals[modalId].formId);
                const closeBtn = modal.querySelector('.close');
                const cancelBtn = modal.querySelector('.cancel');

                if (modals[modalId].openBtn) {
                    document.getElementById(modals[modalId].openBtn).addEventListener('click', () => {
                        resetForm(modalId, modals[modalId].formId);
                        modal.style.display = 'flex';
                    });
                }

                closeBtn.addEventListener('click', () => {
                    modal.style.display = 'none';
                    resetForm(modalId, modals[modalId].formId);
                });

                cancelBtn.addEventListener('click', () => {
                    modal.style.display = 'none';
                    resetForm(modalId, modals[modalId].formId);
                });

                window.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        modal.style.display = 'none';
                        resetForm(modalId, modals[modalId].formId);
                    }
                });

                form.addEventListener('submit', (event) => {
                    if (!validateForm(modals[modalId].formId)) {
                        event.preventDefault();
                    }
                });
            });

            
        });
        // Đảm bảo modal ẩn khi tải trang
        document.getElementById('specModal').style.display = 'none';
        const closeBtn = document.querySelector(".modal-spec .close");

        // Mở modal khi nhấp nút "Quản lý thông số"
        document.querySelectorAll('.btn-spec').forEach(btn => {
            btn.addEventListener('click', () => {
                const maSanPham = btn.getAttribute('data-id');
                console.log('maSanPham:', maSanPham); // Thêm log để kiểm tra
                document.getElementById('maSanPham').value = maSanPham;
                document.getElementById('specModal').style.display = 'block';
                loadSpecs(maSanPham);
            });
        });

        // Đóng modal
        document.querySelector('.close-spec').addEventListener('click', () => {
            document.getElementById('specModal').style.display = 'none';
        });
        window.addEventListener('click', (e) => {
            if (e.target === document.getElementById('specModal')) {
                document.getElementById('specModal').style.display = 'none';
            }
        });

        // Load thông số hiện có
        function loadSpecs(maSanPham) {
            if (!maSanPham) {
                console.error('Thiếu maSanPham khi gọi loadSpecs');
                return;
            }
            fetch(`/watch_store/admin/data/getProductThongSo.php?maSanPham=${maSanPham}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error('Lỗi từ server:', data.error);
                        return;
                    }
                    console.log('Dữ liệu từ server:', data); // Thêm log để kiểm tra dữ liệu
                    document.querySelector('input[name="loaiDay"]').value = data.loaiDay || '';
                    document.querySelector('input[name="pin"]').value = data.pin || '';
                    document.querySelector('input[name="matKinh"]').value = data.matKinh || '';
                    document.querySelector('input[name="chongNuoc"]').value = data.chongNuoc || '';
                    document.querySelector('textarea[name="chucNang"]').value = data.chucNang || '';
                })
                .catch(error => {
                    console.error('Lỗi khi load thông số:', error);
                });
        }
    </script>
</body>
</html>