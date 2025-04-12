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

    public function process_checkout($maTaiKhoan, $items, $shipping_info) {
        if (empty($maTaiKhoan) || empty($items) || empty($shipping_info)) {
            return ['success' => false, 'message' => 'Thông tin không đầy đủ'];
        }
    
        $this->db->link->begin_transaction();
    
        try {
            // Tính tổng tiền
            $total = 0;
            foreach ($items as $item) {
                $id_sanpham = (int)$item['idProduct'];
                $quantity = (int)$item['quantity'];
                $price = (float)$item['price'];
    
                // Kiểm tra tồn kho
                $stmt = $this->db->link->prepare("SELECT mact, soluongTon FROM tbl_chitietsanpham WHERE masanpham = ?");
                $stmt->bind_param("i", $id_sanpham);
                $stmt->execute();
                $result = $stmt->get_result();
                $stock = $result->fetch_assoc();
    
                if (!$stock || $stock['soluongTon'] < $quantity) {
                    throw new Exception("Sản phẩm ID $id_sanpham không đủ số lượng tồn kho.");
                }
    
                $total += $price * $quantity;
            }
    
            // Thêm phiếu xuất vào tbl_phieuxuat
            $stmt = $this->db->link->prepare(
                "INSERT INTO tbl_phieuxuat (maTaiKhoan, ngayLap, tongTien, trangThai, nguoiDuyet) 
                VALUES (?, NOW(), ?, 0, NULL)"
            );
            $stmt->bind_param("id", $maTaiKhoan, $total);
            $stmt->execute();
            $phieuxuat_id = $this->db->link->insert_id;
    
            // Lấy maGioHang từ maTaiKhoan để cập nhật giỏ hàng
            $id_giohang = $this->getcartidbycustomer($maTaiKhoan);
    
            // Thêm chi tiết phiếu xuất và cập nhật các bảng liên quan
            $stmt_phieuxuat = $this->db->link->prepare(
                "INSERT INTO tbl_chitietphieuxuat (maPX, mactSP, soLuongXuat) 
                VALUES (?, ?, ?)"
            );
    
            foreach ($items as $item) {
                $id_sanpham = (int)$item['idProduct'];
                $quantity = (int)$item['quantity'];
                $price = (float)$item['price'];
    
                // Lấy mact từ tbl_chitietsanpham
                $stmt = $this->db->link->prepare("SELECT mact FROM tbl_chitietsanpham WHERE masanpham = ?");
                $stmt->bind_param("i", $id_sanpham);
                $stmt->execute();
                $result = $stmt->get_result();
                $mact = $result->fetch_assoc()['mact'];
    
                // Thêm chi tiết phiếu xuất
                $stmt_phieuxuat->bind_param("iii", $phieuxuat_id, $mact, $quantity);
                $stmt_phieuxuat->execute();
    
                // Cập nhật số lượng tồn kho trong tbl_chitietsanpham
                $update_stmt = $this->db->link->prepare(
                    "UPDATE tbl_chitietsanpham SET soluongTon = soluongTon - ? WHERE masanpham = ?"
                );
                $update_stmt->bind_param("ii", $quantity, $id_sanpham);
                $update_stmt->execute();
    
                // Cập nhật giỏ hàng (trừ số lượng thay vì xóa hoàn toàn)
                $this->removecart($id_giohang, $id_sanpham, $quantity);
            }
    
            $this->db->link->commit();
            return ['success' => true, 'message' => 'Phiếu xuất đã được tạo thành công', 'phieuxuat_id' => $phieuxuat_id];
        } catch (Exception $e) {
            $this->db->link->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function removecart($id_giohang, $id_sanpham, $quantity_to_remove) {
        // Lấy số lượng hiện tại trong giỏ hàng
        $stmt = $this->db->link->prepare(
            "SELECT soLuong, thanhTien FROM tbl_chitietgiohang WHERE maGioHang = ? AND maSanPham = ?"
        );
        $stmt->bind_param("ii", $id_giohang, $id_sanpham);
        $stmt->execute();
        $result = $stmt->get_result();
        $cart_item = $result->fetch_assoc();
    
        if (!$cart_item) {
            return ["success" => false, "message" => "Sản phẩm không tồn tại trong giỏ hàng"];
        }
    
        $current_quantity = (int)$cart_item['soLuong'];
        $new_quantity = $current_quantity - $quantity_to_remove;
    
        if ($new_quantity <= 0) {
            // Nếu số lượng còn lại <= 0, xóa bản ghi
            $delete_stmt = $this->db->link->prepare(
                "DELETE FROM tbl_chitietgiohang WHERE maGioHang = ? AND maSanPham = ?"
            );
            $delete_stmt->bind_param("ii", $id_giohang, $id_sanpham);
            $delete_stmt->execute();
        } else {
            // Nếu còn lại > 0, cập nhật số lượng và thành tiền
            $unit_price = $cart_item['thanhTien'] / $current_quantity; // Giá đơn vị
            $new_thanhTien = $unit_price * $new_quantity;
    
            $update_stmt = $this->db->link->prepare(
                "UPDATE tbl_chitietgiohang SET soLuong = ?, thanhTien = ? WHERE maGioHang = ? AND maSanPham = ?"
            );
            $update_stmt->bind_param("idii", $new_quantity, $new_thanhTien, $id_giohang, $id_sanpham);
            $update_stmt->execute();
        }
    
        // Cập nhật tổng tiền giỏ hàng
        $tongTien = $this->update_cart_total($id_giohang);
        return [
            "success" => true,
            "message" => "Cập nhật giỏ hàng thành công",
            "tongTien" => $tongTien
        ];
    }

    public function loadInvoiceOfUser($idUser) {
        $idUser = mysqli_real_escape_string($this->db->link, $idUser);
    
        // Truy vấn JOIN 6 bảng
        $query = "SELECT px.maphieuxuat, px.maTaiKhoan, px.ngayLap, px.tongTien,
        px.trangThai, ct.mactPX, ct.mactSP, ct.soLuongXuat, tk_buyer.id
        AS buyer_id, kh.id_khachhang, kh.tenKhachHang, kh.diaChi, kh.soDT,
        kh.email, sp.maSanPham, sp.tenSanPham, sp.hinhAnh, cts.giaban
        FROM tbl_phieuxuat px
        LEFT JOIN tbl_chitietphieuxuat ct
        ON px.maphieuxuat = ct.maPX
        LEFT JOIN tbl_taikhoan tk_buyer ON px.maTaiKhoan = tk_buyer.id
        LEFT JOIN tbl_khachhang kh ON px.maTaiKhoan = kh.id_taikhoan
        LEFT JOIN tbl_chitietsanpham cts ON ct.mactSP = cts.mact
        LEFT JOIN tbl_sanpham sp ON cts.masanpham = sp.maSanPham
        WHERE px.maTaiKhoan = $idUser
        ORDER BY px.ngayLap
        DESC, ct.mactPX
        ASC";
    
        $result = $this->db->select($query);
    
        if (!$result) {
            return [];
        }
    
        // Xử lý dữ liệu thành mảng phân cấp
        $invoices = [];
        $current_invoice_id = null;
        $current_invoice = null;
    
        while ($row = $result->fetch_assoc()) {
            $maphieuxuat = (int)$row['maphieuxuat'];
    
            // Nếu chuyển sang phiếu xuất mới
            if ($current_invoice_id !== $maphieuxuat) {
                if ($current_invoice !== null) {
                    $invoices[] = $current_invoice;
                }
                $current_invoice = [
                    'maphieuxuat' => $maphieuxuat,
                    'maTaiKhoan' => (int)$row['maTaiKhoan'],
                    'ngayLap' => $row['ngayLap'],
                    'tongTien' => (float)$row['tongTien'],
                    'trangThai' => (int)$row['trangThai'],
                    'buyer' => [
                        'id_taikhoan' => (int)$row['buyer_id'],
                    ],
                    'customer' => [
                        'id_khachhang' => (int)$row['id_khachhang'],
                        'tenKhachHang' => $row['tenKhachHang'],
                        'diaChi' => $row['diaChi'],
                        'soDT' => $row['soDT'],
                        'email' => $row['email']
                    ],
                    'items' => []
                ];
                $current_invoice_id = $maphieuxuat;
            }
    
            // Thêm chi tiết sản phẩm nếu có
            if ($row['mactPX'] !== null) {
                $current_invoice['items'][] = [
                    'mactPX' => (int)$row['mactPX'],
                    'mactSP' => (int)$row['mactSP'],
                    'soLuongXuat' => (int)$row['soLuongXuat'],
                    'maSanPham' => (int)$row['maSanPham'],
                    'tenSanPham' => $row['tenSanPham'],
                    'hinhAnh' => $row['hinhAnh'],
                    'giaban' => (float)$row['giaban']
                ];
            }
        }
    
        // Thêm phiếu xuất cuối cùng vào mảng
        if ($current_invoice !== null) {
            $invoices[] = $current_invoice;
        }
    
        return $invoices;
    }

    public function cancelOrder($maPX) {
        if (empty($maPX)) {
            return ['success' => false, 'message' => 'Mã phiếu xuất không hợp lệ'];
        }
    
        $this->db->link->begin_transaction();
    
        try {
            // Kiểm tra hóa đơn tồn tại và trạng thái
            $stmt = $this->db->link->prepare("SELECT trangThai FROM tbl_phieuxuat WHERE maphieuxuat = ?");
            $stmt->bind_param("i", $maPX);
            $stmt->execute();
            $result = $stmt->get_result();
            $order = $result->fetch_assoc();
    
            if (!$order) {
                throw new Exception("Hóa đơn không tồn tại.");
            }
    
            if ($order['trangThai'] == 2) {
                throw new Exception("Hóa đơn đã bị hủy trước đó.");
            }
    
            // Cập nhật trạng thái hóa đơn thành 2 (hủy)
            $stmt = $this->db->link->prepare("UPDATE tbl_phieuxuat SET trangThai = 2 WHERE maphieuxuat = ?");
            $stmt->bind_param("i", $maPX);
            $stmt->execute();
    
            // Lấy chi tiết hóa đơn để hoàn tồn kho
            $stmt = $this->db->link->prepare(
                "SELECT mactSP, soLuongXuat
                FROM tbl_chitietphieuxuat
                WHERE maPX = ?"
            );
            $stmt->bind_param("i", $maPX);
            $stmt->execute();
            $result = $stmt->get_result();
            $items = $result->fetch_all(MYSQLI_ASSOC);
    
            if (empty($items)) {
                throw new Exception("Không tìm thấy chi tiết hóa đơn.");
            }
    
            // Hoàn lại số lượng tồn kho
            $stmt = $this->db->link->prepare(
                "UPDATE tbl_chitietsanpham
                SET soluongTon = soluongTon + ?
                WHERE mact = ?"
            );
    
            foreach ($items as $item) {
                $soLuongXuat = (int)$item['soLuongXuat'];
                $mact = (int)$item['mactSP'];
    
                $stmt->bind_param("ii", $soLuongXuat, $mact);
                $stmt->execute();
    
                // Kiểm tra xem cập nhật có thành công không
                if ($stmt->affected_rows === 0) {
                    throw new Exception("Không thể cập nhật tồn kho cho mactSP: $mact.");
                }
            }
    
            $this->db->link->commit();
            return ['success' => true, 'message' => 'Hủy hóa đơn thành công', 'phieuxuat_id' => $maPX];
    
        } catch (Exception $e) {
            $this->db->link->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function markAsReceived($maPX) {
        if (empty($maPX)) {
            return ['success' => false, 'message' => 'Mã phiếu xuất không hợp lệ'];
        }
    
        $this->db->link->begin_transaction();
    
        try {
            // Kiểm tra hóa đơn
            $stmt = $this->db->link->prepare("SELECT trangThai FROM tbl_phieuxuat WHERE maphieuxuat = ?");
            $stmt->bind_param("i", $maPX);
            $stmt->execute();
            $result = $stmt->get_result();
            $order = $result->fetch_assoc();
    
            if (!$order) {
                throw new Exception("Hóa đơn không tồn tại.");
            }
    
            if ($order['trangThai'] != 1) {
                throw new Exception("Hóa đơn không ở trạng thái đã duyệt.");
            }
    
            // Cập nhật trạng thái thành Đã nhận hàng (3)
            $stmt = $this->db->link->prepare("UPDATE tbl_phieuxuat SET trangThai = 3 WHERE maphieuxuat = ?");
            $stmt->bind_param("i", $maPX);
            $stmt->execute();
    
            if ($stmt->affected_rows === 0) {
                throw new Exception("Không thể cập nhật trạng thái hóa đơn.");
            }
    
            $this->db->link->commit();
            return ['success' => true, 'message' => 'Xác nhận nhận hàng thành công'];
    
        } catch (Exception $e) {
            $this->db->link->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function requestReturn($maPX) {
        if (empty($maPX)) {
            return ['success' => false, 'message' => 'Mã phiếu xuất không hợp lệ'];
        }
    
        $this->db->link->begin_transaction();
    
        try {
            // Kiểm tra hóa đơn
            $stmt = $this->db->link->prepare("SELECT trangThai FROM tbl_phieuxuat WHERE maphieuxuat = ?");
            $stmt->bind_param("i", $maPX);
            $stmt->execute();
            $result = $stmt->get_result();
            $order = $result->fetch_assoc();
    
            if (!$order) {
                throw new Exception("Hóa đơn không tồn tại.");
            }
    
            if ($order['trangThai'] != 3) {
                throw new Exception("Hóa đơn không ở trạng thái đã nhận hàng.");
            }
    
            // Cập nhật trạng thái thành Yêu cầu trả hàng (4)
            $stmt = $this->db->link->prepare("UPDATE tbl_phieuxuat SET trangThai = 4 WHERE maphieuxuat = ?");
            $stmt->bind_param("i", $maPX);
            $stmt->execute();
    
            if ($stmt->affected_rows === 0) {
                throw new Exception("Không thể cập nhật trạng thái hóa đơn.");
            }
    
            $this->db->link->commit();
            return ['success' => true, 'message' => 'Yêu cầu trả hàng đã được gửi'];
    
        } catch (Exception $e) {
            $this->db->link->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>