<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include class product
include '../../class/product.php';

// Kiểm tra include
if (!class_exists('product')) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Không thể load class product']);
    exit;
}

$pr = new product();

if (!isset($_GET['maSanPham']) || empty($_GET['maSanPham'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Thiếu ID sản phẩm']);
    exit;
}

$maSanPham = $_GET['maSanPham'];
$specs = $pr->getSpecs($maSanPham);

// Đảm bảo luôn trả về JSON
header('Content-Type: application/json');
echo json_encode($specs ?: []);
?>