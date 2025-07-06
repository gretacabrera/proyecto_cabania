<?php
  require("../conexion.php");

  $resultado = $mysql->query("update estadopersona set estadopersona_estado = 0 WHERE id_estadopersona=$_REQUEST[id_estadopersona]");

  if ($resultado) {
	echo 'Se dió de baja correctamente el estado de persona';
  } else {
	echo 'Error: ' . $mysql->error;
  }

  $mysql->close();
?>