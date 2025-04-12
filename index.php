<?php
	include_once "class/brand.php";
	include_once "class/page_user.php";
	$brand = new brand();
	$page_user = new page_user();
?>
<!DOCTYPE HTML>
	<head>
		<title>Watch Store</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<link rel="stylesheet" href="css/head.css">
		<link rel="stylesheet" href="css/footer.css">
		<link rel="stylesheet" href="css/slide.css">
		<link rel="stylesheet" href="css/Streng.css">
		<link rel="stylesheet" href="css/brand.css">
		<link rel="stylesheet" href="css/index.css">
		<script src="js/index.js"></script>
		<script src="js/productbybrand.js"></script>
		<script src="js/new.js"></script>
		<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	</head>
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			font-family: Arial, sans-serif;
		}
	</style>
<body>
	<?php
        include 'layout/header.php';
        include 'layout/slider.php';
	?>
	<div class="main">
		<div class="content">
			<!-- Hiển thị danh sách các sản phẩm mới -->
			<?php include 'layout/strengs.php';?>
			<section class="product_new">
				<div class="product_new_top">
					<img src="images/sanphammoi.png" alt="">
				</div>
				<div class="product_new_bottom">
					<?php
						$products = $page_user->loadNewProduct();
						foreach ($products as $item): 
					?>
					<div class="product_1">
						<div class="pro_img">
							<a href="details.php?id=<?= $item['maSanPham'] ?>">
								<img src="admin/<?= $item['hinhAnh'] ?>" alt="">
							</a>
						</div>
						<div class="descrise">
							<div class="pro_name">
								<a href="details.php?id=<?= $item['maSanPham'] ?>" 
									class="linktoDetails"
								>
									<p><?= htmlspecialchars($item['tenSanPham']) ?></p>
								</a>
							</div>
							<div class="pro_price">
								<p><?= number_format($item['giaBanSP'], 0, ',', '.') ?>đ</p>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
				</div>
			</section>

			<!-- Thương hiệu CASIO -->
			<?php
				$products = $page_user->loadProduct(7);
				if (!empty($products)) {
			?>
				<section class="product_brands hidden">
					<div class="product_brands_left">
						<a href="productbybrand.php?id=7"><img src="images/bannercasio.png" alt=""></a>
					</div>
					<div class="product_brands_right">
						<div class="product_brands_right_top">
							<h2 class="brand_name">Đồng Hồ CASIO</h2>
							<a href="productbybrand.php?id=7" class="product_by_brand_details">
								Sản Phẩm Khác  <i class="fas fa-arrow-right"></i>
							</a>
						</div>
						<div class="product_brands_right_bottom">
							<?php foreach ($products as $item): ?>
								<div class="product_1">
									<div class="pro_img">
										<a href="details.php?id=<?= $item['maSanPham'] ?>">
											<img src="admin/<?= $item['hinhAnh'] ?>" alt="">
										</a>
									</div>
									<div class="descrise">
										<div class="pro_name">
											<a href="details.php?id=<?= $item['maSanPham'] ?>" 
												class="linktoDetails"
											>
												<p><?= htmlspecialchars($item['tenSanPham']) ?></p>
											</a>
										</div>
										<div class="pro_price">
											<p><?= number_format($item['giaBan'], 0, ',', '.') ?>đ</p>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
			<?php
			}
			?>

			<!-- Thương hiệu ORIENT -->
			<?php
				$products = $page_user->loadProduct(9);
				if (!empty($products)) {
			?>
				<section class="product_brands">
					<div class="product_brands_left">
						<a href=""><img src="images/bannerOrient.png" alt=""></a>
					</div>
					<div class="product_brands_right">
						<div class="product_brands_right_top">
							<h2 class="brand_name">Đồng Hồ ORIENT</h2>
							<a href="productbybrand.php?id=9" class="product_by_brand_details">
								Sản Phẩm Khác <i class="fas fa-arrow-right"></i>
							</a>
						</div>
						<div class="product_brands_right_bottom">
							<?php foreach ($products as $item): ?>
								<div class="product_1">
									<div class="pro_img">
										<a href="details.php?id=<?= $item['maSanPham'] ?>">
											<img src="admin/<?= $item['hinhAnh'] ?>" alt="">
										</a>
									</div>
									<div class="descrise">
										<div class="pro_name">
											<a href="details.php?id=<?= $item['maSanPham'] ?>" 
												class="linktoDetails"
											>
												<p><?= htmlspecialchars($item['tenSanPham']) ?></p>
											</a>
										</div>
										<div class="pro_price">
											<p><?= number_format($item['giaBan'], 0, ',', '.') ?>đ</p>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
			<?php
			}
			?>

			<!-- Thương hiệu CITIZEN-->
			<?php
				$products = $page_user->loadProduct(10);
				if (!empty($products)) {
			?>
				<section class="product_brands hidden">
					<div class="product_brands_left">
						<a href=""><img src="images/bannercitzent.png" alt=""></a>
					</div>
					<div class="product_brands_right">
						<div class="product_brands_right_top">
							<h2 class="brand_name">Đồng Hồ CITIZEN</h2>
							<a href="productbybrand.php?id=10" class="product_by_brand_details">
								Sản Phẩm Khác  <i class="fas fa-arrow-right"></i>
							</a>
						</div>
						<div class="product_brands_right_bottom">
							<?php foreach ($products as $item): ?>
								<div class="product_1">
									<div class="pro_img">
										<a href="details.php?id=<?= $item['maSanPham'] ?>">
											<img src="admin/<?= $item['hinhAnh'] ?>" alt="">
										</a>
									</div>
									<div class="descrise">
										<div class="pro_name">
											<a href="details.php?id=<?= $item['maSanPham'] ?>" 
												class="linktoDetails"
											>
												<p><?= htmlspecialchars($item['tenSanPham']) ?></p>
											</a>
										</div>
										<div class="pro_price">
											<p><?= number_format($item['giaBan'], 0, ',', '.') ?>đ</p>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
			<?php
			}
			?>

			<!-- Thương hiệu Rolex-->
			<?php
				$products = $page_user->loadProduct(6);
				if (!empty($products)) {
			?>
				<section class="product_brands hidden">
					<div class="product_brands_left">
						<a href="productbybrand.php?id=6"><img src="images/BannnerChung.jpg" alt=""></a>
					</div>
					<div class="product_brands_right">
						<div class="product_brands_right_top">
							<h2 class="brand_name">Đồng Hồ Rolex</h2>
							<a href="productbybrand.php?id=6" class="product_by_brand_details">
								Sản Phẩm Khác  <i class="fas fa-arrow-right"></i>
							</a>
						</div>
						<div class="product_brands_right_bottom">
							<?php foreach ($products as $item): ?>
								<div class="product_1">
									<div class="pro_img">
										<a href="details.php?id=<?= $item['maSanPham'] ?>">
											<img src="admin/<?= $item['hinhAnh'] ?>" alt="">
										</a>
									</div>
									<div class="descrise">
										<div class="pro_name">
											<a href="details.php?id=<?= $item['maSanPham'] ?>" 
												class="linktoDetails"
											>
												<p><?= htmlspecialchars($item['tenSanPham']) ?></p>
											</a>
										</div>
										<div class="pro_price">
											<p><?= number_format($item['giaBan'], 0, ',', '.') ?>đ</p>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
			<?php
			}
			?>

			<!-- Thương hiệu Tissot-->
			<?php
				$products = $page_user->loadProduct(11);
				if (!empty($products)) {
			?>
				<section class="product_brands hidden">
					<div class="product_brands_left">
						<a href="productbybrand.php?id=11"><img src="images/BannnerChung.jpg" alt=""></a>
					</div>
					<div class="product_brands_right">
						<div class="product_brands_right_top">
							<h2 class="brand_name">Đồng Hồ Tissot</h2>
							<a href="productbybrand.php?id=11" class="product_by_brand_details">
								Sản Phẩm Khác  <i class="fas fa-arrow-right"></i>
							</a>
						</div>
						<div class="product_brands_right_bottom">
							<?php foreach ($products as $item): ?>
								<div class="product_1">
									<div class="pro_img">
										<a href="details.php?id=<?= $item['maSanPham'] ?>">
											<img src="admin/<?= $item['hinhAnh'] ?>" alt="">
										</a>
									</div>
									<div class="descrise">
										<div class="pro_name">
											<a href="details.php?id=<?= $item['maSanPham'] ?>" 
												class="linktoDetails"
											>
												<p><?= htmlspecialchars($item['tenSanPham']) ?></p>
											</a>
											</div>
										<div class="pro_price">
											<p><?= number_format($item['giaBan'], 0, ',', '.') ?>đ</p>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
			<?php
			}
			?>


			<!-- Thương hiệu TagHeuer-->
			<?php
				$products = $page_user->loadProduct(8);
				if (!empty($products)) {
			?>
				<section class="product_brands hidden">
					<div class="product_brands_left">
						<a href="productbybrand.php?id=8"><img src="images/BannnerChung.jpg" alt=""></a>
					</div>
					<div class="product_brands_right">
						<div class="product_brands_right_top">
							<h2 class="brand_name">Đồng Hồ Tangheuer</h2>
							<a href="productbybrand.php?id=8" class="product_by_brand_details">
								Sản Phẩm Khác  <i class="fas fa-arrow-right"></i>
							</a>
						</div>
						<div class="product_brands_right_bottom">
							<?php foreach ($products as $item): ?>
								<div class="product_1">
									<div class="pro_img">
										<a href="details.php?id=<?= $item['maSanPham'] ?>">
											<img src="admin/<?= $item['hinhAnh'] ?>" alt="">
										</a>
									</div>
									<div class="descrise">
										<div class="pro_name">
											<a href="details.php?id=<?= $item['maSanPham'] ?>" 
												class="linktoDetails"
											>
												<p><?= htmlspecialchars($item['tenSanPham']) ?></p>
											</a>
										</div>
										<div class="pro_price">
											<p><?= number_format($item['giaBan'], 0, ',', '.') ?>đ</p>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
			<?php
			}
			?>

			<!-- Danh sách các thương hiệu-->
			<section class="brand_hot hidden">
				<div class="brand_hot_header">
					<p>Thương Hiệu Nổi Bật</p>
					<span>Cam kết chính hãng 100%, bồi thường 20 lần nếu phát hiện hàng giả</span>
					<div class="under_line"></div>
				</div>
				<div class="brand_hot_content" >
					<div class="btn_pre" style="display: none;">
						<button id="prevPage" class="nextPage">
							<i class="fa-solid fa-arrow-right fa-rotate-180"></i>
							
						</button>
					</div>
					<table id="brandTable">
						<tbody id="brandTableBody">
							<?php

								$result = $brand->show();
								$brands = [];

								while ($row = $result->fetch_assoc()) {
									$brands[] = $row;
								}
								$brandsPerPage = 12;
								$colsPerRow = 6;
								$totalBrands = count($brands);
								$totalPages = ceil($totalBrands / $brandsPerPage);

								$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
								$startIdx = ($currentPage - 1) * $brandsPerPage;
								$endIdx = min($startIdx + $brandsPerPage, $totalBrands);

								for ($i = $startIdx; $i < $endIdx; $i += $colsPerRow) {
									echo "<tr>";
									for ($j = $i; $j < $i + $colsPerRow && $j < $endIdx; $j++) {
										echo "<td><a href='productbybrand.php?id={$brands[$j]['id_thuonghieu']}'>
													<img src='admin/{$brands[$j]['hinhAnh']}' alt=''>
												</a>
											</td>";
									}
									echo "</tr>";
								}
							?>
						</tbody>
					</table>
					<div class="btn_next" style="display: none;">
						<button id="nextPage" class="nextPage">
							<i class="fa-solid fa-arrow-right"></i>
						</button>
					</div>
				</div>
				<script>
					
				</script>
			</section>

		</div>
	</div>
	<?php include 'layout/footer.php';?>
	<script src="js/new.js"></script>
</body>
</html>