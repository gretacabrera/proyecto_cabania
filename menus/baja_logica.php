<?php
require_once("../conexion.php");

$resultado = $mysql->query("update menu set menu_estado = 0 WHERE id_menu=$_REQUEST[id_menu]");

if ($resultado) {
	echo 'Se dió de baja correctamente el menu';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
