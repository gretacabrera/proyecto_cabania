<?php	
require_once("../conexion.php");

$resultado = $mysql->query("update servicio set servicio_estado = 0 WHERE id_servicio=$_REQUEST[id_servicio]");

if ($resultado) {
	echo 'Se dió de baja correctamente el servicio';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>