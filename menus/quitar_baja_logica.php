<?php
require_once("../conexion.php");

$resultado = $mysql->query("UPDATE menu SET menu_estado = 1 WHERE id_menu = $_REQUEST[id_menu]");

if ($resultado) {
	echo 'Menu recuperado correctamente';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
