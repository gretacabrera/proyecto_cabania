<?php
require("conexion.php");

$resultado = $mysql->query("update estadopersona set 
			estadopersona_descripcion='$_REQUEST[estadopersona_descripcion]'
			where id_estadopersona=$_REQUEST[id_estadopersona]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Estados de personas&ruta=estados_personas&archivo=listado.php',
		'Se modificaron los datos del estado de persona correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>