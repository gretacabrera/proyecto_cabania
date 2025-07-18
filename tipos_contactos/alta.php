<?php
require("conexion.php");

$resultado = $mysql->query("insert into tipocontacto (tipocontacto_descripcion, tipocontacto_estado) values ('$_REQUEST[tipocontacto_descripcion]', 1)") or die($mysql->error);

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Tipos de Contacto&ruta=tipos_contactos&archivo=listado.php',
		'Se dió de alta el tipo de contacto correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
