<?php
require("conexion.php");
	
$resultado = $mysql->query("insert into condicionsalud (condicionsalud_descripcion, condicionsalud_estado) values ('$_REQUEST[condicionsalud_descripcion]', 1)");
	
if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Condiciones de Salud&ruta=condiciones_salud&archivo=listado.php',
		'Se dió de alta la condición de salud correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
