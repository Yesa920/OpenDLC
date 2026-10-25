<?php
require_once __DIR__ . '/../../auth.php'; requerir_admin();
$conexion = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));

/* 1. PRODUCTO CON MÁS UNIDADES (TOP VENTAS) */
$res_unidades = $conexion->query("
    SELECT producto, SUM(cantidad) AS total_unidades
    FROM ventas
    GROUP BY producto
    ORDER BY total_unidades DESC
    LIMIT 1
");

/* 2. PRODUCTO CON MÁS DINERO (TOP RECAUDACIÓN) */
$res_dinero = $conexion->query("
    SELECT producto, SUM(total) AS total_dinero
    FROM ventas
    GROUP BY producto
    ORDER BY total_dinero DESC
    LIMIT 1
");

$top_unidades = $res_unidades->fetch_assoc();
$top_dinero = $res_dinero->fetch_assoc();

if ($top_unidades && $top_dinero) {
    echo "
    <div style='display: flex; gap: 20px; justify-content: center; margin-bottom: 20px; font-family: sans-serif;'>
        <div style='background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 5px solid #ffc107;'>
            🏆 <strong>Líder en unidades:</strong><br>
            " . $top_unidades['producto'] . " (" . $top_unidades['total_unidades'] . " uds)
        </div>
        <div style='background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 5px solid #28a745;'>
            💰 <strong>Líder en ingresos:</strong><br>
            " . $top_dinero['producto'] . " (" . number_format($top_dinero['total_dinero'], 2) . " €)
        </div>
    </div>";
}
else {
    echo "No hay ventas todavía";
}
?>