<?php 

    session_start();
    unset($_SESSION['user_login']);
    header("location: index.php");

?>