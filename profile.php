<?php
    include_once "class/brand.php";
    $brand = new brand();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Store</title>
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/head.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			font-family: Arial, sans-serif;
		}
    </style>
</head>
<body>
    <?php
        include 'layout/header.php';
    ?>
    <div class="profile-info">
        <h2 class="information">Thông Tin Cá Nhân</h2>
        <div class="content">
            <div class="profile-img">
                <div class="img_us">
                    <img id="user-avatar" src="images/bannercasio.png" alt="Ảnh Người Dùng">
                    <button id="change_avt" class="change_avt">🖼️</button>
                    <input type="file" id="avatar-input" accept="image/*" style="display: none;">
                </div>
                <button id="save-avatar" class="save-avatar" style="display: none;">Lưu Ảnh</button>
            </div>
            <?php
                if ($check){
                    $info = $cus->getinforcustomerbyid($id_cus)->fetch_assoc();
            ?>    
            <div class="personal-details">        
                <p style="margin-bottom: 20px;"><strong>Họ và Tên: </strong><?php echo htmlspecialchars($info['tenKhachHang']); ?></p>
                <p style="margin-bottom: 20px;"><strong>Địa Chỉ: </strong><?php echo htmlspecialchars($info['diaChi']); ?></p>
                <p style="margin-bottom: 20px;"><strong>Số Điện Thoại: </strong><?php echo htmlspecialchars($info['soDT']); ?></p>
                <p style="margin-bottom: 20px;"><strong>Email: </strong><?php echo htmlspecialchars($info['email']); ?></p>
                <div class="btn_edit_info">
                    <button id="change_pass">Đổi Mật Khẩu</button>
                    <button id="editInfoBtn">Cập Nhật Thông Tin</button>
                </div>

                <!-- Modal (Form Cập Nhật Thông Tin) -->
                <div id="updateModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Cập Nhật Thông Tin</h2>

                        <!-- Form cho việc cập nhật thông tin -->
                        <form id="updateForm" action="" method="POST">
                                
                            <label for="name">Tên:</label>
                            <input type="text" id="name" name="HoTen" value="" >
                            <span class="error" id="emptyname">Tên không được để trống</span>
                                
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="Email" value="" >
                            <span class="error" id="emptyemail">Email không được để trống</span>
                                
                            <label for="phone">Số điện thoại:</label>
                            <input type="tel" id="phone" name="sodt" value="" >
                            <span class="error" id="emptysodt">Số điện thoại không được để trống</span>

                            <label for="address">Địa chỉ:</label>
                            <input type="text" id="address" name="DiaChi" value="" >
                            <span class="error" id="emptyaddress">Địa chỉ không được để trống</span>
                                
                            <div class="control">
                                <button id="cancel" class="btn_cancle">Hủy Bỏ</button>
                                <button id= "save" type="submit" name ="Save" class="btn_save">Cập Nhật</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal (Form Đổi Mật Khẩu) -->
                <div id="updatepassword" class="modal">
                    <div class="modal-content">
                        <span class="close_1">&times;</span>
                        <h2 style="text-align: center; margin-bottom: 30px;">Đổi Mật Khẩu</h2>
                        <!-- Form cho việc cập nhật thông tin -->
                        <form id="updateForm" action="" method="POST">
                                
                            <label for="name">Mật khẩu cũ:</label>
                            <input type="password" id="passwordold" name="matkhaucu" value="">
                            <span class="error" id="emptypassword">Mật khẩu cũ không được để trống</span>
                                
                            <label for="email">Mật khẩu mới:</label>
                            <input type="password" id="passwordnew" name="matkhaumoi" value="">
                            <span id="emptypasswordnew" class="error">Mật khẩu mới không được bỏ trống</span>
                                
                            <label for="email">Xác nhận mật khẩu mới:</label>
                            <input type="password" id="prepasswordnew" name="matkhaumoi" value="">
                            <span id="emptyprepasswordnew" class="error">Xác nhận mật khẩu mới không được bỏ trống</span>
                            <span id="checkprepasswordnew" class="error">Xác nhận mật khẩu mới phải trung với mật khẩu mới</span>

                            <div class="control">
                                <button id="cancel_1" class="btn_cancle">Hủy</button>
                                <button id= "changepass" type="submit" name ="" class="btn_save">Lưu</button>
                            </div>
                        </form>
                    </div>
                </div>
                
            </div>
            <?php
                }
            ?>
        </div>
    </div>
</body>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const changePassBtn = document.getElementById("change_pass");
        const changeinfobtn = document.getElementById("editInfoBtn");

        const modal = document.getElementById("updatepassword");
        const modal_1 = document.getElementById("updateModal");

        const closBtn = document.querySelector(".close");
        const canclBtn = document.getElementById("cancel");

        const closeBtn = document.querySelector(".close_1");
        const cancelBtn = document.getElementById("cancel_1");


        const pass_old = document.getElementById("passwordold");
        const pass_new = document.getElementById("passwordnew");
        const pre_pass_new = document.getElementById("prepasswordnew");

        const error1 = document.getElementById("emptypassword");
        const error2 = document.getElementById("emptypasswordnew");
        const error3 = document.getElementById("emptyprepasswordnew");
        const error4 = document.getElementById("checkprepasswordnew");

        

        // Hiển thị modal khi nhấn nút "Đổi Mật Khẩu"
        changePassBtn.addEventListener("click", function () {
            modal.style.display = "block";
        });

        changeinfobtn.addEventListener("click", function () {
            modal_1.style.display = "block";
        });

        // Đóng modal khi nhấn vào dấu "×"
        closeBtn.addEventListener("click", function () {
            modal.style.display = "none";

            // Ẩn lỗi ban đầu
            error1.style.display = "none";
            error2.style.display = "none";
            error3.style.display = "none";
            error4.style.display = "none";

            //Xóa nội dung ở các ô
            pass_old.value = "";
            pass_new.value = "";
            pre_pass_new.value = "";
        });

        closBtn.addEventListener("click", function () {
            modal_1.style.display = "none";

            // Ẩn lỗi ban đầu
            error1.style.display = "none";
            error2.style.display = "none";
            error3.style.display = "none";
            error4.style.display = "none";

            //Xóa nội dung ở các ô
            pass_old.value = "";
            pass_new.value = "";
            pre_pass_new.value = "";
        });

        // Đóng modal khi nhấn vào nút "Hủy"
        cancelBtn.addEventListener("click", function (event) {
            event.preventDefault(); // Ngăn chặn reload trang khi nhấn nút cancel
            modal.style.display = "none";

            // Ẩn lỗi ban đầu
            error1.style.display = "none";
            error2.style.display = "none";
            error3.style.display = "none";
            error4.style.display = "none";

            //Xóa nội dung ở các ô
            pass_old.value = "";
            pass_new.value = "";
            pre_pass_new.value = "";
        });

        canclBtn.addEventListener("click", function (event) {
            event.preventDefault(); // Ngăn chặn reload trang khi nhấn nút cancel
            modal_1.style.display = "none";

            // Ẩn lỗi ban đầu
            // error1.style.display = "none";
            // error2.style.display = "none";
            // error3.style.display = "none";
            // error4.style.display = "none";

            //Xóa nội dung ở các ô
            // pass_old.value = "";
            // pass_new.value = "";
            // pre_pass_new.value = "";
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const pass_old = document.getElementById("passwordold");
        const pass_new = document.getElementById("passwordnew");
        const pre_pass_new = document.getElementById("prepasswordnew");

        const error1 = document.getElementById("emptypassword");
        const error2 = document.getElementById("emptypasswordnew");
        const error3 = document.getElementById("emptyprepasswordnew");
        const error4 = document.getElementById("checkprepasswordnew");

        // Ẩn lỗi ban đầu
        error1.style.display = "none";
        error2.style.display = "none";
        error3.style.display = "none";
        error4.style.display = "none";

        function validateForm() {
            let isValid = true;

            if (pass_old.value.trim() === "") {
                error1.style.display = "block";
                error1.style.color = "red";
                error1.style.fontWeight = "bold";
                isValid = false;
            } else {
                error1.style.display = "none";
            }

            if (pass_new.value.trim() === "") {
                error2.style.display = "block";
                error2.style.color = "red";
                error2.style.fontWeight = "bold";
                isValid = false;
            } else {
                error2.style.display = "none";
            }

            if (pre_pass_new.value.trim() === "") {
                error3.style.display = "block";
                error3.style.color = "red";
                error3.style.fontWeight = "bold";
                error4.style.display = "none"; // Ẩn lỗi xác nhận mật khẩu sai nếu trường này rỗng
                isValid = false;
            } else {
                error3.style.display = "none";
                if (pass_new.value !== pre_pass_new.value) {
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


        // Gán sự kiện cho nút Lưu
        document.getElementById("changepass").addEventListener("click", function (event) {
            if (!validateForm()) {
                event.preventDefault();
            }
        });
    });


</script>
</html>