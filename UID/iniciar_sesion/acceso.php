<?php
session_start();

// Recuperar datos y error del intento de registro fallido
$registro_error = $_SESSION['registro_error'] ?? '';
$recuperar_ok   = $_SESSION['recuperar_ok']   ?? '';
$d = $_SESSION['registro_datos'] ?? [];
unset($_SESSION['registro_error'], $_SESSION['registro_datos'], $_SESSION['recuperar_ok']);

// Error del login y usuario escrito (la contrasena nunca se devuelve al formulario)
$login_error = $_SESSION['login_error'] ?? '';
$v_usuario   = htmlspecialchars($_SESSION['login_usuario'] ?? '');
unset($_SESSION['login_error'], $_SESSION['login_usuario']);
$demo_activo = getenv('DEMO_MODE') === '1';

$v_nombre   = htmlspecialchars($d['nombre']   ?? '');
$v_apellido = htmlspecialchars($d['apellido'] ?? '');
$v_cp       = htmlspecialchars($d['cp']       ?? '');
$v_correo   = htmlspecialchars($d['correo']   ?? '');
$v_telefono = htmlspecialchars($d['telefono'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso profesional | OpenDLC</title>
  <link rel="stylesheet" href="../../css/UID.css">
  <style>
    .error-registro {
      background: #fee2e2;
      color: #b91c1c;
      border: 1px solid #fca5a5;
      border-radius: 6px;
      padding: 10px 14px;
      margin-bottom: 14px;
      font-size: 14px;
    }
    .msg-ok-acceso {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #86efac;
      border-radius: 6px;
      padding: 10px 14px;
      margin-bottom: 14px;
      font-size: 14px;
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
    .toggle-password:hover {
      color: #1f3b82;
    }
  </style>
</head>
<body>
  <div class="container access-layout">
    <section class="access-hero card">
      <p class="access-kicker">OpenDLC</p>
      <h1>Acceso unificado para clientes y administradores</h1>
      <p class="access-lead">
        Entra a tu cuenta o crea una nueva para comprar, gestionar pedidos y administrar catalogo.
        Toda la informacion de negocio y valoraciones esta en la pagina principal publica.
      </p>
      <div class="access-badges">
        <span><i class="dot dot-secure"></i>Acceso seguro</span>
        <span><i class="dot dot-profile"></i>Perfil unificado</span>
        <span><i class="dot dot-support"></i>Soporte rapido</span>
      </div>

      <div class="access-shortcuts">
        <a href="../../Cliente_sinIniciarSesion.php" class="botonAbajo">Ir a la tienda publica</a>
        <a href="../../Ayuda/formularioAyuda.php" class="botonAbajo">Necesito ayuda</a>
      </div>
    </section>

    <section class="access-forms">
      <form method="post" action="acceso_handler.php" class="card access-form">
        <h2>Iniciar sesion</h2>

        <?php if ($recuperar_ok): ?>
          <div class="msg-ok-acceso">✓ <?= htmlspecialchars($recuperar_ok) ?></div>
        <?php endif; ?>

        <?php if ($login_error): ?>
          <div class="error-registro">⚠️ <?= htmlspecialchars($login_error) ?></div>
        <?php endif; ?>

        <label for="usuario_login">Usuario o correo</label>
        <input type="text" id="usuario_login" name="usuario_login" value="<?= $v_usuario ?>" required <?= $login_error ? 'autofocus' : '' ?> />

        <label for="clave_login">Contrasena</label>
        <div class="input-password-wrapper">
          <input type="password" id="clave_login" name="clave_login" required />
          <button type="button" class="toggle-password" onclick="togglePass('clave_login', this)" title="Mostrar/ocultar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>

        <a href="recuperar_contrasena.php" style="font-size:13px; margin: 0 0 10px 0; display:inline-block;">¿Olvidaste tu contraseña?</a>

        <input type="hidden" name="accion" value="login">
        <input type="submit" value="Entrar">
      </form>

      <?php if ($demo_activo): ?>
      <form method="post" action="acceso_handler.php" class="card access-form" style="text-align:center;">
        <h2>¿Solo quieres echar un vistazo?</h2>
        <p style="font-size:14px;">Entra con una cuenta de demostración, sin registrarte.</p>
        <input type="hidden" name="accion" value="demo_cliente">
        <input type="submit" value="▶ Entrar como cliente demo">
      </form>
      <?php endif; ?>

      <form method="post" action="acceso_handler.php" class="card access-form">
        <h2>Registro de cliente</h2>

        <?php if ($registro_error): ?>
          <div class="error-registro">⚠️ <?= htmlspecialchars($registro_error) ?></div>
        <?php endif; ?>

        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" value="<?= $v_nombre ?>" maxlength="50" required />

        <label for="apellido">Apellido</label>
        <input type="text" id="apellido" name="apellido" value="<?= $v_apellido ?>" maxlength="50" required />

        <label for="cp">Codigo Postal</label>
        <input type="text" id="cp" name="cp" value="<?= $v_cp ?>" required pattern="\d{5}" minlength="5" maxlength="5" title="Codigo de 5 digitos." />

        <label for="correo">Correo</label>
        <input type="email" id="correo" name="correo" value="<?= $v_correo ?>" required />

        <label for="telefono">Telefono</label>
        <input type="tel" id="telefono" name="telefono" value="<?= $v_telefono ?>" maxlength="9" />

        <label for="contrasena_registro">Contrasena</label>
        <div class="input-password-wrapper">
          <input type="password" id="contrasena_registro" name="contrasena_registro" required />
          <button type="button" class="toggle-password" onclick="togglePass('contrasena_registro', this)" title="Mostrar/ocultar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>

        <label for="contrasena_again">Confirmar contrasena</label>
        <div class="input-password-wrapper">
          <input type="password" id="contrasena_again" name="contrasena_again" required />
          <button type="button" class="toggle-password" onclick="togglePass('contrasena_again', this)" title="Mostrar/ocultar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>

        <input type="hidden" name="accion" value="registro_cliente">
        <input type="submit" value="Crear cuenta">
      </form>
    </section>

    <p class="access-help">
      Si necesitas ayuda adicional, usa
      <a href="../../Ayuda/formularioAyuda.php">Ayuda / Quejas</a>.
    </p>
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