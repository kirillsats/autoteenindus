<?php
$host = "localhost";
$db   = "autoteenindus";
$user = "root";
$pass = ""; // по умолчанию в XAMPP пусто

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
