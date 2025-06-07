<?php
session_start();
require 'vendor/autoload.php';

use MongoDB\Client;

$uri = "mongodb+srv://joelnp:joel16@cluster0.qcsid.mongodb.net/?retryWrites=true&w=majority";

try {
    $client = new Client($uri);
    $database = $client->selectDatabase('PFDJoel'); 
    $collection = $database->selectCollection('comunidad');
    $collectionUsuarios = $database->selectCollection('usuarios');

    $usuario = $_SESSION['usuario'] ?? null;
    $esAdmin = false;
$posts = $collection->find([], ['sort' => ['fecha' => -1]]);

    if ($usuario) {
        $usuarioDB = $collectionUsuarios->findOne(['nombre' => $usuario]);
        if ($usuarioDB && isset($usuarioDB['rol']) && $usuarioDB['rol'] === 'admin') {
            $esAdmin = true;
        }
    }
 if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario']) && isset($_POST['post_id'])) {
    $comentario = trim($_POST['comentario']);
    $postId = new MongoDB\BSON\ObjectId($_POST['post_id']);
    $nombreUsuario = $usuario['nombre'] ?? 'Anónimo';
    $fechaComentario = new MongoDB\BSON\UTCDateTime();

    if ($comentario !== '') {
        $collection->updateOne(
            ['_id' => $postId],
            ['$push' => [
                'comentarios' => [
                    'usuario' => $nombreUsuario,
                    'texto' => $comentario,
                    'fecha' => $fechaComentario
                ]
            ]]
        );
    }
    header("Location: comunidad.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_post_id'])) {
    $postId = new MongoDB\BSON\ObjectId($_POST['like_post_id']);
    $nombreUsuario = $usuario['nombre'] ?? null;
    if ($nombreUsuario) {
        // Verifica si ya dio like
        $post = $collection->findOne(['_id' => $postId, 'likes' => $nombreUsuario]);
        if ($post) {
            // Si ya dio like, lo quita (toggle)
            $collection->updateOne(
                ['_id' => $postId],
                ['$pull' => ['likes' => $nombreUsuario]]
            );
        } else {
            // Si no, lo añade
            $collection->updateOne(
                ['_id' => $postId],
                ['$addToSet' => ['likes' => $nombreUsuario]]
            );
        }
    }
    header("Location: comunidad.php");
    exit();
}

// --- Procesar nuevo post ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['texto'])) {
    $texto = trim($_POST['texto']);
    $fotoPath = '';
    $videoPath = '';

    // Guardar foto si se sube
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fotoName = uniqid() . '_' . basename($_FILES['foto']['name']);
        $fotoPath = 'img/comunidad/' . $fotoName;
        if (!is_dir('img/comunidad')) {
            mkdir('img/comunidad', 0777, true);
        }
        move_uploaded_file($_FILES['foto']['tmp_name'], $fotoPath);
    }

    // Guardar video si se sube
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $videoName = uniqid() . '_' . basename($_FILES['video']['name']);
        $videoPath = 'img/comunidad/' . $videoName;
        if (!is_dir('img/comunidad')) {
            mkdir('img/comunidad', 0777, true);
        }
        move_uploaded_file($_FILES['video']['tmp_name'], $videoPath);
    }

    // Guardar el post en la colección comunidad
    $collection->insertOne([
        'usuario' => [
            'nombre' => $usuario['nombre'],
            'gmail'  => $usuario['gmail']
        ],
        'texto' => $texto,
        'foto' => $fotoPath,
        'video' => $videoPath,
        'fecha' => new MongoDB\BSON\UTCDateTime(),
        'likes' => [], 
        'comentarios' => []
    ]);

    header("Location: comunidad.php");
    exit();
}
  }
 catch (Exception $e) {
    echo "Error al conectar a MongoDB: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&family=Special+Gothic+Expanded+One&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css">
    <title>Document</title>
</head>
<body>
<div id="redes">
  <div class="info-contacto">
    <img src="img/correo-electronico.png" alt="Teléfono" class="icono-red">
    <span>666 123 456</span>
  </div>
  <div class="iconos-redes">
    <img src="img/simbolo-de-la-aplicacion-de-facebook.png" alt="Facebook" class="icono-red">
    <img src="img/gorjeo.png" alt="Twitter" class="icono-red">
    <img src="img/instagram.png" alt="Instagram" class="icono-red">
  </div>
</div>

<header>
  <nav class="navbar navbar-expand-md w-100 py-3">
    <div class="container-fluid px-4">

      <a class="navbar-brand m-0" href="index.html">
        <img class="logo" src="img/logo - copia.png" alt="Logo">
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapseContent" aria-controls="navbarCollapseContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-end" id="navbarCollapseContent">
        <ul class="navbar-nav d-flex  flex-md-row align-items-md-center">

          <li class="nav-item">
            <a class="nav-link enlace" href="partida.php">PARTIDA</a>
          </li>
          <li class="nav-item">
            <a class="nav-link enlace enlace-destacado" href="comunidad.php">COMUNIDAD</a>
          </li>
          <li class="nav-item">
            <a class="nav-link enlace" href="contacto.php">CONTACTO</a>
          </li>

          <li class="nav-item">
            <a class="nav-link enlace-icono" href="mapa2.html">
               <!-- <img src="img/marcador.png" alt="Localiza tu campo">-->
              LOCALIZA TU CAMPO
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link enlace-icono" href="reservar.php">
              <!-- <img src="img/reserva.png" alt="Reserva pista">--> RESERVA PISTA
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link enlace-icono" href="perfil.php">
             <!-- <img src="img/usuario.png" alt="Usuario">-->MI CUENTA
            </a>
          </li>

        </ul>
      </div>

    </div>
  </nav>
</header>


<?php if (!$usuario): ?>
  <a 
    href="login.php" 
    class="btn btn-primary btn-lg rounded-circle shadow fab-btn" 
    title="Inicia sesión para publicar"
    style="display: flex; align-items: center; justify-content: center; font-size: 4rem;">
    +
  </a>
<?php else: ?>
  <button 
    class="btn btn-primary btn-lg rounded-circle shadow fab-btn" 
    type="button" 
    data-bs-toggle="collapse" 
    data-bs-target="#nuevoPost" 
    aria-expanded="false" 
    aria-controls="nuevoPost"
    title="Añadir post">
    <span style="font-size:4rem;line-height:1;">+</span>
  </button>
<?php endif; ?>

<div class="collapse" id="nuevoPost">
  <div class="fullscreen-form">
    <form action="comunidad.php" method="POST" enctype="multipart/form-data" class="w-100 h-100 d-flex flex-column justify-content-center align-items-center">
      <button type="button" class="btn-close position-absolute top-0 end-0 m-4" data-bs-toggle="collapse" data-bs-target="#nuevoPost" aria-label="Cerrar"></button>
      <div class="mb-3 w-75">
        <textarea name="texto" class="form-control" rows="6" placeholder="¿Qué quieres compartir?" required></textarea>
      </div>
      <div class="mb-3 w-75">
        <label class="form-label">Foto (opcional):</label>
        <input type="file" name="foto" accept="image/*" class="form-control">
      </div>
      <div class="mb-3 w-75">
        <label class="form-label">Vídeo (opcional):</label>
        <input type="file" name="video" accept="video/*" class="form-control">
      </div>
      <div class="text-end w-75">
        <button type="submit" class="btn btn-success w-100">Publicar</button>
      </div>
    </form>
  </div>
</div>
<div class="container my-5 post-card-container">
  <?php foreach ($posts as $post): ?>
    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <!-- Nombre arriba a la izquierda -->
        <div class="d-flex align-items-center mb-2">
          <strong><?= htmlspecialchars($post['usuario']['nombre'] ?? 'Anónimo') ?></strong>
          <span class="text-muted ms-2" style="font-size:0.9em;">
            <?= isset($post['fecha']) ? $post['fecha']->toDateTime()->format('d/m/Y H:i') : '' ?>
          </span>
        </div>
        <!-- Texto -->
        <p class="mb-3"><?= nl2br(htmlspecialchars($post['texto'] ?? '')) ?></p>
        <!-- Foto más pequeña -->
        <?php if (!empty($post['foto'])): ?>
          <img src="<?= htmlspecialchars($post['foto']) ?>" alt="Foto del post" class="post-card-img rounded">
        <?php endif; ?>
        <!-- Vídeo -->
        <?php if (!empty($post['video'])): ?>
          <video controls autoplay class="w-100 rounded mb-2">
            <source  src="<?= htmlspecialchars($post['video']) ?>">
            Tu navegador no soporta el video.
          </video>
        <?php endif; ?>
        <?php if (!empty($post['comentarios'])): ?>
  <div class="mb-2 ps-2">
    <?php foreach ($post['comentarios'] as $coment): ?>
      <div class="border-start ps-2 mb-1">
        <strong style="font-size:0.95em;"><?= htmlspecialchars($coment['usuario']) ?></strong>
        <span class="text-muted" style="font-size:0.8em;">
          <?= isset($coment['fecha']) ? $coment['fecha']->toDateTime()->format('d/m/Y H:i') : '' ?>
        </span>
        <div><?= nl2br(htmlspecialchars($coment['texto'])) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

<?php endif; ?>

<!-- Contenedor flex para comentario y like en línea -->
<div class="d-flex align-items-center gap-2 mt-2">
  <?php if (isset($usuario['nombre'])): ?>
    <!-- Formulario para añadir comentario -->
    <form action="comunidad.php" method="POST" class="flex-grow-1 d-flex gap-2 m-0">
        <input type="hidden" name="post_id" value="<?= $post['_id'] ?>">
        <input type="text" name="comentario" class="form-control" placeholder="Escribe un comentario..." required maxlength="200">
        <button type="submit" class="btn btn-outline-primary">Comentar</button>
    </form>
    <!-- Formulario para dar like -->
    <form action="comunidad.php" method="POST" class="m-0">
        <input type="hidden" name="like_post_id" value="<?= $post['_id'] ?>">
        <?php
            $likesArray = isset($post['likes']) ? (array)$post['likes'] : [];
            $yaLike = in_array($usuario['nombre'], $likesArray);
            $numLikes = count($likesArray);
        ?>
        <button type="submit" class="btn btn-link text-decoration-none p-0" style="font-size:1.7em; color:<?= $yaLike ? 'red' : '#aaa' ?>;">
            <?= $yaLike ? '❤️' : '🤍' ?>
        </button>
        <span style="font-size:1.1em;"><?= $numLikes ?></span>
    </form>
  <?php else: ?>
    <!-- Si no está logueado, solo muestra el input de comentario deshabilitado y el like gris -->
    <input type="text" class="form-control" placeholder="Inicia sesión para comentar" disabled style="max-width:300px;">
    <?php
        $likesArray = isset($post['likes']) ? (array)$post['likes'] : [];
        $numLikes = count($likesArray);
    ?>
    <button type="button" class="btn btn-link text-decoration-none p-0" style="font-size:1.7em; color:#aaa;" disabled>
        🤍
    </button>
    <span style="font-size:1.1em;"><?= $numLikes ?></span>
  <?php endif; ?>
</div>
      </div>
    </div>
  <?php endforeach; ?>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
