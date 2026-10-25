<?php
// logout.php - Cierra la sesion de verdad (cliente o administrador) y vuelve a la tienda.
session_start();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
header('Cache-Control: no-store');
header('Location: Cliente_sinIniciarSesion.php');
exit();
