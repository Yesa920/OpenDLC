<?php
session_start();
require_once __DIR__ . '/auth.php'; requerir_cliente();
include 'chatbot.php';

if (!isset($_SESSION['id_cliente'])) {
    header('Location: UID/iniciar_sesion/acceso.php');
    exit();
}

$id_cliente = $_SESSION['id_cliente'];
$conexion = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
if ($conexion->connect_error) {
    die('Error de conexión: ' . $conexion->connect_error);
}

// (Las tablas y columnas ya vienen creadas en db/init/pccomponentes.sql: la web no necesita
// permiso para crear ni alterar tablas.)

$stmt = $conexion->prepare(
    "SELECT v.id, v.codigo_producto, v.producto, v.tipoHardware, v.cantidad, v.precio, v.total, v.fecha,
            d.estado AS devolucion_estado, d.fecha_horario, d.punto_recogida,
            val.puntuacion AS mi_puntuacion, val.comentario AS mi_comentario
     FROM ventas v
     LEFT JOIN devoluciones d ON d.id_venta = v.id AND d.id_cliente = v.id_cliente
     LEFT JOIN valoraciones val ON val.codigo_producto = v.codigo_producto AND val.id_cliente = v.id_cliente
     WHERE v.id_cliente = ?
       AND v.fecha >= DATE_SUB(NOW(), INTERVAL 1 DAY)
     ORDER BY v.fecha DESC"
);
$stmt->bind_param('i', $id_cliente);
$stmt->execute();
$result = $stmt->get_result();

$compras = [];
while ($fila = $result->fetch_assoc()) {
    $compras[] = $fila;
}
$stmt->close();

$puntos_recogida = [
    'Centro de recogida A — Madrid',
    'Centro de recogida B — Barcelona',
    'Centro de recogida C — Valencia',
    'Punto de entrega Exprés — Sevilla'
];

function generar_horarios_disponibles() {
    $slots = [];
    $hoy = new DateTime('today');
    $dias = [1, 2, 3, 4];
    $horas = ['10:00', '14:00', '18:00'];

    foreach ($dias as $dia) {
        $fecha = (clone $hoy)->modify("+$dia day");
        foreach ($horas as $hora) {
            [$h, $m] = explode(':', $hora);
            $fechaSlot = (clone $fecha)->setTime($h, $m, 0);
            $slots[$fechaSlot->format('Y-m-d H:i:s')] = $fechaSlot->format('d/m/Y H:i');
        }
    }
    return $slots;
}

$id_usuario = $_SESSION['id_cliente'] ?? null; 
$nombre_completo = "Invitado"; 
$horarios = generar_horarios_disponibles();


if ($id_usuario) {
    // Añadimos 'apellido' a la consulta SELECT
    $sql_user = "SELECT nombre, apellido FROM cliente WHERE id_cliente = '$id_usuario'"; 
    $res_user = mysqli_query($conexion, $sql_user);
    
    if ($res_user && $fila_user = mysqli_fetch_assoc($res_user)) {
        // Concatenamos nombre y apellido con un espacio
        $nombre_completo = $fila_user['nombre'] . " " . $fila_user['apellido'];
    }
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Devoluciones recientes</title>
<link rel="stylesheet" href="css/prueba_cliente.css">
</head>
<body>
<div class="navbar">
    <div class="nav-left">
        <a href="cliente.php" class="nav-back">← Volver</a>
    </div>
    <div class="nav-center">
        <h2 style="margin:0; color:#1f3b82;">Solicitar devolución</h2>
    </div>
    <div class="nav-right">
		<div class="perfil">
			  <button class="perfil-btn" onclick="toggleMenu()">👤</button>

			  <div class="dropdown">
				<div style="padding: 12px 15px; border-bottom: 1px solid #eee; background: #f8fafc;">
					<small style="color: #64748b; display: block; font-size: 11px; text-transform: uppercase;">Bienvenido:</small>
					<strong style="color: #1e293b; font-size: 14px;"><?php echo htmlspecialchars($nombre_completo); ?></strong>
				</div>
				<a href="carrito/carrito.php">Carrito</a>
				<a href="devolucion.php">Devoluciones/Valorar</a>
				<a href="UID/iniciar_sesion/acceso.php">Iniciar sesion con otra cuenta</a>
				<a href="Ayuda/formularioAyuda.php">Ayuda</a>
				<a href="logout.php">Cerrar sesion</a>
			  </div>
		</div>
    </div>
</div>

<div class="container">
    <div class="home-professional-strip" style="margin-top:16px;">
        <div class="strip-item">
            <h3>Últimas 24 horas</h3>
            <p>Solo aparecen compras realizadas en las últimas 24 horas. Si no ves tu pedido, revisa la fecha de compra o consulta con soporte.</p>
        </div>
        <div class="strip-item">
            <h3>Devolución a tu medida</h3>
            <p>Elige el punto de entrega y la hora disponible para llevar tu devolución sin depender de un administrador.</p>
        </div>
    </div>

    <div class="card" style="padding:24px;">
        <h1 class="title">Devoluciones recientes</h1>
        <p class="subtitle">Revisa tus compras elegibles y solicita la devolución en un solo clic.</p>

        <?php if (empty($compras)): ?>
            <div class="strip-item" style="margin-top:24px;">
                <p>No tienes compras con la condición de devolución para las últimas 24 horas.</p>
                <p>Visita el catálogo para comprar nuevos productos o contacta con soporte si crees que hay un error.</p>
            </div>
        <?php else: ?>
            <table class="return-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Cantidad</th>
                        <th>Precio unidad</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Punto de recogida</th>
                        <th>Fecha / Hora</th>
                        <th>Estado</th>
                        <th>Acción</th>
                        <th>Valorar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($compras as $item): ?>
                        <?php
                            $estado = $item['devolucion_estado'] ? $item['devolucion_estado'] : 'ELEGIBLE';
                            $puede = empty($item['devolucion_estado']);
                            $punto = $item['punto_recogida'] ? $item['punto_recogida'] : '-';
                            $fechaHora = $item['fecha_horario'] ? date('d/m/Y H:i', strtotime($item['fecha_horario'])) : '-';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['producto']); ?></td>
                            <td><?php echo htmlspecialchars($item['tipoHardware']); ?></td>
                            <td><?php echo intval($item['cantidad']); ?></td>
                            <td><?php echo number_format($item['precio'], 2); ?> €</td>
                            <td><?php echo number_format($item['total'], 2); ?> €</td>
                            <td><?php echo htmlspecialchars($item['fecha']); ?></td>
                            <td><?php echo htmlspecialchars($punto); ?></td>
                            <td><?php echo htmlspecialchars($fechaHora); ?></td>
                            <td>
                                <span class="status-pill <?php echo $estado === 'PENDIENTE' ? 'status-warning' : ($estado === 'PROGRAMADA' ? 'status-info' : ($estado === 'DEVUELTA' ? 'status-success' : 'status-info')); ?>">
                                    <?php echo htmlspecialchars($estado); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($puede): ?>
                                    <form method="post" action="procesar_devolucion.php" class="return-form">
                                        <input type="hidden" name="id_venta" value="<?php echo intval($item['id']); ?>">
                                        <select name="punto_recogida" required>
                                            <option value="">Elegir punto de recogida</option>
                                            <?php foreach ($puntos_recogida as $puntoOption): ?>
                                                <option value="<?php echo htmlspecialchars($puntoOption); ?>"><?php echo htmlspecialchars($puntoOption); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <select name="fecha_hora" required>
                                            <option value="">Elegir fecha/hora disponible</option>
                                            <?php foreach ($horarios as $slotValue => $slotLabel): ?>
                                                <option value="<?php echo htmlspecialchars($slotValue); ?>"><?php echo htmlspecialchars($slotLabel); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn-carrito">Reservar devolución</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn-carrito" disabled>Reservada</button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['mi_puntuacion']): ?>
                                    <div style="text-align:center;">
                                        <div style="color:#f59e0b; font-size:18px;">
                                            <?php for ($i=1; $i<=5; $i++) echo ($i <= $item['mi_puntuacion']) ? '⭐' : '☆'; ?>
                                        </div>
                                        <?php if ($item['mi_comentario']): ?>
                                            <p style="font-size:11px; color:#64748b; margin:4px 0 0 0; font-style:italic;">"<?php echo htmlspecialchars($item['mi_comentario']); ?>"</p>
                                        <?php endif; ?>
                                        <p style="font-size:11px; color:#22c55e; margin:4px 0 0 0;">✓ Valorado</p>
                                    </div>
                                <?php else: ?>
                                    <form method="POST" action="valorar.php" style="min-width:180px;">
                                        <input type="hidden" name="producto_id" value="<?php echo intval($item['codigo_producto']); ?>">
                                        <input type="hidden" name="desde_devolucion" value="1">
                                        <div class="rating-container" style="flex-direction:column; gap:6px;">
                                            <div class="stars">
                                                <input type="radio" id="star5_<?php echo $item['codigo_producto']; ?>" name="puntuacion" value="5" required />
                                                <label for="star5_<?php echo $item['codigo_producto']; ?>" title="5 estrellas">★</label>
                                                <input type="radio" id="star4_<?php echo $item['codigo_producto']; ?>" name="puntuacion" value="4" />
                                                <label for="star4_<?php echo $item['codigo_producto']; ?>" title="4 estrellas">★</label>
                                                <input type="radio" id="star3_<?php echo $item['codigo_producto']; ?>" name="puntuacion" value="3" />
                                                <label for="star3_<?php echo $item['codigo_producto']; ?>" title="3 estrellas">★</label>
                                                <input type="radio" id="star2_<?php echo $item['codigo_producto']; ?>" name="puntuacion" value="2" />
                                                <label for="star2_<?php echo $item['codigo_producto']; ?>" title="2 estrellas">★</label>
                                                <input type="radio" id="star1_<?php echo $item['codigo_producto']; ?>" name="puntuacion" value="1" />
                                                <label for="star1_<?php echo $item['codigo_producto']; ?>" title="1 estrella">★</label>
                                            </div>
                                            <textarea name="comentario" placeholder="Escribe un comentario (opcional)..." style="width:100%; font-size:12px; padding:5px; border-radius:6px; border:1px solid #cbd5e1; resize:vertical; min-height:50px;"></textarea>
                                            <button type="submit" class="btn-valorar" style="margin-top:4px;">Enviar valoración</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
<script>
function toggleMenu() {
  document.querySelector(".perfil").classList.toggle("active");
}

document.addEventListener("click", function(e) {
  const perfil = document.querySelector(".perfil");
  if (!perfil.contains(e.target)) {
    perfil.classList.remove("active");
  }
});

let user_id = "<?php echo $id_usuario ?? 'invitado'; ?>";

// Función para cambiar cantidad con botones +/-
function cambiarCantidad(btn, cambio) {
  const form = btn.closest('.form-carrito');
  const input = form.querySelector('.input-cantidad');
  let cantidad = parseInt(input.value);
  const max = parseInt(input.max);
  
  cantidad += cambio;
  if (cantidad < 1) cantidad = 1;
  if (cantidad > max) cantidad = max;
  
  input.value = cantidad;
}
</script>
</html>
