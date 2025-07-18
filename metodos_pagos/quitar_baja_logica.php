<?php
require_once("../conexion.php");

$resultado = $mysql->query("UPDATE metododepago SET metododepago_estado = 1 WHERE id_metododepago = $_REQUEST[id_metododepago]");

if ($resultado) {
	echo 'Método de pago recuperado correctamente';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
