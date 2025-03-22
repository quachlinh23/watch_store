<?php
include_once(__DIR__ . '/../lib/database.php');
include_once(__DIR__ . '/../helpers/format.php');

class nhaphang {
    private $db;
    private $fm;

    public function __construct() {
        $this->db = new Database();
        $this->fm = new Format();
    }

    public function add($mapn, $nguoinhap, $tongtien, $mancc, $ngaylap, $chitiet_sanpham) {
        $query = "INSERT INTO tbl_phieunhap (maPhieuNhap, maTaiKhoan, maNCC, ngayLap, tongTien) 
                VALUES ('$mapn', '$nguoinhap', '$mancc', '$ngaylap', '$tongtien')";
    
        $result = $this->db->insert($query);
    
        if ($result) {
            foreach ($chitiet_sanpham as $item) {
                $masp = $item['masanpham'];      // Mã sản phẩm (người dùng chọn từ giao diện)
                $gianhap = $item['gianhap']; // Giá nhập
                $soluong = $item['soluong']; // Số lượng nhập
    
                $query_find = "SELECT maCTSP FROM tbl_chitietsanpham WHERE maSanPham = '$masp' LIMIT 1";
                $result_find = $this->db->select($query_find);
    
                if ($result_find) {
                    $row = $result_find->fetch_assoc();
                    $mactsp = $row['maCTSP'];
    
                    $query_ct = "INSERT INTO tbl_chitietphieunhap (maPhieuNhap, maCTSP, giaNhap, soLuong) 
                                VALUES ('$mapn', '$mactsp', '$gianhap', '$soluong')";
    
                    $this->db->insert($query_ct);
    
                    $query_update = "UPDATE tbl_chitietsanpham 
                                    SET soLuongTon = soLuongTon + $soluong 
                                    WHERE maCTSP = '$mactsp'";
    
                    $this->db->update($query_update);
                }
            }
            return "Thêm phiếu nhập và cập nhật kho thành công!";
        } else {
            return "Lỗi khi thêm phiếu nhập!";
        }
    }

    public function getNextID() {
        $query = "SELECT MAX(maPhieuNhap) as max_id FROM tbl_phieunhap";
        $result = $this->db->select($query);
    
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['max_id'] + 1;
        }
        return 1;
    }

    public function getSupplier(){
        $query = "SELECT * FROM tbl_nhacungcap WHERE trangThai = 1";
        $result = $this->db->select($query);
        return $result;
    }

    public function getProduct() {
        $query = "SELECT sp.*
                FROM tbl_sanpham sp
                LEFT JOIN tbl_chitietsanpham ctsp ON ctsp.masanpham = sp.maSanPham
                WHERE sp.trangthai = 1
                AND (ctsp.masanpham IS NULL OR ctsp.soluongTon = 0)";
    
        $result = $this->db->select($query);
        return $result;
    }

    public function getAllImports() {
        $query = "SELECT pn.maPhieuNhap, ncc.tenNCC, pn.ngayLap, nv.tenNhanVien, pn.tongTien
                FROM tbl_phieunhap pn 
                JOIN tbl_nhacungcap ncc ON pn.maNCC = ncc.id_nhacungcap
                JOIN tbl_taikhoannhanvien tknv ON pn.maTaiKhoan = tknv.id
                JOIN tbl_nhanvien nv ON tknv.id = nv.id_taikhoan";
        $result = $this->db->select($query);
        return $result;
    }

    public function addInvoice($maPN, $nguoiNhap, $tongTien, $maNCC, $ngayLap, $chiTietSanPham) {
        $maPN = mysqli_escape_string($this->db->link, $maPN);
        $nguoiNhap = mysqli_escape_string($this->db->link, $nguoiNhap);
        $maNCC = mysqli_escape_string($this->db->link, $maNCC);
        $ngayLap = mysqli_escape_string($this->db->link, $ngayLap);
        $tongTien = mysqli_escape_string($this->db->link, $tongTien);
        $queryPN = "INSERT INTO tbl_phieuNhap (maPhieuNhap, maTaiKhoan, maNCC, ngayLap, tongTien) 
                    VALUES ('$maPN', '$nguoiNhap', '$maNCC', '$ngayLap', '$tongTien')";
        $resultPN = $this->db->insert($queryPN);
        if (!$resultPN) {
            return "Lỗi khi thêm hóa đơn nhập!";
        }
        foreach ($chiTietSanPham as $item) {
            $maSP = mysqli_escape_string($this->db->link, $item['masanpham']);
            $soLuong = mysqli_escape_string($this->db->link, $item['soluong']);
            $giaBan = mysqli_escape_string($this->db->link, $item['giaban']);
            $giaNhap = mysqli_escape_string($this->db->link, $item['gianhap']);
    
            $queryCTSP = "INSERT INTO tbl_chitietsanpham (masanpham, soluongTon, giaban) 
                        VALUES ('$maSP', '$soLuong', '$giaBan')";
            $resultCTSP = $this->db->insert($queryCTSP);
            if (!$resultCTSP) {
                return "Lỗi khi thêm chi tiết sản phẩm $maSP!";
            }
            $mactSP = $this->db->link->insert_id;
            $queryCTPN = "INSERT INTO tbl_chitietphieunhap (maPN, mactSP, giaNhap, soluongNhap) 
                        VALUES ('$maPN', '$mactSP', '$giaNhap', '$soLuong')";
            $resultCTPN = $this->db->insert($queryCTPN);
            if (!$resultCTPN) {
                return "Lỗi khi thêm chi tiết phiếu nhập!";
            }
        }
        return "Thêm phiếu nhập thành công!";
    }

    // Trong nhaphang.php
    public function getImportDetails($importId) {
        // Thoát chuỗi để tránh SQL Injection
        $importId = mysqli_escape_string($this->db->link, $importId);
        
        // Truy vấn để lấy chi tiết hóa đơn nhập
        $sql = "SELECT 
                    ctp.maPN,
                    ctsp.masanpham,
                    sp.tenSanPham,
                    ctp.soLuongNhap,
                    ctp.giaNhap,
                    ctsp.giaban
                FROM tbl_chitietphieunhap ctp
                JOIN tbl_chitietsanpham ctsp ON ctp.mactSP = ctsp.mact
                JOIN tbl_sanpham sp ON ctsp.masanpham = sp.maSanPham
                WHERE ctp.maPN = '$importId'";
        
        $result = $this->db->select($sql);
        
        if (!$result) {
            return false; // Trả về false nếu truy vấn thất bại
        }
        
        $details = [];
        while ($row = $result->fetch_assoc()) {
            $details[] = [
                'productCode' => $row['masanpham'],
                'productName' => $row['tenSanPham'],
                'quantity' => $row['soLuongNhap'],
                'importPrice' => $row['giaNhap'],
                'sellPrice' => $row['giaban']
            ];
        }
        
        return $details;
    }

    public function searchInvoice($date_start, $date_end) {
        $date_start = mysqli_escape_string($this->db->link, $date_start);
        $date_end = mysqli_escape_string($this->db->link, $date_end);
        
        $query = "SELECT pn.maPhieuNhap, pn.ngayLap, pn.tongTien, pn.maNCC, ncc.tenNCC, nv.tenNhanVien
                FROM tbl_phieunhap pn
                JOIN tbl_nhacungcap ncc ON pn.maNCC = ncc.id_nhacungcap
                JOIN tbl_taikhoannhanvien tk ON pn.maTaiKhoan = tk.id
                JOIN tbl_nhanvien nv ON tk.id = nv.id_taikhoan
                WHERE pn.ngayLap BETWEEN '$date_start' AND '$date_end'
                ORDER BY pn.ngayLap DESC";
        
        $result = $this->db->select($query); // Giả sử $this->db->select trả về mysqli_result
        return $result;
    }
}
?>
