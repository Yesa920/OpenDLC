<?php
require_once __DIR__ . '/../../auth.php'; requerir_admin();
$conexion = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));

/* ADMIN */
// Añadimos SUM(cantidad) a la consulta
$res_admin = $conexion->query("
    SELECT id_administrador, SUM(total) as total, SUM(cantidad) as unidades
    FROM ventas
    GROUP BY id_administrador
");

$labels_admin = [];
$datos_admin = [];
$unidades_admin = []; // Nuevo array

while ($fila = $res_admin->fetch_assoc()) {
    $labels_admin[] = "Admin " . $fila['id_administrador'];
    $datos_admin[] = (float)$fila['total'];
    $unidades_admin[] = (int)$fila['unidades']; // Guardamos las unidades
}

/* TIPO HARDWARE */
// Añadimos SUM(cantidad) también aquí
$res_tipo = $conexion->query("
    SELECT tipoHardware, SUM(total) as total, SUM(cantidad) as unidades
    FROM ventas
    GROUP BY tipoHardware
");

$labels_tipo = [];
$datos_tipo = [];
$unidades_tipo = []; // Nuevo array

while ($fila = $res_tipo->fetch_assoc()) {
    $labels_tipo[] = $fila['tipoHardware'];
    $datos_tipo[] = (float)$fila['total'];
    $unidades_tipo[] = (int)$fila['unidades']; // Guardamos las unidades
}

echo json_encode([
    "admin" => [
        "labels" => $labels_admin, 
        "datos" => $datos_admin, 
        "unidades" => $unidades_admin // Lo enviamos al JS
    ],
    "tipo" => [
        "labels" => $labels_tipo, 
        "datos" => $datos_tipo, 
        "unidades" => $unidades_tipo // Lo enviamos al JS
    ]
]);
?>