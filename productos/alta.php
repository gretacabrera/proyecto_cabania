<?php
	require("../conexion.php");
	require("../includes/mensajes.php");
	
	$resultado = $mysql->query("insert into producto (producto_nombre, producto_descripcion, producto_precio, producto_stock, producto_foto, rela_marca, rela_categoria, rela_estadoproducto) values ('$_REQUEST[producto_nombre]', 
	'$_REQUEST[producto_descripcion]', $_REQUEST[producto_precio], $_REQUEST[producto_stock], '$_REQUEST[producto_foto]', $_REQUEST[rela_marca], $_REQUEST[rela_categoria], $_REQUEST[rela_estadoproducto])");
	
	if ($resultado) {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Se dió de alta el producto correctamente', 'exito');
	} else {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
	}
?>
