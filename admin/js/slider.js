document.addEventListener("DOMContentLoaded", function () {
    var modal = document.getElementById("modal");
    var closeBtn = document.querySelector(".close");
    var cancelBtn = document.querySelector(".cancel");
    var errorTen = document.getElementById("errorten");
    var errorImage = document.getElementById("erroranh");
    let slideName = document.getElementById("slide_name");
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
        document.getElementById("slide_name").value = "";
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

        let slideName = document.getElementById("slide_name");
        let errorTen = document.getElementById("errorten");

        let imageInput = document.getElementById("image");
        let errorImage = document.getElementById("erroranh");

        // Kiểm tra lỗi nhập tên slide
        if (slideName.value.trim() === "") {
            errorTen.textContent = "Vui lòng nhập tên slide";
            errorTen.style.color = "red";
            errorTen.style.display = "block";
            isValid = false;
        } else {
            errorTen.style.display = "none";
        }

        // Kiểm tra lỗi chọn ảnh
        if (imageInput.files.length === 0) {
            errorImage.textContent = "Vui lòng chọn hình ảnh";
            errorImage.style.color = "red";
            errorImage.style.display = "block";
            isValid = false;
        } else {
            errorImage.style.display = "none";
        }

        // Nếu không hợp lệ, ngăn chặn form gửi đi
        if (!isValid) {
            event.preventDefault();
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    var updateModal = document.getElementById("modalupdate");
    var closeBtns = document.querySelectorAll(".close");
    var cancelBtns = document.querySelectorAll(".cancel");
    var editButtons = document.querySelectorAll(".btn-edit");
    
    editButtons.forEach(function (editBtn) {
        editBtn.addEventListener("click", function (event) {
            event.preventDefault(); // Ngăn chặn reload trang

            var row = editBtn.closest("tr");
            var slideName = row.cells[1].textContent.trim();
            var imageSrc = row.cells[2].querySelector("img").src;
            var slideId = editBtn.getAttribute("data-id");

            document.getElementById("modalupdate").style.display = "flex";
            document.getElementById("slide_name_update").value = slideName;
            document.getElementById("image_preview").src = imageSrc;
            document.getElementById("slider_id").value = slideId;
        });
    });

    closeBtns.forEach(btn => btn.addEventListener("click", function () {
        updateModal.style.display = "none";
    }));

    cancelBtns.forEach(btn => btn.addEventListener("click", function () {
        updateModal.style.display = "none";
    }));

    window.addEventListener("click", function (event) {
        if (event.target === updateModal) {
            updateModal.style.display = "none";
        }
    });
});

window.onload = function () {
    document.getElementById("searchdata").addEventListener("input", function () {
        if (this.value.trim() === "") {
            window.location.href = window.location.pathname; // Reload trang khi ô tìm kiếm trống
        }
    });
};