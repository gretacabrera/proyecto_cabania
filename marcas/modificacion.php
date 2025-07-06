
<?php
  require("../conexion.php");
  require_once("../funciones.php");

  $resultado = $mysql->query("update marca set 
				marca_descripcion='$_REQUEST[marca_descripcion]'
				where id_marca=$_REQUEST[id_marca]");

  if ($resultado) {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Se modificaron los datos de la marca correctamente', 'exito');
  } else {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
  }
?>