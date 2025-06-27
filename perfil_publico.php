<?php
require_once 'conexion.php';
session_start();
$autenticado = isset($_SESSION['usuario_id']);

if (!isset($_GET['usuario']) || empty($_GET['usuario'])) {
    die("Usuario no especificado.");
}

$nombre_usuario_url = $_GET['usuario'];

try {
    $stmt_usuario = $conexion->prepare("SELECT id, nombre_usuario FROM usuarios WHERE nombre_usuario = :nombre_usuario");
    $stmt_usuario->bindParam(':nombre_usuario', $nombre_usuario_url, PDO::PARAM_STR);
    $stmt_usuario->execute();
    $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("Usuario no encontrado.");
    }

    $stmt_posts = $conexion->prepare("
        SELECT 
            p.id, 
            p.titulo, 
            p.fecha_publicacion, 
            GROUP_CONCAT(c.nombre SEPARATOR ', ') AS categorias
        FROM publicaciones p
        LEFT JOIN publicaciones_categorias pc ON pc.id_publicacion = p.id
        LEFT JOIN categorias c ON pc.id_categoria = c.id
        WHERE p.autor_id = :usuario_id
        GROUP BY p.id
        ORDER BY p.fecha_publicacion DESC
    ");
    $stmt_posts->bindParam(':usuario_id', $usuario['id'], PDO::PARAM_INT);
    $stmt_posts->execute();
    $publicaciones = $stmt_posts->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al obtener perfil: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil público de <?= htmlspecialchars($usuario['nombre_usuario']) ?></title>
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f7fa;
      color: #333;
      display: flex;
      min-height: 100vh;
    }
    a {
      font-size: 1rem;
      color: rgb(255, 255, 255);
      text-decoration: none;
      transition: color 0.3s ease, border-bottom 0.3s ease;
      border-bottom: 2px solid transparent;
      padding-bottom: 2px;
    }
    a:hover {
      color: rgb(255, 255, 255);
      border-bottom: 2px solid #155ab6;
    }
    .sidebar-left, .sidebar-right {
      background-color: #2c3e50;
      color: #ecf0f1;
      padding: 1.5rem 1rem;
      width: 220px;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    .sidebar-left h3, .sidebar-right h3 {
      margin-top: 0;
      margin-bottom: 1rem;
      font-size: 1.3rem;
      font-weight: 700;
      border-bottom: 1px solid #34495e;
      padding-bottom: 0.5rem;
    }
    .sidebar-left a, .sidebar-right a {
      padding: 0.4rem 0.25rem;
      border-radius: 4px;
      transition: background-color 0.3s ease;
    }
    .sidebar-left a:hover, .sidebar-right a:hover {
      background-color: #34495e;
    }

    .main-content {
      flex-grow: 1;
      background-color: #ffffff;
      padding: 2rem 3rem;
      max-width: 900px;
      margin: 1rem auto;
      box-shadow: 0 0 12px rgba(0,0,0,0.1);
      border-radius: 8px;
    }
    .perfil-header h1 {
      margin-top: 0;
      font-size: 2.25rem;
      color: #2c3e50;
    }
    .perfil-header p {
      color: #555;
      font-size: 1rem;
      margin-bottom: 2rem;
    }

    .post-list {
      list-style: none;
      padding-left: 0;
    }
    .post-item {
      border-bottom: 1px solid #ddd;
      padding: 1.25rem 0;
    }
    .post-item:last-child {
      border-bottom: none;
    }
    .post-item h3 {
      margin: 0 0 0.4rem 0;
      font-size: 1.4rem;
    }
    .post-item h3 a {
      color: rgb(17, 77, 156);
      text-decoration: none;
      border-bottom: 2px solid transparent;
      padding-bottom: 2px;
      transition: color 0.3s ease, border-bottom 0.3s ease;
    }
    .post-item h3 a:hover {
      color: #1a73e8;
      border-bottom: 2px solid #1a73e8;
    }
    .post-item p {
      margin: 0.3rem 0;
      color: #555;
    }

    @media (max-width: 900px) {
      body {
        flex-direction: column;
      }
      .sidebar-left, .sidebar-right {
        width: 100%;
        flex-direction: row;
        justify-content: center;
        gap: 2rem;
        padding: 1rem;
      }
      .sidebar-left a, .sidebar-right a {
        padding: 0.5rem 1rem;
      }
      .main-content {
        max-width: 100%;
        margin: 1rem;
        padding: 1.5rem;
        border-radius: 0;
        box-shadow: none;
      }
    }
  </style>
</head>
<body>
  <div class="sidebar-left">
    <h3>Blog Personal</h3>
    <a href="index.php">Inicio</a>
    <?php if ($autenticado): ?>
      <a href="crear_post.php">Crear Publicación</a>
      <a href="perfil.php">Mi Perfil</a>
    <?php endif; ?>
  </div>

  <div class="main-content">
    <div class="perfil-header">
      <h1>Perfil público de <?= htmlspecialchars($usuario['nombre_usuario']) ?></h1>
      <p>Estas son las publicaciones de este autor:</p>
    </div>

    <?php if (count($publicaciones) > 0): ?>
      <ul class="post-list">
        <?php foreach ($publicaciones as $post): ?>
          <li class="post-item">
            <h3><a href="ver_post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['titulo']) ?></a></h3>
            <p><strong>Fecha:</strong> <?= htmlspecialchars($post['fecha_publicacion']) ?></p>
            <p><strong>Categorías:</strong> <?= htmlspecialchars($post['categorias'] ?? 'Sin Categorías') ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>Este usuario aún no ha publicado contenido.</p>
    <?php endif; ?>
  </div>

  <div class="sidebar-right">
    <h3>Opciones</h3>
    <?php if (!$autenticado): ?>
      <a href="registro.html">Registrarse</a>
      <a href="login.html">Iniciar Sesión</a>
    <?php else: ?>
      <a href="logout.php">Cerrar Sesión</a>
    <?php endif; ?>
  </div>
</body>
</html>
