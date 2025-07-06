<?php
	require("../conexion.php");
	require_once("../funciones.php");
	
	$resultado = $mysql->query("insert into periodo (periodo_descripcion, periodo_fechainicio, periodo_fechafin, periodo_anio, periodo_orden, periodo_estado) values ('$_REQUEST[periodo_descripcion]', '$_REQUEST[periodo_fechainicio]', '$_REQUEST[periodo_fechafin]', $_REQUEST[periodo_anio], $_REQUEST[periodo_orden], 1)");
	
	if ($resultado) {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Se dió de alta el período correctamente', 'exito');
	} else {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
	}
?>
