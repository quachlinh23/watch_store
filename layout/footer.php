<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-column">
                <h3><i class="fa fa-headset"></i> Tổng Đài Hỗ Trợ</h3>
                <ul>
                    <li><i class="fa fa-phone"></i>&nbsp;&nbsp;Gọi mua: <strong><pre> 0794 628 40</pre></strong></li>
                    <li><i class="fa fa-comments"></i>&nbsp;&nbsp;Khiếu nại: <strong><pre> 0794 628 40</pre></strong></li>
                    <li><i class="fa fa-tools"></i>&nbsp;&nbsp;Bảo hành: <strong><pre> 0794 628 40</pre></strong></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3><i class="fa fa-store"></i> Về chúng tôi</h3>
                <ul>
                    <li><a href="infostore.php">Giới thiệu</a></li>
                    <li><a href="infostore.php">Tuyển dụng</a></li>
                    <li><a href="contact.php">Góp ý, bảo hành</a></li>
                    <li><a href="infostore.php">Cửa hàng gần đây</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3><i class="fa fa-info-circle"></i> Thông tin khác</h3>
                <ul>
                    <li><a href="contact.php">Chính sách bảo hành</a></li>
                    <li><a href="contact.php">Điều khoản sử dụng</a></li>
                    <li><a href="contact.php">Hướng dẫn mua hàng</a></li>
                    <li><a href="contact.php">Liên hệ</a></li>
                </ul>
            </div>
            
            <div class="footer-column certification">
                <h3><i class="fa fa-award"></i> Chứng nhận</h3>
                <div class="certification-list">
                    <img src="images/logobocongthuong.png" alt="Bộ Công Thương">
                    <img src="images/logonoikhongvoihanggia.png" alt="Nói không với hàng giả">
                    <img src="images/logoiso.png" alt="ISO">
                    <img src="images/sgu.png" alt="SGU">
                </div>
            </div>
        </div>
        <div class="line"></div>
        <div class="footer-bottom">
            <p>© 2025-2026 <a href="infostore.php">Nhóm 13</a> - Thiết kế bởi: Quách Hồng Linh, Dương Thanh Luận, Trần Thị Khánh Như, Nguyễn Đình Vũ.</p>
            <div class="social-icons">
                <a href="infostore.php" class="social-icon facebook"><i class="fab fa-facebook"></i></a>
                <a href="infostore.php" class="social-icon instagram"><i class="fab fa-instagram"></i></a>
                <a href="infostore.php" class="social-icon youtube"><i class="fab fa-youtube"></i></a>
                <a href="infostore.php" class="social-icon tiktok"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
    </div>
    <a href="#" id="totop" title="Lên đầu trang">
        <i class="fa-solid fa-chevron-up"></i>
    </a>
    <a href="contact.php" id="contactButton" style="display: block;">
		<i class="fa fa-phone-alt icon_contact"></i>
	</a>
</footer>
<script>
    document.addEventListener("DOMContentLoaded", function () {
    const toTop = document.getElementById("totop");

    // Ẩn ban đầu
    toTop.style.display = "none";

    window.addEventListener("scroll", function () {
        if (window.scrollY > 200 || window.scrollY > 100) {
            toTop.style.display = "flex"; // Hiển thị khi cuộn xuống
        } else {
            toTop.style.display = "none"; // Ẩn nếu cuộn lên đầu
        }
    });

    // Sự kiện click để cuộn lên đầu
    toTop.addEventListener("click", function (e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: "smooth" });
    });
});
</script>