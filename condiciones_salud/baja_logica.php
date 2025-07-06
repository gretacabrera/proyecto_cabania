<?php
  require("../conexion.php");

  $resultado = $mysql->query("update condicionsalud set condicionsalud_estado = 0 WHERE id_condicionsalud=$_REQUEST[id_condicionsalud]");

  if ($resultado) {
	echo 'Se dió de baja correctamente la condicionsalud';
  } else {
	echo 'Error: ' . $mysql->error;
  }

  $mysql->close();
?>