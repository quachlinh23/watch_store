<?php
    include 'class/customerlogin.php';
    $customer = new customerlogin();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
        
        $hoten = $_POST['hoten'];
        $username = $_POST['username'];
        $password = $_POST['password'];

        $data = [
            'hoten' =>$hoten,
            'username' => $username,
            'password' => $password
        ];
        $result = $customer->register($data);
        if ($result === true) {
            header('Location: dangnhap.php');
            exit();
        } else {
            $error_message = $result;
        }
    }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login_register.css">
    <title>Đăng nhập</title>
</head>
<body>
    <div class="login-container">
        <h2>Đăng nhập</h2>
        <form action="register.php" method="POST">
            
            <label for="hoten">Họ và Tên:</label>
            <input type="text" id="hoten" name="hoten" placeholder="Tên đăng nhập">
            <span id="emptyhoten" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng nhập tên đăng nhập</span>
            
            <label for="username">Tên đăng nhập:</label>
            <input type="text" id="username" name="username" placeholder="Tên đăng nhập">
            <span id="emptyusername" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng nhập tên đăng nhập</span>
    
            <label for="password">Mật khẩu:</label>
            <input type="password" id="password" name="password" placeholder="Mật khẩu">
            <span id="emptypassword" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng nhập mật khẩu</span>
            
            <label for="prepassword">Xác nhận mật khẩu:</label>
            <input type="password" id="prepassword" name="password" placeholder="Xác nhận mật khẩu" style="margin-bottom: 10px;">
            <span id="emptyprepassword" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng nhập lại mật khẩu</span>
            <span id="mismatchpassword" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Mật khẩu xác nhận không khớp</span>

            <?php if (!empty($error_message)): ?>
                <p style="color: red; font-weight: bold; text-align: left; margin-top: 10px;">
                    <?php echo $error_message; ?>
                </p>
            <?php endif; ?>

            <button type="submit" name="register">Đăng ký</button>
        </form>
    
        <div class="extra-links">
            <a href="login.php">Bạn đã có tài khoản? Đăng nhập</a>
        </div>
    </div>
    

</body>
<script>
    document.querySelector("form").addEventListener("submit", function(event) {
        let isValid = true;
        let firstErrorField = null; // Biến lưu ô input đầu tiên bị lỗi

        // Lấy giá trị của các ô input
        let hoten = document.getElementById("hoten").value.trim();
        let username = document.getElementById("username").value.trim();
        let password = document.getElementById("password").value.trim();
        let prepassword = document.getElementById("prepassword").value.trim();

        // Kiểm tra họ tên
        if (hoten === "") {
            document.getElementById("emptyhoten").style.display = "block";
            if (!firstErrorField) firstErrorField = document.getElementById("hoten");
            isValid = false;
        } else {
            document.getElementById("emptyhoten").style.display = "none";
        }

        // Kiểm tra tên đăng nhập
        if (username === "") {
            document.getElementById("emptyusername").style.display = "block";
            if (!firstErrorField) firstErrorField = document.getElementById("username");
            isValid = false;
        } else {
            document.getElementById("emptyusername").style.display = "none";
        }

        // Kiểm tra mật khẩu
        if (password === "") {
            document.getElementById("emptypassword").style.display = "block";
            if (!firstErrorField) firstErrorField = document.getElementById("password");
            isValid = false;
        } else {
            document.getElementById("emptypassword").style.display = "none";
        }

        // Kiểm tra xác nhận mật khẩu
        if (prepassword === "") {
            document.getElementById("emptyprepassword").style.display = "block";
            if (!firstErrorField) firstErrorField = document.getElementById("prepassword");
            isValid = false;
        } else if (prepassword !== password) {
            document.getElementById("emptyprepassword").style.display = "none";
            document.getElementById("mismatchpassword").style.display = "block";
            if (!firstErrorField) firstErrorField = document.getElementById("prepassword");
            isValid = false;
        } else {
            document.getElementById("emptyprepassword").style.display = "none";
            document.getElementById("mismatchpassword").style.display = "none";
        }

        // Nếu có lỗi, focus vào ô đầu tiên bị lỗi & ngăn form submit
        if (!isValid) {
            event.preventDefault();
            if (firstErrorField) firstErrorField.focus();
        }
    });
</script>
</html>