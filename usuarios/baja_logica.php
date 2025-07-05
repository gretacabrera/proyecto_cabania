<?php
	require("../conexion.php");
	require("../includes/mensajes.php");

	$resultado = $mysql->query("update usuario set usuario_estado = 3 WHERE id_usuario=$_REQUEST[id_usuario]");

	if ($resultado) {
	  echo 'Se dió de baja correctamente el usuario';
  } else {
	  echo 'Error: ' . $mysql->error;
  }

  $mysql->close();
?>