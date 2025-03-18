<?php
    include '../class/employee.php';
	$employ = new employee();

	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])){
        $id = intval($_POST['idtaikhoan']);
        $scroll = intval($_POST['permissionValue']);
        $result = $employ->updateRoll($id,$scroll);
	}

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addAcount'])){
        $name = $_POST['user_name'];
        $email = $_POST['email'];
        $username = $_POST['username'];

        $result = $employ->addEmployee($name,$email,$username);

	}
    $resultusername = $employ->createAcount();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['trangThai'])) {
        $id = intval($_POST['id']);
        $status = intval($_POST['trangThai']);
        $update_status = $employ->updateStatus($id, $status);
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
    <script src="js/employee.js"></script>
    <script >
        document.addEventListener("DOMContentLoaded", function () {
            var modal = document.getElementById("modal");
            var closeBtn = document.querySelector(".clos");
            var cancelBtn = document.querySelector(".canc");
            var errorTen = document.getElementById("errorten");
            var errorImage = document.getElementById("erroranh");
            let slideName = document.getElementById("slide_name");
            let imageInput = document.getElementById("image");

            document.getElementById("openModal").addEventListener("click", function () {
                modal.style.display = "flex";
            });

            closeBtn.addEventListener("click", function () {
                modal.style.display = "none";
            });

            cancelBtn.addEventListener("click", function () {
                modal.style.display = "none";
                errorTen.style.display = "none";
                errorImage.style.display = "none";
                document.getElementById("slide_name").value = "";
                document.getElementById("image").value = "";
            });

            window.addEventListener("click", function (event) {
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("formAcount").addEventListener("submit", function (event) {
                let isValid = true;

                let hoten = document.getElementById("user_name");
                let errorTen = document.getElementById("errorten");

                let email = document.getElementById("email");
                let erroremail = document.getElementById("erroremail");


                // Kiểm tra lỗi nhập họ tên
                if (hoten.value.trim() === "") {
                    errorTen.textContent = "Vui lòng nhập họ tên";
                    errorTen.style.display = "block";
                    isValid = false;
                } else {
                    errorTen.style.display = "none";
                }

                // Kiểm tra lỗi nhập email
                if (email.value.trim() === "") {
                    erroremail.textContent = "Vui lòng nhập email";
                    erroremail.style.display = "block";
                    isValid = false;
                } else {
                    erroremail.style.display = "none";
                }

                // Nếu có lỗi, ngăn form gửi đi
                if (!isValid) {
                    event.preventDefault();
                }
            });
        });

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
        <h2>Quản lý nhân viên</h2>
        <div class="search-container">
            <!-- Nút Thêm -->
            <button class="btn-add" id="openModal">
                <i class="fa-solid fa-plus"></i> Thêm
            </button>

            <!-- Form tìm kiếm -->
            <form method="POST" class="search-input">
                <input type="text" name="searchdata" id="searchdata" placeholder="Nhập tên, email hoặc số điện thoại..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" name="Search"><i class="fa fa-search"></i> Tìm kiếm</button>
            </form>
        </div>
        <!-- Bọc bảng trong div có thanh cuộn -->
        <div class="table-container">
            <table class="table_slider">
                <thead>
                    <tr>
                        <th style="width: 5%;">STT</th>
                        <th style="width: 18%;">Tên nhân viên</th>
                        <th style="width: 12%;">SĐT</th>
                        <th style="width: 18%;">Email</th>
                        <th style="width: 12%;">Tài khoản</th>
                        <th style="width: 12%;">Quyền</th>
                        <th style="width: 13%;">Trạng thái</th>
                        <th style="width: 10%;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if (!empty($search)) {
                            $employeelist = $employ->search($search);
                        } else {
                            $employeelist = $employ->getAllEmployees();
                        }

                        $i = 0;
                        if ($employeelist !== false && $employeelist->num_rows > 0) { // Kiểm tra $sdlist không phải false
                            while ($result = $employeelist->fetch_assoc()) {
                                $i++;
                    ?>
                    <tr>
                        <td><?php echo $i?></td>
                        <td><?php echo $result['tenNhanVien']?></td>
                        <td><?php echo $result['soDT']?></td>
                        <td><?php echo $result['email']?></td>
                        <td><?php echo $result['username']?></td>
                        <td><?php echo $result['quyen']?></td>
                        <td><?php echo ($result['trangthai'] == 1) ? "Còn làm" : "Nghỉ làm"; ?></td>
                        <td class="btn-container">
                            <form action="" method="POST">
                                <input type="hidden" name="id" value="<?php echo $result['id']; ?>">
                                <input type="hidden" name="trangThai" value="<?php echo ($result['trangthai'] == 1) ? 1 : 0; ?>">
                                <button type="submit" class="btn-action btn-edit" title="Đổi trạng thái">
                                    <i class="fa-solid fa-rotate"></i>
                                </button>
                            </form>
                                <a title="Phân quyền" href="#" class="btn-action btn-permission btn-delete" data-id="<?php echo $result['id']; ?>" data-quyen="<?php echo $result['quyen']; ?>">
                                    <i class="fa-solid fa-user-gear"></i>
                                </a>
                        </td>
                    </tr>
                    <?php
                            }
                        } else {
                            // Hiển thị thông báo khi không có kết quả hoặc truy vấn lỗi
                            echo '<tr><td colspan="8" style="text-align: center; padding: 20px;">';
                            if ($employeelist === false) {
                                echo "Không tìm thấy thương hiệu nào phù hợp với từ khóa '" . htmlspecialchars($search) . "'";
                            }
                            echo '</td></tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
        <!-- Phân quyền -->
        <div id="modalPermission" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Phân quyền</h2>
                <form method="post" class="form" id="formPermission">
                    <table>
                        <tr>
                            <td><input type="checkbox" class="permission" value="1"> Quản lý nhân viên</td>
                            <td><input type="checkbox" class="permission" value="2"> Quản lý khách hàng</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="permission" value="4"> Quản lý thương hiệu</td>
                            <td><input type="checkbox" class="permission" value="8"> Quản lý loại sản phẩm</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="permission" value="16"> Quản lý nhà cung cấp</td>
                            <td><input type="checkbox" class="permission" value="32"> Quản lý nhập hàng</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="permission" value="64"> Quản lý sản phẩm</td>
                            <td><input type="checkbox" class="permission" value="128"> Quản lý đơn hàng</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="permission" value="256"> Quản lý slider</td>
                            <td></td>
                        </tr>

                        <!-- Input ẩn để lưu tổng quyền -->
                        <input type="hidden" id="permissionValue" name="permissionValue" value="0">
                        <input type="hidden" id="idtaikhoan" name="idtaikhoan" value="">

                        <tr>
                            <td><button type="button" class="cancel">Hủy</button></td>
                            <td><input type="submit" name="submit" value="Lưu lại" /></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
        <!-- Thêm nhân viên -->
        <div id="modal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close clos">&times;</span>
                <h2>Thêm Nhân Viên</h2>
                <form method="post" enctype="multipart/form-data" class="form" id="formAcount">
                    <table>
                        <tr>
                            <td><label for="user_name">Họ và Tên:</label></td>
                            <td><input type="text" id="user_name" name="user_name" placeholder="Nhập tên..."/></td>
                            
                        </tr>
                        <tr>
                            <td colspan="2">
                                <span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="errorten">
                                    Vui lòng nhập tên nhân viên
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td><label for="Email">Email:</label></td>
                            <td><input type="text" id="email" name="email" placeholder="Nhập email..."/></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <span style="display: none; color: red; font-weight: bold; padding:10px 0;" id="erroremail">
                                    Vui lòng nhập email
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td><label for="username">Tài khoản:</label></td>
                            <td><input readonly type="text" id="username" name="username" value="<?php echo $resultusername?>"/></td>
                        </tr>

                        <tr>
                            <td><button type="button" class="cancel canc">Hủy</button></td>
                            <td><input type="submit" name="addAcount" value="Lưu lại" /></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
    <script src="js/employee.js"></script>
</body>
</html>