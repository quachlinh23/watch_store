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
}

// Xử lý sửa sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $update_data = [
        'product_name' => $_POST['product_name'],
        'product_desc' => $_POST['product_desc'],
        'product_type' => $_POST['product_type'],
        'product_brand' => $_POST['product_brand'],
        'delete_sub_images' => $_POST['delete_sub_images'] // Danh sách ảnh phụ cần xóa
    ];
    $update_product = $pr->update($product_id, $update_data, $_FILES);
}

// Xử lý đổi trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['trangThai'])) {
    $id = intval($_POST['id']);
    $status = intval($_POST['trangThai']);
    $pr->updateStatus($id, $status);
    echo "<script>window.location.href = window.location.pathname;</script>";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .image-preview-container img { max-width: 100px; margin: 5px; }
        .image-placeholder { width: 100px; height: 100px; background: #f0f0f0; margin: 5px; display: inline-block; }
        .error { color: red; font-size: 12px; display: none; }
        #selectImagesBtn, #selectImagesBtnEdit { padding: 5px 10px; background: #007bff; color: white; border: none; cursor: pointer; }
        #selectImagesBtn:hover, #selectImagesBtnEdit:hover { background: #0056b3; }
    </style>
</head>
<body>
    <!-- Modal Thêm Sản Phẩm -->
    <div id="add-modal" class="modal" style="display: none;">
        <div class="modal-content">
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
                            <label for="product_images_add">Ảnh phụ (tối đa 3):</label>
                            <button type="button" id="selectImagesBtn" onclick="document.getElementById('product_images_add').click();">Chọn ảnh phụ</button>
                            <input type="file" id="product_images_add" accept="image/*" style="display: none;" onchange="previewImages(this, 'image-preview-add')" />
                            <div id="image-preview-add" class="image-preview-container"></div>
                            <div id="hidden-images-add"></div>
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
        <div class="modal-content">
            <span class="close">×</span>
            <h2>Sửa sản phẩm</h2>
            <form method="post" enctype="multipart/form-data" class="form" id="editForm">
                <input type="hidden" id="product_id_edit" name="product_id">
                <input type="hidden" id="delete_sub_images" name="delete_sub_images" value="[]">
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
                            <label for="product_images_edit">Ảnh phụ (tối đa 3):</label>
                            <button type="button" id="selectImagesBtnEdit" onclick="document.getElementById('product_images_edit').click();">Chọn ảnh phụ</button>
                            <input type="file" id="product_images_edit" accept="image/*" style="display: none;" onchange="previewImages(this, 'image-preview-edit')" />
                            <div id="image-preview-edit" class="image-preview-container"></div>
                            <div id="hidden-images-edit"></div>
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
                    <input type="submit" name="submit" value="Lưu lại" class="submit-btn" />
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="container">
        <h2>Quản lý sản phẩm</h2>
        <button class="btn-add" id="openAddModal"><i class="fa-solid fa-plus"></i> Thêm</button>
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
                    $prList = $pr->show();
                    if ($prList && $prList->num_rows > 0) {
                        $i = 0;
                        while ($result = $prList->fetch_assoc()) {
                            $i++;
                            $sub_images = $pr->getSubImages($result['maSanPham']);
                            $sub_images_array = [];
                            if ($sub_images) {
                                while ($sub = $sub_images->fetch_assoc()) {
                                    $sub_images_array[] = $sub['hinhAnh'];
                                }
                            }
                            echo "<tr class='table_row'>";
                            echo "<td>{$i}</td>";
                            echo "<td>{$result['tenSanPham']}</td>";
                            echo "<td>{$result['tenLoai']}</td>";
                            echo "<td>{$result['tenThuongHieu']}</td>";
                            echo "<td><img src='{$result['hinhAnh']}' alt='' class='product_image' style='max-width: 100px;'></td>";
                            echo "<td>" . ($result['trangthai'] == 1 ? "Còn kinh doanh" : "Ngừng kinh doanh") . "</td>";
                            echo '<td class="btn-container">
                                    <a href="javascript:void(0)" title="Sửa" class="btn-action btn-edit" 
                                        data-id="' . $result['maSanPham'] . '"
                                        data-name="' . htmlspecialchars($result['tenSanPham']) . '"
                                        data-desc="' . htmlspecialchars($result['mota']) . '"
                                        data-type="' . $result['id_loai'] . '"
                                        data-brand="' . $result['id_thuonghieu'] . '"
                                        data-main-image="' . $result['hinhAnh'] . '"
                                        data-sub-images="' . htmlspecialchars(json_encode($sub_images_array)) . '">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="" method="POST" class="status-form">
                                        <input type="hidden" name="id" value="' . $result['maSanPham'] . '">
                                        <input type="hidden" name="trangThai" value="' . $result['trangthai'] . '">
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
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        let selectedFilesAdd = [];
        let selectedFilesEdit = [];
        let deletedSubImages = [];

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

        function previewImages(input, previewId) {
            const previewContainer = document.getElementById(previewId);
            const hiddenContainer = document.getElementById(previewId.includes('add') ? 'hidden-images-add' : 'hidden-images-edit');
            const files = input.files;
            const selectedFiles = previewId.includes('add') ? selectedFilesAdd : selectedFilesEdit;

            if (files) {
                const totalImages = selectedFiles.length + files.length;
                if (totalImages > 3) {
                    // alert('Chỉ được chọn tối đa 3 ảnh phụ! Bạn đã chọn ' + selectedFiles.length + ' ảnh.');
                    input.value = '';
                    return;
                }

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
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

        function loadExistingSubImages(subImages, previewId, hiddenId) {
            const previewContainer = document.getElementById(previewId);
            const hiddenContainer = document.getElementById(hiddenId);
            const selectedFiles = previewId.includes('add') ? selectedFilesAdd : selectedFilesEdit;
            previewContainer.innerHTML = '';
            selectedFiles.length = 0;
            deletedSubImages = [];

            if (subImages && subImages.length > 0) {
                subImages.forEach((imgUrl, index) => {
                    const imgWrapper = document.createElement('div');
                    imgWrapper.style.display = 'inline-block';
                    imgWrapper.style.position = 'relative';

                    const img = document.createElement('img');
                    img.src = imgUrl;
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
                        deletedSubImages.push(imgUrl);
                        document.getElementById('delete_sub_images').value = JSON.stringify(deletedSubImages);
                        const index = selectedFiles.indexOf(imgUrl);
                        if (index > -1) {
                            selectedFiles.splice(index, 1);
                        }
                        updateHiddenInputs(hiddenContainer, selectedFiles);
                    };

                    imgWrapper.appendChild(img);
                    imgWrapper.appendChild(removeBtn);
                    previewContainer.appendChild(imgWrapper);

                    fetch(imgUrl)
                        .then(res => res.blob())
                        .then(blob => {
                            const file = new File([blob], `existing_image_${index}.jpg`, { type: blob.type });
                            selectedFiles.push(file);
                            updateHiddenInputs(hiddenContainer, selectedFiles);
                        });
                });
            }
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
                document.getElementById('delete_sub_images').value = '[]';
                deletedSubImages = [];
            }
            document.getElementById('main-image-preview-' + (modalId === 'add-modal' ? 'add' : 'edit')).innerHTML = '';
            document.querySelectorAll(`#${modalId} .error`).forEach(error => error.style.display = 'none');
        }

        function editProduct(id) {
            const button = document.querySelector(`.btn-edit[data-id="${id}"]`);
            if (!button) {
                alert('Không tìm thấy sản phẩm với ID: ' + id);
                return;
            }

            const data = {
                maSanPham: id,
                tenSanPham: button.getAttribute('data-name'),
                moTa: button.getAttribute('data-desc'),
                id_loai: button.getAttribute('data-type'),
                id_thuonghieu: button.getAttribute('data-brand'),
                hinhAnh: button.getAttribute('data-main-image'),
                anhPhu: button.getAttribute('data-sub-images') ? JSON.parse(button.getAttribute('data-sub-images')) : []
            };

            document.getElementById('product_id_edit').value = data.maSanPham;
            document.getElementById('product_name_edit').value = data.tenSanPham || '';
            document.getElementById('desc_edit').value = data.moTa || '';
            document.getElementById('product_type_edit').value = data.id_loai || '0';
            document.getElementById('product_brand_edit').value = data.id_thuonghieu || '0';
            document.getElementById('main-image-preview-edit').innerHTML = data.hinhAnh ? `<img src="${data.hinhAnh}" class="image-preview">` : '';
            
            loadExistingSubImages(data.anhPhu, 'image-preview-edit', 'hidden-images-edit');
            
            document.getElementById('edit-modal').style.display = 'flex';
        }

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

            document.querySelectorAll('.btn-edit').forEach(button => {
                button.addEventListener('click', (e) => {
                    const id = button.getAttribute('data-id');
                    if (id) editProduct(parseInt(id));
                    e.preventDefault();
                });
            });
        });
    </script>
</body>
</html>