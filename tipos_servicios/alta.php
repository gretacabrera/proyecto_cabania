<?php
	require("../conexion.php");
	
	$mysql->query("insert into tiposervicio (tiposervicio_descripcion, tiposervicio_estado) values ('$_REQUEST[tiposervicio_descripcion]', 1)") or die($mysql->error);
	
	echo 'Se dió de alta el tipo de servicio correctamente';
	echo '<br>';
	echo '<button onclick="location.href=\'index.php\'">Volver</button>';
	
	$mysql->close();

 ?>
