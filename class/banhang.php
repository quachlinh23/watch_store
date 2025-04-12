<?php
include_once(__DIR__ . '/../lib/database.php');
include_once(__DIR__ . '/../helpers/format.php');

class banhang {
    private $db;
    private $fm;

    public function __construct() {
        $this->db = new Database();
        $this->fm = new Format();
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
}
?>
