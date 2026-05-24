<?php
$host = 'YOUR_DATABASE_HOST'; // Cambiar por la IP o dominio de tu Base de Datos
$db   = 'YOUR_DATABASE_NAME'; // Cambiar por el nombre de la BBDD
$user = 'YOUR_DATABASE_USER'; // Cambiar por el usuario con permisos
$pass = 'YOUR_DATABASE_PASSWORD'; // Cambiar por la contraseña segura

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5 // Tiempo máximo de espera para no colgar la web
    ]);
} catch (PDOException $e) {
    // IMPORTANTE: No ponemos die(). Dejamos que la variable $pdo sea null
    // y el error lo gestionará send.php
    $pdo = null; 
    error_log("Fallo de conexión: " . $e->getMessage());
}