
<?php
  require("../conexion.php");
  require_once("../funciones.php");

  $resultado = $mysql->query("update estadoreserva set 
				estadoreserva_descripcion='$_REQUEST[estadoreserva_descripcion]'
				where id_estadoreserva=$_REQUEST[id_estadoreserva]");

  if ($resultado) {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Se modificaron los datos del estado de reserva correctamente', 'exito');
  } else {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
  }
?>