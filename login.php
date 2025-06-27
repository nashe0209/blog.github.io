<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';

    if (!empty($correo) && !empty($contraseña)) {
        try {
            // Buscar usuario por correo
            $stmt = $conexion->prepare("SELECT id, nombre_usuario, contraseña FROM usuarios WHERE correo = :correo");
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() === 1) {
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verificar contraseña
                if (password_verify($contraseña, $usuario['contraseña'])) {
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];

                    // Redirigir al perfil o a index
                    header("Location: index.php");
                    exit;
                } else {
                    echo "Contraseña incorrecta.";
                }
            } else {
                echo "Correo no registrado.";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Por favor, completa todos los campos.";
    }
} else {
    echo "Acceso no autorizado.";
}
?>
