<?php
    $filePath = realpath(dirname(__FILE__));
    // include_once __DIR__ . '/../lib/session.php';
    // // Session::checkLogin();
    include_once __DIR__ . '/../lib/database.php';
    include_once __DIR__ . '/../helpers/format.php';

    class customer {
        private $db;
        private $fm;

        public function __construct(){
            $this->db = new Database();
            $this->fm = new Format();
        }

        public function getAllCustomers() {
            $query = "SELECT * FROM tbl_taikhoan";
            $result = $this->db->select($query);
            return $result;
        }

        public function updateStatus($id, $status) {
            $id = intval($id);
            $newStatus = ($status == 1) ? 0 : 1; // Đảo trạng thái
        
            $query = "UPDATE tbl_taikhoan SET trangThai = $newStatus WHERE id = $id";
        
            $result = $this->db->update($query); // Gọi phương thức update
        
            return ($result) ? true : false; // Trả về kết quả cập nhật
        }

        public function searchCustomers($keyword) {
            $keyword = trim($keyword); // Loại bỏ khoảng trắng thừa
            $keyword = addslashes($keyword); // Tránh lỗi SQL khi có dấu nháy
        
            $query = "SELECT * FROM tbl_taikhoan WHERE username LIKE '%$keyword%'";
        
            return $this->db->select($query);
        } 
    }
?>