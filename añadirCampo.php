<?php
session_start();
require 'vendor/autoload.php';

use MongoDB\Client;

$uri = "mongodb+srv://joelnp:joel16@cluster0.qcsid.mongodb.net/?retryWrites=true&w=majority";

try {
    $client = new Client($uri);
    $database = $client->selectDatabase('PFDJoel'); 
    $collection = $database->selectCollection('campos');
    $collectionUsuarios = $database->selectCollection('usuarios');

    $usuario = $_SESSION['usuario'] ?? null;
    $esAdmin = false;

    if ($usuario) {
        $usuarioDB = $collectionUsuarios->findOne(['nombre' => $usuario]);
        if ($usuarioDB && isset($usuarioDB['rol']) && $usuarioDB['rol'] === 'admin') {
            $esAdmin = true;
        }
    }

    if (!$esAdmin) {
        header("Location: index.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $hoyosPost = $_POST['hoyos'] ?? [];

        $rutaImagen = '';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $nombreImagen = uniqid() . '_' . basename($_FILES['imagen']['name']);
            $rutaDestino = 'img/campos/' . $nombreImagen;

            if (!is_dir('img/campos')) {
                mkdir('img/campos', 0777, true);
            }

            move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino);
            $rutaImagen = $rutaDestino;
        }

        $hoyos = [];
        $uploadPathHoyo = 'img/hoyos/';

        foreach ($hoyosPost as $index => $hoyoData) {
            $nombreHoyo = $hoyoData['nombre'] ?? '';
            $descripcionHoyo = $hoyoData['descripcion'] ?? '';
            $imagenesHoyo = [];

            if (isset($_FILES['hoyos']) && isset($_FILES['hoyos']['name'][$index]['imagenes'])) {
                $totalArchivos = count($_FILES['hoyos']['name'][$index]['imagenes']);

                for ($i = 0; $i < $totalArchivos; $i++) {
                    $nombreArchivo = $_FILES['hoyos']['name'][$index]['imagenes'][$i];
                    $tmpArchivo = $_FILES['hoyos']['tmp_name'][$index]['imagenes'][$i];
                    $errorArchivo = $_FILES['hoyos']['error'][$index]['imagenes'][$i];

                    if ($errorArchivo === UPLOAD_ERR_OK && !empty($nombreArchivo)) {
                        if (!is_dir($uploadPathHoyo)) {
                            mkdir($uploadPathHoyo, 0777, true);
                        }

                        $rutaFinal = $uploadPathHoyo . uniqid() . '_' . basename($nombreArchivo);
                        if (move_uploaded_file($tmpArchivo, $rutaFinal)) {
                            $imagenesHoyo[] = $rutaFinal;
                        }
                    }
                }
            }

            $hoyos[] = [
                'nombre' => $nombreHoyo,
                'descripcion' => $descripcionHoyo,
                'imagenes' => $imagenesHoyo
            ];
        }

        $collection->insertOne([
            'nombre' => $nombre,
            'desc' => $descripcion,
            'img' => $rutaImagen,
            'hoyos' => $hoyos,
        ]);

        exit();
    }
} catch (Exception $e) {
    echo "Error al conectar a MongoDB: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear nuevo campo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&family=Special+Gothic+Expanded+One&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>

<div class="container mt-5">
    <h1 class="mb-4">Crear nuevo campo</h1>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre del campo</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Describe brevemente el campo..."></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Hoyos</label>
            <div id="hoyosContainer">
                <div class="hoyo-item mb-3 border p-3 rounded">
                    <input type="text" name="hoyos[0][nombre]" class="form-control mb-2" placeholder="Hoyo 1 - 100m" required>
                    <label>Descripción del hoyo</label>
                    <textarea name="hoyos[0][descripcion]" class="form-control mb-2" placeholder="Descripción del hoyo..."></textarea>
                    <label>Imágenes del hoyo</label>
                    <input type="file" name="hoyos[0][imagenes][]" multiple accept="image/*" class="form-control mb-2">
                    <button type="button" class="btn btn-danger remove-hoyo">Eliminar</button>
                </div>
            </div>
            <button type="button" id="addHoyo" class="btn btn-secondary">Añadir otro hoyo</button>
        </div>

        <div class="mb-3">
            <label for="imagen" class="form-label">Imagen del campo</label>
            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" required>
        </div>

        <button type="submit" class="btn btn-success">Guardar Campo</button>
    </form>
</div>

<script>
    let hoyoIndex = 1;

    document.getElementById('addHoyo').addEventListener('click', function () {
        const container = document.getElementById('hoyosContainer');
        const div = document.createElement('div');
        div.classList.add('hoyo-item', 'mb-3', 'border', 'p-3', 'rounded');

        div.innerHTML = `
            <input type="text" name="hoyos[${hoyoIndex}][nombre]" class="form-control mb-2" placeholder="Hoyo ${hoyoIndex + 1} - distancia" required>
            <label>Descripción del hoyo</label>
            <textarea name="hoyos[${hoyoIndex}][descripcion]" class="form-control mb-2" placeholder="Descripción del hoyo..."></textarea>
            <label>Imágenes del hoyo</label>
            <input type="file" name="hoyos[${hoyoIndex}][imagenes][]" multiple accept="image/*" class="form-control mb-2">
            <button type="button" class="btn btn-danger remove-hoyo">Eliminar</button>
        `;
        container.appendChild(div);
        hoyoIndex++;
    });

    document.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('remove-hoyo')) {
            e.target.closest('.hoyo-item').remove();
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
