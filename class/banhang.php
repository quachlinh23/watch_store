<?php
include_once(__DIR__ . '/../lib/database.php');
include_once(__DIR__ . '/../helpers/format.php');

class banhang {
    private $db;
    private $fm;

    //Khởi tạo
    public function __construct() {
        $this->db = new Database();
        $this->fm = new Format();
    }

    //Load chi tiết của một đơn hàng
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
        WHERE px.maTaiKhoan = 3
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

    //Load danh sách các đơn hàng
    public function loadAllInvoices() {
        // Truy vấn JOIN 6 bảng để lấy tất cả các phiếu xuất
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
        ORDER BY px.ngayLap DESC, ct.mactPX ASC";
    
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

    //Xác nhận đơn hàng cho khách hàng
    public function approveInvoice($idInvoice, $idUser) {
        $idInvoice = mysqli_real_escape_string($this->db->link, $idInvoice);
        $idUser = mysqli_real_escape_string($this->db->link, $idUser);
        $query = "UPDATE tbl_phieuxuat
                SET trangThai = 1, nguoiDuyet = '$idUser'
                WHERE maphieuxuat = '$idInvoice'";
        $result = $this->db->update($query);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    //Xác nhận trả hàng cho khách hàng
    public function approveReturn($maPX, $nguoiduyet) {
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
    
            if ($order['trangThai'] != 4) {
                throw new Exception("Hóa đơn không ở trạng thái yêu cầu trả hàng.");
            }
    
            // Cập nhật trạng thái hóa đơn thành 5 (đồng ý trả hàng)
            $stmt = $this->db->link->prepare(
                "UPDATE tbl_phieuxuat SET trangThai = 2, nguoiDuyet = ? WHERE maphieuxuat = ?"
            );
            $stmt->bind_param("ii", $nguoiduyet, $maPX);
            $stmt->execute();
    
            if ($stmt->affected_rows === 0) {
                throw new Exception("Không thể cập nhật trạng thái hóa đơn.");
            }
    
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
    
                if ($stmt->affected_rows === 0) {
                    throw new Exception("Không thể cập nhật tồn kho cho mactSP: $mact.");
                }
            }
    
            $this->db->link->commit();
            return ['success' => true, 'message' => 'Đồng ý trả hàng thành công', 'phieuxuat_id' => $maPX];
    
        } catch (Exception $e) {
            $this->db->link->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>
