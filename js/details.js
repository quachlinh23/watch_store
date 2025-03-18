document.addEventListener("DOMContentLoaded", function() {
    const zoomBtn = document.querySelector(".zoom-btn");
    const subZoom = document.getElementById("subZoom");
    const zoomedImage = document.getElementById("zoomedImage");
    const mainImage = document.getElementById("mainImage");
    const closeZoom = document.querySelector(".close_zoom");

    // Khi bấm vào nút phóng to
    zoomBtn.addEventListener("click", function() {
        zoomedImage.src = mainImage.src; // Gán ảnh chính vào ảnh phóng to
        subZoom.style.display = "flex"; // Hiện phần phóng to
    });

    // Khi bấm vào nút đóng
    closeZoom.addEventListener("click", function() {
        subZoom.style.display = "none"; // Ẩn phần phóng to
    });

    // Khi bấm ra ngoài ảnh thì đóng
    subZoom.addEventListener("click", function(e) {
        if (e.target === subZoom) {
            subZoom.style.display = "none";
        }
    });
});

document.addEventListener("DOMContentLoaded", function() {
    const addToCartBtn = document.querySelector(".add-to-cart");
    const mainImage = document.getElementById("mainImage");

    addToCartBtn.addEventListener("click", function(event) {
        event.preventDefault(); // Ngăn chặn reload trang
        
        // Clone ảnh để tạo hiệu ứng bay vào giỏ hàng
        let cloneImage = mainImage.cloneNode(true);
        cloneImage.classList.add("shrinking");
        cloneImage.style.position = "fixed";
        cloneImage.style.top = mainImage.getBoundingClientRect().top + "px";
        cloneImage.style.left = mainImage.getBoundingClientRect().left + "px";
        cloneImage.style.width = mainImage.width + "px";
        cloneImage.style.zIndex = "1000";
        document.body.appendChild(cloneImage);

        // Sau khi hoàn thành hiệu ứng, xóa ảnh clone
        setTimeout(() => {
            cloneImage.remove();
        }, 500);
    });
});


function openTab(tabId) {
    // Ẩn tất cả nội dung tab
    document.querySelectorAll(".tab-content").forEach(tab => {
        tab.classList.remove("active");
    });

    // Xóa class 'active' khỏi tất cả nút tab
    document.querySelectorAll(".tab-btn").forEach(btn => {
        btn.classList.remove("active");
    });

    // Hiển thị tab được chọn
    document.getElementById(tabId).classList.add("active");

    // Đánh dấu nút đang được chọn
    event.currentTarget.classList.add("active");
}

function updateQuantity(change) {
    let qtyInput = document.getElementById("quantity");
    let currentValue = parseInt(qtyInput.value);

    // Tăng hoặc giảm số lượng, đảm bảo không nhỏ hơn min (1)
    let newValue = currentValue + change;
    if (newValue < 1) newValue = 1; // Không cho số lượng nhỏ hơn 1

    qtyInput.value = newValue;
}

document.addEventListener("DOMContentLoaded", function () {
    const thumbnailsContainer = document.getElementById("thumbnails");
    const thumbnails = document.querySelectorAll(".thumb");
    const mainImage = document.getElementById("mainImage");
    const prevBtn = document.querySelector(".prev-btn");
    const nextBtn = document.querySelector(".next-btn");

    let currentIndex = 0; // Chỉ mục bắt đầu hiển thị

    function updateMainImage(index) {
        mainImage.src = thumbnails[index].src;
    }

    function updateVisibleThumbnails() {
        thumbnails.forEach((thumb, index) => {
            if (index >= currentIndex && index < currentIndex + 4) {
                thumb.style.display = "inline-block";
            } else {
                thumb.style.display = "none";
            }
        });
    }

    // Xử lý khi nhấn vào thumbnail
    thumbnails.forEach((thumb, index) => {
        thumb.addEventListener("click", () => {
            updateMainImage(index);
        });
    });

    // Xử lý khi nhấn Next
    nextBtn.addEventListener("click", () => {
        if (currentIndex + 4 < thumbnails.length) {
            currentIndex++;
            updateVisibleThumbnails();
        }
    });

    // Xử lý khi nhấn Previous
    prevBtn.addEventListener("click", () => {
        if (currentIndex > 0) {
            currentIndex--;
            updateVisibleThumbnails();
        }
    });

    // Hiển thị ban đầu
    updateVisibleThumbnails();
});