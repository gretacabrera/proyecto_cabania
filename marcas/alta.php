<?php
require("conexion.php");
$resultado = $mysql->query("insert into marca (marca_descripcion, marca_estado) values ('$_REQUEST[marca_descripcion]', 1)");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Marcas&ruta=marcas&archivo=listado.php',
		'Se dió de alta la marca correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
?>
