<?php
require_once __DIR__ . '/../../auth.php'; requerir_admin();
$conexion = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));

$res_u_admin = $conexion->query("SELECT id_administrador, SUM(cantidad) AS total_u FROM ventas GROUP BY id_administrador ORDER BY total_u DESC LIMIT 1");
$res_d_admin = $conexion->query("SELECT id_administrador, SUM(total) AS total_d FROM ventas GROUP BY id_administrador ORDER BY total_d DESC LIMIT 1");

$top_u = $res_u_admin->fetch_assoc();
$top_d = $res_d_admin->fetch_assoc();

if ($top_u && $top_d) {
    echo "
    <div style='display: flex; gap: 15px; justify-content: center; margin-top: 10px;'>
        <div style='background: #fff; padding: 10px; border-radius: 8px; border-left: 5px solid #3498db; box-shadow: 0 2px 4px rgba(0,0,0,0.05); flex: 1; text-align: left;'>
            👤 <strong>Más ventas:</strong><br>
            Admin {$top_u['id_administrador']} ({$top_u['total_u']} uds)
        </div>
        <div style='background: #fff; padding: 10px; border-radius: 8px; border-left: 5px solid #9b59b6; box-shadow: 0 2px 4px rgba(0,0,0,0.05); flex: 1; text-align: left;'>
            💵 <strong>Más recaudado:</strong><br>
            Admin {$top_d['id_administrador']} (" . number_format($top_d['total_d'], 2) . " €)
        </div>
    </div>";
}else {
    echo "No hay ventas todavía";
}
?>
