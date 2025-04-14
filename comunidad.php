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
<header class="m-0 p-0">
  <nav class="navbar navbar-expand-md w-100 py-3">
    <div class="container-fluid px-4">

      <!-- LOGO IZQUIERDA -->
      <a class="navbar-brand" href="index.html">
        <img id="heaimg3" src="img/logo - copia.png" alt="Logo HardGz" height="60">
      </a>

      <!-- Botón Collapse -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapseContent" aria-controls="navbarCollapseContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- TODO A LA DERECHA -->
      <div class="collapse navbar-collapse ms-auto justify-content-end" id="navbarCollapseContent">

        <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
          
          <!-- ENLACES -->
          <ul class="navbar-nav d-flex gap-5">
            <li class="nav-item">
              <a id="enlaces" class="nav-link px-3" href="partida.php">PARTIDA</a>
            </li>
            <li class="nav-item">
              <a id="enlaces3" class="nav-link px-3" href="comunidad.php">COMUNIDAD</a>
            </li>
            <li class="nav-item">
              <a id="enlaces" class="nav-link px-3" href="contacto.php">CONTACTO</a>
            </li>
          </ul>

          <!-- ICONOS -->
          <div class="iconos d-flex align-items-center gap-5">
            <a class="nav-link" href="mapa2.html">
              <img id="heaimg1" src="img/marcador.png" alt="Localiza tu campo" height="30"> LOCALIZA TU CAMPO
            </a>
            <a class="nav-link" href="login.html">
              <img id="heaimg1" src="img/reserva.png" alt="reserva pista" height="30"> RESERVA PISTA  
            </a>
            <a class="nav-link" href="login.html">
              <img id="heaimg1" src="img/usuario.png" alt="Usuario" height="30">
            </a>
          </div>

        </div>

      </div>
    </div>
  </nav>
</header>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>