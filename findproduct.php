<?php
include_once "class/brand.php";
$brand = new brand();
include_once "class/page_user.php";
$page_user = new Page_user();
$result = "";
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['result'])) {
    $result = $_GET['result'];
}

// Xử lý Ajax request cho tìm kiếm
// if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
//     header('Content-Type: application/json');
//     $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
//     $search_query = isset($_GET['result']) ? $_GET['result'] : '';
//     $products_per_page = 10;

//     // Giả lập dữ liệu (thay bằng truy vấn database thực tế)
//     $total_products = 50; // Thay bằng số lượng thực tế từ database
//     $total_pages = ceil($total_products / $products_per_page);
//     $start = ($page - 1) * $products_per_page + 1;
//     $end = min($start + $products_per_page - 1, $total_products);

//     $output = '';
//     for ($i = $start; $i <= $end; $i++) {
//         $output .= '
//             <div class="product_item">
//                 <div class="pro_img">
//                     <a href="details.php">
//                         <img src="images/sanphamdemo_1.png" alt="Đồng hồ ' . $i . '">
//                     </a>
//                 </div>
//                 <div class="pro_price">
//                     <p>' . number_format(100 + $i * 10, 0, ',', '.') . 'đ</p>
//                 </div>
//                 <div class="descrise">
//                     <a href="details.php?id=">
//                         <div class="pro_name">
//                             <p>Đồng hồ ' . $i . '</p>
//                         </div>
//                     </a>
//                 </div>
//             </div>';
//     }

//     // Thay đoạn giả lập trên bằng truy vấn thực tế, ví dụ:
//     // $data = $brand->searchProducts($search_query, $page, $products_per_page);
//     // $total_pages = $data['total_pages'];
//     // while ($row = $data['products']->fetch_assoc()) {
//     //     $output .= '...'; // HTML cho sản phẩm
//     // }

//     $response = [
//         'products' => $output,
//         'total_pages' => $total_pages,
//         'current_page' => $page,
//         'total_results' => $total_products, // Số lượng kết quả tìm thấy
//         'status' => 'success'
//     ];
//     echo json_encode($response);
//     exit;
// }

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');

    // Nhận các tham số
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $search_query = isset($_GET['result']) ? $_GET['result'] : '';
    $products_per_page = 10;

    // Gọi đến hàm xử lý
    $data = $page_user->SearchProductsByKey($search_query, $page, $products_per_page);

    // Duyệt dữ liệu thực tế
    $output = '';
    if ($data['products']) {
        while ($row = $data['products']->fetch_assoc()) {
            $output .= '
                <div class="product_item">
                    <div class="pro_img">
                        <a href="details.php?id=' . $row['maSanPham'] . '">
                            <img src="admin/' . htmlspecialchars($row['hinhAnh']) . '" alt="' . htmlspecialchars($row['tenSanPham']) . '">
                        </a>
                    </div>
                    <div class="pro_price">
                        <p>' . number_format($row['giaBan'], 0, ',', '.') . 'đ</p>
                    </div>
                    <div class="descrise">
                        <a href="details.php?id=' . $row['maSanPham'] . '">
                            <div class="pro_name">
                                <p>' . htmlspecialchars($row['tenSanPham']) . '</p>
                            </div>
                        </a>
                    </div>
                </div>';
        }
    }

    // Trả dữ liệu về cho frontend
    $response = [
        'products' => $output,
        'total_pages' => $data['total_pages'],
        'current_page' => $data['current_page'],
        'total_results' => $data['total_products'],
        'status' => 'success'
    ];

    echo json_encode($response);
    exit;
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Sản Phẩm</title>
    <link rel="stylesheet" href="css/findproduct.css">
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/slider_product_by_brand.css">
    <link rel="stylesheet" href="css/brand_products.css">
    <link rel="stylesheet" href="css/pages.css">
    <script src="js/productbybrand.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include 'layout/header.php'?>
    <?php include 'layout/sidebar.php'?>

    <div class="contain">
        <h3>Tìm thấy <strong id="total-results">0</strong> kết quả cho từ khóa <strong><?php echo htmlspecialchars($result); ?></strong></h3>
        <hr style="margin: 20px 0;">
        <div class="product_new_bottom" id="product-container">
            <!-- Sản phẩm sẽ được load bằng Ajax -->
        </div>
        <div class="pagination" id="pagination">
            <!-- Phân trang sẽ được tạo động bằng JavaScript -->
        </div>
    </div>
    <?php include 'layout/footer.php'?>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const productContainer = document.getElementById("product-container");
            const paginationContainer = document.getElementById("pagination");
            const totalResults = document.getElementById("total-results");
            const searchQuery = "<?php echo htmlspecialchars($result); ?>";

            function loadProducts(page) {
                if (!searchQuery) {
                    productContainer.innerHTML = "<p>Vui lòng nhập từ khóa tìm kiếm.</p>";
                    paginationContainer.innerHTML = "";
                    totalResults.textContent = "0";
                    return;
                }

                let url = `?result=${encodeURIComponent(searchQuery)}&ajax=1&page=${page}`;

                fetch(url)
                    .then(response => {
                        if (!response.ok) throw new Error("Network response was not ok");
                        return response.json();
                    })
                    .then(data => {
                        productContainer.innerHTML = data.products || "<p>Không tìm thấy sản phẩm nào.</p>";
                        totalResults.textContent = data.total_results || 0;

                        // Tạo phân trang
                        const fragment = document.createDocumentFragment();
                        const prevBtn = document.createElement("button");
                        prevBtn.textContent = "❮";
                        prevBtn.classList.add("page-btn");
                        if (data.current_page === 1) prevBtn.disabled = true;
                        prevBtn.addEventListener("click", () => loadProducts(data.current_page - 1));
                        fragment.appendChild(prevBtn);

                        for (let i = 1; i <= data.total_pages; i++) {
                            const pageBtn = document.createElement("button");
                            pageBtn.textContent = i;
                            pageBtn.classList.add("page-btn");
                            if (i === data.current_page) pageBtn.classList.add("active");
                            pageBtn.addEventListener("click", () => loadProducts(i));
                            fragment.appendChild(pageBtn);
                        }

                        const nextBtn = document.createElement("button");
                        nextBtn.textContent = "❯";
                        nextBtn.classList.add("page-btn");
                        if (data.current_page === data.total_pages) nextBtn.disabled = true;
                        nextBtn.addEventListener("click", () => loadProducts(data.current_page + 1));
                        fragment.appendChild(nextBtn);

                        paginationContainer.innerHTML = "";
                        paginationContainer.appendChild(fragment);
                    })
                    .catch(error => {
                        console.error("Error fetching products:", error);
                        productContainer.innerHTML = "<p>Có lỗi xảy ra khi tải sản phẩm.</p>";
                    });
            }
            loadProducts(1);
        });
    </script>
</body>
</html>