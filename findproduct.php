<?php
    include_once "class/brand.php";
    $brand = new brand();
    $result = "";
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['result'])) {
        $result = $_GET['result'];
    }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Sản Phẩm</title>
    <link rel="stylesheet" href="test.css">
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/product.css">
    <script src="js/productbybrand.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include 'layout/header.php'?>

    <section class="slide-container" id="slide-container" style="margin-top: 50px;">
        <button class="btn_close" onclick="closeSlide()">X</button>
        <div class="btns_pre" onclick="prevSlide()">&#10094;</div>
        <div class="slide-wrapper">
            <div class="slide_left">
                <img id="slideLeft" src="images/slide_1.png" alt="Slide Left">
            </div>
            <div class="slide_right">
                <img id="slideRight" src="images/slide_2.png" alt="Slide Right">
            </div>
        </div>
        <div class="btns_next" onclick="nextSlide()">&#10095;</div>
    </section>

    <div class="contain">
        <h3>Tìm thấy <strong>10</strong> kết quả cho từ khóa <strong><?php echo $result?></strong></h3>
        <hr style="margin: 20px 0;">
        <div id="productContainer" class="product_brands_right_bottom"></div>

        <div class="pagination">
            <button id="prevPage" onclick="changePage(-1)" disabled>Trước</button>
            <span id="pageNumber">1</span> / <span id="totalPages"></span>
            <button id="nextPage" onclick="changePage(1)">Tiếp</button>
        </div>
        
    </div>
    <?php include 'layout/footer.php'?>
<script>
    const products = Array.from({ length: 50 }, (_, i) => ({
        name: `Đồng hồ ${i + 1}`,
        price: `${(100 + i * 10).toLocaleString()}đ`,
        img: "images/sanphamdemo_1.png"
    }));

    let currentPage = 1;
    const productsPerPage = 15;
    const totalPages = Math.ceil(products.length / productsPerPage); // Tổng số trang

    function displayProducts() {
        const start = (currentPage - 1) * productsPerPage;
        const end = start + productsPerPage;
        const visibleProducts = products.slice(start, end);

        const productContainer = document.getElementById("productContainer");
        productContainer.innerHTML = "";

        visibleProducts.forEach(product => {
            productContainer.innerHTML += `
                <div class="product_1">
                    <div class="pro_img">
                        <a href="details.php"><img src="${product.img}" alt="${product.name}"></a>
                    </div>
                    <div class="descrise">
                        <div class="pro_name">
                            <p>${product.name}</p>
                        </div>
                        <div class="pro_price">
                            <p>${product.price}</p>
                        </div>
                    </div>
                </div>
            `;
        });

        document.getElementById("pageNumber").innerText = currentPage;
        document.getElementById("totalPages").innerText = totalPages; // Hiển thị tổng số trang
        document.getElementById("prevPage").disabled = currentPage === 1;
        document.getElementById("nextPage").disabled = currentPage === totalPages;
    }

    function changePage(step) {
        currentPage += step;
        displayProducts();
    }

    displayProducts();

</script>
</body>
</html>