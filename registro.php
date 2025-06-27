<?php
session_start(); // Iniciar sesión

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nombre_usuario'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';

    if (!empty($nombre_usuario) && !empty($correo) && !empty($contraseña)) {
        try {
            $hash_contraseña = password_hash($contraseña, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuarios (nombre_usuario, correo, contraseña) 
                    VALUES (?, ?, ?)";
            $query = $conexion->prepare($sql);
            $query->execute([$nombre_usuario, $correo, $hash_contraseña]);

            // Obtener el ID del nuevo usuario
            $usuario_id = $conexion->lastInsertId();

            // Guardar datos en la sesión
            $_SESSION['usuario_id'] = $usuario_id;
            $_SESSION['nombre_usuario'] = $nombre_usuario;
            $_SESSION['correo'] = $correo;

            // Redirigir a index.php
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Por favor, completa todos los campos.";
    }
}
?>
