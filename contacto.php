<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contacto</title>
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

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapseContent" aria-controls="navbarCollapseContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-end" id="navbarCollapseContent">
        <ul class="navbar-nav d-flex  flex-md-row align-items-md-center">

          <li class="nav-item">
            <a class="nav-link enlace" href="partida.php">PARTIDA</a>
          </li>
          <li class="nav-item">
            <a class="nav-link enlace " href="comunidad.php">COMUNIDAD</a>
          </li>
          <li class="nav-item">
            <a class="nav-link enlace enlace-destacado" href="contacto.php">CONTACTO</a>
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
<div id="headercont">
    <img id="imgcont" src="img/golf-6561026_1920.jpg" alt="Campo de golf">
    <div class="overlay-text">  Contáctanos y haz hoy el mejor approach.</div>
    <h2>Contáctanos</h2>
  </div>

  <p class="subcontacto">¿Tienes dudas, problemas al reservar o simplemente comunicarnos? Estamos aquí para ayudarte.</p>

  <div id="division">
<div class="datos-contacto">
    <p><strong>Correo:</strong> contacto@golfclub.com</p>
    <p><strong>Teléfono:</strong> +34 600 123 456</p>
  </div>

  <form id="formcontacto">
    <h2>Formulario contacto</h2>
    <input type="text" name="nombre" placeholder="Tu nombre" required>
    <input type="email" name="email" placeholder="Tu correo electrónico" required>
    <textarea name="mensaje" rows="5" placeholder="Escribe tu mensaje aquí..." required></textarea>
    <button type="submit">Enviar mensaje</button>
  </form>

  
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
