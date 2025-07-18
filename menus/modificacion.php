<?php
require("conexion.php");
$resultado = $mysql->query("update menu set 
			menu_nombre='$_REQUEST[menu_nombre]'
			where id_menu=$_REQUEST[id_menu]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Menus&ruta=menus&archivo=listado.php',
		'Se modificaron los datos del menu correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
