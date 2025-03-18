<?php
    $filePath = realpath(dirname(__FILE__));
    include_once __DIR__ . '/../lib/session.php';
    Session::checkLogin();
    include_once __DIR__ . '/../lib/database.php';
    include_once __DIR__ . '/../helpers/format.php';

    class customerlogin {
        private $db;
        private $fm;

        public function __construct(){
            $this->db = new Database();
            $this->fm = new Format();
        }

        public function login($data) {
            $username = mysqli_real_escape_string($this->db->link, $data['username']);
            $password = mysqli_real_escape_string($this->db->link, md5($data['password']));
        
            // Truy vấn kiểm tra username
            $query = "SELECT * FROM tbl_taikhoan WHERE username = '$username' AND trangThai = 1 LIMIT 1"; 
            $result = mysqli_query($this->db->link, $query);
        
            // Kiểm tra lỗi SQL
            if (!$result) {
                return "Lỗi truy vấn SQL: " . mysqli_error($this->db->link);
            }
        
            // Kiểm tra nếu tài khoản không tồn tại
            if (mysqli_num_rows($result) == 0) {
                return "Tên đăng nhập hoặc mật khẩu không đúng!";
            }
        
            // Lấy thông tin tài khoản
            $user = mysqli_fetch_assoc($result);
        
            // Kiểm tra mật khẩu
            if ($user['password'] !== $password) {
                return "Tên đăng nhập hoặc mật khẩu không đúng!";
            }
        
            // Lưu vào session khi đăng nhập thành công
            Session::set('customer_login', true);
            Session::set('customer_id', $user['id']);
            Session::set('customer_name', $user['username']);
        
            return true; // Đăng nhập thành công
        }
        
        public function register($data) {
            $hoten = mysqli_real_escape_string($this->db->link, $data['hoten']);
            $username = mysqli_real_escape_string($this->db->link, $data['username']);
            $password = mysqli_real_escape_string($this->db->link, md5($data['password']));
        
            // Kiểm tra username đã tồn tại chưa
            $checkQuery = "SELECT * FROM tbl_taikhoan WHERE username = '$username'";
            $checkResult = mysqli_query($this->db->link, $checkQuery);
            
            if (mysqli_num_rows($checkResult) > 0) {
                return "Tên Đăng Nhập Đã Tồn Tại";
            }
        
            // Thêm tài khoản vào bảng tbl_taikhoan
            $query = "INSERT INTO tbl_taikhoan (username, password) 
                      VALUES ('$username', '$password')";
            $result = mysqli_query($this->db->link, $query);
        
            if (!$result) {
                return "Lỗi khi tạo tài khoản: " . mysqli_error($this->db->link);
            }
        
            // Lấy ID của tài khoản vừa tạo
            $account_id = mysqli_insert_id($this->db->link);
        
            // Thêm vào bảng tbl_khachhang
            $query_tblkhachhang = "INSERT INTO tbl_khachhang (id_taikhoan, tenKhachHang, diaChi, soDT, email) 
                                   VALUES ('$account_id', '$hoten', '', '', '')";
            $result_tblkhachhang = mysqli_query($this->db->link, $query_tblkhachhang);
        
            if (!$result_tblkhachhang) {
                return "Lỗi khi thêm khách hàng: " . mysqli_error($this->db->link);
            }
        
            // Thêm ảnh đại diện mặc định
            $hinhanh = 'admin/uploads/avt_user/avatar.png';
            $query_tblanhdaidien = "INSERT INTO tbl_anhdaidien (id_taikhoan, hinhAnh) 
                                    VALUES ('$account_id', '$hinhanh')";
            $result_tblanhdaidien = mysqli_query($this->db->link, $query_tblanhdaidien);
        
            if (!$result_tblanhdaidien) {
                return "Lỗi khi thêm ảnh đại diện: " . mysqli_error($this->db->link);
            }
        
            // Thêm giỏ hàng cho khách hàng mới
            $query_cart = "INSERT INTO tbl_giohang (maTaiKhoan) VALUES ('$account_id')";
            $result_cart = mysqli_query($this->db->link, $query_cart);
        
            if (!$result_cart) {
                return "Lỗi khi thêm giỏ hàng: " . mysqli_error($this->db->link);
            }
        
            return true;
        }
        
        public function getinforcustomerbyid($id) {
            $query = "SELECT * FROM tbl_khachhang WHERE id_taikhoan = $id";
            $result = $this->db->select($query);
            return $result;
        }
    }
?>