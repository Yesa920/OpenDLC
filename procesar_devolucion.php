<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require_once __DIR__ . '/auth.php'; requerir_cliente();
require __DIR__ . '/carrito/Tarjetas/PHPMailer-master/PHPMailer-master/src/Exception.php';
require __DIR__ . '/carrito/Tarjetas/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/carrito/Tarjetas/PHPMailer-master/PHPMailer-master/src/SMTP.php';

if (!isset($_SESSION['id_cliente'])) {
    header('Location: UID/iniciar_sesion/acceso.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_venta']) || empty($_POST['punto_recogida']) || empty($_POST['fecha_hora'])) {
    header('Location: devolucion.php');
    exit();
}

$id_cliente = intval($_SESSION['id_cliente']);
$id_venta   = intval($_POST['id_venta']);
$fecha_hora = trim($_POST['fecha_hora']);

$conexion = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
if ($conexion->connect_error) {
    die('Error de conexión: ' . $conexion->connect_error);
}

$punto_recogida = $conexion->real_escape_string(trim($_POST['punto_recogida']));

// (Las tablas y columnas ya vienen creadas en db/init/pccomponentes.sql: la web no necesita
// permiso para crear ni alterar tablas.)

// Obtener datos de la venta y del cliente
$stmt = $conexion->prepare("
    SELECT v.id, v.codigo_producto, v.producto, v.tipoHardware, v.cantidad, v.precio, v.total, v.fecha,
           c.nombre, c.apellido, c.email
    FROM ventas v
    JOIN cliente c ON c.id_cliente = v.id_cliente
    WHERE v.id = ? AND v.id_cliente = ? AND v.fecha >= DATE_SUB(NOW(), INTERVAL 1 DAY)
");
$stmt->bind_param('ii', $id_venta, $id_cliente);
$stmt->execute();
$venta = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$venta) {
    echo "<script>alert('No se ha encontrado esa compra o ya no es elegible para devolución.'); window.location.href='devolucion.php';</script>";
    exit();
}

// Verificar que no se ha solicitado ya
$stmt_check = $conexion->prepare("SELECT id FROM devoluciones WHERE id_venta = ? LIMIT 1");
$stmt_check->bind_param('i', $id_venta);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    echo "<script>alert('Ya has solicitado la devolución de este producto.'); window.location.href='devolucion.php';</script>";
    exit();
}
$stmt_check->close();

// Registrar la devolución
$stmt_insert = $conexion->prepare("INSERT INTO devoluciones (id_venta, id_cliente, estado, punto_recogida, fecha_horario) VALUES (?, ?, 'PROGRAMADA', ?, ?)");
$stmt_insert->bind_param('iiss', $id_venta, $id_cliente, $punto_recogida, $fecha_hora);
$stmt_insert->execute();
$stmt_insert->close();

$conexion->close();

// Datos formateados
$fecha_compra_fmt   = date('d/m/Y H:i', strtotime($venta['fecha']));
$fecha_recogida_fmt = date('d/m/Y H:i', strtotime($fecha_hora));
$nombre_cliente     = $venta['nombre'] . ' ' . $venta['apellido'];

$tabla_detalles = "
<table border='1' cellpadding='10' cellspacing='0'
       style='border-collapse: collapse; width: 100%; max-width: 600px;'>
    <tr style='background-color: #f1f5f9;'>
        <th align='left'>Campo</th>
        <th align='left'>Valor</th>
    </tr>
    <tr><td><strong>Producto</strong></td><td>" . htmlspecialchars($venta['producto']) . "</td></tr>
    <tr><td><strong>Categoría</strong></td><td>" . htmlspecialchars($venta['tipoHardware']) . "</td></tr>
    <tr><td><strong>Cantidad</strong></td><td>" . intval($venta['cantidad']) . " uds.</td></tr>
    <tr><td><strong>Precio unitario</strong></td><td>" . number_format($venta['precio'], 2) . " €</td></tr>
    <tr><td><strong>Total</strong></td><td>" . number_format($venta['total'], 2) . " €</td></tr>
    <tr><td><strong>Fecha de compra</strong></td><td>{$fecha_compra_fmt}</td></tr>
    <tr><td><strong>Punto de recogida</strong></td><td>" . htmlspecialchars($punto_recogida) . "</td></tr>
    <tr><td><strong>Fecha y hora de recogida</strong></td><td>{$fecha_recogida_fmt}</td></tr>
</table>";

$smtpConfig = function(PHPMailer $mail) {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('MAIL_USER');
    $mail->Password   = getenv('MAIL_PASS');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom(getenv('MAIL_USER'), 'OpenDLC');
};

// --- Correo al CLIENTE: confirmación ---
try {
    $mail = new PHPMailer(true);
    $smtpConfig($mail);
    $mail->addAddress($venta['email'], $nombre_cliente);
    $mail->isHTML(true);
    $mail->Subject = "Confirmación de devolución — " . $venta['producto'];
    $mail->Body = "
    <html><body style='font-family: Arial, sans-serif; color: #1e293b;'>
        <h2 style='color: #1f3b82;'>✅ Solicitud de devolución registrada</h2>
        <p>Hola <strong>" . htmlspecialchars($nombre_cliente) . "</strong>,</p>
        <p>Hemos recibido tu solicitud de devolución. Aquí tienes el resumen:</p>
        {$tabla_detalles}
        <p style='margin-top:20px;'>Recuerda acudir al punto de recogida en la fecha indicada.</p>
        <p>Si tienes alguna duda, contacta con nuestro soporte.</p>
        <br><p>Atentamente,<br><strong>El equipo de OpenDLC</strong></p>
    </body></html>";
    $mail->AltBody = "Devolución registrada: {$venta['producto']} — Recogida: {$fecha_recogida_fmt} en {$punto_recogida}";
    $mail->send();
} catch (Exception $e) {
    error_log('Error correo cliente devolución: ' . $mail->ErrorInfo);
}

// --- Correo a los ADMINISTRADORES: notificación ---
try {
    $mail = new PHPMailer(true);
    $smtpConfig($mail);
    $mail->addAddress(getenv('MAIL_USER'), 'Administración OpenDLC');
    $mail->isHTML(true);
    $mail->Subject = "Nueva devolución solicitada — " . $venta['producto'];
    $mail->Body = "
    <html><body style='font-family: Arial, sans-serif; color: #1e293b;'>
        <h2 style='color: #1f3b82;'>📦 Nueva solicitud de devolución</h2>
        <p>El cliente <strong>" . htmlspecialchars($nombre_cliente) . "</strong>
           (<a href='mailto:" . htmlspecialchars($venta['email']) . "'>" . htmlspecialchars($venta['email']) . "</a>)
           ha solicitado una devolución:</p>
        {$tabla_detalles}
        <p style='margin-top:20px;'>La venta se eliminará del sistema y el stock se actualizará automáticamente cuando llegue la fecha de recogida.</p>
        <br><p>Sistema OpenDLC</p>
    </body></html>";
    $mail->AltBody = "Devolución de {$nombre_cliente}: {$venta['producto']} — Recogida: {$fecha_recogida_fmt} en {$punto_recogida}";
    $mail->send();
} catch (Exception $e) {
    error_log('Error correo admin devolución: ' . $mail->ErrorInfo);
}

echo "<script>alert('Tu solicitud de devolución se ha registrado. Te hemos enviado una confirmación por correo.'); window.location.href='devolucion.php';</script>";
exit();