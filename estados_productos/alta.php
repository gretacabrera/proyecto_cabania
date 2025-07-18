<?php
require("conexion.php");

$resultado = $mysql->query("insert into estadoproducto (estadoproducto_descripcion, estadoproducto_estado) values ('$_REQUEST[estadoproducto_descripcion]', 1)");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Estados de productos&ruta=estados_productos&archivo=listado.php',
		'Se dió de alta el estado de producto correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
