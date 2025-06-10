<?php
require 'vendor/autoload.php';
use MongoDB\Client;
session_start();

$client = new Client("mongodb+srv://joelnp:joel16@cluster0.qcsid.mongodb.net/?retryWrites=true&w=majority");
$database = $client->selectDatabase('PFDJoel');
$collection = $database->selectCollection('comunidad');

// Obtener el id de la publicación
$id = $_GET['id'] ?? null;
$post = null;
$msg = "";
$usuario = $_SESSION['usuario'] ?? null;

// Procesar nuevo comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_comentario']) && $usuario && $id) {
    $nuevoComentario = trim($_POST['nuevo_comentario']);
    if ($nuevoComentario !== "") {
        $comentario = [
            'usuario' => $usuario['nombre'],
            'texto' => $nuevoComentario,
            'fecha' => new MongoDB\BSON\UTCDateTime()
        ];
        $collection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($id)],
            ['$push' => ['comentarios' => $comentario]]
        );
        $msg = "<div class='pub-msg pub-success'>Comentario publicado.</div>";
    }
}

// Procesar like
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_post_id']) && $usuario && $id) {
    $postId = $_POST['like_post_id'];
    $postObj = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($postId)]);
    $likesArray = isset($postObj['likes']) ? (array)$postObj['likes'] : [];
    $yaLike = in_array($usuario['nombre'], $likesArray);

    if ($yaLike) {
        // Quitar like
        $collection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($postId)],
            ['$pull' => ['likes' => $usuario['nombre']]]
        );
    } else {
        // Dar like
        $collection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($postId)],
            ['$push' => ['likes' => $usuario['nombre']]]
        );
    }
    // Mensaje opcional
    $msg = "<div class='pub-msg pub-success'>¡Gracias por tu reacción!</div>";
}

// Recargar post actualizado
if ($id) {
    $post = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Publicación</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&family=Special+Gothic+Expanded+One&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css">
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

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapseContent"
          aria-controls="navbarCollapseContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarCollapseContent">
          <ul class="navbar-nav d-flex  flex-md-row align-items-md-center">

            <li class="nav-item">
              <a class="nav-link enlace  " href="partida.php">PARTIDA</a>
            </li>
            <li class="nav-item">
              <a class="nav-link enlace " href="comunidad.php">COMUNIDAD</a>
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
              <a class="nav-link enlace-icono enlace-destacado"  href="perfil.php">
                <!-- <img src="img/usuario.png" alt="Usuario">-->MI CUENTA
              </a>
            </li>

          </ul>
        </div>

      </div>
    </nav>
  </header>
<div class="pub-container">
    <a href="comunidad.php" class="pub-back">&larr; Volver a la comunidad</a>
    <?= $msg ?>
    <?php if ($post): ?>
        <div class="pub-header">
            <span class="pub-user"><?= htmlspecialchars($post['usuario']['nombre'] ?? 'Anónimo') ?></span>
            <span class="pub-date">
                <?= isset($post['fecha']) ? $post['fecha']->toDateTime()->format('d/m/Y H:i') : '' ?>
            </span>
        </div>
        <div class="pub-text"><?= nl2br(htmlspecialchars($post['texto'] ?? '')) ?></div>
        <?php if (!empty($post['foto'])): ?>
            <img src="<?= htmlspecialchars($post['foto']) ?>" alt="Imagen de la publicación" class="pub-img">
        <?php endif; ?>
        <?php if (!empty($post['video'])): ?>
            <video controls class="pub-video">
                <source src="<?= htmlspecialchars($post['video']) ?>">
                Tu navegador no soporta el video.
            </video>
        <?php endif; ?>
        <!-- Likes -->
        <div class="pub-likes">
            <?php
                $likesArray = isset($post['likes']) ? (array)$post['likes'] : [];
                $yaLike = $usuario ? in_array($usuario['nombre'], $likesArray) : false;
                $numLikes = count($likesArray);
            ?>
            <form method="post" style="display:inline;">
                <input type="hidden" name="like_post_id" value="<?= $post['_id'] ?>">
                <button type="submit" class="like-btn<?= $yaLike ? ' liked' : '' ?>" <?= $usuario ? '' : 'disabled' ?>>
                    <?= $yaLike ? '❤️' : '🤍' ?>
                </button>
            </form>
            <span><?= $numLikes ?></span>
        </div>
        <!-- Comentarios -->
        <div class="pub-comments-title">Comentarios</div>
        <?php if (!empty($post['comentarios'])): ?>
            <?php foreach ($post['comentarios'] as $coment): ?>
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
            <form method="post" class="pub-comment-form">
                <input type="text" name="nuevo_comentario" placeholder="Escribe un comentario..." maxlength="300" required>
                <button type="submit">Comentar</button>
            </form>
        <?php else: ?>
            <div class="text-muted" style="margin-top:1em;">Inicia sesión para comentar o dar like.</div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-danger">Publicación no encontrada.</div>
    <?php endif; ?>
</div>
</body>
</html>