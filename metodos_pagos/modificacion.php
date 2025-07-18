
<?php
require("conexion.php");
$resultado = $mysql->query("update metododepago set 
			metododepago_descripcion='$_REQUEST[metododepago_descripcion]'
			where id_metododepago=$_REQUEST[id_metododepago]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Métodos de pago&ruta=metodos_pagos&archivo=listado.php',
		'Se modificaron los datos del método de pago correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>