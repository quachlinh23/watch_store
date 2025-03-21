<?php
    include_once(__DIR__ . '/../lib/database.php');
    include_once(__DIR__ . '/../helpers/format.php');

    class category {
        private $db;
        private $fm;

        public function __construct(){
            $this->db = new Database();
            $this->fm = new Format();
        }

        public function insertcategory($catname){
            $catname = $this->fm->validation($catname);
            $catname = mysqli_real_escape_string($this->db->link, $catname);

            if (empty($catname)){
                return false; // Trả về false nếu lỗi
            }

            $check = $this->checkDuplicateName($catname);
            if ($check && $check->num_rows > 0) {
                return 'duplicate'; // Trả về mã lỗi đặc biệt cho trùng tên
            }

            $query = "INSERT INTO tbl_loaisp(tenLoai, trangthai) VALUES('$catname', 1)";
            return $this->db->insert($query); // Trả về true nếu thành công, false nếu thất bại
        }

        public function show(){
            $query = "SELECT * FROM tbl_loaisp"; 
            return $this->db->select($query);
        }

        public function get_all_type(){
            $query = "SELECT * FROM tbl_loaisp WHERE id_loai > 0"; 
            return $this->db->select($query);
        }

        public function getcatbyid($id){
            $id = intval($id);
            $query = "SELECT * FROM tbl_loaisp WHERE id_loai = $id";
            return $this->db->select($query);
        }

        public function update($id, $name){
            $name = $this->fm->validation($name);
            $name = mysqli_real_escape_string($this->db->link, $name);
            $id = intval($id);

            if (empty($name)){
                return false;
            }

            $check = $this->checkDuplicateName($name, $id);
            if ($check && $check->num_rows > 0) {
                return 'duplicate';
            }

            $query = "UPDATE tbl_loaisp SET tenLoai = '$name' WHERE id_loai = $id";
            return $this->db->update($query);
        }

        public function delete($id, $status) {
            $id = intval($id);
            $newStatus = ($status == 1) ? 0 : 1;
            $query = "UPDATE tbl_loaisp SET trangthai = '$newStatus' WHERE id_loai = $id";
            return $this->db->update($query);
        }

        public function search($data) {
            $data = $this->fm->validation($data);
            $data = mysqli_real_escape_string($this->db->link, $data);
            $query = "SELECT * FROM tbl_loaisp WHERE tenLoai LIKE '%$data%'";
            return $this->db->select($query);
        }

        public function updateStatus($id, $status) {
            $id = intval($id);
            $newStatus = ($status == 1) ? 0 : 1;
            $query = "UPDATE tbl_loaisp SET trangthai = $newStatus WHERE id_loai = $id";
            return $this->db->update($query);
        }

        public function checkDuplicateName($name, $id = null) {
            $name = mysqli_real_escape_string($this->db->link, $name);
            $query = "SELECT * FROM tbl_loaisp WHERE tenLoai = '$name'";
            if ($id) {
                $id = intval($id);
                $query .= " AND id_loai != $id";
            }
            return $this->db->select($query);
        }
    }
?>