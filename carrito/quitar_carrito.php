<html>
<head></head>
<body>
<?php
session_start();
require_once __DIR__ . '/../auth.php'; requerir_cliente();

// Verificar que el cliente está logueado
if (!isset($_SESSION['id_cliente'])) {
    die("Error: No hay un cliente logueado.");
}

$id_cliente = $_SESSION['id_cliente'];

$conexion = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if (isset($_POST['id'])) {
    $id_carrito = intval($_POST['id']);

    if ($id_carrito <= 0) {
        die("ID de carrito no válido");
    }
    // 1. Obtener cantidad y producto_id ANTES de borrar
    $stmt_get = $conexion->prepare("SELECT producto_id, cantidad FROM carrito WHERE id = ? AND id_cliente = ?");
    $stmt_get->bind_param("ii", $id_carrito, $id_cliente);
    $stmt_get->execute();
    $res = $stmt_get->get_result();
    $fila = $res->fetch_assoc();
    $stmt_get->close();

    if (!$fila) {
        die("Producto no encontrado en el carrito");
    }

    // 2. Borrar del carrito
    $stmt = $conexion->prepare("DELETE FROM carrito WHERE id = ? AND id_cliente = ?");
    $stmt->bind_param("ii", $id_carrito, $id_cliente);
    $stmt->execute();
    $stmt->close();

    // 3. Devolver stock con + en vez de -
    $updateStock = $conexion->prepare("UPDATE componentes SET stock = stock + ? WHERE codigo_producto = ?");
    $updateStock->bind_param("ii", $fila['cantidad'], $fila['producto_id']);
    $updateStock->execute();
    $updateStock->close();

    header("Location: carrito.php");
    exit();

    if ($stmt->execute()) {
        header("Location: carrito.php");
        exit();
    } else {
        die("Error al eliminar el producto: " . $stmt->error);
    }

    $stmt->close();
} else {
    die("No se recibió ningún ID de carrito");
}

$conexion->close();
?>
</body>
</html>