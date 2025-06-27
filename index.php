<?php
session_start();
$autenticado = isset($_SESSION['usuario_id']);
require 'conexion.php';

// Configuración de paginación
$por_pagina = 5; // Número de publicaciones por página
$pagina_actual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// Obtener el total de publicaciones
$total_sql = "SELECT COUNT(*) FROM publicaciones";
$total_stmt = $conexion->query($total_sql);
$total_publicaciones = $total_stmt->fetchColumn();
$total_paginas = ceil($total_publicaciones / $por_pagina);

// Consultar publicaciones con límite y desplazamiento
$sql = "
    SELECT 
        p.id, 
        p.titulo, 
        p.fecha_publicacion, 
        p.contenido, 
        u.nombre_usuario AS autor, 
        GROUP_CONCAT(c.nombre SEPARATOR ', ') AS categorias
    FROM publicaciones p
    LEFT JOIN usuarios u ON p.autor_id = u.id
    LEFT JOIN publicaciones_categorias pc ON pc.id_publicacion = p.id
    LEFT JOIN categorias c ON pc.id_categoria = c.id
    GROUP BY p.id
    ORDER BY p.fecha_publicacion DESC
    LIMIT :limite OFFSET :offset
";

$stmt = $conexion->prepare($sql);
$stmt->bindValue(':limite', $por_pagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Inicio</title>
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
    .main-content h1 {
        margin-top: 0;
        font-size: 2.25rem;
        color: #2c3e50;
        margin-bottom: 1rem;
    }
    .main-content p {
        font-size: 1rem;
        color: #555;
        margin-bottom: 1.5rem;
    }

    /* Barra de búsqueda */
    .search-form {
        display: flex;
        margin-bottom: 2rem;
        gap: 0.5rem;
    }
    .search-form input[type="text"] {
        flex-grow: 1;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-family: inherit;
        transition: border-color 0.3s ease;
    }
    .search-form input[type="text"]:focus {
        border-color: #1a73e8;
        outline: none;
    }
    .search-form button {
        background-color: #1a73e8;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .search-form button:hover {
        background-color: #155ab6;
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

    .pagination {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .pagination a {
        display: inline-block;
        padding: 0.5rem 0.9rem;
        background-color: #2c3e50;
        color: #ecf0f1;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 500;
        transition: background-color 0.3s ease;
    }

    .pagination a:hover {
        background-color: #34495e;
    }

    .pagination a.active {
        background-color: #155ab6;
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
    <h1>Blog Personal</h1>
    
    <!-- Barra de búsqueda -->
    <form action="resultados_busqueda.php" method="GET" class="search-form">
        <input type="text" name="q" placeholder="Buscar publicaciones..." required>
        <button type="submit" style="font-size: 1rem;">🔍</button>
    </form>

    <?php if ($autenticado): ?>
      <p>Hola, <?= htmlspecialchars($_SESSION['nombre_usuario']) ?>. Aquí puedes ver todas las publicaciones recientes.</p>
    <?php else: ?>
      <p>Por favor inicia sesión para interactuar o crea una cuenta nueva.</p>
    <?php endif; ?>

    <!-- Sección de publicaciones recientes -->
    <section id="posts">
      <h2>Publicaciones Recientes</h2>
      <?php if (count($posts) > 0): ?>
        <ul class="post-list">
          <?php foreach ($posts as $post): ?>
            <li class="post-item">
              <h3>
                <a href="ver_post.php?id=<?= $post['id'] ?>">
                  <?= htmlspecialchars($post['titulo']) ?>
                </a>
              </h3>
              <p><strong>Autor:</strong> <?= htmlspecialchars($post['autor']) ?></p>
              <p><strong>Fecha:</strong> <?= htmlspecialchars($post['fecha_publicacion']) ?></p>
              <p><strong>Categorías:</strong> <?= htmlspecialchars($post['categorias'] ?? 'Sin Categorías') ?></p>
              <p><?= htmlspecialchars(substr($post['contenido'], 0, 100)) ?>...</p>
            </li>
          <?php endforeach; ?>
        </ul>
        <?php if ($total_paginas > 1): ?>
            <div class="pagination">
                <?php if ($pagina_actual > 1): ?>
                <a href="?pagina=<?= $pagina_actual - 1 ?>">&laquo; Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="?pagina=<?= $i ?>" class="<?= $i === $pagina_actual ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>

                <?php if ($pagina_actual < $total_paginas): ?>
                <a href="?pagina=<?= $pagina_actual + 1 ?>">Siguiente &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

      <?php else: ?>
        <p>No hay publicaciones recientes.</p>
      <?php endif; ?>
    </section>
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
