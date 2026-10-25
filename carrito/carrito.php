<?php
session_start();
require_once __DIR__ . '/../auth.php'; requerir_cliente();
include '../chatbot.php'; 
$conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));

$id_usuario = $_SESSION['id_cliente'] ?? null; 
$nombre_completo = "Invitado"; 

if ($id_usuario) {
    // Añadimos 'apellido' a la consulta SELECT
    $sql_user = "SELECT nombre, apellido FROM cliente WHERE id_cliente = '$id_usuario'"; 
    $res_user = mysqli_query($conexion, $sql_user);
    
    if ($res_user && $fila_user = mysqli_fetch_assoc($res_user)) {
        // Concatenamos nombre y apellido con un espacio
        $nombre_completo = $fila_user['nombre'] . " " . $fila_user['apellido'];
    }
}

// Si el usuario no ha iniciado sesión, lo mandamos al login
if (!isset($_SESSION['id_cliente'])) {
    header("Location: ../UID/iniciar_sesion/acceso.php");
    exit();
}

$id_cliente = $_SESSION['id_cliente'];
?>
<html>
<head>
    <link rel="stylesheet" href="../css/carrito.css">
    <style>
    </style>
</head>
<script>
function toggleMenu() {
    const perfil = document.querySelector(".perfil");
    perfil.classList.toggle("active");
}

document.addEventListener("click", function(e) {
    const perfil = document.querySelector(".perfil");

    if (!perfil.contains(e.target)) {
        perfil.classList.remove("active");
    }
});
</script>

<body>
<div class="page">
	<div class="navbar">

	  <!-- IZQUIERDA: volver -->
	  <div class="nav-left">
		<a href="../cliente.php" class="nav-back">← Volver</a>
	  </div>

	  <!-- DERECHA: usuario -->
	  <div class="nav-right">
			<div class="perfil">
			  <button class="perfil-btn" onclick="toggleMenu()">👤</button>

			  <div class="dropdown">
				<div style="padding: 12px 15px; border-bottom: 1px solid #eee; background: #f8fafc;">
					<small style="color: #64748b; display: block; font-size: 11px; text-transform: uppercase;">Bienvenido:</small>
					<strong style="color: #1e293b; font-size: 14px;"><?php echo htmlspecialchars($nombre_completo); ?></strong>
				</div>
				<a href="carrito.php">Carrito</a>
				<a href="../devolucion.php">Devoluciones/Valorar</a>
				<a href="../UID/iniciar_sesion/acceso.php">Iniciar sesion con otra cuenta</a>
				<a href="../Ayuda/formularioAyuda.php">Ayuda</a>
				<a href="../logout.php">Cerrar sesion</a>
			  </div>
			</div>
		  </div>
	  </div>

	</div>

	<div class="container">
		<header class="header">
			<div>
				<h1 class="title">PCComponentes</h1>
				<p class="subtitle">Tu carrito</p>
			</div>
		</header>

		<div class="card">
	<?php
		// Conexión a la BD
		$conexion = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
		if ($conexion->connect_error) {
			die("<p class='badge danger'>Error de conexión: " . $conexion->connect_error . "</p>");
		}

		$total = 0;

		// Consulta filtrando por cliente
		$sql = "SELECT id, nombre, tipo_hardware, Precio, cantidad
				FROM carrito 
				WHERE id_cliente = $id_cliente";

		$resultado = $conexion->query($sql);

		$carrito_vacio = ($resultado->num_rows == 0);

		if (!$carrito_vacio) {
			echo "<h2>Tu carrito</h2>";
			echo "<table class='table'>
					<thead>
						<tr>
							<th>Nombre</th>
							<th>Tipo de hardware</th>
							<th>Cantidad</th>
							<th>Precio (€)</th>
							<th>Eliminar</th>
						</tr>
					</thead>
					<tbody>";
			
			while ($fila = $resultado->fetch_assoc()) {
				echo "<tr>";
				echo "<td>" . htmlspecialchars($fila['nombre']) . "</td>";
				echo "<td>" . htmlspecialchars($fila['tipo_hardware']) . "</td>";
				echo "<td>" . htmlspecialchars($fila['cantidad']) . "</td>";
				echo "<td><span class='badge'>" . number_format($fila['Precio'] * $fila['cantidad'],2) . " €</span></td>";
				echo "<td>
						<form method='POST' action='quitar_carrito.php'>
							<input type='hidden' name='id' value='" . $fila['id'] . "'>
							<input type='submit' value='Quitar del carrito'>
						</form>
					  </td>";
				echo "</tr>";

				$total += $fila['Precio'] * $fila['cantidad'];
			}

			echo "</tbody></table>";
		} else {
			echo "<p class='footer-note'>Tu carrito está vacío.</p>";
		}

		$conexion->close();
	?>
			<div class="total-box">
				<p>Costo total: <strong><?php echo number_format($total, 2); ?> €</strong></p>
			</div>

			<form method='POST' action='Tarjetas/comprar_MasterCard.php' 
				  onsubmit="return verificarCarrito(<?php echo $carrito_vacio ? 'true' : 'false'; ?>)">
				<input type='submit' value='Comprar el carrito'>
			</form>

		<a href="../devolucion.php" class="botonAbajo">Ver Devoluciones/Valorar</a>
		<footer class="footer-note">
			&copy; <?php echo date("Y"); ?> OpenDLC — Todos los derechos reservados.
		</footer>
	</div>
</div>

<script>
function verificarCarrito(vacio) {
    if (vacio) {
        alert("No hay productos en el carrito. Agrega algo antes de comprar.");
        return false;
    }
    return true;
}
</script>

</body>
</html>