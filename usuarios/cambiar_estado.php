<?php
require_once("../conexion.php");

switch ($_REQUEST['accion']) {
    case "bloquear":
        $estado = 2; // Bloqueado
        $mensaje = 'Se bloqueó correctamente el usuario';
        break;
    case "desbloquear":
        $estado = 1; // Activo
        $mensaje = 'Se desbloqueó correctamente el usuario';
        break;
    case "baja":
        $estado = 3; // Baja
        $mensaje = 'Se dio de baja correctamente el usuario';
        break;
    case "recuperar":
        $estado = 1; // Activo
        $mensaje = 'Se recuperó correctamente el usuario';
        break;
    default:
        $mensaje = 'Acción no reconocida';
        break;
}

$resultado = $mysql->query("update usuario set usuario_estado = $estado WHERE id_usuario=$_REQUEST[id_usuario]");

if ($resultado) {
    echo $mensaje;
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>