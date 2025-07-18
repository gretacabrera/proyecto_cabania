<?php
require_once("../conexion.php");

$resultado = $mysql->query("UPDATE tiposervicio SET tiposervicio_estado = 1 WHERE id_tiposervicio = $_REQUEST[id_tiposervicio]");

if ($resultado) {
	echo 'Tipo de servicio recuperado correctamente';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
