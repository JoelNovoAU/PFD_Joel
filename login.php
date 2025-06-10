<?php
session_start([
    'cookie_lifetime' => 60, 
]);
require 'vendor/autoload.php'; 

use MongoDB\Client;

$uri = "mongodb+srv://joelnp:joel16@cluster0.qcsid.mongodb.net/?retryWrites=true&w=majority";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $password = trim($_POST['password']);

    $client = new Client($uri);
    $database = $client->selectDatabase('PFDJoel'); 
    $collection = $database->selectCollection('usuarios');

    $usuario = $collection->findOne(['nombre' => $nombre]);

    if ($usuario && password_verify($password, $usuario['password'])) { 
        $_SESSION['usuario'] = $usuario['nombre'];
        $_SESSION['id'] = $usuario['_id'];
$_SESSION['usuario'] = [
    'nombre' => $usuario['nombre'],
    'gmail'  => $usuario['gmail']
];
        header('Location: index.html');
        exit;
    } else {
        
        $error = "Nombre o contraseña incorrectos.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Document</title>
</head>
<body>
      <div class="login-wrapper">
    <div class="login-form-side">
      <form id="login" method="post">
        <h3>INICIO DE SESIÓN</h3>
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button id="boton" type="submit">ACCEDER</button>
        <p>¿No estás registrado?<a id="registrar" href="registrar.php"> Regístrate</a></p>
        <?php if (isset($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
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