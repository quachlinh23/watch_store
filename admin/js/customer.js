window.onload = function () {
    document.getElementById("searchdata").addEventListener("input", function () {
        if (this.value.trim() === "") {
            window.location.href = window.location.pathname; // Reload trang khi ô tìm kiếm trống
        }
    });
};