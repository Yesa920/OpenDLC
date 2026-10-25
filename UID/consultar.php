<?php require_once __DIR__ . '/../auth.php'; requerir_admin(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="../css/UID.css" media="screen" />
<script type="text/javascript" src="script.js">
</script>
<head>
<title>Proveedores Consultar</title>
</head>
<body>
<div class="top-bar">
  <a href="../logout.php" class="btn inicio">🚪 Cerrar sesión</a>

  <div class="nav-right">
	<a href="insert.php" class="btn">➕ Insert</a>
    <a href="update.php" class="btn">✏️ Update</a>
    <a href="delete.php" class="btn danger">🗑 Delete</a>
    <a href="ventas_globales.php" class="btn">📊 Ventas</a>
  </div>
</div>
<h1>Para hacer un consultar complete</h1>
<form method="post" action="accionBotonIUD.php">
<label for="codigoproducto">Ingrese la clave primaria del articulo</label>
<input type="text" id="codigoproducto" name="codigoproducto" value=""/>
<br/>
<input type="submit" name="accion" value="Confirmar Consultar"/>
</form>
<a href="admin_chatbot.php" class="admin-float-btn">
    <div class="admin-icon-circle">
        <span class="admin-emoji">⚙️</span>
    </div>
    <span class="admin-label">Panel Gestión Chat</span>
</a>
</body>
</html>