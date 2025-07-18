<?php
require("conexion.php");

$resultado = $mysql->query("insert into perfil_modulo (rela_perfil, rela_modulo, perfilmodulo_estado) values ($_REQUEST[rela_perfil], $_REQUEST[rela_modulo], 1)");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Asignar Módulo a Perfil&ruta=perfiles_modulos&archivo=listado.php',
		'Se dió de alta la asignación de módulo al perfil correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
