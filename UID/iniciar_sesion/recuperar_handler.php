<?php
session_start();
require_once __DIR__ . '/../../limitador.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../carrito/Tarjetas/PHPMailer-master/PHPMailer-master/src/Exception.php';
require '../../carrito/Tarjetas/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require '../../carrito/Tarjetas/PHPMailer-master/PHPMailer-master/src/SMTP.php';

$paso = $_POST['paso'] ?? '';

// ─────────────────────────────────────────────
// PASO 1: recibir correo y enviar código
// ─────────────────────────────────────────────
if ($paso === 'enviar_codigo') {

    $correo = trim($_POST['correo'] ?? '');

    if ($correo === '') {
        $_SESSION['recuperar_error'] = 'Introduce un correo válido.';
        header("Location: recuperar_contrasena.php");
        exit();
    }

    // Limite: 3 codigos por correo y 5 por conexion cada 15 minutos (evita llenar de correos a alguien)
    $claveCorreo = 'recuperar|' . mb_strtolower($correo);
    $claveIpRec  = 'recuperar-ip|' . lim_ip();
    if (lim_excedido($claveCorreo, 3, 900) || lim_excedido($claveIpRec, 5, 900)) {
        $_SESSION['recuperar_error'] = 'Has pedido demasiados códigos. Espera 15 minutos.';
        header("Location: recuperar_contrasena.php");
        exit();
    }
    lim_registrar($claveCorreo, 900);
    lim_registrar($claveIpRec, 900);

    $conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
    $correoSafe = mysqli_real_escape_string($conexion, $correo);

    $res = mysqli_query($conexion, "SELECT id_cliente, nombre FROM cliente WHERE email='$correoSafe' LIMIT 1");

    if (!$res || mysqli_num_rows($res) === 0) {
        mysqli_close($conexion);
        // Mensaje neutro por seguridad (no revelar si el correo existe)
        $_SESSION['recuperar_ok'] = 'Si ese correo está registrado, recibirás un código en breve.';
        header("Location: recuperar_contrasena.php");
        exit();
    }

    $cliente = mysqli_fetch_assoc($res);
    mysqli_close($conexion);

    // Generar código de 6 dígitos
    $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Guardar en sesión con expiración de 15 minutos
    $_SESSION['recuperar_correo']  = $correo;
    $_SESSION['recuperar_codigo']  = $codigo;
    $_SESSION['recuperar_expira']  = time() + (15 * 60);
    $_SESSION['recuperar_intentos'] = 0;   // intentos fallidos con ESTE codigo
    $_SESSION['recuperar_nombre']  = $cliente['nombre'];

    // Enviar correo con PHPMailer
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('MAIL_USER');
        $mail->Password   = getenv('MAIL_PASS');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(getenv('MAIL_USER'), 'OpenDLC');
        $mail->addAddress($correo, $cliente['nombre']);
        $mail->isHTML(true);
        $mail->Subject = 'Código de verificación - OpenDLC';
        $mail->Body = "
          <html>
          <body style='font-family: Segoe UI, Arial, sans-serif; background:#f0f6ff; padding:30px;'>
            <div style='max-width:480px; margin:0 auto; background:#fff; border-radius:12px; padding:32px; box-shadow:0 4px 20px rgba(29,95,184,0.12);'>
              <p style='color:#2f80ed; font-weight:700; font-size:12px; text-transform:uppercase; letter-spacing:1px; margin:0 0 8px 0;'>OpenDLC</p>
              <h2 style='color:#0d2f58; margin:0 0 16px 0;'>Recuperación de contraseña</h2>
              <p style='color:#365983;'>Hola <strong>{$cliente['nombre']}</strong>, hemos recibido una solicitud para restablecer tu contraseña.</p>
              <p style='color:#365983;'>Tu código de verificación es:</p>
              <div style='background:#eef6ff; border:2px solid #9dbce2; border-radius:10px; padding:20px; text-align:center; margin:20px 0;'>
                <span style='font-size:38px; font-weight:700; color:#1d5fb8; letter-spacing:0.4em;'>{$codigo}</span>
              </div>
              <p style='color:#365983; font-size:13px;'>Este código expira en <strong>15 minutos</strong>. Si no solicitaste este cambio, ignora este mensaje.</p>
              <hr style='border:none; border-top:1px solid #c9def8; margin:20px 0;'>
              <p style='color:#9dbce2; font-size:12px; text-align:center;'>© " . date('Y') . " OpenDLC — Todos los derechos reservados.</p>
            </div>
          </body>
          </html>
        ";

        $mail->send();

    } catch (Exception $e) {
        $_SESSION['recuperar_error'] = 'Error al enviar el correo. Inténtalo de nuevo.';
        header("Location: recuperar_contrasena.php");
        exit();
    }

    header("Location: verificar_codigo.php");
    exit();
}

// ─────────────────────────────────────────────
// PASO 2: verificar código y cambiar contraseña
// ─────────────────────────────────────────────
if ($paso === 'verificar_codigo') {

    $codigo_introducido = trim($_POST['codigo'] ?? '');
    $nueva              = trim($_POST['nueva_contrasena'] ?? '');
    $confirmar          = trim($_POST['confirmar_contrasena'] ?? '');

    // Comprobar que la sesión de recuperación existe
    if (empty($_SESSION['recuperar_codigo']) || empty($_SESSION['recuperar_correo'])) {
        $_SESSION['recuperar_error'] = 'La sesión ha expirado. Vuelve a solicitar el código.';
        header("Location: recuperar_contrasena.php");
        exit();
    }

    // Comprobar expiración
    if (time() > $_SESSION['recuperar_expira']) {
        unset($_SESSION['recuperar_codigo'], $_SESSION['recuperar_correo'], $_SESSION['recuperar_expira']);
        $_SESSION['recuperar_error'] = 'El código ha expirado. Solicita uno nuevo.';
        header("Location: recuperar_contrasena.php");
        exit();
    }

    // Comprobar código
    if (!hash_equals((string)$_SESSION['recuperar_codigo'], (string)$codigo_introducido)) {
        $_SESSION['recuperar_intentos'] = ($_SESSION['recuperar_intentos'] ?? 0) + 1;
        if ($_SESSION['recuperar_intentos'] >= 5) {
            // 5 fallos: el codigo se anula y hay que pedir uno nuevo
            unset($_SESSION['recuperar_codigo'], $_SESSION['recuperar_correo'], $_SESSION['recuperar_expira']);
            $_SESSION['recuperar_error'] = 'Demasiados intentos. Solicita un código nuevo.';
            header("Location: recuperar_contrasena.php");
            exit();
        }
        $_SESSION['recuperar_error'] = 'El código introducido no es correcto.';
        header("Location: verificar_codigo.php");
        exit();
    }

    // Comprobar contraseñas
    if ($nueva === '') {
        $_SESSION['recuperar_error'] = 'La contraseña no puede estar vacía.';
        header("Location: verificar_codigo.php");
        exit();
    }

    if (strlen($nueva) < 8) {
        $_SESSION['recuperar_error'] = 'La contraseña debe tener al menos 8 caracteres.';
        header("Location: verificar_codigo.php");
        exit();
    }

    if ($nueva !== $confirmar) {
        $_SESSION['recuperar_error'] = 'Las contraseñas no coinciden.';
        header("Location: verificar_codigo.php");
        exit();
    }

    // Actualizar en BD
    $conexion   = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
    $correo     = $_SESSION['recuperar_correo'];
    $correoSafe = mysqli_real_escape_string($conexion, $correo);

    // Se guarda el hash (bcrypt + sal aleatoria), nunca la contrasena en claro
    $hash = password_hash($nueva, PASSWORD_DEFAULT);
    $stmtUp = $conexion->prepare("UPDATE cliente SET contrasegna = ? WHERE email = ?");
    $stmtUp->bind_param('ss', $hash, $correo);
    $stmtUp->execute();
    $stmtUp->close();
    mysqli_close($conexion);

    // Limpiar sesión de recuperación
    unset($_SESSION['recuperar_codigo'], $_SESSION['recuperar_correo'],
          $_SESSION['recuperar_expira'], $_SESSION['recuperar_nombre']);

    // Redirigir al login con mensaje de éxito
    $_SESSION['recuperar_ok'] = '¡Contraseña cambiada correctamente! Ya puedes iniciar sesión.';
    header("Location: acceso.php");
    exit();
}

header("Location: recuperar_contrasena.php");
exit();
?>
