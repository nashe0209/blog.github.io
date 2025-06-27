<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    die("Debes iniciar sesión para dar like.");
}

if (!isset($_POST['post_id'])) {
    die("ID de publicación no proporcionado.");
}

$usuario_id = $_SESSION['usuario_id'];
$post_id = $_POST['post_id'];

// Verificar si ya dio like
$sql = "SELECT COUNT(*) FROM likes WHERE publicacion_id = :post_id AND usuario_id = :usuario_id";
$stmt = $conexion->prepare($sql);
$stmt->execute([
    ':post_id' => $post_id,
    ':usuario_id' => $usuario_id
]);
$existe = $stmt->fetchColumn();

if ($existe == 0) {
    // Insertar el like
    $sql_insert = "INSERT INTO likes (usuario_id, publicacion_id) VALUES (:usuario_id, :post_id)";
    $stmt = $conexion->prepare($sql_insert);
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':post_id' => $post_id
    ]);
}

header("Location: ver_post.php?id=" . urlencode($post_id));
exit;
