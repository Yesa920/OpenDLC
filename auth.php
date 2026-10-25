<?php
/*
 * auth.php - Control de acceso de la tienda.
 *
 * Se incluye al principio de cada pagina que necesita sesion:
 *   requerir_cliente();  -> paginas de cliente (carrito, pago, devoluciones...)
 *   requerir_admin();    -> panel de administracion (insert, update, delete...)
 *
 * Si no hay sesion valida, redirige SIEMPRE al inicio de sesion.
 * "Valida" = la sesion tiene un id Y ese id existe en la base de datos
 * (un COUNT(*) que tiene que dar exactamente 1).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Las paginas protegidas no se guardan en la cache del navegador
header('Cache-Control: no-store');

// Ruta al login calculada desde donde esta este archivo, asi funciona igual
// en Docker (http://localhost:8080/) que en una subcarpeta de XAMPP.
function auth_url_login() {
    $raiz = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $aqui = str_replace('\\', '/', __DIR__);
    $base = ($raiz !== '' && strpos($aqui, $raiz) === 0) ? substr($aqui, strlen($raiz)) : '';
    return $base . '/UID/iniciar_sesion/acceso.php';
}

function auth_redirigir_a_login() {
    header('Location: ' . auth_url_login());
    exit();
}

// Cuenta cuantas filas tienen ese id. $tabla y $columna los escribo yo
// en este archivo (nunca vienen del usuario); el id va como parametro.
function auth_existe($tabla, $columna, $id) {
    try {
        $c = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
        $stmt = $c->prepare("SELECT COUNT(*) FROM $tabla WHERE $columna = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($n);
        $stmt->fetch();
        $stmt->close();
        $c->close();
        return (int)$n === 1;
    } catch (Throwable $e) {
        return false;   // si algo falla, mejor no dejar pasar
    }
}

function requerir_cliente() {
    $id = (int)($_SESSION['id_cliente'] ?? 0);
    if ($id > 0 && auth_existe('cliente', 'id_cliente', $id)) {
        return;
    }
    unset($_SESSION['id_cliente']);
    auth_redirigir_a_login();
}

function requerir_admin() {
    $id = (int)($_SESSION['id_administrador'] ?? 0);
    if ($id > 0 && auth_existe('administradores', 'id_administrador', $id)) {
        return;
    }
    unset($_SESSION['id_administrador']);
    auth_redirigir_a_login();
}
