<?php
	require("../conexion.php");
	
	$mysql->query("insert into marca (marca_descripcion, marca_estado) values ('$_REQUEST[marca_descripcion]', 1)") or die($mysql->error);
	
	echo 'Se dió de alta la marca correctamente';
	echo '<br>';
	echo '<button onclick="location.href=\'index.php\'">Volver</button>';
	
	$mysql->close();

 ?>
