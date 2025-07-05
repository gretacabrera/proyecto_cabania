<?php
	require("../conexion.php");
	require("../includes/mensajes.php");
	
	$resultado = $mysql->query("insert into servicio (servicio_nombre, servicio_descripcion, servicio_precio, rela_tiposervicio, servicio_estado) values ('$_REQUEST[servicio_nombre]', 
	'$_REQUEST[servicio_descripcion]', $_REQUEST[servicio_precio], $_REQUEST[rela_tiposervicio], $_REQUEST[servicio_estado])");
	
	if ($resultado) {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Se dió de alta el servicio correctamente', 'exito');
	} else {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
	}
?>
