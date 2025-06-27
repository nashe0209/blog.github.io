<?php
session_start();
require_once 'conexion.php';

// Verificar si usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit;
}

// Obtener categorías para mostrar en el formulario
try {
    $sql = "SELECT id, nombre FROM categorias ORDER BY nombre ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar categorías: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Crear Publicación</title>
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

        /* Formulario */
        form {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }
        label {
            font-weight: 600;
            color: #155ab8;
        }
        input[type="text"],
        textarea,
        select {
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-family: inherit;
            resize: vertical;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            border-color: #1a73e8;
            outline: none;
        }
        textarea {
            min-height: 150px;
        }
        select[multiple] {
            height: auto;
            min-height: 120px;
        }
        button {
            background-color: #1a73e8;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            align-self: flex-start;
        }
        button:hover {
            background-color: #155ab6;
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
    <a href="perfil.php">Perfil</a>
  </div>

  <div class="main-content">
    <h1>Crear Nueva Publicación</h1>
    <form action="guardar_post.php" method="POST">
      <label for="titulo">Título:</label>
      <input type="text" name="titulo" id="titulo" required>

      <label for="contenido">Contenido:</label>
      <textarea name="contenido" id="contenido" rows="10" required></textarea>

      <label for="categorias">Categorías (mantén Ctrlpara seleccionar varias):</label>
      <select name="categorias[]" id="categorias" multiple size="5">
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
        <?php endforeach; ?>
      </select>

      <button type="submit">Publicar</button>
    </form>
  </div>

  <div class="sidebar-right">
    <h3>Opciones</h3>
    <a href="logout.php">Cerrar Sesión</a>
  </div>
</body>
</html>
