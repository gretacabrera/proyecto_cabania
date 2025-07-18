<?php
require("conexion.php");

$resultado = $mysql->query("insert into periodo (periodo_descripcion, periodo_fechainicio, periodo_fechafin, periodo_anio, periodo_orden, periodo_estado) values ('$_REQUEST[periodo_descripcion]', '$_REQUEST[periodo_fechainicio]', '$_REQUEST[periodo_fechafin]', $_REQUEST[periodo_anio], $_REQUEST[periodo_orden], 1)");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Períodos&ruta=periodos&archivo=listado.php',
		'Se dió de alta el período correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
