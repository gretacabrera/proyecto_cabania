<?php
	require("../conexion.php");
	require_once("../funciones.php");
	
	$resultado = $mysql->query("insert into modulo (modulo_descripcion, modulo_ruta, modulo_estado) values ('$_REQUEST[modulo_descripcion]', '$_REQUEST[modulo_ruta]', 1)");
	
	if ($resultado) {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Se dió de alta el módulo correctamente', 'exito');
	} else {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
	}
?>
