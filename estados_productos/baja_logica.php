<?php
  require_once("../conexion.php");

  $resultado = $mysql->query("update estadoproducto set estadoproducto_estado = 0 WHERE id_estadoproducto=$_REQUEST[id_estadoproducto]");

  if ($resultado) {
	echo 'Se dió de baja correctamente el estado de producto';
  } else {
	echo 'Error: ' . $mysql->error;
  }

  $mysql->close();
?>