<?php
require_once __DIR__ . '/vendor/autoload.php'; // Autoload de Composer

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
                'correo' => $correo
            ];
            $collection->insertOne($reserva);

            // Generar código QR
          $qrData = "Reserva:\nNombre: $nombre\nFecha: $fecha\nHora: $hora";
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
                    <p>Adjunto encontrarás un código QR con los datos de tu reserva.</p>
                    <p>Nos pondremos en contacto si hay algún cambio.</p>
                    <br>
                    <p>Saludos,<br>Equipo de Reservas</p>
                ";

                $mail->send();
                echo '✅ Reserva confirmada. Revisa tu correo.';
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

</head>

 <div class="container mt-5">
        <h2>Reservar una pista</h2>

        <form action="" method="POST" class="p-4 border bg-white rounded">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre:</label>
                <input type="text" name="nombre" id="nombre" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha:</label>
                <input type="date" name="fecha" id="fecha" class="form-control" 
                    min="<?= $hoy->format('Y-m-d') ?>" max="<?= $fechaLimite->format('Y-m-d') ?>" required>
            </div>

           <div class="mb-3">
    <label for="hora" class="form-label">Hora:</label>
    <div id="hora-container" class="d-flex flex-wrap gap-2"></div>
    <input type="hidden" name="hora" id="hora" required>
</div>


            <div class="mb-3">
                <label for="correo" class="form-label">Correo electrónico:</label>
                <input type="email" name="correo" id="correo" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Reservar</button>
        </form>
    </div>

   
<script>
document.getElementById('fecha').addEventListener('change', function() {
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
                    btn.className = 'btn btn-outline-primary rounded-pill';
                    btn.textContent = hora;

                    btn.addEventListener('click', () => {
                        // Quitar selección previa
                        document.querySelectorAll('#hora-container button').forEach(b => b.classList.remove('active'));
                        // Marcar seleccionada
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