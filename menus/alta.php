<?php
require("conexion.php");
$resultado = $mysql->query("insert into menu (menu_nombre, menu_orden, menu_estado) values ('$_REQUEST[menu_nombre]', $_REQUEST[menu_orden], 1)");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Menus&ruta=menus&archivo=listado.php',
		'Se dió de alta el menu correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
