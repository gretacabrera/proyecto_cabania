
<?php
require("conexion.php");
$resultado = $mysql->query("update estadoproducto set 
			estadoproducto_descripcion='$_REQUEST[estadoproducto_descripcion]'
			where id_estadoproducto=$_REQUEST[id_estadoproducto]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Estados de productos&ruta=estados_productos&archivo=listado.php',
		'Se modificaron los datos del estado de producto correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>