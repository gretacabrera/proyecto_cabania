<?php
	require("../conexion.php");
	require_once("../funciones.php");
	
	$resultado = $mysql->query("insert into metododepago (metododepago_descripcion, metododepago_estado) values ('$_REQUEST[metododepago_descripcion]', 1)");
	
	if ($resultado) {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Se dió de alta el método de pago correctamente', 'exito');
	} else {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
	}
?>
