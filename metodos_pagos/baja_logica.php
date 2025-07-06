<?php
  require("../conexion.php");

  $resultado = $mysql->query("update metodopago set metodopago_estado = 0 WHERE id_metodopago=$_REQUEST[id_metodopago]");

  if ($resultado) {
	echo 'Se dió de baja correctamente la metodopago';
  } else {
	echo 'Error: ' . $mysql->error;
  }

  $mysql->close();
?>