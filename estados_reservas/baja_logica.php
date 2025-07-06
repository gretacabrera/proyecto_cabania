<?php
  require("../conexion.php");

  $resultado = $mysql->query("update estadoreserva set estadoreserva_estado = 0 WHERE id_estadoreserva=$_REQUEST[id_estadoreserva]");

  if ($resultado) {
	echo 'Se dió de baja correctamente el estado de reserva';
  } else {
	echo 'Error: ' . $mysql->error;
  }

  $mysql->close();
?>