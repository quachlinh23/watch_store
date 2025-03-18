<?php
    $filePath = realpath(dirname(__FILE__));
    include_once __DIR__ . '/../lib/session.php';
    Session::checkLogin();
    include_once __DIR__ . '/../lib/database.php';
    include_once __DIR__ . '/../helpers/format.php';
    
    class adminlogin {
        private $db;
        private $fm;

        public function __construct(){
            $this->db = new Database();
            $this->fm = new Format();
        }

        public function login_employee($username,$password){
            $username = $this->fm->validation($username);
            $password = $this->fm->validation($password);


            $admin_user = mysqli_real_escape_string($this->db->link,$username);
            $pass_user = mysqli_real_escape_string($this->db->link,$password);

            if (empty($admin_user) || empty($pass_user)){
                $alert = "Vui lòng nhập tên đăng nhập hoặc mật khẩu";
                return $alert;
            } else{
                $query = "SELECT tk.*, nv.*
                    FROM tbl_taikhoannhanvien tk
                    INNER JOIN tbl_nhanvien nv ON tk.id = nv.id_taikhoan
                    WHERE tk.username = '$admin_user' AND tk.password = '$pass_user' AND tk.trangthai = 1
                    LIMIT 1";

                $result = $this->db->select($query);

                if ($result != false){
                    $value = $result->fetch_assoc();
                    Session::set('login',true);
                    Session::set('idAcount',$value['id']);
                    Session::set('fullname',$value['tenNhanVien']);
                    Session::set('idEmployee',$value['id_nhanvien']);
                    Session::set('password',$value['password']);
                    Session::set('roll',$value['quyen']);
                    header('Location:index.php');
                }else{
                    $alert = "Tên đăng nhập hoặc Mật khẩu không đúng";
                    return $alert;
                }
            }
        }
    }
?>