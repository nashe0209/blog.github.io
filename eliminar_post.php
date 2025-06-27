<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit;
}

require_once 'conexion.php';

if (!isset($_GET['id'])) {
    die("No se proporcionó un ID de publicación.");
}

$usuario_id = $_SESSION['usuario_id'];
$post_id = $_GET['id'];

try {
    // Verificar si el usuario es el autor de la publicación
    $sql = "SELECT id FROM publicaciones WHERE id = :post_id AND autor_id = :usuario_id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        die("No tienes permiso para eliminar esta publicación o no existe.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Eliminar la publicación
        $delete_sql = "DELETE FROM publicaciones WHERE id = :post_id AND autor_id = :usuario_id";
        $delete_stmt = $conexion->prepare($delete_sql);
        $delete_stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $delete_stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);

        if ($delete_stmt->execute()) {
            header("Location: perfil.php");
            exit;
        } else {
            echo "Error al eliminar la publicación.";
        }
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Publicación</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="main-content">
        <h1>Confirmar Eliminación</h1>
        <p>¿Estás seguro de que deseas eliminar esta publicación? Esta acción no se puede deshacer.</p>
        <form action="" method="POST">
            <button type="submit">Eliminar</button>
            <a href="perfil.php">Cancelar</a>
        </form>
    </div>
</body>
</html>
