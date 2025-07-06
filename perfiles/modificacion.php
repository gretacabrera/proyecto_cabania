
<?php
  require("../conexion.php");
  require_once("../funciones.php");

  $resultado = $mysql->query("update perfil set 
				perfil_descripcion='$_REQUEST[perfil_descripcion]'
				where id_perfil=$_REQUEST[id_perfil]");

  if ($resultado) {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Se modificaron los datos del perfil correctamente', 'exito');
  } else {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
  }
?>