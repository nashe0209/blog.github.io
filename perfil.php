<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit;
}

require_once 'conexion.php';

$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = htmlspecialchars($_SESSION['nombre_usuario']);

try {
    $sql = "
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
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $mis_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener publicaciones: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi Perfil</title>
  <style>
  /* Reset y base */
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

  /* Barras laterales */
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

  /* Contenido principal */
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

  /* Lista de posts */
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
  .post-actions {
    margin-top: 0.8rem;
  }
  .post-actions a.edit-button,
  .post-actions button.delete-button {
    background-color: #1a73e8;
    color: white;
    border: none;
    padding: 0.5rem 1.2rem;
    font-size: 1rem;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-right: 0.5rem;
    text-decoration: none;
    display: inline-block;
  }
  .post-actions a.edit-button:hover {
    background-color: #155ab6;
  }
  .post-actions button.delete-button {
    background-color: rgb(255, 107, 107);
  }
  .post-actions button.delete-button:hover {
    background-color: rgb(211, 77, 95);
  }

  /* Responsive */
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

  <script>
    function confirmarEliminacion(postId) {
      if (confirm("¿Estás seguro de que deseas eliminar esta publicación?")) {
        window.location.href = `eliminar_post.php?id=${postId}`;
      }
    }
  </script>
</head>
<body>
  <!-- Barra lateral izquierda -->
  <div class="sidebar-left">
    <h3>Blog Personal</h3>
    <a href="index.php">Inicio</a>
    <a href="crear_post.php">Crear Publicación</a>
    <a href="perfil.php">Perfil</a>
  </div>

  <!-- Contenido principal -->
  <div class="main-content">
    <div class="perfil-header">
      <h1>Perfil de <?= $nombre_usuario ?></h1>
      <p>Bienvenido a tu perfil personal. Aquí están tus publicaciones:</p>
    </div>

    <section id="mis-posts">
      <?php if (count($mis_posts) > 0): ?>
        <ul class="post-list">
          <?php foreach ($mis_posts as $post): ?>
            <li class="post-item">
              <h3>
                <a href="ver_post.php?id=<?= $post['id'] ?>">
                  <?= htmlspecialchars($post['titulo']) ?>
                </a>
              </h3>
              <p><strong>Fecha:</strong> <?= htmlspecialchars($post['fecha_publicacion']) ?></p>
              <p><strong>Categorías:</strong> <?= htmlspecialchars($post['categorias'] ?? 'Sin Categorías') ?></p>
              <div class="post-actions">
                <a href="editar_post.php?id=<?= $post['id'] ?>" class="edit-button">Editar</a>
                <button onclick="confirmarEliminacion(<?= $post['id'] ?>)" class="delete-button">Eliminar</button>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>Aún no has publicado nada. ¡Crea tu primera publicación!</p>
      <?php endif; ?>
    </section>
  </div>

  <!-- Barra lateral derecha -->
  <div class="sidebar-right">
    <h3>Opciones</h3>
    <a href="logout.php">Cerrar Sesión</a>
  </div>
</body>
</html>