<?php
session_start();
include '../class/employeelogin.php';

// Load PHPMailer
include '../helpers/phpmailer/src/Exception.php';
include '../helpers/phpmailer/src/PHPMailer.php';
include '../helpers/phpmailer/src/SMTP.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$class = new adminlogin();

$message = "";
$step = 'email'; // Bước mặc định: nhập email

// Tạo token CSRF nếu chưa có
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Bước 1: Nhập email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['csrf_token'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Yêu cầu không hợp lệ.";
    } else {
        $email = trim($_POST['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Email không hợp lệ.";
        } else {
            $user = $class->getEmployeeByEmail($email);
            if ($user) {
                // Tạo mã xác nhận 6 chữ số
                $code = sprintf("%06d", mt_rand(0, 999999));

                // Lưu mã, thời gian hết hạn và thông tin người dùng vào session
                $_SESSION['reset_code'] = $code;
                $_SESSION['code_expiry'] = time() + 900; // 15 phút
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_user_id'] = $user['id'];

                // Gửi email với mã xác nhận qua PHPMailer
                $mail = new PHPMailer(true);
                try {
                    // Cấu hình server
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'quachl020@gmail.com';
                    $mail->Password = 'oxlf myec fkbf ipmh'; // Thay bằng mật khẩu ứng dụng mới
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Tùy chọn SSL
                    $mail->SMTPOptions = [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        ]
                    ];

                    // Người gửi và người nhận
                    $mail->setFrom('quachl020@gmail.com', 'Tên Cửa Hàng');
                    $mail->addReplyTo('quachl020@gmail.com', 'Tên Cửa Hàng');
                    $mail->addAddress($user['email'], $user['tenNhanVien']);

                    // Nội dung email
                    $mail->isHTML(true);
                    $mail->Subject = 'Mã xác nhận khôi phục mật khẩu';
                    $mail->Body = "
                        <h2>Khôi phục mật khẩu</h2>
                        <p>Xin chào {$user['tenNhanVien']},</p>
                        <p>Mã xác nhận của bạn là: <strong>{$code}</strong></p>
                        <p>Vui lòng nhập mã này để đặt lại mật khẩu. Mã có hiệu lực trong 15 phút.</p>
                        <p>Nếu không phải bạn yêu cầu, vui lòng bỏ qua email này.</p>
                        <hr>
                        <p>Trân trọng,<br>Ban quản trị</p>
                    ";
                    $mail->AltBody = "Mã xác nhận của bạn là: {$code}\nVui lòng nhập mã này để đặt lại mật khẩu. Mã có hiệu lực trong 15 phút.";

                    // Gửi email
                    $mail->send();
                    $message = "Mã xác nhận đã được gửi đến email của bạn.";
                    $step = 'verify_code';
                } catch (Exception $e) {
                    error_log('Mailer Error: ' . $mail->ErrorInfo);
                    $message = "Lỗi khi gửi email: " . $mail->ErrorInfo;
                }
            } else {
                $message = "Email không tồn tại trong hệ thống.";
            }
        }
    }
}

// Bước 2: Xác minh mã xác nhận
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'], $_POST['csrf_token'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Yêu cầu không hợp lệ.";
    } else {
        $code = trim($_POST['code']);

        if (isset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['code_expiry']) && $code === $_SESSION['reset_code']) {
            if (time() > $_SESSION['code_expiry']) {
                $message = "Mã xác nhận đã hết hạn. Vui lòng yêu cầu mã mới.";
                $step = 'email';
                unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_user_id'], $_SESSION['code_expiry']);
            } else {
                $message = "Mã xác nhận hợp lệ. Vui lòng nhập mật khẩu mới.";
                $step = 'reset_password';
            }
        } else {
            $message = "Mã xác nhận không hợp lệ.";
        }
    }
}

// Bước 3: Đặt lại mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'], $_POST['confirm_password'], $_POST['csrf_token'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Yêu cầu không hợp lệ.";
    } else {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $message = "Mật khẩu xác nhận không khớp.";
        } elseif (strlen($new_password) < 6) {
            $message = "Mật khẩu phải có ít nhất 6 ký tự.";
        } else {
            if (isset($_SESSION['reset_user_id'], $_SESSION['reset_email'])) {
                $class->updatePassword($_SESSION['reset_user_id'], $new_password);
                // Xóa dữ liệu session
                unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_user_id'], $_SESSION['code_expiry'], $_SESSION['csrf_token']);
                // Chuyển hướng đến trang đăng nhập
                header("Location: loginpage.php");
                exit();
            } else {
                $message = "Phiên làm việc không hợp lệ. Vui lòng thử lại.";
                $step = 'email';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>
    <link rel="stylesheet" href="css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .login-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .login-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .login-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <?php if ($step === 'email'): ?>
            <h2>Quên mật khẩu</h2>
            <form method="POST" id="emailForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <label>Nhập email của bạn:</label>
                <input type="email" id="email" name="email" placeholder="Email" required>
                <span id="emptyEmail" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng nhập email</span>
                <button type="submit">Gửi mã xác nhận</button>
            </form>

        <?php elseif ($step === 'verify_code'): ?>
            <h2>Xác nhận mã</h2>
            <form method="POST" id="codeForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <label>Nhập mã xác nhận:</label>
                <input type="text" id="code" name="code" placeholder="Mã xác nhận" required>
                <span id="emptyCode" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng nhập mã xác nhận</span>
                <button type="submit">Xác nhận</button>
            </form>

        <?php elseif ($step === 'reset_password'): ?>
            <h2>Đặt lại mật khẩu</h2>
            <form method="POST" id="passwordForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <label>Nhập mật khẩu mới:</label>
                <input type="password" id="new_password" name="new_password" placeholder="Mật khẩu mới" required>
                <span id="emptyNewPassword" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng nhập mật khẩu mới</span>
                <label>Xác nhận mật khẩu:</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
                <span id="emptyConfirmPassword" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng xác nhận mật khẩu</span>
                <span id="passwordMismatch" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Mật khẩu xác nhận không khớp</span>
                <button type="submit">Lưu</button>
            </form>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: '<?php echo strpos($message, "thành công") !== false ? "success" : "error"; ?>',
                        title: 'Thông báo',
                        text: '<?php echo addslashes($message); ?>',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'custom-popup'
                        }
                    });
                });
            </script>
        <?php endif; ?>
    </div>

    <script>
        // Client-side validation
        document.getElementById('emailForm')?.addEventListener('submit', function(event) {
            validateForm(event, 'email', 'emptyEmail');
        });

        document.getElementById('codeForm')?.addEventListener('submit', function(event) {
            validateForm(event, 'code', 'emptyCode');
        });

        document.getElementById('passwordForm')?.addEventListener('submit', function(event) {
            let isValid = true;
            let firstErrorField = null;

            // Validate new password
            const newPassword = document.getElementById('new_password').value.trim();
            if (newPassword === '') {
                document.getElementById('emptyNewPassword').style.display = 'block';
                if (!firstErrorField) firstErrorField = document.getElementById('new_password');
                isValid = false;
            } else {
                document.getElementById('emptyNewPassword').style.display = 'none';
            }

            // Validate confirm password
            const confirmPassword = document.getElementById('confirm_password').value.trim();
            if (confirmPassword === '') {
                document.getElementById('emptyConfirmPassword').style.display = 'block';
                if (!firstErrorField) firstErrorField = document.getElementById('confirm_password');
                isValid = false;
            } else {
                document.getElementById('emptyConfirmPassword').style.display = 'none';
            }

            // Check password match
            if (newPassword !== confirmPassword) {
                document.getElementById('passwordMismatch').style.display = 'block';
                if (!firstErrorField) firstErrorField = document.getElementById('confirm_password');
                isValid = false;
            } else {
                document.getElementById('passwordMismatch').style.display = 'none';
            }

            if (!isValid) {
                event.preventDefault();
                if (firstErrorField) firstErrorField.focus();
            }
        });

        function validateForm(event, fieldId, errorSpanId) {
            const value = document.getElementById(fieldId).value.trim();
            if (value === '') {
                document.getElementById(errorSpanId).style.display = 'block';
                event.preventDefault();
                document.getElementById(fieldId).focus();
            } else {
                document.getElementById(errorSpanId).style.display = 'none';
            }
        }
    </script>
</body>
</html>