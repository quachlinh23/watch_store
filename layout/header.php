<?php 
include 'lib/session.php';
Session::init();
?>
<script>
    window.addEventListener("scroll", function () {
        let headerTop = document.querySelector(".header_top");
        let header = document.querySelector(".header");

        if (window.scrollY > 50) {
            headerTop.style.height = "0";
            headerTop.style.overflow = "hidden";
        } else {
            headerTop.style.height = "25px";
        }
    });

    window.addEventListener("scroll", function () {
        let headerTop = document.querySelector(".header_top");
        
        if (window.scrollY > 50) {
            headerTop.classList.add("hidden");
        } else {
            headerTop.classList.remove("hidden");
        }
    });

    function validateSearch() {
        var searchValue = document.getElementById("searchInput").value.trim();
        
        if (searchValue === "" || searchValue === "Bạn cần tìm gì...") {
            alert("Vui lòng nhập từ khóa tìm kiếm!");
            return false; // Ngăn form gửi đi
        }
        return true; // Cho phép gửi đi nếu hợp lệ
    }

    function clearPlaceholder() {
        var input = document.getElementById("searchInput");
        if (input.value === "Bạn cần tìm gì...") {
            input.value = "";
        }
    }

    function restorePlaceholder() {
        var input = document.getElementById("searchInput");
        if (input.value.trim() === "") {
            input.value = "Bạn cần tìm gì...";
        }
    }
</script>
<?php
	$check = Session::get('customer_login');
	$name = Session::get('customer_name');
    $id_cus = Session::get("customer_id");
	
    include_once "lib/database.php";
    include_once "helpers/format.php";
    include_once "class/category.php";
    
    include_once "class/product.php";
    include_once "class/cart.php";
    include_once "class/customerlogin.php";

	$db = new database();
	$fm = new format();
	$cat = new Category();
	$pr = new product();
	$cart = new cart();
	$cus = new customerlogin();
    $userId = $_SESSION['customer_id'] ?? null;

	// if (isset($_GET['logout'])) {
	// 	Session::set('customer_login', false);
	// 	header("Location: " . $_SERVER['PHP_SELF']);
	// 	exit();
	// }

    if (isset($_GET['logout'])) {
        Session::set('customer_login', false);
        $current_page = basename($_SERVER['PHP_SELF']);
        if ($current_page === 'profile.php' || $current_page === 'order.php' || $current_page === 'cart.php'
        || $current_page === 'details.php' || $current_page === 'productbybrand.php') {
            header("Location: index.php");
        }else {
            header("Location: " . $_SERVER['PHP_SELF']);
        }
        exit();
    }
?>
<header class="header">
    <div class="header_top">
        <div class="header_top_left">
            <span>watchstore.com.vn | Hotline: 077400500</span>
        </div>
        <div class="header_top_right">
            <a href="#" class="icon fb"><i class="fab fa-facebook"></i></a>|
            <a href="#" class="icon ins"><i class="fab fa-instagram"></i></a>|
            <a href="#" class="icon yt"><i class="fab fa-youtube"></i></a>|
            <a href="#" class="icon tik"><i class="fab fa-tiktok"></i></a>
        </div>
    </div>
    <div class="header_container">
        <div class="logo_shop">
            <a href="index.php"><img src="images/Logo.png" alt="Logo"></a>
        </div>
        <nav class="menu_items">
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li class="has-dropdown">Thương hiệu
                    <div class="dropdown">
                    <?php
                        if ($brand_data = $brand->show()) {
                            while ($brand_list = $brand_data->fetch_assoc()) {
                                echo '<a href="productbybrand.php?id=' .$brand_list['id_thuonghieu']. '">' .$brand_list['tenThuongHieu']. '</a><br>';
                            }
                        }
                    ?>
                    </div>
                </li>
                <li><a href="#">Tin tức</a></li>
                <li><a href="#">Thương hiệu</a></li>
                <li><a href="contact.php">Hỗ trợ</a></li>
            </ul>
        </nav>
        <div class="header-right">
            <div class="search">
                <form method="get" action="findproduct.php" onsubmit="return validateSearch()">
                    <button type="submit" class="search-btn">
                        <i class="fa fa-search"></i>
                    </button>
                    <input id="searchInput" type="text" name="result" value="Bạn cần tìm gì..."
                        onfocus="this.value = '';"
                        onblur="if (this.value == '') {this.value = 'Bạn cần tìm gì...';}">
                </form>
            </div>

            
            <?php
                if ($check){
                    echo '
                        <div class="header_login">
                            <a href="">
                                <i class="fa-regular fa-user"></i>
                                <span>Chào ' . htmlspecialchars($name) . ' </span>
                            </a>
                            <div class="login_dropdown">
                                <a href="profile.php"><i class="fa-solid fa-user"></i>  Thông tin cá nhân</a>
                                <a href="order.php"><i class="fa-solid fa-receipt"></i>   Đơn hàng</a>
                                <a href="?logout=true"><i class="fa-solid fa-right-from-bracket"></i>  Đăng xuất</a>
                            </div>
                        </div>';
                }
                else{
                    echo '<div class="header_login">
                            <a href="login.php">
                                <i class="fa-regular fa-user"></i>
                                <span>Đăng Nhập</span>
                            </a>
                        </div>';
                }
            ?>
            
            <div class="header_cart">
                <?php
                    $link = "";
                    if ($check){
                        $link = "cart.php";
                        $idgiohang = $cart->getcartidbycustomer($userId);
                        $soluong = $cart->countProductCartByUser($idgiohang);
                    }else{
                        $soluong = 0;
                    }
                ?>
                <a href="<?php echo $link; ?>" id="cart-link" title="Xem Giỏ Hàng" rel="nofollow">
                    <i class="fa fa-shopping-cart"></i>
                    <?php echo ($soluong > 0) ? '<span class="cart-count">' . $soluong . '</span>' : ''; ?>
                </a>
                <script>
                    document.getElementById('cart-link').addEventListener('click', function(event) {
                        <?php if (!$check) { ?>
                            event.preventDefault();
                            Swal.fire({
                                icon: 'error',
                                title: 'Bạn chưa đăng nhập',
                                text: 'Vui lòng đăng nhập để truy cập giỏ hàng',
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'custom-popup'
                                }
                            });
                        <?php } ?>
                    });
                </script>
            </div>
        </div>
    </div>
</header>