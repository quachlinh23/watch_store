<?php
    include_once(__DIR__ . '/../lib/database.php');
    include_once(__DIR__ . '/../helpers/format.php');

    class product {
        private $db;
        private $fm;

        public function __construct() {
            $this->db = new Database();
            $this->fm = new Format();
        }

        public function insert($data, $files) {
            $product_name = mysqli_real_escape_string($this->db->link, $data['product_name']);
            $product_desc = mysqli_real_escape_string($this->db->link, $data['product_desc']);
            $product_type = intval($data['product_type']);
            $product_brand = intval($data['product_brand']);
        
            $main_upload_dir = "uploads/product/";
            $sub_upload_dir = "uploads/product/subs/";
            $this->ensureDirectory($main_upload_dir);
            $this->ensureDirectory($sub_upload_dir);
        
            $permited = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff');
            $max_size = 5 * 1024 * 1024; // 5MB
        
            $main_image = '';
            if (!empty($files['main_image']['name'])) {
                $main_image = $this->uploadImage($files['main_image'], $main_upload_dir, $permited, $max_size);
                if (is_string($main_image) && strpos($main_image, "Lỗi") === 0) {
                    return $main_image;
                }
            }
        
            $query = "INSERT INTO tbl_sanpham (tenSanPham, id_loai, id_thuonghieu, mota, hinhAnh) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->link->prepare($query);
            if (!$stmt) {
                $this->cleanup($main_image);
                return "Lỗi truy vấn: " . $this->db->link->error;
            }
            $stmt->bind_param("siiss", $product_name, $product_type, $product_brand, $product_desc, $main_image);
            if (!$stmt->execute()) {
                $this->cleanup($main_image);
                return "Thêm sản phẩm thất bại: " . $stmt->error;
            }
        
            $product_id = $this->db->link->insert_id;
            $uploaded_sub_images = [];
        
            if (isset($files['product_images']) && !empty($files['product_images']['name']) && !empty($files['product_images']['name'][0])) {
                $image_count = count(array_filter($files['product_images']['name']));
                if ($image_count > 3) {
                    $this->rollback($product_id, $main_image, []);
                    return "Chỉ được chọn tối đa 3 ảnh phụ!";
                }
        
                for ($i = 0; $i < $image_count; $i++) {
                    if (empty($files['product_images']['name'][$i])) continue;
        
                    $sub_image = $this->uploadImage([
                        'name' => $files['product_images']['name'][$i],
                        'size' => $files['product_images']['size'][$i],
                        'tmp_name' => $files['product_images']['tmp_name'][$i],
                        'error' => $files['product_images']['error'][$i]
                    ], $sub_upload_dir, $permited, $max_size);
        
                    if (is_string($sub_image) && strpos($sub_image, "Lỗi") === 0) {
                        $this->rollback($product_id, $main_image, $uploaded_sub_images);
                        return $sub_image;
                    }
        
                    $uploaded_sub_images[] = $sub_image;
        
                    $query = "INSERT INTO tbl_anhspphu (id_sanpham, hinhAnh) VALUES (?, ?)";
                    $stmt = $this->db->link->prepare($query);
                    if (!$stmt || !$stmt->bind_param("is", $product_id, $sub_image) || !$stmt->execute()) {
                        $this->rollback($product_id, $main_image, $uploaded_sub_images);
                        return "Lỗi khi lưu ảnh phụ: " . ($stmt ? $stmt->error : $this->db->link->error);
                    }
                }
            }
        
            return "Thêm sản phẩm thành công";
        }

        public function update($id, $data, $files) {
            $id = intval($id);
            $product_name = mysqli_real_escape_string($this->db->link, $data['product_name']);
            $product_desc = mysqli_real_escape_string($this->db->link, $data['product_desc']);
            $product_type = intval($data['product_type']);
            $product_brand = intval($data['product_brand']);
            $deleted_sub_images = isset($data['delete_sub_images']) ? json_decode($data['delete_sub_images'], true) : [];

            $main_upload_dir = "uploads/product/";
            $sub_upload_dir = "uploads/product/subs/";
            $this->ensureDirectory($main_upload_dir);
            $this->ensureDirectory($sub_upload_dir);

            $permited = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff');
            $max_size = 5 * 1024 * 1024;

            $current_product = $this->getById($id);
            if (!$current_product) {
                return "Sản phẩm không tồn tại!";
            }
            $old_main_image = $current_product['hinhAnh'];

            $main_image = $old_main_image;
            if (!empty($files['main_image']['name'])) {
                $main_image = $this->uploadImage($files['main_image'], $main_upload_dir, $permited, $max_size);
                if (is_string($main_image) && strpos($main_image, "Lỗi") === 0) {
                    return $main_image;
                }
            }

            $query = "UPDATE tbl_sanpham SET tenSanPham = ?, id_loai = ?, id_thuonghieu = ?, mota = ?, hinhAnh = ? WHERE maSanPham = ?";
            $stmt = $this->db->link->prepare($query);
            if (!$stmt) {
                $this->cleanup($main_image !== $old_main_image ? $main_image : '');
                return "Lỗi truy vấn: " . $this->db->link->error;
            }
            $stmt->bind_param("siissi", $product_name, $product_type, $product_brand, $product_desc, $main_image, $id);
            if (!$stmt->execute()) {
                $this->cleanup($main_image !== $old_main_image ? $main_image : '');
                return "Cập nhật sản phẩm thất bại: " . $stmt->error;
            }

            if ($main_image !== $old_main_image && file_exists($old_main_image)) {
                unlink($old_main_image);
            }

            // Xử lý ảnh phụ
            $current_sub_images = [];
            $sub_images_result = $this->getSubImages($id);
            if ($sub_images_result) {
                while ($row = $sub_images_result->fetch_assoc()) {
                    $current_sub_images[] = $row['hinhAnh'];
                }
            }

            // Xóa ảnh phụ được đánh dấu
            if (!empty($deleted_sub_images)) {
                foreach ($deleted_sub_images as $img) {
                    if (in_array($img, $current_sub_images)) {
                        $query = "DELETE FROM tbl_anhspphu WHERE id_sanpham = ? AND hinhAnh = ?";
                        $stmt = $this->db->link->prepare($query);
                        $stmt->bind_param("is", $id, $img);
                        $stmt->execute();
                        if (file_exists($img)) {
                            unlink($img);
                        }
                    }
                }
            }

            // Thêm ảnh phụ mới
            if (isset($files['product_images']) && !empty($files['product_images']['name']) && !empty($files['product_images']['name'][0])) {
                $remaining_images = array_diff($current_sub_images, $deleted_sub_images);
                $image_count = count($remaining_images) + count(array_filter($files['product_images']['name']));
                if ($image_count > 3) {
                    return "Tổng số ảnh phụ không được vượt quá 3!";
                }

                $uploaded_sub_images = [];
                for ($i = 0; $i < count($files['product_images']['name']); $i++) {
                    if (empty($files['product_images']['name'][$i])) continue;

                    $sub_image = $this->uploadImage([
                        'name' => $files['product_images']['name'][$i],
                        'size' => $files['product_images']['size'][$i],
                        'tmp_name' => $files['product_images']['tmp_name'][$i],
                        'error' => $files['product_images']['error'][$i]
                    ], $sub_upload_dir, $permited, $max_size);

                    if (is_string($sub_image) && strpos($sub_image, "Lỗi") === 0) {
                        foreach ($uploaded_sub_images as $img) {
                            $this->cleanup($img);
                        }
                        return $sub_image;
                    }

                    $uploaded_sub_images[] = $sub_image;

                    $query = "INSERT INTO tbl_anhspphu (id_sanpham, hinhAnh) VALUES (?, ?)";
                    $stmt = $this->db->link->prepare($query);
                    $stmt->bind_param("is", $id, $sub_image);
                    if (!$stmt->execute()) {
                        foreach ($uploaded_sub_images as $img) {
                            $this->cleanup($img);
                        }
                        return "Lỗi khi lưu ảnh phụ: " . $stmt->error;
                    }
                }
            }

            return "Cập nhật sản phẩm thành công";
        }

        private function ensureDirectory($dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true) or die("Không thể tạo thư mục $dir!");
            }
        }
        
        private function uploadImage($file, $target_dir, $permited, $max_size) {
            if ($file['error'] != 0) return "Lỗi file upload!";
            
            $file_name = $file['name'];
            $file_size = $file['size'];
            $file_temp = $file['tmp_name'];
            $div = explode('.', $file_name);
            $file_ext = strtolower(end($div));
            $unique_image = substr(md5(time() . rand()), 0, 10) . '.' . $file_ext;
            $target_path = $target_dir . $unique_image;
        
            if (!in_array($file_ext, $permited)) {
                return "Chỉ chấp nhận định dạng ảnh: " . implode(', ', $permited);
            }
            if ($file_size > $max_size) {
                return "Kích thước ảnh không được vượt quá 5MB!";
            }
            if (!move_uploaded_file($file_temp, $target_path)) {
                return "Lỗi khi tải ảnh lên server!";
            }
            return $target_path;
        }
        
        private function cleanup($file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        private function rollback($product_id, $main_image, $sub_images) {
            $this->cleanup($main_image);
            foreach ($sub_images as $sub_image) {
                $this->cleanup($sub_image);
            }
            $this->delete($product_id);
        }

        public function show() {
            $query = "SELECT sp.*, lsp.tenLoai, th.tenThuongHieu
                        FROM tbl_sanpham AS sp
                        LEFT JOIN tbl_loaisp AS lsp ON sp.id_loai = lsp.id_loai
                        LEFT JOIN tbl_thuonghieu AS th ON sp.id_thuonghieu = th.id_thuonghieu";
            $result = $this->db->select($query);
            if (!$result) {
                return false;
            }
            return $result;
        }

        public function delete($id) {
            $id = intval($id);
            if ($id <= 0) {
                return false;
            }

            $query = "SELECT hinhAnh FROM tbl_anhspphu WHERE id_sanpham = ?";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (file_exists($row['hinhAnh'])) {
                        unlink($row['hinhAnh']);
                    }
                }
            }

            $query = "DELETE FROM tbl_anhspphu WHERE id_sanpham = ?";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $query = "SELECT hinhAnh FROM tbl_sanpham WHERE maSanPham = ?";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                $row = $result->fetch_assoc();
                if (file_exists($row['hinhAnh'])) {
                    unlink($row['hinhAnh']);
                }
            }

            $query = "DELETE FROM tbl_sanpham WHERE maSanPham = ?";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("i", $id);
            $result = $stmt->execute();
            return $result;
        }

        public function getById($id) {
            $id = intval($id);
            $query = "SELECT sp.*, th.tenThuongHieu 
                        FROM tbl_sanpham sp 
                        LEFT JOIN tbl_thuonghieu th ON sp.id_thuonghieu = th.id_thuonghieu 
                        WHERE sp.maSanPham = ?";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }

        public function getSubImages($product_id) {
            $product_id = intval($product_id);
            $query = "SELECT hinhAnh FROM tbl_anhspphu WHERE id_sanpham = ?";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            return $stmt->get_result();
        }

        public function updateStatus($id, $status) {
            $id = intval($id);
            $newStatus = ($status == 1) ? 0 : 1;
            $query = "UPDATE tbl_sanpham SET trangthai = $newStatus WHERE maSanPham = $id";
            $result = $this->db->update($query);
            return ($result) ? true : false;
        }

        public function getProductById($id){
            // Chuyển id sang kiểu integer để bảo mật
            $id = intval($id);
        
            // Câu lệnh SQL để lấy thông tin từ cả hai bảng tbl_sanpham và tbl_chitietsanpham
            $query = "
                SELECT sp.*, cts.*, th.tenThuongHieu
                FROM tbl_sanpham sp
                JOIN tbl_chitietsanpham cts ON sp.maSanPham = cts.masanpham
                JOIN tbl_thuonghieu th ON sp.id_thuonghieu = th.id_thuonghieu
                WHERE sp.maSanPham = ? LIMIT 1
            ";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result;
        }
        
    }
?>