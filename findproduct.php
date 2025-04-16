<?php
ob_start();
session_start();
include_once "class/brand.php";
include_once "class/category.php";
include_once "class/page_user.php";

try {
    $brand = new brand();
    $category = new category();
    $page_user = new Page_user();
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    error_log("Initialization error: " . $e->getMessage());
    exit;
}

$result = "";
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['result'])) {
    $result = $_GET['result'];
}

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');

    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $search_query = isset($_GET['result']) ? trim($_GET['result']) : '';
    $products_per_page = 10;

    $filters = [];
    if (isset($_GET['category'])) {
        $filters['category'] = array_map('intval', explode(',', $_GET['category']));
    }
    if (isset($_GET['brand'])) {
        $filters['brand'] = array_map('intval', explode(',', $_GET['brand']));
    }
    if (isset($_GET['price_min']) && isset($_GET['price_max'])) {
        $filters['min_price'] = floatval($_GET['price_min']);
        $filters['max_price'] = floatval($_GET['price_max']);
    }

    try {
        $data = $page_user->SearchProductsByKey($search_query, $page, $products_per_page, $filters);
        $output = '';
        if ($data['products'] && $data['products']->num_rows > 0) {
            while ($row = $data['products']->fetch_assoc()) {
                $formatted_price = number_format($row['giaBan'], 0, ',', '.');
                $output .= '
                    <div class="product_item">
                        <div class="pro_img">
                            <a href="details.php?id=' . $row['maSanPham'] . '">
                                <img src="admin/' . htmlspecialchars($row['hinhAnh']) . '" alt="' . htmlspecialchars($row['tenSanPham']) . '">
                            </a>
                        </div>
                        <div class="pro_price">
                            <p class="price">' . $formatted_price . '<span>đ</span></p>
                        </div>
                        <div class="descrise">
                            <a href="details.php?id=' . $row['maSanPham'] . '">
                                <div class="pro_name"><p>' . htmlspecialchars($row['tenSanPham']) . '</p></div>
                            </a>
                        </div>
                    </div>';
            }
        } else {
            $output = '<p>Không tìm thấy sản phẩm nào cho từ khóa "' . htmlspecialchars($search_query) . '".</p>';
        }

        $response = [
            'products' => $output,
            'total_pages' => $data['total_pages'] ?? 1,
            'current_page' => $data['current_page'] ?? $page,
            'total_results' => $data['total_products'] ?? 0,
            'status' => 'success',
            'debug' => ['keyword' => $search_query]
        ];
    } catch (Exception $e) {
        error_log("Ajax error: " . $e->getMessage());
        $response = [
            'status' => 'error',
            'message' => $e->getMessage(),
            'debug' => ['keyword' => $search_query]
        ];
    }

    ob_clean();
    echo json_encode($response);
    ob_end_flush();
    exit;
}

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Store</title>
    <link rel="stylesheet" href="css/findproduct.css">
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/slider_product_by_brand.css">
    <link rel="stylesheet" href="css/brand_products.css">
    <link rel="stylesheet" href="css/pages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include 'layout/header.php'?>
    <?php include 'layout/sidebar.php'?>

    <div class="contain">
        <h3>Tìm thấy <strong id="total-results">0</strong> kết quả cho từ khóa <strong><?php echo htmlspecialchars($result); ?></strong></h3>
        <div class="btn_filter">
            <button id="btnOpenFilter">
                <span class="icon-container">
                    <i class="fas fa-filter"></i>
                    <i class="fas fa-check-circle"></i>
                </span>
                Lọc
            </button>
        </div>
        <div class="modal-overlay" id="modalOverlay"></div>
        <div id="filterModal" class="modal">
            <div class="modal-content">
                <div class="header-modal">
                    <h3>Lọc sản phẩm</h3>
                    <span class="close" id="closeModal">×</span>
                </div>
                <div class="choice">
                    <h4>Đã chọn:</h4>
                    <div id="selectedFilters" class="selected-filters"></div>
                </div>
                <form class="modal-body" action="#" method="get">
                    <div class="filter-section">
                        <h4>Loại sản phẩm</h4>
                        <div class="border"></div>
                        <div class="product-grid">
                            <?php
                                $categoryList = $category->get_all_type();
                                if ($categoryList) {
                                    while ($result = $categoryList->fetch_assoc()) {
                                        echo '<button type="button" class="product-btn category-btn" data-type="' . $result['id_loai'] . '">' . $result['tenLoai'] . '</button>';
                                    }
                                }
                            ?>
                        </div>
                    </div>
                    <div class="filter-section">
                        <h4>Thương hiệu</h4>
                        <div class="border"></div>
                        <div class="product-grid">
                            <?php
                                $brandList = $brand->show();
                                if ($brandList) {
                                    while ($result = $brandList->fetch_assoc()) {
                                        echo '<button type="button" class="product-btn brand-btn" data-type="' . $result['id_thuonghieu'] . '">' . $result['tenThuongHieu'] . '</button>';
                                    }
                                }
                            ?>
                        </div>
                    </div>
                    <div class="filter-section">
                        <h4>Giá sản phẩm</h4>
                        <div class="border"></div>
                        <div class="product-grid">
                            <button type="button" class="price-btn" data-price="0-500000">Dưới 500.000đ</button>
                            <button type="button" class="price-btn" data-price="500000-2500000">Từ 500.000đ - Dưới 2.500.000đ</button>
                            <button type="button" class="price-btn" data-price="2500000-5000000">Từ 2.500.000đ - 5.000.000đ</button>
                            <button type="button" class="price-btn" data-price="5000000-999999999">Trên 5.000.000đ</button>
                        </div>
                    </div>
                    <div class="btn-control">
                        <button type="button" class="btn_Cancle">Hủy</button>
                        <button type="button" class="btn_Filter" id="applyFilter">Áp dụng</button>
                    </div>
                </form>
            </div>
        </div>
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
            const modal = document.getElementById("filterModal");
            const overlay = document.getElementById("modalOverlay");
            const selectedContainer = document.getElementById("selectedFilters");
            const selectedFilters = new Map();

            const btnClearAll = document.createElement("button");
            btnClearAll.innerText = "Xóa Tất Cả";
            btnClearAll.classList.add("btn-clear-all");
            btnClearAll.style.display = "none";
            btnClearAll.addEventListener("click", clearAllFilters);
            selectedContainer.parentNode.appendChild(btnClearAll);

            function toggleModal(show) {
                modal.style.display = overlay.style.display = show ? "block" : "none";
                document.body.classList.toggle("modal-open", show);
            }

            function toggleFilter(element) {
                const isPrice = element.classList.contains("price-btn");
                const isCategory = element.classList.contains("category-btn");
                const isBrand = element.classList.contains("brand-btn");
                const key = isPrice ? "price" : (isCategory ? `cat_${element.dataset.type}` : `brand_${element.dataset.type}`);
                const value = isPrice ? element.dataset.price : element.dataset.type;
                const text = element.innerText.trim();

                if (isPrice) {
                    if (selectedFilters.has("price")) {
                        document.querySelectorAll(".price-btn.selected").forEach(el => el.classList.remove("selected"));
                        selectedFilters.delete("price");
                    }
                    if (!element.classList.contains("selected")) {
                        selectedFilters.set("price", value + "|" + text);
                        element.classList.add("selected");
                    }
                } else {
                    selectedFilters.has(key) ? selectedFilters.delete(key) : selectedFilters.set(key, text);
                    element.classList.toggle("selected");
                }

                updateSelectedFilters();
            }

            function updateSelectedFilters() {
                selectedContainer.innerHTML = selectedFilters.size
                    ? [...selectedFilters].map(([key, val]) => {
                        const [value, text] = key === "price" ? val.split("|") : [key, val];
                        return `<button class="filter-tag">${text}
                                    <span class="remove-tag" data-key="${key}">×</span>
                                </button>`;
                    }).join("")
                    : "Không có bộ lọc nào";
                
                btnClearAll.style.display = selectedFilters.size > 1 ? "block" : "none";

                document.querySelectorAll(".remove-tag").forEach(tag =>
                    tag.addEventListener("click", () => removeFilter(tag.dataset.key))
                );
            }

            function removeFilter(key) {
                selectedFilters.delete(key);
                if (key === "price") {
                    document.querySelectorAll(".price-btn").forEach(el => el.classList.remove("selected"));
                } else if (key.startsWith("cat_")) {
                    document.querySelectorAll(`.category-btn[data-type="${key.replace('cat_', '')}"]`).forEach(el => el.classList.remove("selected"));
                } else if (key.startsWith("brand_")) {
                    document.querySelectorAll(`.brand-btn[data-type="${key.replace('brand_', '')}"]`).forEach(el => el.classList.remove("selected"));
                }
                updateSelectedFilters();
                loadProducts(1);
            }

            function clearAllFilters() {
                selectedFilters.clear();
                document.querySelectorAll(".selected").forEach(el => el.classList.remove("selected"));
                updateSelectedFilters();
                loadProducts(1);
            }

            function loadProducts(page) {
                let url = `?result=${encodeURIComponent(searchQuery)}&ajax=1&page=${page}`;
                
                if (selectedFilters.size > 0) {
                    const categories = [...selectedFilters]
                        .filter(([key]) => key.startsWith("cat_"))
                        .map(([key]) => key.replace('cat_', ''));
                    const brands = [...selectedFilters]
                        .filter(([key]) => key.startsWith("brand_"))
                        .map(([key]) => key.replace('brand_', ''));
                    if (categories.length > 0) {
                        url += `&category=${categories.join(',')}`;
                    }
                    if (brands.length > 0) {
                        url += `&brand=${brands.join(',')}`;
                    }
                    if (selectedFilters.has("price")) {
                        const [min, max] = selectedFilters.get("price").split("|")[0].split("-");
                        url += `&price_min=${min}&price_max=${max}`;
                    }
                }

                console.log("Fetching URL:", url);

                fetch(url)
                    .then(response => {
                        console.log("Response status:", response.status);
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(`Network response was not ok: ${response.status}, Response: ${text}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log("Response data:", data);
                        if (data.status === 'error') {
                            throw new Error(data.message);
                        }
                        productContainer.innerHTML = data.products || "<p>Không tìm thấy sản phẩm nào.</p>";
                        totalResults.textContent = data.total_results || 0;

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
                        console.error("Error fetching products:", error.message);
                        productContainer.innerHTML = `<p>Có lỗi xảy ra khi tải sản phẩm: ${error.message}</p>`;
                    });
            }

            document.getElementById("btnOpenFilter").addEventListener("click", () => {
                console.log("Mở modal lọc");
                toggleModal(true);
            });
            document.getElementById("closeModal").addEventListener("click", () => toggleModal(false));
            overlay.addEventListener("click", () => toggleModal(false));
            document.querySelectorAll(".product-btn").forEach(btn => btn.addEventListener("click", () => toggleFilter(btn)));
            document.querySelectorAll(".price-btn").forEach(btn => btn.addEventListener("click", () => toggleFilter(btn)));
            document.querySelector(".btn_Cancle").addEventListener("click", () => toggleModal(false));
            document.getElementById("applyFilter").addEventListener("click", () => {
                toggleModal(false);
                loadProducts(1);
            });

            loadProducts(1);
        });
    </script>
</body>
</html>