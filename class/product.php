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
        // Kiểm tra dữ liệu đầu vào từ form
        if (empty($data['product_name'])) {
            return "Vui lòng nhập tên sản phẩm!";
        }
        if (empty($data['product_desc'])) {
            return "Vui lòng điền mô tả cho sản phẩm!";
        }
        if (empty($data['product_type']) || $data['product_type'] == 0) {
            return "Vui lòng chọn loại sản phẩm!";
        }
        if (empty($data['product_brand']) || $data['product_brand'] == 0) {
            return "Vui lòng chọn thương hiệu cho sản phẩm!";
        }
    
        $product_name = mysqli_real_escape_string($this->db->link, $data['product_name']);
        $product_desc = mysqli_real_escape_string($this->db->link, $data['product_desc']);
        $product_type = intval($data['product_type']);
        $product_brand = intval($data['product_brand']);
    
        // Kiểm tra và tạo thư mục upload nếu chưa tồn tại
        $main_upload_dir = "uploads/product/";
        $sub_upload_dir = "uploads/product/subs/";
        if (!is_dir($main_upload_dir)) {
            mkdir($main_upload_dir, 0777, true) or die("Không thể tạo thư mục uploads/product!");
        }
        if (!is_dir($sub_upload_dir)) {
            mkdir($sub_upload_dir, 0777, true) or die("Không thể tạo thư mục uploads/product/subs!");
        }
    
        // Xử lý ảnh chính
        $main_image = '';
        if (!empty($files['main_image']['name'])) {
            $file_name = $files['main_image']['name'];
            $file_size = $files['main_image']['size'];
            $file_temp = $files['main_image']['tmp_name'];
            $div = explode('.', $file_name);
            $file_ext = strtolower(end($div));
            $unique_image = substr(md5(time()), 0, 10) . '.' . $file_ext;
            $main_image = $main_upload_dir . $unique_image;
    
            $permited = array('jpg', 'jpeg', 'png', 'gif');
            if (!in_array($file_ext, $permited)) {
                return "Chỉ chấp nhận định dạng ảnh: " . implode(', ', $permited);
            }
            if ($file_size > 5 * 1024 * 1024) {
                return "Kích thước ảnh chính không được vượt quá 5MB!";
            }
            if (!move_uploaded_file($file_temp, $main_image)) {
                return "Lỗi khi tải ảnh chính lên server! Kiểm tra quyền thư mục hoặc dung lượng server.";
            }
        } else {
            return "Vui lòng chọn ảnh chính!";
        }
    
        // Thêm sản phẩm vào tbl_sanpham
        $query = "INSERT INTO tbl_sanpham (tenSanPham, id_loai, id_thuonghieu, mota, hinhAnh, trangthai) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->link->prepare($query);
        if (!$stmt) {
            if (file_exists($main_image)) {
                unlink($main_image);
            }
            return "Lỗi truy vấn: " . $this->db->link->error;
        }
        $trangthai = 1;
        $stmt->bind_param("siissi", $product_name, $product_type, $product_brand, $product_desc, $main_image, $trangthai);
        $result = $stmt->execute();
        if ($result) {
            $product_id = $this->db->link->insert_id;
    
            // Xử lý các ảnh phụ
            if (!empty($files['product_images']['name'][0])) {
                $image_count = count(array_filter($files['product_images']['name']));
                if ($image_count > 3) {
                    if (file_exists($main_image)) {
                        unlink($main_image);
                    }
                    $this->delete($product_id);
                    return "Chỉ được chọn tối đa 3 ảnh phụ!";
                }
    
                for ($i = 0; $i < $image_count; $i++) {
                    if (empty($files['product_images']['name'][$i])) continue;
    
                    $file_name = $files['product_images']['name'][$i];
                    $file_size = $files['product_images']['size'][$i];
                    $file_temp = $files['product_images']['tmp_name'][$i];
                    $div = explode('.', $file_name);
                    $file_ext = strtolower(end($div));
                    $unique_image = substr(md5(time() . $i), 0, 10) . '.' . $file_ext;
                    $uploaded_image = $sub_upload_dir . $unique_image;
    
                    if (!in_array($file_ext, $permited)) {
                        if (file_exists($main_image)) {
                            unlink($main_image);
                        }
                        for ($j = 0; $j < $i; $j++) {
                            $prev_image = $sub_upload_dir . substr(md5(time() . $j), 0, 10) . '.' . strtolower(end(explode('.', $files['product_images']['name'][$j])));
                            if (file_exists($prev_image)) {
                                unlink($prev_image);
                            }
                        }
                        $this->delete($product_id);
                        return "Chỉ chấp nhận định dạng ảnh phụ: " . implode(', ', $permited);
                    }
                    if ($file_size > 5 * 1024 * 1024) {
                        if (file_exists($main_image)) {
                            unlink($main_image);
                        }
                        for ($j = 0; $j < $i; $j++) {
                            $prev_image = $sub_upload_dir . substr(md5(time() . $j), 0, 10) . '.' . strtolower(end(explode('.', $files['product_images']['name'][$j])));
                            if (file_exists($prev_image)) {
                                unlink($prev_image);
                            }
                        }
                        $this->delete($product_id);
                        return "Kích thước ảnh phụ không được vượt quá 5MB!";
                    }
                    if (!move_uploaded_file($file_temp, $uploaded_image)) {
                        if (file_exists($main_image)) {
                            unlink($main_image);
                        }
                        for ($j = 0; $j < $i; $j++) {
                            $prev_image = $sub_upload_dir . substr(md5(time() . $j), 0, 10) . '.' . strtolower(end(explode('.', $files['product_images']['name'][$j])));
                            if (file_exists($prev_image)) {
                                unlink($prev_image);
                            }
                        }
                        $this->delete($product_id);
                        return "Lỗi khi tải ảnh phụ lên server!";
                    }
    
                    // Lưu ảnh phụ vào tbl_anhspphu
                    $query = "INSERT INTO tbl_anhspphu (id_sanpham, hinhAnh) VALUES (?, ?)";
                    $stmt = $this->db->link->prepare($query);
                    if (!$stmt || !$stmt->bind_param("is", $product_id, $uploaded_image) || !$stmt->execute()) {
                        if (file_exists($main_image)) {
                            unlink($main_image);
                        }
                        for ($j = 0; $j <= $i; $j++) {
                            $prev_image = $sub_upload_dir . substr(md5(time() . $j), 0, 10) . '.' . strtolower(end(explode('.', $files['product_images']['name'][$j])));
                            if (file_exists($prev_image)) {
                                unlink($prev_image);
                            }
                        }
                        $this->delete($product_id);
                        return "Lỗi khi lưu ảnh phụ: " . ($stmt ? $stmt->error : $this->db->link->error);
                    }
                }
            }
    
            return "Thêm sản phẩm thành công!";
        } else {
            if (file_exists($main_image)) {
                unlink($main_image);
            }
            return "Thêm sản phẩm thất bại: " . $stmt->error;
        }
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

    // Hàm lấy thông tin sản phẩm theo ID (dùng cho chỉnh sửa sau này)
    public function getById($id) {
        $id = intval($id);
        $query = "SELECT sp.*, th.tenThuongHieu 
                    FROM tbl_sanpham sp 
                    LEFT JOIN tbl_thuonghieu th ON sp.id_thuonghieu = th.id_thuonghieu 
                    WHERE sp.maSanPham = ? AND sp.trangthai = 1";
        $stmt = $this->db->link->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Hàm lấy danh sách ảnh phụ theo ID sản phẩm
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
}
?>