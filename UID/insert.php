<?php require_once __DIR__ . '/../auth.php'; requerir_admin(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="../css/UID.css" media="screen" />
<head>
<title>Proveedores Insert</title>
</head>
<body>
<div class="top-bar">
  <a href="../logout.php" class="btn inicio">🚪 Cerrar sesión</a>
  <div class="nav-right">
    <a href="update.php" class="btn">✏️ Update</a>
    <a href="delete.php" class="btn danger">🗑 Delete</a>
    <a href="consultar.php" class="btn">📄 Consultar</a>
    <a href="ventas_globales.php" class="btn">📊 Ventas</a>
  </div>
</div>

<h1>Para hacer un insertar</h1>

<form method="post" action="accionBotonIUD.php" enctype="multipart/form-data" onsubmit="return validarFormulario()">

  <label for="nombre">Elige tu producto para ir a la pagina web</label><br/>
  <input type="text" id="nombre" name="nombre" value=""/><br/>

  <label>Selecciona el tipo de harware para implementar</label><br/>
  <select name="tipoHardware">
    <option value="ram">RAM</option>
    <option value="discos">Discos</option>
    <option value="cables">Cables</option>
    <option value="TarjGraf">Tarjetas graficas</option>
    <option value="FuenteAlimentacion">Fuente de alimentacion</option>
    <option value="Procesador">Procesador</option>
    <option value="Refrigeracion">Refrigeración</option>
    <option value="Placa_base">Placa base</option>
    <option value="Torre">Torres</option>
	<option value="Monitor">Monitor</option>
	<option value="Raton">Raton</option>
	<option value="Tarjeta_de_red">Tarjeta de red</option>
	<option value="Tarjeta_de_sonido">Tarjeta de sonido</option>
    <option value="OrdenadorPremontados">Ordenador Premontados</option>
  </select><br/>

  <label>¿Su producto tiene más de 5 años?</label>
  <input type="radio" name="masCincoAgnos" value="si"/> Sí
  <input type="radio" name="masCincoAgnos" value="no"/> No<br/>

  <label for="precio">Introduzca el precio (no pongas €)</label><br/>
  <input type="text" id="precio" name="precio" value=""/><br/>

  <label for="imagen">Imagen del producto</label><br/>
  <input type="file" name="imagen" id="imagen" accept="image/*" onchange="previsualizarImagen(this)">

  <div id="preview-container" style="display:none; margin: 10px 0;">
    <p style="font-size:13px; color:#365983; margin: 0 0 6px 0;">Vista previa:</p>
    <img id="preview-img" src="" alt="Vista previa"
         style="max-width: 200px; max-height: 200px; border-radius: 8px;
                border: 2px solid #9dbce2; box-shadow: 0 2px 8px rgba(29,95,184,0.15);
                object-fit: contain; background: #f8fbff; padding: 4px; display:block;">
  </div>

  <br/>
  <label for="stock">Introduzca el stock</label><br/>
  <input type="text" id="stock" name="stock" value=""/>

  <label>Introduce un comentario</label><br/>
  <textarea name="comentario"></textarea><br/>

  <input type="submit" name="accion" value="Insert"/>
  <input type="submit" name="accionFichero" value="Guardar Archivo"/>
  <input type="hidden" name="form_origen" value="insert">
  <input type="submit" name="accionFichero" value="Abrir Archivo"/>
</form>

<a href="admin_chatbot.php" class="admin-float-btn">
    <div class="admin-icon-circle">
        <span class="admin-emoji">⚙️</span>
    </div>
    <span class="admin-label">Panel Gestión Chat</span>
</a>

<script type="text/javascript">

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

function validarFormulario() {
    const nombre = document.getElementById("nombre").value.trim();
    const masCincoAgnos = document.querySelector("input[name='masCincoAgnos']:checked");
    const precio = document.getElementById("precio").value.trim();
    const stock = document.getElementById("stock").value.trim();
    const imagen = document.getElementById("imagen");

    if (!nombre) {
        alert("Por favor, ingrese un nombre.");
        return false;
    }
    if (!masCincoAgnos) {
        alert("Por favor, seleccione si tiene más de 5 años.");
        return false;
    }
    if (isNaN(precio) || precio === "" || Number(precio) <= 0) {
        alert("El precio debe ser un número positivo.");
        return false;
    }
    if (imagen.files.length === 0) {
        alert("Por favor, seleccione una imagen para el producto.");
        return false;
    }
    if (isNaN(stock) || stock === "" || Number(stock) <= 0) {
        alert("El stock debe ser un número positivo.");
        return false;
    }
    return true;
}

</script>
</body>
</html>