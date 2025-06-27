<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $id_usuario = $_SESSION['usuario_id'];
    $categorias_seleccionadas = $_POST['categorias'] ?? [];

    if (empty($titulo) || empty($contenido)) {
        die("Título y contenido son obligatorios.");
    }

    try {
        // Insertar post
        $sql = "INSERT INTO publicaciones (titulo, contenido, autor_id) VALUES (:titulo, :contenido, :autor_id)";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':contenido', $contenido);
        $stmt->bindParam(':autor_id', $id_usuario);
        $stmt->execute();

        $id_post = $conexion->lastInsertId();

        // Guardar categorías asignadas
        if (!empty($categorias_seleccionadas)) {
            $sql_cat = "INSERT INTO publicaciones_categorias (id_publicacion, id_categoria) VALUES (:id_publicacion, :id_categoria)";
            $stmt_cat = $conexion->prepare($sql_cat);
            foreach ($categorias_seleccionadas as $id_cat) {
                $id_cat_int = intval($id_cat);
                $stmt_cat->bindParam(':id_publicacion', $id_post);
                $stmt_cat->bindParam(':id_categoria', $id_cat_int);
                $stmt_cat->execute();
            }
        }

        header("Location: ver_post.php?id=$id_post");
        exit;
    } catch (PDOException $e) {
        die("Error al guardar la publicación: " . $e->getMessage());
    }
} else {
    die("Método no permitido.");
}
