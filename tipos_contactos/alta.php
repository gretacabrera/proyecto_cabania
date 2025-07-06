<?php
	require("../conexion.php");
	
	$mysql->query("insert into tipocontacto (tipocontacto_descripcion, tipocontacto_estado) values ('$_REQUEST[tipocontacto_descripcion]', 1)") or die($mysql->error);
	
	echo 'Se dió de alta el tipo de contacto correctamente';
	echo '<br>';
	echo '<button onclick="location.href=\'index.php\'">Volver</button>';
	
	$mysql->close();

 ?>
