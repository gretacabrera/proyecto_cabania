<?php
require_once("../conexion.php");

$resultado = $mysql->query("update metododepago set metododepago_estado = 0 WHERE id_metododepago=$_REQUEST[id_metododepago]");

if ($resultado) {
	echo 'Se dió de baja correctamente el método de pago';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>