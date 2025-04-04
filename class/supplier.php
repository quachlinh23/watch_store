<?php
    include_once(__DIR__ . '/../lib/database.php');
    include_once(__DIR__ . '/../helpers/format.php');
    
    class supplier {
        private $db;
        private $fm;

        public function __construct(){
            $this->db = new Database();
            $this->fm = new Format();
        }

        public function show(){
            $querry = "SELECT * FROM tbl_nhacungcap"; 
            $result = $this->db->select($querry);
            return $result;
        }

        public function add($tenNCC, $diaChi, $soDT) {
            // Kiểm tra trùng tên nhà cung cấp
            $checkNameQuery = "SELECT * FROM tbl_nhacungcap WHERE tenNCC = '$tenNCC'";
            $checkNameResult = $this->db->select($checkNameQuery);
        
            if ($checkNameResult) {
                return "Tên nhà cung cấp đã tồn tại!";
            }
        
            // Kiểm tra trùng số điện thoại
            $checkPhoneQuery = "SELECT * FROM tbl_nhacungcap WHERE soDT = '$soDT'";
            $checkPhoneResult = $this->db->select($checkPhoneQuery);
        
            if ($checkPhoneResult) {
                return "Số điện thoại đã tồn tại!";
            }
        
            // Nếu không trùng, thêm mới vào CSDL
            $query = "INSERT INTO tbl_nhacungcap (tenNCC, diaChi, soDT) VALUES ('$tenNCC', '$diaChi', '$soDT')";
            $insertResult = $this->db->insert($query);
        
            return $insertResult ? true : false;
        }

        public function update($idNCC, $tenNCC, $diaChi, $soDT) {
            // Kiểm tra trùng tên nhà cung cấp nhưng bỏ qua chính nó
            $checkNameQuery = "SELECT * FROM tbl_nhacungcap WHERE tenNCC = '$tenNCC' AND id_nhacungcap != '$idNCC'";
            $checkNameResult = $this->db->select($checkNameQuery);
        
            if ($checkNameResult) {
                return "Tên nhà cung cấp đã tồn tại!";
            }
        
            // Kiểm tra trùng số điện thoại nhưng bỏ qua chính nó
            $checkPhoneQuery = "SELECT * FROM tbl_nhacungcap WHERE soDT = '$soDT' AND id_nhacungcap != '$idNCC'";
            $checkPhoneResult = $this->db->select($checkPhoneQuery);
        
            if ($checkPhoneResult) {
                return "Số điện thoại đã tồn tại!";
            }
        
            // Nếu không trùng, tiến hành cập nhật
            $query = "UPDATE tbl_nhacungcap 
                      SET tenNCC = '$tenNCC', diaChi = '$diaChi', soDT = '$soDT' 
                      WHERE id_nhacungcap = '$idNCC'";
            $updateResult = $this->db->update($query);
        
            return $updateResult;
        }

        function searchInfoSupplier($data){
            $query = "SELECT * FROM tbl_nhacungcap WHERE tenNCC LIKE '%$data%' OR soDT LIKE '%$data%'";
            $supplierlist = $this->db->select($query);
            return $supplierlist;
        }

        public function updateStatus($id, $status) {
            $id = intval($id);
            $newStatus = ($status == 1) ? 0 : 1;
        
            $query = "UPDATE tbl_nhacungcap SET trangThai = $newStatus WHERE id_nhacungcap = $id";
        
            $result = $this->db->update($query);
        
            return ($result) ? true : false;
        }
    }
?>