
<?php
  require("../conexion.php");
  require_once("../funciones.php");

  $resultado = $mysql->query("update metododepago set 
				metododepago_descripcion='$_REQUEST[metododepago_descripcion]'
				where id_metododepago=$_REQUEST[id_metododepago]");

  if ($resultado) {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Se modificaron los datos de la categoría correctamente', 'exito');
  } else {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
  }
?>