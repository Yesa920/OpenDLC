<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado de queja</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .contenedor {
            background: #fff;
            padding: 25px;
            max-width: 600px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .item {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
            font-size: 16px;
            color: #444;
        }

        .item span {
            font-weight: bold;
            color: #222;
        }

        .mensaje {
            text-align: center;
            margin-top: 20px;
            font-style: italic;
            color: #666;
        }
    </style>
</head>

<body>

<div class="contenedor">
    <h1>Queja registrada</h1>

    <?php
    // Se recorta a lo que cabe en la tabla (nombre 20, correo 50, comentario 200)
    $nombre     = mb_substr(trim((string)($_REQUEST['nombre'] ?? '')), 0, 20);
    $cuenta     = mb_substr(trim((string)($_REQUEST['cuenta'] ?? '')), 0, 50);
    $soloDigitos = substr(preg_replace('/\D/', '', (string)($_REQUEST['telefono'] ?? '')), 0, 9);
    $telefono   = ($soloDigitos !== '') ? $soloDigitos : null;
    $comentario = mb_substr(trim((string)($_REQUEST['comentario'] ?? '')), 0, 200);

    // Lo que se ve en pantalla va escapado: asi nadie puede meter codigo en la pagina
    $usuario1 = array(
        'Nombre' => htmlspecialchars($nombre),
        'Cuenta' => htmlspecialchars($cuenta),
        'Telefono' => htmlspecialchars((string)$telefono),
        'Comentario' => htmlspecialchars($comentario)
    );

    foreach ($usuario1 as $clave => $valor) {
        echo "<div class='item'><span>$clave:</span> $valor</div>";
    }

    // Limite: 5 quejas por conexion cada hora (evita spam)
    require_once __DIR__ . '/../limitador.php';
    if (lim_excedido('ayuda|' . lim_ip(), 5, 3600)) {
        echo "<div class='mensaje'>Has enviado demasiadas quejas seguidas. Inténtalo más tarde.</div></div></body></html>";
        exit();
    }
    lim_registrar('ayuda|' . lim_ip(), 3600);

    // Consulta preparada: los datos nunca se mezclan con el SQL
    $conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME')) or die("Problemas con la conexión");
    $stmt = $conexion->prepare("INSERT INTO quejas(nombre,correo,telefono,comentario) VALUES (?,?,?,?)");
    $stmt->bind_param('ssss', $nombre, $cuenta, $telefono, $comentario);
    $stmt->execute();
    $stmt->close();
    mysqli_close($conexion);

    echo "
    <div class='mensaje'>Redirigiendo a la página de consulta en unos segundos...</div>
    <script>
        setTimeout(function() {
            alert('Redirigimos a la página consultar');
            window.location.href = 'formularioAyuda.php';
        }, 7000);
    </script>
    ";
    ?>
</div>

</body>
</html>