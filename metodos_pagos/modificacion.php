
<?php
  require("../conexion.php");
  require("../includes/mensajes.php");

  $resultado = $mysql->query("update metodopago set 
				metodopago_descripcion='$_REQUEST[metodopago_descripcion]'
				where id_metodopago=$_REQUEST[id_metodopago]");

  if ($resultado) {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Se modificaron los datos de la categoría correctamente', 'exito');
  } else {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
  }
?>