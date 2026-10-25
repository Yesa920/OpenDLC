<?php require_once __DIR__ . '/../auth.php'; requerir_admin(); ?>
<html>

<head></head>

<body>
<?php
$conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME')) 
    or die("Problemas con la conexión");

// Recoger la clave primaria
$id_producto = (int)($_REQUEST['id_producto'] ?? 0);

// Precio y stock tienen que ser numeros validos
$precio = str_replace(',', '.', trim((string)($_REQUEST['precioNuevo'] ?? '')));
$stockTxt = trim((string)($_REQUEST['stock'] ?? ''));
if (!is_numeric($precio) || (float)$precio < 0 || !ctype_digit($stockTxt)) {
    echo "<script>alert('El precio y el stock deben ser numeros validos.'); history.back();</script>";
    exit();
}
$stock = (int)$stockTxt;


// ---- MANEJO DE LA IMAGEN ----
$nombreImagenNuevo = basename((string)($_REQUEST['imagenVieja'] ?? '')); // por defecto mantenemos la vieja

if (isset($_FILES['imagenProducto']) && $_FILES['imagenProducto']['error'] == 0) {

    $directorio = "../imagenes_productos/";
    $ext = strtolower(pathinfo($_FILES["imagenProducto"]["name"], PATHINFO_EXTENSION));
    if (!in_array($ext, ["jpg", "jpeg", "png", "gif", "webp"], true) || @getimagesize($_FILES["imagenProducto"]["tmp_name"]) === false || $_FILES["imagenProducto"]["size"] > 5 * 1024 * 1024) {
        echo "<script>alert('Solo se permiten imagenes JPG, PNG, GIF o WEBP de hasta 5 MB.'); history.back();</script>";
        exit();
    }
    $nombreArchivo = uniqid() . "." . $ext;   // el nombre lo decide el servidor
    $rutaCompleta = $directorio . $nombreArchivo;

    if (move_uploaded_file($_FILES["imagenProducto"]["tmp_name"], $rutaCompleta)) {
        $nombreImagenNuevo = $nombreArchivo; 
    }
}

// ---- UPDATE REAL ----
// AHORA SÍ: actualizamos usando SOLO la PK
$nombre     = (string)($_REQUEST['nombreNuevo'] ?? '');
$tipo       = (string)($_REQUEST['tipo_hardwareNuevo'] ?? '');
$antiguo    = (string)($_REQUEST['masCincoAgnosNuevo'] ?? '');
$comentario = (string)($_REQUEST['comentarioNuevo'] ?? '');
$stmt = $conexion->prepare("UPDATE componentes SET nombre=?, tipo_hardware=?, Antiguo=?, Precio=?, Comentario=?, imagen=?, stock=? WHERE codigo_producto=?");
$stmt->bind_param('ssssssii', $nombre, $tipo, $antiguo, $precio, $comentario, $nombreImagenNuevo, $stock, $id_producto);
$stmt->execute();
$stmt->close();

echo "<script>
			alert('El componente se ha actualizado correctamente');
			window.location.href = 'update.php';
	  </script>";
?>
</body>
</html>