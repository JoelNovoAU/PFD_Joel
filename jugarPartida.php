<?php
session_start();
require 'vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

$uri = "mongodb+srv://joelnp:joel16@cluster0.qcsid.mongodb.net/?retryWrites=true&w=majority";

try {
    $client = new Client($uri);
    $database = $client->selectDatabase('PFDJoel');
    $collectionCampos = $database->selectCollection('campos');
    $collection = $database->selectCollection('partidas');
    $collectionUsuarios = $database->selectCollection('usuarios');

    $idCampo = $_GET['id'] ?? null;

    if ($idCampo) {
        $campo = $collectionCampos->findOne(['_id' => new ObjectId($idCampo)]);
    } else {
        $campo = $collectionCampos->findOne();
    }
    $hoyos = $campo['hoyos'] ?? [];

} catch (Exception $e) {
    echo "Error al conectar a MongoDB: " . $e->getMessage();
    exit;
}

// Paso 1: Preguntar número de jugadores y nombres
if (!isset($_POST['num_jugadores']) && !isset($_POST['jugadores'])):
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Jugar Partida</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
    <h2 class="mb-4 text-center">¿Cuántos jugadores sois?</h2>
    <form method="POST" class="mx-auto" style="max-width:400px;">
        <div class="mb-3">
            <label for="num_jugadores" class="form-label">Número de jugadores (2-6):</label>
            <input type="number" min="2" max="6" class="form-control" name="num_jugadores" id="num_jugadores" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Siguiente</button>
    </form>
</div>
<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['num_jugadores'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('form-nombres').scrollIntoView({behavior: 'smooth'});
    });
</script>
<?php endif; ?>
<?php
// Paso 2: Pedir nombres de los jugadores
elseif (isset($_POST['num_jugadores']) && !isset($_POST['jugadores'])):
    $num = intval($_POST['num_jugadores']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nombres de los jugadores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
    <h2 class="mb-4 text-center">Introduce el nombre de cada jugador</h2>
    <form method="POST" id="form-nombres" class="mx-auto" style="max-width:400px;">
        <?php for ($i = 1; $i <= $num; $i++): ?>
            <div class="mb-3">
                <label class="form-label">Jugador <?= $i ?>:</label>
                <input type="text" name="jugadores[]" class="form-control" required>
            </div>
        <?php endfor; ?>
        <input type="hidden" name="num_jugadores" value="<?= $num ?>">
        <button type="submit" class="btn btn-success w-100">Jugar</button>
    </form>
</div>
</body>
</html>
<?php
// Paso 3: Guardar partida y mostrar formulario de puntuaciones
elseif (isset($_POST['jugadores'])):
    $jugadores = $_POST['jugadores'];

   if (isset($_POST['tiros'])) {
    $jugadores = $_POST['jugadores'];
    $puntuaciones = $_POST['tiros'];
    $correo = $_SESSION['usuario']['gmail'] ?? 'anonimo@sinlogin.com';

    // Procesar archivo antes de crear $partida
   $mediaPaths = [];
if (isset($_FILES['media']) && is_array($_FILES['media']['name'])) {
    foreach ($_FILES['media']['tmp_name'] as $i => $tmpName) {
        if ($_FILES['media']['error'][$i] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['media']['name'][$i], PATHINFO_EXTENSION);
            $filename = uniqid('media_') . '.' . $ext;
            $destino = __DIR__ . '/uploads/' . $filename;
            if (!is_dir(__DIR__ . '/uploads')) {
                mkdir(__DIR__ . '/uploads', 0777, true);
            }
            if (move_uploaded_file($tmpName, $destino)) {
                $mediaPaths[] = 'uploads/' . $filename;
            }
        }
    }
}

    // Ahora sí, crea el array con media ya definido
    $partida = [
        'fecha' => new MongoDB\BSON\UTCDateTime(),
        'campo_id' => $campo['_id'],
        'campo_nombre' => $campo['nombre'],
        'correo' => $correo,
        'jugadores' => [],
        'media' => $mediaPaths,
    ];

    foreach ($jugadores as $idx => $jugador) {
        $jugadorData = [
            'nombre' => $jugador,
            'hoyos' => []
        ];
        foreach ($hoyos as $hoyo) {
            $nombreHoyo = $hoyo['nombre'];
            $jugadorData['hoyos'][] = [
                'hoyo' => $nombreHoyo,
                'puntuacion' => $_POST['tiros'][$nombreHoyo][$idx] ?? null
            ];
        }
        $partida['jugadores'][] = $jugadorData;
    }

        // Guardar en la colección
        $collection->insertOne($partida);

        // Calcular resultados
        $resultados = [];
        foreach ($partida['jugadores'] as $jugador) {
            $total = 0;
            foreach ($jugador['hoyos'] as $hoyo) {
                $total += intval($hoyo['puntuacion']);
            }
            $resultados[] = [
                'nombre' => $jugador['nombre'],
                'total' => $total
            ];
        }
        usort($resultados, fn($a, $b) => $a['total'] <=> $b['total']);
        $_SESSION['partida_reciente'] = $partida;
        $_SESSION['resultados_partida'] = $resultados;
        // Redirige a la página de resultados
        header("Location: resultados.php");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&family=Special+Gothic+Expanded+One&display=swap" rel="stylesheet">
    <title>Partida en curso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/jugarpartida.css">
    <style>
        .score-input { width: 60px; text-align: center; }
        .hoyo-card { margin-bottom: 2rem; }
    </style>
</head>
<body class="bg-light">
<div class="container my-5">
    <h2 class="mb-4 text-center">Anota los tiros de cada jugador</h2>
<form method="POST" enctype="multipart/form-data">
            <?php foreach ($jugadores as $j): ?>
            <input type="hidden" name="jugadores[]" value="<?= htmlspecialchars($j) ?>">
        <?php endforeach; ?>
        <?php foreach ($hoyos as $hoyo): ?>
            <div class="card mb-4 hoyo-card">
                <div class="card-header hoyo-header d-flex align-items-center">
                    <i class="bi bi-flag me-2 hoyo-flag"></i>
                    <span class="hoyo-nombre">
                        <?= htmlspecialchars($hoyo['nombre']) ?>
                    </span>
                </div>
                <div class="card-body hoyo-body">
                    <p class="hoyo-desc">
                        <?= htmlspecialchars($hoyo['descripcion'] ?? ($hoyo['desc'] ?? '')) ?>
                    </p>
                    <div class="row">
                        <?php foreach ($jugadores as $idx => $j): ?>
                            <div class="col-12 col-md-6 col-lg-4 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text jugador-icon">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="number" min="1" class="form-control score-input"
                                        name="tiros[<?= htmlspecialchars($hoyo['nombre']) ?>][<?= $idx ?>]"
                                        placeholder="Tiros de <?= htmlspecialchars($j) ?>">
                                </div>
                                <div class="text-center mt-1 jugador-nombre">
                                    <?= htmlspecialchars($j) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <div class="mb-4 mt-4">
    <label class="form-label fw-bold" style="font-size:1.1rem;">
        <i class="bi bi-camera-video" style="font-size:1.3em;vertical-align:-0.15em;color:#495057;"></i>
        Añadir imagen o vídeo de la partida :
    </label>
    <div class="input-group custom-media-input">
<input type="file" class="form-control" name="media[]" accept="image/*,video/*" id="mediaInput" multiple>
        <label class="input-group-text" for="mediaInput" style="cursor:pointer;">
            <i class="bi bi-upload"></i> Elegir archivo
        </label>
    </div>
    <div class="form-text text-muted mt-1" id="mediaFileName">
        Puedes tomar una foto o vídeo desde tu móvil o subir uno desde tu ordenador.
    </div>
</div>
        <div class="text-center">
            <button type="submit" class="btn  px-5 py-2 btn-guardar">Guardar puntuaciones</button>
        </div>
    </form>
</div>
</body>
</html>
<?php endif; ?>