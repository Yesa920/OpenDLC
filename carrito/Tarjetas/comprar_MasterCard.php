<?php
session_start();
require_once __DIR__ . '/../../auth.php'; requerir_cliente(); 
include '../../chatbot.php'; 

$conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
$id_usuario = $_SESSION['id_cliente'] ?? null; 
$nombre_completo = "Invitado"; 

if ($id_usuario) {
    $sql_user = "SELECT nombre, apellido, cp, telefono FROM cliente WHERE id_cliente = '$id_usuario'"; 
    $res_user = mysqli_query($conexion, $sql_user);
    
    if ($res_user && $fila_user = mysqli_fetch_assoc($res_user)) {
        $nombre_completo = $fila_user['nombre'] . " " . $fila_user['apellido'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Compra MasterCard</title>

<!-- Leaflet CSS y JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- CSS propio -->
<link rel="stylesheet" href="../../css/carrito.css">
<style>
    body { font-family: Arial, sans-serif; }
    img { width: 200px; height: 150px; }
    .total-box { margin-top: 15px; font-weight: bold; font-size: 1.1em; }
    .seccion-entrega { display: flex; gap: 25px; margin-top: 20px; flex-wrap: wrap; }
    .col-datos, .col-mapa { flex: 1; min-width: 300px; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #ddd; }
    #mapa_envio { height: 350px; width: 100%; border-radius: 5px; border: 2px solid #007bff; margin-bottom: 10px; }
    #opcionesPago img { display: inline-block; }
    #opcionesPago a.seleccionada img { border: 10px solid green !important; border-radius: 10px; }
    .perfil.active .dropdown { display: block; }
    .mensaje-error { color: red; font-size: 0.9em; display: block; margin-top: 3px; }
	input.error, select.error {border: 2px solid red !important;}
</style>
</head>
<body>
<div class="navbar">
  <div class="nav-left"><a href="../carrito.php" class="nav-back">← Volver</a></div>
  <div class="nav-right">
     <div class="perfil">
      <button class="perfil-btn" onclick="toggleMenu()">👤</button>

      <div class="dropdown">
	    <div style="padding: 12px 15px; border-bottom: 1px solid #eee; background: #f8fafc;">
            <small style="color: #64748b; display: block; font-size: 11px; text-transform: uppercase;">Bienvenido:</small>
            <strong style="color: #1e293b; font-size: 14px;"><?php echo htmlspecialchars($nombre_completo); ?></strong>
        </div>
        <a href="../carrito.php">Carrito</a>
		<a href="../../devolucion.php">Devoluciones/Valorar</a>
        <a href="../../UID/iniciar_sesion/acceso.php">Iniciar sesion con otra cuenta</a>
        <a href="../../Ayuda/formularioAyuda.php">Ayuda</a>
        <a href="../../logout.php">Cerrar sesion</a>
      </div>
    </div>
  </div>
  </div>
</div>

<div class="container">
<header class="header">
    <div>
        <h1 class="title">OpenDLC</h1>
        <p class="subtitle">Tu compra</p>
    </div>
</header>

<h1>Opciones de compra</h1>
<div id="opcionesPago">
    <img src="../../imagenes/MasterCard.png" class="opcion-pago" data-tipo="mastercard">
    <img src="../../imagenes/Visa.png" class="opcion-pago" data-tipo="visa">
    <img src="../../imagenes/American_Express.png" class="opcion-pago" data-tipo="american">
</div>

<div class="card">
<div style="background:#fff7ed; border:2px solid #f97316; border-radius:8px; padding:14px 16px; margin-bottom:16px; color:#7c2d12; line-height:1.5;">
  <strong>⚠️ ZONA DE PAGO SIMULADA (proyecto de demostración)</strong><br>
  No se realiza ningún cobro y <strong>no debes escribir los datos de tu tarjeta real</strong>.
  Usa un número inventado (por ejemplo <code>4242424242424242</code>), cualquier fecha futura y un código como <code>123</code>.
  Los datos de la tarjeta ni siquiera salen de tu navegador: no se envían ni se guardan.<br>
  <small>En una tienda real, el pago lo gestiona una pasarela de pago contratada con un banco o un proveedor autorizado
  (por ejemplo Redsys o Stripe). Los datos de la tarjeta se escriben en la página segura de esa entidad, nunca en el servidor de la tienda,
  y es el banco quien autoriza el cobro.</small>
</div>

<p>Los campos con * son obligatorios o que esten en <style color="red">rojo</style></p>
<h2>Has elegido MasterCard,Visa,American_Express</h2>

<form method="post" action="comprarAccion.php" id="formCompra">

    <label for="numTar">Número de la tarjeta*</label><br>
    <input type="text" id="numTar" autocomplete="off" minlength="13" maxlength="19" pattern="\d{13,19}" required/><br>

    <label>Fecha de caducidad*</label><br>
    <label for="mes">Mes</label>
    <select id="mes" autocomplete="off" required>
        <option value="">--</option>
        <?php for($m=1;$m<=12;$m++) { printf('<option value="%02d">%02d</option>', $m,$m); } ?>
    </select>
    <label for="anio">Año</label>
    <select id="anio" autocomplete="off" required>
        <option value="">--</option>
        <?php for($y=date("Y");$y<=2050;$y++) { echo "<option value='$y'>$y</option>"; } ?>
    </select><br>

    <label for="codSeg">Código de seguridad*</label><br>
    <input type="password" id="codSeg" autocomplete="off" minlength="3" maxlength="4" pattern="\d{3,4}" required/><br>

    <h2>Informe de facturación</h2>
    <div class="seccion-entrega">
        <div class="col-datos">
            <label for="nombre">Nombre</label><br>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($fila_user['nombre'] ?? ''); ?>"/><br>

            <label for="apellidos">Apellidos</label><br>
            <input type="text" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($fila_user['apellido'] ?? ''); ?>"/><br>

            <label for="correo">Correo*</label><br>
            <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required/><br>

            <label for="localidad">Localidad</label><br>
            <input type="text" id="localidad" name="localidad"/><br>

            <label for="codigo_postal">Código postal</label><br>
            <input type="text" id="codigo_postal" name="codigo_postal" value="<?php echo htmlspecialchars($fila_user['cp'] ?? ''); ?>"/><br>

            <label for="direccion">Dirección de facturación*</label><br>
            <input type="text" id="direccion" name="direccion" required/><br>

            <label for="pais">País</label><br>
            <input type="text" id="pais" name="pais"/><br>

            <label for="telefono">Teléfono</label><br>
            <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($fila_user['telefono'] ?? ''); ?>"/><br>
        </div>

        <div class="col-mapa">
            <h3>Otra ubicación de entrega</h3>
            <small>Haz clic en el mapa o escribe una dirección</small>
            <div id="mapa_envio"></div>
            <label>Coordenadas de entrega:</label>
            <input type="text" id="coords_entrega" name="coords_entrega" placeholder="No se ha seleccionado punto" readonly/>
            <input type="hidden" id="lat" name="lat_entrega">
            <input type="hidden" id="lng" name="lng_entrega"><br>
            <label for="direccion_entrega">Dirección de entrega</label>
            <input type="text" id="direccion_entrega" name="direccion_entrega" placeholder="Calle, número, piso..." />
        </div>
    </div>

    <?php
    $id_cliente = $_SESSION['id_cliente'];
    $sql = "SELECT nombre, tipo_hardware, Precio,cantidad FROM carrito WHERE id_cliente='$id_cliente'";
    $resultado = $conexion->query($sql);
    $total = 0;
    if ($resultado->num_rows > 0) {
        echo "<h2>Tu carrito</h2><table class='table'><thead>
              <tr><th>Nombre</th><th>Tipo de hardware</th><th>Cantidad</th><th>Precio (€)</th></tr></thead><tbody>";
        while ($fila = $resultado->fetch_assoc()) {
            echo "<tr><td>".htmlspecialchars($fila['nombre'])."</td><td>".htmlspecialchars($fila['tipo_hardware'])."</td><td>".htmlspecialchars($fila['cantidad'])."</td><td>".number_format($fila['Precio'] * $fila['cantidad'],2)." €</td></tr>";
            $total += $fila['Precio'] * $fila['cantidad'];
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No hay productos en el carrito.</p>";
    }
    ?>
    <div class="total-box">Costo total: <strong><?php echo number_format($total,2); ?> €</strong></div>

    <input type="hidden" name="metodo_pago" value="MasterCard">
    <input type="submit" value="Comprar MasterCard">
</form>
</div>

<footer class="footer-note">&copy; <?php echo date("Y"); ?> OpenDLC — Todos los derechos reservados.</footer>

<script>
setTimeout(function() { map.invalidateSize(); }, 200);
function toggleMenu() {
    document.querySelector(".perfil").classList.toggle("active");
}

document.addEventListener("DOMContentLoaded", function() {
    // Mapa
    if(typeof L !== "undefined") {
        var map = L.map('mapa_envio').setView([40.4167,-3.70325],6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        var marker;
        map.on('click', function(e) {
            if(marker) map.removeLayer(marker);
            marker = L.marker(e.latlng).addTo(map);
            document.getElementById('lat').value = e.latlng.lat;
            document.getElementById('lng').value = e.latlng.lng;
            document.getElementById('coords_entrega').value = e.latlng.lat.toFixed(5)+", "+e.latlng.lng.toFixed(5);
        });
    }

    // Validación del formulario
    const form = document.getElementById('formCompra');
form.addEventListener("submit", function(event) {
    let valido = true;
    let primerCampoError = null;

    function marcarError(idCampo, mensaje) {
        const campo = document.getElementById(idCampo);
        let msg = document.getElementById("error-" + idCampo);
        if (!msg) {
            msg = document.createElement("span");
            msg.id = "error-" + idCampo;
            msg.classList.add("mensaje-error");
            campo.insertAdjacentElement("afterend", msg);
        }
        campo.classList.add("error");
        msg.textContent = mensaje;
        msg.classList.add("activo");

        if (valido) { primerCampoError = campo; valido = false; }
    }

		// Limpiar errores previos
		document.querySelectorAll(".error").forEach(c => c.classList.remove("error"));
		document.querySelectorAll(".mensaje-error").forEach(m => {
			m.classList.remove("activo"); m.textContent = "";
		});

		// Validaciones de campos obligatorios con asterisco
		const correo = document.getElementById("correo").value.trim();
		const tarjeta = document.getElementById("numTar").value.trim();
		const mes = document.getElementById("mes").value;
		const anio = document.getElementById("anio").value;
		const codigo = document.getElementById("codSeg").value.trim();

		if (tarjeta === "") marcarError("numTar", "Complete este campo.");
		else if (!/^\d{13,19}$/.test(tarjeta)) marcarError("numTar", "El número debe tener entre 13 y 19 dígitos.");

		if (!mes) marcarError("mes", "Seleccione un mes.");
		if (!anio) marcarError("anio", "Seleccione un año.");

		if (codigo === "") marcarError("codSeg", "Complete este campo.");
		else if (!/^\d{3,4}$/.test(codigo)) marcarError("codSeg", "Código inválido (3 o 4 dígitos).");

		if (correo === "") marcarError("correo", "Complete este campo.");
		else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) marcarError("correo", "Formato de correo inválido.");

		// Si hubo algún error, evitar submit y enfocar primer campo
		if (!valido) {
			event.preventDefault();
			primerCampoError.focus();
		}
	});
});
</script>
</body>
</html>