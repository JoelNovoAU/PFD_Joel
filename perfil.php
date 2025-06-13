<?php
session_start();
require 'vendor/autoload.php';

use MongoDB\Client;

$uri = "mongodb+srv://joelnp:joel16@cluster0.qcsid.mongodb.net/?retryWrites=true&w=majority";

if (!isset($_SESSION['usuario'])) {
  header('Location: login.php');
  exit();
}

$client = new Client($uri);
$database = $client->selectDatabase('PFDJoel');
$usuarios = $database->selectCollection('usuarios');
$partidas = $database->selectCollection('partidas');
$reservas = $database->selectCollection('reservas');

$usuario = $_SESSION['usuario']['nombre'];
$usuarioDB = $usuarios->findOne(['nombre' => $usuario]);
$correo = $_SESSION['usuario']['gmail'];
// Cambiar contraseña
$msg = '';
if(isset($_POST['cambiar_pass'])){
    $actual = $_POST['pass_actual'];
    $nueva = $_POST['pass_nueva'];
    $nueva2 = $_POST['pass_nueva2'];
    if(password_verify($actual, $usuarioDB['password'])){
        if($nueva === $nueva2){
            $usuarios->updateOne(['_id'=>$usuarioDB['_id']], ['$set'=>['password'=>password_hash($nueva, PASSWORD_DEFAULT)]]);
            $msg = "<div class='alert alert-success'>Contraseña cambiada correctamente.</div>";
        }else{
            $msg = "<div class='alert alert-danger'>Las contraseñas nuevas no coinciden.</div>";
        }
    }else{
        $msg = "<div class='alert alert-danger'>Contraseña actual incorrecta.</div>";
    }
}

// Editar datos
if(isset($_POST['editar_datos'])){
    $nuevoNombre = trim($_POST['nombre']);
$nuevoCorreo = trim($_POST['gmail']);
    $nuevoTelefono = trim($_POST['telefono']);
    $usuarios->updateOne(['_id'=>$usuarioDB['_id']], [
        '$set'=>[
            'nombre'=>$nuevoNombre,
'gmail'=>$nuevoCorreo,
            'telefono'=>$nuevoTelefono
        ]
    ]);
$_SESSION['usuario'] = [
    'nombre' => $nuevoNombre,
    'gmail'  => $nuevoCorreo
];    $msg = "<div class='alert alert-success'>Datos actualizados.</div>";
    // Recargar datos
    $usuarioDB = $usuarios->findOne(['_id'=>$usuarioDB['_id']]);
}
if(isset($_POST['borrar_reserva']) && isset($_POST['id_reserva'])){
    $idReserva = new MongoDB\BSON\ObjectId($_POST['id_reserva']);
    $reservas->deleteOne(['_id' => $idReserva]);
    // Recargar reservas después de borrar
    $misReservas = $reservas->find(['correo'=>$usuarioDB['gmail']])->toArray();
}
// Eliminar cuenta
if(isset($_POST['eliminar_cuenta'])){
    $usuarios->deleteOne(['_id'=>$usuarioDB['_id']]);
    $partidas->deleteMany(['usuario'=>$usuarioDB['nombre']]);
$reservas->deleteMany(['correo'=>$usuarioDB['gmail']]);
    session_destroy();
    header('Location: login.php');
    exit();
}

// Cerrar sesión
if(isset($_POST['logout'])){
    session_destroy();
    header('Location: login.php');
    exit();
}

// Obtener partidas y reservas del usuario
$misPartidas = $partidas->find(['correo'=>$usuarioDB['gmail']])->toArray();
$misReservas = $reservas->find(['correo'=>$usuarioDB['gmail']])->toArray();

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&family=Special+Gothic+Expanded+One&display=swap"
    rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/perfil.css">
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
              <a class="nav-link enlace  " href="partida.php">PARTIDA</a>
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
              <a class="nav-link enlace-icono enlace-destacado"  href="perfil.php">
                <!-- <img src="img/usuario.png" alt="Usuario">-->MI CUENTA
              </a>
            </li>

          </ul>
        </div>

      </div>
    </nav>
  </header>


<div class="container my-4">
  <h2>Mi perfil</h2>
  <?= $msg ?>
  <ul class="nav nav-tabs mb-3" id="perfilTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="datos-tab" data-bs-toggle="tab" data-bs-target="#datos" type="button" role="tab">Mis datos</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="partidas-tab" data-bs-toggle="tab" data-bs-target="#partidas" type="button" role="tab">Partidas guardadas</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="reservas-tab" data-bs-toggle="tab" data-bs-target="#reservas" type="button" role="tab">Reservas realizadas</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="pass-tab" data-bs-toggle="tab" data-bs-target="#pass" type="button" role="tab">Cambiar contraseña</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="eliminar-tab" data-bs-toggle="tab" data-bs-target="#eliminar" type="button" role="tab">Eliminar cuenta</button>
    </li>
  </ul>
  <div class="tab-content" id="perfilTabsContent">
    <!-- Mis datos -->
    <div class="tab-pane fade show active" id="datos" role="tabpanel">
      <form method="POST" class="mb-3">
        <input type="hidden" name="editar_datos" value="1">
        <div class="mb-2">
          <label>Nombre</label>
          <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuarioDB['nombre']) ?>" required>
        </div>
        <div class="mb-2">
          <label>Correo</label>
          <input type="email" name="gmail" class="form-control" value="<?= htmlspecialchars($usuarioDB['gmail']) ?>" required>
        </div>
        <div class="mb-2">
          <label>Teléfono</label>
          <input type="text" name="telefono" minlength="9" maxlength="9" class="form-control" value="<?= htmlspecialchars($usuarioDB['telefono'] ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
      </form>
    </div>
    <!-- Partidas guardadas -->
   <div class="tab-pane fade" id="partidas" role="tabpanel">
  <h5>Mis partidas</h5>
  <?php if(count($misPartidas)): ?>
    <ul class="list-group">
      <?php foreach($misPartidas as $p): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <?= htmlspecialchars($p['campo_nombre'] ?? ($p['nombre'] ?? 'Sin nombre')) ?>
            -  <?php
    if (isset($p['fecha']) && $p['fecha'] instanceof MongoDB\BSON\UTCDateTime) {
        $dt = $p['fecha']->toDateTime();
        $dt->setTimezone(new DateTimeZone('Europe/Madrid'));
        echo $dt->format('d/m/Y H:i');
    }
  ?>
          </span>
          <form method="POST" action="resultados.php" class="m-0">
            <input type="hidden" name="id_partida" value="<?= $p['_id'] ?>">
            <button type="submit" class="btn btn-sm btn-primary">Ver resultados</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <div class="text-muted">No tienes partidas guardadas.</div>
  <?php endif; ?>
</div>
    <!-- Reservas realizadas -->
 <div class="tab-pane fade" id="reservas" role="tabpanel">
  <h5>Mis reservas</h5>
  <?php if(count($misReservas)): ?>
    <ul class="list-group">
      <?php foreach($misReservas as $r): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <?= htmlspecialchars($r['fecha'] ?? '') ?> - <?= htmlspecialchars($r['hora'] ?? '') ?> - <?= htmlspecialchars($r['campo'] ?? '') ?>
          </span>
          <form method="POST" class="m-0 borrar-reserva-form" data-id="<?= $r['_id'] ?>">
            <input type="hidden" name="borrar_reserva" value="1">
            <input type="hidden" name="id_reserva" value="<?= $r['_id'] ?>">
            <button type="button" class="btn btn-sm btn-danger" onclick="abrirModal('<?= $r['_id'] ?>')">Cancelar</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <div class="text-muted">No tienes reservas.</div>
  <?php endif; ?>
</div>

<div class="modal fade" id="modalConfirmacion" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Confirmar cancelación</h5>
        
      </div>
      <div class="modal-body">
        ¿Estás seguro de que deseas cancelar esta reserva?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarBorrar">Sí, cancelar</button>
      </div>
    </div>
  </div>
</div>

<script>
let idReservaAEliminar = null;

function abrirModal(idReserva) {
  idReservaAEliminar = idReserva;
  $('#modalConfirmacion').modal('show');
}

document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('btnConfirmarBorrar').addEventListener('click', function() {
    if (idReservaAEliminar) {
      const form = document.querySelector(`form.borrar-reserva-form[data-id="${idReservaAEliminar}"]`);
      if (form) form.submit();
      idReservaAEliminar = null;
      $('#modalConfirmacion').modal('hide');
    }
  });
});
</script>
    <!-- Cambiar contraseña -->
    <div class="tab-pane fade" id="pass" role="tabpanel">
      <form method="POST" class="mb-3">
        <input type="hidden" name="cambiar_pass" value="1">
        <div class="mb-2">
          <label>Contraseña actual</label>
          <input type="password" name="pass_actual" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>Nueva contraseña</label>
          <input type="password" name="pass_nueva" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>Repetir nueva contraseña</label>
          <input type="password" name="pass_nueva2" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
      </form>
    </div>
    <!-- Eliminar cuenta -->
    <div class="tab-pane fade" id="eliminar" role="tabpanel">
      <form method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar tu cuenta? Esta acción no se puede deshacer.');">
        <input type="hidden" name="eliminar_cuenta" value="1">
<button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmarBorrarPerfil">
    Borrar perfil
</button>
      </form>
    </div>
  </div>
  <form method="POST" class="mt-4">
    <button type="submit" name="logout" class="btn btn-secondary">Cerrar sesión</button>
  </form>
</div>
<div class="modal fade" id="confirmarBorrarPerfil" tabindex="-1" aria-labelledby="confirmarBorrarPerfilLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header border-0">
        <h5 class="modal-title w-100" id="confirmarBorrarPerfilLabel">¿Estás seguro?</h5>
      </div>
      <div class="modal-body">
        <p class="fs-5">Esta acción eliminará tu perfil y todos tus datos.<br>¿Deseas continuar?</p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <form method="POST" action="borrar_perfil.php">
          <button type="submit" class="btn btn-danger">Sí, borrar</button>
        </form>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>