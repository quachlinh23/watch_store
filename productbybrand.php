<?php
ob_start();
include_once "class/brand.php";
include_once "class/category.php";
include_once "class/page_user.php";

try {
    $brand = new brand();
    $category = new category();
    $product = new Page_user();
} catch (Exception $e) {
    header('Content-Type: application/json');
    exit;
}

// Xử lý Ajax request
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;

    $filters = [];
    if (isset($_GET['category'])) $filters['category'] = $_GET['category'];

    $response = ['status' => 'error', 'message' => 'Unknown error'];

    try {
        $data = $product->getProductsByBrand($id, $page, 10, $filters);
        $output = '';
        $product_list = [];
        if ($data && isset($data['products']) && $data['products'] && $data['products']->num_rows > 0) {
            while ($row = $data['products']->fetch_assoc()) {
                $product_list[] = $row;
                $formatted_price = number_format($row['giaban'], 0, ',', '.');
                $output .= '
                    <div class="product_item">
                        <div class="pro_img">
                            <a href="details.php?id=' . $row['maSanPham'] . '">
                            <img src="admin/' . $row['hinhAnh'] . '" alt="' . $row['tenSanPham'] . '">
                            </a>
                        </div>
                        <div class="pro_price">
                            <p class="price">' . $formatted_price . '<span>đ</span></p>
                        </div>
                        <div class="descrise">
                            <a href="details.php?id=' . $row['maSanPham'] . '">
                                <div class="pro_name"><p>' . $row['tenSanPham'] . '</p></div>
                            </a>
                            </div>
                    </div>';
            }
        } else {
            $output = '<p>Không có sản phẩm nào phù hợp.</p>';
        }

        $response = [
            'products' => $output,
            'total_pages' => $data['total_pages'] ?? 1,
            'current_page' => $data['current_page'] ?? $page,
            'status' => 'success'
        ];
    } catch (Exception $e) {
        $response = [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
    echo json_encode($response);
    ob_end_flush();
    exit;
}

if (isset($_GET['id']) && $_GET['id'] != '') {
    $id = intval($_GET['id']);
    $brand_name = $brand->getnamebyid($id);
    $row = $brand_name ? $brand_name->fetch_assoc() : [];
}

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Store</title>
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/slider_product_by_brand.css">
    <link rel="stylesheet" href="css/brand_products.css">
    <link rel="stylesheet" href="css/pages.css">
    <script src="js/productbybrand.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        *{
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <?php include 'layout/header.php'; ?>
    <section class="breadcrumbs_wrapper">
        <ul class="breadcrumb">
            <li class="breadcrumb_item"><a href="index.php"><i class="fas fa-home home-icon" title="Về trang chủ"></i></a></li>
            <pre class="items">   >   </pre>
            <li class="breadcrumb_item product-name">Đồng hồ <?php echo $row['tenThuongHieu'] ?? ''; ?></li>
        </ul>
    </section>
    <?php include 'layout/sidebar.php'; ?>

    <section class="main-top">
        <div class="cate-main-filter">
            <div class="btn_filter">
                <button id="btnOpenFilter">
                    <span class="icon-container">
                        <i class="fas fa-filter"></i> 
                        <i class="fas fa-check-circle"></i>
                    </span>
                    Lọc
                </button>
                <div id="thuonghieu">
                    <span class="icon-container" style="text-transform: uppercase; font-weight: bold;">
                        <?php echo $row['tenThuongHieu'] ?? ''; ?>
                    </span>
                </div>
            </div>
            <div class="line_bottom"></div>

            <!-- Overlay -->
            <div class="modal-overlay" id="modalOverlay"></div>

            <!-- Modal -->
            <div id="filterModal" class="modal">
                <div class="modal-content">
                    <div class="header-modal">
                        <h3>Lọc sản phẩm</h3>
                        <span class="close" id="close">×</span>
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
                                    if ($categoryList){
                                        while($result = $categoryList->fetch_assoc()){
                                            echo '<button type="button" class="product-btn" data-type="' .$result['id_loai'].'">' .$result['tenLoai'].'</button>';
                                        }
                                    }
                                ?>
                            </div>
                        </div>

                        <div class="btn-control">
                            <button type="button" class="btn_Cancle">Hủy</button>
                            <button type="button" class="btn_Filter" id="applyFilter">Áp dụng</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="manuinfo">
            <span class="brand-name" id="brand-name">
                <strong style="font-size: 25px;">Thương hiệu <span style="color: #4586ee; text-transform: uppercase;"><?php echo $row['tenThuongHieu'] ?? ''; ?> :</span></strong>
                <?php
                    $mota = $row['mota'] ?? '';
                    $dotPos = strpos($mota, '.');
                    $firstSentence = ($dotPos !== false) ? substr($mota, 0, $dotPos + 1) : "Thông tin này chưa được cập nhật.";
                    echo '<span style="font-size: 17px;">' . $firstSentence . '...</span>';
                ?>
                <span class="extend" id="extend" style="cursor: pointer;">Xem thêm <i id="toggle-icon" class="fas fa-chevron-down"></i></span>
            </span>
            <div class="info" id="info" style="display: none;">
                <ul class="info-list">
                    <?php
                        $mota = $row['mota'] ?? '';
                        $sentences = explode('.', $mota);
                        echo '<h2 class="name">Thương hiệu ' . ($row['tenThuongHieu'] ?? '') . '</h2>';
                        if (empty($mota)) {
                            $des = "Thông tin này chưa được cập nhật";
                            echo '<li class="info-item">-' . $des . '</li>';
                        }
                        foreach ($sentences as $sentence) {
                            $sentence = trim($sentence);
                            if (!empty($sentence)) {
                                echo '<li class="info-item">-' . $sentence . '.</li>';
                            }
                        }
                        echo '<div id="info-item" style="cursor: pointer;">[Thu nhỏ]</div>';
                    ?>
                </ul>
            </div>
            <div class="line_bottom" style="margin-top: 20px;"></div>
        </div>

        <div class="product_new_bottom" id="product-container">
            <!-- Sản phẩm sẽ được load bằng Ajax -->
        </div>
        <div class="pagination" id="pagination">
            <!-- Phân trang sẽ được tạo động bằng JavaScript -->
        </div>
    </section>

    <?php include 'layout/footer.php'; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const extendBtn = document.getElementById("extend");
            const brandDiv = document.getElementById("brand-name");
            const infoDiv = document.getElementById("info");
            const collapseBtn = document.getElementById("info-item");

            extendBtn.addEventListener("click", () => {
                brandDiv.style.display = "none";
                infoDiv.style.display = "block";
            });

            collapseBtn.addEventListener("click", () => {
                infoDiv.style.display = "none";
                brandDiv.style.display = "block";
            });

            const modal = document.getElementById("filterModal");
            const overlay = document.getElementById("modalOverlay");
            const selectedContainer = document.getElementById("selectedFilters");
            const selectedFilters = new Map();
            const productContainer = document.getElementById("product-container");
            const paginationContainer = document.getElementById("pagination");
            const brandId = <?php echo $id ?? 0; ?>;

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
                const value = element.dataset.type;
                const text = element.innerText.trim();

                selectedFilters.has(value) ? selectedFilters.delete(value) : selectedFilters.set(value, text);
                element.classList.toggle("selected");
                element.classList.toggle("active");

                updateSelectedFilters();
            }

            function updateSelectedFilters() {
                selectedContainer.innerHTML = selectedFilters.size
                    ? [...selectedFilters].map(([value, text]) => 
                        `<button class="filter-tag">${text} 
                            <span class="remove-tag" data-value="${value}">×</span>
                        </button>`
                    ).join("")
                    : "Không có bộ lọc nào";
                
                btnClearAll.style.display = selectedFilters.size > 1 ? "block" : "none";

                document.querySelectorAll(".remove-tag").forEach(tag => 
                    tag.addEventListener("click", () => removeFilter(tag.dataset.value))
                );
            }

            function removeFilter(value) {
                selectedFilters.delete(value);
                document.querySelectorAll(`[data-type="${value}"]`)
                    .forEach(el => {
                        el.classList.remove("selected", "active");
                    });
                updateSelectedFilters();
            }

            function clearAllFilters() {
                selectedFilters.clear();
                document.querySelectorAll(".selected, .active").forEach(el => el.classList.remove("selected", "active"));
                updateSelectedFilters();
                loadProducts(1);
            }

            function loadProducts(page) {
                let url = `?id=${brandId}&ajax=1&page=${page}`;
                
                if (selectedFilters.size > 0) {
                    const filters = [...selectedFilters].map(([key, value]) => {
                        return `category=${key}`;
                    }).join('&');
                    url += `&${filters}`;
                }

                fetch(url)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`Network response was not ok: ${response.status}, Response: ${text}`);
                        });
                    }
                    return response.text();
                })
                .then(text => {
                    if (!text) throw new Error("Empty response from server");
                    const data = JSON.parse(text);
                    productContainer.innerHTML = data.products;

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

                    paginationContainer.innerHTML = '';
                    paginationContainer.appendChild(fragment);
                })
                .catch(error => console.error('Error fetching products:', error));
            }

            document.getElementById("btnOpenFilter").addEventListener("click", () => toggleModal(true));
            document.getElementById("close").addEventListener("click", () => toggleModal(false));
            overlay.addEventListener("click", () => toggleModal(false));
            document.querySelectorAll(".product-btn").forEach(btn => btn.addEventListener("click", () => toggleFilter(btn)));
            document.querySelector(".btn_Cancle").addEventListener("click", clearAllFilters);
            document.getElementById("applyFilter").addEventListener("click", () => {
                toggleModal(false);
                loadProducts(1);
            });

            loadProducts(1);
        });
    </script>
</body>
</html>