<?php
session_start();

$error = $_SESSION['recuperar_error'] ?? '';
$ok    = $_SESSION['recuperar_ok']    ?? '';
unset($_SESSION['recuperar_error'], $_SESSION['recuperar_ok']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar contraseña | OpenDLC</title>
  <link rel="stylesheet" href="../../css/UID.css">
  <style>
    .recuperar-card {
      max-width: 420px;
      margin: 60px auto;
    }
    .msg-error {
      background: #fee2e2;
      color: #b91c1c;
      border: 1px solid #fca5a5;
      border-radius: 8px;
      padding: 10px 14px;
      margin-bottom: 14px;
      font-size: 14px;
    }
    .msg-ok {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #86efac;
      border-radius: 8px;
      padding: 10px 14px;
      margin-bottom: 14px;
      font-size: 14px;
    }
    .subtitulo {
      color: var(--pro-muted, #365983);
      font-size: 14px;
      margin: 0 0 20px 0;
    }
  </style>
</head>
<body>
  <div class="recuperar-card card" style="padding: 30px;">
    <p style="margin:0 0 4px 0; color:#2f80ed; font-weight:700; font-size:0.78rem; text-transform:uppercase; letter-spacing:0.06em;">OpenDLC</p>
    <h2 style="margin: 0 0 8px 0;">Recuperar contraseña</h2>
    <p class="subtitulo">Introduce tu correo y te enviaremos un código de verificación.</p>

    <?php if ($error): ?>
      <div class="msg-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($ok): ?>
      <div class="msg-ok">✓ <?= htmlspecialchars($ok) ?></div>
    <?php endif; ?>

    <form method="post" action="recuperar_handler.php">
      <label for="correo">Correo electrónico</label>
      <input type="email" id="correo" name="correo" required placeholder="ejemplo@correo.com" />
      <input type="hidden" name="paso" value="enviar_codigo">
      <input type="submit" value="Enviar código">
    </form>

    <a href="acceso.php">← Volver al inicio de sesión</a>
  </div>
</body>
</html>
