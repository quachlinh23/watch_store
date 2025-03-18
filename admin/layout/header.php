<header class="header">
    <div class="logo_shop">
        <img src="images/Logo.png" alt="Logo Shop">
        <p class="slogan">Mỗi Giây Phút Đều Quý Giá, Mỗi Chiếc Đồng Hồ Đều Kể Một Câu Chuyện.</p>
    </div>
    <div class="logout_content">
        <span>
            <?php
                $username = Session::get('fullname');
            ?>
            <i class="fa-solid fa-user"></i>  Xin Chào, <?php echo $username ?> | 
            <i class="fa-solid fa-right-from-bracket"></i>
            <?php
                if (isset($_GET['action'])  && $_GET['action'] == 'logout'){
                    Session::destroy();
                } 
            ?>
            <a class="logout" href="?action=logout">Đăng xuất</a> | 
            <i class="fa-solid fa-globe"></i> <a href="../index.php" class="visit" onclick="logoutAndRedirect()">Visit website</a>
            <script>
                function logoutAndRedirect() {
                    fetch("?action=logout")
                        .then(() => {
                            window.location.href = "../index.php";
                        });
                    return false;
                }
            </script>
        </span>
    </div>
</header>