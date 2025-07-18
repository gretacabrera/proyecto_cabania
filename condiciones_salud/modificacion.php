
<?php
require("conexion.php");
$resultado = $mysql->query("update condicionsalud set 
			condicionsalud_descripcion='$_REQUEST[condicionsalud_descripcion]'
			where id_condicionsalud=$_REQUEST[id_condicionsalud]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Condiciones de salud&ruta=condiciones_salud&archivo=listado.php',
		'Se modificaron los datos de la condición de salud correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>