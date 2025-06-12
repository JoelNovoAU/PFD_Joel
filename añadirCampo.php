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
    $usuario = $_SESSION['usuario'] ?? null;
    $esAdmin = false;

    if (isset($_SESSION['usuario']['nombre'])) {
    $nombreUsuario = $_SESSION['usuario']['nombre'];
    $usuarioDB = $collectionUsuarios->findOne(['nombre' => $nombreUsuario]);
    
    if ($usuarioDB && isset($usuarioDB['rol']) && $usuarioDB['rol'] === 'admin') {
        $esAdmin = true;
    }
}

    if (!$esAdmin) {
        header("Location: index.html");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $hoyosPost = $_POST['hoyos'] ?? [];

        $rutaImagen = '';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $nombreImagen = uniqid() . '_' . basename($_FILES['imagen']['name']);
            $rutaDestino = 'img/campos/' . $nombreImagen;

            if (!is_dir('img/campos')) {
                mkdir('img/campos', 0777, true);
            }

            move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino);
            $rutaImagen = $rutaDestino;
        }

        $hoyos = [];
        $uploadPathHoyo = 'img/hoyos/';

        foreach ($hoyosPost as $index => $hoyoData) {
            $nombreHoyo = $hoyoData['nombre'] ?? '';
            $descripcionHoyo = $hoyoData['descripcion'] ?? '';
            $imagenesHoyo = [];

            if (isset($_FILES['hoyos']) && isset($_FILES['hoyos']['name'][$index]['imagenes'])) {
                $totalArchivos = count($_FILES['hoyos']['name'][$index]['imagenes']);

                for ($i = 0; $i < $totalArchivos; $i++) {
                    $nombreArchivo = $_FILES['hoyos']['name'][$index]['imagenes'][$i];
                    $tmpArchivo = $_FILES['hoyos']['tmp_name'][$index]['imagenes'][$i];
                    $errorArchivo = $_FILES['hoyos']['error'][$index]['imagenes'][$i];

                    if ($errorArchivo === UPLOAD_ERR_OK && !empty($nombreArchivo)) {
                        if (!is_dir($uploadPathHoyo)) {
                            mkdir($uploadPathHoyo, 0777, true);
                        }

                        $rutaFinal = $uploadPathHoyo . uniqid() . '_' . basename($nombreArchivo);
                        if (move_uploaded_file($tmpArchivo, $rutaFinal)) {
                            $imagenesHoyo[] = $rutaFinal;
                        }
                    }
                }
            }

            $hoyos[] = [
                'nombre' => $nombreHoyo,
                'descripcion' => $descripcionHoyo,
                'imagenes' => $imagenesHoyo
            ];
        }

        $collection->insertOne([
            'nombre' => $nombre,
            'desc' => $descripcion,
            'img' => $rutaImagen,
            'hoyos' => $hoyos,
        ]);
header("Location: partida.php");

        exit();
    }
} catch (Exception $e) {
    echo "Error al conectar a MongoDB: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear nuevo campo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&family=Special+Gothic+Expanded+One&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/añadirCampo.css">
    
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
<div class="container mt-5">
    <h1 class="mb-4"><i class="fa-solid fa-golf-ball-tee"></i> Crear nuevo campo</h1>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nombre" class="form-label"><i class="fa-solid fa-flag"></i> Nombre del campo</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="60" placeholder="Ej: Campo NovoGolf">
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label"><i class="fa-solid fa-align-left"></i> Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Describe brevemente el campo..."></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label"><i class="fa-solid fa-golf-club"></i> Hoyos</label>
            <div class="info-text">Puedes añadir tantos hoyos como quieras. Cada hoyo puede tener varias imágenes.</div>
            <div id="hoyosContainer">
                <div class="hoyo-item border p-3 rounded position-relative">
                    <span class="hoyo-title"><i class="fa-solid fa-circle"></i> Hoyo 1</span>
                    <button type="button" class="btn remove-hoyo" title="Eliminar hoyo"><i class="fa fa-trash"></i></button>
                    <input type="text" name="hoyos[0][nombre]" class="form-control mb-2" placeholder="Ej: 120m Par 3" required maxlength="40">
                    <label>Descripción del hoyo</label>
                    <textarea name="hoyos[0][descripcion]" class="form-control mb-2" placeholder="Descripción del hoyo..."></textarea>
                    <label>Imágenes del hoyo</label>
                    <input type="file" name="hoyos[0][imagenes][]" multiple accept="image/*" class="form-control mb-2">
                </div>
            </div>
            <button type="button" id="addHoyo" class="btn btn-secondary mt-2"><i class="fa fa-plus"></i> Añadir otro hoyo</button>
        </div>

        <div class="mb-3">
            <label for="imagen" class="form-label"><i class="fa-solid fa-image"></i> Imagen principal del campo</label>
            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" required>
        </div>

        <button type="submit" class="btn btn-success w-100 mt-3"><i class="fa-solid fa-floppy-disk"></i> Guardar Campo</button>
    </form>
</div>


<script>
    let hoyoIndex = 1;

   document.getElementById('addHoyo').addEventListener('click', function () {
    const container = document.getElementById('hoyosContainer');
    const div = document.createElement('div');
    div.classList.add('hoyo-item', 'mb-3', 'border', 'p-3', 'rounded');
    div.innerHTML = `
        <span class="hoyo-title"><i class="fa-solid fa-circle"></i> Hoyo ${hoyoIndex + 1}</span>
        <input type="text" name="hoyos[${hoyoIndex}][nombre]" class="form-control mb-2" placeholder="Hoyo ${hoyoIndex + 1} - distancia" required>
        <label>Descripción del hoyo</label>
        <textarea name="hoyos[${hoyoIndex}][descripcion]" class="form-control mb-2" placeholder="Descripción del hoyo..."></textarea>
        <label>Imágenes del hoyo</label>
        <input type="file" name="hoyos[${hoyoIndex}][imagenes][]" multiple accept="image/*" class="form-control mb-2">
        <div class="hoyo-actions">
            <button type="button" class="btn btn-danger remove-hoyo">
                <i class="fa fa-trash"></i> Eliminar
            </button>
        </div>
    `;
    container.appendChild(div);
    hoyoIndex++;
});

    document.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('remove-hoyo')) {
            e.target.closest('.hoyo-item').remove();
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
