<?php
	require("../conexion.php");
	require_once("../funciones.php");
	
	$resultado = $mysql->query("insert into perfil (perfil_descripcion, perfil_estado) values ('$_REQUEST[perfil_descripcion]', 1)");
	
	if ($resultado) {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Se dió de alta el perfil correctamente', 'exito');
	} else {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
	}
?>
