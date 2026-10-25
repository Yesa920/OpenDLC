<?php
/*
 * chat_lib.php - El navegador NUNCA habla con Flask. Habla con PHP (misma web),
 * y PHP habla con Flask por dentro de Docker usando un token secreto.
 */

// Ruta base de la web (vacia en http://localhost:8080/, "/carpeta" en una subcarpeta de XAMPP)
function chat_base_url() {
    $raiz = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $aqui = str_replace('\\', '/', __DIR__);
    return ($raiz !== '' && strpos($aqui, $raiz) === 0) ? substr($aqui, strlen($raiz)) : '';
}

// Llama a Flask. Devuelve el JSON ya convertido en array, o null si falla.
function chat_flask($metodo, $ruta, $datos = null) {
    $url = rtrim(getenv('CHAT_URL') ?: 'http://chatbot:5000', '/') . $ruta;
    $cabeceras = "X-Chat-Token: " . getenv('CHAT_TOKEN') . "\r\n";
    $opciones = ['http' => ['method' => $metodo, 'timeout' => 5, 'ignore_errors' => true]];
    if ($datos !== null) {
        $cabeceras .= "Content-Type: application/json\r\n";
        $opciones['http']['content'] = json_encode($datos, JSON_UNESCAPED_UNICODE);
    }
    $opciones['http']['header'] = $cabeceras;
    $r = @file_get_contents($url, false, stream_context_create($opciones));
    return ($r === false) ? null : json_decode($r, true);
}
