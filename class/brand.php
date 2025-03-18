<?php
    include_once(__DIR__ . '/../lib/database.php');
    include_once(__DIR__ . '/../helpers/format.php');
    
    class brand {
        private $db;
        private $fm;

        public function __construct(){
            $this->db = new Database();
            $this->fm = new Format();
        }

        public function add($data, $files) {
            $brand_name = mysqli_real_escape_string($this->db->link, $data['brand_name']);
            $brand_desc = mysqli_real_escape_string($this->db->link, $data['brand_desc']);

            $permited = array('jpg', 'jpeg', 'png', 'gif');
            $file_name = $_FILES['image']['name'];
            $file_size = $_FILES['image']['size'];
            $file_temp = $_FILES['image']['tmp_name'];
        
            $div = explode('.', $file_name);
            $file_ext = strtolower(end($div));
            $unique_image = substr(md5(time()), 0, 10) . '.' . $file_ext;
            $uploaded_image = "uploads/brand/" . $unique_image;
        

            move_uploaded_file($file_temp, $uploaded_image);
            $query = "INSERT INTO tbl_thuonghieu (tenThuongHieu,mota,hinhAnh,trangthai)VALUES ('$brand_name','$brand_desc','$uploaded_image',1)";
        
            $result = $this->db->insert($query);
            if ($result) {
                $alert = "<span class='success'>Thêm slider thành công</span>";
                return $alert;
            } else {
                $alert = "<span class='error'>Thêm silder thất bại</span>";
                return $alert;
            }
        }

        public function update($id, $data, $files) {
            $tenSlider = mysqli_real_escape_string($this->db->link, $data['brand_name_update']);
            $image = $files['image'];
            $id = intval($id);
            $tenmota = mysqli_real_escape_string($this->db->link, $data['brand_desc_update']);
        
            if ($id <= 0) {
                return "<span class='error'>ID không hợp lệ!</span>";
            }
        
            // Lấy thông tin slider hiện tại để lấy tên ảnh cũ
            $query = "SELECT hinhAnh FROM tbl_thuonghieu WHERE id_thuonghieu = ?";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
        
            if (!$result || $result->num_rows == 0) {
                return "<span class='error'>Brand không tồn tại!</span>";
            }
        
            $row = $result->fetch_assoc();
            $oldImage = $row['hinhAnh'];
        
            // Giữ nguyên ảnh cũ nếu không có ảnh mới
            $imageName = $oldImage;
        
            // Kiểm tra xem có ảnh mới không
            if ($image['size'] > 0) {
                $permited = array('jpg', 'jpeg', 'png', 'gif');
                $file_ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
                $file_size = $image['size'];
                $file_temp = $image['tmp_name'];
        
                if (!in_array($file_ext, $permited)) {
                    return "<span class='error'>Chỉ chấp nhận định dạng: " . implode(', ', $permited) . "</span>";
                }
        
                if ($file_size > 5 * 1024 * 1024) {
                    return "<span class='error'>Kích thước ảnh không được vượt quá 5MB!</span>";
                }
        
                // Tạo tên ảnh duy nhất
                $imageName = "uploads/brand/" . time() . '-' . uniqid() . '.' . $file_ext;
        
                // Kiểm tra thư mục và tạo nếu chưa có
                if (!is_dir("uploads/brand/")) {
                    mkdir("uploads/brand/", 0777, true);
                }
        
                // Upload ảnh mới
                if (move_uploaded_file($file_temp, $imageName)) {
                    // Xóa ảnh cũ nếu tồn tại
                    if (!empty($oldImage) && file_exists($oldImage)) {
                        unlink($oldImage);
                    }
                } else {
                    return "<span class='error'>Lỗi khi tải ảnh lên!</span>";
                }
            }
        
            // Cập nhật thông tin slider
            $query = "UPDATE tbl_thuonghieu SET tenThuongHieu = ?, hinhAnh = ?, mota = ? WHERE id_thuonghieu = ?";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("sssi", $tenSlider, $imageName, $tenmota, $id);
            $update = $stmt->execute();
        
            if ($update) {
                return "<span class='success'>Cập nhật thành công!</span>";
            } else {
                return "<span class='error'>Cập nhật thất bại!</span>";
            }
        }

        public function show(){
            $querry = "SELECT * FROM tbl_thuonghieu"; 
            $result = $this->db->select($querry);
            return $result;
        }

        public function getnamebyid($id) {
            $id = intval($id);
            $query = "SELECT * FROM tbl_thuonghieu WHERE id_thuonghieu = $id";
            $result = $this->db->select($query);
            return $result;
        }

        public function search($data) {
            $query = "SELECT * FROM tbl_thuonghieu WHERE tenThuongHieu LIKE '%" . $data . "%'";
            $result = $this->db->select($query);
            return $result;
        }

        public function updateStatus($id, $status) {
            $id = intval($id);
            $newStatus = ($status == 1) ? 0 : 1; // Đảo trạng thái
        
            $query = "UPDATE tbl_thuonghieu SET trangthai = $newStatus WHERE id_thuonghieu = $id";
        
            $result = $this->db->update($query); // Gọi phương thức update
        
            return ($result) ? true : false; // Trả về kết quả cập nhật
        }
    }
?>