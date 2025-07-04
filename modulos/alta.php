<?php
	require("../conexion.php");
	
	$mysql->query("insert into modulo (modulo_descripcion, modulo_ruta, modulo_estado) values ('$_REQUEST[modulo_descripcion]', '$_REQUEST[modulo_ruta]', 1)") or die($mysql->error);
	
	echo 'Se dió de alta el modulo correctamente';
	echo '<br>';
	echo '<button onclick="location.href=\'index.php\'">Volver</button>';
	
	$mysql->close();

 ?>
