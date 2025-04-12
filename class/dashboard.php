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
                WHERE YEAR(ngayLap) = '$year'
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
    
    // Hàm lấy tổng số phiếu xuất
    public function getTotalExportBills() {
        $query = "SELECT COUNT(*) as total FROM tbl_phieuxuat";
        $result = $this->db->select($query);
        
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['total'];
        }
        return 0;
    }

    // Hàm lấy doanh thu từ tbl_phieuxuat theo tháng và năm
    public function getRevenueStats($type = 'month') {
        $query = $type === 'year' ?
            "SELECT YEAR(ngayLap) as period, SUM(tongTien) as revenue 
            FROM tbl_phieuxuat 
            GROUP BY YEAR(ngayLap) 
            ORDER BY period" :
            "SELECT DATE_FORMAT(ngayLap, '%Y-%m') as period, SUM(tongTien) as revenue 
            FROM tbl_phieuxuat 
            GROUP BY DATE_FORMAT(ngayLap, '%Y-%m') 
            ORDER BY period";
        
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
        $query = $type === 'year' ?
            "SELECT YEAR(px.ngayLap) as period, 
                    SUM(px.tongTien - (ctsp.giaBan * ctpx.soLuongXuat)) as profit
             FROM tbl_phieuxuat px
             JOIN tbl_chitietphieuxuat ctpx ON px.maPhieuXuat = ctpx.maPX
             JOIN tbl_chitietsanpham ctsp ON ctpx.maCTSP = ctsp.mact
             GROUP BY YEAR(px.ngayLap)
             ORDER BY period" :
            "SELECT DATE_FORMAT(px.ngayLap, '%Y-%m') as period, 
                    SUM(px.tongTien - (ctsp.giaBan * ctpx.soLuongXuat)) as profit
             FROM tbl_phieuxuat px
             JOIN tbl_chitietphieuxuat ctpx ON px.maPhieuXuat = ctpx.maPX
             JOIN tbl_chitietsanpham ctsp ON ctpx.maCTSP = ctsp.mact
             GROUP BY DATE_FORMAT(px.ngayLap, '%Y-%m')
             ORDER BY period";
        
        $result = $this->db->select($query);
        $data = ['labels' => [], 'values' => []];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data['labels'][] = $row['period'];
                $data['values'][] = (float)$row['profit'];
            }
        }
        return $data;
    }
}
?>