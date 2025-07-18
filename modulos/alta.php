<?php
require("conexion.php");

$resultado = $mysql->query("insert into modulo (modulo_descripcion, modulo_ruta, modulo_estado) values ('$_REQUEST[modulo_descripcion]', '$_REQUEST[modulo_ruta]', 1)");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Módulos&ruta=modulos&archivo=listado.php',
		'Se dió de alta el módulo correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
?>
