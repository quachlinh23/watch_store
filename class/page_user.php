<?php
include_once(__DIR__ . '/../lib/database.php');
include_once(__DIR__ . '/../helpers/format.php');

class Page_user {
    private $db;
    private $fm;

    public function __construct() {
        $this->db = new Database();
        $this->fm = new Format();
    }

    //Lọc sản phẩm theo thương hiệu
    public function getProductsByBrand($brandId, $page = 1, $limit = 10, $filters = []) {
        $brandId = intval($brandId);
        $offset = ($page - 1) * $limit;
    
        // Xây dựng điều kiện WHERE
        $where = "WHERE sp.id_thuonghieu = $brandId AND ct.soluongTon > 0"; // Sản phẩm có số lượng > 0
    
        if (!empty($filters['category'])) {
            $category_id = intval($filters['category']);
            $where .= " AND sp.id_loai = $category_id";
        }
    
        if (!empty($filters['min_price'])) {
            $min_price = floatval($filters['min_price']);
            $where .= " AND ct.giaban >= $min_price";
        }
    
        if (!empty($filters['max_price'])) {
            $max_price = floatval($filters['max_price']);
            $where .= " AND ct.giaban <= $max_price";
        }
    
        // Truy vấn lấy sản phẩm có số lượng > 0
        $sql = "SELECT sp.*, ct.soluongton, ct.giaban
                FROM tbl_sanpham sp
                JOIN tbl_chitietsanpham ct ON sp.maSanPham = ct.masanpham
                $where
                LIMIT $offset, $limit";
        $products = $this->db->select($sql);
    
        // Đếm tổng số sản phẩm có số lượng > 0
        $total_sql = "SELECT COUNT(*) as total
                    FROM tbl_sanpham sp
                    JOIN tbl_chitietsanpham ct ON sp.maSanPham = ct.masanpham
                    $where";
        $total_result = $this->db->select($total_sql);
        $total_row = $total_result ? $total_result->fetch_assoc() : ['total' => 0];
        $total_products = $total_row['total'];
        $total_pages = ceil($total_products / $limit);
    
        return [
            'products' => $products,
            'total_pages' => $total_pages,
            'current_page' => $page
        ];
    }

    //Lọc sản phẩm theo từ khóa(Mở rộng lọc theo giá, theo loại, theo thương hiệu)
    private function normalizeKeyword($keyword) {
        $keyword = trim(strtolower($keyword));
        $unaccented = [
            'a' => '[àáảãạăắằẳẵặâấầẩẫậ]',
            'e' => '[èéẻẽẹêếềểễệ]',
            'i' => '[ìíỉĩị]',
            'o' => '[òóỏõọôốồổỗộơớờởỡợ]',
            'u' => '[ùúủũụưứừửữự]',
            'y' => '[ỳýỷỹỵ]',
            'd' => '[đ]',
        ];
        foreach ($unaccented as $replace => $pattern) {
            $keyword = preg_replace("/$pattern/i", $replace, $keyword);
        }
        return $keyword;
    }
    
    //Tìm kiếm sản phẩm theo từ khóa
    public function SearchProductsByKey($keyword = '', $page = 1, $limit = 10, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;

            // Xây dựng điều kiện WHERE
            $where = "WHERE ct.soluongTon > 0";

            // Xử lý từ khóa tìm kiếm
            if (!empty($keyword)) {
                $keyword = trim($keyword);
                $escapedKeyword = mysqli_real_escape_string($this->db->link, $keyword);
                $normalizedKeyword = $this->normalizeKeyword($keyword);
                $escapedNormalizedKeyword = mysqli_real_escape_string($this->db->link, $normalizedKeyword);

                // Tìm kiếm chính xác trong tenSanPham
                $where .= " AND (sp.tenSanPham LIKE '%$escapedKeyword%' OR sp.tenSanPham LIKE '%$escapedNormalizedKeyword%')";
            }

            // Lọc theo danh mục
            if (!empty($filters['category']) && is_array($filters['category'])) {
                $category_ids = implode(',', array_map('intval', $filters['category']));
                $where .= " AND sp.id_loai IN ($category_ids)";
            }

            // Lọc theo thương hiệu
            if (!empty($filters['brand']) && is_array($filters['brand'])) {
                $brand_ids = implode(',', array_map('intval', $filters['brand']));
                $where .= " AND sp.id_thuonghieu IN ($brand_ids)";
            }

            // Lọc theo giá
            if (isset($filters['min_price']) && isset($filters['max_price'])) {
                $min_price = floatval($filters['min_price']);
                $max_price = floatval($filters['max_price']);
                $where .= " AND ct.giaBan BETWEEN $min_price AND $max_price";
            }

            // Truy vấn lấy danh sách sản phẩm
            $sql = "SELECT sp.*, ct.soluongTon, ct.giaBan,
                            (CASE
                                WHEN LOWER(sp.tenSanPham) LIKE '$escapedKeyword%' THEN 3
                                WHEN LOWER(sp.tenSanPham) LIKE '%$escapedKeyword%' THEN 2
                                WHEN LOWER(sp.tenSanPham) LIKE '$escapedNormalizedKeyword%' THEN 1
                                WHEN LOWER(sp.tenSanPham) LIKE '%$escapedNormalizedKeyword%' THEN 0
                                ELSE -1
                            END) as relevance
                    FROM tbl_sanpham sp
                    JOIN tbl_chitietsanpham ct ON sp.maSanPham = ct.masanpham
                    $where
                    ORDER BY relevance DESC, sp.tenSanPham ASC
                    LIMIT $offset, $limit";

            error_log("SQL Query: $sql");
            $products = $this->db->select($sql);
            if ($products === false) {
                throw new Exception("Lỗi truy vấn SQL: " . mysqli_error($this->db->link));
            }

            // Đếm tổng số sản phẩm
            $total_sql = "SELECT COUNT(*) as total
                        FROM tbl_sanpham sp
                        JOIN tbl_chitietsanpham ct ON sp.maSanPham = ct.masanpham
                        $where";
            $total_result = $this->db->select($total_sql);
            if ($total_result === false) {
                throw new Exception("Lỗi truy vấn tổng số: " . mysqli_error($this->db->link));
            }

            $total_row = $total_result ? $total_result->fetch_assoc() : ['total' => 0];
            $total_products = $total_row['total'];
            $total_pages = ceil($total_products / $limit);

            return [
                'products' => $products,
                'total_pages' => $total_pages,
                'current_page' => $page,
                'total_products' => $total_products
            ];
        } catch (Exception $e) {
            error_log("SearchProductsByKey error: " . $e->getMessage());
            throw new Exception("Lỗi xử lý tìm kiếm: " . $e->getMessage());
        }
    }
    
    //Hàm load danh sách các sản phẩm lên trang index
    public function loadProduct($brandId){
        $brandId = intval($brandId);
        $query = "SELECT sp.*, ct.giaBan
        FROM tbl_sanpham sp
        JOIN tbl_chitietsanpham ct ON sp.maSanPham = ct.masanpham
        WHERE sp.id_thuonghieu = $brandId AND ct.soluongTon > 0
        LIMIT 5";

        $result = $this->db->select($query);

        $products = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        return $products; // Trả về dạng mảng
    }

    //Hiển thị danh sách sản phẩm
    public function loadNewProduct() {
        $query = "SELECT sp.maSanPham, sp.tenSanPham, sp.moTa, sp.hinhAnh, sp.id_thuonghieu,
        ctsp.giaBan AS giaBanSP, ctsp.soluongTon, pn.ngayLap
        FROM tbl_sanpham sp
        JOIN tbl_chitietsanpham ctsp ON sp.maSanPham = ctsp.maSanPham
        JOIN tbl_chitietphieunhap ctpn ON ctsp.mact = ctpn.mactSP
        JOIN tbl_phieunhap pn ON ctpn.maPN = pn.maPhieuNhap
        WHERE ctsp.soluongTon > 0
        ORDER BY pn.ngayLap DESC
        LIMIT 5";

        $result_new = $this->db->select($query);

        $products_new = [];
        if ($result_new  && $result_new ->num_rows > 0) {
            while ($row = $result_new ->fetch_assoc()) {
                $products_new[] = $row;
            }
        }

        return $products_new;
    }
}
?>