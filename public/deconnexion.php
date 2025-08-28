<?php  
if (session_status() == PHP_SESSION_NONE)
    session_start();

    if (isset($_COOKIE['PHPSESSID'])) {
        setcookie('PHPSESSID', '', time() - 3600, '/');
    }

    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/');
    }

    $_SESSION=[];
    unset($_SESSION);
    unset($_COOKIE["remember_me"]);
    session_destroy();


    header("Location: login.php");
    exit();
