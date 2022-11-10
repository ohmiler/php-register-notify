<?php 

    class System {

    private const DBHOST = "localhost";
    private const DBUSER = "root";
    private const DBPASS = "root";
    private const DBNAME = "system_db";

    private $dsn = 'mysql:host=' . self::DBHOST . ';dbname=' . self::DBNAME . ''; 

    public $conn = null;


    public function __construct() {   
        try {
            $this->conn = new PDO($this->dsn, self::DBUSER, self::DBPASS);
            // set PDO Error mode to exception
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // echo "Connection success";
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public function registerUser($firstname, $lastname, $email, $password, $c_password) {
        $urole = 'user';
    
            if (empty($firstname)) {
                return $_SESSION["error"] = "กรุณากรอกชื่อ!";
                header("location: register.php");
            } else if (empty($lastname)) {
                return $_SESSION["error"] = "กรุณากรอกนามสกุล!";
                header("location: register.php");
            } else if (empty($email)) {
                return $_SESSION["error"] = "กรุณากรอกอีเมล!";
                header("location: register.php");
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $_SESSION["error"] = "อีเมลไม่ถูกต้อง!";
                header("location: register.php");
            } else if (empty($password)) {
                return $_SESSION["error"] = "กรุณากรอกรหัสผ่าน!";
                header("location: register.php");
            } else if (strlen($password) > 20 || strlen($password) < 5) {
                return $_SESSION["error"] = "รหัสผ่านต้องมีความยาวระหว่าง 5 ถึง 20 ตัวอักษร!";
                header("location: register.php");
            } else if (empty($c_password)) {
                return $_SESSION["error"] = "กรุณายืนยันรหัสผ่าน!";
                header("location: register.php");
            } else if ($password != $c_password) {
                return $_SESSION["error"] = "รหัสผ่านไม่ตรงกัน!";
                header("location: register.php");
            } else {
                try {
    
                    $check_email = $this->conn->prepare("SELECT email FROM users WHERE email = :email");
                    $check_email->bindParam(":email", $email);
                    $check_email->execute();
                    $row = $check_email->fetch(PDO::FETCH_ASSOC);
    
                    if (isset($row['email']) == $email) {
                        return $_SESSION['warning'] = "มีอีเมลนี้อยู่ในระบบแล้ว <a href='login.php' class='alert-link'>คลิ๊กที่นี่</a> เพื่อเข้าสู่ระบบ";
                        header("location: register.php");
                    } else if (!isset($_SESSION['error'])) {
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $this->conn->prepare("INSERT INTO users(firstname, lastname, email, password, urole) VALUES(:firstname, :lastname, :email, :password, :urole)");
                        $stmt->bindParam(":firstname", $firstname);
                        $stmt->bindParam(":lastname", $lastname);
                        $stmt->bindParam(":email", $email);
                        $stmt->bindParam(":password", $passwordHash);
                        $stmt->bindParam(":urole", $urole);
                        $stmt->execute();
    
                        return $_SESSION['success'] = "สมัครสมาชิกเรียบร้อยแล้ว <a href='login.php' class='alert-link'>คลิ๊กที่นี่</a> เพื่อเข้าสู่ระบบ";
                        header("location: register.php");
                    } else {
                        return $_SESSION['error'] = "มีบางอย่างผิดพลาด";
                        header("location: register.php");
                    }
    
                } catch (PDOException $e) {
                    echo $e->getMessage();
                }
            }
        
    }

    public function loginUser($email, $password) {
       
        if (empty($email)) {
            return $_SESSION['error'] = 'กรุณากรอกอีเมล!';
            header("location: login.php");
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $_SESSION['error'] = 'อีเมลไม่ถูกต้อง!';
            header("location: login.php");
        } else if (empty($password)) {
            return $_SESSION['error'] = 'กรุณากรอกรหัสผ่าน!';
            header("location: login.php");
        } else if (strlen($password) > 20 || strlen($password) < 5) {
            return $_SESSION['error'] = 'รหัสผ่านต้องมีความยาวระหว่าง 5 ถึง 20 ตัวอักษร';
            header("location: login.php");
        } else {
            try {
                $check_data = $this->conn->prepare("SELECT * FROM users WHERE email = :email");
                $check_data->bindParam(":email", $email);
                $check_data->execute();
                $row = $check_data->fetch(PDO::FETCH_ASSOC);

                if ($check_data->rowCount() > 0) {
                    if ($email == $row['email']) {
                        if (password_verify($password, $row['password'])) {
                            if ($row['urole'] == 'admin') {
                                $_SESSION['admin_login'] = $row['user_id'];
                                header("location: admin/index.php");
                            } else {
                                $_SESSION['user_login'] = $row['user_id'];
                                header("location: welcome.php");
                            }
                        } else {
                            return $_SESSION['error'] = "รหัสผ่านไม่ถูกต้อง!";
                            header("location: login.php");
                        }
                    } else {
                        return $_SESSION['error'] = "อีเมลไม่ถูกต้อง!";
                        header("location: login.php");
                    }
                } else {
                    return $_SESSION['error'] = "ไม่มีข้อมูลในระบบ!";
                    header("location: login.php");
                }

            } catch(PDOException $e) {
                echo $e->getMessage();
            }
        }


    }

}


?>