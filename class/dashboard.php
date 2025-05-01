<?php
include_once(__DIR__ . '/../lib/database.php');
include_once(__DIR__ . '/../helpers/format.php');

class dashboard {
    private $db;
    private $fm;

    public function __construct() {
        $this->db = new Database();
        $this->fm = new Format();
    }

    // Hàm lấy tổng số khách hàng
    public function getTotalCustomers() {
        $query = "SELECT COUNT(*) as total FROM tbl_khachhang";
        $result = $this->db->select($query);
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['total'];
        }
        return 0;
    }

    // Hàm lấy tổng số nhân viên
    public function getTotalEmployees() {
        $query = "SELECT COUNT(*) as total FROM tbl_nhanvien";
        $result = $this->db->select($query);
        
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['total'];
        }
        return 0;
    }

    // Hàm lấy số đơn hàng trong ngày hiện tại
    public function getDailyOrders() {
        $today = date('Y-m-d');
        $query = "SELECT COUNT(*) as total FROM tbl_phieuxuat WHERE DATE(ngayLap) = '$today'";
        $result = $this->db->select($query);
        if ($result) {
            $row = $result->fetch_assoc();
            return (int)$row['total'];
        }
        
        return 0;
    }

    // Hàm lấy số đơn hàng theo tháng trong một năm
    public function getMonthlyOrders($year) {
        $query = "SELECT MONTH(ngayLap) as month, COUNT(*) as total
                FROM tbl_phieuxuat
                WHERE YEAR(ngayLap) = '$year' AND trangThai = 3
                GROUP BY MONTH(ngayLap)
                ORDER BY MONTH(ngayLap)";
        
        $result = $this->db->select($query);
        
        $monthlyOrders = array_fill(0, 12, 0);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $monthlyOrders[(int)$row['month'] - 1] = (int)$row['total'];
            }
        }
        
        return $monthlyOrders;
    }

    //Hàm lấy số đơn hàng chưa được duyệt
    public function getOrdersnotapproved() {
        $query = "SELECT COUNT(*) as total FROM tbl_phieuxuat WHERE trangThai = 0 OR trangThai = 4";
        $result = $this->db->select($query);
        if ($result) {
            $row = $result->fetch_assoc();
            return (int)$row['total'];
        }
        return 0;
    }
    
    // Hàm lấy tổng số phiếu xuất
    public function getTotalExportBills() {
        $query = "SELECT COUNT(*) as total FROM tbl_phieuxuat WHERE trangThai = 3";
        $result = $this->db->select($query);
        
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['total'];
        }
        return 0;
    }

    // Hàm lấy doanh thu từ tbl_phieuxuat theo tháng và năm
    public function getRevenueStats($type = 'month') {
        if ($type === 'year') {
            $query = "
                SELECT YEAR(ngayhoanThanh) AS period, SUM(tongTien) AS revenue
                FROM tbl_phieuxuat
                WHERE trangThai = 3
                GROUP BY YEAR(ngayhoanThanh)
                ORDER BY period
            ";
        } else { // Mặc định là theo tháng
            $query = "
                SELECT DATE_FORMAT(ngayhoanThanh, '%Y-%m') AS period, SUM(tongTien) AS revenue
                FROM tbl_phieuxuat
                WHERE trangThai = 3
                GROUP BY DATE_FORMAT(ngayhoanThanh, '%Y-%m')
                ORDER BY period
            ";
        }
    
        $result = $this->db->select($query);
        $data = ['labels' => [], 'values' => []];
    
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data['labels'][] = $row['period'];
                $data['values'][] = (float)$row['revenue'];
            }
        }
    
        return $data;
    }


    // Hàm lấy lợi nhuận theo tháng và năm
    public function getProfitStats($type = 'month') {
        // Kiểm tra type hợp lệ
        $allowed_types = ['month', 'year'];
        if (!in_array($type, $allowed_types)) {
            $type = 'month';
        }
    
        // Xây dựng query để tính lợi nhuận
        $query = $type === 'year' ?
            "SELECT 
                YEAR(px.ngayhoanThanh) as period,
                SUM(px.tongTien) as revenue,
                SUM(ctpx.soLuongXuat * COALESCE((
                    SELECT AVG(ctpn.giaNhap)
                    FROM tbl_chitietphieunhap ctpn
                    WHERE ctpn.mactSP = ctpx.mactSP
                ), 0)) as cost,
                SUM(px.tongTien - (ctpx.soLuongXuat * COALESCE((
                    SELECT AVG(ctpn.giaNhap)
                    FROM tbl_chitietphieunhap ctpn
                    WHERE ctpn.mactSP = ctpx.mactSP
                ), 0))) as profit
            FROM tbl_phieuxuat px
            INNER JOIN tbl_chitietphieuxuat ctpx ON px.maphieuxuat = ctpx.maPX
            WHERE px.trangThai = 3 AND px.ngayhoanThanh IS NOT NULL
            GROUP BY YEAR(px.ngayhoanThanh)
            ORDER BY period" :
            "SELECT 
                DATE_FORMAT(px.ngayhoanThanh, '%Y-%m') as period,
                SUM(px.tongTien) as revenue,
                SUM(ctpx.soLuongXuat * COALESCE((
                    SELECT AVG(ctpn.giaNhap)
                    FROM tbl_chitietphieunhap ctpn
                    WHERE ctpn.mactSP = ctpx.mactSP
                ), 0)) as cost,
                SUM(px.tongTien - (ctpx.soLuongXuat * COALESCE((
                    SELECT AVG(ctpn.giaNhap)
                    FROM tbl_chitietphieunhap ctpn
                    WHERE ctpn.mactSP = ctpx.mactSP
                ), 0))) as profit
            FROM tbl_phieuxuat px
            INNER JOIN tbl_chitietphieuxuat ctpx ON px.maphieuxuat = ctpx.maPX
            WHERE px.trangThai = 3 AND px.ngayhoanThanh IS NOT NULL
            GROUP BY DATE_FORMAT(px.ngayhoanThanh, '%Y-%m')
            ORDER BY period";
    
        // Thực thi truy vấn
        $result = $this->db->select($query);
    
        // Khởi tạo mảng để lưu kết quả
        $data = ['labels' => [], 'values' => []];
    
        // Xử lý lỗi truy vấn
        if ($result === false) {
            return $data;
        }
    
        // Xử lý kết quả
        while ($row = $result->fetch_assoc()) {
            $data['labels'][] = $row['period'];
            $data['values'][] = (float)($row['profit'] ?? 0);
        }
    
        // Đảm bảo dữ liệu không rỗng
        if (empty($data['labels'])) {
            $current_year = date('Y');
            if ($type === 'year') {
                for ($i = 5; $i >= 0; $i--) {
                    $data['labels'][] = (string)($current_year - $i);
                    $data['values'][] = 0.0;
                }
            } else {
                for ($i = 1; $i <= 12; $i++) {
                    $data['labels'][] = sprintf("%d-%02d", $current_year, $i);
                    $data['values'][] = 0.0;
                }
            }
        }
    
        return $data;
    }

    public function getTopSellingProduct() {
        $query = "
            WITH Sold AS (
                SELECT 
                    ctpx.mactSP,
                    SUM(ctpx.soLuongXuat) AS totalSold
                FROM tbl_chitietphieuxuat ctpx
                JOIN tbl_phieuxuat px ON ctpx.maPX = px.maphieuxuat
                WHERE px.trangThai = 3
                GROUP BY ctpx.mactSP
            ),
            MaxSold AS (
                SELECT MAX(totalSold) AS maxSold FROM Sold
            )
            SELECT 
                sp.maSanPham,
                sp.tenSanPham,
                sp.hinhAnh,
                s.totalSold
            FROM Sold s
            JOIN MaxSold m ON s.totalSold = m.maxSold
            JOIN tbl_chitietsanpham ctsp ON s.mactSP = ctsp.mact
            JOIN tbl_sanpham sp ON ctsp.masanpham = sp.maSanPham
            ORDER BY sp.tenSanPham
        ";
    
        $result = $this->db->select($query);
        $data = ['products' => []];
    
        if ($result === false) {
            $data['products'][] = [
                'masanpham' => '',
                'tenSanPham' => 'Chưa có dữ liệu',
                'hinhAnh' => 'https://via.placeholder.com/150?text=No+Image',
                'totalSold' => 0
            ];
            return $data;
        }
    
        while ($row = $result->fetch_assoc()) {
            $data['products'][] = [
                'masanpham' => $row['maSanPham'],
                'tenSanPham' => $row['tenSanPham'],
                'hinhAnh' => $row['hinhAnh'] ?: 'https://via.placeholder.com/150?text=No+Image',
                'totalSold' => (int)$row['totalSold']
            ];
        }
    
        if (empty($data['products'])) {
            $data['products'][] = [
                'masanpham' => '',
                'tenSanPham' => 'Chưa có dữ liệu',
                'hinhAnh' => 'https://via.placeholder.com/150?text=No+Image',
                'totalSold' => 0
            ];
        }
    
        return $data;
    }
}
?>