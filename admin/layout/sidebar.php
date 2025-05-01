<?php
    $quyen = Session::get('roll');
    function hasPermission($quyen, $bit) {
        return ($quyen & (1 << ($bit - 1))) != 0;
    }
?>

<nav class="sidebar">
    <ul>
        <!-- Bảng điều khiển luôn hiển thị -->
        <li onclick="showPage('daboardAdmin.php', this)">📊 Bảng điều khiển</li>
        <?php
            if (hasPermission($quyen, 1))
                echo '<li onclick="showPage(\'employee_manage.php\', this)"><i class="fa-solid fa-user-tie"></i> Nhân viên</li>';
            
            if (hasPermission($quyen, 2))
                echo '<li onclick="showPage(\'customer_manage.php\', this)"><i class="fa-solid fa-users"></i> Khách hàng</li>';
            
            if (hasPermission($quyen, 3))
                echo '<li onclick="showPage(\'brand_manage.php\', this)"><i class="fa-solid fa-tags"></i> Thương hiệu</li>';

            if (hasPermission($quyen, 4))
                echo '<li onclick="showPage(\'product_type_manage.php\', this)"><i class="fa-solid fa-boxes-stacked"></i> Loại sản phẩm</li>';

            if (hasPermission($quyen, 5))
                    echo '<li onclick="showPage(\'supplier_manage.php\', this)"><i class="fa-solid fa-truck"></i> Nhà cung cấp</li>';
            
            if (hasPermission($quyen, 6))
                echo '<li onclick="showPage(\'quanlynhaphang.php\', this)"><i class="fa-solid fa-file-import"></i> Nhập hàng</li>';

            if (hasPermission($quyen, 7))
                echo '<li onclick="showPage(\'product_manage.php\', this)"><i class="fa-solid fa-box"></i> Sản phẩm</li>';

            if (hasPermission($quyen, 8))
                echo '<li onclick="showPage(\'quanlyhoadonban.php\', this)"><i class="fa-solid fa-file-invoice-dollar"></i> Đơn hàng</li>';

            if (hasPermission($quyen, 9))
                echo '<li onclick="showPage(\'slider_manage.php\', this)"><i class="fa-solid fa-sliders"></i> Slider</li>';
            
            if (hasPermission($quyen, 10))
                echo '<li onclick="showPage(\'contact_manage.php\', this)"><i class="fa-solid fa-sliders"></i> CSKH</li>';
        ?>
        <!-- <li onclick="showPage('contact_manage.php', this)"><i class="fa-solid fa-headset"></i> CSKH</li> -->
        <li onclick="showPage('ft.php', this)"><i class="fa-solid fa-gear"></i> Thiết lập</li>
    </ul>
</nav>