
<?php
require("conexion.php");

$resultado = $mysql->query("update modulo set 
			modulo_descripcion='$_REQUEST[modulo_descripcion]'
			where id_modulo=$_REQUEST[id_modulo]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Módulos&ruta=modulos&archivo=listado.php',
		'Se modificaron los datos del módulo correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>