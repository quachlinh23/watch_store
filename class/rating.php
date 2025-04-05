<?php
    include_once(__DIR__ . '/../lib/database.php');
    include_once(__DIR__ . '/../helpers/format.php');

    class Rating{
        private $db;
        private $fm;

        public function __construct(){
            $this->db = new Database();
            $this->fm = new Format();
        }

        // Hàm thêm đánh giá
        public function insert($userId, $productId, $data) {
            $rating = intval($data['rating']);
            $comment = $data['comment'];
        
            $query = "INSERT INTO tbl_danhgia (masanPham, maTaiKhoan, soSao, noiDung) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("iiis", $productId, $userId, $rating, $comment); // Giả sử masanPham và maTaiKhoan là int, soSao là int, noiDung là string
            $result = $stmt->execute();
            $stmt->close();
        
            return $result;
        }

        // Hàm lấy danh sách đánh giá
        public function getRatingsWithCustomerInfo($productId) {
            // Query to join tbl_danhgia and tbl_khachhang based on id_taikhoan
            $query = "
                SELECT dg.*, kh.*
                FROM tbl_danhgia dg
                INNER JOIN tbl_taikhoan tk ON dg.maTaiKhoan = tk.id
                INNER JOIN tbl_khachhang kh ON tk.id = kh.id_taikhoan
                WHERE dg.masanPham = ?";
            
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $ratings = [];
            while ($row = $result->fetch_assoc()) {
                $ratings[] = $row;
            }
            
            return $ratings;
        }
        
        public function update($userId, $productId, $data) {
            if (!isset($data['rating'])) {
                return false;
            }
        
            $rating = intval($data['rating']);
            $comment = trim($data['comment']);
        
            if (empty($comment)) {
                return false;
            }
        
            $userId = (int)$userId;
            $productId = (int)$productId;
        
            $query = "UPDATE tbl_danhgia SET soSao = ?, noiDung = ? WHERE maSanPham = ? AND maTaiKhoan = ?";
            $stmt = $this->db->link->prepare($query);
            if (!$stmt) {
                return false;
            }
        
            $stmt->bind_param("isii", $rating, $comment, $productId, $userId);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

        public function getAverageRating($productId) {
            $query = "SELECT soSao FROM tbl_danhgia WHERE masanPham = ?";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
        
            $totalRating = 0;
            $numRatings = 0;
            while ($row = $result->fetch_assoc()) {
                $totalRating += $row['soSao'];
                $numRatings++;
            }
            if ($numRatings > 0) {
                $averageRating = $totalRating / $numRatings;
            } else {
                $averageRating = 0;
            }
        
            return round($averageRating, 1);
        }
        
    }
?>