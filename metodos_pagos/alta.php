<?php
require("conexion.php");

$resultado = $mysql->query("insert into metododepago (metododepago_descripcion, metododepago_estado) values ('$_REQUEST[metododepago_descripcion]', 1)");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Metodos de pago&ruta=metodos_pagos&archivo=listado.php',
		'Se dió de alta el método de pago correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
