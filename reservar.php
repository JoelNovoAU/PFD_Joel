<?php
require_once __DIR__ . '/vendor/autoload.php'; // Autoload de Composer
session_start();

$usuario = $_SESSION['usuario'] ?? null;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use MongoDB\Client;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

// Conexión a MongoDB
$uri = "mongodb+srv://joelnp:joel16@cluster0.qcsid.mongodb.net/?retryWrites=true&w=majority";
$client = new Client($uri);
$database = $client->selectDatabase('PFDJoel');
$collection = $database->selectCollection('reservas');
$collectionCampos = $database->selectCollection('campos');
$campos = $collectionCampos->find()->toArray();
$campoSeleccionado = $_GET['campo'] ?? ($_POST['campo'] ?? '');
// Fechas permitidas
$hoy = new DateTime();
$fechaLimite = clone $hoy;
$fechaLimite->modify('+3 weeks');

// Obtener horas ocupadas vía AJAX
if (isset($_GET['getHoras'])) {
  $fecha = $_GET['getHoras'];
  $reservas = $collection->find(['fecha' => $fecha]);

  $ocupadas = [];
  foreach ($reservas as $reserva) {
    $ocupadas[] = $reserva['hora'];
  }

  header('Content-Type: application/json');
  echo json_encode($ocupadas);
  exit;
}

// Procesamiento de formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $nombre = $_POST['nombre'];
  $fecha = $_POST['fecha'];
  $hora = $_POST['hora'];
  $correo = $_POST['correo'];
  $campo = $_POST['campo'];

  $fechaSeleccionada = new DateTime($fecha);
  if ($fechaSeleccionada < $hoy || $fechaSeleccionada > $fechaLimite) {
    echo "<p style='color:red;'>❌ La fecha seleccionada debe estar dentro de las próximas 3 semanas.</p>";
  } else {
    $reservaExistente = $collection->findOne([
      'fecha' => $fecha,
      'hora' => $hora
    ]);

    if ($reservaExistente) {
      echo "<p style='color:red;'>❌ Ya existe una reserva para el $fecha a las $hora.</p>";
    } else {
      $reserva = [
        'nombre' => $nombre,
        'fecha' => $fecha,
        'hora' => $hora,
        'correo' => $correo,
        'campo' => $campo
      ];
      $collection->insertOne($reserva);

      // Contar reservas del usuario por su correo
      $correo = trim(strtolower($correo));
      $reservasUsuario = $collection->countDocuments(['correo' => $correo]);

      if ($reservasUsuario % 3 == 0) {
        // Generar cupón aleatorio de 8 caracteres (letras y números)
        $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $cupon = '';
        for ($i = 0; $i < 8; $i++) {
          $cupon .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }

        // Puedes guardar el cupón en la base de datos si lo deseas
        // $database->selectCollection('cupones')->insertOne([
        //     'correo' => $correo,
        //     'cupon' => $cupon,
        //     'fecha' => new MongoDB\BSON\UTCDateTime()
        // ]);

        // Enviar correo con el cupón
        $mailCupon = new PHPMailer(true);
        try {
          $mailCupon->isSMTP();
          $mailCupon->Host = 'smtp.gmail.com';
          $mailCupon->SMTPAuth = true;
          $mailCupon->Username = 'jnovopampillon@gmail.com';
          $mailCupon->Password = 'wsmp peuo dony dovc'; // Usa App Password en Gmail
          $mailCupon->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
          $mailCupon->Port = 465;

          $mailCupon->setFrom('jnovopampillon@gmail.com', 'Reservas Web');
          $mailCupon->addAddress($correo);

          $mailCupon->isHTML(true);
          $mailCupon->Subject = '¡Has conseguido un cupón de descuento!';
          $mailCupon->Body = "
            <h3>¡Enhorabuena $nombre!</h3>
            <p>Por haber realizado 3 reservas, aquí tienes tu cupón de descuento:</p>
            <p style='font-size:1.5em; font-weight:bold;'>$cupon</p>
            <p>¡Gracias por confiar en nosotros!</p>
        ";

          $mailCupon->send();
          echo "<div class='alert alert-success text-center mt-4'>
                🎉 ¡Felicidades! Has conseguido un cupón de descuento: 
                <strong style='font-size:1.3em;'>$cupon</strong><br>
                <span>Te lo hemos enviado también a tu correo.</span>
              </div>";
        } catch (Exception $e) {
          echo "<div class='alert alert-danger text-center mt-4'>
                ❌ Error al enviar el cupón: {$mailCupon->ErrorInfo}
              </div>";
        }
      }
      // Generar código QR
      $qrData = "Reserva:\nNombre: $nombre\nFecha: $fecha\nHora: $hora \nCampo: $campo";
      $qrTempPath = sys_get_temp_dir() . '/qr_' . uniqid() . '.png';

      $options = new QROptions([
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'scale' => 6,
      ]);

      // Generar QR y guardar como archivo PNG válido
      $qrDataUri = (new QRCode($options))->render($qrData);
      $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $qrDataUri);
      $pngData = base64_decode($base64);
      file_put_contents($qrTempPath, $pngData);


      // Enviar correo con PHPMailer
      $mail = new PHPMailer(true);

      try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jnovopampillon@gmail.com';
        $mail->Password = 'wsmp peuo dony dovc'; // Usa App Password en Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('jnovopampillon@gmail.com', 'Reservas Web');
        $mail->addAddress($correo);

        $mail->addAttachment($qrTempPath, 'reserva_qr.png');

        $mail->isHTML(true);
        $mail->Subject = 'Confirmacion de tu reserva';
        $mail->Body = "
    <h3>Hola $nombre,</h3>
    <p>Gracias por realizar tu reserva para la fecha <strong>$fecha</strong> a las $hora horas.</p>
    <p><strong>Campo reservado:</strong> $campo</p>
    <p>Adjunto encontrarás un código QR con los datos de tu reserva.</p>
    <p>Nos pondremos en contacto si hay algún cambio.</p>
    <br>
    <p>Saludos,<br>Equipo de NovoGolf Experiencie</p>
                ";  

        $mail->send();
        echo "<script>
  document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('reservaOkModal'));
    modal.show();
  });
</script>";
      } catch (Exception $e) {
        echo "❌ Error al enviar el correo: {$mail->ErrorInfo}";
      } finally {
        // Comenta esta línea mientras verificas el archivo
        // if (file_exists($qrTempPath)) {
        //     unlink($qrTempPath);
        // }
      }
    }
  }
}
?>



<!DOCTYPE html>
<html lang="en">

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
<div id="headercont">
    <img id="imgcont" src="img/golf-1695459.jpg" alt="Campo de golf">
    <div class="overlay-text">    "Reservar aquí es rápido y sencillo, ¡siempre encuentro pista!"<br>
</div>
  </div>

  <div class="container my-5" id="formulario-reserva">
    <h2 class="text-center mb-4" style="color: #798d4e; font-weight: 700;">Reservar una pista</h2>

    <?php if (!$usuario): ?>
      <div class="alert alert-warning text-center" role="alert">
        ¿Ya tienes cuenta? <a href="login.php" class="btn btn-sm btn-primary ms-2">Inicia sesión</a> para rellenar tus
        datos automáticamente.
      </div>
    <?php endif; ?>

    <form action="" method="POST" class="p-4 bg-white rounded shadow-lg" style="max-width: 700px; margin: auto;">
      <div class="mb-3">
        <label for="nombre" class="form-label">Nombre:</label>
        <input type="text" name="nombre" id="nombre" class="form-control"
          value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required>
      </div>

      <div class="mb-3">
        <label for="fecha" class="form-label">Fecha:</label>
        <input type="date" name="fecha" id="fecha" class="form-control" min="<?= $hoy->format('Y-m-d') ?>"
          max="<?= $fechaLimite->format('Y-m-d') ?>" required>
      </div>
      <div class="mb-3">
  <label for="campo" class="form-label">Campo:</label>
  <select name="campo" id="campo" class="form-control" required <?= isset($_GET['campo']) ? 'disabled' : '' ?>>
    <option value="">Selecciona un campo</option>
    <?php foreach ($campos as $campo): ?>
      <option value="<?= htmlspecialchars($campo['nombre']) ?>"
        <?= ($campoSeleccionado === $campo['nombre']) ? 'selected' : '' ?>>
        <?= htmlspecialchars($campo['nombre']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <?php if (isset($_GET['campo'])): ?>
    <input type="hidden" name="campo" value="<?= htmlspecialchars($campoSeleccionado) ?>">
  <?php endif; ?>
</div>
      <div class="mb-3">
        <label for="hora" class="form-label">Hora:</label>
        <div id="hora-container" class="d-flex flex-wrap gap-2"></div>
        <input type="hidden" name="hora" id="hora" required>
      </div>

      <div class="mb-3">
        <label for="correo" class="form-label">Correo electrónico:</label>
        <input type="email" name="correo" id="correo" class="form-control"
          value="<?= htmlspecialchars($usuario['gmail'] ?? '') ?>" required>
      </div>

      <div class="text-center">
        <button type="submit" class="btn btn-success w-100">Reservar</button>
      </div>
    </form>
  </div>

<div class="modal fade" id="reservaOkModal" tabindex="-1" aria-labelledby="reservaOkLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header border-0">
        <h5 class="modal-title w-100" id="reservaOkLabel">¡Reserva realizada!</h5>
      </div>
      <div class="modal-body">
        <p class="fs-4">✅ Tu reserva se ha realizado correctamente.</p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Aceptar</button>
      </div>
    </div>
  </div>
</div>
  <script>
    document.getElementById('fecha').addEventListener('change', function () {
      const fecha = this.value;
      const horaContainer = document.getElementById('hora-container');
      const horaInput = document.getElementById('hora');

      horaInput.value = ''; // Reset selected hour
      horaContainer.innerHTML = ''; // Clear existing buttons

      if (!fecha) return;

      fetch(`reservar.php?getHoras=${fecha}`)
        .then(response => response.json())
        .then(horasOcupadas => {
          const todasHoras = [
            '09:00', '10:00', '11:00', '12:00',
            '13:00', '14:00', '15:00', '16:00',
            '17:00', '18:00', '19:00', '20:00'
          ];

          const horasLibres = todasHoras.filter(h => !horasOcupadas.includes(h));

          if (horasLibres.length === 0) {
            horaContainer.innerHTML = '<p class="text-danger">No hay horas disponibles para esta fecha</p>';
          } else {
            horasLibres.forEach(hora => {
              const btn = document.createElement('button');
              btn.type = 'button';
              btn.className = 'hora-btn';
              btn.textContent = hora;

              btn.addEventListener('click', () => {
                document.querySelectorAll('#hora-container button').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                horaInput.value = hora;
              });

              horaContainer.appendChild(btn);
            });
          }
        })
        .catch(error => {
          console.error('Error al cargar horas:', error);
          horaContainer.innerHTML = '<p class="text-danger">Error al cargar horas</p>';
        });
    });

  </script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
  <style>
    #hora-container button.active {
      background-color: #0d6efd;
      color: white;
      border-color: #0d6efd;
    }
  </style>

</body>

</html>