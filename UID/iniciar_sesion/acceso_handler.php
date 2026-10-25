<?php
session_start();
require_once __DIR__ . '/../../limitador.php';

// Comprueba la contrasena contra lo guardado en la base de datos.
// - Normal: lo guardado es un hash y se comprueba con password_verify().
// - Cuenta antigua que aun siga en texto plano: se acepta y se convierte a hash
//   en ese mismo momento, asi nadie se queda fuera. Despues ya no hace falta.
// Vuelve al formulario con un mensaje, SIN perder el usuario que se habia escrito
function login_fallo($mensaje, $usuario) {
    $_SESSION['login_error'] = $mensaje;
    $_SESSION['login_usuario'] = $usuario;
    header("Location: acceso.php");
    exit();
}

// Claves del contador de intentos para lo que se ha escrito en el login:
//  - una por cada CUENTA que coincide (da igual si se escribe el nombre o el email de esa cuenta)
//  - una por el texto escrito (asi tambien se limitan los usuarios que no existen)
function claves_intentos($conexion, $ip, $usuario) {
    $claves = ['login|' . $ip . '|' . mb_strtolower($usuario)];

    $q = $conexion->prepare("SELECT id_administrador FROM administradores WHERE usuario = ?");
    $q->bind_param('s', $usuario);
    $q->execute();
    $r = $q->get_result();
    while ($f = $r->fetch_assoc()) {
        $claves[] = 'login|' . $ip . '|admin|' . $f['id_administrador'];
    }
    $q->close();

    $q = $conexion->prepare("SELECT id_cliente FROM cliente WHERE nombre = ? OR email = ?");
    $q->bind_param('ss', $usuario, $usuario);
    $q->execute();
    $r = $q->get_result();
    while ($f = $r->fetch_assoc()) {
        $claves[] = 'login|' . $ip . '|cliente|' . $f['id_cliente'];
    }
    $q->close();

    return $claves;
}

function verificar_y_actualizar($conexion, $tabla, $columnaId, $id, $guardada, $clave) {
    $guardada = (string)$guardada;
    $esHash = (bool)preg_match('/^\$2[aby]\$\d\d\$/', $guardada);
    $ok = password_verify($clave, $guardada);
    if (!$ok && !$esHash && $guardada !== '' && hash_equals($guardada, $clave)) {
        $ok = true;
    }
    if ($ok && (!$esHash || password_needs_rehash($guardada, PASSWORD_DEFAULT))) {
        try {
            $hash = password_hash($clave, PASSWORD_DEFAULT);
            // $tabla y $columnaId son texto fijo escrito aqui, nunca viene del usuario
            $up = $conexion->prepare("UPDATE $tabla SET contrasegna = ? WHERE $columnaId = ?");
            $up->bind_param('si', $hash, $id);
            $up->execute();
            $up->close();
        } catch (Throwable $e) {
            // si no se pudo guardar el hash, deja entrar igualmente
        }
    }
    return $ok;
}

$accion = $_POST['accion'] ?? '';

if ($accion === 'login') {
    $usuario = trim($_POST['usuario_login'] ?? '');
    $clave = trim($_POST['clave_login'] ?? '');

    if ($usuario === '' || $clave === '') {
        login_fallo('Debes completar usuario y contraseña.', $usuario);
    }

    // Limite de intentos: 5 fallos por CUENTA y 20 por conexion cada 15 minutos.
    // Es por cuenta: si fallas con el nombre y luego pruebas con el email, cuenta como la misma.
    $ip = lim_ip();
    $claveIp = 'login-ip|' . $ip;

    $conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME')) or die("Error de conexion");

    $clavesIntentos = claves_intentos($conexion, $ip, $usuario);
    $bloqueado = lim_excedido($claveIp, 20, 900);
    foreach ($clavesIntentos as $k) {
        if (lim_excedido($k, 5, 900)) { $bloqueado = true; }
    }
    if ($bloqueado) {
        mysqli_close($conexion);
        login_fallo('Demasiados intentos fallidos. Espera 15 minutos y vuelve a probar.', $usuario);
    }

    // Las contrasenas se guardan con hash (bcrypt + sal aleatoria):
    // no se puede "buscar" la contrasena en la consulta, se trae el hash y se
    // comprueba con password_verify().

    // 1) Administradores
    $stmt = $conexion->prepare("SELECT id_administrador, contrasegna FROM administradores WHERE usuario = ?");
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $resAdmin = $stmt->get_result();
    while ($admin = $resAdmin->fetch_assoc()) {
        if (verificar_y_actualizar($conexion, 'administradores', 'id_administrador', $admin['id_administrador'], $admin['contrasegna'], $clave)) {
            lim_limpiar($clavesIntentos[0]);
            lim_limpiar('login|' . $ip . '|admin|' . $admin['id_administrador']);
            session_regenerate_id(true);   // sesion nueva al entrar (evita fijacion de sesion)
            $_SESSION['id_administrador'] = $admin['id_administrador'];
            $stmt->close();
            mysqli_close($conexion);
            header("Location: ../insert.php");
            exit();
        }
    }
    $stmt->close();

    // 2) Clientes (puede haber varios con el mismo nombre: se prueba cada uno)
    $stmt = $conexion->prepare("SELECT id_cliente, nombre, email, contrasegna FROM cliente WHERE nombre = ? OR email = ?");
    $stmt->bind_param('ss', $usuario, $usuario);
    $stmt->execute();
    $resCliente = $stmt->get_result();
    while ($cliente = $resCliente->fetch_assoc()) {
        if (verificar_y_actualizar($conexion, 'cliente', 'id_cliente', $cliente['id_cliente'], $cliente['contrasegna'], $clave)) {
            lim_limpiar($clavesIntentos[0]);
            lim_limpiar('login|' . $ip . '|cliente|' . $cliente['id_cliente']);
            session_regenerate_id(true);
            $_SESSION['id_cliente'] = $cliente['id_cliente'];
            $_SESSION['nombre'] = $cliente['nombre'];
            $_SESSION['email'] = $cliente['email'];
            $stmt->close();
            mysqli_close($conexion);
            header("Location: ../../cliente.php");
            exit();
        }
    }
    $stmt->close();

    mysqli_close($conexion);
    foreach ($clavesIntentos as $k) {
        lim_registrar($k, 900);
    }
    lim_registrar($claveIp, 900);
    login_fallo('Usuario o contraseña incorrectos.', $usuario);
}

// Boton "Entrar como cliente demo" (solo si DEMO_MODE=1 en el .env)
if ($accion === 'demo_cliente') {
    if (getenv('DEMO_MODE') !== '1') {
        login_fallo('El acceso de demostración no está activado.', '');
    }
    $conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME')) or die("Error de conexion");
    $email = 'demo@opendlc.test';
    $stmt = $conexion->prepare("SELECT id_cliente, nombre, email FROM cliente WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $demo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    mysqli_close($conexion);
    if (!$demo) {
        login_fallo('No existe la cuenta de demostración.', '');
    }
    session_regenerate_id(true);
    $_SESSION['id_cliente'] = $demo['id_cliente'];
    $_SESSION['nombre'] = $demo['nombre'];
    $_SESSION['email'] = $demo['email'];
    header("Location: ../../cliente.php");
    exit();
}

if ($accion === 'registro_cliente') {
    $nombre        = trim($_POST['nombre'] ?? '');
    $apellido      = trim($_POST['apellido'] ?? '');
    $cp            = trim($_POST['cp'] ?? '');
    $correo        = trim($_POST['correo'] ?? '');
    $telefono      = trim($_POST['telefono'] ?? '');
    $contrasena    = trim($_POST['contrasena_registro'] ?? '');
    $contrasenaAgain = trim($_POST['contrasena_again'] ?? '');

    // Función helper: guarda datos en sesión y redirige con error
    function redirigir_con_error($msg, $datos) {
        $_SESSION['registro_error'] = $msg;
        $_SESSION['registro_datos'] = $datos;
        header("Location: acceso.php");
        exit();
    }

    $datos = [
        'nombre'   => $nombre,
        'apellido' => $apellido,
        'cp'       => $cp,
        'correo'   => $correo,
        'telefono' => $telefono,
		
		
    ];

    if ($nombre === '' || $apellido === '' || $cp === '' ||
        $correo === '' || $contrasena === '' || $contrasenaAgain === ''
    ) {
        redirigir_con_error('Faltan campos obligatorios para el registro.', $datos);
    }

    if (strlen($contrasena) < 8) {
        redirigir_con_error('La contraseña debe tener al menos 8 caracteres.', $datos);
    }

    if (lim_excedido('registro|' . lim_ip(), 5, 3600)) {
        redirigir_con_error('Demasiados registros desde tu conexión. Inténtalo más tarde.', $datos);
    }

    if ($contrasena !== $contrasenaAgain) {
        redirigir_con_error('Las contraseñas no coinciden.', $datos);
    }

    $conexion = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME')) or die("Error de conexion");

    $nombreSafe    = mysqli_real_escape_string($conexion, $nombre);
    $apellidoSafe  = mysqli_real_escape_string($conexion, $apellido);
    $cpSafe        = mysqli_real_escape_string($conexion, $cp);
    $correoSafe    = mysqli_real_escape_string($conexion, $correo);
    $telefonoSafe  = mysqli_real_escape_string($conexion, $telefono);

    $sqlCorreo = "SELECT email FROM cliente WHERE email='$correoSafe' LIMIT 1";
    $resultadoCorreo = mysqli_query($conexion, $sqlCorreo);

    if ($resultadoCorreo && mysqli_num_rows($resultadoCorreo) > 0) {
        mysqli_close($conexion);
        redirigir_con_error('Este correo ya está registrado.', $datos);
    }

    // password_hash genera una sal aleatoria distinta para cada contrasena
    // y la guarda dentro del propio hash: no hay que anadir ninguna a mano.
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    $stmtIns = $conexion->prepare("INSERT INTO cliente(nombre,apellido,cp,email,telefono,contrasegna) VALUES (?,?,?,?,?,?)");
    $stmtIns->bind_param('ssssss', $nombre, $apellido, $cp, $correo, $telefono, $hash);
    $stmtIns->execute() or die("Error en registro");
    $nuevoId = $stmtIns->insert_id;
    $stmtIns->close();

    lim_registrar('registro|' . lim_ip(), 3600);
    session_regenerate_id(true);
    $_SESSION['id_cliente'] = $nuevoId;
    $_SESSION['nombre']     = $nombre;
    $_SESSION['email']      = $correo;
    $_SESSION['cp']         = $cp;
    $_SESSION['telefono']   = $telefono;

    // Limpiar datos de registro si los había
    unset($_SESSION['registro_error'], $_SESSION['registro_datos']);

    mysqli_close($conexion);
    header("Location: ../../cliente.php");
    exit();
}

echo "<script>alert('Accion no valida.'); window.location.href='acceso.php';</script>";
exit();
?>