<?php
/*
 * chat_admin_api.php - API del chat SOLO para administradores.
 *   GET           -> todos los mensajes
 *   POST {id, respuesta} -> responde a un mensaje
 */
require_once __DIR__ . '/../auth.php';
requerir_admin();
require_once __DIR__ . '/../chat_lib.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?: [];
    $r = chat_flask('POST', '/responder', [
        'id'        => (string)($in['id'] ?? ''),
        'respuesta' => mb_substr((string)($in['respuesta'] ?? ''), 0, 1000),
    ]);
    echo json_encode($r ?? ['error' => 'Chat no disponible']);
    exit();
}

echo json_encode(chat_flask('GET', '/mensajes') ?? []);
