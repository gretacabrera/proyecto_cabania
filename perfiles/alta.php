<?php
	require("../conexion.php");
	
	$mysql->query("insert into perfil (perfil_descripcion, perfil_estado) values ('$_REQUEST[perfil_descripcion]', 1)") or die($mysql->error);
	
	echo 'Se dió de alta el perfil correctamente';
	echo '<br>';
	echo '<button onclick="location.href=\'index.php\'">Volver</button>';
	
	$mysql->close();

 ?>
