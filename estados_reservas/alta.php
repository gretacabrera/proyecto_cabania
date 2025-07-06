<?php
	require("../conexion.php");
	require_once("../funciones.php");
	
	$resultado = $mysql->query("insert into estadoreserva (estadoreserva_descripcion, estadoreserva_estado) values ('$_REQUEST[estadoreserva_descripcion]', 1)");
	
	if ($resultado) {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Se dió de alta el estado de reserva correctamente', 'exito');
	} else {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
	}
?>
