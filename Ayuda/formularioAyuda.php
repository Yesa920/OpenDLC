<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="../css/UID.css" media="screen" />
<script type="text/javascript">
window.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
    const usuario = document.getElementById("usuario");
    const cuenta = document.getElementById("cuenta");

    form.addEventListener("submit", (e) => {
        // Si los dos están vacíos
        if (usuario.value.trim() === "" && cuenta.value.trim() === "") {
            e.preventDefault(); // Detiene el envío
            alert("Debes rellenar al menos usuario o nombre de la cuenta.");
        }
    });
});
</script>
<head>
<title>Proveedores Insert</title>
</head>
<body>
<a href="../pagina_principal.html" class="botonAbajo">←Ir a la pagina principal</a>
<h1>Para enviar una queja</h1>
<form method="post" action="ayuda.php">
<label for="usuario">Usuario (si no tiene usuario no lo ponga)</label>
<br/>
<input type="text" id="usuario" name="nombre" value=""/>
<br/>
<label for="cuenta">Correo</label>
<br/>
<input type="email" id="cuenta" name="cuenta" value=""/>
<br/>
<label for="telefono">Telefono(Opcional)</label>
<br/>
<input type="text" id="telefono" name="telefono" value="" minlength="9" maxlength="9"/>
<br/>
<label>Introduce un comentario</label>
<br/>
<textarea name="comentario"></textarea>
<br/>
<input type="submit" name="accion" value="Confirmar Comentario"/>
</form>

</body>
</html>