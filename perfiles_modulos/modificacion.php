<?php
require("conexion.php");

$resultado = $mysql->query("update perfil_modulo set 
			rela_perfil=$_REQUEST[rela_perfil],
			rela_modulo=$_REQUEST[rela_modulo]
			where id_perfilmodulo=$_REQUEST[id_perfilmodulo]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Asignar Módulo a Perfil&ruta=perfiles_modulos&archivo=listado.php',
		'Se modificaron los datos de la asignación perfil-módulo correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>