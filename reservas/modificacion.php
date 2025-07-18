
<?php
require("conexion.php");
$resultado = $mysql->query("update reserva set 
			reserva_fhinicio='$_REQUEST[reserva_fhinicio]',
			reserva_fhfin='$_REQUEST[reserva_fhfin]',
			rela_cabania=$_REQUEST[rela_cabania],
			rela_estadoreserva=$_REQUEST[rela_estadoreserva]
			where id_reserva=$_REQUEST[id_reserva]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Reservas&ruta=reservas&archivo=listado.php',
		'Se modificaron los datos de la reserva correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>

  ?>