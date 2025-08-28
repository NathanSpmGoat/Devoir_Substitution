<?php
    $host="localhost";
    $user="root";
    $password="";
    $db="ece_ciné";

    try {
        $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }