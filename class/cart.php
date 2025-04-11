<?php
include_once(__DIR__ . '/../lib/database.php');
include_once(__DIR__ . '/../helpers/format.php');

class cart {
    private $db;
    private $fm;

    public function __construct() {
        $this->db = new Database();
        $this->fm = new Format();
    }

    public function getcartidbycustomer($idKhachHang) {
        $idKhachHang = mysqli_real_escape_string($this->db->link, $idKhachHang);
        $query = "SELECT magiohang FROM tbl_giohang WHERE maTaiKhoan = '$idKhachHang' LIMIT 1";
        $result = $this->db->select($query);
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['magiohang'];
        }
        return 0;
    }

    private function update_cart_total($id_giohang) {
        $stmt = $this->db->link->prepare("SELECT SUM(thanhTien) as total FROM tbl_chitietgiohang WHERE maGioHang = ?");
        $stmt->bind_param("i", $id_giohang);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total = $row['total'] ?? 0;

        $update_stmt = $this->db->link->prepare("UPDATE tbl_giohang SET tongTien = ? WHERE maGioHang = ?");
        $update_stmt->bind_param("di", $total, $id_giohang);
        $update_stmt->execute();
        return $total;
    }

    public function addtocart($id_giohang, $id_sanpham, $soLuong) {
        $quantity = (int)$this->fm->validation($soLuong);
        $id_giohang = (int)$this->fm->validation($id_giohang);
        $id_sanpham = (int)$this->fm->validation($id_sanpham);
    
        if ($quantity <= 0 || empty($id_giohang) || empty($id_sanpham)) {
            return ['status' => 'error', 'message' => 'Dữ liệu không hợp lệ!'];
        }

        $stmt = $this->db->link->prepare("SELECT soluongTon, giaban FROM tbl_chitietsanpham WHERE masanpham = ?");
        $stmt->bind_param("i", $id_sanpham);
        $stmt->execute();
        $stock_result = $stmt->get_result();
    
        if ($stock_result && $stock_result->num_rows > 0) {
            $stock_row = $stock_result->fetch_assoc();
            $stock_quantity = (int)$stock_row['soluongTon'];
            $price = (float)$stock_row['giaban'];
    
            $check_stmt = $this->db->link->prepare("SELECT soLuong FROM tbl_chitietgiohang WHERE maGioHang = ? AND maSanPham = ?");
            $check_stmt->bind_param("ii", $id_giohang, $id_sanpham);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
    
            $current_quantity = 0;
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $current_quantity = (int)$row['soLuong'];
            }

            $total_quantity = $current_quantity + $quantity;
            if ($total_quantity > $stock_quantity) {
                return ['status' => 'error', 'message' => "Số lượng vượt quá tồn kho! Chỉ còn $stock_quantity sản phẩm trong kho."];
            }
    
            if ($current_quantity > 0) {
                $new_quantity = $total_quantity;
                $thanhTien = $new_quantity * $price;
    
                $update_stmt = $this->db->link->prepare("UPDATE tbl_chitietgiohang SET soLuong = ?, thanhTien = ? WHERE maGioHang = ? AND maSanPham = ?");
                $update_stmt->bind_param("idii", $new_quantity, $thanhTien, $id_giohang, $id_sanpham);
                $update_result = $update_stmt->execute();
    
                if ($update_result) {
                    $this->update_cart_total($id_giohang);
                    return ['status' => 'success', 'message' => 'Sản phẩm đã được cập nhật trong giỏ hàng!'];
                }
                return ['status' => 'error', 'message' => 'Có lỗi xảy ra khi cập nhật giỏ hàng!'];
            } else {
                $thanhTien = $quantity * $price;
                $insert_stmt = $this->db->link->prepare("INSERT INTO tbl_chitietgiohang (maGioHang, maSanPham, soLuong, thanhTien) VALUES (?, ?, ?, ?)");
                $insert_stmt->bind_param("iiid", $id_giohang, $id_sanpham, $quantity, $thanhTien);
                $insert_result = $insert_stmt->execute();
    
                if ($insert_result) {
                    $this->update_cart_total($id_giohang);
                    return ['status' => 'success', 'message' => 'Sản phẩm đã được thêm vào giỏ hàng!'];
                }
                return ['status' => 'error', 'message' => 'Có lỗi xảy ra khi thêm vào giỏ hàng!'];
            }
        }
        return ['status' => 'error', 'message' => 'Sản phẩm không tồn tại trong kho!'];
    }

    public function showCart($id) {
        $id_cart = intval($id);
        $query = "SELECT
                    sp.maSanPham,
                    sp.tenSanPham,
                    sp.mota,
                    sp.hinhAnh,
                    sp.trangthai,
                    ctsp.giaban,
                    ctsp.soluongTon,
                    ctgh.soLuong,
                    ctgh.thanhTien,
                    gh.tongTien
                FROM tbl_giohang gh
                JOIN tbl_chitietgiohang ctgh ON gh.magiohang = ctgh.maGioHang
                JOIN tbl_sanpham sp ON ctgh.maSanPham = sp.maSanPham
                JOIN tbl_chitietsanpham ctsp ON sp.maSanPham = ctsp.masanpham
                WHERE gh.maTaiKhoan = $id_cart";

        $result = $this->db->select($query);
        if ($result === false || $result->num_rows == 0) {
            return [];
        }
        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    public function increment_quantity($id_giohang, $id_sanpham) {
        $stmt = $this->db->link->prepare("SELECT soLuong, (SELECT soluongTon FROM tbl_chitietsanpham WHERE masanpham = ?) as soluongTon, (SELECT giaban FROM tbl_chitietsanpham WHERE masanpham = ?) as giaban FROM tbl_chitietgiohang WHERE maGioHang = ? AND maSanPham = ?");
        $stmt->bind_param("iiii", $id_sanpham, $id_sanpham, $id_giohang, $id_sanpham);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $current_quantity = (int)$row['soLuong'];
            $soluongTon = (int)$row['soluongTon'];
            $giaban = (float)$row['giaban'];

            if ($current_quantity < $soluongTon) {
                $new_quantity = $current_quantity + 1;
                $thanhTien = $new_quantity * $giaban;
                $update_stmt = $this->db->link->prepare("UPDATE tbl_chitietgiohang SET soLuong = ?, thanhTien = ? WHERE maGioHang = ? AND maSanPham = ?");
                $update_stmt->bind_param("idii", $new_quantity, $thanhTien, $id_giohang, $id_sanpham);
                if ($update_stmt->execute()) {
                    $tongTien = $this->update_cart_total($id_giohang);
                    return [
                        "success" => true,
                        "message" => "Tăng số lượng thành công",
                        "soLuong" => $new_quantity,
                        "thanhTien" => $thanhTien,
                        "tongTien" => $tongTien,
                        "soluongTon" => $soluongTon
                    ];
                }
            } else {
                return ["success" => false, "message" => "Số lượng vượt quá tồn kho! Chỉ còn $soluongTon sản phẩm."];
            }
        }
        return ["success" => false, "message" => "Tăng số lượng thất bại"];
    }

    public function decrement_quantity($id_giohang, $id_sanpham) {
        $stmt = $this->db->link->prepare("SELECT soLuong, (SELECT soluongTon FROM tbl_chitietsanpham WHERE masanpham = ?) as soluongTon, (SELECT giaban FROM tbl_chitietsanpham WHERE masanpham = ?) as giaban FROM tbl_chitietgiohang WHERE maGioHang = ? AND maSanPham = ?");
        $stmt->bind_param("iiii", $id_sanpham, $id_sanpham, $id_giohang, $id_sanpham);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $current_quantity = (int)$row['soLuong'];
            $soluongTon = (int)$row['soluongTon'];
            $giaban = (float)$row['giaban'];

            if ($current_quantity > 1) {
                $new_quantity = $current_quantity - 1;
                $thanhTien = $new_quantity * $giaban;
                $update_stmt = $this->db->link->prepare("UPDATE tbl_chitietgiohang SET soLuong = ?, thanhTien = ? WHERE maGioHang = ? AND maSanPham = ?");
                $update_stmt->bind_param("idii", $new_quantity, $thanhTien, $id_giohang, $id_sanpham);
                if ($update_stmt->execute()) {
                    $tongTien = $this->update_cart_total($id_giohang);
                    return [
                        "success" => true,
                        "message" => "Giảm số lượng thành công",
                        "soLuong" => $new_quantity,
                        "thanhTien" => $thanhTien,
                        "tongTien" => $tongTien,
                        "soluongTon" => $soluongTon
                    ];
                }
            } else {
                return ["success" => false, "message" => "Số lượng tối thiểu là 1"];
            }
        }
        return ["success" => false, "message" => "Giảm số lượng thất bại"];
    }

    public function update_quantity($id_giohang, $id_sanpham, $soluong) {
        $soluong = (int)$soluong;
        $stmt = $this->db->link->prepare("SELECT soluongTon, giaban FROM tbl_chitietsanpham WHERE masanpham = ?");
        $stmt->bind_param("i", $id_sanpham);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $soluongTon = (int)$row['soluongTon'];
            $giaban = (float)$row['giaban'];

            if ($soluong >= 1 && $soluong <= $soluongTon) {
                $thanhTien = $soluong * $giaban;
                $update_stmt = $this->db->link->prepare("UPDATE tbl_chitietgiohang SET soLuong = ?, thanhTien = ? WHERE maGioHang = ? AND maSanPham = ?");
                $update_stmt->bind_param("idii", $soluong, $thanhTien, $id_giohang, $id_sanpham);
                if ($update_stmt->execute()) {
                    $tongTien = $this->update_cart_total($id_giohang);
                    return [
                        "success" => true,
                        "message" => "Cập nhật số lượng thành công",
                        "soLuong" => $soluong,
                        "thanhTien" => $thanhTien,
                        "tongTien" => $tongTien,
                        "soluongTon" => $soluongTon
                    ];
                }
            } else {
                return ["success" => false, "message" => $soluong < 1 ? "Số lượng tối thiểu là 1" : "Số lượng vượt quá tồn kho! Chỉ còn $soluongTon sản phẩm."];
            }
        }
        return ["success" => false, "message" => "Cập nhật số lượng thất bại"];
    }

    public function remove_from_cart($id_giohang, $id_sanpham) {
        $delete_stmt = $this->db->link->prepare("DELETE FROM tbl_chitietgiohang WHERE maGioHang = ? AND maSanPham = ?");
        $delete_stmt->bind_param("ii", $id_giohang, $id_sanpham);
        if ($delete_stmt->execute()) {
            $tongTien = $this->update_cart_total($id_giohang);
            return [
                "success" => true,
                "message" => "Xóa sản phẩm thành công",
                "tongTien" => $tongTien
            ];
        }
        return ["success" => false, "message" => "Xóa sản phẩm thất bại"];
    }

    public function countProductCartByUser($id_giohang) {
        $id_giohang = intval($id_giohang);
    
        $query = "SELECT SUM(soLuong) AS tongSoLuong FROM tbl_chitietgiohang WHERE maGioHang = $id_giohang";
        
        $result = $this->db->select($query);
    
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row['tongSoLuong'] ?? 0; // Nếu null thì trả về 0
        }
    
        return 0; // Nếu truy vấn lỗi
    }
    
}
?>