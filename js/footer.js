document.addEventListener("DOMContentLoaded", function () {
    const toTop = document.getElementById("totop");

    // Ẩn ban đầu
    toTop.style.display = "none";

    window.addEventListener("scroll", function () {
        if (window.scrollY > 200) {
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
