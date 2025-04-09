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

    // Lấy sản phẩm theo id_thuonghieu với phân trang và bộ lọc (bỏ giới tính và giá)
    // public function getProductsByBrand($brandId, $page = 1, $limit = 10, $filters = []) {
    //     $brandId = intval($brandId);
    //     $offset = ($page - 1) * $limit;

    //     // Xây dựng điều kiện WHERE
    //     $where = "WHERE id_thuonghieu = $brandId";
        
    //     if (!empty($filters['category'])) {
    //         $category_id = intval($filters['category']);
    //         $where .= " AND id_loai = $category_id";
    //     }

    //     // Truy vấn lấy sản phẩm
    //     $sql = "SELECT * FROM tbl_sanpham $where LIMIT $offset, $limit";
    //     $products = $this->db->select($sql);

    //     // Đếm tổng số sản phẩm
    //     $total_sql = "SELECT COUNT(*) as total FROM tbl_sanpham $where";
    //     $total_result = $this->db->select($total_sql);
    //     $total_row = $total_result ? $total_result->fetch_assoc() : ['total' => 0];
    //     $total_products = $total_row['total'];
    //     $total_pages = ceil($total_products / $limit);

    //     return [
    //         'products' => $products,
    //         'total_pages' => $total_pages,
    //         'current_page' => $page
    //     ];
    // }

    public function getProductsByBrand($brandId, $page = 1, $limit = 10, $filters = []) {
        $brandId = intval($brandId);
        $offset = ($page - 1) * $limit;
    
        // Xây dựng điều kiện WHERE
        $where = "WHERE sp.id_thuonghieu = $brandId AND ct.soluongTon > 0"; // Sản phẩm có số lượng > 0
    
        if (!empty($filters['category'])) {
            $category_id = intval($filters['category']);
            $where .= " AND sp.id_loai = $category_id";
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

    public function SearchProductsByKey($keyword = '', $page = 1, $limit = 10, $filters = []) {
        $offset = ($page - 1) * $limit;
    
        // Xây dựng điều kiện WHERE
        $where = "WHERE ct.soluongTon > 0"; // Chỉ lấy sản phẩm còn hàng
    
        // Tìm kiếm theo từ khóa (tên sản phẩm)
        if (!empty($keyword)) {
            $escapedKeyword = mysqli_real_escape_string($this->db->link,$keyword);
            $where .= " AND sp.tenSanPham LIKE '%$escapedKeyword%'";
        }
    
        // Lọc theo danh mục nếu có
        if (!empty($filters['category'])) {
            $category_id = intval($filters['category']);
            $where .= " AND sp.id_loai = $category_id";
        }
    
        // Truy vấn lấy danh sách sản phẩm
        $sql = "SELECT sp.*, ct.soluongTon, ct.giaBan
                FROM tbl_sanpham sp
                JOIN tbl_chitietsanpham ct ON sp.maSanPham = ct.masanpham
                $where
                LIMIT $offset, $limit";
        $products = $this->db->select($sql);
    
        // Đếm tổng số sản phẩm phù hợp
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
            'current_page' => $page,
            'total_products' => $total_products
        ];
    }

        #Hàm load danh sách các sản phẩm lên trang index
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
    }
?>