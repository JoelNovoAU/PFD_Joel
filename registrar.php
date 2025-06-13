<?php
   session_start();
require 'vendor/autoload.php';
use MongoDB\Client;

$uri = "mongodb+srv://joelnp:joel16@cluster0.qcsid.mongodb.net/?retryWrites=true&w=majority";
$client = new Client($uri);
$database = $client->selectDatabase('PFDJoel'); 
$collection = $database->selectCollection('usuarios');

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $gmail = trim($_POST['gmail']);
    $password = trim($_POST['password']);

    $usuarioExistente = $collection->findOne([
        '$or' => [
            ['nombre' => $nombre],
            ['gmail' => $gmail]
        ]
    ]);

    if ($usuarioExistente) {
        $error = "El nombre de usuario o el correo ya están registrados.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $collection->insertOne([
            'nombre' => $nombre,
            'telefono' => $telefono,
            'gmail' => $gmail,
            'password' => $hashedPassword 
        ]);
$_SESSION['usuario'] = [
            'nombre' => $nombre,
            'gmail'  => $gmail
        ];

        header("Location: index.html");
        exit;
    }
}

    ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso</title>
    <link rel="stylesheet" href="css/login.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="login-wrapper">
    <div class="login-form-side">
      <form id="login" method="post">
        <h3>FORMULARIO DE REGISTRO</h3>
        <?php if(isset($error) && $error): ?>
          <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="tel" name="telefono" placeholder="Telf" minlength="9" maxlength="9" required>
        <input type="email" name="gmail" placeholder="Gmail" required>
        <input type="password" name="password" placeholder="Contraseña" minlength="8" required>
        <button id="boton" type="submit">ACCEDER</button>
      </form>
    </div>
    <div class="login-image-side">
      <img src="img/golf-4608460.jpg" alt="Golf o Logo">
        <div class="image-caption">
        <strong>¡Bienvenido a tu club de golf!</strong><br>
             </div>

    </div>
  </div>
</body>
</html>