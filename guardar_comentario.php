<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
  header("Location: login.html");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_post = intval($_POST['id_post']);
  $contenido = trim($_POST['contenido']);
  $id_usuario = $_SESSION['usuario_id'];

  if ($id_post > 0 && !empty($contenido)) {
    try {
      $sql = "INSERT INTO comentarios (id_post, id_usuario, contenido)
              VALUES (:id_post, :id_usuario, :contenido)";
      $stmt = $conexion->prepare($sql);
      $stmt->bindParam(':id_post', $id_post);
      $stmt->bindParam(':id_usuario', $id_usuario);
      $stmt->bindParam(':contenido', $contenido);
      $stmt->execute();

      header("Location: ver_post.php?id=$id_post");
      exit;
    } catch (PDOException $e) {
      die("Error al guardar el comentario: " . $e->getMessage());
    }
  } else {
    die("Datos inválidos.");
  }
}
?>
