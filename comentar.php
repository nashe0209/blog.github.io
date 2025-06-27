<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    die("No estás autenticado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_post = $_POST['id_post'] ?? null;
    $contenido = trim($_POST['contenido'] ?? '');

    if ($id_post && $contenido !== '') {
        $sql = "INSERT INTO comentarios (id_post, id_usuario, contenido) VALUES (:id_post, :id_usuario, :contenido)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id_post' => $id_post,
            ':id_usuario' => $_SESSION['usuario_id'],
            ':contenido' => $contenido
        ]);
    }
}

header("Location: ver_post.php?id=" . urlencode($id_post));
exit;
?>
