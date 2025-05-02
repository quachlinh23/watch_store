<?php
include 'class/customerlogin.php';
$customer = new customerlogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $hoten = $_POST['hoten'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; // Thêm confirm_password

    // Kiểm tra confirm_password trước khi gửi dữ liệu
    if ($password !== $confirm_password) {
        $error_message = "Mật khẩu xác nhận không khớp!";
    } else {
        $data = [
            'hoten' => $hoten,
            'username' => $username,
            'password' => $password
        ];
        $result = $customer->register($data);
        if ($result === true) {
            header('Location: login.php');
            exit();
        } else {
            $error_message = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login_register.css">
    <title>Watch Store</title>
</head>
<body>
    <div class="login-container">
        <h2>Đăng ký</h2>
        <form action="register.php" method="POST">
            <label for="hoten">Họ và Tên:</label>
            <input type="text" id="hoten" name="hoten" placeholder="Nhập họ và tên">
            <span id="emptyhoten" class="error" style="display: none;">Vui lòng nhập họ và tên</span>

            <label for="username">Tên đăng nhập:</label>
            <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập">
            <span id="emptyusername" class="error" style="display: none;">Vui lòng nhập tên đăng nhập</span>

            <label for="password">Mật khẩu:</label>
            <input type="password" id="password" name="password" placeholder="Nhập mật khẩu">
            <span id="emptypassword" class="error" style="display: none;">Vui lòng nhập mật khẩu</span>

            <label for="confirm_password">Xác nhận mật khẩu:</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu">
            <span id="emptyprepassword" class="error" style="display: none;">Vui lòng nhập lại mật khẩu</span>
            <span id="mismatchpassword" class="error" style="display: none;">Mật khẩu xác nhận không khớp</span>

            <?php if (!empty($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <button type="submit" name="register">Đăng ký</button>
        </form>

        <div class="extra-links">
            <a href="login.php">Bạn đã có tài khoản? Đăng nhập</a>
        </div>
    </div>

    <script>
        document.querySelector("form").addEventListener("submit", function(event) {
            let isValid = true;
            let firstErrorField = null;

            let hoten = document.getElementById("hoten").value.trim();
            let username = document.getElementById("username").value.trim();
            let password = document.getElementById("password").value.trim();
            let confirm_password = document.getElementById("confirm_password").value.trim();

            if (hoten === "") {
                document.getElementById("emptyhoten").style.display = "block";
                if (!firstErrorField) firstErrorField = document.getElementById("hoten");
                isValid = false;
            } else {
                document.getElementById("emptyhoten").style.display = "none";
            }

            if (username === "") {
                document.getElementById("emptyusername").style.display = "block";
                if (!firstErrorField) firstErrorField = document.getElementById("username");
                isValid = false;
            } else {
                document.getElementById("emptyusername").style.display = "none";
            }

            if (password === "") {
                document.getElementById("emptypassword").style.display = "block";
                if (!firstErrorField) firstErrorField = document.getElementById("password");
                isValid = false;
            } else {
                document.getElementById("emptypassword").style.display = "none";
            }

            if (confirm_password === "") {
                document.getElementById("emptyprepassword").style.display = "block";
                if (!firstErrorField) firstErrorField = document.getElementById("confirm_password");
                isValid = false;
            } else if (confirm_password !== password) {
                document.getElementById("emptyprepassword").style.display = "none";
                document.getElementById("mismatchpassword").style.display = "block";
                if (!firstErrorField) firstErrorField = document.getElementById("confirm_password");
                isValid = false;
            } else {
                document.getElementById("emptyprepassword").style.display = "none";
                document.getElementById("mismatchpassword").style.display = "none";
            }

            if (!isValid) {
                event.preventDefault();
                if (firstErrorField) firstErrorField.focus();
            }
        });
    </script>
</body>
</html>