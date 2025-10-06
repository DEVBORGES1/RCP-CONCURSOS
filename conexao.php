<?php
$host = "localhost";
$db   = "concursos";
$user = "root"; 
$pass = "F8b5o12a52";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>