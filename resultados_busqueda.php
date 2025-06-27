<?php
session_start();
require_once 'conexion.php';

$autenticado = isset($_SESSION['usuario_id']);
$termino = $_GET['q'] ?? '';
$resultados = [];

if (!empty($termino)) {
    try {
        $sql = "SELECT p.id, p.titulo, p.contenido, p.fecha_publicacion, u.nombre_usuario 
                FROM publicaciones p
                JOIN usuarios u ON p.autor_id = u.id
                WHERE p.titulo LIKE :termino OR p.contenido LIKE :termino
                ORDER BY p.fecha_publicacion DESC";

        $stmt = $conexion->prepare($sql);
        $likeTerm = "%$termino%";
        $stmt->bindParam(':termino', $likeTerm, PDO::PARAM_STR);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error en la búsqueda: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Resultados de Búsqueda</title>
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
  p a {
    color: rgb(17, 77, 156);
    text-decoration: none;
  }
  p a:hover {
    color: #1a73e8;
    border-bottom: 2px solid #1a73e8;
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
  .main-content h1 {
    margin-top: 0;
    font-size: 2.25rem;
    color: #2c3e50;
  }
  .main-content p {
    line-height: 1.5;
    margin: 0.3rem 0 1rem 0;
    font-size: 1rem;
    color: #555;
  }
  .main-content strong {
    color: #34495e;
  }

  /* Lista de resultados */
  ul {
    list-style: none;
    padding-left: 0;
  }
  ul li {
    border-bottom: 1px solid #ddd;
    padding: 1rem 0;
  }
  ul li:last-child {
    border-bottom: none;
  }
  ul li h3 {
    margin: 0 0 0.3rem 0;
    font-size: 1.3rem;
  }
  ul li h3 a {
    color: rgb(17, 77, 156);
    text-decoration: none;
    border-bottom: 2px solid transparent;
    padding-bottom: 2px;
    transition: color 0.3s ease, border-bottom 0.3s ease;
  }
  ul li h3 a:hover {
    color: #1a73e8;
    border-bottom: 2px solid #1a73e8;
  }
  ul li small {
    color: #999;
    font-size: 0.9rem;
    display: block;
    margin-bottom: 0.5rem;
  }
  ul li p {
    margin: 0;
    color: #444;
    font-size: 1rem;
  }

  /* Mensaje no resultados */
  .no-results {
    font-style: italic;
    color: #777;
    font-size: 1.1rem;
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

</head>
<body>
  <!-- Barra lateral izquierda -->
  <div class="sidebar-left">
    <h3>Blog Personal</h3>
    <a href="index.php">Inicio</a>
    <?php if ($autenticado): ?>
      <a href="crear_post.php">Crear Publicación</a>
      <a href="perfil.php">Perfil</a>
    <?php endif; ?>
  </div>

  <!-- Contenido principal -->
  <div class="main-content">
    <h1>Resultados de Búsqueda</h1>
    <p>Mostrando resultados para: <strong>"<?= htmlspecialchars($termino) ?>"</strong></p>

    <?php if (!empty($resultados)): ?>
      <ul>
        <?php foreach ($resultados as $post): ?>
          <li>
            <h3><a href="ver_post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['titulo']) ?></a></h3>
            <small>Por <?= htmlspecialchars($post['nombre_usuario']) ?> – <?= $post['fecha_publicacion'] ?></small>
            <p><?= substr(strip_tags($post['contenido']), 0, 100) ?>...</p>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="no-results">No se encontraron resultados.</p>
    <?php endif; ?>
  </div>

  <!-- Barra lateral derecha -->
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
