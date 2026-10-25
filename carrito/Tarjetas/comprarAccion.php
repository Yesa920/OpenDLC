<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 1. Iniciar sesi�n y cargar librer�as siempre al principio
session_start();
require_once __DIR__ . '/../../auth.php'; requerir_cliente();
require 'PHPMailer-master/PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/PHPMailer-master/src/SMTP.php';

if (!isset($_SESSION['id_cliente'])) {
    die("Error: no hay cliente logueado.");
}

$id_cliente = $_SESSION['id_cliente'];

// Conexi�n a la BD
$conexion = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
if ($conexion->connect_error) {
    die("Error de conexi�n: " . $conexion->connect_error);
}

// (Las tablas y columnas ya vienen creadas en db/init/pccomponentes.sql: la web no necesita
// permiso para crear ni alterar tablas.)

// 2. Recoger datos del POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo      = $_POST['correo'];
    $nombre      = $_POST['nombre'];
    $metodo_pago = $_POST['metodo_pago']; 
    $apellido    = $_POST['apellidos'];
    $localidad   = $_POST['localidad'];
    $cod_postal  = $_POST['codigo_postal'];
	$direccion  = $_POST['direccion'];
    $total       = 0;
	
	$coords = $_POST['coords_entrega'] ?? '';
	$lat = $_POST['lat_entrega'] ?? '';
	$lng = $_POST['lng_entrega'] ?? '';
	$direccion_entrega = $_POST['direccion_entrega'] ?? '';
}

// Version segura para HTML de lo que se muestra en pantalla y en el correo
$h = [];
foreach (['nombre','apellido','localidad','cod_postal','metodo_pago','direccion','coords','direccion_entrega'] as $k) {
    $h[$k] = htmlspecialchars((string)($$k ?? ''), ENT_QUOTES, 'UTF-8');
}

// 3. Calcular total y obtener productos usando Prepared Statements para seguridad
$stmt_productos = $conexion->prepare("SELECT 
    ca.producto_id, 
    ca.nombre, 
    ca.Precio,
	ca.cantidad,
    c.id_administrador,
    c.tipo_hardware
FROM carrito ca 
JOIN componentes c ON ca.producto_id = c.codigo_producto 
WHERE ca.id_cliente = ?");
$stmt_productos->bind_param("i", $id_cliente);
$stmt_productos->execute();
$resultado = $stmt_productos->get_result();



$productos_html = "";
$ventas_admin = [];
if ($resultado->num_rows > 0) {
    $productos_html .= "
    <table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
        <tr style='background-color: #f2f2f2;'>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio (&euro;)</th>
        </tr>";

    while ($fila = $resultado->fetch_assoc()) {
        $total += $fila['Precio'] * $fila['cantidad'];
		
			
		// ?? NUEVO: coger admin del producto
		$id_admin = $fila['id_administrador'];

		// Agrupar por admin
		if (!isset($ventas_admin[$id_admin])) {
			$ventas_admin[$id_admin] = 0;
		}
		$stmt_venta = $conexion->prepare("
			INSERT INTO ventas 
			(codigo_producto, producto, tipoHardware, cantidad, precio, total, id_administrador, id_cliente, fecha)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
		");
		

		$cantidad = $fila['cantidad'];
		$total_producto = $fila['Precio'] * $cantidad;

		$stmt_venta->bind_param(
			"issiddii",
			$fila['producto_id'],
			$fila['nombre'],
			$fila['tipo_hardware'],
			$cantidad,
			$fila['Precio'],
			$total_producto,
			$fila['id_administrador'],
			$id_cliente
		);

		$stmt_venta->execute();
		$ventas_admin[$id_admin] += $fila['Precio'];
		
        $productos_html .= "
        <tr>
            <td>" . htmlspecialchars($fila['nombre']) . "</td>
            <td>" . number_format($fila['cantidad']) . "</td>
            <td>" . number_format($fila['Precio'] * $fila['cantidad'], 2) . " &euro;</td>
        </tr>";
    }
    $productos_html .= "</table>";
} else {
    // Si el carrito est� vac�o, redirigir
    header("Location: ../carrito.php");
    exit;
}

// 4. Configuraci�n de PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('MAIL_USER');
    $mail->Password   = getenv('MAIL_PASS');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	$mail->CharSet = 'UTF-8';
    $mail->Port       = 587;

    $mail->setFrom(getenv('MAIL_USER'), 'OpenDLC');
    $mail->addAddress($correo, $nombre . ' ' . $apellido);
    $mail->isHTML(true);
    $mail->Subject = "Confirmación de compra - OpenDLC";
    
    $mail->Body = "
			<html>
			<head><title>Confirmación de compra</title></head>
			<body style='font-family: Arial, sans-serif;'>
				<h2 style='color: #ff6600;'>Gracias por tu compra, {$h['nombre']} {$h['apellido']}</h2>
				<p>Con localidad: <strong>{$h['localidad']}</strong> y código postal <strong>{$h['cod_postal']}</strong>.</p>
				<p>Tu pago con <strong>{$h['metodo_pago']}</strong> ha sido procesado correctamente.</p>

				<h3>Detalles de tu compra:</h3>
				$productos_html

				<p style='margin-top: 10px;'>Total abonado: <strong>" . number_format($total, 2) . " &euro;</strong></p>
				<p>Calle principal por el usaurio {$h['direccion']}</p>
				<h3>Dirección de entrega alternativa:</h3>
				<p>Coordenadas seleccionadas en el mapa: <strong>{$h['coords']}</strong></p>
				<p>Latitud: $lat, Longitud: $lng</p>
				<p>Dirección escrita por el usuario: <strong>{$h['direccion_entrega']}</strong></p>
				<p>En breve recibirás los detalles del envío.</p>
				<br>
				<p>Atentamente,<br><strong>El equipo de OpenDLC</strong></p>
			</body>
			</html>";

    $mail->send();



    // 6. VACIAR CARRITO
    $stmt_del = $conexion->prepare("DELETE FROM carrito WHERE id_cliente = ?");
    $stmt_del->bind_param("i", $id_cliente);
    $stmt_del->execute();
		
	echo "<script>
            alert('¡Pedido realizado con éxito! Se ha enviado un correo a " . htmlspecialchars($correo) . "');
            window.location.href = '../../cliente.php';
          </script>";
    exit;

    exit;

} catch (Exception $e) {
        // ERROR: Mostramos el error de PHPMailer y redirigimos
    echo "<script>
            alert('Hubo un problema: " . addslashes($mail->ErrorInfo) . "');
            window.location.href = '../../cliente.php';
          </script>";
    exit;
    exit;
}
?>
