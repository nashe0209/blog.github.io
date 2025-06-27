<?php
$host = "localhost"; // Cambia si no usas localhost
$dbname = "blog_personal";
$username = "root"; // Cambia al nombre de usuario de tu DB
$password = ""; // Cambia a tu contraseña de DB

try {
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
?>
