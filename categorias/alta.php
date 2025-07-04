<?php
	require("../conexion.php");
	
	$mysql->query("insert into categoria (categoria_descripcion, categoria_estado) values ('$_REQUEST[categoria_descripcion]', 1)") or die($mysql->error);
	
	echo 'Se dió de alta la categoria correctamente';
	echo '<br>';
	echo '<button onclick="location.href=\'index.php\'">Volver</button>';
	
	$mysql->close();

 ?>
