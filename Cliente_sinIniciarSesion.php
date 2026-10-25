<?php
include 'chatbot.php';
$conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));

$ventasPorTipo = [];
$ventasPorAdmin = [];
$productosEstrellaVentas = [];
$productosEstrellaValorados = [];
$mediaValoraciones = 0;
$totalValoraciones = 0;

$min = $_GET['precio_min'] ?? 0;
$max = $_GET['precio_max'] ?? 6000;
$val = $_GET['valoracion'] ?? "";

if ($conexion) {
  $resTipo = mysqli_query($conexion, "SELECT tipoHardware, SUM(total) AS total FROM ventas GROUP BY tipoHardware ORDER BY total DESC LIMIT 5");
  if ($resTipo) {
    while ($f = mysqli_fetch_assoc($resTipo)) {
      $ventasPorTipo[] = [
        'nombre' => $f['tipoHardware'] !== null && $f['tipoHardware'] !== '' ? $f['tipoHardware'] : 'Sin tipo',
        'total' => (float)$f['total']
      ];
    }
  }

  $resAdmin = mysqli_query($conexion, "SELECT id_administrador, SUM(total) AS total FROM ventas GROUP BY id_administrador ORDER BY total DESC LIMIT 5");
  if ($resAdmin) {
    while ($f = mysqli_fetch_assoc($resAdmin)) {
      $ventasPorAdmin[] = [
        'nombre' => $f['id_administrador'] ? ('Admin ' . $f['id_administrador']) : 'Venta sin admin',
        'total' => (float)$f['total']
      ];
    }
  }

  $resTopVentas = mysqli_query($conexion, "SELECT producto, SUM(cantidad) AS unidades FROM ventas GROUP BY producto ORDER BY unidades DESC LIMIT 3");
  if ($resTopVentas) {
    while ($f = mysqli_fetch_assoc($resTopVentas)) {
      $productosEstrellaVentas[] = $f;
    }
  }

  $resTopValorados = mysqli_query($conexion, "SELECT c.nombre, ROUND(AVG(v.puntuacion),1) AS media, COUNT(*) AS votos FROM valoraciones v INNER JOIN componentes c ON c.codigo_producto = v.codigo_producto GROUP BY v.codigo_producto, c.nombre ORDER BY media DESC, votos DESC LIMIT 3");
  if ($resTopValorados) {
    while ($f = mysqli_fetch_assoc($resTopValorados)) {
      $productosEstrellaValorados[] = $f;
    }
  }

  $resMedia = mysqli_query($conexion, "SELECT ROUND(AVG(puntuacion),1) AS media, COUNT(*) AS total FROM valoraciones");
  if ($resMedia && $f = mysqli_fetch_assoc($resMedia)) {
    $mediaValoraciones = (float)($f['media'] ?? 0);
    $totalValoraciones = (int)($f['total'] ?? 0);
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OpenDLC</title>

<link rel="stylesheet" href="css/prueba_cliente.css">

</head>

<body>

<!-- NAVBAR -->
<div class="navbar">

  <div class="nav-left">
    <a href="Cliente_sinIniciarSesion.php" class="nav-back">Inicio</a>
  </div>

  <div class="nav-center">
    <form method="get" class="barra-busqueda">
      <input type="text" name="navegador" placeholder="Buscar productos..."
      value="<?php echo isset($_GET['navegador']) ? htmlspecialchars($_GET['navegador']) : ''; ?>">
		<!-- ✅ Preservar filtros al buscar -->
		  <input type="hidden" name="precio_min" value="<?php echo htmlspecialchars($min); ?>">
		  <input type="hidden" name="precio_max" value="<?php echo htmlspecialchars($max); ?>">
		  <input type="hidden" name="valoracion" value="<?php echo htmlspecialchars($val); ?>">
      <button type="submit" class="search-btn" aria-label="Buscar">
        <span class="icon-search" aria-hidden="true"></span>
      </button>
	  <button type="button" class="btn-filtros" onclick="toggleFiltros(event)">⚙️ Filtros</button>
    </form>
  </div>

  <div class="nav-right">
    <a class="top-btn top-btn-login" href="UID/iniciar_sesion/acceso.php">Inicio de sesion / Registro</a>
    <a class="top-btn top-btn-help" href="Ayuda/formularioAyuda.php">Ayuda</a>
  </div>

</div>

<div id="panelFiltros" class="panel-filtros">
  
<label>Precio</label>

<div class="precio-inputs">
  <input type="number" id="minInput" min="0" max="6000" value="<?php echo $min ?? 0; ?>">
  <span> - </span>
  <input type="number" id="maxInput" min="0" max="6000" value="<?php echo $max ?? 6000; ?>">
</div>

<div class="range-container">
  <div class="range-track"></div>
  <div class="range-fill" id="rangeFill"></div>

  <input type="range" id="minRange" min="0" max="6000" value="<?php echo $min ?? 0; ?>">
  <input type="range" id="maxRange" min="0" max="6000" value="<?php echo $max ?? 6000; ?>">
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

  minInput.value = min;
  maxInput.value = max;

  const percentMin = (min / MAX) * 100;
  const percentMax = (max / MAX) * 100;

  rangeFill.style.left = percentMin + "%";
  rangeFill.style.width = (percentMax - percentMin) + "%";
}

minRange.addEventListener("input", updateSlider);
maxRange.addEventListener("input", updateSlider);

minInput.addEventListener("input", () => {
  minRange.value = minInput.value;
  updateSlider();
});

maxInput.addEventListener("input", () => {
  maxRange.value = maxInput.value;
  updateSlider();
});

updateSlider();

window.addEventListener("load", updateSlider);

function toggleFiltros(e) {
  e.stopPropagation();
  document.getElementById("panelFiltros").classList.toggle("active");
}

document.addEventListener("click", function(e) {
  const panel = document.getElementById("panelFiltros");
  const btn = document.querySelector(".btn-filtros");

  if (!panel.contains(e.target) && !btn.contains(e.target)) {
    panel.classList.remove("active");
  }
});

function aplicarFiltros() {
  const min = document.getElementById("minInput").value;
  const max = document.getElementById("maxInput").value;
  const val = document.querySelector('[name="valoracion"]:checked')?.value;
  const navegador = new URLSearchParams(window.location.search).get('navegador') || '';

  let url = "?";

  if (navegador) url += "navegador=" + encodeURIComponent(navegador) + "&";
  if (min) url += "precio_min=" + min + "&";
  if (max) url += "precio_max=" + max + "&";
  if (val) url += "valoracion=" + val + "&";

  window.location.href = url;
}
</script>

<div class="container">

<header>
  <h1>OpenDLC</h1>
  <p>Catalogo de productos para tienda informatica profesional</p>
  <div class="hero-badges">
    <span class="hero-badge"><span class="icon-dot icon-dot-stock"></span>Stock actualizado</span>
    <span class="hero-badge"><span class="icon-dot icon-dot-secure"></span>Compra segura</span>
    <span class="hero-badge"><span class="icon-dot icon-dot-service"></span>Soporte experto</span>
  </div>
</header>

<section class="home-insight-unified">
  <h2>🔥 Ofertas y Destacados</h2>
  <p class="unified-desc">
    Descubre nuestras promociones especiales, productos más vendidos y las mejores ofertas de la tienda.
  </p>

  <div class="unified-columns">
    <div class="unified-col">
      <h3><span class="section-icon section-icon-star"></span>🔥 Top Ofertas</h3>
      <div class="ranking-list">
        <?php
        $sqlOfertas = "SELECT nombre, tipo_hardware, Precio, Antiguo FROM componentes 
                       WHERE Precio < Antiguo AND Antiguo > 0 
                       ORDER BY (Antiguo - Precio) DESC LIMIT 3";
        $resOfertas = mysqli_query($conexion, $sqlOfertas);
        
        if ($resOfertas && mysqli_num_rows($resOfertas) > 0) {
          while ($oferta = mysqli_fetch_assoc($resOfertas)) {
            $descuento = round((1 - $oferta['Precio'] / $oferta['Antiguo']) * 100);
            echo "<p><strong>💰 " . htmlspecialchars($oferta['nombre']) . "</strong></p>";
            echo "<p class='precio-oferta'><span class='precio-nuevo'>" . $oferta['Precio'] . "€</span> 
                  <span class='precio-antiguo'>" . $oferta['Antiguo'] . "€</span>
                  <span class='descuento-badge'>-" . $descuento . "%</span></p>";
          }
        } else {
          echo "<p class='empty-note'>No hay ofertas disponibles.</p>";
        }
        ?>
      </div>

      <h3><span class="section-icon section-icon-graph"></span>⭐ Más Vendidos</h3>
      <div class="ranking-list">
        <?php
        if (!empty($productosEstrellaVentas)) {
          foreach ($productosEstrellaVentas as $i => $fila) {
            $pos = $i + 1;
            $icono = $pos == 1 ? "🥇" : ($pos == 2 ? "🥈" : "🥉");
            echo "<p><strong>{$icono} #{$pos}</strong> " . htmlspecialchars($fila['producto']) . " (" . (int)$fila['unidades'] . " uds)</p>";
          }
        } else {
          echo "<p class='empty-note'>Aún no hay ranking de ventas.</p>";
        }
        ?>
      </div>
    </div>

    <div class="unified-col">
      <h3><span class="section-icon section-icon-rating"></span>💎 Mejor Valorados</h3>
      <div class="ranking-list">
        <?php
        if (!empty($productosEstrellaValorados)) {
          foreach ($productosEstrellaValorados as $i => $fila) {
            $pos = $i + 1;
            echo "<p><strong>⭐ #{$pos}</strong> " . htmlspecialchars($fila['nombre']) . " - " . number_format((float)$fila['media'], 1) . "★ (" . (int)$fila['votos'] . " votos)</p>";
          }
        } else {
          echo "<p class='empty-note'>Aún no hay productos valorados.</p>";
        }
        ?>
      </div>

      <h3><span class="section-icon section-icon-secure"></span>¿Por qué elegirnos?</h3>
      <div class="ranking-list">
        <p>🚚 <strong>Envío Gratis</strong></p>
        <p>🛡️ <strong>Garantía 2 años</strong> en todos los productos</p>
        <p>🔄 <strong>Devolución 30 días</strong> sin complicaciones</p>
        <p>💬 <strong>Soporte 24/7</strong> disponible siempre</p>
      </div>
      
      <h3>⭐ Valoración de la tienda</h3>
      <?php
      $estrellasMedia = (int)round($mediaValoraciones);
      $estrellasTxt = str_repeat("★", $estrellasMedia) . str_repeat("☆", 5 - $estrellasMedia);
      ?>
      <p class="overall-rating"><?php echo number_format($mediaValoraciones, 1); ?> / 5</p>
      <p class="overall-stars"><?php echo $estrellasTxt; ?></p>
      <p class="overall-meta"><?php echo $totalValoraciones; ?> valoraciones</p>
    </div>
  </div>
</section>

<div class="productos">

<?php
$conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));

$hayBusqueda = isset($_GET['navegador']) && !empty(trim($_GET['navegador']));

$where = [];

// 🔍 BUSCADOR (busca por nombre O tipo)
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
    $productoId = (int)$fila['codigo_producto'];
    $sql_val = "SELECT AVG(puntuacion) AS media, COUNT(*) AS total FROM valoraciones WHERE codigo_producto = $productoId";
    $res_val = mysqli_query($conexion, $sql_val);
    $rating = ['media' => 0, 'total' => 0];
    if ($res_val) {
      $rating = mysqli_fetch_assoc($res_val);
    }
    $media = round((float)($rating['media'] ?? 0));
    $total = (int)($rating['total'] ?? 0);
    $estrellas = str_repeat("★", $media) . str_repeat("☆", 5 - $media);

    echo "
    <div class='producto'>
      <img src='imagenes_productos/".htmlspecialchars($fila['imagen'])."' class='producto-img'>

      <h3>".htmlspecialchars($fila['nombre'])."</h3>
      <p class='tipo'>".htmlspecialchars($fila['tipo_hardware'])."</p>
      <p class='comentario'>".htmlspecialchars($fila['Comentario'])."</p>
      <p class='precio'>€".htmlspecialchars($fila['Precio'])."</p>
      <p class='antiguo'>Antiguo: ".htmlspecialchars($fila['Antiguo'])."</p>
      <p class='catalogo-stars'>{$estrellas} <span>({$total})</span></p>
    </div>
    ";
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