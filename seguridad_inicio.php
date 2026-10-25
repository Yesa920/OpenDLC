<?php
// Se ejecuta automaticamente antes de CADA pagina (auto_prepend_file en docker/web/php-app.ini).
// Si la web se visita por HTTPS (aunque llegue a traves de Caddy u otro proxy), la cookie
// de sesion se marca como "solo seguro": el navegador no la manda nunca por HTTP.
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
if ($https) {
    $_SERVER['HTTPS'] = 'on';
    ini_set('session.cookie_secure', '1');
}
