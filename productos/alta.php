<?php
	require("../conexion.php");
	
	$mysql->query("insert into producto (producto_nombre, producto_descripcion, producto_precio, producto_stock, producto_foto, rela_marca, rela_categoria, rela_estadoproducto) values ('$_REQUEST[producto_nombre]', 
	'$_REQUEST[producto_descripcion]', $_REQUEST[producto_precio], $_REQUEST[producto_stock], '$_REQUEST[producto_foto]', $_REQUEST[rela_marca], $_REQUEST[rela_categoria], $_REQUEST[rela_estadoproducto])") or die($mysql->error);
	
	echo 'Se dió de alta el producto correctamente';
	echo '<br>';
	echo '<button onclick="location.href=\'index.php\'">Volver</button>';
	
	$mysql->close();

 ?>
