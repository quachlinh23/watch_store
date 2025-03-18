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

            $catname = mysqli_real_escape_string($this->db->link,$catname);

            if (empty($catname)){
                $alert = "<span class='error'>Vui lòng nhập tên loại sản phẩm</span>";
                return $alert;
            } else{
                $query = "INSERT INTO tbl_loaisp(tenLoai,trangthai) VALUES('$catname',1)";
                $result = $this->db->insert($query);

                if ($result){
                    $alert = "<span class='success'>Thêm loại sản phẩm thành công</span>";
                    return $alert;
                }else{
                    $alert = "<span class='error'>Thêm loại sản phẩm thất bại</span>";
                    return $alert;
                }
            }
        }


        public function show(){
            $querry = "SELECT * FROM tbl_loaisp"; 
            $result = $this->db->select($querry);
            return $result;
        }

        public function get_all_type(){
            $querry = "SELECT * FROM tbl_loaisp where id_loai > 0"; 
            $result = $this->db->select($querry);
            return $result;
        }

        public function getcatbyid($id){
            $id = intval($id);
            $querry = "SELECT * FROM tbl_loaisanpham WHERE id_loai = $id ";
            $result = $this->db->select($querry);
            return $result;
        }

        public function update($id,$name){

            if (empty($name)){
                $alert = "<span class='error'>Vui lòng nhập tên loại sản phẩm</span>";
                return $alert;
            } else{
                $query = "UPDATE tbl_loaisanpham SET tenLoai = '$name' WHERE id_loai = $id";
                $result = $this->db->update($query);

                if ($result){
                    $alert = "<span class='success'>Sửa loại sản phẩm thành công</span>";
                    return $alert;
                }else{
                    $alert = "<span class='error'>Sửa loại sản phẩm thất bại</span>";
                    return $alert;
                }
            }
        }

        public function delete($id, $status) {
            $newStatus = ($status == 1) ? 0 : 1;
            $query = "UPDATE tbl_loaisanpham SET trangthai = '$newStatus' WHERE id_loai = $id";
            
            $result = $this->db->update($query);
            
            if ($result) {
                $alert = "<span class='success'>Thay đổi trạng thái thành công</span>";
                return $alert;
            } else {
                $alert = "<span class='error'>Thay đổi trạng thái thất bại</span>";
                return $alert;
            }
        }

        public function search($data) {
            $query = "SELECT * FROM tbl_loaisp WHERE tenLoai LIKE '%" . $data . "%'";
            $result = $this->db->select($query);
            return $result;
        }

        public function updateStatus($id, $status) {
            $id = intval($id);
            $newStatus = ($status == 1) ? 0 : 1; // Đảo trạng thái
        
            $query = "UPDATE tbl_loaisp SET trangthai = $newStatus WHERE id_loai = $id";
        
            $result = $this->db->update($query); // Gọi phương thức update
        
            return ($result) ? true : false; // Trả về kết quả cập nhật
        }
        
    }
?>