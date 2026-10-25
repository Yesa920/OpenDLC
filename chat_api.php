<?php
/*
 * chat_api.php - API del chat para los visitantes/clientes.
 *   POST ?accion=enviar  {pregunta, respuesta}  -> guarda el mensaje y lo responde la IA
 *   GET  ?accion=mios                           -> devuelve SOLO los mensajes de esta persona
 * El identificador del usuario lo decide el servidor (la sesion), no el navegador.
 *
 * Flujo de "enviar":
 *   1. Se guarda la pregunta (estado "pensando").
 *   2. Si pide un administrador -> queda pendiente para el panel de admin.
 *   3. Si no, responde la IA. Si la IA no sabe ([[ADMIN]]) -> pendiente para el admin.
 *   4. Si la IA no esta disponible -> se usa la respuesta por palabras clave; si no hay -> admin.
 */
session_start();
require_once __DIR__ . '/chat_lib.php';
require_once __DIR__ . '/chat_ia.php';
require_once __DIR__ . '/limitador.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$uid = isset($_SESSION['id_cliente'])
    ? (string)(int)$_SESSION['id_cliente']
    : 'invitado-' . substr(hash('sha256', session_id()), 0, 8);

$accion = $_GET['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'enviar') {
    $in = json_decode(file_get_contents('php://input'), true) ?: [];
    $pregunta = mb_substr(trim((string)($in['pregunta'] ?? '')), 0, 500);
    $reserva  = mb_substr((string)($in['respuesta'] ?? ''), 0, 1000);   // respuesta por palabras clave
    if ($pregunta === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Mensaje vacio']);
        exit();
    }

    // Limite: 30 mensajes cada 10 minutos por conexion
    if (lim_excedido('chat|' . lim_ip(), 30, 600)) {
        http_response_code(429);
        echo json_encode(['error' => 'Demasiados mensajes. Espera un poco.']);
        exit();
    }
    lim_registrar('chat|' . lim_ip(), 600);

    // 1) Guardar la pregunta enseguida (asi el cliente la ve y aparece "Escribiendo...")
    $g = chat_flask('POST', '/mensaje', ['user_id' => $uid, 'pregunta' => $pregunta, 'estado' => 'pensando']);
    $id = $g['id'] ?? null;
    if (!$id) {
        http_response_code(503);
        echo json_encode(['error' => 'Chat no disponible']);
        exit();
    }

    // 2) Decidir quien responde
    $respuesta = null;
    $origen = '';
    $pideAdmin = preg_match('/\b(admin|administrador|administradora|humano)\b/iu', $pregunta) === 1;

    if (!$pideAdmin) {
        // Contexto: las ultimas 3 preguntas ya respondidas de esta misma persona
        $previos = chat_flask('GET', '/mensajes?user_id=' . rawurlencode($uid)) ?? [];
        $historial = array_values(array_filter($previos, fn($m) => ($m['id'] ?? '') !== $id && ($m['respuesta'] ?? '') !== ''));
        $historial = array_slice($historial, -3);

        $ia = ia_responder($pregunta, $historial);
        if ($ia === '[[ADMIN]]') {
            $respuesta = null;                     // la IA no sabe -> administrador
        } elseif ($ia !== null) {
            $respuesta = $ia;
            $origen = 'ia';
        } elseif ($reserva !== '') {
            $respuesta = $reserva;                 // IA caida o sin configurar -> palabras clave
            $origen = 'auto';
        }
    }

    // 3) Guardar el resultado
    if ($respuesta !== null) {
        chat_flask('POST', '/responder', ['id' => $id, 'respuesta' => $respuesta, 'origen' => $origen, 'estado' => 'respondido']);
    } else {
        chat_flask('POST', '/responder', ['id' => $id, 'respuesta' => '', 'estado' => 'pendiente']);
    }
    echo json_encode(['ok' => true, 'estado' => $respuesta !== null ? 'respondido' : 'pendiente']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'mios') {
    $r = chat_flask('GET', '/mensajes?user_id=' . rawurlencode($uid)) ?? [];
    foreach ($r as &$m) {
        // Si algo fallo a mitad y lleva mas de 60 s "pensando", pasa a pendiente de administrador
        if (($m['estado'] ?? '') === 'pensando' && time() - (int)($m['timestamp'] ?? 0) > 60) {
            $m['estado'] = 'pendiente';
        }
    }
    unset($m);
    echo json_encode($r);
    exit();
}

http_response_code(400);
echo json_encode(['error' => 'Accion no valida']);
