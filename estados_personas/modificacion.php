
<?php
  require("../conexion.php");
  require_once("../funciones.php");

  $resultado = $mysql->query("update estadopersona set 
				estadopersona_descripcion='$_REQUEST[estadopersona_descripcion]'
				where id_estadopersona=$_REQUEST[id_estadopersona]");

  if ($resultado) {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Se modificaron los datos del estado de persona correctamente', 'exito');
  } else {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
  }
?>