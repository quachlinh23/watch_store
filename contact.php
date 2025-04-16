<?php
	include_once "class/brand.php";
    include_once "class/contact.php";
	$brand = new brand();
    $contact = new contact();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        $fullname = $_POST['name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $message = $_POST['message'];

        $result = $contact->add($fullname, $email, $phone, $message);
    }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Store</title>
    <link rel="stylesheet" href="styles.css">
	<link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/contact.css">
	<link rel="stylesheet" href="css/footer.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    
</head>
<body>
	<?php
        include 'layout/header.php';
    ?>
    <section class="contact-container">
        <div class="contact-info">
            <h2>Thông Tin Liên Hệ</h2>
            <p><strong>Địa chỉ:</strong> Đại đại đi</p>
            <p><strong>Điện thoại:</strong> 077400500</p>
            <p><strong>Email:</strong> watchstore@gmail.com</p>
            <p><strong>Theo dõi:</strong> <a href="#">Facebook</a>, <a href="#">Twitter</a>, 
            <a href="#">Zalo</a>, <a href="#">Tiktok</a></p>
        </div>
        <div class="contact-form">
            <h2>Liên Hệ Chúng Tôi</h2>
            <form action="" method="post" onsubmit="validateForm(event)">
                <label for="name" style="margin-bottom: 10px;">Họ và Tên <span style="color: red;">*</span></label>
                <input type="text" id="name" name="name" placeholder="Nhập họ và tên của bạn">
                <span id="emptyName" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng nhập họ tên</span>

                <label for="email" style="margin-bottom: 10px;">E-mail</label>
                <input type="email" id="email" name="email" placeholder="Nhập email của bạn">

                <label for="phone" style="margin-bottom: 10px;">Số điện thoại <span style="color: red;">*</span></label>
                <input type="tel" id="phone" name="phone" placeholder="Nhập số điện thoại của bạn">
                <span id="emptyPhone" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng nhập số điện thoại</span>

                <label for="message" style="margin-bottom: 10px;">Nội dung <span style="color: red;">*</span></label>
                <textarea id="message" rows="5" name="message" placeholder="Nhập nội dung liên hệ"></textarea>
                <span id="emptyMessage" style="display: none; color: red; font-weight: bold; text-align: left; margin-bottom: 10px;">Vui lòng nhập nội dung hỗ trợ</span>

                <button type="submit" name="submit">Gửi Tin Nhắn</button>
            </form>
        </div>
    </section>

	<?php include 'layout/footer.php';?>
    <script>
        function validateForm(event) {
            let isValid = true;

            const name = document.getElementById('name');
            const phone = document.getElementById('phone');
            const message = document.getElementById('message');

            const emptyName = document.getElementById('emptyName');
            const emptyPhone = document.getElementById('emptyPhone');
            const emptyMessage = document.getElementById('emptyMessage');

            emptyName.style.display = 'none';
            emptyPhone.style.display = 'none';
            emptyMessage.style.display = 'none';

            if (name.value.trim() === '') {
                emptyName.style.display = 'block';
                isValid = false;
            }

            if (phone.value.trim() === '') {
                emptyPhone.style.display = 'block';
                isValid = false;
            }

            if (message.value.trim() === '') {
                emptyMessage.style.display = 'block';
                isValid = false;
            }

            // Nếu hợp lệ, cho phép gửi form
            if (!isValid) {
                event.preventDefault();
            }
        }
    </script>
</body>
</html>