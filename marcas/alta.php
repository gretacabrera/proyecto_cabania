<?php
	require("../conexion.php");
	require("../includes/mensajes.php");
	
	$resultado = $mysql->query("insert into marca (marca_descripcion, marca_estado) values ('$_REQUEST[marca_descripcion]', 1)");
	
	if ($resultado) {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Se dió de alta la marca correctamente', 'exito');
	} else {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
	}
?>
