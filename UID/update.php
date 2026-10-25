<?php require_once __DIR__ . '/../auth.php'; requerir_admin(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="../css/UID.css" media="screen" />
<script type="text/javascript">
function validarFormulario() {
	const id_producto = document.getElementById("id_producto").value.trim();
	
	if (!id_producto){
		alert("Por favor, Ingrese la clave primaria para continuar.");
		window.location.href = 'update.php';
		return false;
	}
    return true;
}
</script>
<head>
<title>Proveedores Insert</title>
</head>
<body>
<div class="top-bar">
  <a href="../logout.php" class="btn inicio">🚪 Cerrar sesión</a>

  <div class="nav-right">
    <a href="insert.php" class="btn">➕ Insert</a>
    <a href="delete.php" class="btn danger">🗑 Delete</a>
    <a href="consultar.php" class="btn">📄 Consultar</a>
	<a href="ventas_globales.php" class="btn">📊 Ventas</a>
  </div>
</div>
<h1>Para hacer un UPDATE</h1>
<form method="post" action="accionBotonIUD.php">
<label for="id_producto">Ingrese el codigo producto del producto</label>
<br/>
<input type="text" id="id_producto" name="id_producto" value=""/>
<label for="nombre">Ingrese el nombre del producto</label>
<br/>
<input type="text" id="nombre" name="nombre" value=""/>
<br/>
<label>Selecciona el tipo de harware para implementar</label>
<br/>
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
</select>
<br/>
<label>¿Su producto tiene más de 5 años?</label>
<input type="radio" name="masCincoAgnos" value="si">Sí</input>
<input type="radio" name="masCincoAgnos" value="no">No</input>
<br/>
<label for="imagen">Imagen del producto</label>
<br/>
<input type="file" name="imagen" id="imagen" accept="image/*">
<br/>
<label for="precio">Introduzca el precio(sin el €)</label>
<br/>
<input type="text" id="precio" name="precio" value=""/>
<br/>
<label for="stock">Introduzca el stock</label>
<br/>
<input type="text" id="stock" name="stock" value=""/>
<br/>
<label>Introduce un comentario</label>
<br/>
<textarea name="comentario"></textarea>
<br/>

<input type="submit" name="accion" onclick="validarFormulario()" value="Confirmar Update"/>
<input type="submit" name="accionFichero" value="Guardar Archivo"/>
<input type="hidden" name="form_origen" value="update">
<input type="submit" name="accionFichero" value="Abrir Archivo"/>
</form>

<a href="admin_chatbot.php" class="admin-float-btn">
    <div class="admin-icon-circle">
        <span class="admin-emoji">⚙️</span>
    </div>
    <span class="admin-label">Panel Gestión Chat</span>
</a>
</body>
</html>