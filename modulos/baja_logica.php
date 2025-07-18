<?php
require_once("../conexion.php");

$resultado = $mysql->query("update modulo set modulo_estado = 0 WHERE id_modulo=$_REQUEST[id_modulo]");

if ($resultado) {
	echo 'Se dió de baja correctamente el módulo';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>