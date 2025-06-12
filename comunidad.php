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
    <style>
   
        
    </style>
</head>
<body>
  <div id="redes">
  <div class="info-contacto">
    <img src="img/correo-electronico.png" alt="Teléfono" class="icono-red">
    <span>info@novogolf.com</span>
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
            <a class="nav-link enlace " href="partida.php">PARTIDA</a>
          </li>
          <li class="nav-item">
            <a class="nav-link enlace enlace-destacado " href="comunidad.php">COMUNIDAD</a>
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
            <a class="nav-link enlace-icono " href="reservar.php">
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
    class="btn btn-primary rounded-circle shadow fab-btn"
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
  <div class="fullscreen-form d-flex justify-content-center align-items-center">
    <form action="comunidad.php" method="POST" enctype="multipart/form-data" class="post-modal-form shadow-lg rounded-4 p-4 bg-white">
      <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-toggle="collapse" data-bs-target="#nuevoPost" aria-label="Cerrar"></button>
      <h3 class="mb-4 text-center fw-bold" style="letter-spacing:1px;">Nueva publicación</h3>
      <div class="mb-3">
        <textarea name="texto" class="form-control form-control-lg rounded-3" rows="5" placeholder="¿Qué quieres compartir?" maxlength="500" required></textarea>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
          <label class="form-label fw-semibold">Foto (opcional):</label>
          <input type="file" name="foto" accept="image/*" class="form-control rounded-3" id="inputFoto">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label fw-semibold">Vídeo (opcional):</label>
          <input type="file" name="video" accept="video/*" class="form-control rounded-3" id="inputVideo">
        </div>
      </div>
    
      <div class="d-grid">
        <button type="submit" class="btn btn-success btn-lg rounded-3">Publicar</button>
      </div>
    </form>
  </div>
</div>
<div class="container my-5 post-card-container">
  <?php foreach ($posts as $post): ?>
    <div class="pub-container mb-4">
     
      <a href="publicacionInd.php?id=<?= $post['_id'] ?>" style="text-decoration:none; color:inherit; display:block;">
        <div class="pub-header">
          <span class="pub-user"><?= htmlspecialchars($post['usuario']['nombre'] ?? 'Anónimo') ?></span>
          <span class="pub-date">
 <?php
    if (isset($post['fecha']) && $post['fecha'] instanceof MongoDB\BSON\UTCDateTime) {
        $dt = $post['fecha']->toDateTime();
        $dt->setTimezone(new DateTimeZone('Europe/Madrid'));
        echo $dt->format('d/m/Y H:i');
    }
  ?>          </span>
        </div>
        <div class="pub-text"><?= nl2br(htmlspecialchars($post['texto'] ?? '')) ?></div>
        <?php if (!empty($post['foto'])): ?>
          <img src="<?= htmlspecialchars($post['foto']) ?>" alt="Foto del post" class="pub-img">
        <?php endif; ?>
        <?php if (!empty($post['video'])): ?>
          <video controls muted autoplay class="pub-video">
            <source src="<?= htmlspecialchars($post['video']) ?>">
            Tu navegador no soporta el video.
          </video>
        <?php endif; ?>
      </a>
      <!-- Likes y comentarios resumen -->
      <div class="pub-likes">
        <?php
          $likesArray = isset($post['likes']) ? (array)$post['likes'] : [];
          $yaLike = $usuario ? in_array($usuario['nombre'], $likesArray) : false;
          $numLikes = count($likesArray);
        ?>
        <form action="comunidad.php" method="POST" style="display:inline;">
          <input type="hidden" name="like_post_id" value="<?= $post['_id'] ?>">
          <button type="submit" class="like-btn<?= $yaLike ? ' liked' : '' ?>" <?= $usuario ? '' : 'disabled' ?>>
            <?= $yaLike ? '❤️' : '🤍' ?>
          </button>
        </form>
        <span><?= $numLikes ?></span>
      </div>
      <div class="pub-comments-title">Comentarios</div>
     <?php if (!empty($post['comentarios'])): ?>
  <?php
    // Mostrar solo los dos últimos comentarios
$comentarios = array_slice((array)$post['comentarios'], -2);
    foreach ($comentarios as $coment):
  ?>
    <div class="pub-comment">
      <span class="pub-comment-user"><?= htmlspecialchars($coment['usuario']) ?></span>
      <span class="pub-comment-date">
        <?= isset($coment['fecha']) ? $coment['fecha']->toDateTime()->format('d/m/Y H:i') : '' ?>
      </span>
      <div class="pub-comment-text"><?= nl2br(htmlspecialchars($coment['texto'])) ?></div>
    </div>
  <?php endforeach; ?>
<?php else: ?>
  <div class="text-muted">No hay comentarios aún.</div>
<?php endif; ?>
      <!-- Formulario para comentar -->
      <?php if ($usuario): ?>
        <form action="comunidad.php" method="POST" class="pub-comment-form">
          <input type="hidden" name="post_id" value="<?= $post['_id'] ?>">
          <input type="text" name="comentario" placeholder="Escribe un comentario..." maxlength="200" required>
          <button type="submit">Comentar</button>
        </form>
      <?php else: ?>
        <div class="text-muted" style="margin-top:1em;">Inicia sesión para comentar o dar like.</div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
