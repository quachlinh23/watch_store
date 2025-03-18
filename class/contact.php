<?php
    $filePath = realpath(dirname(__FILE__));
    include_once __DIR__ . '/../lib/database.php';
    include_once __DIR__ . '/../helpers/format.php';

    class contact {
        private $db;
        private $fm;

        public function __construct(){
            $this->db = new Database();
            $this->fm = new Format();
        }

        public function add($fullname, $email, $phone, $data){
            $query = "INSERT INTO tbl_hotro (hoTen, email, soDT, noiDung) VALUES ('$fullname', '$email', '$phone', '$data')";
            $result = $this->db->insert($query);
            return $result;
        }

        public function show() {
            $query = "SELECT ht.*, 
                             nv.tenNhanVien
                      FROM tbl_hotro ht
                      LEFT JOIN tbl_taikhoannhanvien tk ON tk.id = ht.nguoiDuyet
                      LEFT JOIN tbl_nhanvien nv ON tk.id = nv.id_taikhoan";
        
            $result = $this->db->select($query);
            return $result;
        }

        public function getContactsByStatus($status){
            $query = "SELECT ht.*, nv.tenNhanVien
                      FROM tbl_hotro ht
                      LEFT JOIN tbl_taikhoannhanvien tk ON tk.id = ht.nguoiDuyet
                      LEFT JOIN tbl_nhanvien nv ON tk.id = nv.id_taikhoan WHERE ht.trangThai = $status";
            $result = $this->db->select($query);
            return $result;
        }

        public function getContactById($id){
            // $query = "SELECT * FROM tbl_hotro WHERE id = $id";
            $query = "SELECT ht.*, nv.tenNhanVien
                      FROM tbl_hotro ht
                      LEFT JOIN tbl_taikhoannhanvien tk ON tk.id = ht.nguoiDuyet
                      LEFT JOIN tbl_nhanvien nv ON tk.id = nv.id_taikhoan WHERE ht.id = $id";
            $result = $this->db->select($query);
            return $result;
        }


        public function updateStatus($id,$idAcount,$date) {
            $id = intval($id);
            $idAcount = intval($idAcount);
            $newStatus = 1;
        
            $query = "UPDATE tbl_hotro SET trangThai = $newStatus,nguoiDuyet = $idAcount, ngayDuyet='$date' WHERE id = $id";
        
            $result = $this->db->update($query);
        
            return ($result) ? true : false;
        }
    }
?>