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
        $usuarioDB = $collectionUsuarios->findOne(['nombre' => $usuario]);
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
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&family=Special+Gothic+Expanded+One&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css">
    <title>Document</title>
</head>
<body>
  <div id="redes">
  <div class="info-contacto">
    <img src="img/llamar.png" alt="Teléfono" class="icono-red">
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
            <a class="nav-link enlace-icono" href="login.html">
             <!-- <img src="img/usuario.png" alt="Usuario">-->MI CUENTA
            </a>
          </li>

        </ul>
      </div>

    </div>
  </nav>
</header>
<?php if ($esAdmin): ?>
        <a href="añadirCampo.php">Añadir Campo</a>
<?php endif; ?>
<div class="container mt-5">
    <h1>Campos Disponibles</h1>

    <?php if (empty($campos)): ?>
        <p>No hay campos para mostrar.</p>
    <?php else: ?>
        <?php foreach ($campos as $campo): ?>
            <div class="mb-5 border rounded p-4 shadow-sm">
                <h2><?php echo htmlspecialchars($campo['nombre']); ?></h2>
                <p><strong>Distancia total:</strong> <?php echo htmlspecialchars($campo['distancia']); ?> m</p>
                <p><?php echo nl2br(htmlspecialchars($campo['desc'])); ?></p>

                <?php if (!empty($campo['img'])): ?>
                    <img src="<?php echo htmlspecialchars($campo['img']); ?>" alt="Imagen de <?php echo htmlspecialchars($campo['nombre']); ?>" class="campo-img img-fluid" onclick="abrirModal(this.src)" />
                <?php endif; ?>

                <?php if (!empty($campo['hoyos'])): ?>
                    <h4>Hoyos:</h4>
                    <div class="row">
                        <?php foreach ($campo['hoyos'] as $hoyo): ?>
                            <div class="col-md-4 hoyo-container">
                                <h5><?php echo htmlspecialchars($hoyo['nombre']); ?></h5>
                                <?php if (!empty($hoyo['imagenes'])): ?>
                                    <div class="d-flex flex-wrap">
                                        <?php foreach ($hoyo['imagenes'] as $imgHoyo): ?>
                                            <img src="<?php echo htmlspecialchars($imgHoyo); ?>" alt="Imagen <?php echo htmlspecialchars($hoyo['nombre']); ?>" class="hoyo-imagen" onclick="abrirModal(this.src)" />
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p><em>Sin imágenes</em></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p><em>No hay hoyos registrados para este campo.</em></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const modal = new bootstrap.Modal(document.getElementById('imagenModal'));
    const modalImagen = document.getElementById('modalImagen');

    function abrirModal(src) {
        modalImagen.src = src;
        modal.show();
    }
</script>
</body>
</html>
