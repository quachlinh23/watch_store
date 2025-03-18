<?php
    include_once(__DIR__ . '/../lib/database.php');
    include_once(__DIR__ . '/../helpers/format.php');

    class slider{
        private $db;
        private $fm;

        public function __construct(){
            $this->db = new Database();
            $this->fm = new Format();
        }

        // Hàm thêm slider
        public function insert($data, $files) {
            $slider_name = mysqli_real_escape_string($this->db->link, $data['slide_name']);

            $permited = array('jpg', 'jpeg', 'png', 'gif');
            $file_name = $_FILES['image']['name'];
            $file_size = $_FILES['image']['size'];
            $file_temp = $_FILES['image']['tmp_name'];
        
            $div = explode('.', $file_name);
            $file_ext = strtolower(end($div));
            $unique_image = substr(md5(time()), 0, 10) . '.' . $file_ext;
            $uploaded_image = "uploads/slider/" . $unique_image;
        

            move_uploaded_file($file_temp, $uploaded_image);
            $query = "INSERT INTO tbl_slider (tenSlider,hinhAnh)VALUES ('$slider_name','$uploaded_image')";
        
            $result = $this->db->insert($query);
        
            if ($result) {
                $alert = "<span class='success'>Thêm slider thành công</span>";
                return $alert;
            } else {
                $alert = "<span class='error'>Thêm silder thất bại</span>";
                return $alert;
            }
        }

        // Hàm hiển thị tất cả các slider
        public function show(){
            $query = "SELECT * FROM tbl_slider";
            $result = $this->db->select($query);
            return $result;
        }

        // Hàm xóa slider
        public function delete($id){
            $id = intval($id);
            $query = "DELETE FROM tbl_slider WHERE id_slider=$id";
            $result = $this->db->delete($query);
            return $result;
        }

        public function update($id, $data, $files) {
            $tenSlider = mysqli_real_escape_string($this->db->link, $data['slide_name']);
            $image = $files['image'];
            $id = intval($id);
        
            if ($id <= 0) {
                return "<span class='error'>ID không hợp lệ!</span>";
            }
        
            // Lấy thông tin slider hiện tại để lấy tên ảnh cũ
            $query = "SELECT hinhAnh FROM tbl_slider WHERE id_slider = ?";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
        
            if (!$result || $result->num_rows == 0) {
                return "<span class='error'>Slider không tồn tại!</span>";
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
                $imageName = "uploads/slider/" . time() . '-' . uniqid() . '.' . $file_ext;
        
                // Kiểm tra thư mục và tạo nếu chưa có
                if (!is_dir("uploads/slider/")) {
                    mkdir("uploads/slider/", 0777, true);
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
            $query = "UPDATE tbl_slider SET tenSlider = ?, hinhAnh = ? WHERE id_slider = ?";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("ssi", $tenSlider, $imageName, $id);
            $update = $stmt->execute();
        
            if ($update) {
                return "<span class='success'>Cập nhật thành công!</span>";
            } else {
                return "<span class='error'>Cập nhật thất bại!</span>";
            }
        }

        public function search($data) {
            $query = "SELECT * FROM tbl_slider WHERE tenSlider LIKE '%" . $data . "%'";
            $result = $this->db->select($query);
            return $result;
        }
    }
?>