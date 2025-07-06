<?php
  require("../conexion.php");

  $resultado = $mysql->query("update perfil_modulo set perfilmodulo_estado = 0 WHERE id_perfilmodulo=$_REQUEST[id_perfilmodulo]");

  if ($resultado) {
	echo 'Se dió de baja correctamente la asignación perfil-modulo';
  } else {
	echo 'Error: ' . $mysql->error;
  }

  $mysql->close();
?>