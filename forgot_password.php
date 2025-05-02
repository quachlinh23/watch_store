<?php
ob_start();
session_start();
include 'class/customerlogin.php';

// Load PHPMailer
include 'helpers/phpmailer/src/Exception.php';
include 'helpers/phpmailer/src/PHPMailer.php';
include 'helpers/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$cust_auth = new customerlogin();

$notice = "";
$phase = 'enter_email';

// Tạo token CSRF nếu chưa có
if (!isset($_SESSION['cust_csrf_token'])) {
    $_SESSION['cust_csrf_token'] = bin2hex(random_bytes(32));
}

// Bước 1: Nhập email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cust_email'], $_POST['cust_csrf_token'])) {
    if ($_POST['cust_csrf_token'] !== $_SESSION['cust_csrf_token']) {
        $notice = "Yêu cầu không hợp lệ.";
    } else {
        $cust_email = trim($_POST['cust_email']);
        if (!filter_var($cust_email, FILTER_VALIDATE_EMAIL)) {
            $notice = "Email không hợp lệ.";
        } else {
            $cust_data = $cust_auth->getCustomerByEmail($cust_email);
            error_log("getCustomerByEmail result for $cust_email: " . print_r($cust_data, true));
            if ($cust_data) {
                $verify_code = sprintf("%06d", mt_rand(0, 999999));
                $_SESSION['cust_verify_code'] = $verify_code;
                $_SESSION['cust_code_expiry'] = time() + 900;
                $_SESSION['cust_reset_email'] = $cust_email;
                $_SESSION['cust_reset_id'] = $cust_data['id'];
                error_log("Stored session data: " . print_r($_SESSION, true));

                echo '<script>';
                echo 'const custResetId = ' . json_encode($_SESSION['cust_reset_id']) . ';';
                echo 'console.log("cust_reset_id:", custResetId);';
                echo '</script>';

                $mailer = new PHPMailer(true);
                try {
                    $mailer->isSMTP();
                    $mailer->Host = 'smtp.gmail.com';
                    $mailer->SMTPAuth = true;
                    $mailer->Username = 'quachl020@gmail.com';
                    $mailer->Password = 'oxlf myec fkbf ipmh';
                    $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mailer->Port = 587;

                    $mailer->SMTPOptions = [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        ]
                    ];

                    $mailer->setFrom('quachl020@gmail.com', 'Tên Cửa Hàng');
                    $mailer->addReplyTo('quachl020@gmail.com', 'Tên Cửa Hàng');
                    $mailer->addAddress($cust_data['email'], $cust_data['tenkhachhang']);

                    $mailer->isHTML(true);
                    $mailer->Subject = 'Mã xác nhận khôi phục mật khẩu';
                    $mailer->Body = "
                        <h2>Khôi phục mật khẩu</h2>
                        <p>Xin chào {$cust_data['tenkhachhang']},</p>
                        <p>Mã xác nhận của bạn là: <strong>{$verify_code}</strong></p>
                        <p>Vui lòng nhập mã này để đặt lại mật khẩu. Mã có hiệu lực trong 15 phút.</p>
                        <p>Nếu không phải bạn yêu cầu, vui lòng bỏ qua email này.</p>
                        <hr>
                        <p>Trân trọng,<br>Ban quản trị</p>
                    ";
                    $mailer->AltBody = "Mã xác nhận của bạn là: {$verify_code}\nVui lòng nhập mã này để đặt lại mật khẩu. Mã có hiệu lực trong 15 phút.";

                    $mailer->send();
                    $notice = "Mã xác nhận đã được gửi đến email của bạn.";
                    $phase = 'verify_code';
                } catch (Exception $e) {
                    error_log('Mailer Error: ' . $mailer->ErrorInfo);
                    $notice = "Lỗi khi gửi email: " . $mailer->ErrorInfo;
                }
            } else {
                $notice = "Email không tồn tại trong hệ thống.";
            }
        }
    }
}

// Bước 2: Xác minh mã xác nhận
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cust_code'], $_POST['cust_csrf_token'])) {
    if ($_POST['cust_csrf_token'] !== $_SESSION['cust_csrf_token']) {
        $notice = "Yêu cầu không hợp lệ.";
    } else {
        $input_code = trim($_POST['cust_code']);
        if (isset($_SESSION['cust_verify_code'], $_SESSION['cust_reset_email'], $_SESSION['cust_code_expiry']) && $input_code === $_SESSION['cust_verify_code']) {
            if (time() > $_SESSION['cust_code_expiry']) {
                $notice = "Mã xác nhận đã hết hạn. Vui lòng yêu cầu mã mới.";
                $phase = 'enter_email';
                unset($_SESSION['cust_verify_code'], $_SESSION['cust_reset_email'], $_SESSION['cust_reset_id'], $_SESSION['cust_code_expiry']);
            } else {
                $notice = "Mã xác nhận hợp lệ. Vui lòng nhập mật khẩu mới.";
                $phase = 'reset_password';
            }
        } else {
            $notice = "Mã xác nhận không hợp lệ.";
        }
    }
}

// Bước 3: Đặt lại mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_pass'], $_POST['confirm_pass'], $_POST['cust_csrf_token'])) {
    if ($_POST['cust_csrf_token'] !== $_SESSION['cust_csrf_token']) {
        $notice = "Yêu cầu không hợp lệ.";
    } else {
        $new_pass = trim($_POST['new_pass']);
        $confirm_pass = trim($_POST['confirm_pass']);

        if ($new_pass !== $confirm_pass) {
            $notice = "Mật khẩu xác nhận không khớp.";
        } elseif (strlen($new_pass) < 6) {
            $notice = "Mật khẩu phải có ít nhất 6 ký tự.";
        } else {
            if (isset($_SESSION['cust_reset_id'], $_SESSION['cust_reset_email'])) {
                error_log("Session data before update: " . print_r($_SESSION, true));
                $update_result = $cust_auth->updatePassword($_SESSION['cust_reset_id'], $new_pass);
                error_log("Update password result: " . ($update_result ? "Success" : "Failed"));
                if ($update_result) {
                    unset($_SESSION['cust_verify_code'], $_SESSION['cust_reset_email'], $_SESSION['cust_reset_id'], $_SESSION['cust_code_expiry'], $_SESSION['cust_csrf_token']);
                    $notice = "Cập nhật mật khẩu thành công.";
                    // Use PHP redirect as fallback
                    ob_end_clean(); // Clear output buffer
                    header("Location: login.php");
                    // JavaScript redirect for user feedback
                    echo '<script>
                        document.addEventListener("DOMContentLoaded", function() {
                            if (typeof Swal !== "undefined") {
                                Swal.fire({
                                    icon: "success",
                                    title: "Thành công",
                                    text: "Cập nhật mật khẩu thành công. Bạn sẽ được chuyển đến trang đăng nhập.",
                                    confirmButtonText: "OK",
                                    timer: 3000,
                                    timerProgressBar: true
                                }).then(() => {
                                    window.location.href = "login.php";
                                });
                            } else {
                                console.error("SweetAlert2 not loaded");
                                window.location.href = "login.php";
                            }
                        });
                    </script>';
                    exit();
                } else {
                    $notice = "Lỗi khi cập nhật mật khẩu. Vui lòng thử lại.";
                    $phase = 'reset_password';
                }
            } else {
                $notice = "Phiên làm việc không hợp lệ. Vui lòng thử lại.";
                $phase = 'enter_email';
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
        <?php if ($phase === 'enter_email'): ?>
            <h2>Quên mật khẩu</h2>
            <form method="POST" id="emailForm">
                <input type="hidden" name="cust_csrf_token" value="<?php echo htmlspecialchars($_SESSION['cust_csrf_token']); ?>">
                <label>Nhập email của bạn:</label>
                <input type="email" id="cust_email" name="cust_email" placeholder="Email" required>
                <span id="emptyEmail" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng nhập email</span>
                <button type="submit">Gửi mã xác nhận</button>
            </form>

        <?php elseif ($phase === 'verify_code'): ?>
            <h2>Xác nhận mã</h2>
            <form method="POST" id="codeForm">
                <input type="hidden" name="cust_csrf_token" value="<?php echo htmlspecialchars($_SESSION['cust_csrf_token']); ?>">
                <label>Nhập mã xác nhận:</label>
                <input type="text" id="cust_code" name="cust_code" placeholder="Mã xác nhận" required>
                <span id="emptyCode" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng nhập mã xác nhận</span>
                <button type="submit">Xác nhận</button>
            </form>

        <?php elseif ($phase === 'reset_password'): ?>
            <h2>Đặt lại mật khẩu</h2>
            <form method="POST" id="passwordForm">
                <input type="hidden" name="cust_csrf_token" value="<?php echo htmlspecialchars($_SESSION['cust_csrf_token']); ?>">
                <label>Nhập mật khẩu mới:</label>
                <input type="password" id="new_pass" name="new_pass" placeholder="Mật khẩu mới" required>
                <span id="emptyNewPassword" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng nhập mật khẩu mới</span>
                <label>Xác nhận mật khẩu:</label>
                <input type="password" id="confirm_pass" name="confirm_pass" placeholder="Xác nhận mật khẩu" required>
                <span id="emptyConfirmPassword" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng xác nhận mật khẩu</span>
                <span id="passwordMismatch" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Mật khẩu xác nhận không khớp</span>
                <button type="submit">Lưu</button>
            </form>
        <?php endif; ?>

        <?php if (!empty($notice)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: '<?php echo strpos($notice, "thành công") !== false ? "success" : "error"; ?>',
                            title: 'Thông báo',
                            text: '<?php echo addslashes($notice); ?>',
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'custom-popup'
                            }
                        });
                    } else {
                        console.error('SweetAlert2 not loaded');
                        alert('<?php echo addslashes($notice); ?>');
                    }
                });
            </script>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('emailForm')?.addEventListener('submit', function(event) {
            validateForm(event, 'cust_email', 'emptyEmail');
        });

        document.getElementById('codeForm')?.addEventListener('submit', function(event) {
            validateForm(event, 'cust_code', 'emptyCode');
        });

        document.getElementById('passwordForm')?.addEventListener('submit', function(event) {
            let isValid = true;
            let firstErrorField = null;

            const newPass = document.getElementById('new_pass').value.trim();
            if (newPass === '') {
                document.getElementById('emptyNewPassword').style.display = 'block';
                if (!firstErrorField) firstErrorField = document.getElementById('new_pass');
                isValid = false;
            } else {
                document.getElementById('emptyNewPassword').style.display = 'none';
            }

            const confirmPass = document.getElementById('confirm_pass').value.trim();
            if (confirmPass === '') {
                document.getElementById('emptyConfirmPassword').style.display = 'block';
                if (!firstErrorField) firstErrorField = document.getElementById('confirm_pass');
                isValid = false;
            } else {
                document.getElementById('emptyConfirmPassword').style.display = 'none';
            }

            if (newPass !== confirmPass) {
                document.getElementById('passwordMismatch').style.display = 'block';
                if (!firstErrorField) firstErrorField = document.getElementById('confirm_pass');
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
<?php
ob_end_flush(); // Flush output buffer at the end
?>