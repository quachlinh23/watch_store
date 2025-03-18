
document.addEventListener("DOMContentLoaded", function () {
    var modal = document.getElementById("modalPermission");
    var openPermissionBtns = document.querySelectorAll(".btn-permission");
    var checkboxes = document.querySelectorAll(".permission");
    var inputPermissionValue = document.getElementById("permissionValue");
    var inputAccountId = document.getElementById("idtaikhoan");

    openPermissionBtns.forEach(function (btn) {
        btn.addEventListener("click", function (event) {
            event.preventDefault();
            var accountId = btn.getAttribute("data-id");
            var permissionValue = parseInt(btn.getAttribute("data-quyen"));

            // Hiển thị modal
            modal.style.display = "flex";

            // Gán ID tài khoản vào input ẩn
            inputAccountId.value = accountId;

            // Đặt lại checkbox trước khi cập nhật
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = false;
            });

            // Lặp qua các checkbox và kiểm tra quyền
            checkboxes.forEach(function (checkbox) {
                var value = parseInt(checkbox.value);
                if ((permissionValue & value) !== 0) {
                    checkbox.checked = true;
                }
            });

            // Cập nhật giá trị tổng quyền vào input ẩn
            inputPermissionValue.value = permissionValue;
        });
    });

    // Cập nhật giá trị khi thay đổi checkbox
    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener("change", function () {
            var totalPermission = 0;
            checkboxes.forEach(function (cb) {
                if (cb.checked) {
                    totalPermission += parseInt(cb.value);
                }
            });
            inputPermissionValue.value = totalPermission;
        });
    });

    // Đóng modal khi nhấn vào nút hủy
    document.querySelector("#modalPermission .cancel").addEventListener("click", function () {
        modal.style.display = "none";
    });

    // Đóng modal khi nhấn vào dấu "x"
    document.querySelector("#modalPermission .close").addEventListener("click", function () {
        modal.style.display = "none";
    });

    // Đóng modal khi nhấn bên ngoài
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
});