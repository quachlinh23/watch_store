<?php
    $filePath = realpath(dirname(__FILE__));
    include_once __DIR__ . '/../lib/database.php';
    include_once __DIR__ . '/../helpers/format.php';

    class Employee {
        private $db;
        private $fm;

        public function __construct(){
            $this->db = new Database();
            $this->fm = new Format();
        }

        public function getAllEmployees() {
            $query = "SELECT nv.tenNhanVien, nv.soDT, nv.email, tk.username, tk.quyen, tk.trangthai, tk.id
                      FROM tbl_nhanvien nv
                      JOIN tbl_taikhoannhanvien tk ON nv.id_nhanvien = tk.id
                      WHERE tk.username NOT LIKE 'admin'";
            $result = $this->db->select($query);
            return $result;
        }

        public function updateRoll($id, $scroll){
            $id = intval($id);
            $scroll = intval($scroll);
            $query = "UPDATE tbl_taikhoannhanvien SET quyen = $scroll WHERE id = $id";
            $result = $this->db->update($query);
            return $result;
        }


        function getLastEmployeeAcount() {
            $sql = "SELECT username FROM tbl_taikhoannhanvien WHERE username LIKE 'nv____' ORDER BY username DESC LIMIT 1";
            $result = $this->db->select($sql);
        
            if ($result && $row = $result->fetch_assoc()) {
                return $row['username']; // Trả về tài khoản lớn nhất
            }
            return null; // Nếu chưa có tài khoản nào
        }
        
        function createAcount() {
            $lastAccount = $this->getLastEmployeeAcount();
            
            if ($lastAccount) {
                // Lấy số thứ tự từ 'nvxxxx' (bỏ 'nv')
                $number = (int)substr($lastAccount, 2);
                $newNumber = $number + 1;
            } else {
                $newNumber = 1; // Nếu chưa có tài khoản nào
            }
        
            // Tạo tài khoản mới dạng nv0001, nv0002,...
            return "nv" . str_pad($newNumber, 4, "0", STR_PAD_LEFT);
        }

        public function addEmployee($name, $email, $username) {
            // Mã hóa mật khẩu bằng MD5 (KHÔNG KHUYẾN KHÍCH)
            $password = md5(123456);
            // Thêm tài khoản vào bảng `tbl_taikhoan`
            $query1 = "INSERT INTO tbl_taikhoannhanvien (username, password, quyen, trangthai) VALUES ('$username', '$password',0,1)";
            $result1 = $this->db->insert($query1);
        
            if ($result1) {
                $id_taikhoan = mysqli_insert_id($this->db->link);
        
                if ($id_taikhoan) {
                    // Thêm nhân viên vào bảng `tbl_nhanvien`
                    $query2 = "INSERT INTO tbl_nhanvien (id_taikhoan, tenNhanVien, email) VALUES ('$id_taikhoan', '$name', '$email')";
                    $result2 = $this->db->insert($query2);
                    if ($result2) {
                        return true;
                    }
                }
            }
            return false; // Trả về false nếu có lỗi
        }

        public function updateEmployee($idEmployee, $fullname, $phone, $email) {
            $idEmployee = intval($idEmployee);
            if ($idEmployee <= 0 || empty($fullname) || empty($phone) || empty($email)) {
                return "Dữ liệu không hợp lệ!";
            }
        
            // Kiểm tra trùng số điện thoại hoặc email (loại trừ nhân viên hiện tại)
            $queryCheck = "SELECT COUNT(*) AS count FROM tbl_nhanvien WHERE (soDT = ? OR email = ?) AND id_nhanvien != ?";
            $stmtCheck = $this->db->link->prepare($queryCheck);
            $stmtCheck->bind_param("ssi", $phone, $email, $idEmployee);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            $row = $resultCheck->fetch_assoc();
            
            if ($row['count'] > 0) {
                return "Số điện thoại hoặc Email đã tồn tại!";
            }
        
            // Nếu không trùng, tiến hành cập nhật
            $queryUpdate = "UPDATE tbl_nhanvien SET tenNhanVien = ?, soDT = ?, email = ? WHERE id_nhanvien = ?";
            $stmtUpdate = $this->db->link->prepare($queryUpdate);
            $stmtUpdate->bind_param("sssi", $fullname, $phone, $email, $idEmployee);
            $updateSuccess = $stmtUpdate->execute();
            
            return $updateSuccess;
        }
        
        public function updatePassword($idEmployee, $newPass) {
            $idEmployee = intval($idEmployee);
            $newPass = mysqli_real_escape_string($this->db->link, $newPass);
    
            $queryUpdate = "UPDATE tbl_taikhoannhanvien
                            SET password = '$newPass' 
                            WHERE id = $idEmployee";
            $result = $this->db->update($queryUpdate);
    
            return ($result) ? true : false;
        }
        
        
        public function updateStatus($id, $status) {
            $id = intval($id);
            $newStatus = ($status == 1) ? 0 : 1; // Đảo trạng thái
        
            $query = "UPDATE tbl_taikhoannhanvien SET trangthai = $newStatus WHERE id = $id";
        
            $result = $this->db->update($query); // Gọi phương thức update
        
            return ($result) ? true : false; // Trả về kết quả cập nhật
        }

        public function getEmployeeById($id){
            $query = "SELECT nv.tenNhanVien, nv.soDT, nv.email, tk.username, tk.password
                    FROM tbl_nhanvien nv
                    JOIN tbl_taikhoannhanvien tk ON nv.id_nhanvien = tk.id
                    WHERE nv.id_nhanvien = $id";
            $result = $this->db->select($query);
            return $result;
        }

        function search($data){
            $query = "SELECT * FROM tbl_nhanvien nv
            JOIN tbl_taikhoannhanvien tk ON nv.id_nhanvien = tk.id
            WHERE (tenNhanVien LIKE '%$data%'
            OR soDT LIKE '%$data%' OR email LIKE '%$data%')AND tk.username NOT LIKE 'admin'";
            $employeelist = $this->db->select($query);
            return $employeelist;
        }
    }
?>