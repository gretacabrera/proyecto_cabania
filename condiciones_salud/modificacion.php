
<?php
  require("../conexion.php");
  require("../includes/mensajes.php");

  $resultado = $mysql->query("update condicionsalud set 
				condicionsalud_descripcion='$_REQUEST[condicionsalud_descripcion]'
				where id_condicionsalud=$_REQUEST[id_condicionsalud]");

  if ($resultado) {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Se modificaron los datos de la condicion de salud correctamente', 'exito');
  } else {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
  }
?>