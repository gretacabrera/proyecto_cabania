<?php
require_once("../conexion.php");

$resultado = $mysql->query("update condicionsalud set condicionsalud_estado = 0 WHERE id_condicionsalud=$_REQUEST[id_condicionsalud]");

if ($resultado) {
	// Solo devolver mensaje de texto para AJAX
	echo 'Se dió de baja correctamente la condición de salud';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>