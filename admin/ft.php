<?php
include '../lib/session.php';
include '../class/employee.php';

// Kiểm tra session
Session::checkSession();
$employ = new employee();

// Lấy thông tin người dùng
$idUser = intval(Session::get('idEmployee'));
$inFo = $employ->getEmployeeById($idUser)->fetch_assoc();
$storedPassword = $inFo['password'];
$messageInfo = "";
$messagePass = "";
$showPasswordForm = false;
$showInfoForm = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if (isset($_POST['fullname']) && isset($_POST['phone']) && isset($_POST['email'])) {
        $fullname = trim($_POST['fullname']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        
        $updateEmployee = $employ->updateEmployee($idUser, $fullname, $phone, $email);
        if ($updateEmployee === true) {
            echo '<script>window.location.href="ft.php";</script>';
            exit;
        } else {
            $messageInfo = $updateEmployee;
            $showInfoForm = true;
        }
    }
    elseif (isset($_POST['old-pass']) && isset($_POST['new-pass']) && isset($_POST['confirm-pass'])) {
        $oldPass = trim($_POST['old-pass']);
        $newPass = trim($_POST['new-pass']);
        $confirmPass = trim($_POST['confirm-pass']);
        if (md5($oldPass) !== $storedPassword) {
            $messagePass = "Vui lòng nhập mật khẩu cũ khớp với mật khẩu cũ!";
            $showPasswordForm = true;
        } elseif ($oldPass === $newPass) {
            $messagePass = "Mật khẩu mới không được trùng với mật khẩu cũ!";
            $showPasswordForm = true;
        } elseif ($newPass !== $confirmPass) {
            $messagePass = "Mật khẩu xác nhận không khớp!";
            $showPasswordForm = true;
        } else {
            $hashedNewPass = md5($newPass);
            if ($employ->updatePassword($idUser, $hashedNewPass)) {
                Session::set('password', $hashedNewPass);
                echo '<script>window.location.href="ft.php";</script>';
                exit;
            } else {
                $messagePass = "Đổi mật khẩu thất bại!";
                $showPasswordForm = true;
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/fix.css">
</head>
<body>
    <div class="container">
        <h2>Thông tin cá nhân</h2>
        <div id="info-section" class="<?php echo (!$showPasswordForm && !$showInfoForm) ? 'visible' : 'hidden'; ?>">
            <p><strong>Họ và tên:</strong> <?php echo htmlspecialchars($inFo['tenNhanVien']); ?></p>
            <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($inFo['soDT']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($inFo['email']); ?></p>
            <p><strong>Tài khoản:</strong> <?php echo htmlspecialchars($inFo['username']); ?></p>
            <button onclick="toggleForm('info-form')">Thay đổi thông tin</button>
            <button onclick="toggleForm('password-form')">Thay đổi mật khẩu</button>
        </div>

        <!-- Form thay đổi thông tin -->
        <form id="info-form" class="<?php echo $showInfoForm ? 'visible' : 'hidden'; ?>" method="POST" onsubmit="return validateInfoForm()" enctype="multipart/form-data">
            <h3>Thay đổi thông tin</h3>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="fullname" placeholder="Họ và tên" value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : htmlspecialchars($inFo['tenNhanVien']); ?>">
            </div>
            <div class="error-message" id="fullname-error">Vui lòng nhập họ và tên</div>
            <div class="input-group">
                <i class="fas fa-phone"></i>
                <input type="tel" name="phone" placeholder="Số điện thoại" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : htmlspecialchars($inFo['soDT']); ?>">
            </div>
            <div class="error-message" id="phone-error">Vui lòng nhập số điện thoại</div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($inFo['email']); ?>">
            </div>
            <div class="error-message" id="email-error">Vui lòng nhập email</div>
            <div class="error-message" style="color: red; margin-bottom: 10px; <?php echo $messageInfo ? 'display: block;' : 'display: none;'; ?>">
                <?php echo $messageInfo; ?>
            </div>
            <button type="submit" name="submit">Lưu thay đổi</button>
            <a class="back-btn" onclick="toggleForm('info-section')">Quay lại</a>
        </form>

        <!-- Form đổi mật khẩu -->
        <form id="password-form" class="<?php echo $showPasswordForm ? 'visible' : 'hidden'; ?>" method="POST" onsubmit="return validatePasswordForm()">
            <h3>Thay đổi mật khẩu</h3>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="old-pass" placeholder="Mật khẩu cũ" value="<?php echo isset($_POST['old-pass']) ? htmlspecialchars($_POST['old-pass']) : ''; ?>">
            </div>
            <div class="error-message" id="old-pass-error">Vui lòng nhập mật khẩu cũ</div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="new-pass" placeholder="Mật khẩu mới" value="<?php echo isset($_POST['new-pass']) ? htmlspecialchars($_POST['new-pass']) : ''; ?>">
            </div>
            <div class="error-message" id="new-pass-error">Vui lòng nhập mật khẩu mới</div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="confirm-pass" placeholder="Xác nhận mật khẩu mới" value="<?php echo isset($_POST['confirm-pass']) ? htmlspecialchars($_POST['confirm-pass']) : ''; ?>">
            </div>
            <div class="error-message" id="confirm-pass-error">Vui lòng xác nhận mật khẩu mới</div>
            <div class="error-message" style="color: red; margin-bottom: 10px; <?php echo $messagePass ? 'display: block;' : 'display: none;'; ?>">
                <?php echo $messagePass; ?>
            </div>
            <button type="submit" name="submit">Đổi mật khẩu</button>
            <a class="back-btn" onclick="toggleForm('info-section')">Quay lại</a>
        </form>
    </div>

    <script>
        function toggleForm(formId) {
            const sections = ['info-section', 'info-form', 'password-form'];
            sections.forEach(id => {
                const element = document.getElementById(id);
                if (id === formId) {
                    element.classList.remove('hidden');
                    element.classList.add('visible');
                } else {
                    element.classList.remove('visible');
                    element.classList.add('hidden');
                }
            });
        }

        function validateInfoForm() {
            let isValid = true;
            const inputs = {
                'fullname': 'fullname-error',
                'email': 'email-error',
                'phone': 'phone-error'
            };

            for (const [inputName, errorId] of Object.entries(inputs)) {
                const input = document.querySelector(`#info-form input[name="${inputName}"]`);
                const error = document.getElementById(errorId);
                if (!input.value.trim()) {
                    input.classList.add('error');
                    error.style.display = 'block';
                    isValid = false;
                } else {
                    input.classList.remove('error');
                    error.style.display = 'none';
                }
            }
            return isValid;
        }

        function validatePasswordForm() {
            let isValid = true;
            const inputs = {
                'old-pass': 'old-pass-error',
                'new-pass': 'new-pass-error',
                'confirm-pass': 'confirm-pass-error'
            };

            for (const [inputName, errorId] of Object.entries(inputs)) {
                const input = document.querySelector(`#password-form input[name="${inputName}"]`);
                const error = document.getElementById(errorId);
                if (!input.value.trim()) {
                    input.classList.add('error');
                    error.style.display = 'block';
                    isValid = false;
                } else {
                    input.classList.remove('error');
                    error.style.display = 'none';
                }
            }

            const oldPass = document.querySelector('#password-form input[name="old-pass"]').value;
            const newPass = document.querySelector('#password-form input[name="new-pass"]').value;
            const confirmPass = document.querySelector('#password-form input[name="confirm-pass"]').value;

            if (newPass === oldPass) {
                document.getElementById('new-pass-error').textContent = "Mật khẩu mới không được trùng với mật khẩu cũ!";
                document.getElementById('new-pass-error').style.display = 'block';
                isValid = false;
            } else if (newPass !== confirmPass) {
                document.getElementById('confirm-pass-error').textContent = "Mật khẩu xác nhận không khớp!";
                document.getElementById('confirm-pass-error').style.display = 'block';
                isValid = false;
            }

            return isValid;
        }
    </script>
</body>
</html>