//Hiển thị logo của thương hiệu
document.addEventListener("DOMContentLoaded", function () {
    let brands = [];
    let currentPage = 1;
    const brandsPerPage = 12;
    const colsPerRow = 6;
    let totalPages = 1;

    function fetchBrands() {
        fetch("brands.php")
            .then(response => response.json())
            .then(data => {
                brands = data;
                totalPages = Math.ceil(brands.length / brandsPerPage);
                renderPage(currentPage);
            })
            .catch(error => console.error("Lỗi tải thương hiệu:", error));
    }

    function renderPage(page) {
        const brandTableBody = document.getElementById("brandTableBody");
        brandTableBody.innerHTML = "";

        const startIdx = (page - 1) * brandsPerPage;
        const endIdx = Math.min(startIdx + brandsPerPage, brands.length);

        let tableHTML = "";
        for (let i = startIdx; i < endIdx; i += colsPerRow) {
            tableHTML += "<tr>";
            for (let j = i; j < i + colsPerRow && j < endIdx; j++) {
                tableHTML += `<td><img src="${brands[j].image}" alt="${brands[j].name}" title="${brands[j].name}"></td>`;
            }
            tableHTML += "</tr>";
        }

        brandTableBody.innerHTML = tableHTML;
    }
    
    function updateButtons() {
        prevButton.style.display = currentPage > totalPages ? "block" : "none";
        nextButton.style.display = currentPage < totalPages ? "block" : "none";
    }
    document.getElementById("prevPage").addEventListener("click", function () {
        if (currentPage > 1) {
            currentPage--;
            renderPage(currentPage);
        }
    });

    document.getElementById("nextPage").addEventListener("click", function () {
        if (currentPage < totalPages) {
            currentPage++;
            renderPage(currentPage);
        }
    });
    fetchBrands();
});
// Hiển thị từng khối section ở header
document.addEventListener("DOMContentLoaded", function () {
    const sections = document.querySelectorAll(".hidden");

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("show"); // Hiển thị khi vào vùng nhìn thấy
            } else {
                entry.target.classList.remove("show"); // Ẩn khi ra khỏi vùng nhìn thấy
            }
        });
    }, {
        threshold: 0.2 // Khi 20% phần tử xuất hiện trên màn hình thì kích hoạt
    });

    sections.forEach(section => observer.observe(section));
});