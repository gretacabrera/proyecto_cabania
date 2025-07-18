
<?php
require("conexion.php");
$resultado = $mysql->query("update producto set 
			producto_nombre='$_REQUEST[producto_nombre]',
			producto_descripcion='$_REQUEST[producto_descripcion]',
			producto_precio=$_REQUEST[producto_precio],
			producto_stock=$_REQUEST[producto_stock],
			producto_foto='$_REQUEST[producto_foto]',
			rela_marca=$_REQUEST[rela_marca],
			rela_categoria=$_REQUEST[rela_categoria],
			rela_estadoproducto=$_REQUEST[rela_estadoproducto]
			where id_producto=$_REQUEST[id_producto]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Productos&ruta=productos&archivo=listado.php',
		'Se modificaron los datos del producto correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>