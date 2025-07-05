<?php
	require("../conexion.php");
	require("../includes/mensajes.php");
	
	$resultado = $mysql->query("insert into categoria (categoria_descripcion, categoria_estado) values ('$_REQUEST[categoria_descripcion]', 1)");
	
	if ($resultado) {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Se dió de alta la categoría correctamente', 'exito');
	} else {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
	}
?>
