
<?php
require("conexion.php");
$resultado = $mysql->query("update estadoreserva set 
			estadoreserva_descripcion='$_REQUEST[estadoreserva_descripcion]'
			where id_estadoreserva=$_REQUEST[id_estadoreserva]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Estados de reservas&ruta=estados_reservas&archivo=listado.php',
		'Se modificaron los datos del estado de reserva correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>