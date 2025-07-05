<?php
  require("../conexion.php");

  $resultado = $mysql->query("update perfil set perfil_estado = 0 WHERE id_perfil=$_REQUEST[id_perfil]");

  if ($resultado) {
	echo 'Se dió de baja correctamente el perfil';
  } else {
	echo 'Error: ' . $mysql->error;
  }

  $mysql->close();
?>