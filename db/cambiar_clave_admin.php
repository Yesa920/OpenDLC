<?php
/*
 * cambiar_clave_admin.php - Pone una contrasena nueva (ya con hash) a un administrador.
 *
 * Uso:
 *     docker compose exec web php db/cambiar_clave_admin.php Antonio NuevaClaveLarga123
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit("Solo desde la terminal.\n"); }
if ($argc !== 3) { exit("Uso: php db/cambiar_clave_admin.php USUARIO NUEVA_CLAVE\n"); }

[, $usuario, $clave] = $argv;
mysqli_report(MYSQLI_REPORT_OFF);
$c = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
if ($c->connect_error) { exit("No se pudo conectar: " . $c->connect_error . "\n"); }

$hash = password_hash($clave, PASSWORD_DEFAULT);
$st = $c->prepare("UPDATE administradores SET contrasegna = ? WHERE usuario = ?");
$st->bind_param('ss', $hash, $usuario);
$st->execute();
echo ($st->affected_rows > 0) ? "Hecho: contrasena de '$usuario' actualizada.\n"
                              : "No se cambio nada (el usuario '$usuario' no existe o ya tenia esa misma clave).\n";
