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
    <link rel="stylesheet" href="">
    <title>Document</title>
</head>
<body>
      <form id="login" method="post">
            <h3>INICIO DE SESIÓN</h3>
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button id="boton" type="submit">ACCEDER</button>
            <p>¿No estás registrado?<a id="registrar" href="registrar.php"> Regístrate</a></p>
            <?php if (isset($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
        </form>
</body>
</html>