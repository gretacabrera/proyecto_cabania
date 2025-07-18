<?php
require_once("../conexion.php");

$resultado = $mysql->query("update categoria set categoria_estado = 0 WHERE id_categoria=$_REQUEST[id_categoria]");

if ($resultado) {
	echo 'Se dió de baja correctamente la categoría';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>