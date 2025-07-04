<?php
	require("../conexion.php");
	
	$mysql->query("insert into servicio (servicio_nombre, servicio_descripcion, servicio_precio, rela_tiposervicio, servicio_estado) values ('$_REQUEST[servicio_nombre]', 
	'$_REQUEST[servicio_descripcion]', $_REQUEST[servicio_precio], $_REQUEST[rela_tiposervicio], $_REQUEST[servicio_estado])") or die($mysql->error);
	
	echo 'Se dió de alta el servicio correctamente';
	echo '<br>';
	echo '<button onclick="location.href=\'index.php\'">Volver</button>';
	
	$mysql->close();

 ?>
