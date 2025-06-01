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

    $usuarioExistente = $collection->findOne(['nombre' => $nombr]);

    if ($usuarioExistente) {
        $error = "Este correo ya está registrado.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $collection->insertOne([
            'nombre' => $nombre,
            'telefono' => $telefono,
            'gmail' => $gmail,
            'password' => $hashedPassword 
        ]);

        header("Location: index.html");
        exit;
    }
}

    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="login.css">
        <title>Document</title>
    </head>
    <body>
        <div id="todo">
        <div id="izquierda">
        <img id="img1" src="logoReloj-fondo.png" alt="">
        </div>
        <form id="login" method="post">
            <h3>INICIO DE SESION</h3>
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="tel" name="telefono" placeholder="Telf" minlength="9" maxlength="9" required>
            <input type="email" name="gmail" placeholder="Gmail" required>
            <input type="password" name="password" placeholder="Contraseña"minlength="8" required >
            <button id="boton" type="submit">ACCEDER</button>
        </form>
    </div>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
    </html>