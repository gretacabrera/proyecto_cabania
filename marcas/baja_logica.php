<?php
require_once("../conexion.php");

$resultado = $mysql->query("update marca set marca_estado = 0 WHERE id_marca=$_REQUEST[id_marca]");

if ($resultado) {
	echo 'Se dió de baja correctamente la marca';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>