document.addEventListener("DOMContentLoaded", function() {
    const buttons = document.querySelectorAll(".btns_filter button");

    buttons.forEach(button => {
        button.addEventListener("click", function() {
            console.log("Nút được nhấn:", this.textContent); // Kiểm tra có click được không

            buttons.forEach(btn => btn.classList.remove("active")); // Xóa active trên tất cả nút
            this.classList.add("active"); // Thêm active cho nút được bấm
        });
    });
});
