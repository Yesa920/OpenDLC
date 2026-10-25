<?php
session_start();

// Si no hay correo en sesión, redirigir al paso 1
if (empty($_SESSION['recuperar_correo'])) {
    header("Location: recuperar_contrasena.php");
    exit();
}

$error  = $_SESSION['recuperar_error'] ?? '';
$correo = $_SESSION['recuperar_correo'];
unset($_SESSION['recuperar_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verificar código | OpenDLC</title>
  <link rel="stylesheet" href="../../css/UID.css">
  <style>
    .verificar-card {
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
    .subtitulo {
      color: var(--pro-muted, #365983);
      font-size: 14px;
      margin: 0 0 20px 0;
    }
    .correo-badge {
      background: #e8f1fd;
      border: 1px solid #a7c4e7;
      color: #1d426e;
      border-radius: 8px;
      padding: 8px 12px;
      font-size: 14px;
      margin-bottom: 18px;
      display: inline-block;
    }
    .input-password-wrapper {
      position: relative;
      display: flex;
      align-items: stretch;
      width: 100%;
    }
    .input-password-wrapper input[type="password"],
    .input-password-wrapper input[type="text"] {
      width: 100% !important;
      padding-right: 42px !important;
      box-sizing: border-box !important;
      margin: 0 !important;
    }
    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: none !important;
      border: none !important;
      box-shadow: none !important;
      cursor: pointer;
      padding: 0;
      margin: 0;
      width: auto !important;
      height: auto !important;
      color: #64748b;
      display: flex;
      align-items: center;
      justify-content: center;
      outline: none;
      z-index: 2;
    }
    .toggle-password:hover { color: #1f3b82; }
    .input-password-wrapper + label { margin-top: 15px; display: block; }
  </style>
</head>
<body>
  <div class="verificar-card card" style="padding: 30px;">
    <p style="margin:0 0 4px 0; color:#2f80ed; font-weight:700; font-size:0.78rem; text-transform:uppercase; letter-spacing:0.06em;">OpenDLC</p>
    <h2 style="margin: 0 0 8px 0;">Verificar código</h2>
    <p class="subtitulo">Hemos enviado un código de 6 dígitos a:</p>
    <span class="correo-badge">📧 <?= htmlspecialchars($correo) ?></span>

    <?php if ($error): ?>
      <div class="msg-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="recuperar_handler.php">
      <label for="codigo">Código de verificación</label>
      <input type="text" id="codigo" name="codigo" required maxlength="6" placeholder="123456"
             style="letter-spacing: 0.3em; font-size: 20px; text-align: center;" />

      <label for="nueva_contrasena">Nueva contraseña</label>
      <div class="input-password-wrapper">
        <input type="password" id="nueva_contrasena" name="nueva_contrasena" required />
        <button type="button" class="toggle-password" onclick="togglePass('nueva_contrasena', this)">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
          </svg>
        </button>
      </div>

      <label for="confirmar_contrasena" style="margin-top:15px;">Confirmar nueva contraseña</label>
      <div class="input-password-wrapper">
        <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required />
        <button type="button" class="toggle-password" onclick="togglePass('confirmar_contrasena', this)">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
          </svg>
        </button>
      </div>

      <input type="hidden" name="paso" value="verificar_codigo">
      <input type="submit" value="Cambiar contraseña" style="margin-top: 18px;">
    </form>

    <a href="recuperar_contrasena.php">← Cambiar correo</a>
  </div>

  <script>
    const SVG_EYE = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
    const SVG_EYE_OFF = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;

    function togglePass(inputId, btn) {
      const input = document.getElementById(inputId);
      const visible = input.type === 'text';
      input.type = visible ? 'password' : 'text';
      btn.innerHTML = visible ? SVG_EYE : SVG_EYE_OFF;
    }
  </script>
</body>
</html>
