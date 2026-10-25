<?php
session_start();
require_once __DIR__ . '/auth.php'; requerir_cliente();
$conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));

$id_producto = intval($_POST['producto_id']);
$puntuacion  = intval($_POST['puntuacion']);
$comentario  = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
$id_usuario  = $_SESSION['id_cliente'] ?? null;
$desde_devolucion = !empty($_POST['desde_devolucion']);

// 🚫 si no está logueado, fuera
if (!$id_usuario) {
    die("Debes iniciar sesión");
}

// ✅ Verificar que el cliente ha comprado este producto
$sql_compra = "SELECT id FROM ventas WHERE id_cliente = ? AND codigo_producto = ? LIMIT 1";
$stmt_compra = $conexion->prepare($sql_compra);
$stmt_compra->bind_param("ii", $id_usuario, $id_producto);
$stmt_compra->execute();
$res_compra = $stmt_compra->get_result();
$stmt_compra->close();

if ($res_compra->num_rows === 0) {
    die("Solo puedes valorar productos que hayas comprado.");
}

// comprobar si ya votó
$sql_check = "SELECT * FROM valoraciones 
              WHERE codigo_producto = ? AND id_cliente = ?";

$stmt = $conexion->prepare($sql_check);
$stmt->bind_param("ii", $id_producto, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // 🔁 ya votó → actualizar
    $sql_update = "UPDATE valoraciones 
                   SET puntuacion = ?, comentario = ?
                   WHERE codigo_producto = ? AND id_cliente = ?";

    $stmt = $conexion->prepare($sql_update);
    $stmt->bind_param("isii", $puntuacion, $comentario, $id_producto, $id_usuario);
    $stmt->execute();

} else {
    // ✅ no ha votado → insertar
    $sql_insert = "INSERT INTO valoraciones (codigo_producto, puntuacion, comentario, id_cliente) 
                   VALUES (?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql_insert);
    $stmt->bind_param("iisi", $id_producto, $puntuacion, $comentario, $id_usuario);
    $stmt->execute();
}

// Redirigir según de dónde viene
if ($desde_devolucion) {
    header("Location: devolucion.php");
} else {
    header("Location: cliente.php");
}
