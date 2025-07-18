
<?php
require("conexion.php");

$resultado = $mysql->query("update perfil set 
			perfil_descripcion='$_REQUEST[perfil_descripcion]'
			where id_perfil=$_REQUEST[id_perfil]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Perfiles&ruta=perfiles&archivo=listado.php',
		'Se modificaron los datos del perfil correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>