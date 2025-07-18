<?php
require("conexion.php");

$resultado = $mysql->query("insert into estadoreserva (estadoreserva_descripcion, estadoreserva_estado) values ('$_REQUEST[estadoreserva_descripcion]', 1)");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Estados de reservas&ruta=estados_reservas&archivo=listado.php',
		'Se dió de alta el estado de reserva correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
