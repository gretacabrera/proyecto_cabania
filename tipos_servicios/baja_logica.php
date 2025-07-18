<?php
require_once("../conexion.php");

$resultado = $mysql->query("update tiposervicio set tiposervicio_estado = 0 WHERE id_tiposervicio=$_REQUEST[id_tiposervicio]");

if ($resultado) {
	echo 'Se dió de baja correctamente el tipo de servicio';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>