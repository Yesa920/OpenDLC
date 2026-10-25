<?php require_once __DIR__ . '/../auth.php'; requerir_admin(); ?>
<html>

<head></head>

<body>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
function hacerInsert(){

				$conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME')) 
					or die("Problemas con la conexión");

// Precio y stock tienen que ser numeros validos
$precio = str_replace(',', '.', trim((string)($_REQUEST['precio'] ?? '')));
$stockTxt = trim((string)($_REQUEST['stock'] ?? ''));
if (!is_numeric($precio) || (float)$precio < 0 || !ctype_digit($stockTxt)) {
    echo "<script>alert('El precio y el stock deben ser numeros validos.'); history.back();</script>";
    exit();
}
$stock = (int)$stockTxt;

				// --- Procesar la imagen ---
				$imagenRuta = "";
	
				// ¿Hay imagen subida?
				if (isset($_FILES["imagen"]) && !empty($_FILES["imagen"]["name"])) {

					// Nombre único
					$ext = strtolower(pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION));
    if (!in_array($ext, ["jpg", "jpeg", "png", "gif", "webp"], true) || @getimagesize($_FILES["imagen"]["tmp_name"]) === false || $_FILES["imagen"]["size"] > 5 * 1024 * 1024) {
        echo "<script>alert('Solo se permiten imagenes JPG, PNG, GIF o WEBP de hasta 5 MB.'); history.back();</script>";
        exit();
    }
    $nombreArchivo = uniqid() . "." . $ext;   // el nombre lo decide el servidor

					$rutaDestino = "../imagenes_productos/" . $nombreArchivo;

					// Mover la imagen
					if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $rutaDestino)) {
						// Guardamos solo el nombre, no la ruta completa
						$imagenRuta = $nombreArchivo;
					} else {
						die("Error al guardar la imagen.");
					}
				}
				if (!isset($_SESSION['id_administrador'])) {
					die("No hay sesión activa");
				}
				
				// --- INSERT con consulta preparada: los datos nunca se mezclan con el SQL ---
				$nombre     = (string)($_REQUEST['nombre'] ?? '');
				$tipo       = (string)($_REQUEST['tipoHardware'] ?? '');
				$antiguo    = (string)($_REQUEST['masCincoAgnos'] ?? '');
				$comentario = (string)($_REQUEST['comentario'] ?? '');
				$idAdmin    = (int)$_SESSION['id_administrador'];
				$stmt = $conexion->prepare("INSERT INTO componentes(nombre, tipo_hardware, antiguo, precio, comentario, imagen, stock, id_administrador) VALUES (?,?,?,?,?,?,?,?)");
				$stmt->bind_param('ssssssii', $nombre, $tipo, $antiguo, $precio, $comentario, $imagenRuta, $stock, $idAdmin);
				$stmt->execute() or die("Problemas en el insert");
				$stmt->close();

				echo "
				<script>
				alert('El componente ha sido dado de alta');
				window.location.href = 'insert.php';
				</script>
				";

				mysqli_close($conexion);
				}
function hacerUpdate(){
				$conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME')) 
					or die("Problemas con la conexión");

				// Recogemos el código del producto
				$id_producto = (int)($_REQUEST['id_producto'] ?? 0);

				// Buscar el producto por su clave primaria
				$registro = mysqli_query(
					$conexion,
					"SELECT * FROM componentes WHERE codigo_producto='$id_producto'"
				) or die("Problemas en el select: " . mysqli_error($conexion));

				if ($reg = mysqli_fetch_array($registro)) {
				?>
				<link rel="stylesheet" type="text/css" href="../css/UID.css" media="screen" />
				<div class="top-bar">
				  <a href="../logout.php" class="btn inicio">🚪 Cerrar sesión</a>
				  <div class="nav-right">
					<a href="update.php" class="btn">✏️ Update</a>
					<a href="delete.php" class="btn danger">🗑 Delete</a>
					<a href="consultar.php" class="btn">📄 Consultar</a>
					<a href="ventas_globales.php" class="btn">📊 Ventas</a>
				  </div>
				</div>

				<h1>Cambia los datos para hacer el update</h1>

				<form method="post" action="confirmarUpdate.php" enctype="multipart/form-data">

				<!-- IMPORTANTÍSIMO: MANDAR LA PK -->
				<input type="hidden" name="id_producto" value="<?php echo htmlspecialchars((string)$reg['codigo_producto'], ENT_QUOTES, 'UTF-8'); ?>">

				<label for="nombre">Cambia los datos para que sean correctos</label>
				<br/>
				<input type="text" id="nombre" name="nombreNuevo" value="<?php echo htmlspecialchars((string)$reg['nombre'], ENT_QUOTES, 'UTF-8'); ?>"/>
				<input type="hidden" name="nombreViejo" value="<?php echo htmlspecialchars((string)$reg['nombre'], ENT_QUOTES, 'UTF-8'); ?>"/>
				<br/>

				<label>Selecciona el tipo de hardware para implementar</label><br/>
				<select name="tipo_hardwareNuevo">
					<option value="ram" <?= ($reg['tipo_hardware'] == 'ram') ? 'selected' : '' ?>>RAM</option>
					<option value="discos" <?= ($reg['tipo_hardware'] == 'discos') ? 'selected' : '' ?>>Discos</option>
					<option value="cables" <?= ($reg['tipo_hardware'] == 'cables') ? 'selected' : '' ?>>Cables</option>
					<option value="TarjGraf" <?= ($reg['tipo_hardware'] == 'TarjGraf') ? 'selected' : '' ?>>Tarjetas gráficas</option>
					<option value="FuenteAlimentacion" <?= ($reg['tipo_hardware'] == 'FuenteAlimentacion') ? 'selected' : '' ?>>Fuente de alimentación</option>				
					<option value="Procesador" <?= ($reg['tipo_hardware'] == 'Procesador') ? 'selected' : '' ?>>Procesador</option>
					<option value="Refrigeracion" <?= ($reg['tipo_hardware'] == 'Refrigeracion') ? 'selected' : '' ?> >Refrigeración</option>
					<option value="Placa_base" <?= ($reg['tipo_hardware'] == 'Placa_base') ? 'selected' : '' ?> >Placa base</option>
					<option value="Torres" <?= ($reg['tipo_hardware'] == 'Torres') ? 'selected' : '' ?> >Torres</option>
					<option value="Monitor" <?= ($reg['tipo_hardware'] == 'Monitor') ? 'selected' : '' ?> >Monitor</option>
					<option value="Raton" <?= ($reg['tipo_hardware'] == 'Raton') ? 'selected' : '' ?> >Raton</option>
					<option value="Tarjeta_de_red" <?= ($reg['tipo_hardware'] == 'Tarjeta_de_red') ? 'selected' : '' ?> >Tarjeta de red</option>
					<option value="Tarjeta_de_sonido" <?= ($reg['tipo_hardware'] == 'Tarjeta_de_sonido') ? 'selected' : '' ?> >Tarjeta de sonido</option>
					<option value="OrdenadorPremontados" <?= ($reg['tipo_hardware'] == 'OrdenadorPremontados') ? 'selected' : '' ?> >Ordenador Premontados</option>
				</select>
				<input type="hidden" name="tipo_hardwareViejo" value="<?= htmlspecialchars((string)$reg['tipo_hardware'], ENT_QUOTES, 'UTF-8') ?>">
				<br/>

				<label>¿Su producto tiene más de 5 años?</label><br/>
				<input type="radio" name="masCincoAgnosNuevo" value="si" <?php if($reg['Antiguo'] == 'si') echo 'checked'; ?>> Sí
				<input type="radio" name="masCincoAgnosNuevo" value="no" <?php if($reg['Antiguo'] == 'no') echo 'checked'; ?>> No
				<input type="hidden" name="masCincoAgnosViejo" value="<?php echo htmlspecialchars((string)$reg['Antiguo'], ENT_QUOTES, 'UTF-8'); ?>"/>
				<br/>

				<label for="precio">Introduzca el precio(sin el €)</label>
				<br/>
				<input type="text" id="precio" name="precioNuevo" value="<?php echo htmlspecialchars((string)$reg['Precio'], ENT_QUOTES, 'UTF-8'); ?>"/>
				<input type="hidden" name="precioViejo" value="<?php echo htmlspecialchars((string)$reg['Precio'], ENT_QUOTES, 'UTF-8'); ?>"/>
				<br/>

				<label>Imagen actual:</label><br>
				<img src="../imagenes_productos/<?php echo htmlspecialchars((string)$reg['imagen'], ENT_QUOTES, 'UTF-8'); ?>" width="150"><br><br>

				<label>Subir nueva imagen:</label><br>
				<input type="file" name="imagenProducto" accept="image/*" onchange="previsualizarImagen(this)">
				<div id="preview-container" style="display:none; margin: 10px 0;">
				  <p style="font-size:13px; color:#365983; margin: 0 0 6px 0;">Vista previa nueva imagen:</p>
				  <img id="preview-img" src="" alt="Vista previa"
				       style="max-width: 200px; max-height: 200px; border-radius: 8px;
				              border: 2px solid #9dbce2; box-shadow: 0 2px 8px rgba(29,95,184,0.15);
				              object-fit: contain; background: #f8fbff; padding: 4px; display:block;">
				</div><br><br>

				<!-- Guardar imagen vieja -->
				<input type="hidden" name="imagenVieja" value="<?php echo htmlspecialchars((string)$reg['imagen'], ENT_QUOTES, 'UTF-8'); ?>">
				<br/>
				<label for="stock">Introduzca el stock</label>
				<br/>
				<input type="text" id="stock" name="stock" value="<?php echo htmlspecialchars((string)$reg['stock'], ENT_QUOTES, 'UTF-8'); ?>"/>
				<br/>
				<label>Introduce un comentario</label>
				<br/>
				<textarea name="comentarioNuevo"><?php echo htmlspecialchars((string)$reg['Comentario'], ENT_QUOTES, 'UTF-8'); ?></textarea>
				<input type="hidden" name="comentarioViejo" value="<?php echo htmlspecialchars((string)$reg['Comentario'], ENT_QUOTES, 'UTF-8'); ?>" />
				<br/>

				<input type="submit" name="accion" value="Update"/>
				</form>
				<a href="admin_chatbot.php" class="admin-float-btn">
					<div class="admin-icon-circle">
						<span class="admin-emoji">⚙️</span>
					</div>
					<span class="admin-label">Panel Gestión Chat</span>
				</a>
				<script>
				function previsualizarImagen(input) {
				  const container = document.getElementById("preview-container");
				  const img = document.getElementById("preview-img");
  if (input.files && input.files[0]) {
    const f = input.files[0];
    if (!/\.(jpe?g|png|gif|webp)$/i.test(f.name) || (f.type && !f.type.startsWith("image/"))) {
      alert("Ese archivo no es una imagen. Elige un archivo JPG, PNG, GIF o WEBP.");
      input.value = ""; container.style.display = "none"; img.src = "";
      return;
    }
    if (f.size > 5 * 1024 * 1024) {
      alert("La imagen pesa demasiado (maximo 5 MB).");
      input.value = ""; container.style.display = "none"; img.src = "";
      return;
    }
  }
				  if (input.files && input.files[0]) {
				    const reader = new FileReader();
				    reader.onload = function(e) {
				      img.src = e.target.result;
				      container.style.display = "block";
				    };
				    reader.readAsDataURL(input.files[0]);
				  } else {
				    container.style.display = "none";
				    img.src = "";
				  }
				}
				</script>
			<?php
			}
			else{
				echo "<script>
				alert('No existe ese producto');
				window.location.href = 'update.php';
				</script>";
			}
			
			
}
function hacerDelete(){

		$conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'))
			or die("Problemas con la conexión");

		// Recogemos la clave primaria
		$codigo = (int)($_REQUEST['codigoproducto'] ?? 0);

		// Buscamos el producto
		$registro = mysqli_query(
			$conexion,
			"SELECT imagen FROM componentes WHERE codigo_producto='$codigo'"
		) or die("Problemas en el select: " . mysqli_error($conexion));

		if ($reg = mysqli_fetch_array($registro)) {

			// Guardamos la ruta de la imagen
			$imagen = $reg['imagen'];
			$rutaImagen = "../imagenes_productos/" . basename((string)$imagen);
			// Borrar en orden para no romper las foreign keys
			mysqli_query($conexion, "DELETE FROM carrito WHERE producto_id = '$codigo'");
			mysqli_query($conexion, "DELETE FROM devoluciones WHERE id_venta IN (SELECT id FROM ventas WHERE codigo_producto = '$codigo')");
			mysqli_query($conexion, "DELETE FROM valoraciones WHERE codigo_producto = '$codigo'");
			mysqli_query($conexion, "DELETE FROM ventas WHERE codigo_producto = '$codigo'");
			// Borramos el registro
			mysqli_query(
				$conexion,
				"DELETE FROM componentes WHERE codigo_producto='$codigo'"
			) or die("Problemas en el delete: " . mysqli_error($conexion));

			// Si existe la imagen → la borramos
			if (file_exists($rutaImagen)) {
				unlink($rutaImagen);
			}

			echo "<script>
					alert('El componente y su imagen han sido borrados correctamente');
					window.location.href = 'delete.php';
				  </script>";
		}
		// Si no existe
		else {
			echo "<script>
					alert('El componente no ha sido encontrado');
					window.location.href = 'delete.php';
				  </script>";
		}

		mysqli_close($conexion);
}
function hacerConsulta(){
	?>
	<style>
    table {
        width: 70%;
        margin: 20px auto;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
        background: #f9f9f9;
        box-shadow: 0 0 10px rgba(0,0,0,0.15);
        border-radius: 8px;
        overflow: hidden;
    }

    th {
        background: #4CAF50;
        color: white;
        padding: 12px;
        text-align: center;
        font-size: 16px;
    }

    td {
        padding: 10px;
        text-align: center;
        font-size: 15px;
        border-bottom: 1px solid #ddd;
    }
</style>
	<?php
	$conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME')) or die("Problemas con la conexión");
			$registro = mysqli_query($conexion, "select * from componentes where codigo_producto='" . (int)($_REQUEST['codigoproducto'] ?? 0) . "'") or die("Problemas en el select" . mysqli_error($conexion));
			if($reg = mysqli_fetch_array($registro)){
				$e = array_map(fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'), $reg);
				echo "<table border='1' cellpadding='5' cellspacing='0'>";
				echo "<tr>
					<th>Código Producto</th>
					<th>Nombre</th>
					<th>Tipo Hardware</th>
					<th>Antiguo</th>
					<th>Precio</th>
					<th>Comentario</th>
					<th>Imagen</th>
					<th>Stock</th>
					<th>Id_administrador</th>
				</tr>";
				echo "<tr>
					<td>$e[codigo_producto]</td>
					<td>$e[nombre]</td>
					<td>$e[tipo_hardware]</td>
					<td>$e[Antiguo]</td>
					<td>$e[Precio]</td>
					<td>$e[Comentario]</td>
					<td><img src='../imagenes_productos/$e[imagen]'></td>
					<td>$e[stock]</td>
					<td>$e[id_administrador]</td>
				</tr>";
				echo "</table>";
			}
			else{
				echo "<script>
				alert('No hay ningun producto con ese id');
				window.location.href = 'consultar.php';
				</script>";//. $_REQUEST['codigoproducto'];
			}
			mysqli_close($conexion);
			echo
			"<script>
			setTimeout(function() {
				alert('Redirigimos a la pagina consultar');
				window.location.href = 'consultar.php';
				}, 7000);
			</script>";
}	
function guardarArchivo(){
	$ar = fopen("crear_archivo/DatosDelComponente.txt", "a") or
	die("Problemas en la creacion");
	fputs($ar, "El nombre del producto: ".$_REQUEST['nombre']);
	fputs($ar, "\n");
	fputs($ar, "El tipo del hardware: ".$_REQUEST['tipoHardware']);
	fputs($ar, "\n");
	fputs($ar, "¿El producto es antuguo? ".$_REQUEST['masCincoAgnos']);
	fputs($ar, "\n");
	fputs($ar, "El precio del producto: ".$_REQUEST['precio']);
	fputs($ar, "\n");
	fputs($ar, "La imagen del producto: ".$_REQUEST['Imagen']);
	fputs($ar, "\n");
	fputs($ar, "El stock del producto: ".$_REQUEST['stock']);
	fputs($ar, "\n");
	fputs($ar, "El comentario del producto: ".$_REQUEST['comentario']);
	fputs($ar, "\n");
	fputs($ar, "------------------------------------------------
	--------");
	fputs($ar, "\n");
	fclose($ar);
	if ($_POST['form_origen'] == "insert") {
    echo "<script>
            alert('Datos generados correctamente');
            window.location.href = 'insert.php';
          </script>";
	}	 elseif ($_POST['form_origen'] == "update") {
    echo "<script>
            alert('Datos generados correctamente');
            window.location.href = 'update.php';
          </script>";
	}
}
function abrirArchivo(){	
	$ar = fopen("crear_archivo/DatosDelComponente.txt", "r") or
	die("No se pudo abrir el archivo");
	while (!feof($ar)) {
		$linea = fgets($ar);
		$lineasalto = nl2br($linea);
		echo $lineasalto;
	}
	fclose($ar);
}
 if (isset($_REQUEST["accion"])) {
        if ($_REQUEST["accion"] === "Insert") {
           hacerInsert();
        } 
		else if ($_REQUEST["accion"] === "Confirmar Update") {
            hacerUpdate();
        }
		else if ($_REQUEST["accion"] === "Confirmar Delete") {
            hacerDelete();
        }
		else if($_REQUEST["accion"] === "Confirmar Consultar"){
			hacerConsulta();
		}
			
    }
if (isset($_REQUEST["accionFichero"])) {
        if ($_REQUEST["accionFichero"] === "Guardar Archivo") {
           guardarArchivo();
        } 
		else if ($_REQUEST["accionFichero"] === "Abrir Archivo") {
           abrirArchivo();
		}
}

?>
</body>

</html>