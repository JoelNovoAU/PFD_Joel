<?php
session_start();
require 'vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

$uri = "mongodb+srv://joelnp:joel16@cluster0.qcsid.mongodb.net/?retryWrites=true&w=majority";

try {
    $client = new Client($uri);
    $database = $client->selectDatabase('PFDJoel');
    $collection = $database->selectCollection('campos');

    if (!isset($_GET['id'])) {
        echo "ID de campo no especificado.";
        exit;
    }

    $id = $_GET['id'];
    $campo = $collection->findOne(['_id' => new ObjectId($id)]);

    if (!$campo) {
        echo "Campo no encontrado.";
        exit;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
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
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&family=Special+Gothic+Expanded+One&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/campoIndiv.css">
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
            <a class="nav-link enlace enlace-destacado" href="partida.php">PARTIDA</a>
          </li>
          <li class="nav-item">
            <a class="nav-link enlace  " href="comunidad.php">COMUNIDAD</a>
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
<div class="contenedor-imagen-texto">
  <img src="<?php echo htmlspecialchars($campo['img']); ?>" alt="Imagen del campo" class="imagen">
  <div class="texto-superpuesto">
    <h1><?php echo htmlspecialchars($campo['nombre']); ?></h1>
    <p id="textoimg"><?php echo nl2br(htmlspecialchars($campo['desc'])); ?></p>
  </div>
</div>


  <?php if (!empty($campo['hoyos'])): ?>
    <h3 id="listahoyos">Hoyos:</h3>
    <div class="hoyos-seleccionables">
    <?php foreach ($campo['hoyos'] as $index => $hoyo): ?>
      <div class="hoyo-circulo"><?php echo htmlspecialchars($hoyo['nombre']); ?></div>

    <?php endforeach; ?>
  </div>

  <!-- Contenedores con info, uno por hoyo -->
  <?php foreach ($campo['hoyos'] as $hoyo): ?>
    <div class="hoyo-info">
      <h3><?php echo htmlspecialchars($hoyo['nombre']); ?></h3>
    <p><?php echo htmlspecialchars($hoyo['descripcion']); ?></p>
      <?php if (!empty($hoyo['imagenes'])): ?>
        <?php foreach ($hoyo['imagenes'] as $img): ?>
          <img src="<?php echo htmlspecialchars($img); ?>" class="hoyo-imagen" onclick="abrirModal(this.src)" alt="Imagen <?php echo htmlspecialchars($hoyo['nombre']); ?>">
        <?php endforeach; ?>
      <?php else: ?>
        <p><em>Sin imágenes</em></p>
      <?php endif; ?>

    </div>
  <?php endforeach; ?>
<?php endif; ?>
<a href="reservar.php?campo=<?= urlencode($campo['nombre']) ?>" class="btn btn-success">Reservar</a>
  <a href="partida.php" class="btn btn-secondary mt-4">← Volver</a>


<div class="modal fade" id="imagenModal" tabindex="-1" aria-labelledby="imagenModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-body p-0">
        <img src="" id="modalImagen" class="img-fluid w-100" alt="Imagen ampliada" />
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<script>
    const modal = new bootstrap.Modal(document.getElementById('imagenModal'));
    const modalImagen = document.getElementById('modalImagen');

    function abrirModal(src) {
        modalImagen.src = src;
        modal.show();
    }
</script>
<script>
  // Seleccionamos todos los círculos y las info de hoyos
  const hoyosCirculos = document.querySelectorAll('.hoyo-circulo');
  const hoyosInfo = document.querySelectorAll('.hoyo-info');

  hoyosCirculos.forEach((circulo, index) => {
    circulo.addEventListener('click', () => {
      // Quitamos active de todos los círculos y contenedores info
      hoyosCirculos.forEach(c => c.classList.remove('active'));
      hoyosInfo.forEach(i => i.classList.remove('active'));

      // Activamos el círculo y la info correspondiente
      circulo.classList.add('active');
      hoyosInfo[index].classList.add('active');
    });
  });
</script>

</body>
</html>
