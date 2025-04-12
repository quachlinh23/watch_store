<?php
include_once "class/brand.php";
$brand = new brand();
include_once "class/customer_info.php";
$cus = new Customer();

session_start();
$check = true;
$id_cus = $_SESSION['customer_id'] ?? null;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Lấy dữ liệu thống kê cho từng năm
$statistical_data = [];
$years = [];
if ($id_cus) {
    $currentYear = date('Y');
    $startYear = $currentYear - 4;
    $years = range($startYear, $currentYear); // Tạo mảng năm động
    foreach ($years as $year) {
        $results = $cus->statistical($id_cus, $year); // Gọi từ $cart, không phải $cus
        $monthly_spending = array_fill(1, 12, 0); // Khởi tạo mảng 12 tháng với giá trị 0
        foreach ($results as $row) {
            $monthly_spending[(int)$row['thang']] = (float)$row['tongChi'];
        }
        $statistical_data[$year] = array_values($monthly_spending); // Chuyển thành mảng liên tục
    }
}

// Xử lý POST (giữ nguyên)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save-avt'])) {
    if (!isset($_SESSION['customer_id'])) {
        echo "<script>alert('Vui lòng đăng nhập để cập nhật ảnh đại diện!');</script>";
    } else {
        $target_dir = "admin/uploads/avt_user/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
        $target_file = $target_dir . "user_" . $id_cus . "_" . time() . "." . $file_extension;
        
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if (!in_array($file_extension, $allowed_types)) {
            echo "<script>alert('Chỉ chấp nhận file JPG, JPEG, PNG & GIF');</script>";
        } elseif ($_FILES["avatar"]["size"] > 5000000) {
            echo "<script>alert('File quá lớn, tối đa 5MB');</script>";
        } else {
            if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                $cus->updateAvatar($id_cus, $target_file);
                echo "<script>alert('Cập nhật ảnh đại diện thành công!'); window.location.href='profile.php';</script>";
            } else {
                echo "<script>alert('Có lỗi khi tải lên ảnh');</script>";
            }
        }
    }
}

$error = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'address' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Save'])) {
    $tenkhachhang = trim($_POST['HoTen'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $sodt = trim($_POST['sodt'] ?? '');
    $diachi = trim($_POST['DiaChi'] ?? '');

    $result = $cus->updateCustomerInfo($id_cus, $tenkhachhang, $diachi, $sodt, $email);
    if ($result === true) {
        echo "<script>alert('Cập nhật thông tin thành công!'); window.location.href='profile.php';</script>";
    } else {
        if ($result === "Email đã tồn tại!") {
            $error['email'] = $result;
        } elseif ($result === "Số điện thoại đã tồn tại!") {
            $error['phone'] = $result;
        } else {
            echo "<script>alert('Cập nhật thông tin thất bại: " . htmlspecialchars($result) . "');</script>";
        }
    }
}

if (isset($_POST['changepass'])) {
    $customer = new Customer();
    if (!isset($_SESSION['customer_id'])) {
        die("Lỗi: Không tìm thấy ID tài khoản trong session!");
    }
    $id_taikhoan = $_SESSION['customer_id'];
    $old_password = $_POST['matkhaucu'];
    $new_password = $_POST['matkhaumoi'];
    $confirm_password = $_POST['prematkhaumoi'];
    $result = $customer->changePassword($id_taikhoan, $old_password, $new_password, $confirm_password);
    
    if ($result === true) {
        echo "<script>window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('$result'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Store</title>
    <link rel="stylesheet" href="css/profileUser.css">
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        .error {
            color: red;
            font-weight: bold;
            display: block;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php include 'layout/header.php'; $cus = new Customer(); ?>
    <div class="content-profile">
    <div class="content-profile-left">
        <div class="profile-info">
            <h2 class="information">Thông Tin Cá Nhân</h2>
            <div class="content">
                <div class="profile-img">
                    <div class="img_us">
                        <?php
                        $avatar_path = $id_cus && $cus->getAvatar($id_cus) ? $cus->getAvatar($id_cus) : "images/bannercasio.png";
                        ?>
                        <img id="user-avatar" src="<?php echo htmlspecialchars($avatar_path); ?>" alt="Ảnh Người Dùng">
                        <button id="change_avt" class="change_avt">🖼️</button>
                        <form id="avatar-form" enctype="multipart/form-data" method="POST">
                            <input type="file" id="avatar-input" name="avatar" accept="image/*" style="display: none;">
                            <input type="hidden" id="avatar-path" name="avatar_path" value="">
                        </form>
                    </div>
                    <button id="save-avatar" class="save-avatar" style="display: none;" name="save-avt">Lưu Ảnh</button>
                </div>
                <?php
                if ($check && $id_cus) {
                    $result = $cus->getinforcustomerbyid($id_cus);
                    if ($result && $result->num_rows > 0) {
                        $info = $result->fetch_assoc();
                    } else {
                        echo "<p>Không tìm thấy thông tin khách hàng!</p>";
                        $info = ['tenKhachHang' => '', 'diaChi' => '', 'soDT' => '', 'email' => ''];
                    }
                ?>
                <div class="personal-details">
                    <p style="margin-bottom: 20px;"><strong>Họ và Tên: </strong><?php echo htmlspecialchars($info['tenKhachHang'] ?? ''); ?></p>
                    <p style="margin-bottom: 20px;"><strong>Địa Chỉ: </strong><?php echo htmlspecialchars($info['diaChi'] ?? ''); ?></p>
                    <p style="margin-bottom: 20px;"><strong>Số Điện Thoại: </strong><?php echo htmlspecialchars($info['soDT'] ?? ''); ?></p>
                    <p style="margin-bottom: 20px;"><strong>Email: </strong><?php echo htmlspecialchars($info['email'] ?? ''); ?></p>
                    <div class="btn_edit_info">
                        <button id="change_pass">Đổi Mật Khẩu</button>
                        <button id="editInfoBtn">Cập Nhật Thông Tin</button>
                    </div>

                    <!-- Modal (Form Cập Nhật Thông Tin) -->
                    <div id="updateModal" class="modal">
                        <div class="modal-content">
                            <span class="close">×</span>
                            <h2>Cập Nhật Thông Tin</h2>
                            <form id="updateForm" action="profile.php" method="POST">
                                <label for="name">Tên:</label>
                                <input type="text" id="name" name="HoTen" value="<?php echo htmlspecialchars($info['tenKhachHang'] ?? ''); ?>">
                                <span class="error" id="emptyname"><?php echo $error['name'] ?? ''; ?></span>
                                
                                <label for="email">Email:</label>
                                <input type="email" id="email" name="Email" value="<?php echo htmlspecialchars($info['email'] ?? ''); ?>">
                                <span class="error" id="emptyemail"><?php echo $error['email'] ?? ''; ?></span>
                                
                                <label for="phone">Số điện thoại:</label>
                                <input type="tel" id="phone" name="sodt" value="<?php echo htmlspecialchars($info['soDT'] ?? ''); ?>">
                                <span class="error" id="emptysodt"><?php echo $error['phone'] ?? ''; ?></span>

                                <label for="address">Địa chỉ:</label>
                                <input type="text" id="address" name="DiaChi" value="<?php echo htmlspecialchars($info['diaChi'] ?? ''); ?>">
                                <span class="error" id="emptyaddress"><?php echo $error['address'] ?? ''; ?></span>
                                
                                <div class="control">
                                    <button id="cancel" class="btn_cancle" type="button">Hủy Bỏ</button>
                                    <button id="save" type="submit" name="Save" class="btn_save">Cập Nhật</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Modal (Form Đổi Mật Khẩu) -->
                    <div id="updatepassword" class="modal">
                        <div class="modal-content">
                            <span class="close_1">×</span>
                            <h2 style="text-align: center; margin-bottom: 30px;">Đổi Mật Khẩu</h2>
                            <form id="updatePassForm" method="POST">
                                <label for="passwordold">Mật khẩu cũ:</label>
                                <input type="password" id="passwordold" name="matkhaucu" value="">
                                <span class="error" id="emptypassword">Mật khẩu cũ không được để trống</span>
                                
                                <label for="passwordnew">Mật khẩu mới:</label>
                                <input type="password" id="passwordnew" name="matkhaumoi" value="">
                                <span id="emptypasswordnew" class="error">Mật khẩu mới không được bỏ trống</span>
                                
                                <label for="prepasswordnew">Xác nhận mật khẩu mới:</label>
                                <input type="password" id="prepasswordnew" name="prematkhaumoi" value="">
                                <span id="emptyprepasswordnew" class="error">Xác nhận mật khẩu mới không được bỏ trống</span>
                                <span id="checkprepasswordnew" class="error">Xác nhận mật khẩu mới phải trùng với mật khẩu mới</span>

                                <div class="control">
                                    <button id="cancel_1" class="btn_cancle" type="button">Hủy</button>
                                    <button id="changepass" type="submit" name="changepass" class="btn_save">Lưu</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
                } else {
                    echo "<p>Vui lòng đăng nhập để xem thông tin cá nhân!</p>";
                }
                ?>
            </div>
        </div>
    </div>
    <div class="content-profile-right">
        <div class="content-right-top">
            <select id="yearSelector"></select>
            <button onclick="showYearlyStats()">Thống kê theo năm</button>
            <canvas id="yearlyChart" style="display: none;"></canvas>
        </div>
        <div class="content-right-bottom">
            <p class="total-spending">Tổng chi tiêu: <span id="totalSpending">0</span> VNĐ</p>
        </div>
    </div>
</div>
    <?php include 'layout/footer.php'; ?>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const changePassBtn = document.getElementById("change_pass");
        const changeInfoBtn = document.getElementById("editInfoBtn");
        const modalPass = document.getElementById("updatepassword");
        const modalInfo = document.getElementById("updateModal");
        const closeBtn = document.querySelector(".close_1");
        const cancelBtn = document.getElementById("cancel_1");
        const closBtn = document.querySelector(".close");
        const canclBtn = document.getElementById("cancel");

        const changeAvatarBtn = document.getElementById("change_avt");
        const avatarInput = document.getElementById("avatar-input");
        const avatarPathInput = document.getElementById("avatar-path");
        const userAvatar = document.getElementById("user-avatar");
        const saveAvatarBtn = document.getElementById("save-avatar");
        const avatarForm = document.getElementById("avatar-form");

        if (changePassBtn) {
            changePassBtn.addEventListener("click", function () {
                modalPass.style.display = "block";
            });
        }

        if (changeInfoBtn) {
            changeInfoBtn.addEventListener("click", function () {
                modalInfo.style.display = "block";
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener("click", function () {
                modalPass.style.display = "none";
            });
        }
        if (cancelBtn) {
            cancelBtn.addEventListener("click", function () {
                modalPass.style.display = "none";
            });
        }

        if (closBtn) {
            closBtn.addEventListener("click", function () {
                modalInfo.style.display = "none";
            });
        }
        if (canclBtn) {
            canclBtn.addEventListener("click", function () {
                modalInfo.style.display = "none";
            });
        }

        const passOld = document.getElementById("passwordold");
        const passNew = document.getElementById("passwordnew");
        const prePassNew = document.getElementById("prepasswordnew");
        const error1 = document.getElementById("emptypassword");
        const error2 = document.getElementById("emptypasswordnew");
        const error3 = document.getElementById("emptyprepasswordnew");
        const error4 = document.getElementById("checkprepasswordnew");

        if (error1) error1.style.display = "none";
        if (error2) error2.style.display = "none";
        if (error3) error3.style.display = "none";
        if (error4) error4.style.display = "none";

        function validatePassForm() {
            let isValid = true;
            if (passOld.value.trim() === "") {
                error1.style.display = "block";
                error1.style.color = "red";
                error1.style.fontWeight = "bold";
                isValid = false;
            } else {
                error1.style.display = "none";
            }

            if (passNew.value.trim() === "") {
                error2.style.display = "block";
                error2.style.color = "red";
                error2.style.fontWeight = "bold";
                isValid = false;
            } else {
                error2.style.display = "none";
            }

            if (prePassNew.value.trim() === "") {
                error3.style.display = "block";
                error3.style.color = "red";
                error3.style.fontWeight = "bold";
                error4.style.display = "none";
                isValid = false;
            } else {
                error3.style.display = "none";
                if (passNew.value !== prePassNew.value) {
                    error4.style.display = "block";
                    error4.style.color = "red";
                    error4.style.fontWeight = "bold";
                    isValid = false;
                } else {
                    error4.style.display = "none";
                }
            }
            return isValid;
        }

        document.getElementById("changepass").addEventListener("click", function (event) {
            if (!validatePassForm()) {
                event.preventDefault();
            }
        });

        if (changeAvatarBtn) {
            changeAvatarBtn.addEventListener("click", function() {
                avatarInput.click();
            });
        }

        if (avatarInput) {
            avatarInput.addEventListener("change", function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        userAvatar.src = e.target.result;
                        avatarPathInput.value = file.name;
                        saveAvatarBtn.style.display = "block";
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        if (saveAvatarBtn) {
            saveAvatarBtn.addEventListener("click", function() {
                const formData = new FormData(avatarForm);
                formData.append('save-avt', 'true');
                fetch('profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    saveAvatarBtn.style.display = "none";
                    window.location.href = 'profile.php';
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                    alert('Có lỗi khi lưu ảnh');
                });
            });
        }

        const modalInfoForm = document.getElementById("updateModal");
        if (modalInfoForm && <?php echo json_encode(!empty($error['email']) || !empty($error['phone'])); ?>) {
            modalInfoForm.style.display = "block";
        }
    });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const yearlyData = <?php echo json_encode($statistical_data); ?>;
            const yearSelector = document.getElementById("yearSelector");
            const years = <?php echo json_encode($years); ?>;
            const currentYear = new Date().getFullYear();

            // Tạo options cho yearSelector từ mảng years
            years.forEach(year => {
                const option = document.createElement("option");
                option.value = year;
                option.textContent = year;
                option.selected = (year === currentYear); // Chọn năm hiện tại mặc định
                yearSelector.appendChild(option);
            });

            let totalSpending = yearlyData[currentYear].reduce((sum, amount) => sum + amount, 0);
            document.getElementById('totalSpending').textContent = totalSpending.toLocaleString('vi-VN');

            let chartInstance = null;

            function showYearlyStats() {
                const selectedYear = document.getElementById('yearSelector').value;
                const canvas = document.getElementById('yearlyChart');
                canvas.style.display = 'block';

                const spendingData = yearlyData[selectedYear] || Array(12).fill(0); // Nếu không có dữ liệu thì dùng mảng 0
                if (chartInstance) chartInstance.destroy();

                chartInstance = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
                        datasets: [{
                            label: `Chi tiêu theo năm ${selectedYear} (VNĐ)`,
                            data: spendingData,
                            backgroundColor: 'rgba(76, 175, 80, 0.6)',
                            borderColor: 'rgba(76, 175, 80, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: { y: { beginAtZero: true, ticks: { callback: value => value.toLocaleString('vi-VN') + ' VNĐ' } } },
                        plugins: { title: { display: true, text: `Thống kê chi tiêu theo năm ${selectedYear}` } }
                    }
                });

                totalSpending = spendingData.reduce((sum, amount) => sum + amount, 0);
                document.getElementById('totalSpending').textContent = totalSpending.toLocaleString('vi-VN');
            }

            showYearlyStats();
            window.showYearlyStats = showYearlyStats;
        });
    </script>
</body>
</html>