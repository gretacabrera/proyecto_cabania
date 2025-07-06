
<?php
  require("../conexion.php");
  require_once("../funciones.php");

  $resultado = $mysql->query("update categoria set 
				categoria_descripcion='$_REQUEST[categoria_descripcion]'
				where id_categoria=$_REQUEST[id_categoria]");

  if ($resultado) {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Se modificaron los datos de la categoría correctamente', 'exito');
  } else {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
  }
?>