
<?php
require("conexion.php");
$resultado = $mysql->query("update marca set 
			marca_descripcion='$_REQUEST[marca_descripcion]'
			where id_marca=$_REQUEST[id_marca]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Marcas&ruta=marcas&archivo=listado.php',
		'Se modificaron los datos de la marca correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>