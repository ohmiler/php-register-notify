<?php 

    session_start();

    require_once('config.php');

    if (isset($_POST['signin'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if (empty($email)) {
            $_SESSION['error'] = 'กรุณากรอกอีเมล!';
            header("location: login.php");
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'อีเมลไม่ถูกต้อง!';
            header("location: login.php");
        } else if (empty($password)) {
            $_SESSION['error'] = 'กรุณากรอกรหัสผ่าน!';
            header("location: login.php");
        } else if (strlen($password) > 20 || strlen($password) < 5) {
            $_SESSION['error'] = 'รหัสผ่านต้องมีความยาวระหว่าง 5 ถึง 20 ตัวอักษร';
            header("location: login.php");
        } else {
            try {
                $check_data = $conn->prepare("SELECT * FROM users WHERE email = :email");
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
                            $_SESSION['error'] = "รหัสผ่านไม่ถูกต้อง!";
                            header("location: login.php");
                        }
                    } else {
                        $_SESSION['error'] = "อีเมลไม่ถูกต้อง!";
                        header("location: login.php");
                    }
                } else {
                    $_SESSION['error'] = "ไม่มีข้อมูลในระบบ!";
                    header("location: login.php");
                }

            } catch(PDOException $e) {
                echo $e->getMessage();
            }
        }
    }


?>