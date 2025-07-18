<?php
require("conexion.php");
$resultado = $mysql->query("insert into producto (producto_nombre, producto_descripcion, producto_precio, producto_stock, producto_foto, rela_marca, rela_categoria, rela_estadoproducto) values ('$_REQUEST[producto_nombre]', 
'$_REQUEST[producto_descripcion]', $_REQUEST[producto_precio], $_REQUEST[producto_stock], '$_REQUEST[producto_foto]', $_REQUEST[rela_marca], $_REQUEST[rela_categoria], $_REQUEST[rela_estadoproducto])");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Productos&ruta=productos&archivo=listado.php',
		'Se dió de alta el producto correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
