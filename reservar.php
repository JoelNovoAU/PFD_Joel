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
<style>
  
    .calendar {
      width: 320px;
      border: 1px solid #ccc;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
   
    .days {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      text-align: center;
      padding: 10px;
    }
    .day-name, .day {
      padding: 10px;
    }
    .day-name {
      font-weight: bold;
      background-color: #f0f0f0;
    }
    .day {
      cursor: pointer;
      border-radius: 4px;
    }
    .day:hover {
      background-color: #e0e0e0;
    }
    .selected {
      background-color: #007bff;
      color: white;
    }
    .nav-button {
      cursor: pointer;
      background: none;
      border: none;
      font-size: 18px;
      color: white;
    }
    .pistas {
      width: 320px;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 10px;
      background: #f9f9f9;
    }
    .pista {
      padding: 10px;
      background-color: #dff0d8;
      border: 1px solid #c3e6cb;
      border-radius: 4px;
      margin: 5px 0;
    }
  </style>
</head>
<body>

<div class="calendar">
  <div class="header">
    <button class="nav-button" onclick="changeMonth(-1)">❮</button>
    <div id="monthYear"></div>
    <button class="nav-button" onclick="changeMonth(1)">❯</button>
  </div>
  <div class="days" id="dayNames"></div>
  <div class="days" id="days"></div>
</div>

<div class="pistas" id="pistas">
  <strong>Selecciona un día para ver pistas disponibles.</strong>
</div>

<script>
  const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
  const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                      'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

  const dayNamesContainer = document.getElementById('dayNames');
  const daysContainer = document.getElementById('days');
  const monthYear = document.getElementById('monthYear');
  const pistasContainer = document.getElementById('pistas');

  let currentDate = new Date();

  // Simulación de disponibilidad
  const pistasData = {
    // formato: 'YYYY-MM-DD': ['Pista 1', 'Pista 2']
    '2025-05-05': ['Pista 1', 'Pista 3'],
    '2025-05-06': ['Pista 2'],
    '2025-05-07': ['Pista 1', 'Pista 2', 'Pista 3'],
  };

  function renderCalendar(date) {
    daysContainer.innerHTML = '';
    monthYear.textContent = `${monthNames[date.getMonth()]} ${date.getFullYear()}`;

    const firstDay = new Date(date.getFullYear(), date.getMonth(), 1).getDay();
    const daysInMonth = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();

    // Espacios en blanco
    for (let i = 0; i < firstDay; i++) {
      daysContainer.innerHTML += `<div></div>`;
    }

    // Días
    for (let i = 1; i <= daysInMonth; i++) {
      const dayDiv = document.createElement('div');
      dayDiv.classList.add('day');
      dayDiv.textContent = i;
      const fullDate = `${date.getFullYear()}-${(date.getMonth()+1).toString().padStart(2,'0')}-${i.toString().padStart(2,'0')}`;
      dayDiv.addEventListener('click', () => {
        document.querySelectorAll('.day').forEach(d => d.classList.remove('selected'));
        dayDiv.classList.add('selected');
        showPistas(fullDate);
      });
      daysContainer.appendChild(dayDiv);
    }
  }

  function changeMonth(change) {
    currentDate.setMonth(currentDate.getMonth() + change);
    renderCalendar(currentDate);
  }

  function showPistas(dateStr) {
    const pistas = pistasData[dateStr] || [];
    if (pistas.length > 0) {
      pistasContainer.innerHTML = `<strong>Pistas disponibles para el ${dateStr}:</strong>`;
      pistas.forEach(p => {
        const div = document.createElement('div');
        div.classList.add('pista');
        div.textContent = p;
        pistasContainer.appendChild(div);
      });
    } else {
      pistasContainer.innerHTML = `<strong>No hay pistas disponibles para el ${dateStr}.</strong>`;
    }
  }

  // Inicializar nombres de días
  dayNames.forEach(name => {
    const div = document.createElement('div');
    div.classList.add('day-name');
    div.textContent = name;
    dayNamesContainer.appendChild(div);
  });

  renderCalendar(currentDate);
</script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>