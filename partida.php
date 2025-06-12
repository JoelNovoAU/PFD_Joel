<?php
session_start();
require 'vendor/autoload.php';

use MongoDB\Client;

$uri = "mongodb+srv://joelnp:joel16@cluster0.qcsid.mongodb.net/?retryWrites=true&w=majority";

try {
  $client = new Client($uri);
  $database = $client->selectDatabase('PFDJoel');
  $collection = $database->selectCollection('campos');
  $collectionUsuarios = $database->selectCollection('usuarios');

  $campos = $collection->find()->toArray();
  $usuario = $_SESSION['usuario'] ?? null;
  $esAdmin = false;

 if ($usuario) {
  $usuarioDB = $collectionUsuarios->findOne(['nombre' => $usuario['nombre']]);
  if ($usuarioDB && isset($usuarioDB['rol']) && $usuarioDB['rol'] === 'admin') {
    $esAdmin = true;
  }
}

} catch (Exception $e) {
  echo "Error al conectar a MongoDB: " . $e->getMessage();
  exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&family=Special+Gothic+Expanded+One&display=swap"
    rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/estilo.css">
  <title>Document</title>
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

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapseContent"
          aria-controls="navbarCollapseContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarCollapseContent">
          <ul class="navbar-nav d-flex  flex-md-row align-items-md-center">

            <li class="nav-item">
              <a class="nav-link enlace enlace-destacado " href="partida.php">PARTIDA</a>
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
              <a class="nav-link enlace-icono" href="perfil.php">
                <!-- <img src="img/usuario.png" alt="Usuario">-->MI CUENTA
              </a>
            </li>

          </ul>
        </div>

      </div>
    </nav>
  </header>
 

  <div class="container">
    <h1 id="titulocampo" style="text-align: center;">Campos Disponibles</h1>
     <?php if ($esAdmin): ?>
    <a href="añadirCampo.php" class="btn btn-success ms-3">+ Añadir Campo</a>
  <?php endif; ?>
<div class="campos">
  <?php foreach ($campos as $campo): ?>
    <div id="campoindv">
      <div class="campoindi mb-5 border rounded p-4 shadow-sm">
        <p style="text-align: center;"><?php echo nl2br(htmlspecialchars($campo['nombre'])); ?></p>

        <?php if (!empty($campo['img'])): ?>
          <img src="<?php echo htmlspecialchars($campo['img']); ?>" class="campo-img img-fluid" />
        <?php endif; ?>
<div id="botones">
  <a class="boton-jugar" href="jugarPartida.php?id=<?php echo $campo['_id']; ?>">Jugar Partida</a>
  <a class="boton-info" href="campoIndividual.php?id=<?php echo $campo['_id']; ?>">Explorar Campo</a>
</div>

      </div>
    </div>
  <?php endforeach; ?>
</div>


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>

</html>