<?php
require_once("../conexion.php");

$resultado = $mysql->query("UPDATE marca SET marca_estado = 1 WHERE id_marca = $_REQUEST[id_marca]");

if ($resultado) {
	echo 'Marca recuperada correctamente';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
