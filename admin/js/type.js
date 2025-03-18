document.addEventListener("DOMContentLoaded", function () {
    var modal = document.getElementById("modal");
    var closeBtn = document.querySelector(".close");
    var cancelBtn = document.querySelector(".cancel");
    var errorTen = document.getElementById("errorten");
    var errorImage = document.getElementById("erroranh");
    let productTypeName = document.getElementById("brand_name");
    let imageInput = document.getElementById("image");

    document.getElementById("openModal").addEventListener("click", function () {
        modal.style.display = "flex";
    });

    closeBtn.addEventListener("click", function () {
        modal.style.display = "none";
    });

    cancelBtn.addEventListener("click", function () {
        modal.style.display = "none";
        errorTen.style.display = "none";
        errorImage.style.display = "none";
        document.getElementById("brand_name").value = "";
        document.getElementById("image").value = "";
    });

    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("formSlider").addEventListener("submit", function (event) {

        let isValid = true;

        let slideName = document.getElementById("brand_name");
        let errorTen = document.getElementById("errorten");

        // Kiểm tra lỗi nhập tên slide
        if (slideName.value.trim() === "") {
            errorTen.textContent = "Vui lòng nhập tên loại sản phẩm";
            errorTen.style.color = "red";
            errorTen.style.display = "block";
            isValid = false;
        } else {
            errorTen.style.display = "none";
        }
        // Nếu không hợp lệ, ngăn chặn form gửi đi
        if (!isValid) {
            event.preventDefault();
        }
    });
});