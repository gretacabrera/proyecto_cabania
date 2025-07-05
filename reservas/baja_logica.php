<?php
	require("../conexion.php");
	require("../includes/mensajes.php");

	$resultado = $mysql->query("update reserva set rela_estadoreserva = 6 WHERE id_reserva=$_REQUEST[id_reserva]");

	if ($resultado) {
	  echo 'Se dió de baja correctamente la reserva';
  } else {
	  echo 'Error: ' . $mysql->error;
  }

  $mysql->close();
?>