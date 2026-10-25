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

if (isset($_POST['codigo_producto'])) {
    $id_producto = intval($_POST['codigo_producto']); 
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;
    if ($cantidad < 1) $cantidad = 1;

    if ($id_producto <= 0) {
        die("codigo_producto de producto no válido");
    }

    // Obtener los datos del producto Y verificar stock
    $stmt = $conexion->prepare("SELECT codigo_producto, nombre, tipo_hardware, Precio, stock FROM componentes WHERE codigo_producto = ?");
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($fila = $resultado->fetch_assoc()) {
        
        // Verificar que hay stock suficiente
        if ($fila['stock'] < $cantidad) {
            die("No hay suficiente stock. Stock disponible: " . $fila['stock']);
        }

        // Insertar en carrito con id_cliente Y cantidad
        $insert = $conexion->prepare(
            "INSERT INTO carrito (producto_id, nombre, tipo_hardware, precio, id_cliente, cantidad)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $insert->bind_param(
            "issdii",
            $fila['codigo_producto'],
            $fila['nombre'],
            $fila['tipo_hardware'],
            $fila['Precio'],
            $id_cliente,
            $cantidad
        );

        if ($insert->execute()) {
            // Actualizar el stock del producto
            $updateStock = $conexion->prepare("UPDATE componentes SET stock = stock - ? WHERE codigo_producto = ?");
            $updateStock->bind_param("ii", $cantidad, $id_producto);
            $updateStock->execute();
            $updateStock->close();
            
            echo "<script>
                    alert('El producto se ha añadido a tu carrito');
                    window.location.href = '../cliente.php';
                  </script>";
            exit();
        } else {
            die("Error al insertar en el carrito: " . $insert->error);
        }

        $insert->close();
    } else {
        die("Producto no encontrado");
    }

    $stmt->close();
} else {
    die("No se recibió ningún codigo_producto de producto");
}

$conexion->close();
?>
</body>
</html>
