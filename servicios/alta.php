<?php
require("conexion.php");

$resultado = $mysql->query("insert into servicio (servicio_nombre, servicio_descripcion, servicio_precio, rela_tiposervicio, servicio_estado) values ('$_REQUEST[servicio_nombre]', '$_REQUEST[servicio_descripcion]', $_REQUEST[servicio_precio], $_REQUEST[rela_tiposervicio], 1)");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Servicios&ruta=servicios&archivo=listado.php',
		'Se dió de alta el servicio correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
