<?php
require("conexion.php");
	
$resultado = $mysql->query("insert into estadopersona (estadopersona_descripcion, estadopersona_estado) values ('$_REQUEST[estadopersona_descripcion]', 1)");
	
if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Estados de personas&ruta=estados_personas&archivo=listado.php',
		'Se dió de alta el estado de persona correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>