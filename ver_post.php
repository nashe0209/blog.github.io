<?php
// Aquí va tu PHP tal cual, sin cambios.
session_start(); 
require 'conexion.php';

// Verificar si se recibe el ID del post
if (!isset($_GET['id'])) {
    die("ID de publicación no especificado.");
}

$post_id = $_GET['id'];

// Consultar la publicación específica
$sql = "
    SELECT 
        p.titulo, 
        p.contenido, 
        p.fecha_publicacion, 
        u.nombre_usuario AS autor, 
        GROUP_CONCAT(c.nombre SEPARATOR ', ') AS categorias
    FROM publicaciones p
    LEFT JOIN usuarios u ON p.autor_id = u.id
    LEFT JOIN publicaciones_categorias pc ON p.id = pc.id_publicacion
    LEFT JOIN categorias c ON pc.id_categoria = c.id
    WHERE p.id = :post_id
    GROUP BY p.id;
";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die("Publicación no encontrada.");
}

// Consultar los comentarios
$sql_comments = "
    SELECT 
        c.contenido, 
        c.fecha_comentario AS fecha, 
        u.nombre_usuario AS autor
    FROM comentarios c
    LEFT JOIN usuarios u ON c.id_usuario = u.id
    WHERE c.id_post = :post_id
    ORDER BY c.fecha_comentario ASC;
";
$stmt = $conexion->prepare($sql_comments);
$stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener la cantidad total de likes del post
$sql_likes = "SELECT COUNT(*) AS total FROM likes WHERE publicacion_id = :post_id";
$stmt_likes = $conexion->prepare($sql_likes);
$stmt_likes->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$stmt_likes->execute();
$total_likes = $stmt_likes->fetchColumn();

// Saber si el usuario actual ya dio like
$usuario_id = $_SESSION['usuario_id'] ?? null;
$ya_dio_like = false;

if ($usuario_id) {
    $sql_check = "SELECT COUNT(*) FROM likes WHERE publicacion_id = :post_id AND usuario_id = :usuario_id";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->execute([
        ':post_id' => $post_id,
        ':usuario_id' => $usuario_id
    ]);
    $ya_dio_like = $stmt_check->fetchColumn() > 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($post['titulo']) ?></title>
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
        color:rgb(255, 255, 255);
        text-decoration: none; /* Quita subrayado por defecto */
        transition: color 0.3s ease, border-bottom 0.3s ease;
        border-bottom: 2px solid transparent; /* Línea invisible por defecto */
        padding-bottom: 2px; /* Para que el borde no “salte” al aparecer */
    }
    a:hover {
        color:rgb(255, 255, 255);
        border-bottom: 2px solid #155ab6; /* Línea visible solo en hover */
    }
    p a {
      color:rgb(17, 77, 156);
      text-decoration: none;
    }
    p a:hover {
        color: #1a73e8;
        border-bottom: 2px solid #1a73e8; /* Línea visible solo en hover */
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
    .contenido-post h2, .comentarios h2, .formulario-comentario h3 {
      color:rgb(21, 91, 184);
      border-bottom: 2px solid rgb(21, 91, 184);
      padding-bottom: 0.3rem;
      margin-bottom: 0.8rem;
      font-weight: 600;
    }
    .contenido-post p {
      font-size: 1.1rem;
      white-space: pre-wrap;
      margin-bottom: 2rem;
      color: #2c3e50;
    }
    /* Comentarios */
    
    .comentarios-lista {
      list-style: none;
      padding-left: 0;
      margin: 0 0 2rem 0;
      max-height: 300px;
      overflow-y: auto;
      border: 1px solid #ddd;
      border-radius: 6px;
      background-color: #fafafa;
    }
    .comentarios-lista li {
      border-bottom: 1px solid #ddd;
      padding: 0.75rem 1rem;
    }
    .comentarios-lista li:last-child {
      border-bottom: none;
    }
    .comentarios-lista p {
      margin: 0.2rem 0;
    }
    .comentarios-lista strong {
      color: #1a73e8;
    }
    .comentarios-lista em {
      font-size: 0.85rem;
      color: #999;
    }
    /* Formulario de comentario */
    .formulario-comentario textarea {
      width: 100%;
      font-family: inherit;
      font-size: 1rem;
      padding: 0.75rem;
      border-radius: 6px;
      border: 1px solid #ccc;
      resize: vertical;
      min-height: 90px;
      margin-bottom: 1rem;
    }
    .formulario-comentario button {
      background-color: #1a73e8;
      color: white;
      border: none;
      padding: 0.7rem 1.5rem;
      font-size: 1rem;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .formulario-comentario button:hover {
      background-color: #155ab6;
    }
    /* Likes */
    .likes {
      margin-top: 2rem;
    }
    .likes form button {
      background-color:rgb(255, 107, 107);
      color: white;
      border: none;
      padding: 0.6rem 1.4rem;
      font-size: 1rem;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .likes form button:hover:not(:disabled) {
      background-color:rgb(211, 77, 95);
    }
    .likes form button:disabled {
      background-color: #d7d7d7;
      cursor: default;
      color: #999;
    }
    .likes p {
      margin-top: 0.4rem;
      color: #666;
      font-size: 1rem;
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
    <?php if ($usuario_id): ?>
      <a href="crear_post.php">Crear Publicación</a>
      <a href="perfil.php">Perfil</a>
    <?php endif; ?>
  </div>

  <!-- Contenido principal -->
  <div class="main-content">
    <h1><?= htmlspecialchars($post['titulo']) ?></h1>
    <p><strong>Autor:</strong> 
        <a href="perfil_publico.php?usuario=<?= urlencode($post['autor']) ?>">
            <?= htmlspecialchars($post['autor']) ?>
        </a>
    </p>

    <p><strong>Fecha de publicación:</strong> <?= htmlspecialchars($post['fecha_publicacion']) ?></p>
    <p><strong>Categorías:</strong> 
        <?php
        if (!empty($post['categorias'])) {
            $categorias = explode(', ', $post['categorias']);
            $enlaces = array_map(function ($categoria) {
                $nombre = htmlspecialchars($categoria);
                return "<a href='posts_categoria.php?categoria=" . urlencode($nombre) . "'>$nombre</a>";
            }, $categorias);
            echo implode(', ', $enlaces);
        } else {
            echo "Sin Categorías";
        }
        ?>
    </p>


    <div class="contenido-post">
      <h2>Contenido:</h2>
      <p><?= nl2br(htmlspecialchars($post['contenido'])) ?></p>
    </div>

    <div class="comentarios">
        <h2>Comentarios:</h2>
        <?php if (count($comments) > 0): ?>
            <ul class="comentarios-lista">
            <?php foreach ($comments as $comment): ?>
                <li>
                <p>
                    <strong>
                    <a href="perfil_publico.php?usuario=<?= urlencode($comment['autor']) ?>">
                        <?= htmlspecialchars($comment['autor']) ?>
                    </a>:
                    </strong>
                </p>
                <p><?= nl2br(htmlspecialchars($comment['contenido'])) ?></p>
                <p><em><?= htmlspecialchars($comment['fecha']) ?></em></p>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No hay comentarios.</p>
        <?php endif; ?>
    </div>


    <?php if ($usuario_id): ?>
      <div class="formulario-comentario">
        <h3>Agregar comentario:</h3>
        <form action="comentar.php" method="POST">
          <input type="hidden" name="id_post" value="<?= htmlspecialchars($post_id) ?>">
          <textarea name="contenido" rows="4" required placeholder="Escribe tu comentario..."></textarea><br>
          <button type="submit">Publicar comentario</button>
        </form>
      </div>
    <?php else: ?>
      <p><a href="login.html">Inicia sesión</a> para comentar.</p>
    <?php endif; ?>

    <div class="likes">
      <form action="like.php" method="POST">
        <input type="hidden" name="post_id" value="<?= htmlspecialchars($post_id) ?>">
        <?php if ($usuario_id): ?>
          <button type="submit" <?= $ya_dio_like ? 'disabled' : '' ?>>
            👍 Me gusta (<?= $total_likes ?>)
          </button>
          <?php if ($ya_dio_like): ?>
            <p>Ya diste like a esta publicación.</p>
          <?php endif; ?>
        <?php else: ?>
          <p><a href="login.html">Inicia sesión</a> para dar like.</p>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <!-- Barra lateral derecha -->
  <div class="sidebar-right">
    <h3>Opciones</h3>
    <?php if (!$usuario_id): ?>
      <a href="registro.html">Registrarse</a>
      <a href="login.html">Iniciar Sesión</a>
    <?php else: ?>
      <a href="logout.php">Cerrar Sesión</a>
    <?php endif; ?>
  </div>
</body>
</html>
