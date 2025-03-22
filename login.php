<?php
    include 'class/customerlogin.php';
    $customer = new customerlogin();
    $er = "";
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $login_msg = $customer->login($_POST);
        if ($login_msg === true) {
            header('Location: index.php');
            exit();
        }else{
            $er = $login_msg;
        }
    }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="css/test.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="login-container">
        <h2>Đăng nhập</h2>
        <form action="login.php" method="POST">
            <label for="username">Tên đăng nhập:</label>
            <input type="text" id="username" name="username" placeholder="Tên đăng nhập">
            <span id="emptyusername" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng nhập tên đăng nhập</span>
    
            <label for="password">Mật khẩu:</label>
            <input type="password" id="password" name="password" placeholder="Mật khẩu">
            <span id="emptypassword" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng nhập mật khẩu</span>

            <?php if (!empty($er)): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi đăng nhập!',
                            text: '<?php echo addslashes($er); ?>',
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'custom-popup'
                            }
                        });

                        // Giữ lại giá trị của username và password
                        document.getElementById("username").value = "<?php echo addslashes($_POST['username'] ?? ''); ?>";
                        document.getElementById("password").value = "<?php echo addslashes($_POST['password'] ?? ''); ?>";
                    });
                </script>
            <?php endif; ?>

            <button type="submit" name="login">Đăng nhập</button>
        </form>
    
        <div class="extra-links">
            <a href="#">Quên mật khẩu?</a> |
            <a href="register.php">Bạn chưa có tài khoản? Đăng ký</a>
        </div>
    </div>
    

</body>
<script>
    document.querySelector("form").addEventListener("submit", function(event) {
        let isValid = true;
        let firstErrorField = null; // Biến lưu ô input đầu tiên bị lỗi

        let username = document.getElementById("username").value.trim();
        let password = document.getElementById("password").value.trim();

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

        // Nếu có lỗi, focus vào ô đầu tiên bị lỗi & ngăn form submit
        if (!isValid) {
            event.preventDefault();
            if (firstErrorField) firstErrorField.focus();
        }
    });
</script>
</html>