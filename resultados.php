<?php
session_start();
require 'vendor/autoload.php';
use MongoDB\Client;
$uri = "mongodb+srv://joelnp:joel16@cluster0.qcsid.mongodb.net/?retryWrites=true&w=majority";
$client = new Client($uri);
$database = $client->selectDatabase('PFDJoel');
$partidas = $database->selectCollection('partidas');

$resultados = [];
$id = $_POST['id_partida'] ?? $_GET['id_partida'] ?? null;
if ($id) {
    $partida = $partidas->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
    // Calcula resultados si es necesario
    $resultados = [];
    foreach ($partida['jugadores'] as $jugador) {
        $total = 0;
        foreach ($jugador['hoyos'] as $hoyo) {
            $total += intval($hoyo['puntuacion']);
        }
        $resultados[] = [
            'nombre' => $jugador['nombre'],
            'total' => $total
        ];
    }
    usort($resultados, fn($a, $b) => $a['total'] <=> $b['total']);
} else {
    // Si vienes de una partida recién jugada
    $resultados = $_SESSION['resultados_partida'] ?? [];
    $partida = $_SESSION['partida_reciente'] ?? null;
}

if (!$resultados) {
    echo "No hay resultados para mostrar.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de la Partida</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&family=Special+Gothic+Expanded+One&display=swap"
    rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/resultados.css">
</head>
<body class="bg-light">
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
              <a class="nav-link enlace " href="partida.php">PARTIDA</a>
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
              <a class="nav-link enlace-icono enlace-destacado" href="reservar.php">
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
<div class="container my-5">
    <h2 class="mb-4 text-center">🏆 Resultados de la Partida 🏆</h2>
    <div class="podium">
        <?php
        // Podio: 2º, 1º, 3º (si hay suficientes jugadores)
        $segundo = $resultados[1] ?? null;
        $tercero = $resultados[2] ?? null;
        ?>
        <?php if ($segundo): ?>
        <div class="podium-pos">
            <div class="pos-num">2º</div>
            <div class="podium-2"><?= htmlspecialchars($segundo['nombre']) ?><br><span style="font-size:0.9em;">Puntos: <?= $segundo['total'] ?></span></div>
        </div>
        <?php endif; ?>
        <div class="podium-pos">
            <div class="pos-num">1º</div>
            <div class="podium-1"><?= htmlspecialchars($resultados[0]['nombre']) ?><br><span style="font-size:1em;">Puntos: <?= $resultados[0]['total'] ?></span></div>
        </div>
        <?php if ($tercero): ?>
        <div class="podium-pos">
            <div class="pos-num">3º</div>
            <div class="podium-3"><?= htmlspecialchars($tercero['nombre']) ?><br><span style="font-size:0.9em;">Puntos: <?= $tercero['total'] ?></span></div>
        </div>
        <?php endif; ?>
    </div>
    <h4 class="mb-3">Clasificación completa:</h4>
    <table class="table table-bordered text-center">
        <thead class="table-success">
            <tr>
                <th>Posición</th>
                <th>Jugador</th>
                <th>Puntuación total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultados as $i => $jugador): ?>
                <tr<?= $i === 0 ? ' style="font-weight:bold;background:#f8c467;"' : '' ?>>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($jugador['nombre']) ?></td>
                    <td><?= $jugador['total'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php if (!empty($partida['media'])): ?>
    <div class="text-center mb-4">
        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#imagenesCollapse" aria-expanded="false" aria-controls="imagenesCollapse">
            Ver imágenes
        </button>
    </div>
    <div class="collapse mb-4" id="imagenesCollapse">
        <div class="card card-body">
            <div class="row">
    <?php foreach ($partida['media'] as $media): ?>
        <?php
            $ext = strtolower(pathinfo($media, PATHINFO_EXTENSION));
            $isVideo = in_array($ext, ['mp4', 'webm', 'ogg']);
        ?>
        <div class="col-md-4 mb-3 text-center">
            <?php if ($isVideo): ?>
                <video class="mini-vid rounded shadow-sm img-click"
                       style="cursor:pointer"
                       data-bs-toggle="modal"
                       data-bs-target="#modalImagen"
                       data-media="<?= htmlspecialchars($media) ?>"
                       data-type="video"
                       src="<?= htmlspecialchars($media) ?>"
                      controls
                       muted
                       preload="metadata"
                       width="420"
                       height="420"></video>
            <?php else: ?>
                <img src="<?= htmlspecialchars($media) ?>"
                     class="mini-img rounded shadow-sm img-click"
                     alt="Imagen de la partida"
                     style="cursor:pointer"
                     data-bs-toggle="modal"
                     data-bs-target="#modalImagen"
                     data-media="<?= htmlspecialchars($media) ?>"
                     data-type="image">
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
        </div>
    </div>

    <!-- Modal para mostrar la imagen grande -->
    <div class="modal fade" id="modalImagen" tabindex="-1" aria-labelledby="modalImagenLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center" id="modalMediaBody">
        <!-- Aquí se insertará la imagen o el video -->
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
    <div class="text-center mt-4">
        <a href="partida.php" class="btn btn-success">Volver a los campos</a>
    </div>
</div>
<script>
// Cambia la imagen o video del modal al hacer click en una miniatura
document.addEventListener('DOMContentLoaded', function() {
    var modalBody = document.getElementById('modalMediaBody');
    var imgs = document.querySelectorAll('.img-click');
    imgs.forEach(function(img) {
        img.addEventListener('click', function() {
            var src = this.getAttribute('data-media');
            var type = this.getAttribute('data-type');
            if (type === 'video') {
                modalBody.innerHTML = '<video src="' + src + '" controls autoplay class="img-fluid rounded" style="max-height:70vh;"></video>';
            } else {
                modalBody.innerHTML = '<img src="' + src + '" class="img-fluid rounded" style="max-height:70vh; min-width:80%;" alt="Imagen grande">';
            }
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
unset($_SESSION['resultados_partida']);
?>