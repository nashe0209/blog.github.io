<?php
session_start();
require 'conexion.php';

// Validar parámetro
if (!isset($_GET['categoria'])) {
    die("Categoría no especificada.");
}

$categoria_nombre = $_GET['categoria'];

// Obtener publicaciones que pertenezcan a esa categoría
$sql = "
    SELECT p.id, p.titulo, p.fecha_publicacion, u.nombre_usuario AS autor
    FROM publicaciones p
    INNER JOIN publicaciones_categorias pc ON p.id = pc.id_publicacion
    INNER JOIN categorias c ON pc.id_categoria = c.id
    INNER JOIN usuarios u ON p.autor_id = u.id
    WHERE c.nombre = :categoria
    ORDER BY p.fecha_publicacion DESC
";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':categoria', $categoria_nombre, PDO::PARAM_STR);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Publicaciones en <?= htmlspecialchars($categoria_nombre) ?></title>
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
        .main-content a {
            color: #155ab6;
            border-bottom: 2px solid transparent;
        }
        .main-content a:hover {
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
            background-color: #fff;
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
            margin-bottom: 1.5rem;
        }
        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        ul li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #ddd;
        }
        ul li:last-child {
            border-bottom: none;
        }
        li em {
            font-size: 0.85rem;
            color: #666;
        }
        p {
            color: #555;
            font-size: 1rem;
            margin-top: 1rem;
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
  <div class="sidebar-left">
    <h3>Blog Personal</h3>
    <a href="index.php">Inicio</a>
    <?php if (isset($_SESSION['usuario_id'])): ?>
      <a href="crear_post.php">Crear Publicación</a>
      <a href="perfil.php">Perfil</a>
    <?php endif; ?>
  </div>

  <div class="main-content">
    <h1>Categoría: <?= htmlspecialchars($categoria_nombre) ?></h1>

    <?php if (count($posts) > 0): ?>
      <ul>
        <?php foreach ($posts as $post): ?>
          <li>
            <a href="ver_post.php?id=<?= $post['id'] ?>">
              <?= htmlspecialchars($post['titulo']) ?>
            </a> - <em><?= $post['fecha_publicacion'] ?></em> por <?= htmlspecialchars($post['autor']) ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No hay publicaciones en esta categoría.</p>
    <?php endif; ?>
  </div>

  <div class="sidebar-right">
    <h3>Opciones</h3>
    <?php if (!isset($_SESSION['usuario_id'])): ?>
      <a href="registro.html">Registrarse</a>
      <a href="login.html">Iniciar Sesión</a>
    <?php else: ?>
      <a href="logout.php">Cerrar Sesión</a>
    <?php endif; ?>
  </div>
</body>
</html>
