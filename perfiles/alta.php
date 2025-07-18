<?php
require("conexion.php");

$resultado = $mysql->query("insert into perfil (perfil_descripcion, perfil_estado) values ('$_REQUEST[perfil_descripcion]', 1)");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Perfiles&ruta=perfiles&archivo=listado.php',
		'Se dió de alta el perfil correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
