<?php
    $host="localhost";
    $user="root";
    $password="";
    $db="ece_ciné";

    try {
        $db = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }