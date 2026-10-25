<?php 
session_start(); 
include 'chatbot.php'; 

$conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));

$id_usuario = $_SESSION['id_cliente'] ?? null; 
$nombre_completo = "Invitado"; 

$min = $_GET['precio_min'] ?? 0;
$max = $_GET['precio_max'] ?? 6000;
$val = $_GET['valoracion'] ?? "";


if ($id_usuario) {
    // Añadimos 'apellido' a la consulta SELECT
    $sql_user = "SELECT nombre, apellido FROM cliente WHERE id_cliente = '$id_usuario'"; 
    $res_user = mysqli_query($conexion, $sql_user);
    
    if ($res_user && $fila_user = mysqli_fetch_assoc($res_user)) {
        // Concatenamos nombre y apellido con un espacio
        $nombre_completo = $fila_user['nombre'] . " " . $fila_user['apellido'];
    }
}?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OpenDLC</title>

<link rel="stylesheet" href="css/prueba_cliente.css">

</head>

<body>

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

<?php

?>

<!-- NAVBAR -->
<div class="navbar">

  <div class="nav-left">
    <a href="Cliente_sinIniciarSesion.php" class="nav-back">← Volver</a>
  </div>

  <div class="nav-center">
    <form method="get" class="barra-busqueda">
      <input type="text" name="navegador" placeholder="Buscar productos..."
      value="<?php echo isset($_GET['navegador']) ? htmlspecialchars($_GET['navegador']) : ''; ?>">
		<!--Preservar filtros al buscar -->
		<input type="hidden" name="precio_min" value="<?php echo htmlspecialchars($min); ?>">
		<input type="hidden" name="precio_max" value="<?php echo htmlspecialchars($max); ?>">
		<input type="hidden" name="valoracion" value="<?php echo htmlspecialchars($val); ?>">
	  <button type="submit">🔍</button>
	  
	  <button type="button" class="btn-filtros" onclick="toggleFiltros(event)">⚙️ Filtros</button>
    </form>
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

<div id="panelFiltros" class="panel-filtros">
  
<label>Precio</label>

<div class="precio-inputs">
  <input type="number" id="minInput" min="0" max="6000" value="<?php echo $min; ?>">
  <span> - </span>
  <input type="number" id="maxInput" min="0" max="6000" value="<?php echo $max; ?>">
</div>

<div class="range-container">
  <div class="range-track"></div>
  <div class="range-fill" id="rangeFill"></div>

  <input type="range" id="minRange" min="0" max="6000" value="<?php echo $min; ?>">
  <input type="range" id="maxRange" min="0" max="6000" value="<?php echo $max; ?>">
</div>

  <!-- VALORACIÓN -->
<div class="filtro-estrellas">

  <div class="opcion">
    <input type="radio" name="valoracion" value="" id="star0" <?php if($val=="") echo "checked"; ?>>
    <label for="star0">Todas</label>
  </div>

  <div class="opcion">
    <input type="radio" name="valoracion" value="5" id="star5" <?php if($val==5) echo "checked"; ?>>
    <label for="star5">★★★★★</label>
  </div>

  <div class="opcion">
    <input type="radio" name="valoracion" value="4" id="star4" <?php if($val==4) echo "checked"; ?>>
    <label for="star4">★★★★☆ o más</label>
  </div>

  <div class="opcion">
    <input type="radio" name="valoracion" value="3" id="star3" <?php if($val==3) echo "checked"; ?>>
    <label for="star3">★★★☆☆ o más</label>
  </div>

  <div class="opcion">
    <input type="radio" name="valoracion" value="2" id="star2" <?php if($val==2) echo "checked"; ?>>
    <label for="star2">★★☆☆☆ o más</label>
  </div>

  <div class="opcion">
    <input type="radio" name="valoracion" value="1" id="star1" <?php if($val==1) echo "checked"; ?>>
    <label for="star1">★☆☆☆☆ o más</label>
  </div>

</div>

  <button onclick="aplicarFiltros()" class="btn-carrito" style="margin-top:10px;">
    Aplicar filtros
  </button>

</div>

<script>
const minRange = document.getElementById("minRange");
const maxRange = document.getElementById("maxRange");
const minInput = document.getElementById("minInput");
const maxInput = document.getElementById("maxInput");
const rangeFill = document.getElementById("rangeFill");
const MAX = 6000;

// 🔄 actualizar barra azul
function updateSlider() {
  let min = parseInt(minRange.value);
  let max = parseInt(maxRange.value);

  if (min > max) {
    min = max;
    minRange.value = min;
  }

  if (max < min) {
    max = min;
    maxRange.value = max;
  }

  // sincronizar inputs
  minInput.value = min;
  maxInput.value = max;

  // pintar barra
  const percentMin = (min / MAX) * 100;
  const percentMax = (max / MAX) * 100;

  rangeFill.style.left = percentMin + "%";
  rangeFill.style.width = (percentMax - percentMin) + "%";
}

// eventos
minRange.addEventListener("input", updateSlider);
maxRange.addEventListener("input", updateSlider);

// inputs manuales
minInput.addEventListener("input", () => {
  minRange.value = minInput.value;
  updateSlider();
});

maxInput.addEventListener("input", () => {
  maxRange.value = maxInput.value;
  updateSlider();
});

// inicial
updateSlider();

// 🔥 asegura sincronización al cargar
window.addEventListener("load", updateSlider);

function toggleFiltros(e) {
  e.stopPropagation(); // 🔥 evita que el click lo cierre
  document.getElementById("panelFiltros").classList.toggle("active");
}

// Cerrar si haces click fuera
document.addEventListener("click", function(e) {
  const panel = document.getElementById("panelFiltros");
  const btn = document.querySelector(".btn-filtros");

  if (!panel.contains(e.target) && !btn.contains(e.target)) {
    panel.classList.remove("active");
  }
});

// Aplicar filtros (envía por GET)
function aplicarFiltros() {
  const min = document.getElementById("minInput").value;
  const max = document.getElementById("maxInput").value;
  const val = document.querySelector('[name="valoracion"]:checked')?.value;
  const busqueda = document.querySelector('input[name="navegador"]').value;

  let url = "?";

  if (busqueda) url += "navegador=" + encodeURIComponent(busqueda) + "&";
  if (min) url += "precio_min=" + min + "&";
  if (max) url += "precio_max=" + max + "&";
  if (val) url += "valoracion=" + val + "&";

  window.location.href = url;
}
</script>

<div class="container">

<header>
  <h1>OpenDLC</h1>
  <p>Catálogo de productos</p>
</header>

<div class="productos">

<?php

$conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));

$hayBusqueda = isset($_GET['navegador']) && !empty(trim($_GET['navegador']));

$where = [];

// 🔍 BUSCADOR
if ($hayBusqueda) {
  $busqueda = mysqli_real_escape_string($conexion, trim($_GET['navegador']));
  $where[] = "(nombre LIKE '%$busqueda%' OR tipo_hardware LIKE '%$busqueda%')";
}

// 💰 PRECIO MÍNIMO
if (!empty($_GET['precio_min'])) {
  $min = (int) $_GET['precio_min'];
  $where[] = "Precio >= $min";
}

// 💰 PRECIO MÁXIMO
if (!empty($_GET['precio_max'])) {
  $max = (int) $_GET['precio_max'];
  $where[] = "Precio <= $max";
}

// ⭐ VALORACIÓN
if (!empty($_GET['valoracion'])) {
  $val = (int) $_GET['valoracion'];
  $where[] = "(SELECT AVG(puntuacion) FROM valoraciones 
               WHERE codigo_producto = componentes.codigo_producto) >= $val";
}

// 🔧 QUERY FINAL
$sql = "SELECT * FROM componentes";

if (!empty($where)) {
  $sql .= " WHERE " . implode(" AND ", $where);
}

$resultado = mysqli_query($conexion, $sql);

if (mysqli_num_rows($resultado) > 0) {

  while ($fila = mysqli_fetch_assoc($resultado)) {

  echo "<div class='producto'>";
	  echo "<div class='info-superior'>";
		  echo "<img src='imagenes_productos/".htmlspecialchars($fila['imagen'])."' class='producto-img'>";
		  echo "<h3>".htmlspecialchars($fila['nombre'])."</h3>";
		  echo "<p class='tipo'>".htmlspecialchars($fila['tipo_hardware'])."</p>";
		  echo "<p class='comentario'>".htmlspecialchars($fila['Comentario'])."</p>";
		  echo "<p class='precio'>".htmlspecialchars($fila['Precio'])."€</p>";
		  echo "<p class='antiguo'>Antiguo: ".htmlspecialchars($fila['Antiguo'])."</p>";
	   echo "</div>";

 // Contenido inferior (Botón y Estrellas)
    echo "<div class='producto-footer'>";
        if ($fila['stock'] > 0) {
            echo "<p class='stock' style='color:green;'>Stock: ".htmlspecialchars($fila['stock'])."</p>";
            echo "
            <form method='POST' action='carrito/agregar_carrito.php' class='form-carrito'>
                <input type='hidden' name='codigo_producto' value='".htmlspecialchars($fila['codigo_producto'])."'>
                <div class='cantidad-control'>
                    <button type='button' class='btn-cantidad btn-menos' onclick=\"cambiarCantidad(this, -1)\">-</button>
                    <input type='number' name='cantidad' class='input-cantidad' value='1' min='1' max='".$fila['stock']."' readonly>
                    <button type='button' class='btn-cantidad btn-mas' onclick=\"cambiarCantidad(this, 1)\">+</button>
                </div>
                <input type='submit' class='btn-carrito' value='Añadir al carrito'>
            </form>";
        } else {
            echo "<p class='stock' style='color:red;'>❌ No hay stock</p>";
            echo "<button class='btn-carrito' disabled>Sin stock</button>";
        }


	// ⭐ VALORACIÓN MEDIA
	$id_producto = $fila['codigo_producto'];

	$sql_val = "SELECT AVG(puntuacion) as media, COUNT(*) as total 
				FROM valoraciones 
				WHERE codigo_producto = $id_producto";

	$res_val = mysqli_query($conexion, $sql_val);
	$val = mysqli_fetch_assoc($res_val);

	$media = isset($val['media']) ? round($val['media']) : 0;
	$total = isset($val['total']) ? $val['total'] : 0;

	echo "<div class='valoracion-media'>";
	for ($i = 1; $i <= 5; $i++) {
		echo ($i <= $media) ? "⭐" : "☆";
	}
	echo " ({$total})";
	echo "</div>";

	$sql_check = "SELECT * FROM valoraciones 
              WHERE codigo_producto = {$fila['codigo_producto']} 
              AND id_cliente = $id_usuario";

	$res_check = mysqli_query($conexion, $sql_check);
	$ya_voto = mysqli_num_rows($res_check) > 0;
	echo "<div class='espacio-valoracion'>";
	if ($ya_voto) {
       echo "<p class='msg-ya-valorado'>✓ Ya has valorado este producto</p>";
    } else {
       echo "<p style='font-size:12px; color:#64748b;'>Puedes valorar este producto desde <a href='devolucion.php' style='color:#1f3b82;'>tus compras</a> tras comprarlo.</p>";
    }
echo "</div>";
echo "</div>";
echo "</div>";
}

} else {
  echo "<p>No hay productos</p>";
}

mysqli_close($conexion);
?>

</div>

<footer class="footer-note">
  &copy; <?php echo date("Y"); ?> OpenDLC — Todos los derechos reservados.
</footer>

</div>
</body>
</html>