<?php
    include_once "class/brand.php";
    include_once "class/category.php";
    $brand = new brand();
    $category = new category();
    if (isset($_GET['id']) && $_GET['id'] != '') {
        $id = intval($_GET['id']);
        $brand_name = $brand->getnamebyid($id);
        $row = $brand_name->fetch_assoc();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Store</title>
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/productbybrand.css">
    <link rel="stylesheet" href="css/new.css">
    <link rel="stylesheet" href="css/filter.css">
    <script src="js/productbybrand.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include 'layout/header.php'; ?>
    <section class="breadcrumbs_wrapper">
        <ul class="breadcrumb">
            <li class="breadcrumb_item"><a href="index.php"><i class="fas fa-home home-icon" title="Về trang chủ"></i></a></li>
            <pre class="items">   >   </pre>
            <li class="breadcrumb_item product-name">Đồng hồ <?php echo $row['tenThuongHieu']; ?></li>
        </ul>
    </section>
    <section class="slide-container" id="slide-container">
        <button class="btn_close" onclick="closeSlide()">X</button>
        <div class="btns_pre" onclick="prevSlide()">❮</div>
        <div class="slide-wrapper">
            <div class="slide_left">
                <img id="slideLeft" src="images/slide_1.png" alt="Slide Left">
            </div>
            <div class="slide_right">
                <img id="slideRight" src="images/slide_2.png" alt="Slide Right">
            </div>
        </div>
        <div class="btns_next" onclick="nextSlide()">❯</div>
    </section>

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
                        <?php echo $row['tenThuongHieu']; ?>
                    </span>
                </div>
            </div>
            <div class="line_bottom"></div>

            <!-- Overlay -->
            <div class="modal-overlay" id="modalOverlay"></div>

            <!-- Modal -->

            <div class="modal-overlay" id="modalOverlay"></div>
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

                            <div class="filter-section">
                                <h4>Giới tính</h4>
                                <div class="border"></div>
                                <div class="checkbox-group">
                                    <label class="modern-checkbox">
                                        <input type="checkbox" name="sex" value="male"> Nam
                                        <span class="checkmark"></span>
                                    </label>
                                    <label class="modern-checkbox">
                                        <input type="checkbox" name="sex" value="female"> Nữ
                                        <span class="checkmark"></span>
                                    </label>
                                    <label class="modern-checkbox">
                                        <input type="checkbox" name="sex" value="unisex"> Nam & Nữ
                                        <span class="checkmark"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="filter-section">
                                <h4>Khoảng giá</h4>
                                <div class="border"></div>
                                <div class="price-grid">
                                    <button type="button" class="price-btn" data-price="0-2000000">Dưới 2 triệu</button>
                                    <button type="button" class="price-btn" data-price="2000000-4000000">2 - 4 triệu</button>
                                    <button type="button" class="price-btn" data-price="4000000-7000000">4 - 7 triệu</button>
                                    <button type="button" class="price-btn" data-price="7000000-13000000">7 - 13 triệu</button>
                                    <button type="button" class="price-btn" data-price="13000000-20000000">13 - 20 triệu</button>
                                    <button type="button" class="price-btn" data-price="20000000+">Trên 20 triệu</button>
                                </div>
                            </div>

                            <div class="btn-control">
                                <button type="button" class="btn_Cancle">Hủy</button>
                                <button type="submit" class="btn_Filter">Áp dụng</button>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
        <div class="manuinfo">
            <span class="brand-name" id="brand-name">
                <strong style="font-size: 25px;">Thương hiệu <span style="color: #4586ee; text-transform: uppercase;"><?php echo $row['tenThuongHieu']?> :</span></strong>
                <?php
                    $mota = $row['mota'];
                    $dotPos = strpos($mota, '.');
                    $firstSentence = ($dotPos !== false) ? substr($mota, 0, $dotPos + 1) : "Thông tin này chưa được cập nhật.";
                    echo '<span style="font-size: 17px;">' . $firstSentence . '...</span>';
                ?>
                <span class="extend" id="extend" style="cursor: pointer;">Xem thêm <i id="toggle-icon" class="fas fa-chevron-down"></i></span>
            </span>
            <div class="info" id="info" style="display: none;">
                <ul class="info-list">
                    <?php
                        $mota = $row['mota'];
                        $sentences = explode('.', $mota);
                        echo '<h2 class="name">Thương hiệu ' . $row['tenThuongHieu'] . '</h2>';
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

        <div class="product_new_bottom">
            <div class="product_1">
                <div class="pro_img">
                    <a href="details.php"><img src="images/sanphamdemo.jpg" alt=""></a>
                </div>
                <div class="descrise">
                    <div class="pro_name"><p>Đồng hồ casio</p></div>
                    <div class="pro_price"><p>100.000đ</p></div>
                </div>
            </div>
            <!-- Các sản phẩm khác giữ nguyên -->
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
            
            // Tạo và thêm nút "Xóa Tất Cả"
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
                const value = element.dataset.type || element.dataset.price || element.value;
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
                document.querySelectorAll(`[data-type='${value}'], [data-price='${value}'], [value='${value}']`)
                    .forEach(el => {
                        el.classList.remove("selected", "active");
                        if (el.type === "checkbox") el.checked = false;
                    });
                updateSelectedFilters();
            }

            function clearAllFilters() {
                selectedFilters.clear();
                document.querySelectorAll(".selected, .active").forEach(el => el.classList.remove("selected", "active"));
                document.querySelectorAll(".checkbox-group input[type='checkbox']").forEach(cb => cb.checked = false);
                updateSelectedFilters();
            }

            // Gán sự kiện cho các phần tử
            document.getElementById("btnOpenFilter").addEventListener("click", () => toggleModal(true));
            document.getElementById("close").addEventListener("click", () => toggleModal(false));
            overlay.addEventListener("click", () => toggleModal(false));
            document.querySelectorAll(".product-btn, .price-btn").forEach(btn => btn.addEventListener("click", () => toggleFilter(btn)));
            document.querySelectorAll(".checkbox-group input[type='checkbox']").forEach(cb => cb.addEventListener("change", () => toggleFilter(cb)));
            document.querySelector(".btn_Cancle").addEventListener("click", clearAllFilters);
        });
    </script>
</body>
</html>