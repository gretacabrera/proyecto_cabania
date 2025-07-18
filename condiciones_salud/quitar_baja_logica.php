<?php
require_once("../conexion.php");

$resultado = $mysql->query("UPDATE condicionsalud SET condicionsalud_estado = 1 WHERE id_condicionsalud = $_REQUEST[id_condicionsalud]");

if ($resultado) {
	// Solo devolver mensaje de texto para AJAX
	echo 'Condición de salud recuperada correctamente';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
