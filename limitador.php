<?php
/*
 * limitador.php - Limita cuantas veces se puede intentar algo (login, recuperar clave, registro...).
 * Asi nadie puede probar miles de contrasenas o llenar la web de spam.
 *
 *   lim_excedido('login|ip|usuario', 5, 900)   -> true si ya hubo 5 intentos en los ultimos 15 min
 *   lim_registrar('login|ip|usuario', 900)     -> anota un intento
 *   lim_limpiar('login|ip|usuario')            -> borra los intentos (por ejemplo tras entrar bien)
 */

// IP real de la persona. Detras de un proxy (Caddy, ngrok) la IP real llega en X-Forwarded-For.
function lim_ip() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $publica = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    if (!$publica && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $primera = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        if (filter_var($primera, FILTER_VALIDATE_IP)) {
            $ip = $primera;
        }
    }
    return $ip;
}

function lim_fichero($clave) {
    return sys_get_temp_dir() . '/lim_' . hash('sha256', $clave);
}

// Intentos anotados dentro de la ventana (en segundos)
function lim_contar($clave, $ventana) {
    $f = lim_fichero($clave);
    if (!is_file($f)) { return 0; }
    $marcas = json_decode((string)@file_get_contents($f), true) ?: [];
    $limite = time() - $ventana;
    return count(array_filter($marcas, fn($t) => $t > $limite));
}

function lim_excedido($clave, $max, $ventana) {
    return lim_contar($clave, $ventana) >= $max;
}

function lim_registrar($clave, $ventana) {
    $fp = @fopen(lim_fichero($clave), 'c+');
    if (!$fp) { return; }
    flock($fp, LOCK_EX);
    $marcas = json_decode((string)stream_get_contents($fp), true) ?: [];
    $limite = time() - $ventana;
    $marcas = array_values(array_filter($marcas, fn($t) => $t > $limite));
    $marcas[] = time();
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($marcas));
    flock($fp, LOCK_UN);
    fclose($fp);
}

function lim_limpiar($clave) {
    @unlink(lim_fichero($clave));
}
