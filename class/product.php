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
        
            // Định nghĩa thư mục upload
            $main_upload_dir = "uploads/product/";
            $sub_upload_dir = "uploads/product/subs/";
            $this->ensureDirectory($main_upload_dir);
            $this->ensureDirectory($sub_upload_dir);
        
            $permited = array('jpg', 'jpeg', 'png', 'gif');
            $max_size = 5 * 1024 * 1024; // 5MB
        
            // Xử lý ảnh chính
            $main_image = '';
            if (!empty($files['main_image']['name'])) {
                $main_image = $this->uploadImage($files['main_image'], $main_upload_dir, $permited, $max_size);
                if (is_string($main_image) && strpos($main_image, "Lỗi") === 0) {
                    return $main_image;
                }
            }
        
            // Thêm sản phẩm vào tbl_sanpham
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
        
            // Xử lý ảnh phụ
            if (isset($files['product_images']) && !empty($files['product_images']['name']) && !empty($files['product_images']['name'][0])) {
                $image_count = count(array_filter($files['product_images']['name']));
                // if ($image_count > 3) {
                //     $this->rollback($product_id, $main_image, []);
                //     return "Chỉ được chọn tối đa 3 ảnh phụ!";
                // }
        
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
        
                    // Lưu ảnh phụ vào tbl_anhspphu
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
            $main_upload_dir = "../uploads/product/";
            $sub_upload_dir = "../uploads/product/subs/";
            $this->ensureDirectory($main_upload_dir);
            $this->ensureDirectory($sub_upload_dir);
        
            $permited = array('jpg', 'jpeg', 'png', 'gif');
            $max_size = 5 * 1024 * 1024; // 5MB
        
            // Lấy thông tin sản phẩm hiện tại để xử lý ảnh cũ
            $current_product = $this->getById($id);
            if (!$current_product) {
                return "Không tìm thấy sản phẩm để cập nhật!";
            }
        
            $old_main_image = $current_product['hinhAnh'];
            $new_main_image = $old_main_image;
        
            if (!empty($files['main_image']['name'])) {
                $new_main_image = $this->uploadImage($files['main_image'], $main_upload_dir, $permited, $max_size);
                if (is_string($new_main_image) && strpos($new_main_image, "Lỗi") === 0) {
                    return $new_main_image;
                }
                // Xóa ảnh chính cũ nếu upload ảnh mới thành công
                if ($old_main_image && file_exists($old_main_image)) {
                    unlink($old_main_image);
                }
            }
        
            // Chuẩn bị truy vấn cập nhật sản phẩm
            $query = "UPDATE tbl_sanpham SET tenSanPham = ?, id_loai = ?, id_thuonghieu = ?, mota = ?, hinhAnh = ? WHERE maSanPham = ?";
            $stmt = $this->db->link->prepare($query);
            if (!$stmt) {
                return "Lỗi truy vấn: " . $this->db->link->error;
            }
        
            // Gán giá trị vào biến để bind
            $name = $data['name'];
            $type = intval($data['type']);
            $brand = intval($data['brand']);
            $description = $data['description'];
            $main_image = $new_main_image;
        
            // Bind các biến
            $stmt->bind_param("siissi", $name, $type, $brand, $description, $main_image, $id);
            if (!$stmt->execute()) {
                return "Cập nhật sản phẩm thất bại: " . $stmt->error;
            }
        
            // Xóa ảnh phụ nếu có
            if (isset($data['delete']) && is_array($data['delete'])) {
                foreach ($data['delete'] as $img) {
                    $query = "DELETE FROM tbl_anhspphu WHERE hinhAnh LIKE ? AND id_sanpham = ?";
                    $stmt = $this->db->link->prepare($query);
                    if (!$stmt) {
                        return "Lỗi truy vấn: " . $this->db->link->error;

                    }
                    $img = '%'.$img.'%';
                    $stmt->bind_param("si", $img, $id);
                    if (!$stmt->execute()) {
                        return "Xóa ảnh phụ thất bại: " . $stmt->error;
                    }
                }
            }
        
            // Thêm ảnh phụ mới
            $image_paths = [];
            if (isset($_FILES['sub_img_add']) && !empty($_FILES['sub_img_add']['name'][0])) {
                $files = $_FILES['sub_img_add'];
        
                if (!is_array($files['name'])) {
                    $result = $this->uploadImage($files, $sub_upload_dir, $permited, $max_size);
                    if (strpos($result, "Lỗi") === false && strpos($result, "Không") === false) {
                        $image_paths[] = $result;
                    } else {
                        echo json_encode(['success' => false, 'message' => $result]);
                        exit;
                    }
                } else {
                    foreach ($files['name'] as $key => $file_name) {
                        $file = [
                            'name' => $files['name'][$key],
                            'type' => $files['type'][$key],
                            'tmp_name' => $files['tmp_name'][$key],
                            'error' => $files['error'][$key],
                            'size' => $files['size'][$key]
                        ];
        
                        $result = $this->uploadImage($file, $sub_upload_dir, $permited, $max_size);
                        if (strpos($result, "Lỗi") === false && strpos($result, "Không") === false) {
                            $image_paths[] = $result;
                        } else {
                            echo json_encode(['success' => false, 'message' => $result . " tại file thứ " . ($key + 1)]);
                            exit;
                        }
                    }
                }
        
                // Thêm ảnh phụ vào database
                if (!empty($image_paths)) {
                    if (!isset($id) || !is_numeric($id)) {
                        echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ!']);
                        exit;
                    }
        
                    $sql = "INSERT INTO tbl_anhspphu (hinhAnh, id_sanpham) VALUES (?, ?)";
                    $stmt = $this->db->link->prepare($sql);
                    if (!$stmt) {
                        echo json_encode(['success' => false, 'message' => "Lỗi truy vấn: " . $this->db->link->error]);
                        exit;
                    }
        
                    foreach ($image_paths as $image_path) {
                        $product_id = $id; // Gán ID vào biến
                        $stmt->bind_param("si", $image_path, $product_id);
                        if (!$stmt->execute()) {
                            echo json_encode(['success' => false, 'message' => "Thêm ảnh phụ thất bại: " . $stmt->error]);
                            exit;
                        }
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Không có ảnh nào được upload thành công!']);
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
            if (empty($file) || !isset($file['name'])) {
                return "Không có file nào được gửi!";
            }
        
            if ($file['error'] != 0) {
                return "Lỗi file upload!";
            }
        
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
                error_log("Failed to move $file_temp to $target_path");
                return "Lỗi khi tải ảnh lên server!";
            }
            if (!file_exists($target_path)) {
                error_log("File does not exist after move: $target_path");
                return "File không tồn tại sau khi di chuyển!";
            }
            
            $relative_path = str_replace('../', '', $target_path);
            return $relative_path;
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
                        LEFT JOIN tbl_thuonghieu AS th ON sp.id_thuonghieu = th.id_thuonghieu
                        ORDER BY maSanPham ASC";
            return $this->db->select($query);
        }

        public function delete($id) {
            $id = intval($id);
            if ($id <= 0) {
                return false;
            }

            // Xóa ảnh phụ trước
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

            // Xóa ảnh chính và sản phẩm
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
            $query = "SELECT sp.*, lsp.tenLoai, th.tenThuongHieu,GROUP_CONCAT(img.hinhAnh) AS anhphu
                        FROM tbl_sanpham AS sp
                        LEFT JOIN tbl_loaisp AS lsp ON sp.id_loai = lsp.id_loai
                        LEFT JOIN tbl_thuonghieu AS th ON sp.id_thuonghieu = th.id_thuonghieu
                        LEFT JOIN tbl_anhspphu AS img ON sp.maSanPham = img.id_sanPham
                        WHERE sp.maSanPham = ?
                        GROUP BY sp.maSanPham";
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
            $newStatus = ($status == 1) ? 0 : 1; // Đảo trạng thái
        
            $query = "UPDATE tbl_sanpham SET trangthai = $newStatus WHERE maSanPham = $id";
        
            $result = $this->db->update($query); // Gọi phương thức update
        
            return ($result) ? true : false; // Trả về kết quả cập nhật
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

        public function getSubImgProductById($idProduct) {
            $subImages = array();
            $idProduct = intval($idProduct);
            $sql = "SELECT hinhAnh FROM tbl_anhspphu WHERE id_sanpham = ?";
            $stmt = $this->db->link->prepare($sql);
            $stmt->bind_param("i", $idProduct);
            $stmt->execute();
            $result = $stmt->get_result();
        
            while ($row = $result->fetch_assoc()) {
                $subImages[] = $row['hinhAnh'];
            }
        
            return $subImages;
        }

        public function searchInfoProduct($data) {
            $data = mysqli_real_escape_string($this->db->link, $data); // Bảo vệ chống SQL Injection
            $query = "SELECT sp.*, lsp.tenLoai, th.tenThuongHieu
                        FROM tbl_sanpham AS sp
                        LEFT JOIN tbl_loaisp AS lsp ON sp.id_loai = lsp.id_loai
                        LEFT JOIN tbl_thuonghieu AS th ON sp.id_thuonghieu = th.id_thuonghieu
                        WHERE sp.tenSanPham LIKE ?
                            OR lsp.tenLoai LIKE ?
                            OR th.tenThuongHieu LIKE ?";
            
            $result = $this->db->link->prepare($query);
                $search = "%$data%";
                $result->bind_param("sss", $search, $search, $search);

                $result->execute();

                $products = [];
                $resultSet = $result->get_result();
                while ($row = $resultSet->fetch_assoc()) {
                    $products[] = $row;
                }

                // Trả về kết quả
                return $products;
        }

        public function insertSpec($maSanPham, $specData) {
            $query = "INSERT INTO tbl_thongso (maSanPham, loaiDay, pin, matKinh, chongNuoc, chucNang) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->link->prepare($query);
            if ($stmt === false) {
                return "Lỗi khi chuẩn bị truy vấn: " . $this->db->link->error;
            }
    
            $stmt->bind_param("ssssss",
                $maSanPham,
                $specData['loaiDay'],
                $specData['pin'],
                $specData['matKinh'],
                $specData['chongNuoc'],
                $specData['chucNang']
            );
            $result = $stmt->execute();
            $stmt->close();
            
            if ($result === false) {
                return "Lỗi khi thêm thông số: " . $this->db->link->error;
            }
            return true;
        }

        public function updateSpec($maSanPham, $specData) {
            // Kiểm tra xem thông số đã tồn tại chưa
            $query = "SELECT id FROM tbl_thongso WHERE maSanPham = ?";
            $stmt = $this->db->link->prepare($query);
            if ($stmt === false) {
                return "Lỗi khi chuẩn bị truy vấn: " . $this->db->link->error;
            }
            
            $stmt->bind_param("s", $maSanPham);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Cập nhật thông số
                $query = "UPDATE tbl_thongso
                        SET loaiDay = ?, pin = ?, matKinh = ?, chongNuoc = ?, chucNang = ? 
                        WHERE maSanPham = ?";
                $stmt = $this->db->link->prepare($query);
                if ($stmt === false) {
                    return "Lỗi khi chuẩn bị truy vấn: " . $this->db->link->error;
                }
                $stmt->bind_param("ssssss",
                    $specData['loaiDay'],
                    $specData['pin'],
                    $specData['matKinh'],
                    $specData['chongNuoc'],
                    $specData['chucNang'],
                    $maSanPham
                );
            } else {
                // Thêm mới nếu chưa có
                $query = "INSERT INTO tbl_thongso (maSanPham, loaiDay, pin, matKinh, chongNuoc, chucNang) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->db->link->prepare($query);
                if ($stmt === false) {
                    return "Lỗi khi chuẩn bị truy vấn: " . $this->db->link->error;
                }
                $stmt->bind_param("ssssss",
                    $maSanPham,
                    $specData['loaiDay'],
                    $specData['pin'],
                    $specData['matKinh'],
                    $specData['chongNuoc'],
                    $specData['chucNang']
                );
            }
            
            $result = $stmt->execute();
            $stmt->close();
            
            if ($result === false) {
                return "Lỗi khi cập nhật thông số: " . $this->db->link->error;
            }
            return true;
        }

        public function getSpecs($maSanPham) {
            $query = "SELECT loaiDay, pin, matKinh, chongNuoc, chucNang
                    FROM tbl_thongso
                    WHERE maSanPham = ?";
            $stmt = $this->db->link->prepare($query);
            if ($stmt === false) {
                error_log("Lỗi khi chuẩn bị truy vấn: " . $this->db->link->error);
                return [];
            }
            
            $stmt->bind_param("s", $maSanPham);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                error_log("Không tìm thấy thông số cho maSanPham: " . $maSanPham);
            }
            $specs = $result->fetch_assoc() ?: [];
            $stmt->close();
            return $specs;
        }
    }
?>