
<?php
require("conexion.php");

// Construir la consulta de actualización con el campo rela_menu opcional
$rela_menu = isset($_REQUEST['rela_menu']) && $_REQUEST['rela_menu'] != '' ? $_REQUEST['rela_menu'] : 'NULL';

$resultado = $mysql->query("update modulo set 
			modulo_descripcion='$_REQUEST[modulo_descripcion]',
			modulo_ruta='$_REQUEST[modulo_ruta]',
			rela_menu=$rela_menu
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