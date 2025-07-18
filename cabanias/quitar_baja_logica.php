<?php
require("conexion.php");
$resultado = $mysql->query("update cabania set cabania_estado=1 where id_cabania=$_REQUEST[id_cabania]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Cabañas&ruta=cabanias&archivo=listado.php',
		'Se recuperó la cabaña correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
