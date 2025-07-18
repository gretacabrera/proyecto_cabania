<?php
require("conexion.php");
$resultado = $mysql->query("insert into categoria (categoria_descripcion, categoria_estado) values ('$_REQUEST[categoria_descripcion]', 1)");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Categorías&ruta=categorias&archivo=listado.php',
		'Se dió de alta la categoría correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
