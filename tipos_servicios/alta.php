<?php
require("conexion.php");
	
$resultado = $mysql->query("insert into tiposervicio (tiposervicio_descripcion, tiposervicio_estado) values ('$_REQUEST[tiposervicio_descripcion]', 1)") or die($mysql->error);

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Tipos de Servicio&ruta=tipos_servicios&archivo=listado.php',
		'Se dió de alta el tipo de servicio correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();

?>
